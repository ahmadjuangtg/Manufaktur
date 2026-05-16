@extends('layouts.app', ['title' => 'Welcome'])

@section('content')
<div class="h-[70vh] flex flex-col items-center justify-center text-center space-y-6">
    <div class="w-24 h-24 bg-indigo-500/10 rounded-[2rem] flex items-center justify-center text-indigo-500 shadow-inner mb-4">
        <i data-lucide="user-check" class="w-12 h-12"></i>
    </div>
    <div>
        <h2 class="text-3xl font-black text-white tracking-tight">Selamat Datang, {{ Auth::user()->name }}</h2>
        <p class="text-slate-400 mt-2">Anda telah berhasil masuk ke sistem Aori Manufacture.</p>
    </div>
    <div class="max-w-md p-6 bg-slate-800/30 rounded-2xl border border-white/5">
        <p class="text-xs text-slate-500 leading-relaxed italic">
            Silakan gunakan menu di samping untuk mengakses modul yang tersedia sesuai dengan hak akses yang diberikan oleh administrator.
        </p>
    </div>
</div>
@endsection
