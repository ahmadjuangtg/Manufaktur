@extends('layouts.app', ['title' => 'Audit Trail Logs'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
        <div>
            <h3 class="text-xl font-bold text-white flex items-center gap-3">
                <i data-lucide="scroll" class="w-6 h-6 text-indigo-400"></i>
                Audit Trail & Activity Logs
            </h3>
            <p class="text-slate-400 text-sm mt-1">
                Real-time tracking of data changes, user activities, and system modifications (5-day automatic retention).
            </p>
        </div>
    </div>

    <!-- Interactive Glass Filter Panel -->
    <div class="glass-card p-6 rounded-2xl border border-white/5 shadow-2xl">
        <form action="{{ route('activity_logs.index') }}" method="GET" id="filterForm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search Input -->
                <div class="relative">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Pencarian</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" 
                               placeholder="Cari deskripsi / operator..." 
                               class="w-full bg-[#1e293b]/70 border border-white/10 rounded-xl py-2.5 pl-10 pr-4 text-xs focus:border-indigo-500 outline-none text-white transition-all font-medium">
                        <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3"></i>
                    </div>
                </div>

                <!-- User Filter -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Operator (User)</label>
                    <select name="user_id" onchange="this.form.submit()" 
                            class="w-full bg-[#1e293b]/70 border border-white/10 rounded-xl py-2.5 px-3 text-xs focus:border-indigo-500 outline-none text-white font-medium cursor-pointer">
                        <option value="">Semua Operator</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $user_id == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Filter -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Tindakan</label>
                    <select name="action" onchange="this.form.submit()" 
                            class="w-full bg-[#1e293b]/70 border border-white/10 rounded-xl py-2.5 px-3 text-xs focus:border-indigo-500 outline-none text-white font-medium cursor-pointer">
                        <option value="">Semua Tindakan</option>
                        <option value="CREATED" {{ $action == 'CREATED' ? 'selected' : '' }}>CREATED (Buat)</option>
                        <option value="UPDATED" {{ $action == 'UPDATED' ? 'selected' : '' }}>UPDATED (Ubah)</option>
                        <option value="DELETED" {{ $action == 'DELETED' ? 'selected' : '' }}>DELETED (Hapus)</option>
                    </select>
                </div>

                <!-- Date Start -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Mulai Tanggal</label>
                    <div class="relative">
                        <input type="date" name="date_start" value="{{ $date_start }}"
                               class="w-full bg-[#1e293b]/70 border border-white/10 rounded-xl py-2.5 px-3 text-xs focus:border-indigo-500 outline-none text-white font-medium cursor-pointer">
                    </div>
                </div>

                <!-- Date End -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Sampai Tanggal</label>
                    <div class="relative flex gap-2">
                        <input type="date" name="date_end" value="{{ $date_end }}"
                               class="w-full bg-[#1e293b]/70 border border-white/10 rounded-xl py-2.5 px-3 text-xs focus:border-indigo-500 outline-none text-white font-medium cursor-pointer">
                        
                        <div class="flex gap-1">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white p-2.5 rounded-xl flex items-center justify-center transition-all shadow-lg shadow-indigo-600/10">
                                <i data-lucide="filter" class="w-4 h-4"></i>
                            </button>
                            <a href="{{ route('activity_logs.index') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 p-2.5 rounded-xl flex items-center justify-center transition-all border border-white/5">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card Container -->
    <div class="glass-card rounded-2xl overflow-hidden border border-white/5 shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-800/40 text-slate-400 text-[10px] font-bold uppercase tracking-widest border-b border-white/5">
                        <th class="px-6 py-4.5">Waktu</th>
                        <th class="px-6 py-4.5">Operator</th>
                        <th class="px-6 py-4.5">Tindakan</th>
                        <th class="px-6 py-4.5">Deskripsi Aktivitas</th>
                        <th class="px-6 py-4.5">Sistem Info</th>
                        <th class="px-6 py-4.5 text-right">Rincian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-sm">
                    @forelse($logs as $log)
                    <tr class="hover:bg-white/[0.03] transition-colors group">
                        <!-- Timestamp -->
                        <td class="px-6 py-4.5 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-500"></i>
                                <span class="text-white font-medium text-xs">
                                    {{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y') }}
                                </span>
                                <span class="text-slate-400 font-bold text-xs bg-slate-800 px-1.5 py-0.5 rounded">
                                    {{ $log->created_at->timezone('Asia/Jakarta')->format('H:i:s') }}
                                </span>
                            </div>
                        </td>

                        <!-- Operator -->
                        <td class="px-6 py-4.5">
                            @if($log->user)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-600/10 border border-indigo-500/20 flex items-center justify-center font-black text-xs text-indigo-400 shadow-sm">
                                    {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                </div>
                                <div class="overflow-hidden max-w-[150px]">
                                    <div class="font-semibold text-white text-xs truncate">{{ $log->user->name }}</div>
                                    <div class="text-[10px] text-slate-500 truncate">{{ $log->user->email }}</div>
                                </div>
                            </div>
                            @else
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-800 border border-white/5 flex items-center justify-center font-black text-xs text-slate-500">
                                    S
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-400 text-xs">System / Guest</div>
                                    <div class="text-[10px] text-slate-600">No Operator</div>
                                </div>
                            </div>
                            @endif
                        </td>

                        <!-- Action Badge -->
                        <td class="px-6 py-4.5 whitespace-nowrap">
                            @if($log->action === 'CREATED')
                            <span class="inline-flex items-center gap-1.5 text-[9px] font-black text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-1 rounded-lg uppercase tracking-wider">
                                <i data-lucide="plus-circle" class="w-3 h-3"></i> Created
                            </span>
                            @elseif($log->action === 'UPDATED')
                            <span class="inline-flex items-center gap-1.5 text-[9px] font-black text-blue-400 bg-blue-500/10 border border-blue-500/20 px-2 py-1 rounded-lg uppercase tracking-wider">
                                <i data-lucide="refresh-cw" class="w-3 h-3"></i> Updated
                            </span>
                            @elseif($log->action === 'DELETED')
                            <span class="inline-flex items-center gap-1.5 text-[9px] font-black text-rose-400 bg-rose-500/10 border border-rose-500/20 px-2 py-1 rounded-lg uppercase tracking-wider">
                                <i data-lucide="trash-2" class="w-3 h-3"></i> Deleted
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 text-[9px] font-black text-slate-400 bg-slate-800 border border-white/10 px-2 py-1 rounded-lg uppercase tracking-wider">
                                {{ $log->action }}
                            </span>
                            @endif
                        </td>

                        <!-- Description -->
                        <td class="px-6 py-4.5">
                            <span class="text-slate-300 font-medium text-xs line-clamp-2" title="{{ $log->description }}">
                                {{ $log->description }}
                            </span>
                        </td>

                        <!-- IP & User Agent -->
                        <td class="px-6 py-4.5 max-w-[200px]">
                            <div class="flex flex-col gap-0.5">
                                <div class="flex items-center gap-1 text-[10px] text-slate-400 font-semibold">
                                    <i data-lucide="globe" class="w-3 h-3 text-slate-500"></i>
                                    {{ $log->ip_address }}
                                </div>
                                <div class="text-[9px] text-slate-600 truncate font-medium" title="{{ $log->user_agent }}">
                                    {{ $log->user_agent }}
                                </div>
                            </div>
                        </td>

                        <!-- Action Button -->
                        <td class="px-6 py-4.5 text-right whitespace-nowrap">
                            @if($log->action === 'UPDATED' && !empty($log->properties))
                            <button onclick="inspectActivity({{ $log->id }}, @json($log->properties), @json($log->user->name ?? 'System'), @json($log->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i:s')))"
                                    class="text-xs text-indigo-400 bg-indigo-500/5 hover:bg-indigo-500/10 border border-indigo-500/10 hover:border-indigo-500/30 px-3.5 py-1.5 rounded-xl font-bold transition-all inline-flex items-center gap-1.5 group-hover:shadow-lg group-hover:shadow-indigo-500/5">
                                <i data-lucide="eye" class="w-3.5 h-3.5 text-indigo-400 group-hover:scale-110 transition-transform"></i>
                                Lihat Perubahan
                            </button>
                            @else
                            <span class="text-[10px] text-slate-600 font-bold bg-slate-800/30 border border-white/[0.02] px-3.5 py-1.5 rounded-xl cursor-not-allowed select-none">
                                No Property Changes
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-24 text-center">
                            <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                <div class="w-14 h-14 rounded-2xl bg-slate-800/80 border border-white/5 flex items-center justify-center text-slate-500 mb-4 shadow-inner">
                                    <i data-lucide="shield-alert" class="w-7 h-7"></i>
                                </div>
                                <h4 class="text-white font-bold text-sm">Tidak Ada Log Ditemukan</h4>
                                <p class="text-slate-500 text-xs mt-2 leading-relaxed">
                                    Tidak ada catatan log aktivitas yang cocok dengan kriteria filter Anda atau data log belum tercatat.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-white/5 bg-slate-900/30 flex items-center justify-center">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Premium Inspection Glass Modal -->
<div id="inspectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4 transition-all duration-300">
    <div class="bg-[#10172a] border border-white/10 w-full max-w-3xl rounded-3xl flex flex-col max-h-[85vh] overflow-hidden shadow-2xl transform scale-95 opacity-0 transition-all duration-300" id="inspectModalContent">
        <!-- Modal Header -->
        <div class="p-6 border-b border-white/5 flex justify-between items-center bg-[#1e293b]/70 backdrop-blur-md">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center">
                    <i data-lucide="history" class="w-5 h-5 text-indigo-400"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white uppercase tracking-wider">Perbandingan Perubahan Data</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        Membandingkan nilai sebelum dan sesudah data diperbarui.
                    </p>
                </div>
            </div>
            <button onclick="closeInspectModal()" class="w-8 h-8 rounded-xl bg-slate-800/80 border border-white/5 flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-700 transition-all">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Modal Body (Custom Scrollable) -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-[#0f172a]/30 custom-scroll">
            <!-- Operator Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-slate-800/40 border border-white/5">
                    <div class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Operator / Pelaku</div>
                    <div class="text-white font-bold text-sm" id="inspectOperator">-</div>
                </div>
                <div class="p-4 rounded-2xl bg-slate-800/40 border border-white/5">
                    <div class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Waktu Kejadian</div>
                    <div class="text-white font-bold text-sm" id="inspectTime">-</div>
                </div>
            </div>

            <!-- Comparison Table Card -->
            <div class="rounded-2xl border border-white/5 overflow-hidden">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-800/60 text-slate-400 font-bold uppercase tracking-widest text-[9px] border-b border-white/5">
                            <th class="px-5 py-3.5 w-1/4">Nama Field</th>
                            <th class="px-5 py-3.5 w-3/8 text-rose-400 bg-rose-500/[0.02]">Nilai Lama (Sebelum)</th>
                            <th class="px-5 py-3.5 w-3/8 text-emerald-400 bg-emerald-500/[0.02]">Nilai Baru (Sesudah)</th>
                        </tr>
                    </thead>
                    <tbody id="inspectCompareBody" class="divide-y divide-white/5 bg-slate-900/20 font-medium">
                        <!-- Dynamic content here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-6 border-t border-white/5 bg-[#1e293b]/70 backdrop-blur-md flex justify-end gap-3 shrink-0">
            <button onclick="closeInspectModal()" class="bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs uppercase tracking-widest px-6 py-3 rounded-xl border border-white/5 transition-all">
                Tutup Rincian
            </button>
        </div>
    </div>
</div>

<script>
    function inspectActivity(logId, properties, operator, timestamp) {
        document.getElementById('inspectOperator').innerText = operator;
        document.getElementById('inspectTime').innerText = timestamp;

        const tbody = document.getElementById('inspectCompareBody');
        tbody.innerHTML = '';

        if (properties && properties.old && properties.new) {
            const oldData = properties.old;
            const newData = properties.new;
            const keys = Object.keys(newData);

            if (keys.length === 0) {
                tbody.innerHTML = `<tr><td colspan="3" class="px-5 py-8 text-center text-slate-500 italic">Tidak ada perubahan field data terdeteksi.</td></tr>`;
            } else {
                keys.forEach(key => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-white/[0.01] transition-colors';

                    // Format Key Name beautifully (capitalize, replace underscores)
                    const formattedKey = key
                        .split('_')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                        .join(' ');

                    const cellKey = document.createElement('td');
                    cellKey.className = 'px-5 py-4 font-bold text-white border-r border-white/5 bg-slate-800/10';
                    cellKey.innerHTML = `<span class="bg-slate-800 px-2 py-1 rounded text-slate-300 text-[10px] uppercase font-bold tracking-wider">${formattedKey}</span>`;

                    // Old Value
                    const cellOld = document.createElement('td');
                    cellOld.className = 'px-5 py-4 text-rose-300 bg-rose-500/[0.01] border-r border-white/5 whitespace-pre-wrap break-all font-mono text-[11px]';
                    let oldVal = oldData[key];
                    if (oldVal === null || oldVal === undefined) {
                        cellOld.innerHTML = `<span class="text-slate-600 italic font-sans font-bold">NULL / KOSONG</span>`;
                    } else if (typeof oldVal === 'object') {
                        cellOld.innerText = JSON.stringify(oldVal, null, 2);
                    } else {
                        cellOld.innerText = oldVal;
                    }

                    // New Value
                    const cellNew = document.createElement('td');
                    cellNew.className = 'px-5 py-4 text-emerald-300 bg-emerald-500/[0.01] whitespace-pre-wrap break-all font-mono text-[11px]';
                    let newVal = newData[key];
                    if (newVal === null || newVal === undefined) {
                        cellNew.innerHTML = `<span class="text-slate-600 italic font-sans font-bold">NULL / KOSONG</span>`;
                    } else if (typeof newVal === 'object') {
                        cellNew.innerText = JSON.stringify(newVal, null, 2);
                    } else {
                        cellNew.innerText = newVal;
                    }

                    row.appendChild(cellKey);
                    row.appendChild(cellOld);
                    row.appendChild(cellNew);
                    tbody.appendChild(row);
                });
            }
        } else {
            tbody.innerHTML = `<tr><td colspan="3" class="px-5 py-8 text-center text-slate-500 italic">Data properti perubahan tidak tersedia.</td></tr>`;
        }

        // Show Modal with animation
        const modal = document.getElementById('inspectModal');
        const modalContent = document.getElementById('inspectModalContent');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    function closeInspectModal() {
        const modal = document.getElementById('inspectModal');
        const modalContent = document.getElementById('inspectModalContent');
        
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Close modal when clicking outside content
    document.getElementById('inspectModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeInspectModal();
        }
    });
</script>
@endsection
