@extends('layouts.app', ['title' => 'Shop Floor Dashboard'])

@section('content')
<div class="space-y-6">
    <!-- Header with active time -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white uppercase tracking-tight">Shop Floor Control</h3>
            <p class="text-slate-500 text-xs font-bold uppercase tracking-[0.2em] mt-1" id="liveTime">{{ now()->format('l, d F Y | H:i:s') }}</p>
        </div>
    </div>

    <!-- Active Stages Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Sidebar: List of Active Jobs -->
        <div class="lg:col-span-3 space-y-4">
            <h3 class="text-slate-500 text-[10px] font-black uppercase tracking-[0.3em] px-2">Job List</h3>
            <div class="space-y-3">
                @forelse($stages as $stage)
                <div onclick="selectJob({{ $stage->id }})" class="glass-card p-4 rounded-2xl border border-white/5 cursor-pointer transition-all hover:bg-white/5 {{ $loop->first ? 'border-indigo-500/50 bg-indigo-500/5' : '' }} job-card" id="job-{{ $stage->id }}">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">{{ $stage->workOrder->wo_number }}</span>
                        <span class="text-[8px] font-black px-1.5 py-0.5 rounded {{ $stage->workOrder->status == 'in_progress' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-indigo-500/20 text-indigo-400' }} uppercase">{{ str_replace('_', ' ', $stage->workOrder->status) }}</span>
                    </div>
                    <h4 class="text-white text-xs font-bold truncate">{{ $stage->workOrder->customer->name ?? 'Internal' }}</h4>
                    <p class="text-slate-500 text-[10px] mt-1">{{ $stage->name }}</p>
                </div>
                @empty
                <div class="p-8 text-center glass-card rounded-2xl border border-white/5">
                    <i data-lucide="inbox" class="w-8 h-8 text-slate-700 mx-auto mb-2"></i>
                    <p class="text-slate-600 text-[10px] font-bold uppercase">No active jobs</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Main Panel: Job Detail & Controls -->
        <div class="lg:col-span-9 space-y-6">
            @if(count($stages) > 0)
                @php $active = $stages[0]; @endphp
                <div id="jobDetailContainer">
                    <div class="glass-card p-8 rounded-[2.5rem] border border-white/5 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-600/5 blur-[100px] rounded-full -mr-48 -mt-48"></div>
                        
                        <!-- Top Info -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 relative z-10">
                            <div>
                                <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest mb-1">Machine</p>
                                <h4 class="text-white font-black">{{ $active->machine->name ?? 'Manual' }}</h4>
                                <span class="text-emerald-400 text-[9px] font-bold uppercase tracking-tighter">Status: Running</span>
                            </div>
                            <div>
                                <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest mb-1">Current Task</p>
                                <h4 class="text-white font-black uppercase">{{ $active->name }}</h4>
                                <span class="text-slate-500 text-[9px] font-bold uppercase">Stage {{ $active->sequence }} of {{ $active->workOrder->stages->count() }}</span>
                            </div>
                            <div>
                                <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest mb-1">Target Quantity</p>
                                <h4 class="text-indigo-400 font-black text-xl">{{ number_format($active->workOrder->products->sum('quantity'), 0) }}</h4>
                                <span class="text-slate-500 text-[9px] font-bold uppercase">{{ $active->workOrder->products->first()->item->unit->name ?? 'PCS' }}</span>
                            </div>
                            <div class="text-right flex flex-col justify-center gap-2">
                                @if($active->status == 'pending')
                                <form action="{{ route('shop_floor.stage.start', $active->id) }}" method="POST">
                                    @csrf
                                    <button id="startJobButton" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-indigo-500/20 flex items-center justify-center gap-2 opacity-50 cursor-not-allowed" disabled>
                                        <i data-lucide="play" class="w-4 h-4"></i> START JOB
                                    </button>
                                </form>
                                @else
                                <div class="grid grid-cols-2 gap-2">
                                    <button onclick="openDowntimeModal({{ $active->machine_id ?? 'null' }})" class="bg-rose-600/20 hover:bg-rose-600/30 text-rose-400 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all border border-rose-500/20">
                                        STOP
                                    </button>
                                    <button onclick="openOutputModal({{ $active->id }})" class="bg-emerald-600 hover:bg-emerald-500 text-white py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg shadow-emerald-500/20">
                                        REPORT LHP
                                    </button>
                                </div>
                                <form action="{{ route('shop_floor.stage.finish', $active->id) }}" method="POST" class="mt-2">
                                    @csrf
                                    <button class="w-full bg-slate-800 hover:bg-slate-700 text-white py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all border border-white/5">
                                        FINISH STAGE
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>

                        <!-- Technical Steps (Master Step Machine) -->
                        <div class="mt-12">
                            <h5 class="text-white text-[10px] font-black uppercase tracking-widest mb-6 flex items-center gap-2">
                                <i data-lucide="list-checks" class="w-4 h-4 text-indigo-400"></i> Technical SOP Steps
                            </h5>
                            <div class="flex flex-wrap gap-4">
                                @if($active->machine && count($active->machine->steps) > 0)
                                    @foreach($active->machine->steps as $step)
                                    <div class="flex items-center gap-3 bg-white/5 border border-white/5 px-6 py-4 rounded-2xl min-w-[180px]">
                                        <span class="w-8 h-8 rounded-full bg-indigo-600/20 text-indigo-400 flex items-center justify-center text-[10px] font-black border border-indigo-500/20">{{ $step->sequence }}</span>
                                        <span class="text-xs font-bold text-white uppercase">{{ $step->step_name }}</span>
                                    </div>
                                    @if(!$loop->last)
                                    <div class="flex items-center text-slate-700">
                                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                    </div>
                                    @endif
                                    @endforeach
                                @else
                                    <p class="text-slate-500 text-[10px] italic">No technical steps defined for this machine.</p>
                                @endif
                            </div>
                        </div>

                        <!-- Material Checklist -->
                        <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="bg-slate-900/50 rounded-3xl p-6 border border-white/5">
                                <h5 class="text-white text-[10px] font-black uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <i data-lucide="package" class="w-4 h-4 text-amber-400"></i> Materials Checklist
                                </h5>
                                <div class="space-y-3">
                                    @foreach($active->items as $item)
                                    <div class="flex items-center gap-3 p-3 bg-white/[0.02] rounded-xl border border-white/5">
                                        <input type="checkbox" class="material-checkbox w-4 h-4 rounded bg-slate-800 border-white/10 text-indigo-600 focus:ring-indigo-500" onchange="validateChecklist()">
                                        <div class="flex-1">
                                            <p class="text-xs font-bold text-white">{{ $item->item->name }}</p>
                                            <p class="text-[9px] text-slate-500 uppercase">{{ number_format($item->quantity_total, 2) }} {{ $item->item->unit->name }}</p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <a href="{{ route('mutations.request.index', ['work_order_id' => $active->work_order_id]) }}" class="mt-6 w-full py-3 bg-amber-600/10 hover:bg-amber-600/20 text-amber-500 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all border border-amber-500/10 flex items-center justify-center gap-2">
                                    <i data-lucide="shopping-cart" class="w-4 h-4"></i> Request to Warehouse
                                </a>
                            </div>

                            <div class="bg-slate-900/50 rounded-3xl p-6 border border-white/5">
                                <h5 class="text-white text-[10px] font-black uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <i data-lucide="history" class="w-4 h-4 text-emerald-400"></i> Production Log (Today)
                                </h5>
                                <div class="space-y-2 overflow-y-auto max-h-[300px] pr-2 custom-scroll">
                                    @foreach($active->outputs as $out)
                                    <div class="p-3 bg-emerald-500/5 rounded-xl border border-emerald-500/10 flex justify-between items-center">
                                        <div>
                                            <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest">Good: {{ number_format($out->quantity_good, 0) }}</p>
                                            <p class="text-[8px] text-slate-500">{{ $out->created_at->format('H:i') }} | {{ $out->operator->name }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-rose-400 text-[10px] font-black">Reject: {{ number_format($out->quantity_reject, 0) }}</span>
                                        </div>
                                    </div>
                                    @endforeach
                                    @if(count($active->outputs) == 0)
                                    <p class="text-center text-slate-600 text-[10px] py-8 italic uppercase font-bold">No outputs recorded yet</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="glass-card p-20 rounded-[3rem] border border-white/5 text-center flex flex-col items-center justify-center">
                    <div class="w-32 h-32 bg-slate-800/50 rounded-full flex items-center justify-center mb-8 text-slate-600 border border-white/5">
                        <i data-lucide="coffee" class="w-16 h-16"></i>
                    </div>
                    <h2 class="text-2xl font-black text-white uppercase tracking-tight">Time for a break!</h2>
                    <p class="text-slate-500 max-w-md mx-auto mt-4 font-medium italic">No production schedules are currently assigned to this line. Please wait for PPIC to release new Work Orders.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- LHP Output Modal -->
<div id="outputModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden">
        <div class="p-8 border-b border-white/5 bg-slate-800/50">
            <h3 class="text-xl font-black text-white uppercase tracking-tight">Report Output (LHP)</h3>
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">Record your production results</p>
        </div>
        <form id="outputForm" action="" method="POST" class="p-8 space-y-6">
            @csrf
            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Good Quantity</label>
                <input type="number" name="quantity_good" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 text-2xl font-black text-emerald-400 focus:border-emerald-500 outline-none transition-all" value="0" required>
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Reject Quantity</label>
                <input type="number" name="quantity_reject" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 text-2xl font-black text-rose-400 focus:border-rose-500 outline-none transition-all" value="0" required>
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Notes / Constraints</label>
                <textarea name="notes" rows="2" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 text-sm text-white focus:border-indigo-500 outline-none transition-all" placeholder="Enter notes if any..."></textarea>
            </div>
            <div class="flex gap-4 pt-4">
                <button type="button" onclick="closeOutputModal()" class="flex-1 py-4 text-slate-400 font-black uppercase text-[10px] tracking-widest">Cancel</button>
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20 transition-all">Submit Report</button>
            </div>
        </form>
    </div>
</div>

<!-- Downtime Modal -->
<div id="downtimeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden">
        <div class="p-8 border-b border-white/5 bg-slate-800/50">
            <h3 class="text-xl font-black text-white uppercase tracking-tight">Machine Downtime</h3>
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">Report machine technical issues</p>
        </div>
        <form id="downtimeForm" action="" method="POST" class="p-8 space-y-6">
            @csrf
            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Status Baru</label>
                <select name="status" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 text-white font-black uppercase tracking-widest focus:border-rose-500 outline-none transition-all">
                    <option value="DOWN">STOP / BREAKDOWN</option>
                    <option value="MAINTENANCE">MAINTENANCE</option>
                    <option value="IDLE">TUNGGU BAHAN / IDLE</option>
                    <option value="RUNNING">KEMBALI BERJALAN</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Reason / Description</label>
                <textarea name="reason" rows="3" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-4 px-6 text-sm text-white focus:border-rose-500 outline-none transition-all" placeholder="Explain what happened..." required></textarea>
            </div>
            <div class="flex gap-4 pt-4">
                <button type="button" onclick="closeDowntimeModal()" class="flex-1 py-4 text-slate-400 font-black uppercase text-[10px] tracking-widest">Cancel</button>
                <button type="submit" class="flex-1 bg-rose-600 hover:bg-rose-500 text-white py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-rose-500/20 transition-all">Submit Issue</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Live clock
    setInterval(() => {
        const now = new Date();
        document.getElementById('liveTime').innerText = now.toLocaleDateString('en-US', { 
            weekday: 'long', 
            day: 'numeric', 
            month: 'long', 
            year: 'numeric' 
        }) + ' | ' + now.toLocaleTimeString('en-US', { hour12: false });
    }, 1000);

    function openOutputModal(id) {
        document.getElementById('outputForm').action = `/shop-floor/output/report/${id}`;
        document.getElementById('outputModal').classList.remove('hidden');
    }

    function closeOutputModal() {
        document.getElementById('outputModal').classList.add('hidden');
    }

    function openDowntimeModal(machineId) {
        if (!machineId) return;
        document.getElementById('downtimeForm').action = `/shop-floor/machine/status/${machineId}`;
        document.getElementById('downtimeModal').classList.remove('hidden');
    }

    function closeDowntimeModal() {
        document.getElementById('downtimeModal').classList.add('hidden');
    }

    function selectJob(id) {
        // This would ideally be an AJAX call to refresh the jobDetailContainer
        // For demonstration, we'll just reload the page or navigate if using proper routing
        // For now, let's keep it simple.
    }

    function validateChecklist() {
        const checkboxes = document.querySelectorAll('.material-checkbox');
        const startButton = document.getElementById('startJobButton');
        
        if (!startButton) return;

        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        if (allChecked || checkboxes.length === 0) {
            startButton.disabled = false;
            startButton.classList.remove('opacity-50', 'cursor-not-allowed');
            startButton.classList.add('hover:scale-[1.02]', 'active:scale-95');
        } else {
            startButton.disabled = true;
            startButton.classList.add('opacity-50', 'cursor-not-allowed');
            startButton.classList.remove('hover:scale-[1.02]', 'active:scale-95');
        }
    }

    // Initial check on load
    document.addEventListener('DOMContentLoaded', validateChecklist);
</script>

<style>
    .modal-scroll::-webkit-scrollbar { width: 4px; }
    .modal-scroll::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); }
    .modal-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.05); border-radius: 10px; }
    
    .job-card.active {
        @apply border-indigo-500/50 bg-indigo-500/5;
    }
</style>
@endsection
