@extends('layouts.app', ['title' => 'Master Gudang'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white uppercase tracking-tight">Master Warehouse</h3>
            <p class="text-slate-400 text-sm italic">Manage storage facilities and regional operations</p>
        </div>
        <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all shadow-lg shadow-indigo-500/20">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Gudang
        </button>
    </div>

    <!-- Table Section -->
    <div class="glass-card rounded-2xl overflow-hidden border border-white/5 bg-slate-900/20">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[12px] font-black uppercase tracking-widest border-b border-white/5">
                    <th class="px-8 py-4">Nama Gudang</th>
                    <th class="px-8 py-4">Lokasi</th>
                    <th class="px-8 py-4 text-center">Server State</th>
                    <th class="px-8 py-4 text-center">Operational</th>
                    <th class="px-8 py-4 text-right">Status</th>
                    <th class="px-8 py-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $item)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-8 py-4">
                        <div class="font-bold text-white text-sm">{{ $item->name }}</div>
                        <div class="text-[12px] text-slate-500 font-black uppercase mt-0.5">{{ $item->warehouse_type }}</div>
                    </td>
                    <td class="px-8 py-4">
                        <div class="text-sm text-indigo-400 font-bold uppercase">{{ $item->city }}</div>
                        <div class="text-[12px] text-slate-500">{{ $item->province }}</div>
                    </td>
                    <td class="px-8 py-4 text-center">
                        <span class="bg-slate-800 text-slate-400 px-3 py-1 rounded-full text-[12px] font-black border border-white/5">{{ $item->server_state }}</span>
                    </td>
                    <td class="px-8 py-4 text-center text-[12px] font-bold text-slate-500 uppercase tracking-tighter">
                        {{ $item->is_24_hours ? '24 HOURS' : ($item->operational_hours ?: '-') }}
                    </td>
                    <td class="px-8 py-4 text-right">
                        <span class="text-[11px] font-black {{ $item->is_active ? 'text-emerald-500 bg-emerald-500/10' : 'text-rose-500 bg-rose-500/10' }} px-2.5 py-1.5 rounded-lg border {{ $item->is_active ? 'border-emerald-500/20' : 'border-rose-500/20' }} uppercase tracking-tighter">
                            {{ $item->is_active ? 'ACTIVE' : 'INACTIVE' }}
                        </span>
                    </td>
                    <td class="px-8 py-4 text-right">
                        <button onclick="editWarehouse({{ $item->toJson() }})" class="p-2 text-slate-500 hover:text-indigo-400 transition-colors">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-8 py-20 text-center text-slate-500 italic">Belum ada data gudang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-[#0f172a]/95 backdrop-blur-xl p-4 md:p-10">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-5xl rounded-[2.5rem] flex flex-col max-h-full overflow-hidden shadow-2xl">
        <div class="p-8 border-b border-white/5 flex justify-between items-center bg-slate-800/30">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/40">
                    <i data-lucide="warehouse" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 id="modalTitle" class="text-xl font-black text-white tracking-tight uppercase">Tambah Gudang</h3>
                    <p class="text-[12px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">Warehouse Registry Terminal</p>
                </div>
            </div>
            <button onclick="closeModal()" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-all"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>

        <div class="flex-1 overflow-y-auto p-10 modal-scroll bg-slate-900/10">
            <form id="warehouseForm" method="POST" class="space-y-10">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
                    <!-- Left Column -->
                    <div class="space-y-10">
                        <div class="space-y-2">
                            <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Nama Gudang*</label>
                            <input type="text" name="name" id="name" placeholder="Gudang Aori Jakarta" class="w-full bg-[#edf2f7] border-none rounded-2xl py-4 px-6 text-slate-800 font-black outline-none focus:ring-2 focus:ring-indigo-500 transition-all shadow-inner" required>
                        </div>

                        <div class="space-y-4">
                            <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Waktu Operasional</label>
                            <div class="flex items-center gap-4 bg-slate-800/30 p-4 rounded-2xl border border-white/5 group hover:border-indigo-500/30 transition-all">
                                <input type="checkbox" name="is_24_hours" id="is_24_hours" class="w-6 h-6 rounded-lg border-white/10 bg-slate-900 text-indigo-600 focus:ring-indigo-500" onchange="toggleHours(this)">
                                <label for="is_24_hours" class="text-sm font-black text-white uppercase tracking-widest cursor-pointer select-none">24 Jam</label>
                            </div>
                            
                            <div id="hours_container" class="flex items-center gap-3 mt-4">
                                <input type="time" name="op_start" id="op_start" class="flex-1 bg-transparent border-b-2 border-slate-700 text-white font-black py-2 outline-none focus:border-indigo-500 transition-all disabled:opacity-20">
                                <span class="text-[12px] font-black text-slate-500 uppercase">s/d</span>
                                <input type="time" name="op_end" id="op_end" class="flex-1 bg-transparent border-b-2 border-slate-700 text-white font-black py-2 outline-none focus:border-indigo-500 transition-all disabled:opacity-20">
                            </div>
                            <input type="hidden" name="operational_hours" id="operational_hours">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Server States*</label>
                            <select name="server_state" id="server_state" class="w-full bg-[#edf2f7] border-none rounded-2xl py-4 px-6 text-slate-800 font-black outline-none focus:ring-2 focus:ring-indigo-500 shadow-inner" required>
                                <option value="">Pilih Server States</option>
                                <option value="WIB">WIB (Waktu Indonesia Barat)</option>
                                <option value="WITA">WITA (Waktu Indonesia Tengah)</option>
                                <option value="WIT">WIT (Waktu Indonesia Timur)</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Alamat</label>
                                <input type="text" name="address" id="address" placeholder="Jl. Inovasi No. 7" class="w-full bg-[#edf2f7] border-none rounded-2xl py-4 px-6 text-slate-800 font-black outline-none focus:ring-2 focus:ring-indigo-500 shadow-inner" required>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Kode Pos</label>
                                <input type="text" name="postal_code" id="postal_code" placeholder="15151" class="w-full bg-[#edf2f7] border-none rounded-2xl py-4 px-6 text-slate-800 font-black outline-none focus:ring-2 focus:ring-indigo-500 shadow-inner">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <select name="province" id="province" class="w-full bg-slate-800 border border-white/10 rounded-xl py-3 px-4 text-white text-sm font-bold outline-none focus:border-indigo-500" onchange="loadCities(this.value)">
                                <option value="">Pilih Provinsi</option>
                            </select>
                            <select name="city" id="city" class="w-full bg-slate-800 border border-white/10 rounded-xl py-3 px-4 text-white text-sm font-bold outline-none focus:border-indigo-500" disabled onchange="loadDistricts(this.value)">
                                <option value="">Pilih Kota</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <select name="district" id="district" class="w-full bg-slate-800 border border-white/10 rounded-xl py-3 px-4 text-white text-sm font-bold outline-none focus:border-indigo-500" disabled onchange="loadVillages(this.value)">
                                <option value="">Pilih Kecamatan</option>
                            </select>
                            <select name="village" id="village" class="w-full bg-slate-800 border border-white/10 rounded-xl py-3 px-4 text-white text-sm font-bold outline-none focus:border-indigo-500" disabled>
                                <option value="">Pilih Kelurahan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-10">
                        <div class="space-y-2">
                            <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Region*</label>
                            <select name="region" id="region" class="w-full bg-[#edf2f7] border-none rounded-2xl py-4 px-6 text-slate-800 font-black outline-none focus:ring-2 focus:ring-indigo-500 shadow-inner" required>
                                <option value="">Pilih Region</option>
                                <option value="WEST">WEST REGION</option>
                                <option value="CENTRAL">CENTRAL REGION</option>
                                <option value="EAST">EAST REGION</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">No. Telepon*</label>
                            <input type="text" name="phone" id="phone" placeholder="0813XXXXXXXX" class="w-full bg-[#edf2f7] border-none rounded-2xl py-4 px-6 text-slate-800 font-black outline-none focus:ring-2 focus:ring-indigo-500 shadow-inner" required>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Tipe Gudang*</label>
                            <select name="warehouse_type" id="warehouse_type" class="w-full bg-[#edf2f7] border-none rounded-2xl py-4 px-6 text-slate-800 font-black outline-none focus:ring-2 focus:ring-indigo-500 shadow-inner" required>
                                <option value="">Pilih Tipe Gudang</option>
                                <option value="MAIN WAREHOUSE">MAIN WAREHOUSE</option>
                                <option value="DISTRIBUTION CENTER">DISTRIBUTION CENTER</option>
                                <option value="TRANSIT POINT">TRANSIT POINT</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Luas (m²)*</label>
                            <div class="relative">
                                <input type="number" name="area" id="area" value="0" class="w-full bg-[#edf2f7] border-none rounded-2xl py-4 px-6 text-slate-800 font-black outline-none focus:ring-2 focus:ring-indigo-500 shadow-inner" required>
                                <span class="absolute right-6 top-1/2 -translate-y-1/2 text-sm font-black text-slate-400">m²</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[12px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Status*</label>
                            <div class="flex gap-4">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="is_active" id="active_yes" value="1" class="hidden peer" checked>
                                    <div class="py-4 text-center rounded-2xl font-black text-sm uppercase tracking-widest text-slate-500 bg-slate-800 border border-white/5 peer-checked:bg-emerald-500 peer-checked:text-white transition-all">Aktif</div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="is_active" id="active_no" value="0" class="hidden peer">
                                    <div class="py-4 text-center rounded-2xl font-black text-sm uppercase tracking-widest text-slate-500 bg-slate-800 border border-white/5 peer-checked:bg-rose-500 peer-checked:text-white transition-all">Non Aktif</div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-8 border-t border-white/5 bg-slate-800/30 flex justify-end gap-6 rounded-b-[2.5rem]">
            <button onclick="closeModal()" class="text-sm font-black text-slate-500 uppercase tracking-widest hover:text-white transition-colors">Discard</button>
            <button type="submit" form="warehouseForm" onclick="prepareSubmit()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-12 py-4 rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-2xl shadow-indigo-500/40 active:scale-95 transition-all">
                Simpan Gudang
            </button>
        </div>
    </div>
</div>

<script>
    function openModal() { 
        document.getElementById('modalTitle').innerText = 'Tambah Gudang';
        document.getElementById('warehouseForm').action = "{{ route('warehouses.store') }}";
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('warehouseForm').reset();
        document.getElementById('modal').classList.remove('hidden'); 
    }
    
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }

    function toggleHours(checkbox) {
        const start = document.getElementById('op_start');
        const end = document.getElementById('op_end');
        if (checkbox.checked) {
            start.disabled = true;
            end.disabled = true;
            start.value = '';
            end.value = '';
        } else {
            start.disabled = false;
            end.disabled = false;
        }
    }

    function prepareSubmit() {
        const is24 = document.getElementById('is_24_hours').checked;
        const start = document.getElementById('op_start').value;
        const end = document.getElementById('op_end').value;
        document.getElementById('operational_hours').value = is24 ? '24 Hours' : (start && end ? `${start} - ${end}` : '');
    }

    async function editWarehouse(data) {
        document.getElementById('modalTitle').innerText = 'Edit Gudang';
        document.getElementById('warehouseForm').action = `/master/warehouses/update/${data.id}`;
        document.getElementById('formMethod').value = 'POST'; // We use POST for updates in your routes
        
        document.getElementById('name').value = data.name;
        document.getElementById('server_state').value = data.server_state;
        document.getElementById('address').value = data.address;
        document.getElementById('phone').value = data.phone;
        document.getElementById('postal_code').value = data.postal_code;
        document.getElementById('region').value = data.region;
        document.getElementById('warehouse_type').value = data.warehouse_type;
        document.getElementById('area').value = data.area;
        
        document.getElementById('is_24_hours').checked = data.is_24_hours;
        toggleHours(document.getElementById('is_24_hours'));
        
        if (!data.is_24_hours && data.operational_hours) {
            const times = data.operational_hours.split(' - ');
            if (times.length == 2) {
                document.getElementById('op_start').value = times[0];
                document.getElementById('op_end').value = times[1];
            }
        }

        if (data.is_active) document.getElementById('active_yes').checked = true;
        else document.getElementById('active_no').checked = true;

        // Populate Regions (This is tricky with IDs vs Names)
        // Since we stored NAMES, we need to match names in the loaded selects
        // This requires the select to be loaded first
        
        document.getElementById('modal').classList.remove('hidden');
    }

    // REGION API LOGIC (Indonesia)
    const API_BASE = 'https://www.emsifa.com/api-wilayah-indonesia/api';

    async function loadProvinces() {
        const resp = await fetch(`${API_BASE}/provinces.json`);
        const data = await resp.json();
        const select = document.getElementById('province');
        data.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.text = p.name;
            opt.dataset.name = p.name;
            select.appendChild(opt);
        });
    }

    async function loadCities(provinceId) {
        const select = document.getElementById('city');
        select.disabled = !provinceId;
        select.innerHTML = '<option value="">Pilih Kota</option>';
        document.getElementById('district').innerHTML = '<option value="">Pilih Kecamatan</option>';
        document.getElementById('district').disabled = true;
        document.getElementById('village').innerHTML = '<option value="">Pilih Kelurahan</option>';
        document.getElementById('village').disabled = true;

        if (!provinceId) return;
        const resp = await fetch(`${API_BASE}/regencies/${provinceId}.json`);
        const data = await resp.json();
        data.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.text = c.name;
            opt.dataset.name = c.name;
            select.appendChild(opt);
        });
    }

    async function loadDistricts(cityId) {
        const select = document.getElementById('district');
        select.disabled = !cityId;
        select.innerHTML = '<option value="">Pilih Kecamatan</option>';
        document.getElementById('village').innerHTML = '<option value="">Pilih Kelurahan</option>';
        document.getElementById('village').disabled = true;

        if (!cityId) return;
        const resp = await fetch(`${API_BASE}/districts/${cityId}.json`);
        const data = await resp.json();
        data.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.id;
            opt.text = d.name;
            opt.dataset.name = d.name;
            select.appendChild(opt);
        });
    }

    async function loadVillages(districtId) {
        const select = document.getElementById('village');
        select.disabled = !districtId;
        select.innerHTML = '<option value="">Pilih Kelurahan</option>';

        if (!districtId) return;
        const resp = await fetch(`${API_BASE}/villages/${districtId}.json`);
        const data = await resp.json();
        data.forEach(v => {
            const opt = document.createElement('option');
            opt.value = v.id;
            opt.text = v.name;
            opt.dataset.name = v.name;
            select.appendChild(opt);
        });
    }

    // Initial load
    loadProvinces();

    // Overwrite submission to send NAMES instead of IDs
    document.getElementById('warehouseForm').addEventListener('submit', function(e) {
        const p = document.getElementById('province');
        const c = document.getElementById('city');
        const d = document.getElementById('district');
        const v = document.getElementById('village');

        if(p.value && !isNaN(p.value)) p.options[p.selectedIndex].value = p.options[p.selectedIndex].dataset.name;
        if(c.value && !isNaN(c.value)) c.options[c.selectedIndex].value = c.options[c.selectedIndex].dataset.name;
        if(d.value && !isNaN(d.value)) d.options[d.selectedIndex].value = d.options[d.selectedIndex].dataset.name;
        if(v.value && !isNaN(v.value)) v.options[v.selectedIndex].value = v.options[v.selectedIndex].dataset.name;
    });
</script>
@endsection
