@extends('layouts.app', ['title' => 'Approval Stock Opname'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-8">
        <div>
            <h3 class="text-xl font-black text-white uppercase tracking-tight">Stock Opname Authorization</h3>
            <p class="text-slate-400 text-sm italic">Review and validate inventory adjustments</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('opname.approval.index') }}" method="GET" class="flex items-center gap-3">
                <select name="warehouse_id" onchange="this.form.submit()" class="bg-slate-800/50 border border-white/5 rounded-xl px-4 py-2 text-xs font-bold text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $w)
                    <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
                @if(request('warehouse_id'))
                <a href="{{ route('opname.approval.index') }}" class="p-2 bg-rose-500/10 text-rose-500 rounded-xl hover:bg-rose-500/20 transition-all" title="Reset Filter">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
                @endif
            </form>
        </div>
    </div>

    <div class="glass-card rounded-[2rem] border border-white/5 bg-slate-900/20">
        <table class="w-full text-left">
            <thead class="sticky top-[-1.5rem] lg:top-[-2.5rem] z-20">
                <tr class="bg-[#1e293b] backdrop-blur-md text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] border-b border-white/5">
                    <th class="px-8 py-5">Item Info</th>
                    <th class="px-8 py-5">Lokasi Gudang</th>
                    <th class="px-8 py-5 text-center">System Qty</th>
                    <th class="px-8 py-5 text-center">Physical Qty</th>
                    <th class="px-8 py-5 text-center">Difference</th>
                    <th class="px-8 py-5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $o)
                <tr class="hover:bg-white/5 transition-colors">
                    <td class="px-8 py-5">
                        <div class="text-xs text-white font-bold">{{ $o->item->name }}</div>
                        <div class="text-[10px] text-slate-500 font-medium mt-1">Oleh: {{ $o->user->name }}</div>
                    </td>
                    <td class="px-8 py-5">
                        <div class="text-[13px] text-indigo-400 font-black tracking-tight">{{ $o->warehouse->name }}</div>
                    </td>
                    <td class="px-8 py-5 text-center">
                        <span class="text-xs text-slate-400 font-bold">{{ $o->system_qty + 0 }}</span>
                    </td>
                    <td class="px-8 py-5 text-center">
                        <span class="text-xs text-white font-black">{{ $o->physical_qty + 0 }}</span>
                    </td>
                    <td class="px-8 py-5 text-center">
                        @if($o->difference > 0)
                        <span class="text-xs text-emerald-500 font-black">+{{ $o->difference + 0 }}</span>
                        @elseif($o->difference < 0)
                        <span class="text-xs text-rose-500 font-black">{{ $o->difference + 0 }}</span>
                        @else
                        <span class="text-xs text-slate-600 font-bold">No Diff</span>
                        @endif
                    </td>
                    <td class="px-8 py-5 text-right space-x-2">
                        <button type="button" 
                            onclick="rejectOpname({{ $o->id }})"
                            class="p-2 bg-slate-800 rounded-lg text-slate-500 hover:text-rose-500 transition-all">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                        
                        <form id="reject-form-{{ $o->id }}" action="{{ route('opname.reject', $o->id) }}" method="POST" class="hidden">
                            @csrf
                            <input type="hidden" name="rejection_reason" id="reason-{{ $o->id }}">
                        </form>

                        <form action="{{ route('opname.approve', $o->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all">
                                Approve
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-8 py-20 text-center text-slate-500 italic">Tidak ada antrian persetujuan opname.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function rejectOpname(id) {
        Swal.fire({
            title: 'REJECT STOCK OPNAME',
            text: 'Harap berikan alasan penolakan untuk stock opname ini:',
            input: 'textarea',
            inputPlaceholder: 'Tuliskan alasan reject di sini...',
            inputAttributes: {
                'aria-label': 'Tuliskan alasan reject di sini'
            },
            showCancelButton: true,
            confirmButtonText: 'REJECT SEKARANG',
            cancelButtonText: 'BATAL',
            confirmButtonColor: '#e11d48',
            inputValidator: (value) => {
                if (!value) {
                    return 'Alasan reject wajib diisi!'
                }
                if (value.length < 5) {
                    return 'Alasan reject minimal 5 karakter!'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('reason-' + id).value = result.value;
                document.getElementById('reject-form-' + id).submit();
            }
        });
    }
</script>
@endsection
