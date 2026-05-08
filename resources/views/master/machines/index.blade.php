@extends('layouts.app', ['title' => 'Master Mesin'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white">Master Machine</h3>
            <p class="text-slate-400 text-sm italic">Operations equipment and production line data</p>
        </div>
        <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Machine
        </button>
    </div>

    <!-- Table Section -->
    <div class="glass-card rounded-xl overflow-hidden border border-white/5">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[12px] uppercase tracking-widest border-b border-white/5">
                    <th class="px-6 py-4">Machine Code</th>
                    <th class="px-6 py-4">Machine Name</th>
                    <th class="px-6 py-4">Category</th>
                    <th class="px-6 py-4">Capacity</th>
                    <th class="px-6 py-4">Outlet</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $item)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-6 py-4 font-mono text-sm text-indigo-400 font-bold">{{ $item->code }}</td>
                    <td class="px-6 py-4 font-bold text-white">{{ $item->name }}</td>
                    <td class="px-6 py-4"><span class="bg-slate-800 text-slate-400 px-2 py-0.5 rounded text-[12px]">{{ $item->category->name }}</span></td>
                    <td class="px-6 py-4 text-sm text-slate-300">{{ number_format($item->capacity, 0) }} / {{ $item->capacity_unit }}</td>
                    <td class="px-6 py-4 text-sm text-slate-400">{{ $item->outlet ?? '-' }}</td>
                    <td class="px-6 py-4">
                        @if($item->is_active)
                        <span class="text-[12px] font-bold text-emerald-500 bg-emerald-500/10 px-2 py-1 rounded">ACTIVE</span>
                        @else
                        <span class="text-[12px] font-bold text-rose-500 bg-rose-500/10 px-2 py-1 rounded">INACTIVE</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button onclick="editMachine({{ $item->toJson() }})" class="p-2 text-slate-500 hover:text-indigo-400"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                        <form action="{{ route('machines.delete', $item->id) }}" method="POST" class="inline">
                            @csrf
                            <button class="p-2 text-slate-500 hover:text-rose-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-slate-500 italic">No machine data available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-2xl rounded-2xl flex flex-col max-h-[95vh] overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-white/5 flex justify-between items-center bg-slate-800/50">
            <h3 id="modalTitle" class="text-lg font-bold text-white">Tambah Machine</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <div class="flex-1 overflow-y-auto p-10 modal-scroll bg-[#0f172a]/50">
            <form id="machineForm" action="{{ route('machines.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[12px] font-bold text-slate-500 uppercase mb-1">Machine Code</label>
                        <input type="text" id="disp_code" value="AUTO-GENERATED" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 text-indigo-400 font-bold" readonly>
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold text-slate-500 uppercase mb-1">Machine Name*</label>
                        <input type="text" name="name" id="name" placeholder="Enter machine name" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white font-medium" required>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-[12px] font-bold text-slate-500 uppercase mb-1">Machine Category*</label>
                        <select name="machine_category_id" id="machine_category_id" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-3 px-4 focus:border-indigo-500 outline-none text-white" required>
                            <option value="">Enter machine category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[12px] font-bold text-slate-500 uppercase mb-1">Outlet</label>
                        <select name="outlet" id="outlet" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-3 px-4 focus:border-indigo-500 outline-none text-white">
                            <option value="">Select outlet</option>
                            @foreach($warehouses as $w)
                            <option value="{{ $w->name }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[12px] font-bold text-slate-500 uppercase mb-1">Machine Capacity*</label>
                            <input type="number" name="capacity" id="capacity" value="0" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white font-medium" required>
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-slate-500 uppercase mb-1">Satuan* (Output)</label>
                            <select name="output_unit" id="output_unit" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-3 px-4 focus:border-indigo-500 outline-none text-white" required>
                                <option value="">Pilih Satuan</option>
                                @foreach($units as $unit)
                                <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-slate-500 uppercase mb-1">Interval Waktu*</label>
                            <select name="capacity_unit" id="capacity_unit" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-3 px-4 focus:border-indigo-500 outline-none text-white" required>
                                <option value="perjam">Per Jam</option>
                                <option value="perhari">Per Hari</option>
                                <option value="perminggu">Per Minggu</option>
                                <option value="perbulan">Per Bulan</option>
                                <option value="pertahun">Per Tahun</option>
                            </select>
                        </div>
                    </div>

                    <div class="md:col-span-2 flex items-center gap-4 py-2 px-4 bg-slate-900/30 rounded-xl border border-white/5">
                        <label class="text-[12px] font-bold text-slate-500 uppercase tracking-widest flex-1">Active Status</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" id="is_active" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
            </form>
        </div>
        <div class="p-8 border-t border-white/5 bg-slate-800/50 flex justify-end gap-3 shrink-0">
            <button onclick="closeModal()" class="px-6 py-2.5 text-slate-400 font-bold hover:text-white transition-colors">Batal</button>
            <button type="submit" form="machineForm" class="bg-indigo-600 hover:bg-indigo-700 text-white px-10 py-2.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition-all active:scale-95">Simpan Machine</button>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('modalTitle').innerText = 'Tambah Machine';
        document.getElementById('machineForm').action = "{{ route('machines.store') }}";
        document.getElementById('machineForm').reset();
        document.getElementById('disp_code').value = 'AUTO-GENERATED';
        document.getElementById('modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('modal').classList.add('hidden');
    }

    function editMachine(data) {
        document.getElementById('modalTitle').innerText = 'Edit Machine';
        document.getElementById('machineForm').action = `/master/machines/update/${data.id}`;
        
        document.getElementById('disp_code').value = data.code;
        document.getElementById('name').value = data.name;
        document.getElementById('machine_category_id').value = data.machine_category_id;
        document.getElementById('outlet').value = data.outlet || '';
        document.getElementById('capacity').value = data.capacity;
        document.getElementById('capacity_unit').value = data.capacity_unit;
        document.getElementById('output_unit').value = data.output_unit || 'pcs';
        
        document.getElementById('is_active').checked = data.is_active == 1;

        document.getElementById('modal').classList.remove('hidden');
    }
</script>
@endsection
