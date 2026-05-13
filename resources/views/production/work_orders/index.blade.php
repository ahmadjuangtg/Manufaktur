@extends('layouts.app', ['title' => 'Work Order Produksi'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-white tracking-tight">Work Orders</h1>
            <p class="text-slate-500 text-xs font-bold uppercase tracking-[0.2em] mt-1">Manage & Track Production Processes</p>
        </div>
        <a href="{{ route('production.work_orders.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black uppercase tracking-widest rounded-2xl transition-all shadow-lg shadow-indigo-500/20">
            <i data-lucide="plus" class="w-4 h-4"></i> Create Work Order
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="glass-card p-6 rounded-3xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i data-lucide="activity" class="w-16 h-16 text-indigo-500"></i>
            </div>
            <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Total WO</p>
            <h3 class="text-3xl font-black text-white mt-2">{{ $workOrders->count() }}</h3>
            <div class="mt-4 flex items-center gap-2 text-indigo-400 text-[10px] font-bold">
                <span class="flex h-1.5 w-1.5 rounded-full bg-indigo-400 animate-pulse"></span> Active Production
            </div>
        </div>
        <div class="glass-card p-6 rounded-3xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i data-lucide="clock" class="w-16 h-16 text-amber-500"></i>
            </div>
            <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Pending</p>
            <h3 class="text-3xl font-black text-white mt-2">{{ $workOrders->where('status', 'pending')->count() }}</h3>
            <div class="mt-4 flex items-center gap-2 text-amber-400 text-[10px] font-bold">
                Waiting for Start
            </div>
        </div>
        <div class="glass-card p-6 rounded-3xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i data-lucide="play-circle" class="w-16 h-16 text-emerald-500"></i>
            </div>
            <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">In Progress</p>
            <h3 class="text-3xl font-black text-white mt-2">{{ $workOrders->where('status', 'in_progress')->count() }}</h3>
            <div class="mt-4 flex items-center gap-2 text-emerald-400 text-[10px] font-bold">
                On Machines
            </div>
        </div>
        <div class="glass-card p-6 rounded-3xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i data-lucide="check-circle" class="w-16 h-16 text-blue-500"></i>
            </div>
            <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Completed Today</p>
            <h3 class="text-3xl font-black text-white mt-2">{{ $workOrders->where('status', 'completed')->where('updated_at', '>=', now()->startOfDay())->count() }}</h3>
            <div class="mt-4 flex items-center gap-2 text-blue-400 text-[10px] font-bold">
                Quality Checked
            </div>
        </div>
    </div>

    <!-- WO Table -->
    <div class="glass-card rounded-3xl overflow-hidden border border-white/5">
        <div class="p-6 border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-6 bg-indigo-500 rounded-full"></div>
                <h3 class="text-white font-black text-sm uppercase tracking-widest">Recent Work Orders</h3>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500"></i>
                    <input type="text" placeholder="Search WO..." class="bg-slate-900/50 border-white/5 rounded-xl pl-10 pr-4 py-2 text-xs text-white focus:ring-indigo-500 focus:border-indigo-500 w-64">
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/50">
                        <th class="p-6 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">WO Number</th>
                        <th class="p-6 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">Line</th>
                        <th class="p-6 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">Production Date</th>
                        <th class="p-6 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">Customer</th>
                        <th class="p-6 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">Products</th>
                        <th class="p-6 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">Status</th>
                        <th class="p-6 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($workOrders as $wo)
                    <tr class="hover:bg-white/[0.02] transition-colors group">
                        <td class="p-6">
                            <span class="text-white font-bold text-sm block">{{ $wo->wo_number }}</span>
                            <span class="text-slate-500 text-[10px] font-bold uppercase tracking-widest">{{ $wo->marketing ?: 'No Marketing' }}</span>
                        </td>
                        <td class="p-6">
                            <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-indigo-400 font-black text-xs">
                                {{ $wo->production_line }}
                            </div>
                        </td>
                        <td class="p-6 text-slate-300 text-xs font-bold">
                            {{ \Carbon\Carbon::parse($wo->production_date)->format('d M Y') }}
                        </td>
                        <td class="p-6 text-slate-300 text-xs font-bold">
                            {{ $wo->customer->name ?? '-' }}
                        </td>
                        <td class="p-6">
                            <div class="flex flex-col gap-1">
                                @foreach($wo->products as $prod)
                                <span class="text-white text-[10px] font-bold flex items-center gap-2">
                                    <span class="w-1 h-1 rounded-full bg-slate-500"></span>
                                    {{ $prod->item->name }} ({{ $prod->quantity }})
                                </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="p-6">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-slate-500/10 text-slate-500 border-slate-500/20',
                                    'ready_to_production' => 'bg-indigo-500/10 text-indigo-500 border-indigo-500/20',
                                    'in_progress' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                    'completed' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                                    'cancelled' => 'bg-rose-500/10 text-rose-500 border-rose-500/20'
                                ];
                                $color = $statusColors[$wo->status] ?? $statusColors['pending'];
                            @endphp
                            <span class="px-3 py-1 rounded-full border {{ $color }} text-[9px] font-black uppercase tracking-widest">
                                {{ str_replace('_', ' ', $wo->status) }}
                            </span>
                        </td>
                        <td class="p-6 text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick="viewDetail({{ $wo->toJson() }})" class="p-2 bg-slate-800 hover:bg-indigo-600 rounded-xl text-slate-400 hover:text-white transition-all shadow-lg group-hover:scale-110" title="View Detail">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                                
                                @if($wo->status === 'pending')
                                <form action="{{ route('production.work_orders.update_status', $wo->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="ready_to_production">
                                    <button type="submit" class="p-2 bg-indigo-600/10 hover:bg-indigo-600 rounded-xl text-indigo-500 hover:text-white transition-all shadow-lg" title="Mark as Ready">
                                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
 
                                @if($wo->status === 'ready_to_production')
                                <form action="{{ route('production.work_orders.update_status', $wo->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="in_progress">
                                    <button type="submit" class="p-2 bg-emerald-600/10 hover:bg-emerald-600 rounded-xl text-emerald-500 hover:text-white transition-all shadow-lg" title="Start Production">
                                        <i data-lucide="play" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif

                                @if($wo->status === 'in_progress')
                                <form action="{{ route('production.work_orders.update_status', $wo->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="p-2 bg-blue-600/10 hover:bg-blue-600 rounded-xl text-blue-500 hover:text-white transition-all shadow-lg" title="Complete Production">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                                
                                @if($wo->status !== 'completed' && $wo->status !== 'cancelled')
                                <form id="cancel-form-{{ $wo->id }}" action="{{ route('production.work_orders.update_status', $wo->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="button" onclick="confirmAction('Batalkan Work Order ini?', () => document.getElementById('cancel-form-{{ $wo->id }}').submit())" class="p-2 bg-rose-600/10 hover:bg-rose-600 rounded-xl text-rose-500 hover:text-white transition-all shadow-lg" title="Cancel WO">
                                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center">
                                    <i data-lucide="file-x" class="w-8 h-8 text-slate-600"></i>
                                </div>
                                <div>
                                    <p class="text-white font-bold">No Work Orders Found</p>
                                    <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mt-1">Start by creating a new production order</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-6 border-b border-white/5 flex justify-between items-center bg-[#1e293b]">
            <div>
                <h3 class="text-xl font-black text-white uppercase tracking-tight" id="detail_wo_number">WO-XXXX</h3>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">Work Order Detail Information</p>
            </div>
            <button onclick="closeDetailModal()" class="p-2 hover:bg-white/5 rounded-xl text-slate-400 transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-8 custom-scroll space-y-8">
            <!-- Basic Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="glass-card p-4 rounded-2xl">
                    <p class="text-[10px] text-slate-500 font-black uppercase tracking-widest mb-1">Customer</p>
                    <p class="text-sm font-bold text-white" id="detail_customer">-</p>
                </div>
                <div class="glass-card p-4 rounded-2xl">
                    <p class="text-[10px] text-slate-500 font-black uppercase tracking-widest mb-1">Production Date</p>
                    <p class="text-sm font-bold text-white" id="detail_date">-</p>
                </div>
                <div class="glass-card p-4 rounded-2xl">
                    <p class="text-[10px] text-slate-500 font-black uppercase tracking-widest mb-1">Status</p>
                    <div id="detail_status_container"></div>
                </div>
            </div>

            <!-- Products -->
            <div>
                <h4 class="text-xs font-black text-white uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i data-lucide="package" class="w-4 h-4 text-indigo-400"></i> Products to Produce
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="detail_products_list"></div>
            </div>

            <!-- Stages -->
            <div>
                <h4 class="text-xs font-black text-white uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i data-lucide="layers" class="w-4 h-4 text-emerald-400"></i> Production Stages
                </h4>
                <div class="space-y-4" id="detail_stages_list"></div>
            </div>
        </div>
    </div>
</div>

<script>
    function viewDetail(wo) {
        document.getElementById('detail_wo_number').innerText = wo.wo_number;
        document.getElementById('detail_customer').innerText = wo.customer ? wo.customer.name : '-';
        document.getElementById('detail_date').innerText = new Date(wo.production_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        
        // Status
        const statusColors = {
            'pending': 'bg-slate-500/10 text-slate-500 border-slate-500/20',
            'ready_to_production': 'bg-indigo-500/10 text-indigo-500 border-indigo-500/20',
            'in_progress': 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
            'completed': 'bg-blue-500/10 text-blue-500 border-blue-500/20',
            'cancelled': 'bg-rose-500/10 text-rose-500 border-rose-500/20'
        };
        const colorClass = statusColors[wo.status] || statusColors.pending;
        document.getElementById('detail_status_container').innerHTML = `
            <span class="px-3 py-1 rounded-full border ${colorClass} text-[9px] font-black uppercase tracking-widest">
                ${wo.status.replace('_', ' ')}
            </span>
        `;

        // Products
        let productsHtml = '';
        wo.products.forEach(p => {
            productsHtml += `
                <div class="bg-[#0f172a]/50 border border-white/5 p-4 rounded-2xl flex justify-between items-center">
                    <div>
                        <p class="text-white font-bold text-xs">${p.item.name}</p>
                        <p class="text-[9px] text-slate-500 font-bold mt-1 uppercase">${p.item.code || ''}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-indigo-400 font-black text-lg">${p.quantity}</p>
                        <p class="text-[9px] text-slate-500 font-bold uppercase">Units</p>
                    </div>
                </div>
            `;
        });
        document.getElementById('detail_products_list').innerHTML = productsHtml;

        // Stages
        let stagesHtml = '';
        wo.stages.forEach(s => {
            let itemsHtml = '';
            if (s.items) {
                s.items.forEach(i => {
                    const typeLabel = i.type.toLowerCase() === 'input' ? '(In)' : '(Out)';
                    const typeColor = i.type.toLowerCase() === 'input' ? 'text-emerald-400' : 'text-rose-400';
                    itemsHtml += `
                        <div class="flex justify-between items-center text-[10px] py-1 border-b border-white/[0.02]">
                            <span class="text-slate-400 font-bold">${i.item ? i.item.name : 'Unknown Item'}</span>
                            <span class="${typeColor} font-black">${typeLabel} ${i.quantity_total} ${i.item && i.item.unit ? i.item.unit.name : ''}</span>
                        </div>
                    `;
                });
            }

            stagesHtml += `
                <div class="glass-card rounded-2xl overflow-hidden">
                    <div class="p-4 bg-white/5 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-lg bg-indigo-600 flex items-center justify-center text-[10px] font-black text-white">${s.sequence}</span>
                            <span class="text-white font-black text-xs uppercase tracking-tight">${s.name}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mr-2">Machine:</span>
                            <span class="text-emerald-400 font-black text-[10px] uppercase">${s.machine ? s.machine.name : 'Manual'}</span>
                        </div>
                    </div>
                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-[9px] text-slate-500 font-black uppercase tracking-widest mb-2">Technical Duration</p>
                            <p class="text-white font-bold text-xs"><i data-lucide="clock" class="w-3 h-3 inline mr-1 text-indigo-400"></i> ${s.duration_hours || 0} Hours</p>
                        </div>
                        <div>
                            <p class="text-[9px] text-slate-500 font-black uppercase tracking-widest mb-2">Material Allocation</p>
                            <div class="space-y-1">${itemsHtml || '<span class="text-slate-600 italic text-[10px]">No materials</span>'}</div>
                        </div>
                    </div>
                </div>
            `;
        });
        document.getElementById('detail_stages_list').innerHTML = stagesHtml;

        document.getElementById('detailModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
    }
</script>
@endsection
