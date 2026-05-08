@extends('layouts.app', ['title' => 'Stock Opname'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white">Stock Opname History</h3>
            <p class="text-slate-400 text-sm italic">Audit physical stock vs system records</p>
        </div>
        <a href="{{ route('opname.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all shadow-lg shadow-indigo-500/20">
            <i data-lucide="scan" class="w-4 h-4"></i> Mulai Opname Baru
        </a>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden border border-white/5 bg-slate-900/20">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[12px] font-black uppercase tracking-widest border-b border-white/5">
                    <th class="px-8 py-5">Audit Info</th>
                    <th class="px-8 py-5">Product & Warehouse</th>
                    <th class="px-8 py-5 text-center">System Qty</th>
                    <th class="px-8 py-5 text-center">Physical Qty</th>
                    <th class="px-8 py-5 text-center">Diff</th>
                    <th class="px-8 py-5 text-center">Status</th>
                    <th class="px-8 py-5 text-right">Approval</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $item)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-8 py-5">
                        <div class="text-sm text-white font-bold">{{ $item->created_at->format('d/m/Y') }}</div>
                        <div class="text-[12px] text-slate-500 font-mono mt-1">{{ $item->created_at->format('H:i') }} • By: {{ $item->user->name }}</div>
                    </td>
                    <td class="px-8 py-5">
                        <div class="font-bold text-white text-sm">{{ $item->item->name }}</div>
                        <div class="text-[12px] text-indigo-400 font-bold uppercase tracking-widest mt-1">{{ $item->warehouse->name }}</div>
                    </td>
                    <td class="px-8 py-5 text-center text-slate-400 font-mono text-sm">{{ number_format($item->system_qty) }}</td>
                    <td class="px-8 py-5 text-center text-white font-black text-sm">{{ number_format($item->physical_qty) }}</td>
                    <td class="px-8 py-5 text-center">
                        @if($item->difference == 0)
                        <span class="text-[12px] font-bold text-emerald-500 bg-emerald-500/10 px-2 py-1 rounded border border-emerald-500/20">MATCH</span>
                        @else
                        <span class="text-[12px] font-bold {{ $item->difference > 0 ? 'text-indigo-400 bg-indigo-400/10' : 'text-rose-500 bg-rose-500/10' }} px-2 py-1 rounded border {{ $item->difference > 0 ? 'border-indigo-400/20' : 'border-rose-500/20' }}">
                            {{ $item->difference > 0 ? '+' : '' }}{{ number_format($item->difference) }}
                        </span>
                        @endif
                    </td>
                    <td class="px-8 py-5 text-center">
                        @if($item->status == 'PENDING')
                            <span class="text-[11px] font-black bg-amber-500/10 text-amber-500 px-3 py-1 rounded-full border border-amber-500/20 uppercase tracking-widest">Pending</span>
                        @elseif($item->status == 'APPROVED')
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-[11px] font-black bg-emerald-500/10 text-emerald-500 px-3 py-1 rounded-full border border-emerald-500/20 uppercase tracking-widest">Approved</span>
                                <span class="text-[12px] text-slate-500 italic">By: {{ $item->approver->name ?? 'System' }}</span>
                            </div>
                        @else
                            <span class="text-[11px] font-black bg-rose-500/10 text-rose-500 px-3 py-1 rounded-full border border-rose-500/20 uppercase tracking-widest">Rejected</span>
                        @endif
                    </td>
                    <td class="px-8 py-5 text-right">
                        @if($item->status == 'PENDING' && Auth::user()->hasPermission('stock_opname_approve'))
                        <div class="flex justify-end gap-2">
                            <form action="{{ route('opname.approve', $item->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="p-2 bg-emerald-500/10 text-emerald-500 hover:bg-emerald-500 hover:text-white rounded-lg transition-all" title="Approve"><i data-lucide="check" class="w-4 h-4"></i></button>
                            </form>
                            <form action="{{ route('opname.reject', $item->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="p-2 bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-white rounded-lg transition-all" title="Reject"><i data-lucide="x" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                        @else
                        <span class="text-slate-700"><i data-lucide="minus" class="w-4 h-4 ml-auto"></i></span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-8 py-20 text-center text-slate-500 italic">Audit history is empty.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
