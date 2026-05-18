@extends('layouts.app', ['title' => 'Rekap PM & Realisasi'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8">
        <div>
            <h3 class="text-xl font-black text-white uppercase tracking-tight">Rekap PM & Realisasi</h3>
            <p class="text-slate-400 text-sm italic">Monitoring keluar masuk bahan baku & realisasi kiriman bertahap (Gudang & Produksi)</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <form action="{{ route('mutations.rekap.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No PM / WO..." class="bg-slate-900 border border-white/5 rounded-xl px-4 py-2 text-xs text-white outline-none focus:ring-2 focus:ring-indigo-500 transition-all">

                <select name="status" onchange="this.form.submit()" class="bg-slate-900 border border-white/5 rounded-xl px-4 py-2 text-xs text-slate-300 outline-none focus:ring-2 focus:ring-indigo-500 transition-all appearance-none cursor-pointer">
                    <option value="">Status: Semua</option>
                    <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>PENDING</option>
                    <option value="APPROVED" {{ request('status') == 'APPROVED' ? 'selected' : '' }}>APPROVED (Lock)</option>
                    <option value="SENDING" {{ request('status') == 'SENDING' ? 'selected' : '' }}>SENDING (Partial)</option>
                    <option value="COMPLETED" {{ request('status') == 'COMPLETED' ? 'selected' : '' }}>COMPLETED (Done)</option>
                </select>

                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2 rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-500/20">
                    Filter
                </button>

                @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('mutations.rekap.index') }}" class="p-2 bg-rose-500/10 text-rose-500 rounded-xl hover:bg-rose-500/20 transition-all" title="Reset Filter">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
                @endif
            </form>
        </div>
    </div>

    <div class="glass-card rounded-[2rem] border border-white/5 bg-slate-900/20 overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse min-w-[1200px]">
            <thead>
                <tr class="bg-slate-800 text-slate-400 text-[9px] font-black uppercase tracking-wider border-b border-white/5">
                    <th class="px-4 py-4 text-center" style="width: 40px;">No</th>
                    <th class="px-4 py-4" style="width: 90px;">Tanggal</th>
                    <th class="px-4 py-4" style="width: 130px;">No PM</th>
                    <th class="px-4 py-4" style="width: 140px;">Bagian / Rute</th>
                    <th class="px-4 py-4" style="width: 110px;">No WO</th>
                    <th class="px-4 py-4" style="width: 100px;">Kode Item</th>
                    <th class="px-4 py-4">Nama Barang</th>
                    <th class="px-4 py-4 text-right" style="width: 110px;">Permintaan</th>
                    <th class="px-4 py-4 text-center bg-indigo-950/10 border-l border-white/5" style="width: 110px;">Kiriman 1</th>
                    <th class="px-4 py-4 text-center bg-indigo-950/10" style="width: 110px;">Kiriman 2</th>
                    <th class="px-4 py-4 text-center bg-indigo-950/10" style="width: 110px;">Kiriman 3</th>
                    <th class="px-4 py-4 text-center bg-indigo-950/10 border-r border-white/5" style="width: 110px;">Kiriman 4</th>
                    <th class="px-4 py-4 text-right text-amber-400 font-bold" style="width: 100px;">Kekurangan</th>
                    <th class="px-4 py-4 text-center" style="width: 90px;">Status</th>
                    <th class="px-4 py-4 text-center" style="width: 90px;">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @php $no = ($data->currentPage() - 1) * $data->perPage() + 1; @endphp
                @forelse($data as $m)
                    @foreach($m->details as $idx => $d)
                        @php
                            // Get all deliveries for this specific item in this mutation
                            $deliveries = $m->deliveries->where('item_id', $d->item_id)->values();
                            
                            // Handle legacy or full completed mutations
                            if ($m->status === 'COMPLETED' && $deliveries->isEmpty()) {
                                $mockDelivery = (object)[
                                    'quantity' => $d->quantity,
                                    'delivered_at' => $m->completed_at ?? $m->updated_at ?? $m->created_at,
                                    'sender' => (object)['name' => $m->receiver->name ?? $m->user->name ?? 'System']
                                ];
                                $deliveries = collect([$mockDelivery]);
                            }

                            $totalDelivered = $deliveries->sum('quantity');
                            $shortage = $m->status === 'COMPLETED' ? 0 : ($d->quantity - $totalDelivered);
                            $shortage = $shortage < 0 ? 0 : $shortage;
                            
                            $rowStatus = ($m->status === 'COMPLETED' || $shortage <= 0) ? 'DONE' : 'PENDING';
                        @endphp
                        <tr class="hover:bg-white/5 transition-colors text-xs">
                            @if($idx === 0)
                            <td class="px-4 py-4 text-center font-bold text-slate-500" rowspan="{{ $m->details->count() }}">
                                {{ $no++ }}
                            </td>
                            <td class="px-4 py-4 text-slate-400" rowspan="{{ $m->details->count() }}">
                                {{ $m->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-4 font-bold text-white" rowspan="{{ $m->details->count() }}">
                                <span class="block">{{ $m->reference_no }}</span>
                            </td>
                            <td class="px-4 py-4 text-slate-400" rowspan="{{ $m->details->count() }}">
                                <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest">DARI: {{ $m->fromWarehouse->name }}</div>
                                <div class="text-[10px] font-black text-emerald-400 uppercase tracking-widest mt-0.5">KE: {{ $m->toWarehouse->name }}</div>
                            </td>
                            <td class="px-4 py-4" rowspan="{{ $m->details->count() }}">
                                @if($m->workOrder)
                                <span class="px-2 py-0.5 bg-indigo-500/10 text-indigo-400 text-[10px] font-black uppercase rounded border border-indigo-500/10">
                                    {{ $m->workOrder->wo_number }}
                                </span>
                                @else
                                <span class="text-slate-600">-</span>
                                @endif
                            </td>
                            @endif

                            <td class="px-4 py-4 font-bold text-slate-400">
                                {{ $d->item->code }}
                            </td>
                            <td class="px-4 py-4 text-white font-medium">
                                {{ $d->item->name }}
                            </td>
                            <td class="px-4 py-4 text-right font-black text-slate-200">
                                {{ number_format($d->quantity + 0) }} <small class="text-[9px] text-slate-500 font-bold ml-0.5">{{ $d->item->unit->name }}</small>
                            </td>

                            {{-- Kiriman 1 - 4 Columns --}}
                            @for($i = 0; $i < 4; $i++)
                            <td class="px-4 py-4 text-center bg-indigo-950/[0.03] {{ $i === 0 ? 'border-l border-white/5' : '' }} {{ $i === 3 ? 'border-r border-white/5' : '' }}">
                                @if(isset($deliveries[$i]))
                                    <div class="text-xs text-white font-black">{{ number_format($deliveries[$i]->quantity + 0) }} <small class="text-[8px] text-slate-500">{{ $d->item->unit->name }}</small></div>
                                    <div class="text-[8px] text-slate-500 mt-0.5" title="Dikirim oleh: {{ $deliveries[$i]->sender->name ?? '-' }}">{{ $deliveries[$i]->delivered_at->format('d/m H:i') }}</div>
                                @else
                                    <span class="text-slate-700">-</span>
                                @endif
                            </td>
                            @endfor

                            {{-- Shortage --}}
                            <td class="px-4 py-4 text-right font-black {{ $shortage > 0 ? 'text-amber-400' : 'text-slate-500' }}">
                                @if($shortage > 0)
                                {{ number_format($shortage + 0) }} <small class="text-[9px] text-amber-500/50 font-bold ml-0.5">{{ $d->item->unit->name }}</small>
                                @else
                                0
                                @endif
                            </td>

                            <td class="px-4 py-4 text-center">
                                @if($rowStatus === 'DONE')
                                <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-[9px] font-black tracking-widest uppercase">
                                    DONE
                                </span>
                                @else
                                <span class="px-2 py-0.5 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-full text-[9px] font-black tracking-widest uppercase">
                                    PENDING
                                </span>
                                @endif
                            </td>

                            @if($idx === 0)
                            <td class="px-4 py-4 text-center" rowspan="{{ $m->details->count() }}">
                                @if(in_array($m->status, ['APPROVED', 'SENDING']))
                                <button onclick="openDeliveryModal({{ json_encode($m) }}, {{ json_encode($m->details->map(function($det) use ($m) {
                                    $sent = $m->deliveries->where('item_id', $det->item_id)->sum('quantity');
                                    $rem = $det->quantity - $sent;
                                    return [
                                        'item_id' => $det->item_id,
                                        'name' => $det->item->name,
                                        'code' => $det->item->code,
                                        'unit' => $det->item->unit->name,
                                        'requested' => $det->quantity + 0,
                                        'already_sent' => $sent + 0,
                                        'remaining' => $rem > 0 ? $rem + 0 : 0
                                    ];
                                })) }})" class="bg-indigo-600/20 hover:bg-indigo-600 text-indigo-400 hover:text-white px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all border border-indigo-500/20 flex items-center gap-1.5 mx-auto">
                                    <i data-lucide="send" class="w-3 h-3"></i> Kirim
                                </button>
                                @else
                                <span class="text-slate-600 text-[10px] italic">No Action</span>
                                @endif
                            </td>
                            @endif
                        </tr>
                    @endforeach
                @empty
                <tr>
                    <td colspan="15" class="text-center py-16 text-slate-600 text-xs italic">
                        Tidak ada data Rekap PM yang ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $data->links() }}
    </div>
