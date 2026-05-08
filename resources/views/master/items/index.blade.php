@extends('layouts.app', ['title' => 'Master Item'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white">Master Item</h3>
            <p class="text-slate-400 text-sm italic">Manage your SKU inventory data</p>
        </div>
        <button onclick="openCreateModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all shadow-lg shadow-indigo-500/20">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> New Item
        </button>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="glass-card p-6 rounded-xl stat-card-glow flex items-center gap-4 border border-white/5">
            <div class="w-12 h-12 bg-indigo-500/10 rounded-lg flex items-center justify-center text-indigo-500"><i data-lucide="package" class="w-6 h-6"></i></div>
            <div><p class="text-slate-500 text-[12px] font-bold uppercase tracking-widest">Total SKUs</p><p class="text-xl font-black text-white">{{ $data->count() }}</p></div>
        </div>
        <div class="glass-card p-6 rounded-xl flex items-center gap-4 border border-white/5">
            <div class="w-12 h-12 bg-emerald-500/10 rounded-lg flex items-center justify-center text-emerald-500"><i data-lucide="check-circle" class="w-6 h-6"></i></div>
            <div><p class="text-slate-500 text-[12px] font-bold uppercase tracking-widest">Active Items</p><p class="text-xl font-black text-white">{{ $data->count() }}</p></div>
        </div>
        <div class="glass-card p-6 rounded-xl flex items-center gap-4 border border-white/5">
            <div class="w-12 h-12 bg-amber-500/10 rounded-lg flex items-center justify-center text-amber-500"><i data-lucide="alert-triangle" class="w-6 h-6"></i></div>
            <div><p class="text-slate-500 text-[12px] font-bold uppercase tracking-widest">Low Stock</p><p class="text-xl font-black text-white">0</p></div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="glass-card rounded-2xl overflow-hidden border border-white/5 bg-slate-900/20">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[12px] font-black uppercase tracking-widest border-b border-white/5">
                    <th class="px-8 py-5">Item Identifier</th>
                    <th class="px-8 py-5">Product Details</th>
                    <th class="px-8 py-5">Classification</th>
                    <th class="px-8 py-5">Specification</th>
                    <th class="px-8 py-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $item)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-8 py-5">
                        <div class="font-black text-indigo-400 text-sm tracking-tighter">{{ $item->code }}</div>
                        <div class="text-[12px] text-slate-500 font-mono mt-1">{{ $item->barcode }}</div>
                    </td>
                    <td class="px-8 py-5">
                        <div class="font-bold text-white text-sm">{{ $item->name }}</div>
                        <div class="text-[12px] text-slate-500 uppercase tracking-wider">{{ $item->manufacturer->name }}</div>
                    </td>
                    <td class="px-8 py-5">
                        <div class="flex flex-col gap-1">
                            <span class="text-[11px] font-black bg-slate-800 text-slate-400 px-2 py-0.5 rounded border border-white/5 uppercase w-fit">{{ $item->category->name }}</span>
                            <span class="text-[11px] font-black bg-indigo-500/10 text-indigo-400 px-2 py-0.5 rounded border border-indigo-500/10 uppercase w-fit">{{ $item->type->name }}</span>
                        </div>
                    </td>
                    <td class="px-8 py-5">
                        <div class="text-sm text-slate-300 font-bold">{{ $item->package_contain }} / {{ $item->unit->name }}</div>
                        <div class="text-[12px] text-slate-500 mt-1 italic">Dim: {{ $item->length }}x{{ $item->width }}x{{ $item->height }} cm</div>
                    </td>
                    <td class="px-8 py-5 text-right">
                        <div class="flex justify-end gap-2">
                            <button onclick='openEditModal(@json($item))' class="p-2 text-slate-500 hover:text-white hover:bg-slate-800 rounded-lg transition-all"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                            <form action="{{ route('items.delete', $item->id) }}" method="POST" onsubmit="return confirm('Hapus item ini?')">
                                @csrf
                                <button type="submit" class="p-2 text-slate-500 hover:text-rose-500 hover:bg-rose-500/10 rounded-lg transition-all"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-8 py-20 text-center text-slate-500 italic">Inventory catalog is empty.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-[#0f172a]/80 backdrop-blur-md p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-3xl rounded-[2.5rem] flex flex-col max-h-[90vh] overflow-hidden shadow-2xl">
        <div class="p-8 border-b border-white/5 flex justify-between items-center bg-slate-800/30">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white">
                    <i data-lucide="package" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 id="modalTitle" class="text-xl font-black text-white tracking-tight uppercase">Product SKU Entry</h3>
                    <p class="text-[12px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">Master Item Terminal</p>
                </div>
            </div>
            <button onclick="closeModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-all"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-10 modal-scroll bg-[#0f172a]/20">
            <form id="itemForm" action="{{ route('items.store') }}" method="POST" class="space-y-10">
                @csrf
                <input type="hidden" id="item_id" name="item_id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="md:col-span-2 space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Nama Item*</label>
                        <input type="text" id="name" name="name" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-bold text-lg shadow-inner transition-all" required>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Barcode Identity*</label>
                        <input type="text" id="barcode" name="barcode" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-mono tracking-widest" required>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Product Category*</label>
                        <select id="category_id" name="category_id" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-bold" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $c) <option value="{{ $c->id }}">{{ $c->name }}</option> @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Brand Manufacturer*</label>
                        <select id="manufacturer_id" name="manufacturer_id" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-bold" required>
                            <option value="">Select Manufacturer</option>
                            @foreach($manufacturers as $m) <option value="{{ $m->id }}">{{ $m->name }}</option> @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Product Type*</label>
                        <select id="type_id" name="type_id" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-bold" required>
                            <option value="">Select Type</option>
                            @foreach($types as $t) <option value="{{ $t->id }}">{{ $t->name }}</option> @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Measurement Unit*</label>
                        <select id="unit_id" name="unit_id" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-bold" required>
                            <option value="">Select Unit</option>
                            @foreach($units as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Package Contain*</label>
                        <input type="text" id="package_contain" name="package_contain" placeholder="Ex: 50 Pcs/Box" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-bold" required>
                    </div>

                    <div class="md:col-span-2 space-y-4">
                        <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Dimensions (Length x Width x Height in cm)</label>
                        <div class="grid grid-cols-3 gap-6">
                            <input type="number" step="0.01" id="length" name="length" placeholder="L" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-3 text-center text-white font-black focus:border-indigo-500 transition-all">
                            <input type="number" step="0.01" id="width" name="width" placeholder="W" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-3 text-center text-white font-black focus:border-indigo-500 transition-all">
                            <input type="number" step="0.01" id="height" name="height" placeholder="H" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-3 text-center text-white font-black focus:border-indigo-500 transition-all">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="p-10 border-t border-white/5 bg-slate-800/30 flex justify-end gap-6 rounded-b-[2.5rem]">
            <button onclick="closeModal()" class="text-sm font-black text-slate-500 uppercase tracking-widest hover:text-white transition-colors">Discard</button>
            <button type="submit" form="itemForm" class="bg-indigo-600 hover:bg-indigo-700 text-white px-12 py-4 rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-2xl shadow-indigo-500/40 active:scale-95 transition-all">
                Save Product
            </button>
        </div>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'New Product SKU';
        document.getElementById('itemForm').action = "{{ route('items.store') }}";
        document.getElementById('item_id').value = '';
        document.getElementById('itemForm').reset();
        document.getElementById('modal').classList.remove('hidden');
    }

    function openEditModal(item) {
        document.getElementById('modalTitle').innerText = 'Edit SKU: ' + item.code;
        document.getElementById('itemForm').action = "/master/items/update/" + item.id;
        document.getElementById('item_id').value = item.id;
        document.getElementById('name').value = item.name;
        document.getElementById('barcode').value = item.barcode;
        document.getElementById('category_id').value = item.category_id;
        document.getElementById('manufacturer_id').value = item.manufacturer_id;
        document.getElementById('type_id').value = item.type_id;
        document.getElementById('unit_id').value = item.unit_id;
        document.getElementById('package_contain').value = item.package_contain;
        document.getElementById('length').value = item.length;
        document.getElementById('width').value = item.width;
        document.getElementById('height').value = item.height;
        
        document.getElementById('modal').classList.remove('hidden');
    }

    function closeModal() { document.getElementById('modal').classList.add('hidden'); }
</script>
@endsection
