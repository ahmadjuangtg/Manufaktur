@extends('layouts.app', ['title' => 'Stock Card List'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h3 class="text-xl font-bold text-white uppercase tracking-tight">Kartu Stok (Stock Card)</h3>
            <p class="text-slate-400 text-sm italic">Lacak riwayat masuk dan keluar barang secara mendetail</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <a href="{{ route('stock_card.export_excel', ['warehouse_id' => $warehouse_id, 'search' => $search]) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest flex items-center gap-2 transition-all shadow-lg shadow-emerald-500/20">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Export Excel
            </a>
            <a href="{{ route('stock_card.print_all', ['warehouse_id' => $warehouse_id, 'search' => $search]) }}" target="_blank" class="bg-slate-800 hover:bg-slate-700 text-white px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest flex items-center gap-2 transition-all shadow-lg">
                <i data-lucide="printer" class="w-4 h-4"></i> Cetak Laporan
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="glass-card p-6 rounded-xl flex items-center gap-4 border border-white/5 bg-slate-900/50">
            <div class="w-12 h-12 bg-indigo-500/10 rounded-lg flex items-center justify-center text-indigo-500"><i data-lucide="package" class="w-6 h-6"></i></div>
            <div><p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest">Total SKUs</p><p class="text-xl font-black text-white">{{ $total_items }}</p></div>
        </div>
        <div class="glass-card p-6 rounded-xl flex items-center gap-4 border border-white/5 bg-slate-900/50">
            <div class="w-12 h-12 bg-emerald-500/10 rounded-lg flex items-center justify-center text-emerald-500"><i data-lucide="check-circle" class="w-6 h-6"></i></div>
            <div><p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest">Active Items</p><p class="text-xl font-black text-white">{{ $total_items }}</p></div>
        </div>
        <div class="glass-card p-6 rounded-xl flex items-center gap-4 border border-white/5 bg-slate-900/50">
            <div class="w-12 h-12 bg-amber-500/10 rounded-lg flex items-center justify-center text-amber-500"><i data-lucide="alert-triangle" class="w-6 h-6"></i></div>
            <div><p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest">Low Stock (< 10)</p><p class="text-xl font-black text-white">{{ $low_stock_count }}</p></div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="glass-card p-6 rounded-3xl border border-white/5 bg-slate-800/20 mb-8">
        <form action="{{ route('stock_card.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-center w-full">
            <div class="relative flex-1 w-full">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari SKU atau Nama Produk..." class="w-full bg-[#0f172a] border border-white/10 rounded-2xl py-3.5 pl-12 pr-6 text-white text-sm font-bold outline-none focus:border-indigo-500 transition-all shadow-inner">
            </div>
            
            <div class="relative w-full md:w-64">
                <select name="warehouse_id" onchange="this.form.submit()" class="w-full bg-[#0f172a] border border-white/10 rounded-2xl py-3.5 pl-6 pr-10 text-white text-sm font-bold outline-none focus:border-indigo-500 transition-all shadow-inner appearance-none cursor-pointer">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ $warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
                <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none"></i>
            </div>

            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3.5 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl shadow-indigo-500/20 transition-all active:scale-95 w-full md:w-auto">
                Cari Produk
            </button>
            @if($search || $warehouse_id)
                <a href="{{ route('stock_card.index') }}" class="text-slate-500 hover:text-white text-sm font-bold px-4 whitespace-nowrap">Reset</a>
            @endif
        </form>
    </div>

    <div class="glass-card rounded-[2rem] border border-white/5 bg-slate-900/20">
        <div class="p-8 border-b border-white/5 bg-slate-800/30">
            <h4 class="text-[12px] font-black text-white uppercase tracking-[0.3em] flex items-center gap-3">
                <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                Daftar Stok Produk
            </h4>
        </div>
        <table class="w-full text-left">
            <thead class="sticky top-[-1.5rem] lg:top-[-2.5rem] z-20">
                <tr class="bg-[#1e293b] backdrop-blur-md text-slate-400 text-[11px] font-black uppercase tracking-[0.3em] border-b border-white/5">
                    <th class="px-8 py-5">SKU</th>
                    <th class="px-8 py-5">Nama Produk</th>
                    <th class="px-8 py-5">Kategori</th>
                    <th class="px-8 py-5 text-center">Current Stock</th>
                    <th class="px-8 py-5 text-center">Lock Stock</th>
                    <th class="px-8 py-5 text-center">Available Stock</th>
                    <th class="px-8 py-5 text-center">Shadow Stock</th>
                    <th class="px-8 py-5 text-center">Satuan</th>
                    <th class="px-8 py-5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($items as $i)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-8 py-5 text-[12px] text-slate-500 font-bold uppercase tracking-widest">{{ $i->code }}</td>
                    <td class="px-8 py-5 font-bold text-white text-sm">{{ $i->name }}</td>
                    <td class="px-8 py-5">
                        <span class="text-[11px] font-black bg-slate-800 text-slate-400 px-3 py-1 rounded-full border border-white/5 uppercase tracking-tighter">{{ $i->category->name ?? '-' }}</span>
                    </td>
                    <td class="px-8 py-5 text-center font-black text-lg {{ $i->current_stock > 0 ? 'text-white' : 'text-slate-600' }}">
                        {{ number_format($i->current_stock) }}
                    </td>
                    <td class="px-8 py-5 text-center font-bold text-rose-400">
                        {{ number_format($i->lock_stock) }}
                    </td>
                    <td class="px-8 py-5 text-center font-black text-xl {{ ($i->current_stock - $i->lock_stock) > 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                        {{ number_format($i->current_stock - $i->lock_stock) }}
                    </td>
                    <td class="px-8 py-5 text-center font-bold text-amber-400">
                        {{ number_format($i->shadow_stock) }}
                    </td>
                    <td class="px-8 py-5 text-center text-slate-500 text-sm font-bold uppercase">{{ $i->unit->name ?? '-' }}</td>
                    <td class="px-8 py-5 text-right">
                        <a href="{{ route('stock_card.index', ['item_id' => $i->id]) }}" class="inline-flex items-center gap-2 bg-indigo-500/10 hover:bg-indigo-600 text-indigo-400 hover:text-white px-4 py-2 rounded-xl text-[12px] font-black uppercase tracking-widest transition-all">
                            <i data-lucide="search" class="w-3.5 h-3.5"></i> Detail History
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-8 py-20 text-center text-slate-500 italic">Produk tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
        
        @if($items->hasPages())
        <div class="px-8 py-4 bg-slate-800/30 border-t border-white/5">
            {{ $items->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
