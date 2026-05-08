@extends('layouts.app', ['title' => 'Master Customer'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white">Master Customer</h3>
            <p class="text-slate-400 text-sm italic">Manage your customer database and profiles</p>
        </div>
        <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Tambah Pelanggan
        </button>
    </div>

    <!-- Table Section -->
    <div class="glass-card rounded-xl overflow-hidden border border-white/5">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[12px] uppercase tracking-widest border-b border-white/5">
                    <th class="px-6 py-4">Customer ID</th>
                    <th class="px-6 py-4">Informasi Pelanggan</th>
                    <th class="px-6 py-4">Kontak & Alamat</th>
                    <th class="px-6 py-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $item)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-6 py-4 font-mono text-sm text-indigo-400 font-bold">{{ $item->customer_id }}</td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-white">{{ $item->name }}</div>
                        <div class="text-[12px] text-slate-500 italic">User: {{ $item->username ?? '-' }} ({{ $item->gender ?? 'N/A' }})</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-300">{{ $item->email }}</div>
                        <div class="text-[12px] text-slate-500">{{ $item->phone }}</div>
                        <div class="text-[11px] text-slate-600 mt-1 truncate max-w-[200px]">{{ $item->address }}</div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button onclick="editCustomer({{ $item->toJson() }})" class="p-2 text-slate-500 hover:text-indigo-400"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                        <form action="{{ route('customers.delete', $item->id) }}" method="POST" class="inline">
                            @csrf
                            <button class="p-2 text-slate-500 hover:text-rose-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-20 text-center text-slate-500">Belum ada data pelanggan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-2xl rounded-2xl flex flex-col max-h-[90vh] overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-white/5 flex justify-between items-center bg-slate-800/50">
            <h3 id="modalTitle" class="text-lg font-bold text-white">Tambah Pelanggan Baru</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <div class="flex-1 overflow-y-auto p-10 modal-scroll bg-[#0f172a]/50">
            <form id="customerForm" action="{{ route('customers.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-1">Customer ID</label>
                        <input type="text" id="disp_customer_id" value="AUTO-GENERATED" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 text-indigo-400 font-bold" readonly>
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold text-slate-500 uppercase mb-1">Contact Person</label>
                        <input type="text" name="username" id="username" placeholder="Nama PIC / Kontak" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white font-medium">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[12px] font-bold text-slate-500 uppercase mb-1">Nama Pelanggan*</label>
                        <input type="text" name="name" id="name" placeholder="Masukkan nama lengkap" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white font-medium" required>
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold text-slate-500 uppercase mb-1">Email</label>
                        <input type="email" name="email" id="email" placeholder="contoh@mail.com" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold text-slate-500 uppercase mb-1">No. Telepon*</label>
                        <input type="text" name="phone" id="phone" placeholder="0812xxxx" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[12px] font-bold text-slate-500 uppercase mb-1">Alamat Lengkap*</label>
                        <textarea name="address" id="address" rows="3" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white" required></textarea>
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold text-slate-500 uppercase mb-1">Negara</label>
                        <input type="text" name="country" id="country" list="country_list" placeholder="Ketik nama negara..." class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white">
                        <datalist id="country_list"></datalist>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[12px] font-bold text-slate-500 uppercase mb-2">Jenis Kelamin</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="gender" id="gender_male" value="MALE" class="w-4 h-4 text-indigo-600 bg-slate-800 border-white/10">
                                <span class="text-sm text-slate-300">MALE</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="gender" id="gender_female" value="FEMALE" class="w-4 h-4 text-indigo-600 bg-slate-800 border-white/10">
                                <span class="text-sm text-slate-300">FEMALE</span>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="p-8 border-t border-white/5 bg-slate-800/50 flex justify-end gap-3 shrink-0">
            <button onclick="closeModal()" class="px-6 py-2.5 text-slate-400 font-bold hover:text-white transition-colors">Batal</button>
            <button type="submit" form="customerForm" class="bg-indigo-600 hover:bg-indigo-700 text-white px-10 py-2.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition-all active:scale-95">Simpan Pelanggan</button>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('modalTitle').innerText = 'Tambah Pelanggan Baru';
        document.getElementById('customerForm').action = "{{ route('customers.store') }}";
        document.getElementById('customerForm').reset();
        document.getElementById('disp_customer_id').value = 'AUTO-GENERATED';
        document.getElementById('modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('modal').classList.add('hidden');
    }

    function editCustomer(data) {
        document.getElementById('modalTitle').innerText = 'Edit Data Pelanggan';
        document.getElementById('customerForm').action = `/master/customers/update/${data.id}`;
        document.getElementById('disp_customer_id').value = data.customer_id;
        document.getElementById('username').value = data.username;
        document.getElementById('name').value = data.name;
        document.getElementById('email').value = data.email;
        document.getElementById('phone').value = data.phone;
        document.getElementById('address').value = data.address;
        document.getElementById('country').value = data.country;
        
        if (data.gender) {
            const radio = document.querySelector(`input[name="gender"][value="${data.gender}"]`);
            if (radio) radio.checked = true;
        }

        document.getElementById('modal').classList.remove('hidden');
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

    loadCountries();
</script>
@endsection
