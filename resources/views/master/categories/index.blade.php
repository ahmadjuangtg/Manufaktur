@extends('layouts.app', ['title' => 'Master Kategori'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h3 class="text-xl font-bold text-white">Kategori Produk</h3>
            <p class="text-slate-400 text-sm italic">Manage your product groupings</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <form action="{{ route('categories.index') }}" method="GET" class="relative flex-1 md:w-80">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari Nama atau Prefix..." class="w-full bg-slate-800/50 border border-white/10 rounded-lg py-2 pl-10 pr-4 text-white placeholder-slate-500 outline-none focus:border-indigo-500 transition-all text-sm">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
            </form>
            <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold flex items-center gap-2 transition-all shadow-lg shadow-indigo-500/20 whitespace-nowrap text-sm">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Kategori
            </button>
        </div>
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
                        <form id="delete-form-{{ $item->id }}" action="{{ route('categories.delete', $item->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="button" onclick="confirmAction('Yakin ingin menghapus kategori ini?', () => document.getElementById('delete-form-{{ $item->id }}').submit())" class="p-2 text-slate-500 hover:text-rose-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-8 py-12 text-center text-slate-500 italic">No categories found.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if($data->hasPages())
        <div class="px-8 py-4 bg-slate-800/30 border-t border-white/5">
            {{ $data->links() }}
        </div>
        @endif
    </div>
</div>

<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-md rounded-2xl shadow-2xl p-8">
        <div class="flex justify-between items-center mb-6">
            <h3 id="modalTitle" class="text-lg font-bold text-white">Tambah Kategori</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="categoryForm" action="{{ route('categories.store') }}" method="POST" class="space-y-4" onsubmit="return handleFormSubmit(this)">
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
                <button type="submit" id="submitBtn" class="bg-indigo-600 text-white px-8 py-2.5 rounded-lg font-bold shadow-lg shadow-indigo-500/20 flex items-center gap-2">
                    <span id="btnText">Simpan Kategori</span>
                    <div id="btnLoader" class="hidden w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function handleFormSubmit(form) {
        const btn = document.getElementById('submitBtn');
        const text = document.getElementById('btnText');
        const loader = document.getElementById('btnLoader');
        
        btn.disabled = true;
        btn.classList.add('opacity-70', 'cursor-not-allowed');
        text.innerText = 'Memproses...';
        loader.classList.remove('hidden');
        
        return true;
    }

    function openModal() { 
        document.getElementById('modalTitle').innerText = 'Tambah Kategori';
        document.getElementById('categoryForm').action = "{{ route('categories.store') }}";
        document.getElementById('categoryPrefix').value = '';
        document.getElementById('categoryName').value = '';
        
        // Reset button
        const btn = document.getElementById('submitBtn');
        const text = document.getElementById('btnText');
        const loader = document.getElementById('btnLoader');
        btn.disabled = false;
        btn.classList.remove('opacity-70', 'cursor-not-allowed');
        text.innerText = 'Simpan Kategori';
        loader.classList.add('hidden');

        document.getElementById('modal').classList.remove('hidden'); 
    }
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }

    function editCategory(data) {
        document.getElementById('modalTitle').innerText = 'Edit Kategori';
        document.getElementById('categoryForm').action = `/master/categories/update/${data.id}`;
        document.getElementById('categoryPrefix').value = data.prefix;
        document.getElementById('categoryName').value = data.name;
        
        // Reset button
        const btn = document.getElementById('submitBtn');
        const text = document.getElementById('btnText');
        const loader = document.getElementById('btnLoader');
        btn.disabled = false;
        btn.classList.remove('opacity-70', 'cursor-not-allowed');
        text.innerText = 'Simpan Kategori';
        loader.classList.add('hidden');

        document.getElementById('modal').classList.remove('hidden');
    }
</script>
@endsection
