@extends('layouts.app', ['title' => 'Request Mutasi Gudang'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white uppercase tracking-tight">Request Mutasi Barang</h3>
            <p class="text-slate-400 text-sm italic">Ajukan perpindahan stok antar gudang</p>
        </div>
        <button onclick="openRequestModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest flex items-center gap-2 transition-all shadow-lg shadow-indigo-500/20">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Buat Permintaan Baru
        </button>
    </div>

    <div class="glass-card rounded-[2rem] overflow-hidden border border-white/5 bg-slate-900/20">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] border-b border-white/5">
                    <th class="px-8 py-5">Ref No & Tanggal</th>
                    <th class="px-8 py-5">Work Order</th>
                    <th class="px-8 py-5">Dari Gudang</th>
                    <th class="px-8 py-5">Ke Gudang</th>
                    <th class="px-8 py-5 text-center">Status</th>
                    <th class="px-8 py-5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $m)
                <tr class="hover:bg-white/5 transition-colors">
                    <td class="px-8 py-5">
                        <div class="text-xs text-white font-bold">{{ $m->reference_no }}</div>
                        <div class="text-[10px] text-slate-500 font-medium mt-1">{{ $m->created_at->format('d M Y, H:i') }}</div>
                    </td>
                    <td class="px-8 py-5">
                        @if($m->workOrder)
                        <div class="text-[10px] text-indigo-400 font-black uppercase tracking-widest">{{ $m->workOrder->wo_number }}</div>
                        @else
                        <div class="text-[10px] text-slate-600 font-bold uppercase tracking-widest">-</div>
                        @endif
                    </td>
                    <td class="px-8 py-5">
                        <div class="text-xs text-slate-300 font-bold">{{ $m->fromWarehouse->name }}</div>
                    </td>
                    <td class="px-8 py-5">
                        <div class="text-xs text-slate-300 font-bold">{{ $m->toWarehouse->name }}</div>
                    </td>
                    <td class="px-8 py-5 text-center">
                        @php
                            $statusClasses = [
                                'PENDING' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                'APPROVED' => 'bg-indigo-500/10 text-indigo-500 border-indigo-500/20',
                                'SENDING' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                                'COMPLETED' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                'REJECTED' => 'bg-rose-500/10 text-rose-500 border-rose-500/20',
                            ];
                        @endphp
                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $statusClasses[$m->status] }}">
                            {{ $m->status }}
                        </span>
                    </td>
                    <td class="px-8 py-5 text-right">
                        <button onclick="viewDetails({{ $m->id }})" class="p-2 bg-slate-800 rounded-lg text-slate-400 hover:text-white transition-all"><i data-lucide="eye" class="w-4 h-4"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-8 py-20 text-center text-slate-500">Belum ada permintaan mutasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Request Modal -->
