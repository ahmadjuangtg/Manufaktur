@extends('layouts.app', ['title' => 'Stock Card Detail'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('stock_card.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:text-white transition-all"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
            <div>
                <h3 class="text-xl font-bold text-white uppercase tracking-tight">Kartu Stok (Stock Card)</h3>
                <p class="text-slate-400 text-sm italic">Lacak riwayat masuk dan keluar barang secara mendetail</p>
            </div>
        </div>
        <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-700 text-white px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest flex items-center gap-2 transition-all">
            <i data-lucide="printer" class="w-4 h-4"></i> Cetak Laporan
        </button>
    </div>

    <!-- Product Summary Header -->
    <div class="glass-card p-10 rounded-[2.5rem] border border-white/5 bg-slate-900/40 mb-10 flex flex-col md:flex-row justify-between items-start gap-8">
        <div class="flex-1">
            <h2 class="text-3xl font-black text-white leading-tight">{{ $item->name }}</h2>
            <div class="flex items-center gap-4 mt-2 mb-6">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-[0.2em]">SKU: {{ $item->code }}</span>
                <span class="w-1 h-1 bg-slate-700 rounded-full"></span>
                <span class="text-xs text-slate-500 font-bold uppercase tracking-[0.2em]">Satuan: {{ $item->unit->name ?? '-' }}</span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($warehouse_stock as $ws)
                <div class="p-4 bg-white/5 rounded-2xl border border-white/5">
                    <p class="text-[8px] font-black text-slate-500 uppercase tracking-widest mb-1">{{ $ws->name }}</p>
                    <p class="text-sm font-black {{ $ws->total > 0 ? 'text-indigo-400' : 'text-slate-500' }}">
                        {{ number_format($ws->total) }} <span class="text-[9px] text-slate-600 ml-0.5">{{ $item->unit->name ?? '-' }}</span>
                    </p>
                </div>
                @endforeach
            </div>
        </div>
        <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-3xl p-8 flex flex-col items-end min-w-[200px] self-center">
            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Total Stok Saat Ini</p>
            <div class="flex items-baseline gap-2">
                <span class="text-5xl font-black {{ $current_stock > 0 ? 'text-emerald-500' : ($current_stock < 0 ? 'text-rose-500' : 'text-slate-400') }}">{{ number_format($current_stock) }}</span>
                <span class="text-xs font-black text-slate-500 uppercase">{{ $item->unit->name ?? '-' }}</span>
            </div>
        </div>
    </div>

    <div class="glass-card rounded-[2rem] overflow-hidden border border-white/5 bg-slate-900/20">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] border-b border-white/5">
                    <th class="px-8 py-5">Tanggal</th>
                    <th class="px-8 py-5">Gudang</th>
                    <th class="px-8 py-5 text-center">Masuk</th>
                    <th class="px-8 py-5 text-center">Keluar</th>
                    <th class="px-8 py-5 text-center">Satuan</th>
                    <th class="px-8 py-5">Keterangan</th>
                    <th class="px-8 py-5 text-right">Saldo Stok</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @php $running_balance = 0; @endphp
                {{-- Reverse to calculate running balance correctly from start --}}
                @foreach($transactions->reverse() as $t)
                    @php 
                        if($t->type == 'IN') $running_balance += $t->quantity;
                        else $running_balance -= $t->quantity;
                        $t->running_balance = $running_balance;
                    @endphp
                @endforeach

                @forelse($transactions as $t)
                <tr class="hover:bg-white/5 transition-colors">
                    <td class="px-8 py-5">
                        <div class="text-xs text-white font-bold">{{ $t->created_at->format('d M Y') }}</div>
                        <div class="text-[10px] text-slate-500 font-mono">{{ $t->created_at->format('H:i') }}</div>
                    </td>
                    <td class="px-8 py-5">
                        <div class="text-xs text-indigo-400 font-black uppercase tracking-wider">{{ $t->warehouse->name }}</div>
                    </td>
                    <td class="px-8 py-5 text-center">
                        @if($t->type == 'IN')
                        <span class="text-emerald-500 font-black text-sm">+{{ number_format($t->quantity) }}</span>
                        @else
                        <span class="text-slate-800">-</span>
                        @endif
                    </td>
                    <td class="px-8 py-5 text-center">
                        @if($t->type == 'OUT')
                        <span class="text-rose-500 font-black text-sm">-{{ number_format($t->quantity) }}</span>
                        @else
                        <span class="text-slate-800">-</span>
                        @endif
                    </td>
                    <td class="px-8 py-5 text-center text-[10px] text-slate-500 font-bold uppercase">{{ $item->unit->name ?? '-' }}</td>
                    <td class="px-8 py-5">
                        <div class="text-sm text-slate-200 font-black uppercase tracking-tight">{{ $t->reference_no }}</div>
                        <div class="text-xs text-slate-400 font-bold italic mt-1 line-clamp-1">{{ $t->note ?? '-' }}</div>
                    </td>
                    <td class="px-8 py-5 text-right font-black text-slate-400 text-sm bg-white/5">
                        {{ number_format($t->running_balance) }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-8 py-20 text-center text-slate-500">Belum ada pergerakan stok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    @media print {
        .sidebar, button, a { display: none !important; }
        .glass-card { border: 1px solid #ddd !important; background: white !important; color: black !important; }
        .text-white, .text-slate-400, .text-indigo-400 { color: black !important; }
        body { background: white !important; }
    }
</style>
@endsection
