@extends('layouts.app', ['title' => 'Create Purchase Order'])

@section('content')
<style>
    select option {
        background-color: #1e293b;
        color: white;
    }
</style>
<div class="space-y-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-xl font-bold text-white">Purchase Order</h3>
            <p class="text-slate-400 text-sm">Kelola pesanan pembelian barang ke supplier</p>
        </div>
        <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all shadow-lg shadow-indigo-500/20">
            <i data-lucide="plus" class="w-4 h-4"></i> Buat PO Baru
        </button>
    </div>

    <!-- Table Section -->
    <div class="glass-card rounded-xl overflow-hidden border border-white/5">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[12px] uppercase tracking-widest border-b border-white/5">
                    <th class="px-6 py-4">No. PO</th>
                    <th class="px-6 py-4">Supplier</th>
                    <th class="px-6 py-4">Ref. Request</th>
                    <th class="px-6 py-4">Tgl Order</th>
                    <th class="px-6 py-4">Total Amount</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($data as $item)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-6 py-4 font-mono text-sm text-indigo-400">{{ $item->po_no }}</td>
                    <td class="px-6 py-4 text-white text-sm">{{ $item->supplier->name }}</td>
                    <td class="px-6 py-4 text-xs text-slate-400">{{ $item->request->reference_no ?? 'Direct PO' }}</td>
                    <td class="px-6 py-4 text-xs text-white">{{ $item->order_date }}</td>
                    <td class="px-6 py-4 text-sm font-bold text-emerald-400">Rp {{ number_format($item->total_amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase {{ $item->status == 'OPEN' ? 'bg-indigo-500/10 text-indigo-500' : ($item->status == 'CLOSED' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-amber-500/10 text-amber-500') }}">
                            {{ $item->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button onclick="viewDetail({{ $item->toJson() }})" class="p-2 text-slate-500 hover:text-white"><i data-lucide="eye" class="w-4 h-4"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-20 text-center text-slate-500">No Purchase Orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Create PO -->
<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-[1400px] rounded-2xl flex flex-col shadow-2xl overflow-hidden h-[90vh]">
        <div class="p-6 border-b border-white/5 flex justify-between items-center bg-slate-800/50">
            <h3 class="text-lg font-bold text-white">Buat Purchase Order</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <div class="p-8 bg-[#0f172a]/50 flex-1 overflow-y-auto">
            <form id="poForm" action="{{ route('orders.po.store') }}" method="POST" class="space-y-8">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-1">Pilih Supplier*</label>
                        <select name="supplier_id" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white text-sm" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-1">Tgl Order*</label>
                        <input type="date" name="order_date" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white text-sm" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold text-slate-500 uppercase tracking-widest mb-1">Ref. Request (Opsional)</label>
                        <select name="item_request_id" class="w-full bg-[#1e293b] border border-white/10 rounded-lg py-2.5 px-4 focus:border-indigo-500 outline-none text-white text-sm">
                            <option value="">Tanpa Request</option>
                            @foreach($approvedRequests as $ar)
                            <option value="{{ $ar->id }}">{{ $ar->reference_no }} ({{ $ar->user->name }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="manualItemSection" class="pt-6 border-t border-white/5">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center gap-4">
                            <h4 class="text-xs font-bold text-indigo-400 uppercase tracking-widest italic">Daftar Item (Manual)</h4>
                            <div class="w-64">
                                <select id="filterType" class="w-full bg-[#0f172a] border border-white/10 rounded-lg py-1.5 px-3 focus:border-indigo-500 outline-none text-white text-[10px] font-bold uppercase">
                                    <option value="">Semua Tipe</option>
                                    @foreach($types as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button id="cleanupBtn" type="button" onclick="removeInvalidItems()" class="hidden text-xs font-black bg-rose-500/10 text-rose-500 px-4 py-2 rounded-lg hover:bg-rose-500 hover:text-white transition-all flex items-center gap-2 uppercase tracking-widest border border-rose-500/20">
                                <i data-lucide="filter-x" class="w-4 h-4"></i> Bersihkan Item Invalid
                            </button>
                            <button type="button" onclick="addItem()" class="text-xs font-black bg-indigo-500/10 text-indigo-400 px-4 py-2 rounded-lg hover:bg-indigo-500 hover:text-white transition-all flex items-center gap-2 uppercase tracking-widest">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Item
                            </button>
                        </div>
                    </div>
                    
                    <div class="bg-slate-900/40 rounded-2xl border border-white/5 overflow-hidden">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5 bg-slate-800/30">
                                    <th class="px-6 py-4">Product Name</th>
                                    <th class="px-6 py-4 text-center w-32">Qty</th>
                                    <th class="px-6 py-4 text-center w-48">Unit Price (Rp)</th>
                                    <th class="px-6 py-4 text-center w-48">Subtotal</th>
                                    <th class="px-6 py-4 text-right w-16"></th>
                                </tr>
                            </thead>
                            <tbody id="itemList">
                                <tr class="item-row border-b border-white/5">
                                    <td class="px-6 py-4">
                                        <select name="items[0][id]" class="item-select w-full bg-transparent border-none focus:ring-0 text-white text-sm font-bold" onchange="fetchPrice(this)">
                                            <option value="">-- Pilih Item --</option>
                                            @foreach($items as $it)
                                            <option value="{{ $it->id }}" data-type-id="{{ $it->type_id }}">{{ $it->name }} ({{ $it->code }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" name="items[0][quantity]" class="qty-input w-full bg-slate-800/50 border border-white/10 rounded-lg py-2 px-3 text-center text-white font-bold text-[12px]" value="1" oninput="calculateSubtotal(this)">
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="hidden" name="items[0][price]" class="actual-price-input" value="0">
                                        <input type="text" class="price-input w-full bg-slate-800/50 border border-white/10 rounded-lg py-1 px-3 text-center text-white font-bold" value="0" oninput="formatAndCalculate(this)">
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="subtotal-display text-emerald-400 font-bold">Rp 0</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button type="button" onclick="removeItem(this)" class="text-rose-500 hover:bg-rose-500/10 p-2 rounded-lg transition-all"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="requestNoteSection" class="p-6 bg-amber-500/10 border border-amber-500/20 rounded-2xl hidden">
                    <div class="flex items-center gap-4">
                        <i data-lucide="info" class="w-6 h-6 text-amber-500"></i>
                        <p class="text-xs text-amber-500 font-bold leading-relaxed italic uppercase">
                            Catatan: Karena memilih Ref. Request, item akan otomatis terisi sesuai dengan data permintaan yang telah disetujui. Harga akan diambil dari daftar harga terbaru.
                        </p>
                    </div>
                </div>
            </form>
        </div>
        <div class="p-8 border-t border-white/5 bg-slate-800/50 flex justify-between items-center rounded-b-2xl">
            <div>
                <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest">Grand Total Estimate</p>
                <h4 id="grandTotal" class="text-2xl font-black text-emerald-400">Rp 0</h4>
            </div>
            <div class="flex gap-4">
                <form id="cancelRequestForm" method="POST" class="hidden">@csrf</form>
                <button id="cancelRequestBtn" type="button" onclick="confirmCancelRequest()" class="hidden bg-rose-500/10 hover:bg-rose-500 text-rose-500 hover:text-white px-8 py-3 rounded-xl font-black uppercase tracking-widest text-xs transition-all">Cancel Request</button>
                <button onclick="closeModal()" class="px-8 py-3 text-slate-400 font-bold uppercase tracking-widest text-xs">Batal</button>
                <button type="submit" form="poForm" class="bg-indigo-600 hover:bg-indigo-700 text-white px-12 py-3 rounded-xl font-black uppercase tracking-widest text-xs shadow-lg shadow-indigo-500/20 active:scale-95 transition-all">Buat PO</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail PO -->
<div id="detailModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-5xl rounded-[2.5rem] flex flex-col shadow-2xl overflow-hidden h-[85vh]">
        <div class="p-8 border-b border-white/5 flex justify-between items-center bg-slate-800/30">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white">
                    <i data-lucide="eye" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 id="det_po_no" class="text-xl font-black text-white tracking-tight uppercase"></h3>
                    <p id="det_supplier" class="text-[12px] text-slate-500 font-bold uppercase tracking-widest mt-0.5"></p>
                </div>
            </div>
            <button onclick="closeDetailModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:text-white transition-all"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <div class="p-8 overflow-y-auto max-h-[60vh]">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">
                        <th class="px-4 py-3">Item Name</th>
                        <th class="px-4 py-3 text-center">Qty</th>
                        <th class="px-4 py-3 text-center">Received</th>
                        <th class="px-4 py-3 text-right">Price</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="det_rows" class="divide-y divide-white/5"></tbody>
            </table>
        </div>
        <div class="p-8 border-t border-white/5 bg-slate-800/30 flex justify-between items-center">
            <div id="det_status" class="px-4 py-1.5 rounded-xl font-black text-[10px] uppercase"></div>
            <div class="text-right">
                <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest">Total Amount</p>
                <h4 id="det_total" class="text-xl font-black text-emerald-400"></h4>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmation Cancel Request -->
<div id="confirmCancelModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/80 backdrop-blur-md p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-md rounded-[2.5rem] flex flex-col shadow-2xl overflow-hidden">
        <div class="p-10 text-center space-y-6">
            <div class="w-20 h-20 bg-rose-500/10 rounded-full flex items-center justify-center text-rose-500 mx-auto">
                <i data-lucide="alert-triangle" class="w-10 h-10"></i>
            </div>
            <div>
                <h3 class="text-xl font-black text-white tracking-tight uppercase">Batalkan Request?</h3>
                <p class="text-sm text-slate-400 font-bold leading-relaxed mt-4">Apakah Anda yakin ingin membatalkan Request ini? Data permintaan yang dibatalkan tidak akan bisa diproses kembali menjadi PO.</p>
            </div>
            <div class="flex flex-col gap-3 pt-4">
                <button onclick="executeCancelRequest()" class="w-full bg-rose-600 hover:bg-rose-700 text-white py-4 rounded-2xl font-black uppercase tracking-widest text-xs shadow-lg shadow-rose-500/20 active:scale-95 transition-all">Ya, Batalkan Request</button>
                <button onclick="closeConfirmModal()" class="w-full bg-slate-800 hover:bg-slate-700 text-slate-300 py-4 rounded-2xl font-black uppercase tracking-widest text-xs transition-all">Tidak, Simpan Saja</button>
            </div>
        </div>
    </div>
</div>

<script>
    let itemIndex = 1;
    let supplierItems = [];
    let rowTemplate = null;

    document.addEventListener('DOMContentLoaded', function() {
        const firstRow = document.querySelector('.item-row');
        if (firstRow) {
            rowTemplate = firstRow.cloneNode(true);
        }
    });

    async function validateItemsBySupplier() {
        const supplierId = document.querySelector('select[name="supplier_id"]').value;
        const cleanupBtn = document.getElementById('cleanupBtn');
        
        if (!supplierId) {
            supplierItems = [];
            cleanupBtn.classList.add('hidden');
            // Reset warnings
            document.querySelectorAll('.item-row').forEach(row => {
                row.classList.remove('bg-rose-500/10', 'border-rose-500/20');
                const warning = row.querySelector('.supplier-warning');
                if (warning) warning.remove();
            });
            return;
        }

        try {
            const resp = await fetch(`/master/suppliers/get-items/${supplierId}`);
            supplierItems = await resp.json();
            let hasInvalid = false;
            
            document.querySelectorAll('.item-row').forEach(row => {
                const itemIdInput = row.querySelector('input[type="hidden"][name$="[id]"]') || row.querySelector('select.item-select');
                if (!itemIdInput) return;
                
                const itemId = itemIdInput.value;
                
                if (itemId && !supplierItems.includes(parseInt(itemId))) {
                    hasInvalid = true;
                    row.classList.add('bg-rose-500/10', 'border-rose-500/20');
                    if (!row.querySelector('.supplier-warning')) {
                        const nameTd = row.querySelector('td:first-child');
                        const warning = document.createElement('div');
                        warning.className = 'supplier-warning text-[9px] text-rose-500 font-black uppercase tracking-widest mt-1 p-1 bg-rose-500/5 rounded border border-rose-500/10 w-fit';
                        warning.innerHTML = '<i data-lucide="alert-circle" class="w-2.5 h-2.5 inline mr-1"></i> Not in Supplier Price List';
                        nameTd.appendChild(warning);
                        lucide.createIcons();
                    }
                } else {
                    row.classList.remove('bg-rose-500/10', 'border-rose-500/20');
                    const warning = row.querySelector('.supplier-warning');
                    if (warning) warning.remove();
                }
            });

            if (hasInvalid) {
                cleanupBtn.classList.remove('hidden');
            } else {
                cleanupBtn.classList.add('hidden');
            }
        } catch (e) { console.error(e); }
    }

    function removeInvalidItems() {
        Swal.fire({
            title: 'BERSIHKAN ITEM?',
            text: 'Item yang tidak terdaftar di supplier ini akan dihapus dari daftar PO.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'YA, HAPUS',
            cancelButtonText: 'BATAL'
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelectorAll('.item-row').forEach(row => {
                    if (row.classList.contains('bg-rose-500/10')) {
                        row.remove();
                    }
                });
                if (document.querySelectorAll('.item-row').length === 0) {
                    addItem();
                }
                calculateGrandTotal();
                validateItemsBySupplier();
            }
        });
    }

    document.querySelector('select[name="supplier_id"]').addEventListener('change', validateItemsBySupplier);

    function formatRupiah(angka) {
        var number_string = angka.toString().replace(/[^,\d]/g, ''),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    function unformatRupiah(angka) {
        return angka.toString().replace(/\./g, '');
    }

    function formatAndCalculate(input) {
        input.value = formatRupiah(input.value);
        calculateSubtotal(input);
    }

    function calculateSubtotal(el) {
        const row = el.closest('.item-row');
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const priceText = row.querySelector('.price-input').value;
        const price = parseFloat(unformatRupiah(priceText)) || 0;
        
        // Sync to hidden input
        row.querySelector('.actual-price-input').value = price;
        
        const subtotal = qty * price;
        row.querySelector('.subtotal-display').innerText = 'Rp ' + formatRupiah(subtotal);
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(unformatRupiah(row.querySelector('.price-input').value)) || 0;
            total += qty * price;
        });
        document.getElementById('grandTotal').innerText = 'Rp ' + formatRupiah(total);
    }

    async function fetchPrice(select) {
        const itemId = select.value;
        const row = select.closest('.item-row');
        if (!itemId) return;

        validateItemsBySupplier(); // Validate when item changes

        try {
            const resp = await fetch(`/master/price-lists/get-price?item_id=${itemId}`);
            const data = await resp.json();
            row.querySelector('.price-input').value = formatRupiah(data.hna_ppn || 0);
            calculateSubtotal(row.querySelector('.price-input'));
        } catch (e) { console.error(e); }
    }

    function addItem() {
        const list = document.getElementById('itemList');
        if (!rowTemplate) return;
        
        const row = rowTemplate.cloneNode(true);
        
        const select = row.querySelector('select');
        select.name = `items[${itemIndex}][id]`;
        select.value = '';
        
        const qtyInput = row.querySelector('.qty-input');
        qtyInput.name = `items[${itemIndex}][quantity]`;
        qtyInput.value = '1';
        
        const priceInput = row.querySelector('.price-input');
        priceInput.value = '0';
        
        const actualPriceInput = row.querySelector('.actual-price-input');
        actualPriceInput.name = `items[${itemIndex}][price]`;
        actualPriceInput.value = '0';

        row.querySelector('.subtotal-display').innerText = 'Rp 0';
        
        list.appendChild(row);
        itemIndex++;
        lucide.createIcons();
        applyFilter();
    }

    function applyFilter() {
        const typeId = document.getElementById('filterType').value;
        const selects = document.querySelectorAll('.item-select');
        
        selects.forEach(select => {
            const options = select.querySelectorAll('option');
            options.forEach(opt => {
                if (!opt.value) return;
                const itemTypeId = opt.getAttribute('data-type-id');
                if (!typeId || itemTypeId == typeId) {
                    opt.style.display = 'block';
                } else {
                    opt.style.display = 'none';
                    if (select.value == opt.value) select.value = '';
                }
            });
        });
    }

    document.getElementById('filterType').addEventListener('change', applyFilter);

    function removeItem(btn) {
        const list = document.getElementById('itemList');
        btn.closest('.item-row').remove();
        
        if (document.querySelectorAll('.item-row').length === 0) {
            addItem();
        }
        calculateGrandTotal();
    }

    const requestSelect = document.querySelector('select[name="item_request_id"]');
    const manualItemSection = document.getElementById('manualItemSection');
    const requestNoteSection = document.getElementById('requestNoteSection');

    requestSelect.addEventListener('change', async function() {
        const list = document.getElementById('itemList');
        const cancelBtn = document.getElementById('cancelRequestBtn');
        list.innerHTML = ''; // Clear existing rows
        itemIndex = 0;

        if (this.value) {
            requestNoteSection.classList.remove('hidden');
            cancelBtn.classList.remove('hidden');
            try {
                const resp = await fetch(`/orders/requests/get-details/${this.value}`);
                const requestData = await resp.json();
                
                if (requestData.details && requestData.details.length > 0) {
                    for (const detail of requestData.details) {
                        await addItemFromRequest(detail, false); // Don't validate inside loop
                    }
                    calculateGrandTotal(); // Added this
                    validateItemsBySupplier(); // Validate once after all items added
                } else {
                    Swal.fire('Info', 'Semua item dalam request ini sudah diproses ke PO.', 'info');
                    addItem();
                }
            } catch (e) { console.error(e); }
        } else {
            requestNoteSection.classList.add('hidden');
            cancelBtn.classList.add('hidden');
            addItem(); // Add one empty row
        }
    });

    function confirmCancelRequest() {
        document.getElementById('confirmCancelModal').classList.remove('hidden');
    }

    function closeConfirmModal() {
        document.getElementById('confirmCancelModal').classList.add('hidden');
    }

    function executeCancelRequest() {
        const requestId = requestSelect.value;
        if (!requestId) return;
        
        const form = document.getElementById('cancelRequestForm');
        form.action = `/orders/requests/cancel/${requestId}`;
        form.submit();
    }

    async function addItemFromRequest(detail, triggerValidation = true) {
        const list = document.getElementById('itemList');
        const row = document.createElement('tr');
        row.className = 'item-row border-b border-white/5 bg-indigo-500/5';
        
        row.innerHTML = `
            <td class="px-6 py-4">
                <input type="hidden" name="items[${itemIndex}][id]" value="${detail.item_id}">
                <div class="text-white text-sm font-bold">${detail.item.name} (${detail.item.code})</div>
                <div class="text-[10px] text-indigo-400 font-bold uppercase tracking-widest mt-1">From Request</div>
            </td>
            <td class="px-6 py-4">
                <input type="number" name="items[${itemIndex}][quantity]" class="qty-input w-full bg-slate-900/50 border border-white/5 rounded-lg py-2 px-3 text-center text-slate-400 font-bold text-[12px]" value="${detail.quantity}" readonly>
            </td>
            <td class="px-6 py-4">
                <input type="hidden" name="items[${itemIndex}][price]" class="actual-price-input" value="0">
                <input type="text" class="price-input w-full bg-slate-800/50 border border-white/10 rounded-lg py-2 px-3 text-center text-white font-bold" value="0" oninput="formatAndCalculate(this)">
            </td>
            <td class="px-6 py-4 text-center">
                <span class="subtotal-display text-emerald-400 font-bold">Rp 0</span>
            </td>
            <td class="px-6 py-4 text-right">
                <button type="button" onclick="removeItem(this)" class="text-rose-500 hover:bg-rose-500/10 p-2 rounded-lg transition-all"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
            </td>
        `;
        
        list.appendChild(row);
        
        // Fetch price immediately
        const priceInput = row.querySelector('.price-input');
        const itemId = detail.item_id;
        try {
            const resp = await fetch(`/master/price-lists/get-price?item_id=${itemId}`);
            const data = await resp.json();
            priceInput.value = formatRupiah(data.hna_ppn || 0);
            calculateSubtotal(priceInput);
        } catch (e) { console.error(e); }
        
        itemIndex++;
        lucide.createIcons();
        if (triggerValidation) validateItemsBySupplier();
    }

    function viewDetail(po) {
        document.getElementById('det_po_no').innerText = po.po_no;
        document.getElementById('det_supplier').innerText = po.supplier.name + ' | ' + po.order_date;
        document.getElementById('det_total').innerText = 'Rp ' + formatRupiah(po.total_amount || 0);
        
        const statusEl = document.getElementById('det_status');
        statusEl.innerText = po.status;
        statusEl.className = 'px-4 py-1.5 rounded-xl font-black text-[10px] uppercase ' + 
            (po.status == 'OPEN' ? 'bg-indigo-500/10 text-indigo-500' : (po.status == 'CLOSED' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-amber-500/10 text-amber-500'));

        const rows = document.getElementById('det_rows');
        rows.innerHTML = '';
        po.details.forEach(detail => {
            const subtotal = (detail.quantity * detail.price) || 0;
            rows.innerHTML += `
                <tr class="text-sm text-white">
                    <td class="px-4 py-4 font-bold">${detail.item.name}</td>
                    <td class="px-4 py-4 text-center font-black">${detail.quantity}</td>
                    <td class="px-4 py-4 text-center text-slate-500">${detail.received_quantity || 0}</td>
                    <td class="px-4 py-4 text-right font-mono">Rp ${formatRupiah(detail.price || 0)}</td>
                    <td class="px-4 py-4 text-right font-mono text-emerald-400">Rp ${formatRupiah(subtotal)}</td>
                </tr>
            `;
        });
        document.getElementById('detailModal').classList.remove('hidden');
    }

    function closeDetailModal() { document.getElementById('detailModal').classList.add('hidden'); }
    function openModal() { document.getElementById('modal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }

    document.getElementById('poForm').addEventListener('submit', function(e) {
        // Hidden inputs already handled by calculateSubtotal
    });
</script>
@endsection
