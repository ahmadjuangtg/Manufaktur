@extends('layouts.app', ['title' => 'Monitoring Mutasi Gudang'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white uppercase tracking-tight">Monitor & Eksekusi Mutasi</h3>
            <p class="text-slate-400 text-sm italic">Lacak status pengiriman dan penerimaan barang antar gudang</p>
        </div>
    </div>

    <div class="glass-card rounded-[2rem] overflow-hidden border border-white/5 bg-slate-900/20">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] border-b border-white/5">
                    <th class="px-8 py-5">Ref No</th>
                    <th class="px-8 py-5">Rute Gudang</th>
                    <th class="px-8 py-5 text-center">Status</th>
                    <th class="px-8 py-5">Progress</th>
                    <th class="px-8 py-5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $m)
                <tr class="hover:bg-white/5 transition-colors">
                    <td class="px-8 py-5">
                        <div class="text-xs text-white font-bold">{{ $m->reference_no }}</div>
                        <div class="text-[10px] text-slate-500 font-medium mt-1">{{ $m->created_at->format('d M Y') }}</div>
                        @if($m->workOrder)
                        <div class="mt-2">
                            <span class="px-2 py-0.5 bg-indigo-500/10 text-indigo-400 text-[8px] font-black uppercase tracking-widest rounded border border-indigo-500/10">WO: {{ $m->workOrder->wo_number }}</span>
                        </div>
                        @endif
                    </td>
                    <td class="px-8 py-5">
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-tight">{{ $m->fromWarehouse->name }}</span>
                            <i data-lucide="arrow-right" class="w-3 h-3 text-slate-700"></i>
                            <span class="text-[10px] font-black text-white uppercase tracking-tight">{{ $m->toWarehouse->name }}</span>
                        </div>
                    </td>
                    <td class="px-8 py-5 text-center">
                        @php
                            $statusClasses = [
                                'PENDING' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                'APPROVED' => 'bg-indigo-500/10 text-indigo-500 border-indigo-500/20',
                                'SENDING' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                                'COMPLETED' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                'REJECTED' => 'bg-rose-500/10 text-rose-500 border-rose-500/20',
                            ];
                        @endphp
                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $statusClasses[$m->status] }}">
                            {{ $m->status }}
                        </span>
                    </td>
                    <td class="px-8 py-5">
                        @php
                            $step = 0;
                            if($m->status == 'APPROVED') $step = 1;
                            if($m->status == 'SENDING') $step = 2;
                            if($m->status == 'COMPLETED') $step = 3;
                            if($m->status == 'REJECTED') $step = 0;
                        @endphp
                        <div class="flex items-center gap-1.5">
                            @for($i=1; $i<=3; $i++)
                            <div class="h-1.5 w-6 rounded-full {{ $i <= $step ? 'bg-indigo-500' : 'bg-slate-800' }} {{ $m->status == 'REJECTED' ? 'bg-rose-500/20' : '' }}"></div>
                            @endfor
                        </div>
                    </td>
                    <td class="px-8 py-5 text-right space-x-2">
                        @if($m->status == 'APPROVED')
                        <form action="{{ route('mutations.send', $m->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-[9px] font-black uppercase tracking-widest flex items-center gap-1 transition-all">
                                <i data-lucide="truck" class="w-3 h-3"></i> Kirim Barang
                            </button>
                        </form>
                        @elseif($m->status == 'SENDING')
                        <form action="{{ route('mutations.receive', $m->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-[9px] font-black uppercase tracking-widest flex items-center gap-1 transition-all">
                                <i data-lucide="check" class="w-3 h-3"></i> Terima Barang
                            </button>
                        </form>
                        @endif
                        <button onclick="viewDetails({{ $m->id }})" class="p-2 bg-slate-800 rounded-lg text-slate-400 hover:text-white transition-all"><i data-lucide="eye" class="w-4 h-4"></i></button>
                        <button onclick="window.print()" class="p-2 bg-slate-800 rounded-lg text-slate-400 hover:text-white transition-all"><i data-lucide="printer" class="w-4 h-4"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-8 py-20 text-center text-slate-500">Belum ada mutasi terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Details Modal (Same as Request but with Audit Trail) -->
