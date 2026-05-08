@extends('layouts.app', ['title' => 'Approval Mutasi Gudang'])

@section('content')
<div class="space-y-8">
    <div class="flex justify-between items-end mb-4">
        <div>
            <h3 class="text-2xl font-black text-white uppercase tracking-tight">Authorization Center</h3>
            <p class="text-slate-500 text-sm font-medium mt-1">Review and validate warehouse stock transfer requests</p>
        </div>
        <div class="flex gap-4">
            <div class="px-6 py-3 bg-slate-800/50 rounded-2xl border border-white/5 flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-amber-500 animate-pulse"></span>
                <span class="text-xs font-black text-white uppercase tracking-widest">{{ $data->count() }} Pending Requests</span>
            </div>
        </div>
    </div>

    @if($data->count() > 0)
    <div class="grid grid-cols-1 gap-6">
        @foreach($data as $m)
        <div class="glass-card rounded-[2.5rem] border border-white/5 bg-slate-900/40 overflow-hidden hover:border-indigo-500/30 transition-all duration-500 group">
            <div class="grid grid-cols-12">
                <!-- Left Info -->
                <div class="col-span-12 lg:col-span-4 p-10 bg-indigo-500/5 border-r border-white/5 relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 opacity-[0.03] group-hover:opacity-[0.07] transition-opacity duration-700">
                        <i data-lucide="shield-check" class="w-64 h-64 text-white"></i>
                    </div>
                    
                    <div class="relative z-10 space-y-8">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-indigo-600/20">
                                <i data-lucide="file-text" class="w-7 h-7"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.3em]">Reference No</p>
                                <h4 class="text-lg font-black text-white tracking-tight">{{ $m->reference_no }}</h4>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="flex items-start gap-4">
                                <div class="w-1 bg-slate-800 rounded-full h-12 mt-1"></div>
                                <div class="space-y-4 flex-1">
                                    <div>
                                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Source (Sender)</p>
                                        <p class="text-sm font-bold text-slate-300">{{ $m->fromWarehouse->name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Destination (Receiver)</p>
                                        <p class="text-sm font-bold text-white">{{ $m->toWarehouse->name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-white/5">
                            <div class="flex items-center gap-3">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($m->user->name) }}&background=6366f1&color=fff" class="w-8 h-8 rounded-lg shadow-lg">
                                <div>
                                    <p class="text-[8px] font-black text-slate-600 uppercase tracking-widest">Requested By</p>
                                    <p class="text-[11px] font-bold text-slate-400">{{ $m->user->name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Items & Actions -->
                <div class="col-span-12 lg:col-span-8 p-10 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-6">
                            <h5 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Requested Items</h5>
                            <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">{{ $m->details->count() }} SKUs</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($m->details as $d)
                            <div class="flex items-center justify-between p-5 bg-white/5 rounded-2xl border border-white/5 hover:bg-white/[0.08] transition-colors">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-white mb-1">{{ $d->item->name }}</span>
                                    <span class="text-[10px] font-medium text-slate-500">{{ $d->item->code }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-black text-indigo-400">{{ $d->quantity + 0 }}</span>
                                    <span class="text-[9px] font-black text-slate-600 uppercase ml-1">{{ $d->item->unit->name ?? '-' }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @if($m->note)
                        <div class="mt-8 p-6 bg-slate-800/30 rounded-3xl border border-white/5 italic">
                            <p class="text-[11px] text-slate-400 font-medium">"{{ $m->note }}"</p>
                        </div>
                        @endif
                    </div>

                    <div class="mt-12 pt-8 border-t border-white/5 flex justify-end items-center gap-6">
                        <form action="{{ route('mutations.approval.reject', $m->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs font-black text-slate-500 hover:text-rose-500 uppercase tracking-widest transition-colors px-4 py-2">Reject Request</button>
                        </form>
                        <form action="{{ route('mutations.approval.approve', $m->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-10 py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-xl shadow-indigo-600/20 active:scale-[0.98] transition-all">
                                Approve & Authorize
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="flex flex-col items-center justify-center py-32 glass-card rounded-[3rem] border border-white/5">
        <div class="w-24 h-24 bg-slate-800/50 rounded-full flex items-center justify-center mb-8 border border-white/5 shadow-inner">
            <i data-lucide="check-circle" class="w-10 h-10 text-slate-600"></i>
        </div>
        <h4 class="text-xl font-black text-white tracking-tight">Queue is Empty</h4>
        <p class="text-slate-500 text-sm mt-2">All warehouse mutations have been processed.</p>
    </div>
    @endif
</div>
@endsection
