@extends('layouts.app', ['title' => 'New Stock Opname'])

@section('content')
<div class="space-y-8 pb-20">
    <div class="flex justify-between items-center mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('opname.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:text-white transition-all"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
            <div>
                <h3 class="text-2xl font-black text-white tracking-tight uppercase">New Stock Opname</h3>
                <p class="text-[12px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">Physical Audit Terminal</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-10">
        <!-- Selection Panel -->
        <div class="xl:col-span-4 space-y-8">
            <div class="glass-card p-8 rounded-[2.5rem] border border-white/5 bg-slate-900/40">
                <h4 class="text-[12px] font-black text-white uppercase tracking-[0.3em] mb-8 flex items-center gap-3">
                    <span class="w-2 h-2 bg-indigo-500 rounded-full shadow-[0_0_10px_indigo]"></span>
                    Select Product
                </h4>
                
                <div class="space-y-8">
                    <div class="space-y-2">
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Warehouse Location*</label>
                        <select id="warehouse_select" class="w-full bg-[#0f172a] border border-white/10 rounded-xl py-3 px-4 text-white text-sm font-bold outline-none focus:border-indigo-500 transition-all shadow-inner" onchange="resetSelection()">
                            @foreach($warehouses as $w) <option value="{{ $w->id }}">{{ $w->name }}</option> @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Product*</label>
                        <select id="item_select" class="w-full bg-[#0f172a] border border-white/10 rounded-xl py-3 px-4 text-white text-sm font-bold outline-none focus:border-indigo-500 transition-all shadow-inner" onchange="fetchStock()">
                            <option value="">-- Choose Product --</option>
                            @foreach($items as $i) <option value="{{ $i->id }}" data-unit="{{ $i->unit->name }}" data-code="{{ $i->code }}" data-name="{{ $i->name }}">{{ $i->name }} ({{ $i->code }})</option> @endforeach
                        </select>
                    </div>

                    <div id="stock_display" class="hidden animate-in fade-in slide-in-from-top-2 duration-500">
                        <div class="bg-indigo-500/5 border border-indigo-500/10 rounded-2xl p-6 flex flex-col items-center">
                            <span class="text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2">Current System Stock</span>
                            <div class="flex items-baseline gap-2">
                                <span id="current_stock_val" class="text-4xl font-black text-indigo-400">0</span>
                                <span id="current_unit" class="text-sm font-black text-slate-500 uppercase">UNIT</span>
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="addToOpnameList()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-xl font-black text-[12px] uppercase tracking-[0.2em] shadow-xl shadow-indigo-500/30 transition-all active:scale-95 flex items-center justify-center gap-3">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add to List
                    </button>
                </div>
            </div>
        </div>

        <!-- List Panel -->
        <div class="xl:col-span-8">
            <div class="glass-card flex flex-col h-full rounded-[2.5rem] border border-white/5 bg-slate-900/20 overflow-hidden">
                <div class="p-8 border-b border-white/5 flex justify-between items-center bg-slate-800/30">
                    <h4 class="text-[12px] font-black text-white uppercase tracking-[0.3em] flex items-center gap-3">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full shadow-[0_0_10px_emerald]"></span>
                        Audit List
                    </h4>
                    <span id="item_count_badge" class="bg-slate-800 text-slate-400 px-4 py-1.5 rounded-full text-[11px] font-black uppercase tracking-widest">0 Items</span>
                </div>

                <div class="flex-1 overflow-x-auto">
                    <form id="opnameSubmitForm" action="{{ route('opname.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="warehouse_id" id="submit_warehouse_id">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-800/50 text-slate-400 text-[12px] font-black uppercase tracking-[0.3em] border-b border-white/5">
                                    <th class="px-8 py-5">Product Info</th>
                                    <th class="px-4 py-5 text-center">System</th>
                                    <th class="px-4 py-5 text-center">Actual</th>
                                    <th class="px-4 py-5 text-center">Diff</th>
                                    <th class="px-8 py-5 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody id="opnameRows" class="divide-y divide-white/5">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                    </form>
                </div>

                <div class="p-10 border-t border-white/5 bg-slate-800/30">
                    <button type="submit" form="opnameSubmitForm" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-5 rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-2xl shadow-indigo-500/40 transition-all flex items-center justify-center gap-3 active:scale-[0.98]">
                        <i data-lucide="check-circle" class="w-4 h-4"></i> Submit Audit Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Detail Modal -->
