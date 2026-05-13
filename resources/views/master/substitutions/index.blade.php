@extends('layouts.app', ['title' => 'Production Substitutions & Capabilities'])

@section('content')
<div class="w-full space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-white tracking-tight">Substitution & Capability Matrix</h2>
            <p class="text-slate-500 text-sm mt-1 font-medium uppercase tracking-widest">Manage production alternatives and machine process capabilities</p>
        </div>
    </div>

    <!-- Tabs Header -->
    <div class="flex items-center gap-2 bg-slate-900/50 p-1.5 rounded-2xl border border-white/5 w-fit">
        <button onclick="switchTab('machine')" id="tab-machine" class="tab-btn active px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all">
            Machine Substitutions
        </button>
        <button onclick="switchTab('item')" id="tab-item" class="tab-btn px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all">
            Item Substitutions
        </button>
        <button onclick="switchTab('capability')" id="tab-capability" class="tab-btn px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all">
            Capability Matrix
        </button>
    </div>

    <!-- Tab Content: Machine Substitutions -->
    <div id="content-machine" class="tab-content space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form -->
            <div class="glass-card p-8 rounded-[2.5rem] border border-white/5 h-fit">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1.5 h-6 bg-indigo-500 rounded-full"></div>
                    <h3 class="text-white font-black text-sm uppercase tracking-widest">Tambah Substitusi Mesin</h3>
                </div>
                <form action="{{ route('substitutions.machine.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Mesin Utama</label>
                        <select name="machine_id" required class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all">
                            <option value="">-- Pilih Mesin --</option>
                            @foreach($machines as $m)
                                <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Mesin Pengganti</label>
                        <select name="substitute_machine_id" required class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all">
                            <option value="">-- Pilih Mesin Pengganti --</option>
                            @foreach($machines as $m)
                                <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Catatan</label>
                        <textarea name="notes" rows="3" class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all" placeholder="Contoh: Digunakan jika mesin utama overload..."></textarea>
                    </div>
                    <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-black uppercase tracking-widest rounded-2xl transition-all shadow-lg shadow-indigo-500/20">
                        Simpan Relasi
                    </button>
                </form>
            </div>
            <!-- List -->
            <div class="lg:col-span-2 glass-card rounded-[2.5rem] border border-white/5 overflow-hidden flex flex-col">
                <div class="p-6 border-b border-white/5 bg-white/[0.01] flex justify-between items-center">
                    <h3 class="text-white font-black text-[12px] uppercase tracking-widest">Daftar Relasi Mesin</h3>
                    <div class="relative">
                        <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" onkeyup="filterTable(this, 'machine-table')" placeholder="Cari relasi..." class="bg-slate-950/50 border border-white/5 rounded-xl py-2 pl-9 pr-4 text-sm text-white focus:ring-1 focus:ring-indigo-500/50 outline-none w-64 transition-all">
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full" id="machine-table">
                        <thead>
                            <tr class="border-b border-white/5 bg-white/[0.02]">
                                <th class="text-left py-6 px-8 text-[12px] font-black text-slate-500 uppercase tracking-widest">Mesin Utama</th>
                                <th class="text-left py-6 px-8 text-[12px] font-black text-slate-500 uppercase tracking-widest">Mesin Pengganti</th>
                                <th class="text-left py-6 px-8 text-[12px] font-black text-slate-500 uppercase tracking-widest">Catatan</th>
                                <th class="text-right py-6 px-8 text-[12px] font-black text-slate-500 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($machineSubstitutions as $ms)
                            <tr class="hover:bg-white/[0.01] transition-colors group">
                                <td class="py-6 px-8">
                                    <div class="text-sm font-bold text-white">{{ $ms->machine_name }}</div>
                                </td>
                                <td class="py-6 px-8">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="arrow-right" class="w-3 h-3 text-indigo-400"></i>
                                        <div class="text-sm font-bold text-indigo-400">{{ $ms->substitute_name }}</div>
                                    </div>
                                </td>
                                <td class="py-6 px-8 text-sm text-slate-500">{{ $ms->notes ?: '-' }}</td>
                                <td class="py-6 px-8 text-right">
                                    <form action="{{ route('substitutions.delete', ['type' => 'machine', 'id' => $ms->id]) }}" method="POST" onsubmit="return confirm('Hapus relasi ini?')">
                                        @csrf
                                        <button class="p-2 text-slate-600 hover:text-rose-500 transition-colors">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-20 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="w-12 h-12 rounded-full bg-slate-900 flex items-center justify-center text-slate-600">
                                            <i data-lucide="layers" class="w-6 h-6"></i>
                                        </div>
                                        <p class="text-[12px] text-slate-500 font-black uppercase tracking-widest">Belum ada data substitusi</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Content: Item Substitutions -->
    <div id="content-item" class="tab-content hidden space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form -->
            <div class="glass-card p-8 rounded-[2.5rem] border border-white/5 h-fit">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1.5 h-6 bg-emerald-500 rounded-full"></div>
                    <h3 class="text-white font-black text-sm uppercase tracking-widest">Tambah Substitusi Item</h3>
                </div>
                <form action="{{ route('substitutions.item.store') }}" method="POST" class="space-y-6">
                    @csrf
                        <div class="flex items-center gap-4">
                            <div class="flex-1 space-y-2">
                                <label class="text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Item Utama</label>
                                <select name="item_id" id="main_item_select" required onchange="updateItemUnit('main')" class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all">
                                    <option value="">-- Pilih Item --</option>
                                    @foreach($items as $i)
                                        <option value="{{ $i->id }}" data-unit="{{ $i->unit->name ?? '-' }}">{{ $i->name }} ({{ $i->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-24 space-y-2">
                                <label class="text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Qty</label>
                                <div class="relative">
                                    <input type="number" id="main_qty" value="1" step="0.01" oninput="calculateRatio()" class="w-full bg-slate-900 border border-white/5 rounded-2xl px-4 py-4 text-sm text-white outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all">
                                    <span id="main_unit_label" class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-emerald-500/50 uppercase">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex-1 space-y-2">
                                <label class="text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Item Pengganti</label>
                                <select name="substitute_item_id" id="sub_item_select" required onchange="updateItemUnit('sub')" class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all">
                                    <option value="">-- Pilih Item Pengganti --</option>
                                    @foreach($items as $i)
                                        <option value="{{ $i->id }}" data-unit="{{ $i->unit->name ?? '-' }}">{{ $i->name }} ({{ $i->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-24 space-y-2">
                                <label class="text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Qty</label>
                                <div class="relative">
                                    <input type="number" id="sub_qty" value="1" step="0.01" oninput="calculateRatio()" class="w-full bg-slate-900 border border-white/5 rounded-2xl px-4 py-4 text-sm text-white outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all">
                                    <span id="sub_unit_label" class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-emerald-500/50 uppercase">-</span>
                                </div>
                            </div>
                        </div>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center px-1">
                            <label class="text-[12px] font-black text-slate-500 uppercase tracking-widest">Rasio Konversi</label>
                            <span id="ratio_preview" class="text-[10px] font-black text-emerald-500 italic uppercase">1 Utama : 1.0000 Pengganti</span>
                        </div>
                        <div class="flex items-center gap-3 bg-slate-900 border border-white/5 rounded-2xl px-5 py-4">
                            <input type="number" step="0.01" name="conversion_ratio" id="conversion_ratio" required value="1.00" class="w-full bg-transparent border-none text-sm text-white outline-none" placeholder="1.00">
                            <span class="text-[12px] text-emerald-400 font-black tracking-widest opacity-50">FINAL RATIO</span>
                        </div>
                    </div>
                    <button type="submit" class="w-full py-4 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-black uppercase tracking-widest rounded-2xl transition-all shadow-lg shadow-emerald-500/20">
                        Simpan Relasi
                    </button>
                </form>
            </div>
            <!-- List -->
            <div class="lg:col-span-2 glass-card rounded-[2.5rem] border border-white/5 overflow-hidden flex flex-col">
                <div class="p-6 border-b border-white/5 bg-white/[0.01] flex justify-between items-center">
                    <h3 class="text-white font-black text-[12px] uppercase tracking-widest">Daftar Relasi Item</h3>
                    <div class="relative">
                        <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" onkeyup="filterTable(this, 'item-table')" placeholder="Cari relasi..." class="bg-slate-950/50 border border-white/5 rounded-xl py-2 pl-9 pr-4 text-sm text-white focus:ring-1 focus:ring-emerald-500/50 outline-none w-64 transition-all">
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full" id="item-table">
                        <thead>
                            <tr class="border-b border-white/5 bg-white/[0.02]">
                                <th class="text-left py-6 px-8 text-[12px] font-black text-slate-500 uppercase tracking-widest">Item Utama</th>
                                <th class="text-left py-6 px-8 text-[12px] font-black text-slate-500 uppercase tracking-widest">Item Pengganti</th>
                                <th class="text-left py-6 px-8 text-[12px] font-black text-slate-500 uppercase tracking-widest">Rasio</th>
                                <th class="text-right py-6 px-8 text-[12px] font-black text-slate-500 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($itemSubstitutions as $is)
                            <tr class="hover:bg-white/[0.01] transition-colors group">
                                <td class="py-6 px-8">
                                    <div class="text-sm font-bold text-white">{{ $is->item_name }}</div>
                                </td>
                                <td class="py-6 px-8">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="refresh-cw" class="w-3 h-3 text-emerald-400"></i>
                                        <div class="text-sm font-bold text-emerald-400">{{ $is->substitute_name }}</div>
                                    </div>
                                </td>
                                <td class="py-6 px-8">
                                    <div class="flex flex-col">
                                        <span class="px-2 py-1 bg-emerald-500/10 text-emerald-500 rounded text-[12px] font-bold w-fit">{{ number_format($is->conversion_ratio, 2) }}</span>
                                        <div class="text-[9px] text-slate-500 font-bold uppercase mt-1">1 {{ $is->item_unit }} = {{ number_format($is->conversion_ratio, 2) }} {{ $is->substitute_unit }}</div>
                                    </div>
                                </td>
                                <td class="py-6 px-8 text-right">
                                    <form action="{{ route('substitutions.delete', ['type' => 'item', 'id' => $is->id]) }}" method="POST">
                                        @csrf
                                        <button class="p-2 text-slate-600 hover:text-rose-500 transition-colors">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-20 text-center text-slate-500 uppercase text-[12px] font-black">Belum ada data substitusi item</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Content: Capability Matrix -->
    <div id="content-capability" class="tab-content hidden space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form -->
            <div class="glass-card p-8 rounded-[2.5rem] border border-white/5 h-fit">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1.5 h-6 bg-amber-500 rounded-full"></div>
                    <h3 class="text-white font-black text-sm uppercase tracking-widest">Tambah Kapabilitas Mesin</h3>
                </div>
                <form action="{{ route('substitutions.capability.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Mesin</label>
                        <select name="machine_id" required class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white outline-none focus:ring-2 focus:ring-amber-500/20 transition-all">
                            <option value="">-- Pilih Mesin --</option>
                            @foreach($machines as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Item/Produk yang Bisa Dibuat</label>
                        <select name="item_id" required class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white outline-none focus:ring-2 focus:ring-amber-500/20 transition-all">
                            <option value="">-- Pilih Item/Produk --</option>
                            @foreach($items as $i)
                                <option value="{{ $i->id }}">{{ $i->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Laju Produksi (Qty)</label>
                            <input type="number" step="0.01" name="production_rate" class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white outline-none focus:ring-2 focus:ring-amber-500/20 transition-all" placeholder="0.00">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Satuan (Output)</label>
                            <select name="output_unit" required class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white outline-none focus:ring-2 focus:ring-amber-500/20 transition-all">
                                <option value="">Pilih Satuan</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->name }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Interval Waktu</label>
                            <select name="capacity_unit" class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white outline-none focus:ring-2 focus:ring-amber-500/20 transition-all">
                                <option value="perjam">Per Jam</option>
                                <option value="perhari">Per Hari</option>
                                <option value="perminggu">Per Minggu</option>
                                <option value="perbulan">Per Bulan</option>
                                <option value="pertahun">Per Tahun</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Ketebalan / Micron</label>
                            <input type="text" name="thickness" class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white outline-none focus:ring-2 focus:ring-amber-500/20 transition-all" placeholder="Contoh: 80-85">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Diameter (mm)</label>
                            <input type="text" name="diameter" class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white outline-none focus:ring-2 focus:ring-amber-500/20 transition-all" placeholder="Contoh: 91">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Cavity</label>
                            <input type="number" name="cavity" class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white outline-none focus:ring-2 focus:ring-amber-500/20 transition-all" placeholder="0">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[12px] font-black text-slate-500 uppercase tracking-widest ml-1">Cycle</label>
                            <input type="number" step="0.01" name="cycle" class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white outline-none focus:ring-2 focus:ring-amber-500/20 transition-all" placeholder="0.00">
                        </div>
                    </div>
                    <div class="flex items-center gap-3 px-1">
                        <input type="checkbox" name="is_default" id="is_default" class="w-4 h-4 rounded border-white/10 bg-slate-900 text-amber-500 focus:ring-amber-500/20">
                        <label for="is_default" class="text-[12px] font-black text-slate-400 uppercase tracking-widest cursor-pointer">Jadikan Mesin Utama untuk Produk ini</label>
                    </div>
                    <button type="submit" class="w-full py-4 bg-amber-600 hover:bg-amber-500 text-white text-sm font-black uppercase tracking-widest rounded-2xl transition-all shadow-lg shadow-amber-500/20">
                        Simpan Kapabilitas
                    </button>
                </form>
            </div>
            <!-- Matrix Table -->
            <div class="lg:col-span-2 glass-card rounded-[2.5rem] border border-white/5 overflow-hidden flex flex-col">
                <div class="p-6 border-b border-white/5 bg-white/[0.01] flex justify-between items-center">
                    <h3 class="text-white font-black text-[12px] uppercase tracking-widest">Matriks Kapabilitas</h3>
                    <div class="relative">
                        <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" onkeyup="filterTable(this, 'capability-table')" placeholder="Cari kapabilitas..." class="bg-slate-950/50 border border-white/5 rounded-xl py-2 pl-9 pr-4 text-sm text-white focus:ring-1 focus:ring-amber-500/50 outline-none w-64 transition-all">
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full" id="capability-table">
                        <thead>
                            <tr class="border-b border-white/5 bg-white/[0.02]">
                                <th class="text-left py-6 px-8 text-[12px] font-black text-slate-500 uppercase tracking-widest">Item/Produk</th>
                                <th class="text-left py-6 px-8 text-[12px] font-black text-slate-500 uppercase tracking-widest">Specs</th>
                                <th class="text-left py-6 px-8 text-[12px] font-black text-slate-500 uppercase tracking-widest">Process</th>
                                <th class="text-left py-6 px-8 text-[12px] font-black text-slate-500 uppercase tracking-widest">Capacity</th>
                                <th class="text-left py-6 px-8 text-[12px] font-black text-slate-500 uppercase tracking-widest">Status</th>
                                <th class="text-right py-6 px-8 text-[12px] font-black text-slate-500 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @php $groupedCapabilities = $capabilities->groupBy('machine_name'); @endphp
                            @forelse($groupedCapabilities as $machineName => $machineCaps)
                            <tr class="bg-indigo-500/5 border-l-4 border-indigo-500">
                                <td colspan="6" class="py-4 px-8 text-[11px] font-black text-indigo-400 uppercase tracking-widest">
                                    Machine: {{ $machineName }}
                                </td>
                            </tr>
                            @foreach($machineCaps as $cap)
                            <tr class="hover:bg-white/[0.01] transition-colors group">
                                <td class="py-6 px-8 text-sm font-bold text-white">{{ $cap->item_name }}</td>
                                <td class="py-6 px-8">
                                    <div class="flex flex-col gap-1">
                                        @if($cap->thickness) <span class="text-[11px] text-slate-400 font-bold">Thick: {{ $cap->thickness }} mic</span> @endif
                                        @if($cap->diameter) <span class="text-[11px] text-slate-400 font-bold">Diam: {{ $cap->diameter }} mm</span> @endif
                                        @if(!$cap->thickness && !$cap->diameter) <span class="text-slate-600 italic text-[11px]">-</span> @endif
                                    </div>
                                </td>
                                <td class="py-6 px-8">
                                    <div class="flex flex-col gap-1 text-[11px] font-black uppercase tracking-tighter">
                                        <div class="flex justify-between w-24">
                                            <span class="text-slate-500">Cavity:</span>
                                            <span class="text-emerald-400">{{ $cap->cavity ?? 0 }}</span>
                                        </div>
                                        <div class="flex justify-between w-24">
                                            <span class="text-slate-500">Cycle:</span>
                                            <span class="text-emerald-400">{{ number_format($cap->cycle ?? 0, 2) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-6 px-8">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-amber-400">{{ number_format($cap->production_rate, 2) }}</span>
                                        <span class="text-[10px] text-slate-500 font-bold uppercase">{{ $cap->output_unit }} / {{ $cap->capacity_unit }}</span>
                                    </div>
                                </td>
                                <td class="py-6 px-8">
                                    @if($cap->is_default)
                                        <span class="px-2 py-1 bg-amber-500/10 text-amber-500 rounded text-[11px] font-black uppercase tracking-widest">Primary</span>
                                    @else
                                        <span class="px-2 py-1 bg-white/5 text-slate-500 rounded text-[11px] font-black uppercase tracking-widest">Alt</span>
                                    @endif
                                </td>
                                <td class="py-6 px-8 text-right">
                                    <form action="{{ route('substitutions.delete', ['type' => 'capability', 'id' => $cap->id]) }}" method="POST">
                                        @csrf
                                        <button class="p-2 text-slate-600 hover:text-rose-500 transition-colors">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            @empty
                            <tr>
                                <td colspan="6" class="py-20 text-center text-slate-500 uppercase text-[12px] font-black tracking-widest">Matriks Kapabilitas Kosong</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .tab-btn {
        color: #64748b;
    }
    .tab-btn.active {
        background: #4f46e5;
        color: white;
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
    }
    #tab-item.active { background: #10b981; box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.4); }
    #tab-capability.active { background: #f59e0b; box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.4); }

    /* Custom Searchable Select - Premium Version */
    .searchable-select-container {
        position: relative;
        width: 100%;
    }
    .custom-select-trigger {
        width: 100%;
        background: #0f172a;
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 1.25rem;
        padding: 1rem 1.25rem;
        color: white;
        font-size: 0.875rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .custom-select-trigger:hover {
        background: rgba(255,255,255,0.02);
        border-color: rgba(255,255,255,0.1);
    }
    .custom-select-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #1e293b;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 1.25rem;
        margin-top: 0.5rem;
        z-index: 100;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        display: none;
        overflow: hidden;
    }
    .custom-select-dropdown.show {
        display: block;
        animation: slideDown 0.2s ease-out;
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .custom-select-search {
        padding: 0.75rem;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .custom-select-search input {
        width: 100%;
        background: rgba(0,0,0,0.2);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 0.75rem;
        padding: 0.6rem 1rem;
        color: white;
        font-size: 0.75rem;
        outline: none;
    }
    .custom-select-options-list {
        max-height: 200px;
        overflow-y: auto;
    }
    .custom-option {
        padding: 0.75rem 1.25rem;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.8125rem;
        color: #94a3b8;
    }
    .custom-option:hover {
        background: rgba(255,255,255,0.05);
        color: white;
    }
    .custom-option.selected {
        background: rgba(99, 102, 241, 0.1);
        color: #818cf8;
        font-weight: bold;
    }
</style>

<script>
    function switchTab(tab) {
        // Hide all content
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        // Remove active class from buttons
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        
        // Show selected
        document.getElementById('content-' + tab).classList.remove('hidden');
        document.getElementById('tab-' + tab).classList.add('active');
    }

    function filterTable(input, tableId) {
        const filter = input.value.toLowerCase();
        const table = document.getElementById(tableId);
        const tr = table.getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) {
            let found = false;
            const td = tr[i].getElementsByTagName("td");
            for (let j = 0; j < td.length - 1; j++) { // Skip action column
                if (td[j]) {
                    const text = td[j].textContent || td[j].innerText;
                    if (text.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            tr[i].style.display = found ? "" : "none";
        }
    }

    // Premium Searchable Select
    function initSearchableSelects() {
        document.querySelectorAll('select').forEach(select => {
            if (select.closest('.searchable-select-container')) return;

            const container = document.createElement('div');
            container.className = 'searchable-select-container';
            
            const trigger = document.createElement('div');
            trigger.className = 'custom-select-trigger';
            trigger.innerHTML = `
                <span class="trigger-text">${select.options[select.selectedIndex]?.text || '-- Pilih --'}</span>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500"></i>
            `;
            
            const dropdown = document.createElement('div');
            dropdown.className = 'custom-select-dropdown';
            
            const searchWrap = document.createElement('div');
            searchWrap.className = 'custom-select-search';
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = 'Cari...';
            searchWrap.appendChild(searchInput);
            
            const optionsList = document.createElement('div');
            optionsList.className = 'custom-select-options-list custom-scrollbar';
            
            function renderOptions(filter = '') {
                optionsList.innerHTML = '';
                Array.from(select.options).forEach(opt => {
                    if (filter && !opt.text.toLowerCase().includes(filter.toLowerCase()) && opt.value !== "") return;
                    
                    const customOpt = document.createElement('div');
                    customOpt.className = 'custom-option' + (select.value === opt.value ? ' selected' : '');
                    customOpt.innerText = opt.text;
                    customOpt.onclick = () => {
                        select.value = opt.value;
                        trigger.querySelector('.trigger-text').innerText = opt.text;
                        dropdown.classList.remove('show');
                        select.dispatchEvent(new Event('change'));
                        renderOptions(); // Update selection state
                    };
                    optionsList.appendChild(customOpt);
                });
            }

            trigger.onclick = (e) => {
                e.stopPropagation();
                // Close others
                document.querySelectorAll('.custom-select-dropdown.show').forEach(d => {
                    if (d !== dropdown) d.classList.remove('show');
                });
                dropdown.classList.toggle('show');
                if (dropdown.classList.contains('show')) {
                    searchInput.focus();
                }
            };

            searchInput.onclick = (e) => e.stopPropagation();
            searchInput.onkeyup = () => renderOptions(searchInput.value);

            document.addEventListener('click', () => {
                dropdown.classList.remove('show');
            });

            container.appendChild(trigger);
            dropdown.appendChild(searchWrap);
            dropdown.appendChild(optionsList);
            container.appendChild(dropdown);
            
            // Move original select inside container to prevent re-initialization
            select.style.display = 'none';
            select.parentNode.insertBefore(container, select);
            container.appendChild(select);
            
            renderOptions();
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    }

    function updateItemUnit(type) {
        const select = document.getElementById(type === 'main' ? 'main_item_select' : 'sub_item_select');
        const label = document.getElementById(type === 'main' ? 'main_unit_label' : 'sub_unit_label');
        const selectedOption = select.options[select.selectedIndex];
        const unit = selectedOption ? selectedOption.getAttribute('data-unit') : '-';
        label.innerText = unit || '-';
        calculateRatio();
    }

    function calculateRatio() {
        const mainQty = parseFloat(document.getElementById('main_qty').value) || 1;
        const subQty = parseFloat(document.getElementById('sub_qty').value) || 1;
        const ratio = subQty / mainQty;
        
        const ratioInput = document.getElementById('conversion_ratio');
        const preview = document.getElementById('ratio_preview');
        
        ratioInput.value = ratio.toFixed(2);
        preview.innerText = `1 Utama : ${ratio.toFixed(2)} Pengganti`;
    }

    // Call init
    initSearchableSelects();
</script>
@endsection
