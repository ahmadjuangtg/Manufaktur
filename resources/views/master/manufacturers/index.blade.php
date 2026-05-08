@extends('layouts.app', ['title' => 'Master Manufaktur'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold">Database Manufaktur</h3>
            <p class="text-slate-400 text-sm">Kelola data mitra manufaktur terdaftar</p>
        </div>
        <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all shadow-lg shadow-indigo-500/20">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Manufaktur
        </button>
    </div>

    <!-- Table Section -->
    <div class="glass-card rounded-xl overflow-hidden border border-white/5">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[12px] uppercase tracking-widest border-b border-white/5">
                    <th class="px-6 py-4">Kode</th>
                    <th class="px-6 py-4">Manufacturer</th>
                    <th class="px-6 py-4">Lokasi</th>
                    <th class="px-6 py-4">Kontak Person</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $item)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-6 py-4 font-mono text-sm text-indigo-400">{{ $item->code }}</td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-white">{{ $item->name }}</div>
                        <div class="text-[12px] text-slate-500">{{ $item->email }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-400">{{ $item->city }}, {{ $item->province }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-white">{{ $item->contact_name }}</div>
                        <div class="text-[12px] text-slate-500">{{ $item->contact_phone }}</div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button onclick="editManufacturer({{ $item->toJson() }})" class="p-2 text-slate-500 hover:text-indigo-400"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                        <form action="{{ route('manufacturers.delete', $item->id) }}" method="POST" class="inline">
                            @csrf
                            <button class="p-2 text-slate-500 hover:text-rose-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-20 text-center text-slate-500">No data available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-2xl rounded-2xl flex flex-col max-h-[90vh] overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-white/5 flex justify-between items-center bg-slate-800/50">
            <h3 id="modalTitle" class="text-lg font-bold text-white">Input Manufaktur</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <div class="flex-1 overflow-y-auto p-8 modal-scroll bg-[#0f172a]/50">
            <form id="manufForm" action="{{ route('manufacturers.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-2">Asal Manufaktur</label>
                        <div class="flex gap-4 p-4 bg-slate-900/30 rounded-xl border border-white/5">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="is_local" value="1" checked onchange="toggleOrigin(true)" class="w-4 h-4 text-indigo-600 bg-slate-800 border-white/10">
                                <span class="text-sm font-bold text-slate-400 group-hover:text-white transition-colors">Dalam Negeri (Indonesia)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="is_local" id="origin_overseas" value="0" onchange="toggleOrigin(false)" class="w-4 h-4 text-indigo-600 bg-slate-800 border-white/10">
                                <span class="text-sm font-bold text-slate-400 group-hover:text-white transition-colors">Luar Negeri (Overseas)</span>
                            </label>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-1">Nama Manufaktur*</label>
                        <input type="text" name="name" id="name" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none font-medium text-white" required>
                    </div>

                    <div id="country_wrapper" class="hidden md:col-span-2">
                        <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-1">Negara*</label>
                        <input type="text" name="country" id="country" list="country_list" placeholder="Ketik nama negara..." class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none font-medium text-white">
                        <datalist id="country_list"></datalist>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-1">Alamat Lengkap*</label>
                        <textarea name="address" id="address" rows="2" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none font-medium text-white" required></textarea>
                    </div>

                    <div class="grid grid-cols-2 md:col-span-2 gap-4 region-fields">
                        <div>
                            <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-1">Provinsi</label>
                            <select name="province" id="province" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white" onchange="loadCities(this.value)">
                                <option value="">Pilih Provinsi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-1">Kota</label>
                            <select name="city" id="city" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white" disabled onchange="loadDistricts(this.value)">
                                <option value="">Pilih Kota</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-1">Kecamatan</label>
                            <select name="district" id="district" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white" disabled onchange="loadVillages(this.value)">
                                <option value="">Pilih Kecamatan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-1">Kelurahan</label>
                            <select name="sub_district" id="village" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white" disabled>
                                <option value="">Pilih Kelurahan</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-1">No. Telepon*</label>
                        <input type="text" name="phone" id="phone" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white" required>
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-1">Email</label>
                        <input type="email" name="email" id="email" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white">
                    </div>
                </div>
                <div class="pt-6 border-t border-white/5">
                    <h4 class="text-sm font-bold text-indigo-400 uppercase mb-4 tracking-widest italic">Informasi Kontak Person</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <input type="text" name="contact_name" id="contact_name" placeholder="Nama Lengkap PIC*" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white" required>
                        </div>
                        <input type="text" name="contact_phone" id="contact_phone" placeholder="No. Telp PIC*" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white" required>
                        <input type="email" name="contact_email" id="contact_email" placeholder="Email PIC*" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white" required>
                    </div>
                </div>
            </form>
        </div>
        <div class="p-6 border-t border-white/5 bg-slate-800/50 flex justify-end gap-3">
            <button onclick="closeModal()" class="px-6 py-2 text-slate-400 font-bold">Batal</button>
            <button type="submit" form="manufForm" class="bg-indigo-600 text-white px-8 py-2 rounded-lg font-bold shadow-lg shadow-indigo-500/20">Simpan Data</button>
        </div>
    </div>
</div>

<script>
    function openModal() { 
        document.getElementById('modalTitle').innerText = 'Input Manufaktur';
        document.getElementById('manufForm').action = "{{ route('manufacturers.store') }}";
        document.getElementById('manufForm').reset();
        toggleOrigin(true);
        document.getElementById('modal').classList.remove('hidden'); 
    }
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }

    function toggleOrigin(isLocal) {
        const regionFields = document.querySelectorAll('.region-fields');
        const countryWrapper = document.getElementById('country_wrapper');
        const countryInput = document.getElementById('country');

        if (isLocal) {
            regionFields.forEach(f => f.classList.remove('hidden'));
            countryWrapper.classList.add('hidden');
            countryInput.value = 'Indonesia';
        } else {
            regionFields.forEach(f => f.classList.add('hidden'));
            countryWrapper.classList.remove('hidden');
            countryInput.value = '';
        }
    }

    function editManufacturer(data) {
        document.getElementById('modalTitle').innerText = 'Edit Manufaktur';
        document.getElementById('manufForm').action = `/master/manufacturers/update/${data.id}`;
        
        document.getElementById('name').value = data.name;
        document.getElementById('address').value = data.address;
        document.getElementById('phone').value = data.phone;
        document.getElementById('email').value = data.email;
        document.getElementById('contact_name').value = data.contact_name;
        document.getElementById('contact_phone').value = data.contact_phone;
        document.getElementById('contact_email').value = data.contact_email;

        const isLocal = data.is_local == 1;
        document.querySelector(`input[name="is_local"][value="${data.is_local}"]`).checked = true;
        toggleOrigin(isLocal);

        if (!isLocal) {
            document.getElementById('country').value = data.country;
        }

        // Regions
        if (isLocal) {
            if (data.province) {
                const pSel = document.getElementById('province');
                pSel.innerHTML = `<option value="${data.province}" data-name="${data.province}" selected>${data.province}</option>`;
                loadProvinces();
            }
            if (data.city) {
                const cSel = document.getElementById('city');
                cSel.disabled = false;
                cSel.innerHTML = `<option value="${data.city}" data-name="${data.city}" selected>${data.city}</option>`;
            }
            if (data.district) {
                const dSel = document.getElementById('district');
                dSel.disabled = false;
                dSel.innerHTML = `<option value="${data.district}" data-name="${data.district}" selected>${data.district}</option>`;
            }
            if (data.sub_district) {
                const vSel = document.getElementById('village');
                vSel.disabled = false;
                vSel.innerHTML = `<option value="${data.sub_district}" data-name="${data.sub_district}" selected>${data.sub_district}</option>`;
            }
        }
        
        document.getElementById('modal').classList.remove('hidden');
    }

    // REGION API LOGIC
    const API_BASE = 'https://www.emsifa.com/api-wilayah-indonesia/api';

    async function loadProvinces() {
        const select = document.getElementById('province');
        const existingVal = select.value;
        const resp = await fetch(`${API_BASE}/provinces.json`);
        const data = await resp.json();
        
        if (!existingVal) select.innerHTML = '<option value="">Pilih Provinsi</option>';
        
        data.forEach(p => {
            if (p.name === existingVal) {
                select.options[0].value = p.id;
                return;
            }
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.text = p.name;
            opt.dataset.name = p.name;
            select.appendChild(opt);
        });
    }

    async function loadCountries() {
        const datalist = document.getElementById('country_list');
        try {
            const resp = await fetch('/countries.json');
            const sorted = await resp.json();
            sorted.forEach(name => {
                const opt = document.createElement('option');
                opt.value = name;
                datalist.appendChild(opt);
            });
        } catch (e) { console.error("Error loading countries", e); }
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

    loadProvinces();
    loadCountries();

    document.getElementById('manufForm').addEventListener('submit', function(e) {
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
