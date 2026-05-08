@extends('layouts.app', ['title' => 'Master Role & Permissions'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white">Role Access Control</h3>
            <p class="text-slate-400 text-sm italic">Define granular permissions for each user level</p>
        </div>
        <button onclick="openCreateModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all">
            <i data-lucide="shield-plus" class="w-4 h-4"></i> Tambah Role Baru
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($data as $role)
        <div class="glass-card p-6 rounded-2xl border border-white/5 relative group transition-all hover:border-indigo-500/30">
            <div class="flex justify-between items-start mb-6">
                <div class="p-4 bg-indigo-500/10 rounded-2xl text-indigo-400 shadow-inner">
                    <i data-lucide="shield" class="w-6 h-6"></i>
                </div>
                <div class="flex gap-2">
                    @if($role->name !== 'Super Administrator')
                    <button onclick='openEditModal(@json($role))' class="p-2 bg-slate-800 rounded-lg text-slate-400 hover:text-white transition-colors"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                    <form action="{{ route('roles.delete', $role->id) }}" method="POST" onsubmit="return confirm('Hapus role ini?')">
                        @csrf
                        <button type="submit" class="p-2 bg-slate-800 rounded-lg text-slate-400 hover:text-rose-500 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </form>
                    @else
                    <span class="text-[9px] font-black text-indigo-400/50 bg-indigo-500/5 px-3 py-1 rounded-full border border-indigo-500/10 tracking-widest">SYSTEM CORE</span>
                    @endif
                </div>
            </div>
            
            <h4 class="text-xl font-black text-white tracking-tight">{{ $role->name }}</h4>
            <div class="flex items-center gap-2 mt-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.2em]">{{ count($role->permissions ?? []) }} Actions Authorized</p>
            </div>

            <div class="mt-8 pt-6 border-t border-white/5">
                <div class="flex flex-wrap gap-2">
                    @forelse(array_slice($role->permissions ?? [], 0, 6) as $perm)
                    <span class="text-[9px] font-black bg-slate-800/80 text-slate-400 px-2.5 py-1 rounded-lg border border-white/5 uppercase tracking-wider">{{ str_replace('_', ' ', $perm) }}</span>
                    @empty
                    <span class="text-[9px] font-bold text-slate-600 italic">No special permissions</span>
                    @endforelse
                    @if(count($role->permissions ?? []) > 6)
                    <span class="text-[9px] font-black bg-indigo-500/10 text-indigo-400 px-2.5 py-1 rounded-lg border border-indigo-500/20 uppercase">+{{ count($role->permissions) - 6 }} More</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Modal -->
<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-5xl rounded-[2.5rem] flex flex-col max-h-[92vh] overflow-hidden shadow-2xl">
        <div class="p-8 border-b border-white/5 flex justify-between items-center bg-slate-800/30">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-600/20 rounded-2xl flex items-center justify-center text-indigo-400">
                    <i data-lucide="lock" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 id="modalTitle" class="text-xl font-black text-white tracking-tight">Configure Role Permissions</h3>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-0.5">Role Management Terminal</p>
                </div>
            </div>
            <button onclick="closeModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-all"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-10 modal-scroll bg-[#0f172a]/30">
            <form id="roleForm" action="{{ route('roles.store') }}" method="POST" class="space-y-10">
                @csrf
                <input type="hidden" id="role_id" name="role_id">
                <div class="max-w-md">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] mb-3 ml-1">Identity Name*</label>
                    <input type="text" id="role_name" name="name" placeholder="e.g. Warehouse Manager" class="w-full bg-slate-900/50 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-bold text-lg shadow-inner transition-all" required>
                </div>

                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-6 bg-indigo-500 rounded-full"></div>
                        <h4 class="text-xs font-black text-white uppercase tracking-[0.3em]">Permission Matrix</h4>
                    </div>
                    
                    <div class="glass-card rounded-[2rem] overflow-hidden border border-white/5">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-800/80 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                                    <th class="px-8 py-5 border-b border-white/5">Module System</th>
                                    <th class="px-6 py-5 border-b border-white/5 text-center">Akses Modul</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 bg-slate-900/10">
                                @php
                                    $modules = [
                                        ['id' => 'dashboard', 'label' => 'Dashboard System'],
                                        
                                        // Master Data
                                        ['id' => 'master_item', 'label' => 'Master Item'],
                                        ['id' => 'master_category', 'label' => 'Master Kategori'],
                                        ['id' => 'master_type', 'label' => 'Master Tipe Item'],
                                        ['id' => 'master_manufacturer', 'label' => 'Master Manufaktur'],
                                        ['id' => 'master_unit', 'label' => 'Master Satuan'],
                                        ['id' => 'master_customer', 'label' => 'Master Customer'],
                                        ['id' => 'master_machine_category', 'label' => 'Master Kategori Mesin'],
                                        ['id' => 'master_machine', 'label' => 'Master Mesin'],
                                        ['id' => 'master_warehouse', 'label' => 'Master Gudang'],
                                        ['id' => 'master_supplier', 'label' => 'Master Supplier'],
                                        ['id' => 'master_priority', 'label' => 'Master Prioritas'],
                                        ['id' => 'master_substitution', 'label' => 'Master Substitusi & Capability'],
                                        ['id' => 'master_price_list', 'label' => 'Master Price List'],

                                        // Production
                                        ['id' => 'production_template', 'label' => 'Production Template'],
                                        ['id' => 'production_wo', 'label' => 'Work Orders'],
                                        ['id' => 'production_scheduling', 'label' => 'Scheduling Production'],

                                        // Transactional
                                        ['id' => 'inventory', 'label' => 'Inventory Terminal'],
                                        ['id' => 'stock_mutation', 'label' => 'Request Mutasi Gudang'],
                                        ['id' => 'stock_mutation_approval', 'label' => 'Approval Mutasi Gudang'],
                                        ['id' => 'stock_opname', 'label' => 'Stock Opname'],
                                        ['id' => 'stock_opname_approval', 'label' => 'Approval Stock Opname'],
                                        ['id' => 'stock_card', 'label' => 'Kartu Stock'],

                                        // Purchasing
                                        ['id' => 'order_request', 'label' => 'Purchase Request'],
                                        ['id' => 'order_approval', 'label' => 'Approval Request PO'],
                                        ['id' => 'order_po', 'label' => 'Purchase Orders'],
                                        ['id' => 'order_receive', 'label' => 'Incoming Material'],

                                        // Security
                                        ['id' => 'security_role', 'label' => 'Security Roles'],
                                        ['id' => 'security_account', 'label' => 'Account Management'],
                                    ];
                                @endphp
                                @foreach($modules as $module)
                                <tr class="hover:bg-indigo-500/5 transition-colors group">
                                    <td class="px-8 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-2 h-2 rounded-full bg-slate-700 group-hover:bg-indigo-500 transition-colors"></div>
                                            <span class="text-sm font-bold text-slate-300 group-hover:text-white transition-colors">{{ $module['label'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <label class="inline-flex items-center justify-center p-2 rounded-xl hover:bg-slate-800 cursor-pointer transition-all">
                                            <input type="checkbox" name="permissions[]" value="{{ $module['id'] }}_view" class="perm-checkbox w-6 h-6 rounded-lg border-white/10 bg-slate-800 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900">
                                        </label>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="p-10 border-t border-white/5 bg-slate-800/30 flex justify-end gap-6 shrink-0">
            <button onclick="closeModal()" class="text-xs font-black text-slate-500 uppercase tracking-widest hover:text-white transition-colors">Batalkan Perubahan</button>
            <button type="submit" form="roleForm" class="bg-indigo-600 hover:bg-indigo-700 text-white px-12 py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-2xl shadow-indigo-500/20 active:scale-[0.98] transition-all">
                Simpan Konfigurasi
            </button>
        </div>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Create New Role';
        document.getElementById('roleForm').action = "{{ route('roles.store') }}";
        document.getElementById('role_id').value = '';
        document.getElementById('role_name').value = '';
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('modal').classList.remove('hidden');
    }

    function openEditModal(role) {
        document.getElementById('modalTitle').innerText = 'Edit Role: ' + role.name;
        document.getElementById('roleForm').action = "/security/roles/update/" + role.id;
        document.getElementById('role_id').value = role.id;
        document.getElementById('role_name').value = role.name;
        
        document.querySelectorAll('.perm-checkbox').forEach(cb => {
            cb.checked = role.permissions && role.permissions.includes(cb.value);
        });
        
        document.getElementById('modal').classList.remove('hidden');
    }

    function closeModal() { document.getElementById('modal').classList.add('hidden'); }
</script>
@endsection
