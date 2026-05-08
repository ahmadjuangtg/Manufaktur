@extends('layouts.app', ['title' => 'Dashboard Analytics'])

@section('content')
<div class="space-y-8">
    <!-- Hero Section -->
    <div class="relative overflow-hidden glass-card p-10 rounded-2xl border border-white/10 shadow-2xl">
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-indigo-600/20 blur-[100px] rounded-full"></div>
        <div class="relative z-10 max-w-2xl">
            <h1 class="text-4xl font-extrabold text-white mb-4">Welcome back, <span class="text-indigo-400">Admin</span></h1>
            <p class="text-slate-400 text-lg">Your inventory system is currently optimized. You have <span class="text-white font-bold">12 new notifications</span> and 5 pending stock audits.</p>
            <div class="flex gap-4 mt-8">
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold transition-all shadow-lg shadow-indigo-500/20">View Reports</button>
                <button class="bg-slate-800 hover:bg-slate-700 text-white px-8 py-3 rounded-xl font-bold border border-white/5 transition-all">Audit Logs</button>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="glass-card p-6 rounded-2xl stat-card-glow">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-indigo-500/10 rounded-xl text-indigo-500"><i data-lucide="package" class="w-6 h-6"></i></div>
                <span class="text-xs text-emerald-500 font-bold bg-emerald-500/10 px-2 py-0.5 rounded">+12%</span>
            </div>
            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Items</p>
            <h4 class="text-3xl font-bold text-white mt-1">1,284</h4>
        </div>
        <div class="glass-card p-6 rounded-2xl">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-emerald-500/10 rounded-xl text-emerald-500"><i data-lucide="activity" class="w-6 h-6"></i></div>
                <span class="text-xs text-slate-500 font-bold">Steady</span>
            </div>
            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Stock Health</p>
            <h4 class="text-3xl font-bold text-white mt-1">98.2%</h4>
        </div>
        <div class="glass-card p-6 rounded-2xl">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-amber-500/10 rounded-xl text-amber-500"><i data-lucide="factory" class="w-6 h-6"></i></div>
                <span class="text-xs text-rose-500 font-bold bg-rose-500/10 px-2 py-0.5 rounded">-4%</span>
            </div>
            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Manufacturers</p>
            <h4 class="text-3xl font-bold text-white mt-1">42</h4>
        </div>
        <div class="glass-card p-6 rounded-2xl">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-rose-500/10 rounded-xl text-rose-500"><i data-lucide="alert-circle" class="w-6 h-6"></i></div>
                <span class="text-xs text-rose-500 font-bold">Critical</span>
            </div>
            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Out of Stock</p>
            <h4 class="text-3xl font-bold text-white mt-1">12</h4>
        </div>
    </div>

    <!-- Charts & Table Preview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 glass-card p-8 rounded-2xl">
            <div class="flex justify-between items-center mb-8">
                <h4 class="text-xl font-bold">Recent Activities</h4>
                <button class="text-indigo-400 text-xs font-bold hover:underline">View All</button>
            </div>
            <div class="space-y-6">
                @foreach([1,2,3] as $i)
                <div class="flex items-center justify-between p-4 bg-slate-800/30 rounded-xl border border-white/5">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400"><i data-lucide="refresh-ccw" class="w-5 h-5"></i></div>
                        <div>
                            <p class="text-sm font-bold">Stock Updated</p>
                            <p class="text-xs text-slate-500">2,000 units of Aori Smartphone added to Warehouse A</p>
                        </div>
                    </div>
                    <span class="text-[10px] text-slate-600 font-mono italic">2 mins ago</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="glass-card p-8 rounded-2xl">
            <h4 class="text-xl font-bold mb-8">Quick Actions</h4>
            <div class="space-y-3">
                <a href="{{ route('items.index') }}" class="w-full flex items-center justify-between p-4 bg-indigo-600 rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/20">
                    <span>New Item</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
                <button class="w-full flex items-center justify-between p-4 bg-slate-800 rounded-xl font-bold hover:bg-slate-700 transition-all border border-white/5">
                    <span>Generate Report</span>
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                </button>
                <button class="w-full flex items-center justify-between p-4 bg-slate-800 rounded-xl font-bold hover:bg-slate-700 transition-all border border-white/5 text-rose-400">
                    <span>Low Stock Alert</span>
                    <i data-lucide="bell" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
