@extends('layouts.app', ['title' => 'Create Work Order'])

@section('content')
<style>
    /* Hide number input arrows */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
</style>
<div class="w-full">
    <form action="{{ route('production.work_orders.store') }}" method="POST" id="woForm" class="space-y-8">
        @csrf
        
        <!-- Header Actions -->
        <div class="flex items-center justify-between">
            <a href="{{ route('production.work_orders.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-white transition-colors text-xs font-black uppercase tracking-widest">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to List
            </a>
            <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black uppercase tracking-widest rounded-2xl transition-all shadow-lg shadow-indigo-500/20 flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i> Save Work Order
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Primary Info -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Main Info Card -->
                <div class="glass-card p-8 rounded-[2.5rem] border border-white/5 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-600/5 blur-[100px] rounded-full -mr-32 -mt-32"></div>
                    
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-1.5 h-6 bg-indigo-500 rounded-full"></div>
                        <h3 class="text-white font-black text-sm uppercase tracking-widest">Informasi Produksi</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Nomor WO</label>
                            <input type="text" name="wo_number" value="{{ $wo_number }}" readonly class="w-full bg-slate-900/50 border-white/5 rounded-2xl px-5 py-4 text-sm text-indigo-400 font-bold focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Line Produksi</label>
                            <select name="production_line" required class="w-full bg-slate-900/50 border-white/5 rounded-2xl px-5 py-4 text-sm text-white focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all appearance-none cursor-pointer">
                                <option value="1">Line 1</option>
                                <option value="2">Line 2</option>
                                <option value="3">Line 3</option>
                                <option value="4">Line 4</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Tanggal Produksi</label>
                            <input type="date" name="production_date" id="production_date" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-900/50 border-white/5 rounded-2xl px-5 py-4 text-sm text-white focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Marketing</label>
                            <input type="text" name="marketing" placeholder="Nama Marketing..." class="w-full bg-slate-900/50 border-white/5 rounded-2xl px-5 py-4 text-sm text-white focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                        </div>
                        <div class="space-y-2 relative z-20">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Estimasi Durasi (Hari)</label>
                            <input type="number" step="1" name="duration" id="duration" value="1" placeholder="1" class="w-full bg-slate-900 border-white/10 rounded-2xl px-5 py-4 text-sm text-white focus:ring-2 focus:ring-indigo-500/50 outline-none transition-all shadow-inner">
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Estimasi Selesai Produksi</label>
                            <input type="text" id="estimated_finish" readonly class="w-full bg-slate-900/50 border-white/5 rounded-2xl px-5 py-4 text-sm text-emerald-400 font-bold outline-none transition-all" placeholder="Pilih tanggal & durasi...">
                        </div>
                    </div>
                </div>

                <!-- Products Selection Card -->
                <div class="glass-card p-8 rounded-[2.5rem] border border-white/5 relative">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-6 bg-emerald-500 rounded-full"></div>
                            <h3 class="text-white font-black text-sm uppercase tracking-widest">Produk Yang Dihasilkan</h3>
                        </div>
                        <button type="button" onclick="addProductRow()" class="p-2 bg-emerald-500/10 hover:bg-emerald-500 text-emerald-500 hover:text-white rounded-xl transition-all">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <div id="productRows" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end bg-white/[0.02] p-4 rounded-2xl border border-white/5 group product-row">
                            <div class="md:col-span-7 space-y-2">
                                <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest ml-1">Pilih Produk (Finished Good)</label>
                                <select name="products[0][item_id]" required class="w-full bg-slate-900 border-white/5 rounded-xl px-4 py-3 text-xs text-white outline-none">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-unit="{{ $product->unit->name ?? '' }}">{{ $product->code }} - {{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-4 space-y-2">
                                <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest ml-1">Target Qty</label>
                                <div class="flex items-center gap-2 bg-slate-900 border border-white/5 rounded-2xl px-4 py-3">
                                    <input type="number" name="products[0][quantity]" required min="1" class="w-full bg-transparent border-none text-sm text-white outline-none" placeholder="0">
                                    <input type="text" class="w-16 bg-transparent border-none text-[10px] text-indigo-400 font-black uppercase outline-none unit-display text-right" readonly placeholder="Unit">
                                </div>
                            </div>
                            <div class="md:col-span-1 flex justify-center pb-1">
                                <button type="button" class="text-slate-600 hover:text-rose-500 transition-colors opacity-0 group-hover:opacity-100 remove-row">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Template & Composition Card -->
                <div class="glass-card p-8 rounded-[2.5rem] border border-white/5">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-3">
                                <div class="w-1.5 h-6 bg-amber-500 rounded-full"></div>
                                <h3 class="text-white font-black text-sm uppercase tracking-widest">Standar Produksi & Komposisi</h3>
                            </div>
                            <button type="button" onclick="addStageRow()" class="px-6 py-2 bg-amber-500/10 hover:bg-amber-500 text-amber-500 hover:text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all flex items-center gap-2">
                                <i data-lucide="plus" class="w-3 h-3"></i> Tambah Tahapan
                            </button>
                        </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Gunakan Template Produksi</label>
                            <select id="template_id" class="w-full bg-slate-900/50 border-white/5 rounded-2xl px-5 py-4 text-sm text-amber-400 font-bold focus:ring-2 focus:ring-amber-500/20 outline-none transition-all appearance-none cursor-pointer">
                                <option value="">-- Pilih Template --</option>
                                @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->code }} ({{ $tpl->product->name ?? 'Mixed' }})</option>
                                @endforeach
                            </select>
                            <p class="text-[9px] text-slate-500 italic mt-1">* Memilih template akan otomatis mengisi tahapan dan bahan di bawah.</p>
                        </div>
                    </div>

                    <!-- Dynamic Stages Section -->
                    <div id="stagesContainer" class="space-y-6">
                        <!-- Stages will be injected here -->
                    </div>
                </div>
            </div>

            <!-- Right Column: Customer & Summary -->
            <div class="space-y-8">
                <!-- Customer Card -->
                <div class="glass-card p-8 rounded-[2.5rem] border border-white/5 relative overflow-hidden sticky top-8">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-1.5 h-6 bg-blue-500 rounded-full"></div>
                        <h3 class="text-white font-black text-sm uppercase tracking-widest">Detail Customer</h3>
                    </div>

                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Pilih Customer</label>
                            <select name="customer_id" id="customer_id" required class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white focus:ring-2 focus:ring-blue-500/20 outline-none transition-all appearance-none cursor-pointer">
                                <option value="">-- Pilih Customer --</option>
                                @foreach($customers as $cust)
                                <option value="{{ $cust->id }}" data-code="{{ $cust->code }}">{{ $cust->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Kode Customer</label>
                            <input type="text" id="customer_code" readonly class="w-full bg-slate-900/50 border-white/5 rounded-2xl px-5 py-4 text-sm text-blue-400 font-bold outline-none" placeholder="AUTO">
                        </div>
                        <div class="pt-6 border-t border-white/5 space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Kode Tahapan</span>
                                <input type="text" name="stage_code" placeholder="Misal: STEP-01" class="bg-transparent border-b border-white/10 text-right text-xs text-white outline-none focus:border-indigo-500 w-32">
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Kode Komposisi</span>
                                <input type="text" name="composition_code" placeholder="Misal: COMP-A" class="bg-transparent border-b border-white/10 text-right text-xs text-white outline-none focus:border-indigo-500 w-32">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Catatan Produksi</label>
                            <textarea name="notes" rows="4" class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all resize-none" placeholder="Tambahkan instruksi khusus..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Templates for JS Cloning -->
<template id="stageTemplate">
    <div class="stage-block bg-slate-900/30 rounded-[2rem] border border-white/5 p-6 space-y-6 relative group/stage" data-index="__INDEX__">
        <div class="absolute -left-3 top-6 w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center text-[10px] font-black text-white shadow-lg stage-index">1</div>
        <button type="button" class="absolute -right-2 -top-2 w-8 h-8 bg-rose-500/10 hover:bg-rose-500 text-rose-500 hover:text-white rounded-full transition-all opacity-0 group-hover/stage:opacity-100 flex items-center justify-center remove-stage">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="space-y-2">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Nama Tahapan</label>
                <input type="text" name="stages[__INDEX__][name]" required class="w-full bg-slate-900 border-white/5 rounded-xl px-4 py-3 text-xs text-white outline-none" placeholder="Contoh: Pencampuran Awal">
            </div>
            <div class="space-y-2">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Mesin / Area</label>
                <select name="stages[__INDEX__][machine_id]" class="w-full bg-slate-900 border-white/5 rounded-xl px-4 py-3 text-xs text-white outline-none">
                    <option value="">-- Pilih Mesin --</option>
                    @foreach($machines as $machine)
                    <option value="{{ $machine->id }}" data-capacity="{{ $machine->capacity }}" data-unit="{{ $machine->capacity_unit }}">{{ $machine->name }} ({{ $machine->capacity }} {{ $machine->capacity_unit }})</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Total Batch Tahapan</label>
                <div class="flex items-center gap-2 bg-slate-900 border border-white/5 rounded-xl px-4 py-3">
                    <input type="number" step="0.01" name="stages[__INDEX__][total_batch]" required class="w-full bg-transparent border-none text-xs text-white outline-none stage-batch-input" placeholder="1.00" value="1.00" min="0.01">
                    <span class="text-[8px] text-slate-500 font-black uppercase">BTH</span>
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Waktu Pengerjaan (Jam)</label>
                <div class="flex items-center gap-2 bg-slate-900 border border-white/5 rounded-xl px-4 py-3">
                    <input type="number" step="0.01" name="stages[__INDEX__][duration_hours]" required class="w-full bg-transparent border-none text-xs text-white outline-none duration-hours-input" placeholder="0.00">
                    <span class="text-[8px] text-slate-500 font-black uppercase">HRS</span>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Estimasi Mulai</label>
                <input type="datetime-local" name="stages[__INDEX__][planned_start]" required class="w-full bg-slate-900 border-white/5 rounded-xl px-4 py-3 text-xs text-white outline-none planned-start-input">
            </div>
            <div class="space-y-2">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Estimasi Selesai</label>
                <input type="datetime-local" readonly class="w-full bg-slate-900/50 border-white/5 rounded-xl px-4 py-3 text-xs text-emerald-400 font-bold outline-none planned-end-display">
            </div>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Alokasi Bahan & Output</span>
                <button type="button" onclick="addItemRow(this.closest('.stage-block'))" class="text-[9px] font-black text-indigo-400 hover:text-indigo-300 uppercase tracking-widest flex items-center gap-1">
                    <i data-lucide="plus" class="w-3 h-3"></i> Tambah Item
                </button>
            </div>
            <div class="item-rows space-y-2">
                <!-- Items will be here -->
            </div>
        </div>
    </div>
</template>

<template id="itemTemplate">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center bg-white/[0.01] p-3 rounded-xl border border-white/5 group/item">
        <div class="md:col-span-2">
            <select name="stages[__STAGE_INDEX__][items][__ITEM_INDEX__][type]" required class="w-full bg-slate-800 border-transparent rounded-lg px-2 py-2 text-[9px] text-indigo-400 font-bold uppercase outline-none stage-item-type">
                <option value="">-- TIPE --</option>
                <option value="input">INPUT</option>
                <option value="output">OUTPUT</option>
            </select>
        </div>
        <div class="md:col-span-6">
            <select name="stages[__STAGE_INDEX__][items][__ITEM_INDEX__][item_id]" required disabled class="w-full bg-slate-800 border-transparent rounded-lg px-3 py-2 text-[10px] text-slate-400 outline-none stage-item-select">
                <option value="">-- Pilih Tipe Terlebih Dahulu --</option>
                @foreach($items as $item)
                <option value="{{ $item->id }}" data-unit="{{ $item->unit->name ?? '' }}" class="text-white bg-slate-800">{{ $item->code }} - {{ $item->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-3">
            <div class="flex items-center gap-2 bg-slate-800 rounded-lg px-3">
                <input type="number" step="0.01" name="stages[__STAGE_INDEX__][items][__ITEM_INDEX__][quantity]" required class="w-full bg-transparent border-none py-2 text-[10px] text-white outline-none" placeholder="0.00">
                <input type="text" class="w-12 bg-transparent border-none text-[9px] text-indigo-400 font-black uppercase outline-none unit-display" readonly placeholder="Unit">
            </div>
        </div>
        <div class="md:col-span-1 flex justify-center">
            <button type="button" class="text-slate-700 hover:text-rose-500 transition-colors remove-item">
                <i data-lucide="trash" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</template>

<?php
    // 1. Map Machine Capabilities
    $capData = $machines->keyBy('id')->map(function($m) {
        return $m->capabilities->map(function($c) {
            return [
                'item_id' => $c->id,
                'production_rate' => floatval($c->pivot->production_rate),
                'capacity_unit' => $c->pivot->rate_unit ?? $c->pivot->capacity_unit ?? 'kg/jam',
                'thickness' => $c->pivot->thickness,
                'diameter' => $c->pivot->diameter,
                'cavity' => $c->pivot->cavity,
                'cycle' => floatval($c->pivot->cycle),
            ];
        });
    });

    // 2. Map Machine Substitutes
    $machineSubData = $machines->keyBy('id')->map(function($m) {
        return $m->substitutes->pluck('id')->toArray();
    });

    // 3. Map Item Substitutions
    $itemSubData = $items->keyBy('id')->map(function($item) {
        return $item->substitutes->pluck('id')->toArray();
    });
?>
<script>
    // Global state
    let productCount = 1;
    let stageCount = 0;

    // Machine Capabilities Matrix (loaded from database)
    const machineCapabilities = {!! json_encode($capData) !!};

    // Machine Substitutes Matrix
    const machineSubstitutes = {!! json_encode($machineSubData) !!};

    // Item Substitutions Matrix
    const itemSubstitutions = {!! json_encode($itemSubData) !!};

    // --- New Features & Validations ---

    function updateProductOptions() {
        const selectedIds = Array.from(document.querySelectorAll('.product-row select'))
            .map(select => select.value)
            .filter(id => id !== "");

        document.querySelectorAll('.product-row select').forEach(select => {
            const currentValue = select.value;
            const options = select.querySelectorAll('option');
            
            options.forEach(option => {
                if (option.value === "") return;
                
                if (selectedIds.includes(option.value) && option.value !== currentValue) {
                    option.disabled = true;
                    option.style.display = 'none';
                } else {
                    option.disabled = false;
                    option.style.display = '';
                }
            });
        });
    }

    document.getElementById('woForm').addEventListener('submit', function(e) {
        const stages = document.querySelectorAll('.stage-block');
        let isValid = true;
        let errorMessage = "";

        if (stages.length === 0) {
            isValid = false;
            errorMessage = "Minimal harus ada 1 tahapan produksi.";
        }

        stages.forEach((stage, index) => {
            const types = Array.from(stage.querySelectorAll('.stage-item-type')).map(s => s.value);
            const hasInput = types.includes('input');
            const hasOutput = types.includes('output');

            if (!hasInput || !hasOutput) {
                isValid = false;
                const stageName = stage.querySelector('input[name*="[name]"]').value || `Tahapan ${index + 1}`;
                errorMessage = `Tahapan "${stageName}" harus memiliki minimal 1 INPUT dan 1 OUTPUT.`;
            }
        });

        if (!isValid) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: errorMessage,
                    background: '#1e293b',
                    color: '#fff',
                    confirmButtonColor: '#4f46e5'
                });
            } else {
                alert(errorMessage);
            }
        }
    });

    // --- Core Functions ---

    function addProductRow(data = null) {
        const container = document.getElementById('productRows');
        if (!container) return;
        const index = productCount++;
        
        const html = `
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end bg-white/[0.02] p-4 rounded-2xl border border-white/5 group product-row" data-index="${index}">
                <div class="md:col-span-7 space-y-2">
                    <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest ml-1">Pilih Produk (Finished Good)</label>
                    <select name="products[${index}][item_id]" required class="w-full bg-slate-900 border-white/5 rounded-xl px-4 py-3 text-xs text-white outline-none product-select">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" data-unit="{{ $product->unit->name ?? '' }}" class="text-white bg-slate-800">{{ $product->code }} - {{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-4 space-y-2">
                    <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest ml-1">Target Qty</label>
                    <div class="flex items-center gap-2 bg-slate-900 border border-white/5 rounded-2xl px-4 py-3">
                        <input type="number" name="products[${index}][quantity]" required min="1" class="w-full bg-transparent border-none text-sm text-white outline-none" placeholder="0">
                        <input type="text" class="w-16 bg-transparent border-none text-[10px] text-indigo-400 font-black uppercase outline-none unit-display text-right" readonly placeholder="Unit">
                    </div>
                </div>
                <div class="md:col-span-1 flex justify-center pb-1">
                    <button type="button" class="text-slate-600 hover:text-rose-500 transition-colors opacity-0 group-hover:opacity-100 remove-row">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', html);
        const row = container.lastElementChild;
        
        if (data) {
            const select = row.querySelector('select');
            const qtyInput = row.querySelector('input[type="number"]');
            if (select) {
                select.value = data.item_id;
                const selected = select.options[select.selectedIndex];
                if (selected) row.querySelector('.unit-display').value = selected.dataset.unit || '';
            }
            if (qtyInput) qtyInput.value = data.quantity || 1;
        }

        updateProductOptions();
        if (typeof lucide !== 'undefined') lucide.createIcons();
        updateAllDurations();
    }

    function addStageRow(data = null) {
        try {
            const container = document.getElementById('stagesContainer');
            const template = document.getElementById('stageTemplate');
            if (!template || !container) return;

            const index = stageCount++;
            const clone = template.content.cloneNode(true);
            const stageBlock = clone.firstElementChild;
            
            stageBlock.setAttribute('data-index', index);
            const idxDisp = stageBlock.querySelector('.stage-index');
            if (idxDisp) idxDisp.textContent = index + 1;

            stageBlock.querySelectorAll('[name*="__INDEX__"]').forEach(el => {
                el.name = el.name.replace(/__INDEX__/g, index);
            });

            container.appendChild(stageBlock);

            if (data) {
                const nameInp = stageBlock.querySelector('input[name*="[name]"]');
                const machineSel = stageBlock.querySelector('select[name*="[machine_id]"]');
                const durInp = stageBlock.querySelector('.duration-hours-input');
                const batchInp = stageBlock.querySelector('.stage-batch-input');

                if (nameInp) nameInp.value = data.name || '';
                if (machineSel) machineSel.value = data.machine_id || '';
                if (batchInp && data.total_batch) batchInp.value = data.total_batch;
                if (durInp && data.duration_hours) durInp.value = data.duration_hours;

                if (data.items) {
                    const items = Array.isArray(data.items) ? data.items : Object.values(data.items);
                    items.forEach(item => addItemRow(stageBlock, item));
                }
            } else {
                addItemRow(stageBlock);
            }

            updateAllDurations();
            if (typeof lucide !== 'undefined') lucide.createIcons();
            return stageBlock;
        } catch (err) {
            console.error('Error in addStageRow:', err);
        }
    }

    function addItemRow(stageBlock, data = null) {
        try {
            const itemContainer = stageBlock.querySelector('.item-rows');
            const template = document.getElementById('itemTemplate');
            if (!template || !itemContainer) return;

            const stageIndex = stageBlock.getAttribute('data-index');
            const itemIndex = itemContainer.querySelectorAll('.group\\/item').length;
            const clone = template.content.cloneNode(true);
            const itemRow = clone.firstElementChild;
            
            itemRow.querySelectorAll('[name*="__STAGE_INDEX__"]').forEach(el => {
                el.name = el.name.replace(/__STAGE_INDEX__/g, stageIndex).replace(/__ITEM_INDEX__/g, itemIndex);
            });

            itemContainer.appendChild(itemRow);

            if (data) {
                const select = itemRow.querySelector('select[name*="[item_id]"]');
                const qtyInp = itemRow.querySelector('input[name*="[quantity]"]');
                const typeSel = itemRow.querySelector('select[name*="[type]"]');
                
                if (typeSel) typeSel.value = data.type || 'input';
                
                // Trigger filtering immediately so options are correctly populated before setting the item select value
                filterStageItems(stageBlock);
                
                if (select) {
                    select.value = data.item_id;
                    const selected = select.options[select.selectedIndex];
                    if (selected) {
                        const unitDisp = itemRow.querySelector('.unit-display');
                        if (unitDisp) unitDisp.value = selected.dataset.unit || '';
                    }
                }
                if (qtyInp) qtyInp.value = data.quantity || data.quantity_per_batch || 0;
            } else {
                // For a new empty row, run filtering to disable it initially
                filterStageItems(stageBlock);
            }

            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (err) {
            console.error('Error in addItemRow:', err);
        }
    }

    // --- Helper Functions ---

    function toLocalISO(date) {
        if (!date || isNaN(date.getTime())) return '';
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    function calculateFinishDate() {
        const prodDateInput = document.getElementById('production_date');
        const durationInput = document.getElementById('duration');
        const finishInput = document.getElementById('estimated_finish');
        
        if (prodDateInput && durationInput && prodDateInput.value && durationInput.value) {
            const startDate = new Date(prodDateInput.value);
            const durationDays = parseFloat(durationInput.value) || 0;
            const finishDate = new Date(startDate.getTime() + (durationDays * 24 * 3600000));
            
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            if (finishInput) finishInput.value = finishDate.toLocaleDateString('id-ID', options);
        } else if (finishInput) {
            finishInput.value = '';
        }
    }

    function updateAllDurations(sourceElement = null) {
        const prodDateInput = document.getElementById('production_date');
        
        if (!prodDateInput || !prodDateInput.value) return;

        let totalQty = 0;
        document.querySelectorAll('.product-row').forEach(row => {
            const qtyInput = row.querySelector('input[name*="[quantity]"]');
            if (qtyInput) totalQty += parseFloat(qtyInput.value) || 0;
        });

        const grandTotalQty = totalQty;

        let totalHours = 0;
        const stages = document.querySelectorAll('.stage-block');
        
        const dateParts = prodDateInput.value.split('-');
        let nextStartTime = new Date(dateParts[0], dateParts[1] - 1, dateParts[2], 8, 0);

        stages.forEach((stage, idx) => {
            const machineSelect = stage.querySelector('select[name*="[machine_id]"]');
            const durationInput = stage.querySelector('.duration-hours-input');
            const batchInput = stage.querySelector('.stage-batch-input');
            const totalBatch = parseFloat(batchInput ? batchInput.value : 1) || 1;
            const startInput = stage.querySelector('.planned-start-input');
            const endDisplay = stage.querySelector('.planned-end-display');
            
            // 1. Calculate Duration (only if triggered by machine/qty/item change OR if empty)
            let hours = parseFloat(durationInput ? durationInput.value : 0) || 0;
            if (sourceElement && (
                sourceElement.matches('select[name*="[machine_id]"]') || 
                sourceElement.matches('input[name*="[quantity]"]') || 
                sourceElement.matches('select[name*="[item_id]"]') ||
                sourceElement.matches('select[name*="[type]"]') ||
                sourceElement.matches('.stage-batch-input')
            )) {
                if (machineSelect && machineSelect.value) {
                    const machineId = machineSelect.value;
                    const selectedMachineOpt = machineSelect.options[machineSelect.selectedIndex];
                    
                    // Default values from machine option dataset
                    let capacity = parseFloat(selectedMachineOpt.dataset.capacity) || 0;
                    let unit = (selectedMachineOpt.dataset.unit || '').toLowerCase();
                    
                    // Look for selected item inside the stage items
                    let selectedItemId = null;
                    let selectedItemQty = 0;
                    let foundCapability = null;
                    
                    const itemRows = stage.querySelectorAll('.item-rows > div');
                    let itemsInStage = [];
                    
                    itemRows.forEach(row => {
                        const typeSelect = row.querySelector('.stage-item-type');
                        const itemSelect = row.querySelector('select[name*="[item_id]"]');
                        const qtyInput = row.querySelector('input[name*="[quantity]"]');
                        
                        if (itemSelect && itemSelect.value) {
                            itemsInStage.push({
                                item_id: parseInt(itemSelect.value),
                                qty: parseFloat(qtyInput ? qtyInput.value : 0) || 0,
                                type: typeSelect ? typeSelect.value : 'input'
                            });
                        }
                    });
                    
                    // Prioritize finding an item in this stage that actually has a registered machine capability
                    if (machineCapabilities[machineId] && itemsInStage.length > 0) {
                        const capabilities = Object.values(machineCapabilities[machineId]);
                        
                        // 1. Try matching OUTPUT items with capabilities
                        const outputItems = itemsInStage.filter(i => i.type === 'output');
                        for (let item of outputItems) {
                            let cap = capabilities.find(c => c.item_id === item.item_id);
                            if (cap && cap.production_rate > 0) {
                                foundCapability = cap;
                                selectedItemId = item.item_id;
                                selectedItemQty = item.qty;
                                break;
                            }
                        }
                        
                        // 2. Try matching INPUT items with capabilities
                        if (!foundCapability) {
                            const inputItems = itemsInStage.filter(i => i.type === 'input');
                            for (let item of inputItems) {
                                let cap = capabilities.find(c => c.item_id === item.item_id);
                                if (cap && cap.production_rate > 0) {
                                    foundCapability = cap;
                                    selectedItemId = item.item_id;
                                    selectedItemQty = item.qty;
                                    break;
                                }
                            }
                        }
                    }
                    
                    // 3. Fallback: If no capability match is found in the matrix, pick the first output (or first input) to at least use its quantity
                    if (!selectedItemId && itemsInStage.length > 0) {
                        const fallbackItem = itemsInStage.find(i => i.type === 'output') || itemsInStage[0];
                        selectedItemId = fallbackItem.item_id;
                        selectedItemQty = fallbackItem.qty;
                    }
                    
                    // 4. Fallback 2: If the stage is empty, fall back to the first product (Finished Good) of the WO
                    if (!selectedItemId) {
                        const firstProductRow = document.querySelector('.product-row');
                        if (firstProductRow) {
                            const prodSelect = firstProductRow.querySelector('select[name*="[item_id]"]');
                            const prodQty = firstProductRow.querySelector('input[name*="[quantity]"]');
                            if (prodSelect && prodSelect.value) {
                                selectedItemId = parseInt(prodSelect.value);
                                selectedItemQty = parseFloat(prodQty ? prodQty.value : 0) || 0;
                                
                                // Check if this main product has a capability on the machine
                                if (machineCapabilities[machineId]) {
                                    const capabilities = Object.values(machineCapabilities[machineId]);
                                    foundCapability = capabilities.find(c => c.item_id === selectedItemId);
                                }
                            }
                        }
                    }
                    
                    // If capability is found, override default machine capacity
                    if (foundCapability && foundCapability.production_rate > 0) {
                        capacity = foundCapability.production_rate;
                        unit = (foundCapability.capacity_unit || '').toLowerCase();
                        console.log(`Using specific capability for machine ${machineId} and item ${selectedItemId}: ${capacity} ${unit}`);
                    }
                    
                    // Determine the target quantity to process in this stage
                    const qtyToUse = (selectedItemQty > 0) ? (selectedItemQty * totalBatch) : grandTotalQty;
                    
                    if (capacity > 0) {
                        if (unit.includes('menit') || unit.includes('min')) {
                            hours = qtyToUse / (capacity * 60);
                        } else {
                            hours = qtyToUse / capacity;
                        }
                        if (durationInput) durationInput.value = hours.toFixed(2);
                    }
                }
            }
            totalHours += hours;

            // 2. Determine Start Time
            let startTime;
            if (idx === 0 && (!startInput.value || sourceElement?.id === 'production_date')) {
                startTime = nextStartTime;
                if (startInput) startInput.value = toLocalISO(startTime);
            } else if (startInput.value) {
                startTime = new Date(startInput.value);
                if (sourceElement && startTime < nextStartTime) {
                    startTime = nextStartTime;
                    startInput.value = toLocalISO(startTime);
                }
            } else {
                startTime = nextStartTime;
                if (startInput) startInput.value = toLocalISO(startTime);
            }

            const endTime = new Date(startTime.getTime() + (hours * 3600000));
            if (endDisplay) endDisplay.value = toLocalISO(endTime);

            nextStartTime = endTime;
        });
    }

    function filterStageItems(stageBlock) {
        if (!stageBlock) return;
        
        const machineSelect = stageBlock.querySelector('select[name*="[machine_id]"]');
        const machineId = machineSelect ? machineSelect.value : '';
        const itemRows = stageBlock.querySelectorAll('.item-rows > .group\\/item');
        
        itemRows.forEach(row => {
            const typeSelect = row.querySelector('.stage-item-type');
            const itemSelect = row.querySelector('select[name*="[item_id]"]');
            
            if (!itemSelect) return;
            
            // Backup the original options if not already done
            if (!itemSelect.originalOptions) {
                itemSelect.originalOptions = Array.from(itemSelect.options);
            }
            
            const currentValue = itemSelect.value;
            const originalOptions = itemSelect.originalOptions;
            
            // Check if Type is selected
            const typeVal = typeSelect ? typeSelect.value : '';
            if (!typeVal) {
                // If type is not chosen, disable the dropdown and show the warning placeholder
                itemSelect.disabled = true;
                itemSelect.classList.remove('text-white');
                itemSelect.classList.add('text-slate-400');
                itemSelect.innerHTML = '<option value="">-- Pilih Tipe Terlebih Dahulu --</option>';
                const unitDisp = row.querySelector('.unit-display');
                if (unitDisp) unitDisp.value = '';
                return;
            }
            
            // Type is selected -> enable it!
            itemSelect.disabled = false;
            itemSelect.classList.remove('text-slate-400');
            itemSelect.classList.add('text-white');
            
            // Determine matching items if type is OUTPUT
            let allowedItemIds = null;
            if (typeVal === 'output' && machineId) {
                allowedItemIds = new Set();
                
                // 1. Add capable items from machineCapabilities[machineId]
                if (machineCapabilities[machineId]) {
                    Object.values(machineCapabilities[machineId]).forEach(cap => {
                        allowedItemIds.add(cap.item_id);
                        
                        // Add substitute items for this capable item
                        if (itemSubstitutions[cap.item_id]) {
                            itemSubstitutions[cap.item_id].forEach(subId => allowedItemIds.add(subId));
                        }
                    });
                }
                
                // 2. Add capable items from machineSubstitutes[machineId] (substitute machines)
                if (machineSubstitutes[machineId]) {
                    machineSubstitutes[machineId].forEach(subMachineId => {
                        if (machineCapabilities[subMachineId]) {
                            Object.values(machineCapabilities[subMachineId]).forEach(cap => {
                                allowedItemIds.add(cap.item_id);
                                
                                // Add substitute items for this capable item
                                if (itemSubstitutions[cap.item_id]) {
                                    itemSubstitutions[cap.item_id].forEach(subId => allowedItemIds.add(subId));
                                }
                            });
                        }
                    });
                }
            }
            
            // Rebuild the dropdown options
            itemSelect.innerHTML = '';
            originalOptions.forEach(opt => {
                const optVal = parseInt(opt.value);
                
                // Always keep the empty default option
                if (!opt.value) {
                    const clonedOpt = opt.cloneNode(true);
                    clonedOpt.textContent = '-- Pilih Item --';
                    itemSelect.appendChild(clonedOpt);
                    return;
                }
                
                // Filter logic
                if (allowedItemIds === null) {
                    // If type is INPUT or no machine selected, allow all items
                    itemSelect.appendChild(opt.cloneNode(true));
                } else if (allowedItemIds.has(optVal)) {
                    // If type is OUTPUT and item is allowed
                    itemSelect.appendChild(opt.cloneNode(true));
                }
            });
            
            // Restore selection if it's still allowed, otherwise reset
            if (currentValue && itemSelect.querySelector(`option[value="${currentValue}"]`)) {
                itemSelect.value = currentValue;
            } else {
                itemSelect.value = '';
                const unitDisp = row.querySelector('.unit-display');
                if (unitDisp) unitDisp.value = '';
            }
        });
    }

    function getStandardBatchQty(stageBlock) {
        if (!stageBlock) return 0;
        const itemRows = stageBlock.querySelectorAll('.item-rows > .group\\/item');
        for (let row of itemRows) {
            const typeSelect = row.querySelector('.stage-item-type');
            const qtyInput = row.querySelector('input[name*="[quantity]"]');
            if (typeSelect && typeSelect.value === 'output' && qtyInput) {
                const val = parseFloat(qtyInput.value) || 0;
                if (val > 0) return val;
            }
        }
        return 0;
    }

    let lastEditedSource = 'target_qty';

    function syncTargetQtyAndBatch(sourceInput, specificStageBlock = null) {
        const firstProductRow = document.querySelector('.product-row');
        if (!firstProductRow) return;
        
        const productQtyInput = firstProductRow.querySelector('input[name*="[quantity]"]');
        if (!productQtyInput) return;
        
        if (sourceInput === productQtyInput) {
            lastEditedSource = 'target_qty';
            // Update batch in all stages
            const totalQty = parseFloat(productQtyInput.value) || 0;
            document.querySelectorAll('.stage-block').forEach(stage => {
                const standardBatchQty = getStandardBatchQty(stage);
                if (standardBatchQty > 0) {
                    const batchInput = stage.querySelector('.stage-batch-input');
                    if (batchInput) {
                        const calculatedBatch = Math.max(0.01, Math.round((totalQty / standardBatchQty) * 100) / 100);
                        batchInput.value = calculatedBatch;
                    }
                }
            });
        } else if (sourceInput && sourceInput.classList.contains('stage-batch-input')) {
            lastEditedSource = 'total_batch';
            const stage = sourceInput.closest('.stage-block');
            const standardBatchQty = getStandardBatchQty(stage);
            if (standardBatchQty > 0) {
                const totalBatch = parseFloat(sourceInput.value) || 1;
                const calculatedQty = Math.round(standardBatchQty * totalBatch * 100) / 100;
                productQtyInput.value = calculatedQty;
                
                // Keep other stages in sync too!
                document.querySelectorAll('.stage-block').forEach(otherStage => {
                    if (otherStage !== stage) {
                        const otherStandard = getStandardBatchQty(otherStage);
                        if (otherStandard > 0) {
                            const otherBatchInput = otherStage.querySelector('.stage-batch-input');
                            if (otherBatchInput) {
                                const calculatedBatch = Math.max(0.01, Math.round((calculatedQty / otherStandard) * 100) / 100);
                                otherBatchInput.value = calculatedBatch;
                            }
                        }
                    }
                });
            }
        } else if (specificStageBlock) {
            // Triggered when a stage item quantity changes
            const standardBatchQty = getStandardBatchQty(specificStageBlock);
            if (standardBatchQty > 0) {
                const batchInput = specificStageBlock.querySelector('.stage-batch-input');
                if (lastEditedSource === 'target_qty') {
                    const totalQty = parseFloat(productQtyInput.value) || 0;
                    if (batchInput) {
                        const calculatedBatch = Math.max(0.01, Math.round((totalQty / standardBatchQty) * 100) / 100);
                        batchInput.value = calculatedBatch;
                    }
                } else if (lastEditedSource === 'total_batch') {
                    if (batchInput) {
                        const totalBatch = parseFloat(batchInput.value) || 1;
                        const calculatedQty = Math.round(standardBatchQty * totalBatch * 100) / 100;
                        productQtyInput.value = calculatedQty;
                        
                        // Keep other stages in sync too!
                        document.querySelectorAll('.stage-block').forEach(otherStage => {
                            if (otherStage !== specificStageBlock) {
                                const otherStandard = getStandardBatchQty(otherStage);
                                if (otherStandard > 0) {
                                    const otherBatchInput = otherStage.querySelector('.stage-batch-input');
                                    if (otherBatchInput) {
                                        const calculatedBatch = Math.max(0.01, Math.round((calculatedQty / otherStandard) * 100) / 100);
                                        otherBatchInput.value = calculatedBatch;
                                    }
                                }
                            }
                        });
                    }
                }
            }
        }
    }

    // --- Event Listeners ---

    document.getElementById('template_id').addEventListener('change', async function() {
        const id = this.value;
        if (!id) return;
        const container = document.getElementById('stagesContainer');
        const originalContent = container.innerHTML;
        container.innerHTML = '<div class="text-center p-12"><div class="animate-spin w-8 h-8 border-4 border-amber-500 border-t-transparent rounded-full mx-auto"></div><p class="text-[10px] text-slate-500 mt-4 font-black uppercase tracking-widest">Loading Template...</p></div>';

        try {
            const response = await fetch(`/production/work-orders/get-template/${id}`);
            if (!response.ok) throw new Error('Failed to fetch template');
            const data = await response.json();
            
            container.innerHTML = '';
            stageCount = 0;
            
            const setVal = (sel, val) => { const el = document.querySelector(sel); if (el && val != null) el.value = val; };
            setVal('select[name="production_line"]', data.production_line);
            setVal('input[name="marketing"]', data.marketing);
            setVal('input[name="duration"]', data.duration);
            setVal('input[name="stage_code"]', data.stage_code);
            setVal('input[name="composition_code"]', data.composition_code);
            const notesEl = document.querySelector('textarea[name="notes"]');
            if (notesEl) notesEl.value = data.notes || '';

            const productRows = document.getElementById('productRows');
            if (data.products && data.products.length > 0) {
                productRows.innerHTML = '';
                productCount = 0;
                data.products.forEach(p => addProductRow(p));
            } else if (data.product_id) {
                productRows.innerHTML = '';
                productCount = 0;
                addProductRow({ item_id: data.product_id, quantity: 1 });
            }

            if (data.stages && data.stages.length > 0) {
                const stages = Array.isArray(data.stages) ? data.stages : Object.values(data.stages);
                stages.forEach(stage => addStageRow(stage));
            } else {
                addStageRow();
            }
            // Automatically sync batch and target quantities
            const firstProductRow = document.querySelector('.product-row');
            if (firstProductRow) {
                const productQtyInput = firstProductRow.querySelector('input[name*="[quantity]"]');
                if (productQtyInput) syncTargetQtyAndBatch(productQtyInput);
            }
            updateAllDurations(this);
        } catch (error) {
            console.error('Template Load Error:', error);
            container.innerHTML = originalContent;
            alert('Gagal memuat template');
        }
    });

    document.getElementById('customer_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const codeInp = document.getElementById('customer_code');
        if (codeInp) codeInp.value = selected ? (selected.dataset.code || '') : '';
    });

    document.addEventListener('change', function(e) {
        if (e.target.matches('select[name*="[item_id]"]')) {
            const selected = e.target.options[e.target.selectedIndex];
            const row = e.target.closest('.grid');
            if (row) {
                const unitDisp = row.querySelector('.unit-display');
                if (unitDisp) unitDisp.value = selected ? (selected.dataset.unit || '') : '';
            }
            if (e.target.closest('.product-row')) {
                updateProductOptions();
            }
        }

        // Trigger dynamic stage item filtering when machine or row type is changed!
        if (e.target.matches('select[name*="[machine_id]"]') || e.target.matches('select[name*="[type]"]')) {
            const stageBlock = e.target.closest('.stage-block');
            if (stageBlock) filterStageItems(stageBlock);
        }

        if (e.target.matches('select[name*="[machine_id]"]') || 
            e.target.matches('select[name*="[item_id]"]') || 
            e.target.matches('select[name*="[type]"]') || 
            e.target.matches('.stage-batch-input') || 
            e.target.matches('input[name*="[quantity]"]') ||
            e.target.matches('.duration-hours-input') ||
            e.target.matches('.planned-start-input')) {
            updateAllDurations(e.target);
        }
    });

    document.addEventListener('input', function(e) {
        // Bi-directional synchronization between Target Qty and Total Batch per stage
        if (e.target.matches('.product-row input[name*="[quantity]"]') || e.target.matches('.stage-batch-input')) {
            syncTargetQtyAndBatch(e.target);
        }
        
        // Recalculate batch if output item quantity in stage changes
        if (e.target.matches('input[name*="[quantity]"]') && e.target.closest('.group\\/item')) {
            const row = e.target.closest('.group\\/item');
            const typeSelect = row ? row.querySelector('.stage-item-type') : null;
            if (typeSelect && typeSelect.value === 'output') {
                const stageBlock = e.target.closest('.stage-block');
                syncTargetQtyAndBatch(null, stageBlock);
            }
        }

        if (e.target.matches('.stage-batch-input') || 
            e.target.matches('input[name*="[quantity]"]') || 
            e.target.name === 'duration' || 
            e.target.id === 'production_date' ||
            e.target.matches('.duration-hours-input') ||
            e.target.matches('.planned-start-input')) {
            updateAllDurations(e.target);
            calculateFinishDate();
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            e.target.closest('.product-row').remove();
            updateProductOptions();
            updateAllDurations(e.target);
        }
        if (e.target.closest('.remove-stage')) {
            e.target.closest('.stage-block').remove();
            document.querySelectorAll('.stage-block').forEach((block, idx) => {
                const idxDisp = block.querySelector('.stage-index');
                if (idxDisp) idxDisp.textContent = idx + 1;
            });
            updateAllDurations(e.target);
        }
        if (e.target.closest('.remove-item')) {
            e.target.closest('.group\\/item').remove();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
        updateProductOptions();
        calculateFinishDate();
        
        // Initial filtering for any pre-loaded stages (from template/old data)
        document.querySelectorAll('.stage-block').forEach(stageBlock => {
            filterStageItems(stageBlock);
        });
    });
</script>
@endsection
