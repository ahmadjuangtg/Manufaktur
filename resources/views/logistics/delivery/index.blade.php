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

    <div class="space-y-6">
        @forelse($data as $b)
        <div class="glass-card p-6 rounded-[2rem] border border-white/5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-48 h-48 bg-indigo-600/5 blur-[80px] rounded-full -mr-24 -mt-24"></div>
            
            <!-- Main Info Row -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-white/5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-slate-800 rounded-2xl flex items-center justify-center text-emerald-400 shrink-0">
                        <i data-lucide="truck" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">{{ $b->batch_no }}</div>
                        <div class="flex flex-wrap items-center gap-2 mt-1.5 mb-2">
                            @php
                                $destinations = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $b->destination))));
                            @endphp
                            @foreach($destinations as $index => $dest)
                                @php
                                    $cleanDest = preg_replace('/^\d+\.\s*/', '', $dest);
                                @endphp
                                @if($index > 0)
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-600"></i>
                                @endif
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-500/10 border border-indigo-500/20 text-indigo-200 text-xs font-bold rounded-xl shadow-sm">
                                    <span class="px-1.5 py-0.5 bg-indigo-500 text-white text-[8px] font-black uppercase rounded mr-0.5 tracking-wider">Tujuan {{ $index + 1 }}</span>
                                    {{ $cleanDest }}
                                </span>
                            @endforeach
                        </div>
                        <div class="text-[10px] text-slate-500 mt-1">Dibuat oleh: {{ $b->user->name ?? 'Admin' }} | {{ $b->created_at->format('d M Y H:i') }}</div>
                    </div>
                </div>

                <!-- Driver & Vehicle Details -->
                <div class="flex flex-wrap items-center gap-6">
                    <div class="bg-white/[0.02] px-4 py-3 rounded-xl border border-white/5">
                        <div class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-0.5">Nama Supir</div>
                        <div class="text-xs text-white font-bold">{{ $b->driver_name ?? '-' }}</div>
                    </div>
                    <div class="bg-white/[0.02] px-4 py-3 rounded-xl border border-white/5">
                        <div class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-0.5">No. Kendaraan</div>
                        <div class="text-xs text-white font-bold">{{ $b->vehicle_no ?? '-' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Status</div>
                        <span class="px-3 py-1.5 bg-slate-800 text-emerald-400 text-[10px] font-black uppercase tracking-widest rounded-lg border border-white/5">
                            {{ $b->status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Packing Lists Section -->
            <div class="mt-6">
                <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Included Packing Lists & Customers</div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($b->packingLists as $pl)
                    <div class="p-4 bg-white/[0.02] rounded-2xl border border-white/5 flex justify-between items-center gap-4 hover:bg-white/[0.04] transition-all">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 bg-indigo-500/10 text-indigo-400 text-[9px] font-bold rounded border border-indigo-500/20">
                                    {{ $pl->packing_no }}
                                </span>
                                <span class="text-xs text-white font-bold">{{ $pl->customer->name ?? 'Manual' }}</span>
                            </div>
                            <div class="text-[9px] text-slate-500 truncate w-60" title="{{ $pl->customer->address ?? 'Tanpa Alamat' }}">{{ $pl->customer->address ?? 'Tanpa Alamat' }}</div>
                        </div>
                        <a href="{{ route('logistics.delivery.print', $pl->id) }}" target="_blank" class="p-2 bg-indigo-600/10 hover:bg-indigo-600/30 text-indigo-400 hover:text-white rounded-xl transition-all border border-indigo-500/20" title="Cetak Surat Jalan">
                            <i data-lucide="printer" class="w-4 h-4"></i>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
        <div class="glass-card p-20 rounded-[2rem] border border-white/5 text-center">
            <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-600">
                <i data-lucide="map-pin" class="w-10 h-10"></i>
            </div>
            <h3 class="text-white font-bold">Belum ada Pengiriman</h3>
            <p class="text-slate-500 text-sm mt-2">Gabungkan beberapa packing list untuk memulai pengiriman baru.</p>
        </div>
        @endforelse
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
