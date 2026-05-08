@extends('layouts.app', ['title' => 'Edit Production Template'])

@section('content')
<div class="max-w-6xl mx-auto">
    <form action="{{ route('production.templates.update', $template->id) }}" method="POST" id="templateForm" class="space-y-8">
        @csrf
        @method('PUT')
        
        <!-- Header Actions -->
        <div class="flex items-center justify-between">
            <a href="{{ route('production.templates.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-white transition-colors text-xs font-black uppercase tracking-widest">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to List
            </a>
            <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black uppercase tracking-widest rounded-2xl transition-all shadow-lg shadow-indigo-500/20 flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i> Update Template
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
                        <h3 class="text-white font-black text-sm uppercase tracking-widest">Informasi Template</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Kode Template</label>
                            <input type="text" name="code" value="{{ $template->code }}" required class="w-full bg-slate-900/50 border-white/5 rounded-2xl px-5 py-4 text-sm text-indigo-400 font-bold focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Nama Template</label>
                            <input type="text" name="name" value="{{ $template->name }}" required class="w-full bg-slate-900/50 border-white/5 rounded-2xl px-5 py-4 text-sm text-white focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Default Line Produksi</label>
                            <select name="production_line" class="w-full bg-slate-900/50 border-white/5 rounded-2xl px-5 py-4 text-sm text-white focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all appearance-none cursor-pointer">
                                <option value="">-- Pilih Line (Opsional) --</option>
                                <option value="1" {{ $template->production_line == 1 ? 'selected' : '' }}>Line 1</option>
                                <option value="2" {{ $template->production_line == 2 ? 'selected' : '' }}>Line 2</option>
                                <option value="3" {{ $template->production_line == 3 ? 'selected' : '' }}>Line 3</option>
                                <option value="4" {{ $template->production_line == 4 ? 'selected' : '' }}>Line 4</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Default Marketing</label>
                            <input type="text" name="marketing" value="{{ $template->marketing }}" placeholder="Nama Marketing (Opsional)..." class="w-full bg-slate-900/50 border-white/5 rounded-2xl px-5 py-4 text-sm text-white focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Estimasi Durasi (Hari)</label>
                            <input type="number" name="duration" value="{{ $template->duration }}" placeholder="Contoh: 2" class="w-full bg-slate-900/50 border-white/5 rounded-2xl px-5 py-4 text-sm text-white focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                        </div>
                    </div>
                </div>

                <!-- Products Selection Card -->
                <div class="glass-card p-8 rounded-[2.5rem] border border-white/5 relative">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-6 bg-emerald-500 rounded-full"></div>
                            <h3 class="text-white font-black text-sm uppercase tracking-widest">Produk Yang Dihasilkan (Blueprint)</h3>
                        </div>
                        <button type="button" onclick="addProductRow()" class="p-2 bg-emerald-500/10 hover:bg-emerald-500 text-emerald-500 hover:text-white rounded-xl transition-all">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <div id="productRows" class="space-y-4">
                        @foreach($template->products as $index => $prod)
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end bg-white/[0.02] p-4 rounded-2xl border border-white/5 group product-row">
                            <div class="md:col-span-7 space-y-2">
                                <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest ml-1">Pilih Produk (Finished Good)</label>
                                <select name="products[{{ $index }}][item_id]" required class="w-full bg-slate-900 border-white/5 rounded-xl px-4 py-3 text-xs text-white outline-none">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($finishedGoods as $product)
                                    <option value="{{ $product->id }}" data-unit="{{ $product->unit->name ?? '' }}" {{ $prod->item_id == $product->id ? 'selected' : '' }}>{{ $product->code }} - {{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-4 space-y-2">
                                <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest ml-1">Standar Qty</label>
                                <div class="flex items-center gap-2 bg-slate-900 border border-white/5 rounded-2xl px-4 py-3">
                                    <input type="number" name="products[{{ $index }}][quantity]" value="{{ $prod->quantity }}" required min="1" class="w-full bg-transparent border-none text-sm text-white outline-none">
                                    <input type="text" class="w-16 bg-transparent border-none text-[10px] text-indigo-400 font-black uppercase outline-none unit-display text-right" readonly value="{{ $prod->item->unit->name ?? '' }}" placeholder="Unit">
                                </div>
                            </div>
                            <div class="md:col-span-1 flex justify-center pb-1">
                                <button type="button" class="text-slate-600 hover:text-rose-500 transition-colors opacity-0 group-hover:opacity-100 remove-row">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Stages Card -->
                <div class="glass-card p-8 rounded-[2.5rem] border border-white/5">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-6 bg-amber-500 rounded-full"></div>
                            <h3 class="text-white font-black text-sm uppercase tracking-widest">Tahapan Produksi (Master)</h3>
                        </div>
                        <button type="button" onclick="addStageRow()" class="px-6 py-2 bg-amber-500/10 hover:bg-amber-500 text-amber-500 hover:text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all flex items-center gap-2">
                            <i data-lucide="plus" class="w-3 h-3"></i> Tambah Tahapan
                        </button>
                    </div>

                    <div id="stagesContainer" class="space-y-6">
                        <!-- Stages will be injected here via JS init -->
                    </div>
                </div>
            </div>

            <!-- Right Column: Codes & Summary -->
            <div class="space-y-8">
                <div class="glass-card p-8 rounded-[2.5rem] border border-white/5 relative overflow-hidden sticky top-8">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-1.5 h-6 bg-blue-500 rounded-full"></div>
                        <h3 class="text-white font-black text-sm uppercase tracking-widest">Konfigurasi Tambahan</h3>
                    </div>

                    <div class="space-y-6">
                        <div class="pt-2 space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Default Kode Tahapan</span>
                                <input type="text" name="stage_code" value="{{ $template->stage_code }}" placeholder="Misal: STEP-01" class="bg-transparent border-b border-white/10 text-right text-xs text-white outline-none focus:border-indigo-500 w-32">
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Default Kode Komposisi</span>
                                <input type="text" name="composition_code" value="{{ $template->composition_code }}" placeholder="Misal: COMP-A" class="bg-transparent border-b border-white/10 text-right text-xs text-white outline-none focus:border-indigo-500 w-32">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Catatan / Resep Master</label>
                            <textarea name="notes" rows="6" class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all resize-none" placeholder="Tambahkan instruksi resep standar...">{{ $template->notes }}</textarea>
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Nama Tahapan</label>
                <input type="text" name="stages[__INDEX__][name]" required class="w-full bg-slate-900 border-white/5 rounded-xl px-4 py-3 text-xs text-white outline-none" placeholder="Contoh: Pencampuran Awal">
            </div>
            <div class="space-y-2">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Mesin / Area Default</label>
                <select name="stages[__INDEX__][machine_id]" class="w-full bg-slate-900 border-white/5 rounded-xl px-4 py-3 text-xs text-white outline-none">
                    <option value="">-- Pilih Mesin --</option>
                    @foreach($machines as $machine)
                    <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Blueprint Bahan & Output</span>
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
        <div class="md:col-span-6">
            <select name="stages[__STAGE_INDEX__][items][__ITEM_INDEX__][item_id]" required class="w-full bg-slate-800 border-transparent rounded-lg px-3 py-2 text-[10px] text-white outline-none">
                <option value="">-- Pilih Item --</option>
                @foreach($items as $item)
                <option value="{{ $item->id }}" data-unit="{{ $item->unit->name ?? '' }}">{{ $item->code }} - {{ $item->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-3">
            <div class="flex items-center gap-2 bg-slate-800 rounded-lg px-3">
                <input type="number" step="0.01" name="stages[__STAGE_INDEX__][items][__ITEM_INDEX__][quantity_per_batch]" required class="w-full bg-transparent border-none py-2 text-[10px] text-white outline-none" placeholder="0.00">
                <input type="text" class="w-12 bg-transparent border-none text-[9px] text-indigo-400 font-black uppercase outline-none unit-display" readonly placeholder="Unit">
            </div>
        </div>
        <div class="md:col-span-2">
            <select name="stages[__STAGE_INDEX__][items][__ITEM_INDEX__][type]" class="w-full bg-slate-800 border-transparent rounded-lg px-2 py-2 text-[9px] text-indigo-400 font-bold uppercase outline-none">
                <option value="input">INPUT</option>
                <option value="output">OUTPUT</option>
            </select>
        </div>
        <div class="md:col-span-1 flex justify-center">
            <button type="button" class="text-slate-700 hover:text-rose-500 transition-colors remove-item">
                <i data-lucide="trash" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</template>

<script>
    let productCount = {{ $template->products->count() }};
    let stageCount = 0;

    function addProductRow() {
        const row = `
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end bg-white/[0.02] p-4 rounded-2xl border border-white/5 group product-row">
                <div class="md:col-span-7 space-y-2">
                    <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest ml-1">Pilih Produk (Finished Good)</label>
                    <select name="products[${productCount}][item_id]" required class="w-full bg-slate-900 border-white/5 rounded-xl px-4 py-3 text-xs text-white outline-none">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($finishedGoods as $product)
                        <option value="{{ $product->id }}" data-unit="{{ $product->unit->name ?? '' }}">{{ $product->code }} - {{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-4 space-y-2">
                    <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest ml-1">Standar Qty</label>
                    <div class="flex items-center gap-2 bg-slate-900 border border-white/5 rounded-2xl px-4 py-3">
                        <input type="number" name="products[${productCount}][quantity]" required min="1" class="w-full bg-transparent border-none text-sm text-white outline-none" placeholder="0">
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
        document.getElementById('productRows').insertAdjacentHTML('beforeend', row);
        productCount++;
        lucide.createIcons();
    }

    function addStageRow(data = null) {
        const container = document.getElementById('stagesContainer');
        
        let html = document.getElementById('stageTemplate').innerHTML;
        html = html.replace(/__INDEX__/g, stageCount);
        
        const div = document.createElement('div');
        div.innerHTML = html;
        container.appendChild(div.firstElementChild);
        
        const stageBlock = container.lastElementChild;
        stageBlock.querySelector('.stage-index').textContent = stageCount + 1;

        if (data) {
            stageBlock.querySelector('input[name*="[name]"]').value = data.name;
            stageBlock.querySelector('select[name*="[machine_id]"]').value = data.machine_id;
            
            if (data.items) {
                data.items.forEach(item => addItemRow(stageBlock, item));
            }
        }
    }

    function addItemRow(stageBlock, data = null) {
        const stageIndex = stageBlock.dataset.index;
        const itemContainer = stageBlock.querySelector('.item-rows');
        const itemIndex = itemContainer.querySelectorAll('.group\\/item').length;
        
        let html = document.getElementById('itemTemplate').innerHTML;
        html = html.replace(/__STAGE_INDEX__/g, stageIndex);
        html = html.replace(/__ITEM_INDEX__/g, itemIndex);
        
        const div = document.createElement('div');
        div.innerHTML = html;
        itemContainer.appendChild(div.firstElementChild);

        if (data) {
            const itemBlock = itemContainer.lastElementChild;
            const select = itemBlock.querySelector('select[name*="[item_id]"]');
            select.value = data.item_id;
            itemBlock.querySelector('input[name*="[quantity_per_batch]"]').value = data.quantity_per_batch;
            itemBlock.querySelector('select[name*="[type]"]').value = data.type;
            
            // Trigger unit display
            const selected = select.options[select.selectedIndex];
            if (selected) {
                itemBlock.querySelector('.unit-display').value = selected.dataset.unit || '';
            }
        }
        
        lucide.createIcons();
    }

    // Initialize stages from PHP
    @foreach($template->stages as $stage)
        addStageRow({
            name: "{{ $stage->name }}",
            machine_id: "{{ $stage->machine_id }}",
            items: [
                @foreach($stage->items as $item)
                {
                    item_id: "{{ $item->item_id }}",
                    quantity_per_batch: "{{ $item->quantity_per_batch }}",
                    type: "{{ $item->type }}"
                },
                @endforeach
            ]
        });
    @endforeach

    // Unit Display Change Listener
    document.addEventListener('change', function(e) {
        if (e.target.matches('select[name*="[item_id]"]')) {
            const selected = e.target.options[e.target.selectedIndex];
            const row = e.target.closest('.grid');
            const unitDisplay = row.querySelector('.unit-display');
            if (unitDisplay) {
                unitDisplay.value = selected.dataset.unit || '';
            }
        }
    });

    // Delegated Event Listeners
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            e.target.closest('.product-row').remove();
        }
        if (e.target.closest('.remove-stage')) {
            e.target.closest('.stage-block').remove();
            // Re-index stages
            document.querySelectorAll('.stage-block').forEach((block, idx) => {
                block.querySelector('.stage-index').textContent = idx + 1;
            });
        }
        if (e.target.closest('.remove-item')) {
            e.target.closest('.group\\/item').remove();
        }
    });
</script>
@endsection
