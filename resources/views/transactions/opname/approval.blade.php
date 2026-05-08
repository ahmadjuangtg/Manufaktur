@extends('layouts.app', ['title' => 'Approval Stock Opname'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-end mb-8">
        <div>
            <h3 class="text-xl font-black text-white uppercase tracking-tight">Stock Opname Authorization</h3>
            <p class="text-slate-400 text-sm italic">Review and validate inventory adjustments</p>
        </div>
    </div>

    <div class="glass-card rounded-[2rem] overflow-hidden border border-white/5 bg-slate-900/20">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] border-b border-white/5">
                    <th class="px-8 py-5">Item & Gudang</th>
                    <th class="px-8 py-5 text-center">System Qty</th>
                    <th class="px-8 py-5 text-center">Physical Qty</th>
                    <th class="px-8 py-5 text-center">Difference</th>
                    <th class="px-8 py-5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $o)
                <tr class="hover:bg-white/5 transition-colors">
                    <td class="px-8 py-5">
                        <div class="text-xs text-white font-bold">{{ $o->item->name }}</div>
                        <div class="text-[10px] text-slate-500 font-medium mt-1">{{ $o->warehouse->name }} | Oleh: {{ $o->user->name }}</div>
                    </td>
                    <td class="px-8 py-5 text-center">
                        <span class="text-xs text-slate-400 font-bold">{{ $o->system_qty + 0 }}</span>
                    </td>
                    <td class="px-8 py-5 text-center">
                        <span class="text-xs text-white font-black">{{ $o->physical_qty + 0 }}</span>
                    </td>
                    <td class="px-8 py-5 text-center">
                        @if($o->difference > 0)
                        <span class="text-xs text-emerald-500 font-black">+{{ $o->difference + 0 }}</span>
                        @elseif($o->difference < 0)
                        <span class="text-xs text-rose-500 font-black">{{ $o->difference + 0 }}</span>
                        @else
                        <span class="text-xs text-slate-600 font-bold">No Diff</span>
                        @endif
                    </td>
                    <td class="px-8 py-5 text-right space-x-2">
                        <form action="{{ route('opname.reject', $o->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-2 bg-slate-800 rounded-lg text-slate-500 hover:text-rose-500 transition-all"><i data-lucide="x-circle" class="w-4 h-4"></i></button>
                        </form>
                        <form action="{{ route('opname.approve', $o->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all">
                                Approve
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-8 py-20 text-center text-slate-500 italic">Tidak ada antrian persetujuan opname.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