<div id="editModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/80 backdrop-blur-md p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden">
        <div class="p-8 border-b border-white/5 bg-slate-800/50 flex justify-between items-center">
            <h3 class="text-sm font-black text-white uppercase tracking-widest">Audit Detail</h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <div class="p-10 space-y-8">
            <div>
                <p id="modal_item_name" class="text-white font-black text-lg"></p>
                <p id="modal_item_code" class="text-[12px] text-slate-500 font-bold uppercase tracking-widest mt-1"></p>
            </div>
            
            <div class="grid grid-cols-2 gap-6">
                <div class="bg-slate-900/50 p-4 rounded-2xl border border-white/5">
                    <p class="text-[12px] font-black text-slate-500 uppercase tracking-widest mb-1">System Stock</p>
                    <p id="modal_system_stock" class="text-xl font-black text-indigo-400"></p>
                </div>
                <div class="bg-slate-900/50 p-4 rounded-2xl border border-white/5">
                    <p class="text-[12px] font-black text-slate-500 uppercase tracking-widest mb-1">Difference</p>
                    <p id="modal_diff" class="text-xl font-black text-slate-400">0</p>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Physical Stock Count*</label>
                <input type="number" id="modal_actual_input" step="0.01" class="w-full bg-[#0f172a] border border-white/10 rounded-2xl py-4 px-6 text-2xl font-black text-white outline-none focus:border-indigo-500 transition-all shadow-inner" oninput="updateModalDiff()">
            </div>

            <div class="space-y-2">
                <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Audit Notes / Reason</label>
                <textarea id="modal_note_input" rows="3" class="w-full bg-[#0f172a] border border-white/10 rounded-2xl py-4 px-6 text-slate-300 text-sm font-bold outline-none focus:border-indigo-500 transition-all" placeholder="Enter reason for difference..."></textarea>
            </div>
        </div>
        <div class="p-8 bg-slate-800/30 border-t border-white/5">
            <button onclick="saveModalDetail()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-xl font-black text-[12px] uppercase tracking-widest shadow-xl shadow-indigo-500/20 transition-all">Apply Audit Details</button>
        </div>
    </div>
</div>

<template id="row_template">
    <tr class="opname-row group" id="row_INDEX">
        <td class="px-8 py-6">
            <input type="hidden" name="items[INDEX][item_id]" value="ITEM_ID">
            <input type="hidden" name="items[INDEX][physical_qty]" id="input_actual_INDEX" value="SYSTEM_STOCK">
            <input type="hidden" name="items[INDEX][note]" id="input_note_INDEX" value="">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-slate-800 rounded-lg flex items-center justify-center text-slate-500 flex-shrink-0">
                    <i data-lucide="box" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-white font-bold text-[11px] truncate">ITEM_NAME</p>
                    <p class="text-[12px] text-slate-500 uppercase tracking-widest font-bold">ITEM_CODE</p>
                </div>
            </div>
        </td>
        <td class="px-4 py-6 text-center text-slate-500 font-black text-[11px] system-stock-val">SYSTEM_STOCK</td>
        <td class="px-4 py-6 text-center text-white font-black text-[11px] actual-stock-val">SYSTEM_STOCK</td>
        <td class="px-4 py-6 text-center">
            <span class="diff-badge text-[11px] font-black text-slate-400">0</span>
        </td>
        <td class="px-8 py-6 text-right">
            <div class="flex justify-end gap-2">
                <button type="button" class="p-2 bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500 hover:text-white rounded-lg transition-all" onclick="openEditModal(INDEX)" title="Set Audit Detail"><i data-lucide="edit-3" class="w-3.5 h-3.5"></i></button>
                <button type="button" class="p-2 text-slate-700 hover:text-rose-500 transition-colors" onclick="removeRow(this)"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
            </div>
        </td>
    </tr>
</template>

