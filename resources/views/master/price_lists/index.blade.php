@extends('layouts.app', ['title' => 'Daftar Harga Item'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h3 class="text-xl font-bold text-white">Price List Management</h3>
            <p class="text-slate-400 text-sm italic">Handle item pricing per warehouse terminal</p>
        </div>
        <button onclick="openCreateModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold flex items-center gap-2 transition-all shadow-lg shadow-indigo-500/20 whitespace-nowrap text-sm">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Item
        </button>
    </div>

    <!-- Filter Section -->
    <div class="glass-card p-8 rounded-[2.5rem] border border-white/5 bg-slate-900/40 mb-8">
        <form action="{{ route('price_lists.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-8 items-end">
            <div class="space-y-2">
                <label class="block text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Search Item</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Nama / Kode Item..." class="w-full bg-[#0f172a] border border-white/10 rounded-xl py-3 pl-10 pr-4 text-white text-sm font-bold outline-none focus:border-indigo-500 transition-all shadow-inner">
                    <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>
            <div class="space-y-2">
                <label class="block text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Business Manager / Warehouse</label>
                <select name="warehouse_id" class="w-full bg-[#0f172a] border border-white/10 rounded-xl py-3 px-4 text-white text-sm font-bold outline-none focus:border-indigo-500 transition-all shadow-inner">
                    <option value="">-- SEMUA WAREHOUSE --</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ $warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <label class="block text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Quick Select Item</label>
                <select name="item_id" class="w-full bg-[#0f172a] border border-white/10 rounded-xl py-3 px-4 text-white text-sm font-bold outline-none focus:border-indigo-500 transition-all shadow-inner">
                    <option value="">-- SEMUA ITEM --</option>
                    @foreach($items as $i)
                        <option value="{{ $i->id }}" {{ $item_id == $i->id ? 'selected' : '' }}>{{ $i->code }} - {{ $i->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('price_lists.index') }}" class="flex-1 bg-rose-500/10 hover:bg-rose-500 text-rose-500 hover:text-white py-3 rounded-xl font-black text-[12px] uppercase tracking-widest text-center transition-all">Reset</a>
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-xl font-black text-[12px] uppercase tracking-widest shadow-lg shadow-emerald-500/20 transition-all">Filter</button>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="glass-card rounded-[2rem] overflow-hidden border border-white/5 bg-slate-900/20">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[12px] font-black uppercase tracking-widest border-b border-white/5">
                    <th class="px-8 py-5">ID Katalog Item</th>
                    <th class="px-8 py-5">Katalog Item</th>
                    <th class="px-8 py-5 text-center">HNA (Rp)</th>
                    <th class="px-8 py-5 text-center">HNA + PPN (Rp)</th>
                    <th class="px-8 py-5 text-center">HET Pabrik</th>
                    <th class="px-8 py-5 text-center">Berlaku</th>
                    <th class="px-8 py-5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $price)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-8 py-5">
                        <div class="font-black text-indigo-400 text-sm tracking-tighter">{{ $price->item->code }}</div>
                    </td>
                    <td class="px-8 py-5">
                        <div class="font-bold text-white text-sm mb-2">{{ $price->item->name }}</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($price->all_warehouses as $wh)
                                <span class="px-2 py-0.5 bg-slate-800/80 text-[9px] font-black text-indigo-300 border border-indigo-500/20 rounded uppercase tracking-widest">
                                    {{ $wh->name }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-8 py-5 text-center">
                        <div class="font-bold text-white text-sm">Rp {{ number_format($price->hna, 0, ',', '.') }} <span class="text-slate-500 font-normal">/ {{ $price->item->unit->name }}</span></div>
                    </td>
                    <td class="px-8 py-5 text-center">
                        <div class="font-bold text-emerald-400 text-sm">Rp {{ number_format($price->hna_ppn, 0, ',', '.') }} <span class="text-emerald-500/50 font-normal">/ {{ $price->item->unit->name }}</span></div>
                    </td>
                    <td class="px-8 py-5 text-center">
                        <div class="font-bold text-amber-400 text-sm">Rp {{ number_format($price->het, 0, ',', '.') }} <span class="text-amber-500/50 font-normal">/ {{ $price->item->unit->name }}</span></div>
                    </td>
                    <td class="px-8 py-5 text-center">
                        <div class="text-[12px] text-slate-300 font-bold italic">{{ date('d-m-Y', strtotime($price->start_date)) }}</div>
                    </td>
                    <td class="px-8 py-5 text-right">
                        <div class="flex justify-end gap-2">
                            <button onclick='openEditModal(@json($price))' class="p-2 text-slate-500 hover:text-white hover:bg-slate-800 rounded-lg transition-all"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                            <form action="{{ route('price_lists.delete', $price->id) }}" method="POST" onsubmit="return confirm('Hapus harga ini untuk SEMUA gudang terkait?')">
                                @csrf
                                <button type="submit" class="p-2 text-slate-500 hover:text-rose-500 hover:bg-rose-500/10 rounded-lg transition-all"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-8 py-20 text-center text-slate-500 italic uppercase tracking-widest text-[11px]">No price list entries found for the current filter.</td></tr>
                @endforelse
            </tbody>
        </table>
        
        @if($data->hasPages())
        <div class="px-8 py-4 bg-slate-800/30 border-t border-white/5">
            {{ $data->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Create/Edit -->
<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-[#0f172a]/80 backdrop-blur-md p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-3xl rounded-[2.5rem] flex flex-col max-h-[90vh] overflow-hidden shadow-2xl">
        <div class="p-8 border-b border-white/5 flex justify-between items-center bg-slate-800/30">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white">
                    <i data-lucide="tag" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 id="modalTitle" class="text-xl font-black text-white tracking-tight uppercase">Price Entry Terminal</h3>
                    <p class="text-[12px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">Item Pricing & Logistics</p>
                </div>
            </div>
            <button onclick="closeModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-all"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-10 modal-scroll bg-[#0f172a]/20">
            <form id="priceForm" action="{{ route('price_lists.store') }}" method="POST" class="space-y-10">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="md:col-span-2 space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Target Product SKU*</label>
                        <select name="item_id" id="item_id_modal" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-bold text-lg shadow-inner" required>
                            <option value="">-- SELECT PRODUCT --</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->code }} - {{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2 space-y-4">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Warehouse Locations*</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 p-6 bg-slate-900/50 rounded-3xl border border-white/5 max-h-48 overflow-y-auto modal-scroll">
                            @foreach($warehouses as $wh)
                                <label class="flex items-center gap-3 p-3 bg-slate-800/50 rounded-xl border border-white/5 cursor-pointer hover:bg-indigo-500/10 hover:border-indigo-500/20 transition-all group">
                                    <input type="checkbox" name="warehouse_id[]" value="{{ $wh->id }}" class="warehouse-checkbox w-5 h-5 rounded-md bg-slate-900 border-white/10 text-indigo-500 focus:ring-indigo-500 transition-all">
                                    <span class="text-[11px] font-black text-slate-400 group-hover:text-white uppercase tracking-tighter">{{ $wh->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">HNA (Net Amount)*</label>
                        <div class="relative">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-500 font-bold">Rp</span>
                            <input type="number" name="hna" id="hna" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 pl-14 pr-6 focus:border-indigo-500 outline-none text-white font-black" required oninput="calculatePPN()">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">HNA + PPN (11%)*</label>
                        <div class="relative">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-emerald-500 font-bold">Rp</span>
                            <input type="number" name="hna_ppn" id="hna_ppn" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 pl-14 pr-6 focus:border-indigo-500 outline-none text-emerald-400 font-black" required>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">HET (Retail Price)*</label>
                        <div class="relative">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-amber-500 font-bold">Rp</span>
                            <input type="number" name="het" id="het" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 pl-14 pr-6 focus:border-indigo-500 outline-none text-amber-400 font-black" required>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Start Date*</label>
                        <input type="date" name="start_date" id="start_date" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-bold" required>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="p-10 border-t border-white/5 bg-slate-800/30 flex justify-end gap-6 rounded-b-[2.5rem]">
            <button onclick="closeModal()" class="text-sm font-black text-slate-500 uppercase tracking-widest hover:text-white transition-colors">Discard</button>
            <button type="submit" form="priceForm" class="bg-indigo-600 hover:bg-indigo-700 text-white px-12 py-4 rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-2xl shadow-indigo-500/40 active:scale-95 transition-all">
                Authorize Price
            </button>
        </div>
    </div>
</div>

<script>
    function calculatePPN() {
        const hna = document.getElementById('hna').value;
        if (hna) {
            document.getElementById('hna_ppn').value = Math.round(hna * 1.11);
        }
    }

    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Authorize New Price';
        document.getElementById('priceForm').action = "{{ route('price_lists.store') }}";
        document.getElementById('priceForm').reset();
        document.getElementById('item_id_modal').disabled = false;
        document.getElementById('modal').classList.remove('hidden');
    }

    function openEditModal(price) {
        document.getElementById('modalTitle').innerText = 'Edit Authorized Price';
        document.getElementById('priceForm').action = "/master/price_lists/update/" + price.id;
        
        document.getElementById('item_id_modal').value = price.item_id;
        document.getElementById('item_id_modal').disabled = true; // Typically don't change item on edit
        
        // Add a hidden input for item_id since disabled selects don't submit
        let hiddenItem = document.getElementById('hidden_item_id');
        if (!hiddenItem) {
            hiddenItem = document.createElement('input');
            hiddenItem.type = 'hidden';
            hiddenItem.id = 'hidden_item_id';
            hiddenItem.name = 'item_id';
            document.getElementById('priceForm').appendChild(hiddenItem);
        }
        hiddenItem.value = price.item_id;

        document.getElementById('hna').value = price.hna;
        document.getElementById('hna_ppn').value = price.hna_ppn;
        document.getElementById('het').value = price.het;
        document.getElementById('start_date').value = price.start_date;

        // Reset and Set Warehouse Checkboxes
        document.querySelectorAll('.warehouse-checkbox').forEach(cb => {
            cb.checked = price.all_ids && price.all_warehouses.some(wh => wh.id == cb.value);
        });

        document.getElementById('modal').classList.remove('hidden');
    }

    function closeModal() { document.getElementById('modal').classList.add('hidden'); }
</script>
@endsection
