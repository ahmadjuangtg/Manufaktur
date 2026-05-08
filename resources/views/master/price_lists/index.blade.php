@extends('layouts.app', ['title' => 'Daftar Harga Item'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white">Price List Management</h3>
            <p class="text-slate-400 text-sm italic">Handle item pricing per warehouse terminal</p>
        </div>
        <button onclick="openCreateModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all shadow-lg shadow-indigo-500/20">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Item
        </button>
    </div>

    <!-- Filter Section -->
    <div class="glass-card p-8 rounded-[2.5rem] border border-white/5 bg-slate-900/40 mb-8">
        <form action="{{ route('price_lists.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-8 items-end">
            <div class="space-y-2">
                <label class="block text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Business Manager / Warehouse</label>
                <select name="warehouse_id" class="w-full bg-[#0f172a] border border-white/10 rounded-xl py-3 px-4 text-white text-sm font-bold outline-none focus:border-indigo-500 transition-all shadow-inner">
                    <option value="">-- PILIH WAREHOUSE --</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ $warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <label class="block text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Item Filter</label>
                <select name="item_id" class="w-full bg-[#0f172a] border border-white/10 rounded-xl py-3 px-4 text-white text-sm font-bold outline-none focus:border-indigo-500 transition-all shadow-inner">
                    <option value="">-- PILIH ITEM --</option>
                    @foreach($items as $i)
                        <option value="{{ $i->id }}" {{ $item_id == $i->id ? 'selected' : '' }}>{{ $i->code }} - {{ $i->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('price_lists.index') }}" class="flex-1 bg-rose-500/10 hover:bg-rose-500 text-rose-500 hover:text-white py-3 rounded-xl font-black text-[12px] uppercase tracking-widest text-center transition-all">Hapus Pencarian</a>
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-xl font-black text-[12px] uppercase tracking-widest shadow-lg shadow-emerald-500/20 transition-all">Cari</button>
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
                        <div class="font-bold text-white text-sm">{{ $price->item->name }}</div>
                        <div class="text-[11px] text-slate-500 uppercase tracking-wider">{{ $price->warehouse->name }}</div>
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
                            <form action="{{ route('price_lists.delete', $price->id) }}" method="POST" onsubmit="return confirm('Hapus harga ini?')">
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
    </div>
</div>

<!-- Modal -->
<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-[#0f172a]/80 backdrop-blur-md p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-2xl rounded-[2.5rem] flex flex-col max-h-[90vh] overflow-hidden shadow-2xl">
        <div class="p-8 border-b border-white/5 flex justify-between items-center bg-slate-800/30">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white">
                    <i data-lucide="dollar-sign" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 id="modalTitle" class="text-xl font-black text-white tracking-tight uppercase">Price Configuration</h3>
                    <p class="text-[12px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">Pricing Terminal</p>
                </div>
            </div>
            <button onclick="closeModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-all"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-10 modal-scroll bg-[#0f172a]/20">
            <form id="priceForm" action="{{ route('price_lists.store') }}" method="POST" class="space-y-8">
                @csrf
                <input type="hidden" id="price_id" name="price_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Select Item*</label>
                        <select id="item_id" name="item_id" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-bold" required>
                            <option value="">Choose Item</option>
                            @foreach($items as $i) <option value="{{ $i->id }}">{{ $i->code }} - {{ $i->name }}</option> @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Warehouse Location*</label>
                        <select id="warehouse_id" name="warehouse_id" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-bold" required>
                            <option value="">Choose Warehouse</option>
                            @foreach($warehouses as $w) <option value="{{ $w->id }}">{{ $w->name }}</option> @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">HNA (Rp)*</label>
                        <input type="text" id="hna" name="hna" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-bold rupiah-input" placeholder="0" required>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">HNA + PPN (Rp)*</label>
                        <input type="text" id="hna_ppn" name="hna_ppn" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-emerald-400 font-black rupiah-input" placeholder="0" required>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">HET Pabrik (Rp)*</label>
                        <input type="text" id="het" name="het" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-amber-400 font-black rupiah-input" placeholder="0" required>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Effective Date*</label>
                        <input type="date" id="start_date" name="start_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-bold" required>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="p-10 border-t border-white/5 bg-slate-800/30 flex justify-end gap-6 rounded-b-[2.5rem]">
            <button onclick="closeModal()" class="text-xs font-black text-slate-500 uppercase tracking-widest hover:text-white transition-colors">Discard</button>
            <button type="submit" form="priceForm" class="bg-indigo-600 hover:bg-indigo-700 text-white px-12 py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-2xl shadow-indigo-500/40 active:scale-95 transition-all">
                Save Pricing
            </button>
        </div>
    </div>
</div>

<script>
    const rupiahInputs = document.querySelectorAll('.rupiah-input');

    rupiahInputs.forEach(input => {
        input.addEventListener('keyup', function(e) {
            this.value = formatRupiah(this.value);
            if (this.id === 'hna') {
                calculatePPN();
            }
        });
    });

    function formatRupiah(angka, prefix) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }

    function unformatRupiah(angka) {
        return angka.replace(/\./g, '');
    }

    function calculatePPN() {
        const hnaVal = unformatRupiah(document.getElementById('hna').value);
        const hna = parseFloat(hnaVal) || 0;
        const ppn = hna * 0.11; // 11% PPN
        const total = Math.round(hna + ppn);
        document.getElementById('hna_ppn').value = formatRupiah(total.toString());
    }

    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'New Price Entry';
        document.getElementById('priceForm').action = "{{ route('price_lists.store') }}";
        document.getElementById('price_id').value = '';
        document.getElementById('priceForm').reset();
        document.getElementById('modal').classList.remove('hidden');
    }

    function openEditModal(price) {
        document.getElementById('modalTitle').innerText = 'Edit Price Entry';
        document.getElementById('priceForm').action = "/master/price-lists/update/" + price.id;
        document.getElementById('price_id').value = price.id;
        document.getElementById('item_id').value = price.item_id;
        document.getElementById('warehouse_id').value = price.warehouse_id;
        document.getElementById('hna').value = formatRupiah(price.hna.toString());
        document.getElementById('hna_ppn').value = formatRupiah(price.hna_ppn.toString());
        document.getElementById('het').value = formatRupiah(price.het.toString());
        document.getElementById('start_date').value = price.start_date;
        
        document.getElementById('modal').classList.remove('hidden');
    }

    function closeModal() { document.getElementById('modal').classList.add('hidden'); }

    document.getElementById('priceForm').addEventListener('submit', function(e) {
        // Unformat all rupiah inputs before submitting
        rupiahInputs.forEach(input => {
            input.value = unformatRupiah(input.value);
        });
    });
</script>
@endsection
