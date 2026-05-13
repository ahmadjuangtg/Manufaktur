@extends('layouts.app', ['title' => 'Laporan Hasil Produksi (LHP)'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white uppercase tracking-tight">Laporan Hasil Produksi (LHP)</h3>
            <p class="text-slate-400 text-sm italic">Data output produksi harian dari lantai produksi</p>
        </div>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden border border-white/5 bg-slate-900/20">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] border-b border-white/5">
                    <th class="px-8 py-5">Tanggal & Waktu</th>
                    <th class="px-8 py-5">Work Order</th>
                    <th class="px-8 py-5">Tahapan</th>
                    <th class="px-8 py-5 text-emerald-400">Good</th>
                    <th class="px-8 py-5 text-rose-400">Reject</th>
                    <th class="px-8 py-5">Operator</th>
                    <th class="px-8 py-5">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $lhp)
                <tr class="hover:bg-white/[0.02] transition-colors">
                    <td class="px-8 py-4">
                        <span class="text-white font-bold text-xs block">{{ $lhp->created_at->format('d M Y') }}</span>
                        <span class="text-slate-500 text-[10px] font-mono">{{ $lhp->created_at->format('H:i:s') }}</span>
                    </td>
                    <td class="px-8 py-4">
                        <span class="text-indigo-400 font-black text-xs">{{ $lhp->workOrder->wo_number }}</span>
                    </td>
                    <td class="px-8 py-4 text-xs text-white font-medium">{{ $lhp->stage->name }}</td>
                    <td class="px-8 py-4">
                        <span class="text-emerald-400 font-black text-sm">{{ number_format($lhp->quantity_good, 0) }}</span>
                    </td>
                    <td class="px-8 py-4">
                        <span class="text-rose-400 font-black text-sm">{{ number_format($lhp->quantity_reject, 0) }}</span>
                    </td>
                    <td class="px-8 py-4 text-xs text-slate-300">{{ $lhp->operator->name }}</td>
                    <td class="px-8 py-4 text-[10px] text-slate-500 italic">{{ $lhp->notes ?: '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-8 py-12 text-center text-slate-500 italic text-sm">Belum ada laporan hasil produksi hari ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
