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
                                    'pending' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
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
                                <button class="p-2 bg-slate-800 hover:bg-indigo-600 rounded-xl text-slate-400 hover:text-white transition-all shadow-lg group-hover:scale-110" title="View Detail">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                                
                                @if($wo->status === 'pending')
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
                                <form action="{{ route('production.work_orders.update_status', $wo->id) }}" method="POST" onsubmit="return confirm('Cancel this WO?')">
                                    @csrf
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="p-2 bg-rose-600/10 hover:bg-rose-600 rounded-xl text-rose-500 hover:text-white transition-all shadow-lg" title="Cancel WO">
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
@endsection
