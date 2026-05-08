@extends('layouts.app', ['title' => 'Request Items'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white">Permintaan Barang</h3>
            <p class="text-slate-400 text-sm">Ajukan permintaan stok barang baru</p>
        </div>
        <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all shadow-lg shadow-indigo-500/20">
            <i data-lucide="plus" class="w-4 h-4"></i> Buat Permintaan
        </button>
    </div>

    <!-- Table Section -->
    <div class="glass-card rounded-xl overflow-hidden border border-white/5">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[10px] uppercase tracking-widest border-b border-white/5">
                    <th class="px-6 py-4">No. Ref</th>
                    <th class="px-6 py-4">Gudang</th>
                    <th class="px-6 py-4">Tipe Item</th>
                    <th class="px-6 py-4">Item</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Tgl Pengajuan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $item)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-6 py-4 font-mono text-sm text-indigo-400">{{ $item->reference_no }}</td>
                    <td class="px-6 py-4 text-white text-sm">{{ $item->warehouse->name }}</td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-bold text-indigo-300 bg-indigo-500/10 px-2 py-0.5 rounded-md border border-indigo-500/20">
                            {{ $item->type->name ?? '-' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-xs text-white">{{ $item->details->count() }} Items</div>
                        <div class="text-[10px] text-slate-500">{{ $item->details->take(2)->map(fn($d) => $d->item->name)->implode(', ') }}...</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase {{ $item->status == 'PENDING' ? 'bg-amber-500/10 text-amber-500' : ($item->status == 'APPROVED' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500') }}">
                            {{ $item->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-400">{{ $item->created_at->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-20 text-center text-slate-500">No requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-2xl rounded-2xl flex flex-col max-h-[90vh] overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-white/5 flex justify-between items-center bg-slate-800/50">
            <h3 class="text-lg font-bold text-white">Form Permintaan Barang</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <div class="flex-1 overflow-y-auto p-8 modal-scroll bg-[#0f172a]/50">
            <form id="reqForm" action="{{ route('orders.requests.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Pilih Gudang*</label>
                        <select name="warehouse_id" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white" required>
                            @foreach($warehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Tipe Item</label>
                        <select name="type_id" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white">
                            <option value="">Semua Tipe</option>
                            @foreach($types as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pt-6 border-t border-white/5">
                    <h4 class="text-xs font-bold text-indigo-400 uppercase mb-4 tracking-widest italic">Daftar Item</h4>
                    <div id="itemList" class="space-y-4">
                        <div class="grid grid-cols-12 gap-3 item-row">
                            <div class="col-span-8">
                                <select name="items[0][id]" class="item-select w-full bg-[#1e293b] border border-white/10 rounded-lg py-2 px-3 focus:border-indigo-500 outline-none text-white text-xs">
                                    <option value="">-- Pilih Item --</option>
                                    @foreach($items as $it)
                                    <option value="{{ $it->id }}" data-type-id="{{ $it->type_id }}">{{ $it->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-3">
                                <input type="number" name="items[0][quantity]" placeholder="Qty" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2 px-3 focus:border-indigo-500 outline-none text-white text-xs" required>
                            </div>
                            <div class="col-span-1 flex items-center">
                                <button type="button" onclick="removeItem(this)" class="text-rose-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="addItem()" class="mt-4 text-xs font-bold text-indigo-400 flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Item Lain
                    </button>
                </div>

                <div class="pt-6 border-t border-white/5">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Catatan Tambahan</label>
                    <textarea name="note" rows="2" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none font-medium text-white"></textarea>
                </div>
            </form>
        </div>
        <div class="p-6 border-t border-white/5 bg-slate-800/50 flex justify-end gap-3">
            <button onclick="closeModal()" class="px-6 py-2 text-slate-400 font-bold">Batal</button>
            <button type="submit" form="reqForm" class="bg-indigo-600 text-white px-8 py-2 rounded-lg font-bold shadow-lg shadow-indigo-500/20">Kirim Permintaan</button>
        </div>
    </div>
</div>

<script>
    let itemIndex = 1;
    
    // Listen for type change to filter items
    document.querySelector('select[name="type_id"]').addEventListener('change', function() {
        filterAllItemDropdowns(this.value);
    });

    function filterAllItemDropdowns(typeId) {
        const selects = document.querySelectorAll('.item-select');
        selects.forEach(select => {
            const options = select.querySelectorAll('option');
            
            options.forEach(opt => {
                if (!opt.value) return; // Skip placeholder
                
                // If typeId is empty (Semua Tipe) or matches item type, show it
                if (!typeId || opt.dataset.typeId == typeId) {
                    opt.style.display = '';
                    opt.disabled = false;
                } else {
                    opt.style.display = 'none';
                    opt.disabled = true;
                    if (select.value == opt.value) {
                        select.value = ''; // Reset if current selected is now hidden
                    }
                }
            });
        });
    }

    function addItem() {
        const list = document.getElementById('itemList');
        const firstRow = document.querySelector('.item-row');
        const row = firstRow.cloneNode(true);
        
        const select = row.querySelector('select');
        select.name = `items[${itemIndex}][id]`;
        select.value = '';
        
        row.querySelector('input').name = `items[${itemIndex}][quantity]`;
        row.querySelector('input').value = '';
        
        list.appendChild(row);
        
        // Re-apply filter to the new dropdown
        const selectedType = document.querySelector('select[name="type_id"]').value;
        filterAllItemDropdowns(selectedType);
        
        itemIndex++;
        lucide.createIcons();
    }
    function removeItem(btn) {
        if (document.querySelectorAll('.item-row').length > 1) {
            btn.closest('.item-row').remove();
        }
    }
    function openModal() { document.getElementById('modal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }
</script>
@endsection
