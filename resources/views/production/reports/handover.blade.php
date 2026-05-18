@extends('layouts.app', ['title' => 'Serah Terima Barang (NPB/PHP)'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white uppercase tracking-tight">Serah Terima Barang Produksi</h3>
            <p class="text-slate-400 text-sm italic">Nota Penyerahan Barang (NPB) & Penyerahan Hasil Produksi (PHP)</p>
        </div>
        <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest flex items-center gap-2 transition-all shadow-lg shadow-indigo-500/20">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Buat Nota Baru
        </button>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden border border-white/5 bg-slate-900/20">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] border-b border-white/5">
                    <th class="px-8 py-5">No. Referensi</th>
                    <th class="px-8 py-5">Tipe</th>
                    <th class="px-8 py-5">Work Order</th>
                    <th class="px-8 py-5">Jumlah</th>
                    <th class="px-8 py-5">Dari -> Ke</th>
                    <th class="px-8 py-5">Status</th>
                    <th class="px-8 py-5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $t)
                <tr class="hover:bg-white/[0.02] transition-colors">
                    <td class="px-8 py-4">
                        <span class="text-white font-mono text-xs">{{ $t->reference_no }}</span>
                        <p class="text-[9px] text-slate-500 font-bold uppercase mt-1">{{ $t->created_at->format('d M Y, H:i') }}</p>
                    </td>
                    <td class="px-8 py-4">
                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest {{ $t->type == 'PHP' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-blue-500/10 text-blue-400 border border-blue-500/20' }}">
                            {{ $t->type }}
                        </span>
                    </td>
                    <td class="px-8 py-4">
                        <span class="text-slate-300 font-bold text-xs">{{ $t->workOrder->wo_number }}</span>
                    </td>
                    <td class="px-8 py-4">
                        <span class="text-white font-black text-sm">{{ number_format($t->quantity, 0) }}</span>
                        <span class="text-[9px] text-slate-600 font-bold uppercase ml-1">PCS</span>
                    </td>
                    <td class="px-8 py-4">
                        <div class="flex items-center gap-2 text-[10px]">
                            <span class="text-slate-500">{{ $t->fromWarehouse->name }}</span>
                            <i data-lucide="arrow-right" class="w-3 h-3 text-slate-700"></i>
                            <span class="text-indigo-400 font-bold">{{ $t->toWarehouse->name }}</span>
                        </div>
                    </td>
                    <td class="px-8 py-4">
                        @if($t->status == 'VERIFIED')
                        <span class="text-[10px] font-black text-emerald-500 flex items-center gap-1">
                            <i data-lucide="check-circle" class="w-3 h-3"></i> VERIFIED
                        </span>
                        <p class="text-[8px] text-slate-600 mt-0.5">Oleh: {{ $t->verifier->name }}</p>
                        @elseif($t->status == 'REJECTED')
                        <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">REJECTED</span>
                        @else
                        <span class="text-[10px] font-black text-amber-500 animate-pulse">PENDING VERIFICATION</span>
                        @endif
                    </td>
                    <td class="px-8 py-4 text-right">
                        @if($t->status == 'PENDING')
                        <form action="{{ route('production.reports.handover.verify', $t->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all">
                                Verifikasi
                            </button>
                        </form>
                        @else
                        <a href="{{ route('production.reports.handover.print', $t->id) }}" target="_blank" class="p-2 text-slate-600 hover:text-white transition-colors inline-block">
                            <i data-lucide="printer" class="w-4 h-4"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-8 py-12 text-center text-slate-500 italic text-sm">Belum ada data penyerahan barang.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Buat Nota -->
<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-xl rounded-3xl shadow-2xl overflow-hidden">
        <div class="p-8 border-b border-white/5 bg-slate-800/50">
            <h3 class="text-xl font-black text-white uppercase tracking-tight">Buat Nota Penyerahan</h3>
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">NPB (Internal) / PHP (Gudang Barang Jadi)</p>
        </div>
        <form action="{{ route('production.reports.handover.store') }}" method="POST" class="p-10 space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Pilih Work Order*</label>
                    <select name="work_order_id" id="modal_work_order_id" onchange="loadStages(this.value)" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-3 px-4 text-white focus:border-indigo-500 outline-none" required>
                        <option value="">-- Pilih WO --</option>
                        @foreach($workOrders as $wo)
                        <option value="{{ $wo->id }}">{{ $wo->wo_number }} ({{ $wo->customer->name ?? 'Internal' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Pilih Tahapan (Opsional untuk WIP)</label>
                    <select name="work_order_stage_id" id="modal_work_order_stage_id" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-3 px-4 text-white focus:border-indigo-500 outline-none">
                        <option value="">-- Seluruh Work Order (Barang Jadi) --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Tipe Nota*</label>
                    <select name="type" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-3 px-4 text-white focus:border-indigo-500 outline-none" required>
                        <option value="NPB">NPB (Proses Selanjutnya)</option>
                        <option value="PHP">PHP (Gudang Barang Jadi)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Jumlah Realisasi*</label>
                    <input type="number" name="quantity" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-3 px-4 text-white focus:border-indigo-500 outline-none" placeholder="0" required>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Gudang Asal*</label>
                    <select name="from_warehouse_id" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-3 px-4 text-white focus:border-indigo-500 outline-none" required>
                        <option value="">Pilih Gudang Asal</option>
                        @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Gudang Tujuan*</label>
                    <select name="to_warehouse_id" class="w-full bg-slate-900 border border-white/10 rounded-2xl py-3 px-4 text-white focus:border-indigo-500 outline-none" required>
                        <option value="">Pilih Gudang Tujuan</option>
                        @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-4 pt-6">
                <button type="button" onclick="closeModal()" class="flex-1 py-4 text-slate-400 font-black uppercase text-[10px] tracking-widest">Batal</button>
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-indigo-500/20 transition-all">Simpan Nota</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('modal').classList.remove('hidden');
    }
    function closeModal() {
        document.getElementById('modal').classList.add('hidden');
    }

    async function loadStages(woId) {
        const stageSelect = document.getElementById('modal_work_order_stage_id');
        stageSelect.innerHTML = '<option value="">-- Loading... --</option>';
        
        if (!woId) {
            stageSelect.innerHTML = '<option value="">-- Pilih WO Terlebih Dahulu --</option>';
            return;
        }

        try {
            // We can reuse the Shop Floor endpoint or create a new one. 
            // For now, let's assume we can fetch it via a new simple route or just use the existing one if it fits.
            // Actually, I'll use a simple fetch to a new endpoint I'll add.
            const response = await fetch(`/production/work-orders/get-stages/${woId}`);
            const stages = await response.json();
            
            stageSelect.innerHTML = '<option value="">-- Seluruh Work Order (Barang Jadi) --</option>';
            stages.forEach(s => {
                stageSelect.innerHTML += `<option value="${s.id}">Tahapan ${s.sequence}: ${s.name}</option>`;
            });
        } catch (e) {
            stageSelect.innerHTML = '<option value="">-- Gagal memuat tahapan --</option>';
        }
    }
</script>
@endsection