<div id="requestModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-4xl rounded-[2.5rem] flex flex-col max-h-[90vh] overflow-hidden shadow-2xl">
        <div class="p-8 border-b border-white/5 flex justify-between items-center bg-slate-800/30">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-600/20 rounded-2xl flex items-center justify-center text-indigo-400">
                    <i data-lucide="file-plus" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-white tracking-tight">Buat Permintaan Mutasi</h3>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-0.5">Lengkapi detail pemindahan stok</p>
                </div>
            </div>
            <button onclick="closeModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:text-white transition-all"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>

        <div class="flex-1 overflow-y-auto p-10 modal-scroll bg-[#0f172a]/30">
            <form id="requestForm" action="{{ route('mutations.request.store') }}" method="POST" class="space-y-8">
                @csrf
                <div class="grid grid-cols-1 gap-8">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] mb-3 ml-1">Terkait Work Order (Opsional)</label>
                        <select name="work_order_id" class="w-full bg-slate-900/50 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-bold text-sm shadow-inner transition-all appearance-none select2">
                            <option value="">-- Bukan untuk Work Order Spesifik --</option>
                            @foreach($workOrders as $wo)
                            <option value="{{ $wo->id }}" {{ (isset($selected_wo_id) && $selected_wo_id == $wo->id) ? 'selected' : '' }}>{{ $wo->wo_number }} ({{ $wo->status }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-8">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] mb-3 ml-1">Gudang Asal (Pengirim)*</label>
                        <select name="from_warehouse_id" onchange="checkWarehouses(this)" class="w-full bg-slate-900/50 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-bold text-sm shadow-inner transition-all appearance-none" required>
                            <option value="">Pilih Gudang Asal</option>
                            @foreach($warehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] mb-3 ml-1">Gudang Tujuan (Penerima)*</label>
                        <select name="to_warehouse_id" onchange="checkWarehouses(this)" class="w-full bg-slate-900/50 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-bold text-sm shadow-inner transition-all appearance-none" required>
                            <option value="">Pilih Gudang Tujuan</option>
                            @foreach($warehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1">Item yang Diminta*</label>
                        <button type="button" onclick="addItemRow()" class="text-indigo-400 hover:text-indigo-300 text-[10px] font-black uppercase tracking-widest flex items-center gap-1 transition-colors">
                            <i data-lucide="plus" class="w-3 h-3"></i> Tambah Item
                        </button>
                    </div>
                    
                    <div id="itemRows" class="space-y-3">
                        <div class="item-row grid grid-cols-12 gap-4 items-end bg-slate-800/30 p-4 rounded-2xl border border-white/5">
                            <div class="col-span-6">
                                <label class="text-[9px] text-slate-500 font-bold uppercase mb-2 block">Item/SKU</label>
                                <select name="items[0][item_id]" onchange="updateUnitLabel(this)" class="w-full bg-slate-900/80 border border-white/5 rounded-xl py-3 px-4 text-xs text-white outline-none focus:border-indigo-500 transition-all select2" required>
                                    <option value="">Pilih Item</option>
                                    @foreach($items as $i)
                                    <option value="{{ $i->id }}" data-unit="{{ $i->unit->name ?? '-' }}">{{ $i->code }} - {{ $i->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-3">
                                <label class="text-[9px] text-slate-500 font-bold uppercase mb-2 block">Quantity</label>
                                <input type="number" name="items[0][quantity]" step="0.01" class="w-full bg-slate-900/80 border border-white/5 rounded-xl py-3 px-4 text-xs text-white outline-none focus:border-indigo-500 transition-all" required placeholder="0.00">
                            </div>
                            <div class="col-span-2">
                                <label class="text-[9px] text-slate-500 font-bold uppercase mb-2 block">Satuan</label>
                                <input type="text" class="unit-label w-full bg-slate-900/40 border border-transparent rounded-xl py-3 px-4 text-[10px] text-slate-500 font-black uppercase outline-none" readonly value="-">
                            </div>
                            <div class="col-span-1 flex justify-center">
                                <button type="button" class="p-3 text-slate-600 hover:text-rose-500 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] mb-3 ml-1">Catatan Tambahan (Opsional)</label>
                    <textarea name="note" rows="3" class="w-full bg-slate-900/50 border border-white/10 rounded-2xl py-4 px-6 focus:border-indigo-500 outline-none text-white font-bold text-sm shadow-inner transition-all resize-none" placeholder="Alasan mutasi atau instruksi khusus..."></textarea>
                </div>
            </form>
        </div>

        <div class="p-10 border-t border-white/5 bg-slate-800/30 flex justify-end gap-6 shrink-0">
            <button onclick="closeModal()" class="text-xs font-black text-slate-500 uppercase tracking-widest hover:text-white transition-colors">Batalkan</button>
            <button type="button" onclick="validateAndSubmit()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-12 py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-2xl shadow-indigo-500/20 active:scale-[0.98] transition-all">
                Kirim Permintaan
            </button>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-3xl rounded-[2.5rem] overflow-hidden shadow-2xl">
        <div class="p-8 border-b border-white/5 flex justify-between items-center bg-slate-800/30">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-600/20 rounded-2xl flex items-center justify-center text-emerald-400">
                    <i data-lucide="info" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 id="detRef" class="text-xl font-black text-white tracking-tight">Detail Mutasi</h3>
                    <p id="detDate" class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-0.5"></p>
                </div>
            </div>
            <button onclick="closeDetails()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:text-white transition-all"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <div class="p-10 bg-[#0f172a]/30 space-y-8">
            <div class="grid grid-cols-2 gap-8">
                <div class="p-6 bg-slate-800/40 rounded-2xl border border-white/5">
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-2">Dari Gudang</p>
                    <p id="detFrom" class="text-sm font-bold text-white"></p>
                </div>
                <div class="p-6 bg-slate-800/40 rounded-2xl border border-white/5">
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-2">Ke Gudang</p>
                    <p id="detTo" class="text-sm font-bold text-white"></p>
                </div>
            </div>

            <div id="detWOWrapper" class="p-6 bg-indigo-500/5 rounded-2xl border border-indigo-500/10 hidden">
                <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-2">Terkait Work Order</p>
                <p id="detWO" class="text-sm font-black text-white"></p>
            </div>
            
            <div>
                <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] mb-4 ml-1">Daftar Item</h4>
                <div id="detItems" class="space-y-2 max-h-60 overflow-y-auto custom-scroll pr-2">
                    <!-- Dynamic Rows -->
                </div>
            </div>

            <div id="detNoteWrapper" class="p-6 bg-indigo-500/5 rounded-2xl border border-indigo-500/10 hidden">
                <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-2">Catatan</p>
                <p id="detNote" class="text-xs text-slate-300 italic"></p>
            </div>
        </div>
    </div>
</div>

<script>
    let rowCount = 1;

    function openRequestModal() {
        document.getElementById('requestModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('requestModal').classList.add('hidden');
    }

    function checkWarehouses(select) {
        const fromWh = document.querySelector('select[name="from_warehouse_id"]').value;
        const toWh = document.querySelector('select[name="to_warehouse_id"]').value;

        if (fromWh && toWh && fromWh === toWh) {
            showToast('Gudang asal dan tujuan tidak boleh sama!', 'warning');
            select.value = ''; // Reset the select that was just changed
        }
    }

    function updateUnitLabel(select) {
        const unit = select.options[select.selectedIndex].getAttribute('data-unit') || '-';
        select.closest('.item-row').querySelector('.unit-label').value = unit;
    }

    function addItemRow() {
        const row = document.createElement('div');
        row.className = 'item-row grid grid-cols-12 gap-4 items-end bg-slate-800/30 p-4 rounded-2xl border border-white/5';
        row.innerHTML = `
            <div class="col-span-6">
                <select name="items[${rowCount}][item_id]" onchange="updateUnitLabel(this)" class="w-full bg-slate-900/80 border border-white/5 rounded-xl py-3 px-4 text-xs text-white outline-none focus:border-indigo-500 transition-all" required>
                    <option value="">Pilih Item</option>
                    @foreach($items as $i)
                    <option value="{{ $i->id }}" data-unit="{{ $i->unit->name ?? '-' }}">{{ $i->code }} - {{ $i->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-3">
                <input type="number" name="items[${rowCount}][quantity]" step="0.01" class="w-full bg-slate-900/80 border border-white/5 rounded-xl py-3 px-4 text-xs text-white outline-none focus:border-indigo-500 transition-all" required placeholder="0.00">
            </div>
            <div class="col-span-2">
                <input type="text" class="unit-label w-full bg-slate-900/40 border border-transparent rounded-xl py-3 px-4 text-[10px] text-slate-500 font-black uppercase outline-none" readonly value="-">
            </div>
            <div class="col-span-1 flex justify-center">
                <button type="button" onclick="this.closest('.item-row').remove()" class="p-3 text-slate-600 hover:text-rose-500 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
            </div>
        `;
        document.getElementById('itemRows').appendChild(row);
        rowCount++;
        lucide.createIcons();
    }

    function validateAndSubmit() {
        const fromWh = document.querySelector('select[name="from_warehouse_id"]').value;
        const toWh = document.querySelector('select[name="to_warehouse_id"]').value;

        if (!fromWh || !toWh) {
            showToast('Pilih gudang asal dan tujuan!', 'error');
            return;
        }

        if (fromWh === toWh) {
            showToast('Gudang asal dan tujuan tidak boleh sama!', 'warning');
            return;
        }

        document.getElementById('requestForm').submit();
    }

    function viewDetails(id) {
        fetch(`/transactions/mutations/get-details/${id}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('detRef').innerText = data.reference_no;
                document.getElementById('detDate').innerText = new Date(data.created_at).toLocaleString();
                document.getElementById('detFrom').innerText = data.from_warehouse.name;
                document.getElementById('detTo').innerText = data.to_warehouse.name;
                
                const detItems = document.getElementById('detItems');
                detItems.innerHTML = '';
                data.details.forEach(d => {
                    detItems.innerHTML += `
                        <div class="flex justify-between items-center p-4 bg-slate-800/20 rounded-xl border border-white/5">
                            <span class="text-xs text-white font-bold">${d.item.name} <span class="text-[10px] text-slate-500 font-mono ml-2">(${d.item.code})</span></span>
                            <span class="text-sm font-black text-indigo-400">${parseFloat(d.quantity)} <span class="text-[9px] uppercase text-slate-600 ml-1">${d.item.unit?.name || '-'}</span></span>
                        </div>
                    `;
                });
                
                if (data.work_order) {
                    document.getElementById('detWO').innerText = data.work_order.wo_number;
                    document.getElementById('detWOWrapper').classList.remove('hidden');
                } else {
                    document.getElementById('detWOWrapper').classList.add('hidden');
                }

                if (data.note) {
                    document.getElementById('detNote').innerText = data.note;
                    document.getElementById('detNoteWrapper').classList.remove('hidden');
                } else {
                    document.getElementById('detNoteWrapper').classList.add('hidden');
                }

                document.getElementById('detailsModal').classList.remove('hidden');
                lucide.createIcons();
            });
    }

    function closeDetails() {
        document.getElementById('detailsModal').classList.add('hidden');
    }
    // Auto open modal if WO is selected
    document.addEventListener('DOMContentLoaded', function() {
        @if(isset($selected_wo_id) && $selected_wo_id)
            openRequestModal();
        @endif
    });
</script>
@endsection
