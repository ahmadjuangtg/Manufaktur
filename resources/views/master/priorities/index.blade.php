@extends('layouts.app', ['title' => 'Master Prioritas'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white">Master Prioritas</h3>
            <p class="text-slate-400 text-sm italic">Define production priority levels</p>
        </div>
        <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Prioritas
        </button>
    </div>

    <!-- Table Section -->
    <div class="glass-card rounded-xl overflow-hidden border border-white/5">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[12px] uppercase tracking-widest border-b border-white/5">
                    <th class="px-8 py-4">Kode</th>
                    <th class="px-8 py-4">Level</th>
                    <th class="px-8 py-4">Nama Prioritas</th>
                    <th class="px-8 py-4">Warna</th>
                    <th class="px-8 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $item)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-8 py-4 font-mono text-sm font-bold text-indigo-400">{{ $item->code }}</td>
                    <td class="px-8 py-4">
                        <span class="px-2 py-1 rounded bg-slate-800 text-white text-sm font-bold">Level {{ $item->level }}</span>
                    </td>
                    <td class="px-8 py-4 font-bold text-white">{{ $item->name }}</td>
                    <td class="px-8 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded" style="background-color: {{ $item->color }}"></div>
                            <span class="text-sm text-slate-400">{{ $item->color }}</span>
                        </div>
                    </td>
                    <td class="px-8 py-4 text-right flex justify-end gap-2">
                        <button onclick="editPriority({{ $item->toJson() }})" class="p-2 text-slate-500 hover:text-indigo-400"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                        <form action="{{ route('priorities.delete', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus prioritas ini?')">
                            @csrf
                            <button type="submit" class="p-2 text-slate-500 hover:text-rose-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-8 py-12 text-center text-slate-500 italic">No priorities registered.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-md rounded-2xl shadow-2xl p-8">
        <div class="flex justify-between items-center mb-6">
            <h3 id="modalTitle" class="text-lg font-bold text-white">Tambah Prioritas</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="priorityForm" action="{{ route('priorities.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Level (1-5)*</label>
                <select name="level" id="priorityLevel" class="w-full bg-[#0f172a] border border-white/10 rounded-lg py-3 px-4 focus:border-indigo-500 outline-none text-white font-semibold" required>
                    <option value="1">Level 1 (Highest)</option>
                    <option value="2">Level 2</option>
                    <option value="3">Level 3</option>
                    <option value="4">Level 4</option>
                    <option value="5">Level 5 (Lowest)</option>
                </select>
            </div>
            <div>
                <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Nama Prioritas*</label>
                <input type="text" name="name" id="priorityName" placeholder="Urgent / Normal" class="w-full bg-[#0f172a] border border-white/10 rounded-lg py-3 px-4 focus:border-indigo-500 outline-none text-white font-semibold" required>
            </div>
            <div>
                <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Warna Identifikasi</label>
                <input type="color" name="color" id="priorityColor" value="#6366f1" class="w-full h-12 bg-[#0f172a] border border-white/10 rounded-lg p-1 outline-none">
            </div>
            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="closeModal()" class="px-6 py-2 text-slate-400 font-bold">Batal</button>
                <button type="submit" class="bg-indigo-600 text-white px-8 py-2.5 rounded-lg font-bold shadow-lg shadow-indigo-500/20">Simpan Prioritas</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal() { 
        document.getElementById('modalTitle').innerText = 'Tambah Prioritas';
        document.getElementById('priorityForm').action = "{{ route('priorities.store') }}";
        document.getElementById('priorityLevel').value = '3';
        document.getElementById('priorityName').value = '';
        document.getElementById('priorityColor').value = '#6366f1';
        document.getElementById('modal').classList.remove('hidden'); 
    }
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }

    function editPriority(data) {
        document.getElementById('modalTitle').innerText = 'Edit Prioritas';
        document.getElementById('priorityForm').action = `/master/priorities/update/${data.id}`;
        document.getElementById('priorityLevel').value = data.level;
        document.getElementById('priorityName').value = data.name;
        document.getElementById('priorityColor').value = data.color;
        document.getElementById('modal').classList.remove('hidden');
    }
</script>
@endsection