<script>
    let opnameIndex = 0;
    let currentFetchedStock = 0;
    let editingIndex = null;

    async function fetchStock() {
        const itemId = document.getElementById('item_select').value;
        const warehouseId = document.getElementById('warehouse_select').value;
        const display = document.getElementById('stock_display');
        
        if (!itemId) {
            display.classList.add('hidden');
            return;
        }

        const res = await fetch(`/transactions/stock-opname/get-stock?item_id=${itemId}&warehouse_id=${warehouseId}`);
        const data = await res.json();
        
        currentFetchedStock = data.stock;
        document.getElementById('current_stock_val').innerText = data.stock;
        document.getElementById('current_unit').innerText = document.getElementById('item_select').options[document.getElementById('item_select').selectedIndex].dataset.unit;
        display.classList.remove('hidden');
    }

    function resetSelection() {
        document.getElementById('item_select').value = '';
        document.getElementById('stock_display').classList.add('hidden');
    }

    function addToOpnameList() {
        const itemSelect = document.getElementById('item_select');
        const warehouseSelect = document.getElementById('warehouse_select');
        
        if (!itemSelect.value) return;

        warehouseSelect.disabled = true;
        document.getElementById('submit_warehouse_id').value = warehouseSelect.value;

        const existing = document.querySelector(`input[value="${itemSelect.value}"][name*="item_id"]`);
        if (existing) {
            alert('Item sudah ada di daftar!');
            return;
        }

        const template = document.getElementById('row_template').innerHTML;
        const selectedOption = itemSelect.options[itemSelect.selectedIndex];
        
        const rowHtml = template
            .replace(/INDEX/g, opnameIndex)
            .replace(/ITEM_ID/g, itemSelect.value)
            .replace(/ITEM_NAME/g, selectedOption.dataset.name)
            .replace(/ITEM_CODE/g, selectedOption.dataset.code)
            .replace(/SYSTEM_STOCK/g, currentFetchedStock);

        document.getElementById('opnameRows').insertAdjacentHTML('beforeend', rowHtml);
        lucide.createIcons();
        updateCount();
        opnameIndex++;
    }

    function openEditModal(index) {
        editingIndex = index;
        const row = document.getElementById('row_' + index);
        const itemName = row.querySelector('.text-white.font-bold').innerText;
        const itemCode = row.querySelector('.text-slate-500.font-bold').innerText;
        const systemStock = row.querySelector('.system-stock-val').innerText;
        const actualStock = document.getElementById('input_actual_' + index).value;
        const note = document.getElementById('input_note_' + index).value;

        document.getElementById('modal_item_name').innerText = itemName;
        document.getElementById('modal_item_code').innerText = itemCode;
        document.getElementById('modal_system_stock').innerText = systemStock;
        document.getElementById('modal_actual_input').value = actualStock;
        document.getElementById('modal_note_input').value = note;

        updateModalDiff();
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function updateModalDiff() {
        const system = parseFloat(document.getElementById('modal_system_stock').innerText);
        const actual = parseFloat(document.getElementById('modal_actual_input').value) || 0;
        const diff = actual - system;
        const display = document.getElementById('modal_diff');
        
        display.innerText = (diff > 0 ? '+' : '') + diff;
        display.className = 'text-xl font-black ' + (diff === 0 ? 'text-slate-400' : (diff > 0 ? 'text-indigo-400' : 'text-rose-500'));
    }

    function saveModalDetail() {
        const actual = document.getElementById('modal_actual_input').value;
        const note = document.getElementById('modal_note_input').value;
        const index = editingIndex;

        document.getElementById('input_actual_' + index).value = actual;
        document.getElementById('input_note_' + index).value = note;

        const row = document.getElementById('row_' + index);
        row.querySelector('.actual-stock-val').innerText = actual;
        
        const system = parseFloat(row.querySelector('.system-stock-val').innerText);
        const diff = actual - system;
        const badge = row.querySelector('.diff-badge');
        badge.innerText = (diff > 0 ? '+' : '') + diff;
        badge.className = 'diff-badge text-[11px] font-black ' + (diff === 0 ? 'text-slate-400' : (diff > 0 ? 'text-indigo-400' : 'text-rose-500'));

        closeEditModal();
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        updateCount();
        if (document.querySelectorAll('.opname-row').length === 0) {
            document.getElementById('warehouse_select').disabled = false;
        }
    }

    function updateCount() {
        const count = document.querySelectorAll('.opname-row').length;
        document.getElementById('item_count_badge').innerText = count + ' Items';
    }
</script>
@endsection
