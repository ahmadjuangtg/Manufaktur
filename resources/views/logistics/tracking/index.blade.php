@extends('layouts.app', ['title' => 'Tracking Delivery'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-end mb-8">
        <div>
            <h3 class="text-xl font-black text-white uppercase tracking-tight">Active Delivery Tracking</h3>
            <p class="text-slate-400 text-sm italic">Monitor and update real-time shipping status</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
        @forelse($data as $b)
        <div class="glass-card p-8 rounded-[2.5rem] border border-white/5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-64 h-64 bg-blue-600/5 blur-[100px] rounded-full -mr-32 -mt-32"></div>
            
            <div class="flex flex-wrap justify-between items-center gap-6 relative z-10">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 bg-slate-800 rounded-3xl flex items-center justify-center text-blue-400 shadow-xl border border-white/5">
                        <i data-lucide="truck" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">{{ $b->batch_no }}</div>
                        <h4 class="text-white text-xl font-black tracking-tight">{{ $b->destination }}</h4>
                        <div class="flex items-center gap-3 mt-2">
                            <span class="text-xs text-slate-400 font-bold"><i data-lucide="user" class="w-3 h-3 inline mr-1"></i> {{ $b->driver_name }}</span>
                            <span class="text-xs text-slate-400 font-bold"><i data-lucide="hash" class="w-3 h-3 inline mr-1"></i> {{ $b->vehicle_no }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-8">
                    @if($b->status == 'PENDING')
                    <form action="{{ route('logistics.tracking.update', $b->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="ON_DELIVERY">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-blue-500/20 flex items-center gap-2">
                            <i data-lucide="play" class="w-4 h-4"></i> Start Delivery
                        </button>
                    </form>
                    @elseif($b->status == 'ON_DELIVERY')
                    <div class="text-center px-6">
                        <div class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Berangkat Pada</div>
                        <div class="text-xs text-emerald-400 font-black">{{ \Carbon\Carbon::parse($b->departure_at)->timezone('Asia/Jakarta')->format('H:i') }}</div>
                    </div>
                    <form action="{{ route('logistics.tracking.update', $b->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="COMPLETED">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-8 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-emerald-500/20 flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4"></i> Mark as Arrived
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-white/5 flex flex-wrap gap-4">
                @foreach($b->packingLists as $pl)
                <div class="px-4 py-2 bg-white/[0.03] rounded-xl border border-white/5 flex items-center gap-3">
                    <i data-lucide="box" class="w-3 h-3 text-slate-500"></i>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $pl->packing_no }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="glass-card p-20 rounded-[2rem] border border-white/5 text-center">
            <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-600">
                <i data-lucide="navigation" class="w-10 h-10"></i>
            </div>
            <h3 class="text-white font-bold">Tidak ada pengiriman aktif</h3>
            <p class="text-slate-500 text-sm mt-2">Semua pengiriman telah selesai atau belum dimulai.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
