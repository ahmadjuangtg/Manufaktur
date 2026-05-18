@extends('layouts.app', ['title' => 'Delivery Batch'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-end mb-8">
        <div>
            <h3 class="text-xl font-black text-white uppercase tracking-tight">Delivery Batching</h3>
            <p class="text-slate-400 text-sm italic">Consolidate packing lists for group delivery</p>
        </div>
        <button onclick="toggleModal('createDeliveryModal')" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-indigo-500/20 flex items-center gap-2">
            <i data-lucide="truck" class="w-4 h-4"></i> New Delivery Batch
        </button>
    </div>

    <div class="glass-card rounded-[2rem] border border-white/5 bg-slate-900/20 overflow-hidden">
        <div class="p-8 border-b border-white/5 bg-slate-800/30">
            <h4 class="text-[12px] font-black text-white uppercase tracking-[0.3em] flex items-center gap-3">
                <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                Daftar Delivery Batch Aktif
            </h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#1e293b] backdrop-blur-md text-slate-400 text-[11px] font-black uppercase tracking-[0.3em] border-b border-white/5">
                        <th class="px-8 py-5">No. Batch</th>
                        <th class="px-8 py-5">Supir & Kendaraan</th>
                        <th class="px-8 py-5">Daftar Kiriman & Surat Jalan</th>
                        <th class="px-8 py-5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($data as $b)
                    <tr class="hover:bg-white/5 transition-colors group">
                        <!-- Batch No & Date -->
                        <td class="px-8 py-5 align-top">
                            <div class="text-[12px] text-indigo-400 font-bold uppercase tracking-widest">{{ $b->batch_no }}</div>
                            <div class="text-[10px] text-slate-500 mt-1">Dibuat: {{ $b->created_at->format('d M Y H:i') }}</div>
                            <div class="text-[10px] text-slate-500">Oleh: {{ $b->user->name ?? 'Admin' }}</div>
                        </td>

                        <!-- Driver & Vehicle -->
                        <td class="px-8 py-5 align-top">
                            <div class="text-sm font-bold text-white">{{ $b->driver_name ?? '-' }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $b->vehicle_no ?? '-' }}</div>
                        </td>

                        <!-- Included Packing Lists & Custom Print Buttons -->
                        <td class="px-8 py-5 align-top">
                            <div class="space-y-2.5 max-w-xl">
                                @foreach($b->packingLists as $pl)
                                <div class="flex items-center justify-between gap-4 bg-white/[0.02] p-3 rounded-xl border border-white/5 hover:bg-white/[0.04] transition-all">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 bg-indigo-500/10 text-indigo-400 text-[9px] font-bold rounded border border-indigo-500/20">
                                                {{ $pl->packing_no }}
                                            </span>
                                            <span class="text-xs text-white font-bold">{{ $pl->customer->name ?? 'Manual' }}</span>
                                        </div>
                                        <div class="text-[9px] text-slate-500 mt-0.5 max-w-sm truncate" title="{{ $pl->customer->address ?? 'Tanpa Alamat' }}">
                                            {{ $pl->customer->address ?? 'Tanpa Alamat' }}
                                        </div>
                                    </div>
                                    <a href="{{ route('logistics.delivery.print', $pl->id) }}" target="_blank" class="px-3 py-1.5 bg-indigo-600/10 hover:bg-indigo-600/30 text-indigo-400 hover:text-white rounded-lg transition-all border border-indigo-500/20 flex items-center gap-1.5 text-[10px] font-bold shrink-0" title="Cetak Surat Jalan">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        <span>Cetak SJ</span>
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-8 py-5 text-center align-top">
                            <span class="px-3 py-1 bg-slate-800 text-emerald-400 text-[10px] font-black uppercase tracking-widest rounded-lg border border-white/5">
                                {{ $b->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-8 py-20 text-center text-slate-500 italic">
                            Belum ada pengiriman aktif.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div id="createDeliveryModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="toggleModal('createDeliveryModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl p-6">
        <form action="{{ route('logistics.delivery.store') }}" method="POST" class="glass-card p-8 rounded-[2.5rem] border border-white/10 relative overflow-hidden">
            @csrf
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-white font-black text-sm uppercase tracking-widest">Create Delivery Batch</h3>
                <button type="button" onclick="toggleModal('createDeliveryModal')" class="text-slate-500 hover:text-white"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>

            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2 col-span-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Tujuan Pengiriman (Bisa beberapa lokasi)</label>
                        <textarea name="destination" id="destination_input" rows="3" class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-xs text-white outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all resize-none" placeholder="Contoh: 1. Toko Jaya, 2. Gudang B, dst... (Biarkan kosong untuk otomatis mengisi dari alamat customer Packing List)"></textarea>
                        <p class="text-[9px] text-slate-500 italic mt-1">* Gunakan enter untuk memisahkan beberapa tujuan jika diisi manual.</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Nama Supir</label>
                        <input type="text" name="driver_name" class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white outline-none" placeholder="Nama Supir">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">No. Kendaraan</label>
                        <input type="text" name="vehicle_no" class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white outline-none" placeholder="B 1234 ABC">
                    </div>
                </div>

                <div class="space-y-4 pt-6 border-t border-white/5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Pilih Packing List (Status: READY)</label>
                    <div class="grid grid-cols-1 gap-3 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                        @forelse($availablePackingLists as $apl)
                        <label class="flex items-center gap-4 bg-white/[0.02] p-4 rounded-2xl border border-white/5 cursor-pointer hover:bg-white/5 transition-all">
                            <input type="checkbox" name="packing_list_ids[]" value="{{ $apl->id }}" class="w-5 h-5 rounded-lg bg-slate-900 border-white/5 text-indigo-600 focus:ring-indigo-500/20">
                            <div>
                                <div class="text-xs font-black text-white">{{ $apl->packing_no }}</div>
                                <div class="text-[10px] text-emerald-400 font-bold mt-0.5">Customer: {{ $apl->customer->name ?? 'Manual' }}</div>
                                <div class="text-[9px] text-slate-500 mt-1">{{ $apl->details->count() }} Items | {{ $apl->created_at->format('d M Y') }}</div>
                            </div>
                        </label>
                        @empty
                        <div class="text-center py-8 text-slate-600 text-xs italic">Tidak ada Packing List yang siap dikirim.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 mt-8">
                <button type="button" onclick="toggleModal('createDeliveryModal')" class="px-6 py-3 text-xs font-black text-slate-500 uppercase tracking-widest">Batal</button>
                <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black uppercase tracking-widest rounded-2xl transition-all shadow-lg shadow-indigo-500/20">Create Batch</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);
        modal.classList.toggle('hidden');
    }
</script>
@endsection
