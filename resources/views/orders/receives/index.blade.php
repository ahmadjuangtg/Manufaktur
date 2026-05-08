@extends('layouts.app', ['title' => 'Receive Material'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white">Penerimaan Barang</h3>
            <p class="text-slate-400 text-sm">Verifikasi dan input stok barang masuk dari PO</p>
        </div>
    </div>

    <!-- Table Section -->
    <div class="glass-card rounded-xl overflow-hidden border border-white/5">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[10px] uppercase tracking-widest border-b border-white/5">
                    <th class="px-6 py-4">No. PO</th>
                    <th class="px-6 py-4">Supplier</th>
                    <th class="px-6 py-4">Progres Item</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $item)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-6 py-4 font-mono text-sm text-indigo-400">{{ $item->po_no }}</td>
                    <td class="px-6 py-4 text-white text-sm">{{ $item->supplier->name }}</td>
                    <td class="px-6 py-4">
                        <div class="space-y-2">
                            @foreach($item->details as $d)
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs text-slate-300">{{ $d->item->name }}</span>
                                <span class="text-[10px] font-bold text-indigo-400">{{ number_format($d->received_quantity) }} / {{ number_format($d->quantity) }}</span>
                            </div>
                            <div class="w-full bg-slate-800 h-1 rounded-full overflow-hidden">
                                <div class="bg-indigo-500 h-full" style="width: {{ ($d->received_quantity / $d->quantity) * 100 }}%"></div>
                            </div>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button onclick="openReceiveModal({{ $item->id }}, '{{ $item->po_no }}', {{ json_encode($item->details->map(fn($d) => ['id' => $d->item_id, 'name' => $d->item->name, 'pending' => $d->quantity - $d->received_quantity])) }})" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all">Input Terima</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-20 text-center text-slate-500">No open Purchase Orders to receive.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Receive Modal -->
<div id="receiveModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-lg rounded-2xl shadow-2xl">
        <div class="p-6 border-b border-white/5 bg-slate-800/50 rounded-t-2xl flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">Input Barang Masuk (<span id="poLabel"></span>)</h3>
            <button onclick="closeReceiveModal()" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <form id="receiveForm" method="POST" class="p-8 space-y-6">
            @csrf
            <div class="space-y-2 mb-6">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Simpan di Gudang*</label>
                <select name="warehouse_id" class="w-full bg-[#0f172a] border border-white/10 rounded-xl py-3 px-4 focus:border-indigo-500 outline-none text-white text-xs font-bold" required>
                    <option value="">-- Pilih Gudang --</option>
                    @foreach($warehouses as $w)
                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="receiveItemList" class="space-y-4">
                <!-- Dynamic Items from PO -->
            </div>
            
            <div id="additionalItems" class="pt-6 border-t border-white/5 space-y-4">
                <h4 class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest italic">Tambah Item Tambahan</h4>
                <div id="extraItemList" class="space-y-3"></div>
                <button type="button" onclick="addExtraItem()" class="text-[10px] font-bold text-indigo-400 flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-3 h-3"></i> Tambah Item
                </button>
            </div>
            <div class="flex justify-end gap-3 pt-6 border-t border-white/5">
                <button type="button" onclick="closeReceiveModal()" class="px-6 py-2 text-slate-400 font-bold">Batal</button>
                <button type="submit" class="bg-emerald-600 text-white px-8 py-2 rounded-lg font-bold">Simpan Penerimaan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReceiveModal(id, poNo, items) {
        document.getElementById('receiveForm').action = `/orders/receives/${id}`;
        document.getElementById('poLabel').innerText = poNo;
        const list = document.getElementById('receiveItemList');
        list.innerHTML = '';
        
        items.forEach(item => {
            if (item.pending <= 0) return;
            
            const div = document.createElement('div');
            div.className = 'grid grid-cols-12 gap-4 items-center';
            div.innerHTML = `
                <div class="col-span-8 text-xs text-white font-semibold">${item.name}</div>
                <div class="col-span-4">
                    <input type="number" name="items[${item.id}]" max="${item.pending}" min="0" placeholder="Maks ${item.pending}" class="w-full bg-[#0f172a] border border-white/10 rounded-lg py-2 px-3 focus:border-emerald-500 outline-none text-white text-xs">
                </div>
            `;
            list.appendChild(div);
        });
        
        document.getElementById('receiveModal').classList.remove('hidden');
    }
    function closeReceiveModal() {
        document.getElementById('receiveModal').classList.add('hidden');
    }

    let extraIndex = 0;
    const masterItems = {!! json_encode($items ?? []) !!};

    function addExtraItem() {
        const list = document.getElementById('extraItemList');
        const div = document.createElement('div');
        div.className = 'grid grid-cols-12 gap-3 items-center extra-row';
        
        let options = '<option value="">-- Pilih Item --</option>';
        masterItems.forEach(it => {
            options += `<option value="${it.id}">${it.name}</option>`;
        });

        div.innerHTML = `
            <div class="col-span-7">
                <select name="extra_items[${extraIndex}][id]" class="w-full bg-[#0f172a] border border-white/10 rounded-lg py-2 px-3 focus:border-indigo-500 outline-none text-white text-[10px]">
                    ${options}
                </select>
            </div>
            <div class="col-span-4">
                <input type="number" name="extra_items[${extraIndex}][quantity]" placeholder="Qty" class="w-full bg-[#0f172a] border border-white/10 rounded-lg py-2 px-3 focus:border-indigo-500 outline-none text-white text-[10px]">
            </div>
            <div class="col-span-1">
                <button type="button" onclick="this.closest('.extra-row').remove()" class="text-rose-500"><i data-lucide="trash-2" class="w-3 h-3"></i></button>
            </div>
        `;
        list.appendChild(div);
        extraIndex++;
        lucide.createIcons();
    }
</script>
@endsection
