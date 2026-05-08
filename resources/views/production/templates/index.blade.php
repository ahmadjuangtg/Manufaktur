@extends('layouts.app', ['title' => 'Production Templates'])

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-black text-white tracking-tight">PRODUCTION TEMPLATES</h2>
            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mt-1">Master Data Manufacturing Recipes</p>
        </div>
        <a href="{{ route('production.templates.create') }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black uppercase tracking-widest rounded-2xl transition-all shadow-lg shadow-indigo-500/20 flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i> Create Template
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($templates as $template)
        <div class="glass-card p-6 rounded-[2rem] border border-white/5 hover:border-indigo-500/30 transition-all group">
            <div class="flex justify-between items-start mb-6">
                <div class="p-3 bg-indigo-500/10 rounded-2xl text-indigo-400 group-hover:scale-110 transition-transform">
                    <i data-lucide="scroll-text" class="w-6 h-6"></i>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('production.templates.edit', $template->id) }}" class="p-2 text-slate-500 hover:text-white transition-colors">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </a>
                    <form action="{{ route('production.templates.destroy', $template->id) }}" method="POST" onsubmit="return confirm('Hapus template ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2 text-slate-500 hover:text-rose-500 transition-colors">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="space-y-1 mb-6">
                <h4 class="text-white font-black text-lg">{{ $template->name }}</h4>
                <p class="text-indigo-400 text-[10px] font-black uppercase tracking-widest">{{ $template->code }}</p>
            </div>

            <div class="space-y-4 pt-4 border-t border-white/5">
                <div class="flex justify-between items-center">
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Main Product</span>
                    <span class="text-xs text-white font-bold">{{ $template->product->name ?? 'Mixed' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Total Stages</span>
                    <span class="px-2 py-1 bg-slate-800 text-slate-400 text-[10px] font-black rounded-lg">{{ $template->stages->count() }} Stages</span>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-white/5 flex flex-wrap gap-2">
                @foreach($template->stages as $stage)
                <span class="px-2 py-1 bg-indigo-500/5 text-indigo-400 text-[9px] font-black rounded-md border border-indigo-500/10">{{ $stage->name }}</span>
                @endforeach
            </div>
        </div>
        @empty
        <div class="col-span-full py-20 text-center glass-card rounded-[3rem] border border-white/5">
            <div class="w-20 h-20 bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="database" class="w-10 h-10 text-slate-600"></i>
            </div>
            <h3 class="text-white font-black text-xl mb-2">Belum ada template</h3>
            <p class="text-slate-500 text-sm">Mulai buat template produksi untuk mempermudah pembuatan Work Order.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