</div>

{{-- POP-UP MODAL KIRIM CICILAN --}}
<div id="delivery_modal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm hidden flex items-center justify-center z-50 transition-all p-4">
    <div class="glass-card w-full max-w-xl p-8 rounded-[2.5rem] border border-white/5 bg-slate-900/90 relative overflow-hidden shadow-2xl">
        <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-600/5 blur-[100px] rounded-full -mr-32 -mt-32"></div>

        <div class="flex justify-between items-center mb-6 relative z-10">
            <div>
                <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest">LOGISTIK GUDANG</span>
                <h3 class="text-lg font-black text-white uppercase tracking-tight mt-1" id="modal_title">Input Realisasi Kiriman</h3>
            </div>
            <button onclick="closeDeliveryModal()" class="w-10 h-10 bg-slate-800 hover:bg-rose-500/20 hover:text-rose-500 text-slate-400 rounded-full flex items-center justify-center transition-all border border-white/5">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="modal_form" method="POST">
            @csrf
            <div class="space-y-4 max-h-96 overflow-y-auto pr-2 custom-scrollbar relative z-10" id="modal_item_container">
                {{-- Dynamic Inputs will be loaded here --}}
            </div>

            <div class="flex justify-end gap-4 mt-8 relative z-10">
                <button type="button" onclick="closeDeliveryModal()" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all">
                    Batal
                </button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-indigo-500/20">
                    Simpan Pengiriman
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openDeliveryModal(mutation, items) {
        document.getElementById('modal_title').innerText = 'Kirim Bahan Baku - ' + mutation.reference_no;
        document.getElementById('modal_form').action = "{{ url('transactions/mutations/deliver-partial') }}/" + mutation.id;
        
        let container = document.getElementById('modal_item_container');
        container.innerHTML = '';

        items.forEach((item, index) => {
            let row = document.createElement('div');
            row.className = 'p-4 bg-white/[0.02] rounded-2xl border border-white/5 space-y-3';
            row.innerHTML = `
                <div class="flex justify-between items-center text-xs">
                    <div>
                        <span class="px-2 py-0.5 bg-white/5 text-slate-400 text-[9px] font-black rounded">${item.code}</span>
                        <strong class="text-white ml-2">${item.name}</strong>
                    </div>
                    <div class="text-slate-400 text-[10px]">
                        Minta: <strong>${item.requested} ${item.unit}</strong> | Sisa: <strong class="text-amber-400">${item.remaining} ${item.unit}</strong>
                    </div>
                </div>
                <input type="hidden" name="items[${index}][item_id]" value="${item.item_id}">
                <div class="flex items-center gap-4">
                    <label class="text-[10px] text-slate-500 font-bold uppercase tracking-widest whitespace-nowrap">Qty Kirim Saat Ini</label>
                    <div class="relative w-full">
                        <input type="number" step="0.01" min="0" max="${item.remaining}" name="items[${index}][quantity]" value="${item.remaining}" required class="w-full bg-slate-900 border border-white/5 rounded-xl px-4 py-2 text-xs font-bold text-white outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-600 uppercase">${item.unit}</span>
                    </div>
                </div>
            `;
            container.appendChild(row);
        });

        document.getElementById('delivery_modal').classList.remove('hidden');
    }

    function closeDeliveryModal() {
        document.getElementById('delivery_modal').classList.add('hidden');
    }
</script>
@endsection
