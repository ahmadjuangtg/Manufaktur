@extends('layouts.app', ['title' => 'Dashboard Analytics'])

@section('content')
<div class="space-y-8">
    <!-- Hero Section -->
    <div class="relative overflow-hidden glass-card p-10 rounded-2xl border border-white/10 shadow-2xl">
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-indigo-600/20 blur-[100px] rounded-full"></div>
        <div class="relative z-10 max-w-2xl">
            <h1 class="text-4xl font-extrabold text-white mb-4">Welcome back, <span class="text-indigo-400">{{ Auth::user()->name }}</span></h1>
            <p class="text-slate-400 text-lg">Operational overview for <span class="text-white font-bold">{{ date('F d, Y') }}</span>. System is running at optimal efficiency.</p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-card p-6 rounded-2xl stat-card-glow">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-indigo-500/10 rounded-xl text-indigo-500"><i data-lucide="package" class="w-6 h-6"></i></div>
                <span class="text-[10px] text-slate-500 font-black uppercase tracking-widest">Master</span>
            </div>
            <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Total SKU Barang</p>
            <h4 class="text-3xl font-black text-white mt-1">{{ number_format($stats['total_sku']) }}</h4>
        </div>
        <div class="glass-card p-6 rounded-2xl">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-emerald-500/10 rounded-xl text-emerald-500"><i data-lucide="database" class="w-6 h-6"></i></div>
                <span class="text-[10px] text-emerald-500 font-black uppercase tracking-widest">Real-time</span>
            </div>
            <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Total Stok Fisik</p>
            <h4 class="text-3xl font-black text-white mt-1">{{ number_format($stats['total_stock'], 2) }}</h4>
        </div>
        <div class="glass-card p-6 rounded-2xl bg-indigo-600/10 border-indigo-500/20">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-indigo-500/20 rounded-xl text-indigo-400"><i data-lucide="check-circle" class="w-6 h-6"></i></div>
                <span class="text-[10px] text-indigo-400 font-black uppercase tracking-widest">Berhasil</span>
            </div>
            <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Total WO Selesai</p>
            <h4 class="text-3xl font-black text-white mt-1">{{ number_format($stats['wo_stats']['completed']) }}</h4>
        </div>
    </div>

    <!-- Work Order Status Hub -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-3 glass-card p-8 rounded-3xl border border-white/5 bg-slate-900/40">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 bg-indigo-600/10 rounded-xl flex items-center justify-center text-indigo-500"><i data-lucide="clipboard-list" class="w-5 h-5"></i></div>
                <div>
                    <h4 class="text-xl font-black text-white uppercase tracking-tight">Ringkasan Status Produksi</h4>
                    <p class="text-[10px] text-slate-500 font-black uppercase tracking-[0.2em]">Pantauan Alur Kerja Work Order</p>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="p-6 bg-slate-950/40 rounded-2xl border border-white/5 text-center">
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Pending</p>
                    <span class="text-3xl font-black text-white">{{ $stats['wo_stats']['pending'] }}</span>
                </div>
                <div class="p-6 bg-slate-950/40 rounded-2xl border border-white/5 text-center">
                    <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-2">Ready</p>
                    <span class="text-3xl font-black text-emerald-500">{{ $stats['wo_stats']['ready'] }}</span>
                </div>
                <div class="p-6 bg-indigo-600/10 rounded-2xl border border-indigo-500/20 text-center animate-pulse">
                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">In Progress</p>
                    <span class="text-3xl font-black text-indigo-400">{{ $stats['wo_stats']['in_progress'] }}</span>
                </div>
                <div class="p-6 bg-slate-950/40 rounded-2xl border border-white/5 text-center">
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Completed</p>
                    <span class="text-3xl font-black text-white">{{ $stats['wo_stats']['completed'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Production & Stock Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 glass-card rounded-3xl border border-white/5 overflow-hidden flex flex-col">
            <div class="p-8 border-b border-white/5 flex justify-between items-center bg-slate-800/20">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/40"><i data-lucide="activity" class="w-5 h-5"></i></div>
                    <div>
                        <h4 class="text-xl font-black text-white uppercase tracking-tight">Live Production Monitor</h4>
                        <p class="text-[10px] text-slate-500 font-black uppercase tracking-[0.2em]">Active Stages & Processing Items</p>
                    </div>
                </div>
                <a href="{{ route('production.work_orders.index') }}" class="text-[10px] font-black text-indigo-400 hover:text-white transition-colors uppercase tracking-widest">Manage All <i data-lucide="chevron-right" class="w-4 h-4 inline-block"></i></a>
            </div>
            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-950/40 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                            <th class="px-8 py-4">Work Order</th>
                            <th class="px-8 py-4">Current Stage</th>
                            <th class="px-8 py-4">Products</th>
                            <th class="px-8 py-4 text-right">In-Process Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($stats['active_productions'] as $wo)
                        <tr class="group hover:bg-white/5 transition-colors">
                            <td class="px-8 py-6">
                                <span class="text-sm font-black text-indigo-400 tracking-tight">{{ $wo->wo_number }}</span>
                                <div class="text-[9px] text-slate-500 font-bold mt-1 uppercase">{{ $wo->marketing }}</div>
                            </td>
                            <td class="px-8 py-6">
                                @php $activeStage = $wo->stages->first(); @endphp
                                @if($activeStage)
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></div>
                                    <span class="text-sm font-bold text-white">{{ $activeStage->name }}</span>
                                </div>
                                <div class="text-[9px] text-slate-500 font-bold mt-1 uppercase">{{ $activeStage->machine->name ?? 'No Machine' }}</div>
                                @else
                                <span class="text-xs text-slate-500 italic">Stage initializing...</span>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                <div class="space-y-1">
                                    @foreach($wo->products as $p)
                                    <div class="text-[11px] font-bold text-slate-300">{{ $p->item->name ?? 'Unknown Item' }}</div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-8 py-6 text-right font-black text-white text-sm">
                                {{ number_format($wo->products->sum('quantity'), 2) }}
                                <div class="text-[9px] text-slate-500 font-bold uppercase">{{ $wo->products->first()->item->unit->name ?? 'Unit' }}</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center gap-4 opacity-50">
                                    <i data-lucide="inbox" class="w-12 h-12 text-slate-600"></i>
                                    <p class="text-sm font-bold text-slate-500 uppercase tracking-widest italic">No active production found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="glass-card rounded-3xl border border-white/5 flex flex-col min-h-[250px]">
            <div class="p-8 border-b border-white/5 bg-slate-800/20">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-600/10 rounded-xl flex items-center justify-center text-emerald-500"><i data-lucide="pie-chart" class="w-5 h-5"></i></div>
                        <div>
                            <h4 class="text-xl font-black text-white uppercase tracking-tight">Strategic Inventory</h4>
                            <p class="text-[10px] text-slate-500 font-black uppercase tracking-[0.2em]">Quick Stock Check</p>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Pilih Kategori</label>
                        <div class="relative">
                            <select id="catDropdown" class="w-full bg-slate-950/50 border border-white/5 rounded-xl py-3 px-4 text-[11px] text-white font-black outline-none focus:border-indigo-500 transition-all appearance-none uppercase tracking-widest cursor-pointer" onchange="showCategoryStock()">
                                <option value="">-- LIHAT KATEGORI --</option>
                                @foreach($stats['stock_by_category'] as $index => $cat)
                                <option value="cat-{{ $index }}">{{ $cat['name'] }}</option>
                                @endforeach
                            </select>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-8 flex items-center justify-center flex-1" id="stockDisplayArea">
                <div id="placeholderText" class="text-center opacity-30">
                    <i data-lucide="mouse-pointer-2" class="w-8 h-8 mx-auto mb-2"></i>
                    <p class="text-[10px] font-black uppercase tracking-widest">Pilih kategori untuk melihat stok</p>
                </div>

                @foreach($stats['stock_by_category'] as $index => $cat)
                <div id="cat-{{ $index }}" class="stock-item hidden w-full p-6 bg-slate-950/40 rounded-3xl border border-indigo-500/20 shadow-xl shadow-indigo-500/5 animate-in fade-in zoom-in duration-300">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-[11px] font-black text-indigo-400 uppercase tracking-[0.2em]">{{ $cat['name'] }}</span>
                        <div class="text-right">
                            <span class="text-2xl font-black text-white">{{ number_format($cat['balance'], 2) }}</span>
                            <p class="text-[9px] text-slate-500 font-black uppercase">Current Stock</p>
                        </div>
                    </div>
                    <div class="w-full h-2 bg-white/5 rounded-full overflow-hidden">
                        @php $percent = $stats['total_stock'] > 0 ? ($cat['balance'] / $stats['total_stock']) * 100 : 0; @endphp
                        <div class="h-full bg-gradient-to-r from-indigo-600 to-indigo-400 shadow-[0_0_15px_rgba(99,102,241,0.5)]" style="width: {{ $percent }}%"></div>
                    </div>
                    <p class="text-[9px] text-slate-500 font-black mt-3 uppercase text-center tracking-widest">Accounted for {{ number_format($percent, 1) }}% of total inventory</p>
                </div>
                @endforeach
            </div>
        </div>

        <script>
            function showCategoryStock() {
                const select = document.getElementById('catDropdown');
                const selectedId = select.value;
                const placeholder = document.getElementById('placeholderText');
                
                // Hide all items
                document.querySelectorAll('.stock-item').forEach(item => {
                    item.classList.add('hidden');
                });

                if (selectedId) {
                    placeholder.classList.add('hidden');
                    document.getElementById(selectedId).classList.remove('hidden');
                } else {
                    placeholder.classList.remove('hidden');
                }
            }
        </script>
    </div>
</div>
@endsection

