@extends('layouts.app', ['title' => 'Stock Card Detail'])

@section('content')
<div class="space-y-6">
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('stock_card.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:text-white transition-all"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
            <div>
                <h3 class="text-xl font-bold text-white uppercase tracking-tight">KARTU STOK (STOCK CARD)</h3>
                <p class="text-slate-400 text-sm italic">
                    Detail histori stock untuk @if($warehouse_id && $warehouses->find($warehouse_id)) {{ $warehouses->find($warehouse_id)->name }} @else semua gudang @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <a href="{{ route('stock_card.export_excel_single', ['id' => $item->id, 'warehouse_id' => $warehouse_id]) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest flex items-center gap-2 transition-all shadow-lg shadow-emerald-500/20">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Export Excel
            </a>
            <a href="{{ route('stock_card.print', ['id' => $item->id, 'warehouse_id' => $warehouse_id]) }}" target="_blank" class="bg-slate-800 border border-white/5 hover:bg-slate-700 text-slate-300 hover:text-white px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest flex items-center gap-2 transition-all shadow-lg">
                <i data-lucide="printer" class="w-4 h-4"></i> Print / PDF
            </a>
        </div>
    </div>

    <!-- 3 Cards Row: Informasi Item, History Filter, Total Current Stock -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
        <!-- Informasi Item -->
        <div class="lg:col-span-5 glass-card p-6 rounded-3xl border border-white/5 bg-slate-900/40 space-y-4">
            <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Informasi Item
            </h4>
            <div class="space-y-3">
                <div>
                    <label class="block text-[8px] font-extrabold text-slate-500 uppercase tracking-widest mb-1">Nama Item</label>
                    <div class="w-full bg-[#0f172a]/60 border border-white/5 rounded-xl py-3 px-4 text-white font-bold text-sm truncate">
                        {{ $item->name }}
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[8px] font-extrabold text-slate-500 uppercase tracking-widest mb-1">SKU</label>
                        <div class="w-full bg-[#0f172a]/60 border border-white/5 rounded-xl py-3 px-4 text-white font-mono text-xs uppercase truncate">
                            {{ $item->code }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-[8px] font-extrabold text-slate-500 uppercase tracking-widest mb-1">Satuan</label>
                        <div class="w-full bg-[#0f172a]/60 border border-white/5 rounded-xl py-3 px-4 text-white font-bold text-xs uppercase truncate">
                            {{ $item->unit->name ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Filter -->
        <div class="lg:col-span-4 glass-card p-6 rounded-3xl border border-white/5 bg-slate-900/40 flex flex-col justify-between">
            <div>
                <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest flex items-center gap-2 mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> History Filter
                </h4>
                <p class="text-[10px] text-slate-400 leading-relaxed italic">
                    Data history mengikuti gudang yang dipilih. Summary dan saldo tetap aman walaupun item belum punya transaksi.
                </p>
            </div>
            
            <form action="{{ route('stock_card.index') }}" method="GET" class="flex gap-3 items-end mt-4">
                <input type="hidden" name="item_id" value="{{ $item->id }}">
                <div class="relative flex-1">
                    <select name="warehouse_id" class="w-full bg-[#0f172a] border border-white/10 rounded-2xl py-3 pl-4 pr-10 text-white text-xs font-bold outline-none focus:border-indigo-500 appearance-none cursor-pointer transition-all shadow-inner">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ $warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none"></i>
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-500/20 active:scale-[0.98] transition-all">
                    Filter
                </button>
            </form>
        </div>

        <!-- Total Current Stock -->
        <div class="lg:col-span-3 glass-card p-6 rounded-3xl border border-indigo-500/20 bg-indigo-500/5 flex flex-col justify-between items-end">
            <h4 class="text-[9px] font-black text-indigo-400 uppercase tracking-widest w-full text-right">
                Total Current Stock
            </h4>
            <div class="my-3 flex items-baseline gap-1.5 justify-end w-full">
                <span class="text-5xl font-black {{ $current_stock > 0 ? 'text-white' : ($current_stock < 0 ? 'text-rose-500' : 'text-slate-500') }} tracking-tight">
                    {{ number_format($current_stock) }}
                </span>
                <span class="text-xs font-extrabold text-slate-500 uppercase">{{ $item->unit->name ?? '-' }}</span>
            </div>
            <div class="text-[8px] font-bold text-slate-500 uppercase tracking-widest w-full text-right">
                Filter Gudang: <span class="text-indigo-400 font-extrabold uppercase ml-1">@if($warehouse_id && $warehouses->find($warehouse_id)) {{ $warehouses->find($warehouse_id)->name }} @else Semua Gudang @endif</span>
            </div>
        </div>
    </div>

    <!-- 5 Details Grid Row -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-6 mb-10">
        <div class="bg-slate-800/40 border border-indigo-500/10 rounded-3xl p-5 flex flex-col items-start relative overflow-hidden group">
            <div class="absolute right-3 top-3 text-indigo-500/10 group-hover:text-indigo-500/20 transition-colors"><i data-lucide="package" class="w-10 h-10"></i></div>
            <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Current Stock</p>
            <div class="flex items-baseline gap-1">
                <span class="text-3xl font-black text-white tracking-tight">{{ number_format($current_stock) }}</span>
            </div>
        </div>
        <div class="bg-rose-500/5 border border-rose-500/10 rounded-3xl p-5 flex flex-col items-start relative overflow-hidden group">
            <div class="absolute right-3 top-3 text-rose-500/10 group-hover:text-rose-500/20 transition-colors"><i data-lucide="lock" class="w-10 h-10"></i></div>
            <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Lock Stock</p>
            <div class="flex items-baseline gap-1">
                <span class="text-3xl font-black text-rose-400 tracking-tight">{{ number_format($lock_stock) }}</span>
            </div>
        </div>
        <div class="bg-amber-500/5 border border-amber-500/10 rounded-3xl p-5 flex flex-col items-start relative overflow-hidden group">
            <div class="absolute right-3 top-3 text-amber-500/10 group-hover:text-amber-500/20 transition-colors"><i data-lucide="activity" class="w-10 h-10"></i></div>
            <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Shadow Stock</p>
            <div class="flex items-baseline gap-1">
                <span class="text-3xl font-black text-amber-400 tracking-tight">{{ number_format($shadow_stock) }}</span>
            </div>
        </div>
        <div class="bg-emerald-500/5 border border-emerald-500/10 rounded-3xl p-5 flex flex-col items-start relative overflow-hidden group">
            <div class="absolute right-3 top-3 text-emerald-500/10 group-hover:text-emerald-500/20 transition-colors"><i data-lucide="check-circle-2" class="w-10 h-10"></i></div>
            <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Available Stock</p>
            <div class="flex items-baseline gap-1">
                <span class="text-3xl font-black text-emerald-500 tracking-tight">{{ number_format($available_stock) }}</span>
            </div>
        </div>
        <div class="col-span-2 md:col-span-1 bg-slate-800/40 border border-slate-700/20 rounded-3xl p-5 flex flex-col items-start relative overflow-hidden group">
            <div class="absolute right-3 top-3 text-slate-500/10 group-hover:text-slate-500/20 transition-colors"><i data-lucide="warehouse" class="w-10 h-10"></i></div>
            <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> Warehouse Count</p>
            <div class="flex items-baseline gap-1">
                <span class="text-3xl font-black text-slate-300 tracking-tight">{{ number_format($warehouse_count) }}</span>
            </div>
        </div>
    </div>

    <!-- Stock Per Gudang Card -->
    <div class="glass-card rounded-[2.5rem] overflow-hidden border border-white/5 bg-slate-900/20 mb-10">
        <div class="p-8 border-b border-white/5 bg-slate-800/30 flex flex-col gap-1">
            <h4 class="text-[11px] font-black text-white uppercase tracking-[0.25em] flex items-center gap-2">
                <span class="w-2 h-2 bg-indigo-500 rounded-full"></span> Stock Per Gudang
            </h4>
            <p class="text-slate-500 text-[10px] italic">Saldo per gudang menggunakan snapshot yang sama dengan summary detail.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-800/40 text-slate-400 text-[9px] font-black uppercase tracking-[0.25em] border-b border-white/5">
                        <th class="px-8 py-4">Gudang</th>
                        <th class="px-6 py-4 text-center">Current</th>
                        <th class="px-6 py-4 text-center">Lock</th>
                        <th class="px-6 py-4 text-center">Shadow</th>
                        <th class="px-6 py-4 text-center">Available</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 bg-slate-950/10">
                    @forelse($warehouse_stock as $ws)
                    <tr class="hover:bg-white/5 transition-colors {{ $warehouse_id == $ws->warehouse_id ? 'bg-indigo-500/5' : '' }}">
                        <td class="px-8 py-4.5">
                            <span class="text-xs text-white font-bold uppercase tracking-wide flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full {{ $warehouse_id == $ws->warehouse_id ? 'bg-indigo-400' : 'bg-slate-700' }}"></span>
                                {{ $ws->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4.5 text-center text-xs font-black text-slate-300">{{ number_format($ws->total) }}</td>
                        <td class="px-6 py-4.5 text-center text-xs font-bold text-rose-400/80">{{ number_format($ws->lock_stock) }}</td>
                        <td class="px-6 py-4.5 text-center text-xs font-bold text-amber-400/80">{{ number_format($ws->shadow_stock) }}</td>
                        <td class="px-6 py-4.5 text-center text-xs font-black text-emerald-500">{{ number_format(max(0, $ws->total - $ws->lock_stock)) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-10 text-center text-slate-500 italic text-xs">Belum ada data stok gudang untuk barang ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- History Transaksi Card -->
    <div class="glass-card rounded-[2.5rem] overflow-hidden border border-white/5 bg-slate-900/20">
        <div class="p-8 border-b border-white/5 bg-slate-800/30 flex justify-between items-center">
            <div class="flex flex-col gap-1">
                <h4 class="text-[11px] font-black text-white uppercase tracking-[0.25em] flex items-center gap-2">
                    <span class="w-2 h-2 bg-indigo-500 rounded-full"></span> History Transaksi
                </h4>
                <p class="text-slate-500 text-[10px] italic">Pagination tetap aktif agar halaman stabil saat data history besar.</p>
            </div>
            <span class="text-[9px] font-black bg-slate-800 text-slate-400 px-3 py-1.5 rounded-xl border border-white/5 uppercase tracking-widest shadow-inner">
                {{ $transactions->total() }} Record
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-800/40 text-slate-400 text-[9px] font-black uppercase tracking-[0.25em] border-b border-white/5">
                        <th class="px-8 py-4">Tanggal</th>
                        <th class="px-6 py-4">Gudang</th>
                        <th class="px-6 py-4 text-center">Tipe Transaksi</th>
                        <th class="px-6 py-4 text-center">Masuk</th>
                        <th class="px-6 py-4 text-center">Keluar</th>
                        <th class="px-6 py-4 text-center">Satuan</th>
                        <th class="px-8 py-4">Keterangan</th>
                        <th class="px-8 py-4 text-right">Saldo Current</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @php $running_balance = $starting_balance; @endphp
                    {{-- Reverse to calculate running balance correctly from start --}}
                    @foreach($transactions->reverse() as $t)
                        @php 
                            if($t->type == 'IN') {
                                $running_balance += $t->quantity;
                            } elseif ($t->type == 'OUT') {
                                $running_balance -= $t->quantity;
                            }
                            $t->running_balance = $running_balance;
                        @endphp
                    @endforeach

                    @forelse($transactions as $t)
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-8 py-4.5">
                            <div class="text-xs text-white font-bold">{{ $t->created_at->format('d M Y') }}</div>
                            <div class="text-[9px] text-slate-500 font-mono mt-0.5">{{ $t->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4.5">
                            <div class="text-xs text-indigo-400 font-black uppercase tracking-wide">{{ $t->warehouse->name }}</div>
                        </td>
                        <td class="px-6 py-4.5 text-center">
                            @php
                                $typeColors = [
                                    'IN' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                    'OUT' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                                    'LOCK_IN' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                                    'LOCK_OUT' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                                    'SHADOW_IN' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                    'SHADOW_OUT' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                                ];
                                $badgeColor = $typeColors[$t->type] ?? 'bg-slate-500/10 text-slate-400 border-slate-500/20';
                            @endphp
                            <span class="px-2.5 py-1 rounded border {{ $badgeColor }} text-[8px] font-black uppercase tracking-widest">
                                {{ str_replace('_', ' ', $t->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4.5 text-center">
                            @if(in_array($t->type, ['IN', 'SHADOW_IN', 'LOCK_IN']))
                                <span class="text-emerald-500 font-black text-sm">+{{ number_format($t->quantity) }}</span>
                            @else
                                <span class="text-slate-700 font-bold">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4.5 text-center">
                            @if(!in_array($t->type, ['IN', 'SHADOW_IN', 'LOCK_IN']))
                                <span class="text-rose-500 font-black text-sm">-{{ number_format($t->quantity) }}</span>
                            @else
                                <span class="text-slate-700 font-bold">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4.5 text-center text-[10px] text-slate-500 font-bold uppercase">{{ $item->unit->name ?? '-' }}</td>
                        <td class="px-8 py-4.5">
                            <div class="text-xs text-slate-200 font-black uppercase tracking-tight">{{ $t->reference_no }}</div>
                            <div class="text-[10px] text-slate-400 font-medium italic mt-1 line-clamp-1">{{ $t->note ?? '-' }}</div>
                        </td>
                        <td class="px-8 py-4.5 text-right font-black text-slate-300 text-sm bg-white/5">
                            {{ number_format($t->running_balance) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-8 py-20 text-center text-slate-500">
                            <div class="max-w-md mx-auto space-y-2">
                                <p class="text-sm font-black text-slate-400">Belum ada history transaksi</p>
                                <p class="text-xs text-slate-600">Item ini tetap ditampilkan dengan saldo default 0 sampai ada transaksi inventory.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
        <div class="px-8 py-4 bg-slate-800/30 border-t border-white/5">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
