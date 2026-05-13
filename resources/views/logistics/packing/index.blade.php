@extends('layouts.app', ['title' => 'Packing List'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-end mb-8">
        <div>
            <h3 class="text-xl font-black text-white uppercase tracking-tight">Packing List Management</h3>
            <p class="text-slate-400 text-sm italic">Organize items into packages for delivery</p>
        </div>
        <button onclick="toggleModal('createPackingModal')" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-indigo-500/20 flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i> Create Packing List
        </button>
    </div>

    <div class="grid grid-cols-1 gap-6">
        @forelse($data as $p)
        <div class="glass-card p-6 rounded-[2rem] border border-white/5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-600/5 blur-[50px] rounded-full -mr-16 -mt-16"></div>
            
            <div class="flex flex-wrap justify-between items-start gap-4 relative z-10">
                <div class="flex gap-4">
                    <div class="w-12 h-12 bg-slate-800 rounded-2xl flex items-center justify-center text-indigo-400">
                        <i data-lucide="box" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="text-xs font-black text-slate-500 uppercase tracking-widest mb-1">{{ $p->packing_no }}</div>
                        <h4 class="text-white font-bold">{{ $p->details->count() }} Items Packed</h4>
                        <div class="text-[10px] text-slate-500 mt-1">Dibuat oleh: {{ $p->user->name }} | {{ $p->created_at->format('d M Y H:i') }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <div class="text-right">
                        <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Status</div>
                        <span class="px-3 py-1 bg-slate-800 text-indigo-400 text-[10px] font-black uppercase tracking-widest rounded-lg border border-white/5">
                            {{ $p->status }}
                        </span>
                    </div>
                    @if($p->status == 'DRAFT')
                    <div class="text-right border-l border-white/5 pl-6">
                        <form action="{{ route('logistics.packing.ready', $p->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg shadow-emerald-500/20">
                                Mark as Ready
                            </button>
                        </form>
                    </div>
                    @endif
                    @if($p->deliveryBatch)
                    <div class="text-right border-l border-white/5 pl-6">
                        <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Delivery Batch</div>
                        <span class="text-xs text-white font-bold">{{ $p->deliveryBatch->batch_no }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-white/5">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($p->details as $d)
                    <div class="flex items-center gap-3 bg-white/[0.02] p-3 rounded-xl border border-white/5">
                        <div class="w-8 h-8 bg-slate-800 rounded-lg flex items-center justify-center text-[10px] font-black text-white">
                            {{ $d->package_type == 'Box' ? 'BX' : 'PL' }}
                        </div>
                        <div>
                            <div class="text-[10px] text-white font-bold">{{ $d->item->name }}</div>
                            <div class="text-[9px] text-slate-500">{{ $d->quantity + 0 }} {{ $d->item->unit->name }} | {{ $d->package_type }} #{{ $d->package_number }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
        <div class="glass-card p-20 rounded-[2rem] border border-white/5 text-center">
            <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-600">
                <i data-lucide="package-search" class="w-10 h-10"></i>
            </div>
            <h3 class="text-white font-bold">Belum ada Packing List</h3>
            <p class="text-slate-500 text-sm mt-2">Mulai buat packing list untuk item yang siap dikirim.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Create Modal -->
<div id="createPackingModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="toggleModal('createPackingModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-4xl p-6">
        <form action="{{ route('logistics.packing.store') }}" method="POST" class="glass-card p-8 rounded-[2.5rem] border border-white/10 relative overflow-hidden">
            @csrf
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-white font-black text-sm uppercase tracking-widest">New Packing List</h3>
                <button type="button" onclick="toggleModal('createPackingModal')" class="text-slate-500 hover:text-white"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>

            <div class="space-y-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Items to Pack</label>
                        <button type="button" onclick="addItemRow()" class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">+ Add Item</button>
                    </div>
                    <div id="itemRows" class="space-y-3">
                        <div class="grid grid-cols-12 gap-3 items-center bg-white/[0.02] p-3 rounded-2xl border border-white/5">
                            <div class="col-span-4">
                                <select name="items[0][item_id]" required class="w-full bg-slate-900 border-white/5 rounded-xl px-4 py-3 text-xs text-white outline-none">
                                    <option value="">-- Pilih Item --</option>
                                    @foreach($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-2">
                                <input type="number" step="0.01" name="items[0][quantity]" placeholder="Qty" required class="w-full bg-slate-900 border-white/5 rounded-xl px-4 py-3 text-xs text-white outline-none">
                            </div>
                            <div class="col-span-3">
                                <select name="items[0][package_type]" class="w-full bg-slate-900 border-white/5 rounded-xl px-4 py-3 text-xs text-white outline-none">
                                    <option value="Box">Box</option>
                                    <option value="Pallet">Pallet</option>
                                    <option value="Bag">Bag</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <input type="text" name="items[0][package_number]" placeholder="# No" class="w-full bg-slate-900 border-white/5 rounded-xl px-4 py-3 text-xs text-white outline-none">
                            </div>
                            <div class="col-span-1 text-center">
                                <button type="button" class="text-slate-600 hover:text-rose-500 transition-colors" onclick="this.closest('.grid').remove()"><i data-lucide="trash" class="w-4 h-4 mx-auto"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Catatan</label>
                    <textarea name="note" rows="3" class="w-full bg-slate-900 border-white/5 rounded-2xl px-5 py-4 text-sm text-white focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all resize-none" placeholder="Tambahkan catatan..."></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-4 mt-8">
                <button type="button" onclick="toggleModal('createPackingModal')" class="px-6 py-3 text-xs font-black text-slate-500 uppercase tracking-widest">Batal</button>
                <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black uppercase tracking-widest rounded-2xl transition-all shadow-lg shadow-indigo-500/20">Simpan Packing List</button>
            </div>
        </form>
    </div>
</div>

<script>
    let itemCount = 1;
    function addItemRow() {
        const container = document.getElementById('itemRows');
        const index = itemCount++;
        const html = `
            <div class="grid grid-cols-12 gap-3 items-center bg-white/[0.02] p-3 rounded-2xl border border-white/5">
                <div class="col-span-4">
                    <select name="items[${index}][item_id]" required class="w-full bg-slate-900 border-white/5 rounded-xl px-4 py-3 text-xs text-white outline-none">
                        <option value="">-- Pilih Item --</option>
                        @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <input type="number" step="0.01" name="items[${index}][quantity]" placeholder="Qty" required class="w-full bg-slate-900 border-white/5 rounded-xl px-4 py-3 text-xs text-white outline-none">
                </div>
                <div class="col-span-3">
                    <select name="items[${index}][package_type]" class="w-full bg-slate-900 border-white/5 rounded-xl px-4 py-3 text-xs text-white outline-none">
                        <option value="Box">Box</option>
                        <option value="Pallet">Pallet</option>
                        <option value="Bag">Bag</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <input type="text" name="items[${index}][package_number]" placeholder="# No" class="w-full bg-slate-900 border-white/5 rounded-xl px-4 py-3 text-xs text-white outline-none">
                </div>
                <div class="col-span-1 text-center">
                    <button type="button" class="text-slate-600 hover:text-rose-500 transition-colors" onclick="this.closest('.grid').remove()"><i data-lucide="trash" class="w-4 h-4 mx-auto"></i></button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        if(typeof lucide !== 'undefined') lucide.createIcons();
    }

    function toggleModal(id) {
        const modal = document.getElementById(id);
        modal.classList.toggle('hidden');
    }
</script>
@endsection
