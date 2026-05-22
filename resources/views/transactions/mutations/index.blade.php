@extends('layouts.app', ['title' => 'Monitoring Mutasi Gudang'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8">
        <div>
            <h3 class="text-xl font-bold text-white uppercase tracking-tight">Monitor & Eksekusi Mutasi</h3>
            <p class="text-slate-400 text-sm italic">Lacak status pengiriman dan penerimaan barang antar gudang</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <form action="{{ route('mutations.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <select name="from_warehouse_id" onchange="this.form.submit()" class="bg-slate-800/50 border border-white/5 rounded-xl px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                    <option value="">Asal: Semua</option>
                    @foreach($warehouses as $w)
                    <option value="{{ $w->id }}" {{ request('from_warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>

                <select name="to_warehouse_id" onchange="this.form.submit()" class="bg-slate-800/50 border border-white/5 rounded-xl px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                    <option value="">Tujuan: Semua</option>
                    @foreach($allWarehouses as $w)
                    <option value="{{ $w->id }}" {{ request('to_warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()" class="bg-slate-800/50 border border-white/5 rounded-xl px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                    <option value="">Status: Semua</option>
                    <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>PENDING</option>
                    <option value="APPROVED" {{ request('status') == 'APPROVED' ? 'selected' : '' }}>APPROVED</option>
                    <option value="SENDING" {{ request('status') == 'SENDING' ? 'selected' : '' }}>SENDING</option>
                    <option value="COMPLETED" {{ request('status') == 'COMPLETED' ? 'selected' : '' }}>COMPLETED</option>
                    <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>REJECTED</option>
                </select>

                @if(request()->anyFilled(['from_warehouse_id', 'to_warehouse_id', 'status']))
                <a href="{{ route('mutations.index') }}" class="p-2 bg-rose-500/10 text-rose-500 rounded-xl hover:bg-rose-500/20 transition-all" title="Reset Filter">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
                @endif
            </form>
        </div>
    </div>

    <div class="glass-card rounded-[2rem] border border-white/5 bg-slate-900/20">
        <table class="w-full text-left">
            <thead class="sticky top-[-1.5rem] lg:top-[-2.5rem] z-20">
                <tr class="bg-[#1e293b] backdrop-blur-md text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] border-b border-white/5">
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
                    <td class="px-8 py-5 flex justify-end items-center gap-2">
                        @php
                            $isSuper = (Auth::user()->role->name ?? '') === 'Super Administrator';
                            $userWarehouseIds = Auth::user()->warehouses->pluck('id')->toArray();
                            $canSend = $isSuper || in_array($m->from_warehouse_id, $userWarehouseIds);
                            $canReceive = $isSuper || in_array($m->to_warehouse_id, $userWarehouseIds);

                            $totalRequested = $m->details->sum('quantity');
                            $totalSent = $m->deliveries->sum('quantity');
                            $hasRemainingToSend = $totalSent < $totalRequested;
                            $hasShipmentsInTransit = $m->deliveries->whereNull('received_at')->isNotEmpty();
                        @endphp
                        @if(($m->status == 'APPROVED' || $m->status == 'SENDING') && $hasRemainingToSend && $canSend)
                        <button onclick="openDeliveryModal({{ $m->id }})" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-[9px] font-black uppercase tracking-widest flex items-center gap-1 transition-all">
                            <i data-lucide="truck" class="w-3 h-3"></i> Kirim Barang
                        </button>
                        @endif
                        @if($m->status == 'SENDING' && $hasShipmentsInTransit && $canReceive)
                        <button onclick="openReceiveModal({{ $m->id }})" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-[9px] font-black uppercase tracking-widest flex items-center gap-1 transition-all">
                            <i data-lucide="check" class="w-3 h-3"></i> Terima Barang
                        </button>
                        @endif
                        <button onclick="viewDetails({{ $m->id }})" class="flex items-center justify-center w-8 h-8 bg-slate-800 rounded-lg text-slate-400 hover:text-white transition-all"><i data-lucide="eye" class="w-4 h-4"></i></button>
                        <a href="{{ route('mutations.print', $m->id) }}" target="_blank" class="flex items-center justify-center w-8 h-8 bg-slate-800 rounded-lg text-slate-400 hover:text-white transition-all"><i data-lucide="printer" class="w-4 h-4"></i></a>
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
                    <div id="detItems" class="space-y-2 max-h-60 overflow-y-auto custom-scroll pr-2 mb-6"></div>
                </div>

                <div id="detNoteWrapper" class="p-6 bg-slate-800/30 rounded-2xl border border-white/5 hidden mb-4">
                    <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-2">Catatan Pengirim</p>
                    <p id="detNote" class="text-xs text-slate-300 italic"></p>
                </div>

                <div id="detRejectWrapper" class="p-6 bg-rose-500/5 rounded-2xl border border-rose-500/10 hidden">
                    <p class="text-[9px] font-black text-rose-400 uppercase tracking-widest mb-2">Alasan Penolakan</p>
                    <p id="detReject" class="text-xs text-rose-300 font-bold"></p>
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

<!-- Delivery Shipment Modal -->
<div id="deliveryModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-2xl rounded-[2.5rem] overflow-hidden shadow-2xl">
        <div class="p-8 border-b border-white/5 flex justify-between items-center bg-slate-800/30">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-600/20 rounded-2xl flex items-center justify-center text-blue-400">
                    <i data-lucide="truck" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-white tracking-tight" id="deliveryModalTitle">Kirim Bahan Baku</h3>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-0.5">Input Kuantitas Barang Dikirim</p>
                </div>
            </div>
            <button onclick="closeDeliveryModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:text-white transition-all"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        
        <form id="deliveryForm" onsubmit="submitDelivery(event)" class="p-8 space-y-6">
            @csrf
            <input type="hidden" id="deliveryMutationId" name="mutation_id">
            
            <div class="space-y-4">
                <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Rincian Kuantitas Pengiriman</h4>
                <div id="deliveryItemsList" class="space-y-3 max-h-60 overflow-y-auto custom-scroll pr-2">
                    <!-- Dynamic Item Inputs -->
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-white/5 bg-slate-800/10 -mx-8 -mb-8 p-6">
                <button type="button" onclick="closeDeliveryModal()" class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-white/5 px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all">Batal</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i> Simpan Pengiriman
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Receive Shipment Modal -->
<div id="receiveModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-2xl rounded-[2.5rem] overflow-hidden shadow-2xl">
        <div class="p-8 border-b border-white/5 flex justify-between items-center bg-slate-800/30">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-600/20 rounded-2xl flex items-center justify-center text-emerald-400">
                    <i data-lucide="package-check" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-white tracking-tight">Verifikasi & Terima Pengiriman</h3>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-0.5">Input Fisik Barang Diterima</p>
                </div>
            </div>
            <button onclick="closeReceiveModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:text-white transition-all"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        
        <form id="receiveForm" onsubmit="submitReceive(event)" class="p-8 space-y-6">
            @csrf
            <input type="hidden" id="receiveMutationId" name="mutation_id">
            
            <div class="space-y-2">
                <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest block">Pilih Berkas Pengiriman</label>
                <select id="receiveShipmentNo" name="shipment_no" onchange="renderReceiveItems()" class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 text-xs font-bold text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                    <!-- Dynamic Options -->
                </select>
            </div>

            <div class="space-y-4">
                <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Verifikasi Fisik Item</h4>
                <div id="receiveItemsList" class="space-y-3 max-h-60 overflow-y-auto custom-scroll pr-2">
                    <!-- Dynamic Item Inputs -->
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-white/5 bg-slate-800/10 -mx-8 -mb-8 p-6">
                <button type="button" onclick="closeReceiveModal()" class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-white/5 px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all">Batal</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Penerimaan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentMutationData = null;

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
                    // Filter deliveries belonging to this item
                    const itemDeliveries = (data.deliveries || []).filter(del => del.item_id === d.item_id);
                    const totalShipped = itemDeliveries.reduce((sum, del) => sum + parseFloat(del.quantity || 0), 0);
                    const totalReceived = itemDeliveries.reduce((sum, del) => sum + parseFloat(del.received_quantity || 0), 0);
                    const rem = Math.max(0, parseFloat(d.quantity) - totalShipped);

                    detItems.innerHTML += `
                        <div class="p-4 bg-slate-800/20 rounded-xl border border-white/5 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-white font-bold">${d.item.name}</span>
                                <span class="text-xs font-black text-indigo-400">${parseFloat(d.quantity)} <span class="text-[9px] uppercase text-slate-600 ml-0.5">${d.item.unit?.name || '-'}</span></span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[9px] text-slate-400 border-t border-white/5 pt-2 font-bold uppercase tracking-wider">
                                <div>Kirim: <span class="text-blue-400 font-black">${totalShipped}</span></div>
                                <div>Terima: <span class="text-emerald-400 font-black">${totalReceived}</span></div>
                                <div class="ml-auto">Sisa: <span class="text-rose-400 font-black">${rem}</span></div>
                            </div>
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
                if (data.note) {
                    document.getElementById('detNote').innerText = data.note;
                    document.getElementById('detNoteWrapper').classList.remove('hidden');
                } else {
                    document.getElementById('detNoteWrapper').classList.add('hidden');
                }

                if (data.rejection_reason) {
                    document.getElementById('detReject').innerText = data.rejection_reason;
                    document.getElementById('detRejectWrapper').classList.remove('hidden');
                } else {
                    document.getElementById('detRejectWrapper').classList.add('hidden');
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

    function openDeliveryModal(id) {
        document.getElementById('deliveryMutationId').value = id;
        
        fetch(`/transactions/mutations/get-details/${id}`)
            .then(res => res.json())
            .then(data => {
                currentMutationData = data;
                document.getElementById('deliveryModalTitle').innerText = 'Kirim Bahan Baku - ' + data.reference_no;
                
                const itemsList = document.getElementById('deliveryItemsList');
                itemsList.innerHTML = '';
                
                data.details.forEach((d, index) => {
                    const itemDeliveries = (data.deliveries || []).filter(del => del.item_id === d.item_id);
                    const totalShipped = itemDeliveries.reduce((sum, del) => sum + parseFloat(del.quantity || 0), 0);
                    const rem = Math.max(0, parseFloat(d.quantity) - totalShipped);
                    
                    const itemName = d.item?.name || 'Item';
                    const itemCode = d.item?.code || '';
                    const unitName = d.item?.unit?.name || 'pcs';
                    
                    itemsList.innerHTML += `
                        <div class="p-4 bg-slate-800/30 rounded-2xl border border-white/5 space-y-3">
                            <div class="flex justify-between items-center text-xs">
                                <div>
                                    <span class="px-2 py-0.5 bg-white/5 text-slate-400 text-[9px] font-black rounded">${itemCode}</span>
                                    <strong class="text-white ml-2">${itemName}</strong>
                                </div>
                                <div class="text-slate-400 text-[10px]">
                                    Minta: <strong>${parseFloat(d.quantity)} ${unitName}</strong> | Sisa: <strong class="text-amber-400">${rem} ${unitName}</strong>
                                </div>
                            </div>
                            <input type="hidden" name="items[${index}][item_id]" value="${d.item_id}">
                            <div class="flex items-center gap-4">
                                <label class="text-[10px] text-slate-500 font-bold uppercase tracking-widest whitespace-nowrap">Qty Kirim Saat Ini</label>
                                <div class="relative w-full">
                                    <input type="number" step="0.01" min="0" max="${rem}" name="items[${index}][quantity]" value="${rem}" required class="w-full bg-slate-900/50 border border-white/10 rounded-xl px-4 py-2.5 text-xs font-bold text-white outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-600 uppercase">${unitName}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                document.getElementById('deliveryModal').classList.remove('hidden');
                lucide.createIcons();
            });
    }

    function closeDeliveryModal() {
        document.getElementById('deliveryModal').classList.add('hidden');
        document.getElementById('deliveryForm').reset();
        currentMutationData = null;
    }

    async function submitDelivery(event) {
        event.preventDefault();
        
        const id = document.getElementById('deliveryMutationId').value;
        const form = document.getElementById('deliveryForm');
        const formData = new FormData(form);

        const result = await Swal.fire({
            title: 'KONFIRMASI PENGIRIMAN',
            text: 'Apakah Anda yakin ingin mengirim barang dengan jumlah tersebut?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'YA, KIRIM SEKARANG',
            cancelButtonText: 'BATAL',
            confirmButtonColor: '#3b82f6'
        });

        if (!result.isConfirmed) return;

        try {
            const res = await fetch(`/transactions/mutations/deliver-partial/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire('BERHASIL', data.message, 'success').then(() => location.reload());
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            Swal.fire('ERROR', err.message, 'error');
        }
    }

    function openReceiveModal(id) {
        document.getElementById('receiveMutationId').value = id;
        
        fetch(`/transactions/mutations/get-details/${id}`)
            .then(res => res.json())
            .then(data => {
                currentMutationData = data;
                
                // Get unique shipment numbers where received_at is null
                const shipmentNoSelect = document.getElementById('receiveShipmentNo');
                shipmentNoSelect.innerHTML = '';
                
                const uniqueShipments = [];
                (data.deliveries || []).forEach(d => {
                    if (!d.received_at && d.shipment_no && !uniqueShipments.includes(d.shipment_no)) {
                        uniqueShipments.push(d.shipment_no);
                    }
                });

                if (uniqueShipments.length === 0) {
                    Swal.fire('INFO', 'Tidak ada pengiriman aktif (dalam perjalanan) untuk mutasi ini.', 'info');
                    return;
                }

                uniqueShipments.forEach(shipment => {
                    const opt = document.createElement('option');
                    opt.value = shipment;
                    opt.innerText = shipment;
                    shipmentNoSelect.appendChild(opt);
                });

                // Trigger render of items for the selected shipment
                renderReceiveItems();

                document.getElementById('receiveModal').classList.remove('hidden');
                lucide.createIcons();
            });
    }

    function renderReceiveItems() {
        const shipmentNo = document.getElementById('receiveShipmentNo').value;
        const itemsList = document.getElementById('receiveItemsList');
        itemsList.innerHTML = '';

        if (!currentMutationData || !shipmentNo) return;

        // Filter deliveries belonging to this shipment
        const deliveriesInShipment = currentMutationData.deliveries.filter(d => d.shipment_no === shipmentNo);

        deliveriesInShipment.forEach((d, index) => {
            const qtySent = parseFloat(d.quantity);
            const itemName = d.item?.name || 'Item';
            const unitName = d.item?.unit?.name || 'pcs';

            itemsList.innerHTML += `
                <div class="p-4 bg-slate-800/30 rounded-2xl border border-white/5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <p class="text-xs text-white font-bold">${itemName}</p>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Dikirim: <span class="text-slate-300 font-extrabold">${qtySent} ${unitName}</span></p>
                    </div>
                    <div class="w-full md:w-44 flex items-center gap-2">
                        <input type="hidden" name="items[${index}][item_id]" value="${d.item_id}">
                        <div class="relative w-full">
                            <input type="number" step="0.01" min="0" max="${qtySent}" name="items[${index}][quantity]" value="${qtySent}" class="w-full bg-slate-900/50 border border-white/10 rounded-xl pl-4 pr-12 py-2.5 text-xs font-bold text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all" required>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[9px] font-black text-slate-500 uppercase tracking-widest">${unitName}</span>
                        </div>
                    </div>
                </div>
            `;
        });
    }

    function closeReceiveModal() {
        document.getElementById('receiveModal').classList.add('hidden');
        document.getElementById('receiveForm').reset();
        currentMutationData = null;
    }

    async function submitReceive(event) {
        event.preventDefault();
        
        const id = document.getElementById('receiveMutationId').value;
        const form = document.getElementById('receiveForm');
        const formData = new FormData(form);

        const result = await Swal.fire({
            title: 'KONFIRMASI PENERIMAAN FISIK',
            text: 'Apakah Anda yakin jumlah fisik barang yang diterima sudah benar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'YA, SIMPAN SEKARANG',
            cancelButtonText: 'BATAL',
            confirmButtonColor: '#10b981'
        });

        if (!result.isConfirmed) return;

        try {
            const res = await fetch(`/transactions/mutations/receive-partial/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire('BERHASIL', data.message, 'success').then(() => location.reload());
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            Swal.fire('ERROR', err.message, 'error');
        }
    }
</script>
@endsection
