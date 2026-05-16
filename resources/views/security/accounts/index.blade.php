@extends('layouts.app', ['title' => 'Master Accounts'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white">User Accounts</h3>
            <p class="text-slate-400 text-sm italic">Manage system users and their warehouse access</p>
        </div>
        <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Tambah User
        </button>
    </div>

    <!-- Table Section -->
    <div class="glass-card rounded-xl overflow-hidden border border-white/5">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[10px] uppercase tracking-widest border-b border-white/5">
                    <th class="px-6 py-4">User Information</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4">Assigned Warehouses</th>
                    <th class="px-6 py-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $item)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center font-black text-xs text-indigo-400">{{ strtoupper(substr($item->name, 0, 1)) }}</div>
                            <div>
                                <div class="font-bold text-white text-sm">{{ $item->name }}</div>
                                <div class="text-[10px] text-slate-500">{{ $item->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-[10px] font-bold text-indigo-400 bg-indigo-500/10 px-2 py-1 rounded border border-indigo-500/20">{{ $item->role->name ?? 'NO ROLE' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @forelse($item->warehouses as $wh)
                            <span class="text-[9px] font-bold bg-slate-800 text-slate-400 px-2 py-0.5 rounded border border-white/5">{{ $wh->name }}</span>
                            @empty
                            <span class="text-[9px] text-slate-600 italic">No warehouses assigned</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if(($item->role->name ?? '') !== 'Super Administrator' && $item->id !== auth()->id())
                        <div class="flex justify-end gap-2">
                            @php
                                $userData = [
                                    "id" => $item->id,
                                    "name" => $item->name,
                                    "email" => $item->email,
                                    "role_id" => $item->role_id,
                                    "warehouses" => $item->warehouses->pluck("id")->toArray()
                                ];
                            @endphp
                            <button onclick='openEditModal(@json($userData))' class="p-2 text-slate-500 hover:text-indigo-400 bg-indigo-500/0 hover:bg-indigo-500/10 rounded-lg transition-all"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                            <form action="{{ route('accounts.delete', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                @csrf
                                <button type="submit" class="p-2 text-slate-500 hover:text-rose-500 bg-rose-500/0 hover:bg-rose-500/10 rounded-lg transition-all"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                        @else
                        <span class="text-[9px] font-bold text-slate-600 bg-slate-800/50 px-2 py-1 rounded">SYSTEM LOCKED</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-20 text-center text-slate-500">No user accounts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-2xl rounded-2xl flex flex-col max-h-[90vh] overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-white/5 flex justify-between items-center bg-slate-800/50">
            <h3 class="text-lg font-bold text-white">Register New Account</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <div class="flex-1 overflow-y-auto p-10 modal-scroll bg-[#0f172a]/50">
            <form id="userForm" action="{{ route('accounts.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Full Name*</label>
                        <input type="text" name="name" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white font-medium" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Email Address*</label>
                        <input type="email" name="email" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Password</label>
                        <input type="password" name="password" id="user_password" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white" placeholder="Kosongkan jika tidak ingin mengubah password">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Assigned Role*</label>
                        <select name="role_id" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-3 px-4 focus:border-indigo-500 outline-none text-white" required>
                            <option value="">Select a role</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-3">Accessible Warehouses</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($warehouses as $wh)
                            <label class="flex items-center gap-3 p-3 rounded-xl bg-[#1e293b] border border-white/5 cursor-pointer hover:border-indigo-500 transition-all">
                                <input type="checkbox" name="warehouse_ids[]" value="{{ $wh->id }}" id="wh_{{ $wh->id }}" class="warehouse-checkbox w-4 h-4 rounded border-white/10 bg-slate-800 text-indigo-600">
                                <span class="text-xs text-slate-300 font-bold">{{ $wh->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="p-8 border-t border-white/5 bg-slate-800/50 flex justify-end gap-3 shrink-0">
            <button onclick="closeModal()" class="px-6 py-2 text-slate-500 font-bold">Batal</button>
            <button type="submit" form="userForm" id="btnSubmit" class="bg-indigo-600 text-white px-10 py-3 rounded-xl font-bold shadow-xl">Create Account</button>
        </div>
    </div>
</div>

<script>
    function openModal() { 
        document.querySelector('#modal h3').innerText = 'Register New Account';
        document.getElementById('userForm').action = "{{ route('accounts.store') }}";
        document.querySelector('input[name="name"]').value = '';
        document.querySelector('input[name="email"]').value = '';
        document.querySelector('input[name="password"]').required = true;
        document.querySelector('input[name="password"]').placeholder = 'Input password';
        document.querySelector('select[name="role_id"]').value = '';
        document.querySelectorAll('.warehouse-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('btnSubmit').innerText = 'Create Account';
        document.getElementById('modal').classList.remove('hidden'); 
    }

    function openEditModal(user) {
        document.querySelector('#modal h3').innerText = 'Edit Account: ' + user.name;
        document.getElementById('userForm').action = "/security/accounts/update/" + user.id;
        document.querySelector('input[name="name"]').value = user.name;
        document.querySelector('input[name="email"]').value = user.email;
        document.querySelector('input[name="password"]').required = false;
        document.querySelector('input[name="password"]').placeholder = 'Kosongkan jika tidak diubah';
        document.querySelector('select[name="role_id"]').value = user.role_id;
        
        document.querySelectorAll('.warehouse-checkbox').forEach(cb => {
            cb.checked = user.warehouses.includes(parseInt(cb.value));
        });

        document.getElementById('btnSubmit').innerText = 'Save Changes';
        document.getElementById('modal').classList.remove('hidden'); 
    }

    function closeModal() { document.getElementById('modal').classList.add('hidden'); }
</script>
@endsection
