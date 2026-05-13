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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($data as $b)
        <div class="glass-card p-6 rounded-[2rem] border border-white/5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-600/5 blur-[50px] rounded-full -mr-16 -mt-16"></div>
            
            <div class="flex justify-between items-start mb-6">
                <div class="w-12 h-12 bg-slate-800 rounded-2xl flex items-center justify-center text-emerald-400">
                    <i data-lucide="truck" class="w-6 h-6"></i>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 text-[10px] font-black uppercase tracking-widest rounded-lg border border-white/5">
                    {{ $b->status }}
                </span>
            </div>

            <div class="space-y-4">
                <div>
                    <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">{{ $b->batch_no }}</div>
                    <h4 class="text-white font-bold">{{ $b->destination }}</h4>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-white/5">
                    <div>
                        <div class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Driver</div>
                        <div class="text-xs text-white font-bold">{{ $b->driver_name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Vehicle No</div>
                        <div class="text-xs text-white font-bold">{{ $b->vehicle_no ?? '-' }}</div>
                    </div>
                </div>

                <div class="pt-4 border-t border-white/5">
                    <div class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-2">Included Packing Lists</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($b->packingLists as $pl)
                        <span class="px-2 py-1 bg-white/5 text-slate-400 text-[9px] font-bold rounded-md">
                            {{ $pl->packing_no }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full glass-card p-20 rounded-[2rem] border border-white/5 text-center">
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
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Pilih Customer (Opsional)</label>
                        <select id="customer_selector" class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all appearance-none cursor-pointer">
                            <option value="">-- Isi Manual --</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->name }}">{{ $c->name }} ({{ $c->address }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2 col-span-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Tujuan Pengiriman (Bisa beberapa lokasi)</label>
                        <textarea name="destination" id="destination_input" required rows="3" class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all resize-none" placeholder="Contoh: 1. Toko Jaya, 2. Gudang B, dst..."></textarea>
                        <p class="text-[9px] text-slate-500 italic mt-1">* Gunakan koma atau angka untuk memisahkan beberapa tujuan.</p>
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
                                <div class="text-[10px] text-slate-500 mt-1">{{ $apl->details->count() }} Items | {{ $apl->created_at->format('d M Y') }}</div>
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

    // Customer Selection Logic
    document.getElementById('customer_selector').addEventListener('change', function() {
        const destinationInput = document.getElementById('destination_input');
        if (this.value) {
            const currentVal = destinationInput.value.trim();
            const separator = currentVal ? "\n" : "";
            destinationInput.value = currentVal + separator + this.value;
        }
    });
</script>
@endsection
