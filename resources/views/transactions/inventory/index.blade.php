@extends('layouts.app', ['title' => 'Inventory Management'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white">Inventory Management</h3>
            <p class="text-slate-400 text-sm italic">Multi-item stock transaction terminal</p>
        </div>
        <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all shadow-lg shadow-indigo-500/20">
            <i data-lucide="plus" class="w-4 h-4"></i> Buat Transaksi Baru
        </button>
    </div>

    <!-- History Table -->
    <div class="glass-card rounded-2xl overflow-hidden border border-white/5 bg-slate-900/20">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h4 class="text-sm font-black text-slate-500 uppercase tracking-widest">Recent Transactions</h4>
            <div class="flex gap-2">
                <input type="text" placeholder="Cari No. Referensi..." class="bg-slate-800 border border-white/5 rounded-lg px-4 py-1.5 text-sm text-white outline-none focus:border-indigo-500">
            </div>
        </div>
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[12px] font-black uppercase tracking-widest border-b border-white/5">
                    <th class="px-8 py-4">Ref. Number</th>
                    <th class="px-8 py-4">Transaction Date</th>
                    <th class="px-8 py-4">Type</th>
                    <th class="px-8 py-4">Warehouse</th>
                    <th class="px-8 py-4 text-center">Items</th>
                    <th class="px-8 py-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @php
                    $grouped = $data->groupBy('reference_no');
                @endphp
                @forelse($grouped as $ref => $items)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-8 py-4 font-bold text-white tracking-tight">{{ $ref ?: 'TRANS-'.str_pad($items->first()->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-8 py-4 text-sm text-slate-300">{{ $items->first()->created_at->format('d M Y, H:i') }}</td>
                    <td class="px-8 py-4">
                        @php
                            $refText = $ref ?: '';
                            $label = 'MANUAL TRANS';
                            $color = 'indigo';
                            
                            if (str_contains($refText, 'OPNAME')) {
                                $label = 'STOCK OPNAME';
                                $color = 'amber';
                            } elseif (str_contains($refText, 'MUTATION') || str_contains($refText, 'MUT-')) {
                                $label = 'MUTASI GUDANG';
                                $color = 'blue';
                            } elseif (str_contains($refText, 'PRODUCTION') || str_contains($refText, 'WO-')) {
                                $label = 'HASIL PRODUKSI';
                                $color = 'emerald';
                            } elseif (str_contains($refText, 'DEL-') || str_contains($refText, 'PKG-') || str_contains($refText, 'SJ-')) {
                                $label = 'DELIVERY / POS';
                                $color = 'rose';
                            }
                        @endphp
                        <span class="text-[9px] font-black bg-{{ $color }}-500/10 text-{{ $color }}-500 px-2.5 py-1 rounded-lg border border-{{ $color }}-500/20 uppercase tracking-tighter">
                            {{ $label }}
                        </span>
                    </td>
                    <td class="px-8 py-4 text-sm font-bold text-indigo-400 uppercase tracking-wider">{{ $items->first()->warehouse->name }}</td>
                    <td class="px-8 py-4 text-center">
                        <span class="bg-slate-800 text-slate-400 px-3 py-1 rounded-full text-[12px] font-bold">{{ $items->count() }} Items</span>
                    </td>
                    <td class="px-8 py-4 text-right">
                        <button onclick="viewDetail('{{ $ref }}', {{ $items->toJson() }})" class="p-2 text-slate-500 hover:text-white transition-colors"><i data-lucide="eye" class="w-4 h-4"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-8 py-20 text-center text-slate-500">No transactions recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Transaction View Detail Modal -->
<div id="detailModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-[#0f172a]/90 backdrop-blur-md p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-4xl rounded-[2.5rem] flex flex-col shadow-2xl overflow-hidden">
        <div class="p-8 border-b border-white/5 flex justify-between items-center bg-slate-800/30">
            <div>
                <h3 id="detail_ref" class="text-xl font-black text-white tracking-tight uppercase"></h3>
                <p id="detail_info" class="text-[12px] text-slate-500 font-bold uppercase tracking-widest mt-1"></p>
            </div>
            <button onclick="closeDetailModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:text-white transition-all"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <div class="p-8 overflow-y-auto max-h-[60vh]">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[11px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">
                        <th class="px-4 py-3">Product</th>
                        <th class="px-4 py-3 text-center">Quantity</th>
                        <th class="px-4 py-3 text-center">Unit</th>
                        <th class="px-4 py-3">Note</th>
                    </tr>
                </thead>
                <tbody id="detail_rows" class="divide-y divide-white/5"></tbody>
            </table>
        </div>
        <div class="p-8 border-t border-white/5 bg-slate-800/30 flex justify-end">
            <button onclick="closeDetailModal()" class="bg-slate-700 text-white px-8 py-2.5 rounded-xl font-bold text-sm uppercase tracking-widest">Close</button>
        </div>
    </div>
</div>

<!-- Add Transaction Modal -->
<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-[#0f172a]/95 backdrop-blur-xl p-4 md:p-10">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-6xl rounded-[2.5rem] flex flex-col max-h-full overflow-hidden shadow-2xl">
        <div class="p-8 border-b border-white/5 flex justify-between items-center bg-slate-800/30">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/40">
                    <i data-lucide="arrow-left-right" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-white tracking-tight uppercase">New Stock Transaction</h3>
                    <p class="text-[12px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">Inventory Operations Terminal</p>
                </div>
            </div>
            <button onclick="closeModal()" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-all"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-10 modal-scroll">
            <form id="transForm" action="{{ route('inventory.store') }}" method="POST" class="space-y-12">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Type*</label>
                        <div class="flex p-1 bg-slate-900 rounded-2xl border border-white/5">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="type" value="IN" class="hidden peer" checked>
                                <div class="py-3 text-center rounded-xl font-black text-sm uppercase tracking-widest text-slate-500 peer-checked:bg-emerald-500 peer-checked:text-white transition-all">Stock In</div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="type" value="OUT" class="hidden peer">
                                <div class="py-3 text-center rounded-xl font-black text-sm uppercase tracking-widest text-slate-500 peer-checked:bg-rose-500 peer-checked:text-white transition-all">Stock Out</div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Warehouse Location*</label>
                        <select name="warehouse_id" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 text-white font-bold outline-none focus:border-indigo-500 transition-all shadow-inner" required>
                            @foreach($warehouses as $w) <option value="{{ $w->id }}">{{ $w->name }}</option> @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Ref. Number (PO/SO)*</label>
                        <input type="text" name="reference_no" placeholder="REF-{{ date('YmdHis') }}" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 text-white font-bold outline-none focus:border-indigo-500 transition-all shadow-inner" required>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="flex justify-between items-center px-2">
                        <div class="flex items-center gap-3">
                            <div class="w-1 h-6 bg-indigo-500 rounded-full shadow-[0_0_10px_indigo]"></div>
                            <h4 class="text-sm font-black text-white uppercase tracking-[0.3em]">Item Specification List</h4>
                        </div>
                        <button type="button" onclick="addRow()" class="bg-indigo-600/10 hover:bg-indigo-600 text-indigo-400 hover:text-white px-4 py-2 rounded-xl text-[12px] font-black uppercase tracking-widest transition-all flex items-center gap-2">
                            <i data-lucide="plus-circle" class="w-3 h-3"></i> Tambah Item
                        </button>
                    </div>

                    <div class="glass-card rounded-[2rem] overflow-hidden border border-white/5 bg-slate-900/10">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-800/80 text-slate-400 text-[12px] font-black uppercase tracking-[0.2em]">
                                    <th class="px-8 py-5">Product SKU</th>
                                    <th class="px-8 py-5 text-center">Quantity</th>
                                    <th class="px-8 py-5 text-center">Unit</th>
                                    <th class="px-8 py-5">Note</th>
                                    <th class="px-8 py-5 text-right"></th>
                                </tr>
                            </thead>
                            <tbody id="itemRows" class="divide-y divide-white/5">
                                <tr class="item-row group">
                                    <td class="px-6 py-4">
                                        <select name="items[0][item_id]" class="w-full bg-slate-800 border-white/10 rounded-xl px-4 py-3 text-sm text-white font-bold outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all cursor-pointer" required onchange="updateUnit(this, 0)">
                                            <option value="" class="text-slate-400">-- Pilih SKU Produk --</option>
                                            @foreach($skuMasterList as $skuItem) 
                                            <option value="{{ $skuItem->id }}" data-unit="{{ $skuItem->unit_name ?? '-' }}" class="text-white bg-slate-800">
                                                {{ $skuItem->code }} - {{ $skuItem->name }}
                                            </option> 
                                            @endforeach
                                        </select>
                                        <div id="current-stock-0" class="text-[11px] text-indigo-400 font-bold mt-2 ml-1 uppercase tracking-widest"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" name="items[0][quantity]" step="0.01" class="w-full bg-slate-800/50 border-none rounded-xl py-2 px-4 text-center text-white font-black focus:ring-1 focus:ring-indigo-500 shadow-inner" placeholder="0" required>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span id="unit-0" class="text-[12px] font-black text-slate-500 uppercase tracking-widest">-</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="text" name="items[0][note]" class="w-full bg-transparent border-none text-slate-400 text-sm italic outline-none focus:ring-0" placeholder="Optional notes...">
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button type="button" class="text-slate-700 hover:text-rose-500 transition-colors opacity-0 group-hover:opacity-100" onclick="removeRow(this)"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="p-10 border-t border-white/5 bg-slate-800/30 flex justify-end gap-6 rounded-b-[2.5rem]">
            <button onclick="closeModal()" class="text-sm font-black text-slate-500 uppercase tracking-widest hover:text-white transition-colors">Discard</button>
            <button type="submit" form="transForm" class="bg-indigo-600 hover:bg-indigo-700 text-white px-12 py-4 rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-2xl shadow-indigo-500/40 active:scale-95 transition-all">
                Submit Transaction
            </button>
        </div>
    </div>
</div>

<script>
    let rowCount = 1;
    function addRow() {
        const tbody = document.getElementById('itemRows');
        const newRow = document.createElement('tr');
        newRow.className = 'item-row group border-t border-white/5';
        newRow.innerHTML = `
            <td class="px-6 py-4">
                <select name="items[${rowCount}][item_id]" class="w-full bg-slate-800 border-white/10 rounded-xl px-4 py-3 text-sm text-white font-bold outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all cursor-pointer" required onchange="updateUnit(this, ${rowCount})">
                    <option value="" class="text-slate-400">-- Pilih SKU Produk --</option>
                    @foreach($skuMasterList as $skuItem) 
                    <option value="{{ $skuItem->id }}" data-unit="{{ $skuItem->unit_name ?? '-' }}" class="text-white bg-slate-800">
                        {{ $skuItem->code }} - {{ $skuItem->name }}
                    </option> 
                    @endforeach
                </select>
                <div id="current-stock-${rowCount}" class="text-[11px] text-indigo-400 font-bold mt-2 ml-1 uppercase tracking-widest"></div>
            </td>
            <td class="px-6 py-4">
                <input type="number" name="items[${rowCount}][quantity]" step="0.01" class="w-full bg-slate-800/50 border-none rounded-xl py-2 px-4 text-center text-white font-black focus:ring-1 focus:ring-indigo-500 shadow-inner" placeholder="0" required>
            </td>
            <td class="px-6 py-4 text-center">
                <span id="unit-${rowCount}" class="text-[12px] font-black text-slate-500 uppercase tracking-widest">-</span>
            </td>
            <td class="px-6 py-4">
                <input type="text" name="items[${rowCount}][note]" class="w-full bg-transparent border-none text-slate-400 text-sm italic outline-none focus:ring-0" placeholder="Optional notes...">
            </td>
            <td class="px-6 py-4 text-right">
                <button type="button" class="text-slate-700 hover:text-rose-500 transition-colors opacity-0 group-hover:opacity-100" onclick="removeRow(this)"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
            </td>
        `;
        tbody.appendChild(newRow);
        lucide.createIcons();
        rowCount++;
    }

    function removeRow(btn) {
        if(document.querySelectorAll('.item-row').length > 1) {
            btn.closest('tr').remove();
        }
    }

    async function updateUnit(select, index) {
        const option = select.options[select.selectedIndex];
        const unit = option.getAttribute('data-unit');
        const warehouseId = document.querySelector('select[name="warehouse_id"]').value;
        const itemId = select.value;
        
        document.getElementById(`unit-${index}`).innerText = unit || '-';

        // Fetch current stock
        const stockDisplay = document.getElementById(`current-stock-${index}`);
        if (itemId && warehouseId) {
            stockDisplay.innerText = 'Checking...';
            try {
                const res = await fetch(`/transactions/stock-opname/get-stock?item_id=${itemId}&warehouse_id=${warehouseId}`);
                const data = await res.json();
                stockDisplay.innerText = `Stok: ${data.stock}`;
            } catch (err) {
                stockDisplay.innerText = '';
            }
        } else {
            stockDisplay.innerText = '';
        }
    }

    function viewDetail(ref, items) {
        document.getElementById('detail_ref').innerText = ref || 'UNREFERENCED';
        document.getElementById('detail_info').innerText = `${items[0].created_at} | Type: ${items[0].type} | Warehouse: ${items[0].warehouse.name}`;
        
        const rows = document.getElementById('detail_rows');
        rows.innerHTML = '';
        items.forEach(item => {
            const isInput = item.type === 'IN';
            const qtyClass = isInput ? 'text-emerald-400' : 'text-rose-400';
            const qtySign = isInput ? '+' : '-';
            
            rows.innerHTML += `
                <tr class="text-sm text-white">
                    <td class="px-4 py-4 font-bold">${item.item.name}</td>
                    <td class="px-4 py-4 text-center font-black ${qtyClass}">${qtySign}${parseFloat(item.quantity)}</td>
                    <td class="px-4 py-4 text-center text-slate-500 font-bold uppercase tracking-widest">${item.item.unit ? item.item.unit.name : '-'}</td>
                    <td class="px-4 py-4 text-slate-400 italic">${item.note || '-'}</td>
                </tr>
            `;
        });
        document.getElementById('detailModal').classList.remove('hidden');
    }

    function closeDetailModal() { document.getElementById('detailModal').classList.add('hidden'); }
    function openModal() { document.getElementById('modal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }

    // Refresh stock info when warehouse changes
    document.querySelector('select[name="warehouse_id"]').addEventListener('change', function() {
        document.querySelectorAll('.item-row').forEach((row, index) => {
            const select = row.querySelector('select[name*="[item_id]"]');
            if (select && select.value) {
                updateUnit(select, index);
            }
        });
    });
</script>
@endsection