<div id="detailsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-4xl rounded-[2.5rem] overflow-hidden shadow-2xl">
        <div class="p-8 border-b border-white/5 flex justify-between items-center bg-slate-800/30">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-600/20 rounded-2xl flex items-center justify-center text-indigo-400">
                    <i data-lucide="info" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 id="detRef" class="text-xl font-black text-white tracking-tight">Status Mutasi</h3>
                    <p id="detStatus" class="text-xs text-indigo-400 font-bold uppercase tracking-widest mt-0.5"></p>
                </div>
            </div>
            <button onclick="closeDetails()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:text-white transition-all"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <div class="grid grid-cols-12">
            <!-- Items Side -->
            <div class="col-span-8 p-10 bg-[#0f172a]/30 space-y-8">
                <div class="grid grid-cols-2 gap-8">
                    <div class="p-6 bg-slate-800/40 rounded-2xl border border-white/5">
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-2">Dari Gudang</p>
                        <p id="detFrom" class="text-sm font-bold text-white"></p>
                    </div>
                    <div class="p-6 bg-slate-800/40 rounded-2xl border border-white/5">
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-2">Ke Gudang</p>
                        <p id="detTo" class="text-sm font-bold text-white"></p>
                    </div>
                </div>

                <div id="detWOWrapper" class="p-6 bg-indigo-500/5 rounded-2xl border border-indigo-500/10 hidden">
                    <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-2">Terkait Work Order</p>
                    <p id="detWO" class="text-sm font-black text-white"></p>
                </div>
                
                <div>
                    <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] mb-4 ml-1">Daftar Item</h4>
                    <div id="detItems" class="space-y-2 max-h-60 overflow-y-auto custom-scroll pr-2"></div>
                </div>
            </div>

            <!-- Audit Trail Side -->
            <div class="col-span-4 p-10 bg-slate-800/50 border-l border-white/5 space-y-8">
                <h4 class="text-[10px] font-black text-white uppercase tracking-[0.3em] mb-6">Log Aktivitas</h4>
                <div class="space-y-6">
                    <div id="logReq" class="flex gap-4">
                        <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center shrink-0 border border-white/5">
                            <i data-lucide="plus" class="w-4 h-4 text-slate-500"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-white font-bold">Request Dibuat</p>
                            <p id="timeReq" class="text-[8px] text-slate-500 font-bold uppercase tracking-widest"></p>
                        </div>
                    </div>
                    <div id="logApp" class="flex gap-4 opacity-30">
                        <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center shrink-0 border border-white/5">
                            <i data-lucide="check" class="w-4 h-4 text-slate-500"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-white font-bold">Disetujui</p>
                            <p id="timeApp" class="text-[8px] text-slate-500 font-bold uppercase tracking-widest"></p>
                        </div>
                    </div>
                    <div id="logSent" class="flex gap-4 opacity-30">
                        <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center shrink-0 border border-white/5">
                            <i data-lucide="truck" class="w-4 h-4 text-slate-500"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-white font-bold">Dikirim</p>
                            <p id="timeSent" class="text-[8px] text-slate-500 font-bold uppercase tracking-widest"></p>
                        </div>
                    </div>
                    <div id="logRec" class="flex gap-4 opacity-30">
                        <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center shrink-0 border border-white/5">
                            <i data-lucide="package-check" class="w-4 h-4 text-slate-500"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-white font-bold">Diterima</p>
                            <p id="timeRec" class="text-[8px] text-slate-500 font-bold uppercase tracking-widest"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function viewDetails(id) {
        fetch(`/transactions/mutations/get-details/${id}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('detRef').innerText = data.reference_no;
                document.getElementById('detStatus').innerText = data.status;
                document.getElementById('detFrom').innerText = data.from_warehouse.name;
                document.getElementById('detTo').innerText = data.to_warehouse.name;
                
                const detItems = document.getElementById('detItems');
                detItems.innerHTML = '';
                data.details.forEach(d => {
                    detItems.innerHTML += `
                        <div class="flex justify-between items-center p-4 bg-slate-800/20 rounded-xl border border-white/5">
                            <span class="text-xs text-white font-bold">${d.item.name}</span>
                            <span class="text-sm font-black text-indigo-400">${parseFloat(d.quantity)} <span class="text-[9px] uppercase text-slate-600 ml-1">${d.item.unit?.name || '-'}</span></span>
                        </div>
                    `;
                });

                if (data.work_order) {
                    document.getElementById('detWO').innerText = data.work_order.wo_number;
                    document.getElementById('detWOWrapper').classList.remove('hidden');
                } else {
                    document.getElementById('detWOWrapper').classList.add('hidden');
                }

                // Audit Trail
                document.getElementById('timeReq').innerText = new Date(data.created_at).toLocaleString();
                
                if (data.approved_at) {
                    document.getElementById('logApp').classList.remove('opacity-30');
                    document.getElementById('timeApp').innerText = new Date(data.approved_at).toLocaleString();
                }
                if (data.sent_at) {
                    document.getElementById('logSent').classList.remove('opacity-30');
                    document.getElementById('timeSent').innerText = new Date(data.sent_at).toLocaleString();
                }
                if (data.received_at) {
                    document.getElementById('logRec').classList.remove('opacity-30');
                    document.getElementById('timeRec').innerText = new Date(data.received_at).toLocaleString();
                }

                document.getElementById('detailsModal').classList.remove('hidden');
                lucide.createIcons();
            });
    }

    function closeDetails() {
        document.getElementById('detailsModal').classList.add('hidden');
        // Reset audit trail opacity
        ['logApp', 'logSent', 'logRec'].forEach(id => document.getElementById(id).classList.add('opacity-30'));
    }
</script>
@endsection
