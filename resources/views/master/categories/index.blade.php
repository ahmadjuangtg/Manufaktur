@extends('layouts.app', ['title' => 'Master Kategori'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white">Kategori Produk</h3>
            <p class="text-slate-400 text-sm italic">Manage your product groupings</p>
        </div>
        <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all shadow-lg shadow-indigo-500/20">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Kategori
        </button>
    </div>

    <!-- Table Section -->
    <div class="glass-card rounded-xl overflow-hidden border border-white/5">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[12px] uppercase tracking-widest border-b border-white/5">
                    <th class="px-8 py-4">ID Code</th>
                    <th class="px-8 py-4">Prefix</th>
                    <th class="px-8 py-4">Category Name</th>
                    <th class="px-8 py-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $item)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-8 py-4 font-mono text-sm font-bold text-indigo-400">{{ $item->code }}</td>
                    <td class="px-8 py-4 font-bold text-slate-300">{{ $item->prefix }}</td>
                    <td class="px-8 py-4 font-bold text-white">{{ $item->name }}</td>
                    <td class="px-8 py-4 text-right flex justify-end gap-2">
                        <button onclick="editCategory({{ $item->toJson() }})" class="p-2 text-slate-500 hover:text-indigo-400"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                        <form action="{{ route('categories.delete', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                            @csrf
                            <button type="submit" class="p-2 text-slate-500 hover:text-rose-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-8 py-12 text-center text-slate-500 italic">No categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-md rounded-2xl shadow-2xl p-8">
        <div class="flex justify-between items-center mb-6">
            <h3 id="modalTitle" class="text-lg font-bold text-white">Tambah Kategori</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="categoryForm" action="{{ route('categories.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Prefix Kode*</label>
                <input type="text" name="prefix" id="categoryPrefix" placeholder="ELK" class="w-full bg-[#0f172a] border border-white/10 rounded-lg py-3 px-4 focus:border-indigo-500 outline-none text-white font-semibold" required>
            </div>
            <div>
                <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Nama Kategori*</label>
                <input type="text" name="name" id="categoryName" placeholder="Elektronik" class="w-full bg-[#0f172a] border border-white/10 rounded-lg py-3 px-4 focus:border-indigo-500 outline-none text-white font-semibold" required>
            </div>
            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="closeModal()" class="px-6 py-2 text-slate-400 font-bold">Batal</button>
                <button type="submit" class="bg-indigo-600 text-white px-8 py-2.5 rounded-lg font-bold shadow-lg shadow-indigo-500/20">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal() { 
        document.getElementById('modalTitle').innerText = 'Tambah Kategori';
        document.getElementById('categoryForm').action = "{{ route('categories.store') }}";
        document.getElementById('categoryPrefix').value = '';
        document.getElementById('categoryName').value = '';
        document.getElementById('modal').classList.remove('hidden'); 
    }
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }

    function editCategory(data) {
        document.getElementById('modalTitle').innerText = 'Edit Kategori';
        document.getElementById('categoryForm').action = `/master/categories/update/${data.id}`;
        document.getElementById('categoryPrefix').value = data.prefix;
        document.getElementById('categoryName').value = data.name;
        document.getElementById('modal').classList.remove('hidden');
    }
</script>
@endsection
