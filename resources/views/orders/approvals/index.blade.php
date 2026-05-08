@extends('layouts.app', ['title' => 'Approval Request'])

@section('content')
<div class="space-y-6">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h3 class="text-xl font-bold text-white">Persetujuan Permintaan</h3>
                <p class="text-slate-400 text-sm">Review dan setujui permintaan stok dari gudang</p>
            </div>
            <button onclick="openHistoryModal()" class="bg-slate-700 hover:bg-slate-600 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all border border-white/10 shadow-lg">
                <i data-lucide="history" class="w-4 h-4 text-indigo-400"></i> Lihat Riwayat
            </button>
        </div>

        <!-- Table Section -->
        <div class="glass-card rounded-xl overflow-hidden border border-white/5">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-800/50 text-slate-400 text-[10px] uppercase tracking-widest border-b border-white/5">
                        <th class="px-6 py-4">No. Ref</th>
                        <th class="px-6 py-4">Pemohon / Gudang</th>
                        <th class="px-6 py-4">Daftar Item</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($data as $item)
                    <tr class="hover:bg-white/5 transition-colors group">
                        <td class="px-6 py-4 font-mono text-sm text-indigo-400">{{ $item->reference_no }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-white">{{ $item->user->name }}</div>
                            <div class="text-[10px] text-slate-500 uppercase font-black">{{ $item->warehouse->name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                @foreach($item->details as $d)
                                <div class="text-xs text-slate-300">• {{ $d->item->name }} ({{ number_format($d->quantity) }})</div>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <form action="{{ route('orders.approvals.approve', $item->id) }}" method="POST" class="inline">
                                @csrf
                                <button class="bg-emerald-600/10 hover:bg-emerald-600 text-emerald-500 hover:text-white px-4 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all">Approve</button>
                            </form>
                            <button onclick="openRejectModal({{ $item->id }})" class="bg-rose-600/10 hover:bg-rose-600 text-rose-500 hover:text-white px-4 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all">Reject</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-20 text-center text-slate-500">No pending approvals.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- History Modal -->
    <div id="historyModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-[#1e293b] border border-white/10 w-full max-w-5xl rounded-2xl flex flex-col max-h-[90vh] shadow-2xl">
            <div class="p-6 border-b border-white/5 bg-slate-800/50 rounded-t-2xl flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-500/20 rounded-lg">
                        <i data-lucide="history" class="w-5 h-5 text-indigo-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white">Riwayat Persetujuan</h3>
                </div>
                <button onclick="closeHistoryModal()" class="text-slate-400 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="overflow-y-auto p-4 modal-scroll">
                <div class="glass-card rounded-xl overflow-hidden border border-white/5">
                    <table class="w-full text-left">
                        <thead class="sticky top-0 z-10">
                            <tr class="bg-slate-900 text-slate-400 text-[10px] uppercase tracking-widest border-b border-white/5">
                                <th class="px-6 py-4">No. Ref</th>
                                <th class="px-6 py-4">Gudang</th>
                                <th class="px-6 py-4">Item</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4">Tgl Proses</th>
                                <th class="px-6 py-4">Diproses Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($history as $h)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 font-mono text-xs text-indigo-400">{{ $h->reference_no }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-[10px] text-white font-bold">{{ $h->warehouse->name }}</div>
                                    <div class="text-[9px] text-slate-500">Pemohon: {{ $h->user->name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-[10px] text-slate-300">
                                        {{ $h->details->count() }} Items
                                        <span class="text-[9px] text-slate-500 ml-1">({{ $h->details->take(2)->map(fn($d) => $d->item->name)->implode(', ') }}...)</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase {{ $h->status == 'APPROVED' ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' : ($h->status == 'REJECTED' ? 'bg-rose-500/10 text-rose-500 border border-rose-500/20' : 'bg-indigo-500/10 text-indigo-500 border border-indigo-500/20') }}">
                                        {{ $h->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-[10px] text-slate-400">{{ $h->updated_at->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-[10px] text-white italic font-medium">{{ $h->approver->name ?? 'System' }}</div>
                                    @if($h->status == 'REJECTED' && $h->rejection_note)
                                        <div class="text-[9px] text-rose-400 mt-1 max-w-[200px] truncate" title="{{ $h->rejection_note }}">Note: {{ $h->rejection_note }}</div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">Belum ada riwayat proses.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="p-6 border-t border-white/5 bg-slate-800/50 rounded-b-2xl flex justify-end">
                <button onclick="closeHistoryModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-2 rounded-lg font-bold shadow-lg transition-all">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-[#1e293b] border border-white/10 w-full max-w-md rounded-2xl shadow-2xl">
            <div class="p-6 border-b border-white/5 bg-slate-800/50 rounded-t-2xl flex justify-between items-center">
                <h3 class="text-lg font-bold text-white">Alasan Penolakan</h3>
                <button onclick="closeRejectModal()" class="text-slate-400 hover:text-white transition-colors"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <form id="rejectForm" method="POST" class="p-8 space-y-6">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Catatan Revisi / Alasan*</label>
                    <textarea name="rejection_note" rows="3" class="w-full bg-[#0f172a] border border-white/10 rounded-lg py-2.5 px-4 focus:border-rose-500 outline-none text-white text-sm" required placeholder="Jelaskan apa yang harus direvisi..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeRejectModal()" class="px-6 py-2 text-slate-400 font-bold">Batal</button>
                    <button type="submit" class="bg-rose-600 text-white px-8 py-2 rounded-lg font-bold">Tolak Permintaan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRejectModal(id) {
            document.getElementById('rejectForm').action = `/orders/approvals/reject/${id}`;
            document.getElementById('rejectModal').classList.remove('hidden');
        }
        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
        
        function openHistoryModal() {
            document.getElementById('historyModal').classList.remove('hidden');
        }
        function closeHistoryModal() {
            document.getElementById('historyModal').classList.add('hidden');
        }
    </script>
@endsection
