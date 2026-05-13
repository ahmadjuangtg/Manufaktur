@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#0f172a] text-slate-200 pb-20">
    <!-- Header Section -->
    <div class="px-8 py-10 bg-gradient-to-b from-slate-900/50 to-transparent">
        <div class="flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-black text-white tracking-tight uppercase">Production Monitoring</h1>
                <p class="text-slate-500 text-xs font-bold tracking-widest mt-2 uppercase opacity-60">Gantt View & WO Prioritization</p>
            </div>
            <div class="flex gap-4">
                <div class="flex bg-slate-900/80 rounded-xl p-1 border border-white/5">
                    <button onclick="changeGanttView('Day')" id="view-day" class="px-4 py-2 rounded-lg text-[10px] font-black uppercase transition-all duration-300 bg-indigo-500 text-white shadow-lg shadow-indigo-500/20">Day</button>
                    <button onclick="changeGanttView('Week')" id="view-week" class="px-4 py-2 rounded-lg text-[10px] font-black uppercase transition-all duration-300 text-slate-500 hover:text-white">Week</button>
                    <button onclick="changeGanttView('Month')" id="view-month" class="px-4 py-2 rounded-lg text-[10px] font-black uppercase transition-all duration-300 text-slate-500 hover:text-white">Month</button>
                </div>
            </div>
        </div>
    </div>

    <div class="px-8 space-y-8">
        <!-- Gantt Chart Card -->
        <div class="bg-slate-900/40 rounded-3xl border border-white/5 overflow-hidden backdrop-blur-xl shadow-2xl">
            <div class="px-6 py-4 border-b border-white/5 flex justify-between items-center bg-white/[0.02]">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-6 bg-indigo-500 rounded-full"></div>
                    <span class="text-xs font-black uppercase tracking-widest text-slate-300">Timeline Produksi</span>
                </div>
                <div class="flex gap-6">
                    @foreach($priorities as $p)
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full" style="background-color: {{ $p->color ?? '#6366f1' }}"></div>
                            <span class="text-[9px] font-black uppercase text-slate-500">{{ $p->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div id="gantt-container" class="p-6 overflow-x-auto min-h-[400px]">
                <div id="gantt" class="w-full"></div>
            </div>
        </div>

        <!-- Active Schedule List -->
        <div class="grid grid-cols-1 gap-8">
            <div class="bg-slate-900/40 rounded-3xl border border-white/5 overflow-hidden backdrop-blur-xl shadow-2xl">
                <div class="px-6 py-5 border-b border-white/5 flex items-center gap-3 bg-white/[0.02]">
                    <div class="p-2 bg-emerald-500/10 rounded-lg">
                        <i data-lucide="list-checks" class="w-4 h-4 text-emerald-500"></i>
                    </div>
                    <h2 class="text-xs font-black uppercase tracking-widest text-slate-300">Active Schedule</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-950/30">
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">Seq</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">Line</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">WO Number</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">Product</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5 text-center">Qty</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">Priority</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">Scheduled Start</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">Scheduled End</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody id="wo-table-body">
                            @foreach($workOrders as $wo)
                            <tr class="group hover:bg-white/[0.02] transition-all duration-200 border-b border-white/[0.02]">
                                <td class="px-6 py-4">
                                    <span class="w-6 h-6 flex items-center justify-center bg-slate-800 rounded-lg text-[10px] font-black text-slate-400 group-hover:bg-indigo-500 group-hover:text-white transition-colors">{{ $wo->sort_order ?: '-' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-slate-800 rounded-md text-[10px] font-black text-slate-300">LINE {{ $wo->production_line }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-black text-white group-hover:text-indigo-400 transition-colors uppercase">{{ $wo->wo_number }}</div>
                                    <div class="text-[9px] text-slate-500 font-bold uppercase mt-0.5 opacity-60">{{ $wo->customer ? $wo->customer->name : 'No Customer' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @foreach($wo->products as $p)
                                    <div class="text-[10px] font-bold text-slate-300 truncate max-w-[200px]">{{ $p->item->name }}</div>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-xs font-black text-indigo-400">{{ number_format($wo->products->sum('quantity')) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-wider" style="background-color: {{ $wo->priority->color ?? '#6366f1' }}20; color: {{ $wo->priority->color ?? '#6366f1' }}">
                                        {{ $wo->priority->name ?? 'NORMAL' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-tight">
                                    {{ $wo->scheduled_start ? date('d M, H:i', strtotime($wo->scheduled_start)) : '-' }}
                                </td>
                                <td class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-tight">
                                    {{ $wo->scheduled_end ? date('d M, H:i', strtotime($wo->scheduled_end)) : '-' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button onclick='viewDetails({{ json_encode($wo) }})' class="p-2 hover:bg-emerald-500/10 rounded-xl transition-all group/btn">
                                            <i data-lucide="eye" class="w-4 h-4 text-slate-500 group-hover/btn:text-emerald-400"></i>
                                        </button>
                                        <button onclick='editSchedule({{ json_encode($wo) }})' class="p-2 hover:bg-indigo-500/10 rounded-xl transition-all group/btn">
                                            <i data-lucide="edit-3" class="w-4 h-4 text-slate-500 group-hover/btn:text-indigo-400"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div id="scheduleModal" class="fixed inset-0 z-[999] hidden">
    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-md"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-white/10 rounded-[40px] w-full max-w-5xl max-h-[90vh] shadow-3xl overflow-hidden flex flex-col animate-in fade-in zoom-in duration-300">
            <div class="px-10 py-8 border-b border-white/5 flex justify-between items-center bg-white/[0.02]">
                <div>
                    <h3 class="text-xl font-black text-white uppercase tracking-tight">Update Priority & Schedule</h3>
                    <p id="modal_wo_number" class="text-indigo-400 text-[10px] font-black uppercase tracking-[0.2em] mt-1"></p>
                </div>
                <button onclick="closeScheduleModal()" class="p-4 hover:bg-white/5 rounded-full transition-colors">
                    <i data-lucide="x" class="w-6 h-6 text-slate-400"></i>
                </button>
            </div>
            
            <form id="scheduleForm" class="flex flex-col flex-1 overflow-hidden">
                <input type="hidden" id="wo_id">
                
                <div class="flex-1 overflow-y-auto p-10 space-y-10 modal-scroll">
                    <div class="grid grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <label class="text-[10px] text-slate-500 font-black uppercase tracking-widest ml-1">Priority Level</label>
                            <select id="schedule_priority" class="w-full bg-slate-950 border-white/10 rounded-2xl py-4 px-6 text-sm text-white font-bold outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                                @foreach($priorities as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-3">
                            <label class="text-[10px] text-slate-500 font-black uppercase tracking-widest ml-1">Production Line</label>
                            <select id="schedule_line" class="w-full bg-slate-950 border-white/10 rounded-2xl py-4 px-6 text-sm text-white font-bold outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                                <option value="1">LINE 1</option>
                                <option value="2">LINE 2</option>
                                <option value="3">LINE 3</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <label class="text-[10px] text-slate-500 font-black uppercase tracking-widest ml-1">Start Date</label>
                            <input type="datetime-local" id="schedule_start" class="w-full bg-slate-950 border-white/10 rounded-2xl py-4 px-6 text-sm text-white font-bold outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                        </div>
                        <div class="space-y-3">
                            <label class="text-[10px] text-slate-500 font-black uppercase tracking-widest ml-1">End Date</label>
                            <input type="datetime-local" id="schedule_end" class="w-full bg-slate-950 border-white/10 rounded-2xl py-4 px-6 text-sm text-white font-bold outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                        </div>
                    </div>

                    <div id="substitution_section" class="space-y-8 pt-8 border-t border-white/5">
                        <div class="flex items-center justify-between">
                            <h4 class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] italic">Product & Stage Substitutions</h4>
                            <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 text-[8px] font-black rounded-full uppercase tracking-widest border border-indigo-500/20">Planning Phase</span>
                        </div>
                        
                        <!-- Main Product Substitution -->
                        <div id="main_product_substitutions" class="p-6 bg-white/[0.02] border border-white/5 rounded-3xl space-y-4">
                            <span class="text-[9px] text-slate-500 font-black uppercase tracking-widest">Main Finished Good Substitution</span>
                            <div id="item_substitutions_container" class="space-y-4"></div>
                        </div>

                        <!-- Stage-wise Grouping Container -->
                        <div id="stages_substitution_grid" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Dynamic stage-centric groups will be here -->
                        </div>
                    </div>
                </div>

                <div id="scheduleError" class="hidden mx-10 mb-4 p-4 bg-red-500/10 border border-red-500/20 rounded-2xl text-red-400 text-[10px] font-bold uppercase tracking-widest"></div>

                <div class="px-10 py-8 border-t border-white/5 bg-white/[0.02] flex gap-4">
                    <button type="button" onclick="closeScheduleModal()" class="flex-1 py-5 bg-slate-800 hover:bg-slate-700 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl transition-all">Batal</button>
                    <button type="submit" class="flex-1 py-5 bg-indigo-600 hover:bg-indigo-500 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl shadow-xl shadow-indigo-600/20 transition-all">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="fixed inset-0 z-[999] hidden">
    <div class="absolute inset-0 bg-slate-950/90 backdrop-blur-xl"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-white/10 rounded-[40px] w-full max-w-6xl max-h-[90vh] shadow-3xl overflow-hidden flex flex-col">
            <div class="px-10 py-8 border-b border-white/5 flex justify-between items-center bg-white/[0.02]">
                <div>
                    <h3 class="text-xl font-black text-white uppercase tracking-tight">Work Order Details</h3>
                    <p id="detail_wo_number" class="text-emerald-400 text-[10px] font-black uppercase tracking-[0.2em] mt-1"></p>
                </div>
                <button onclick="closeDetailModal()" class="p-4 hover:bg-white/5 rounded-full transition-colors">
                    <i data-lucide="x" class="w-6 h-6 text-slate-400"></i>
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-10 space-y-10">
                <div class="grid grid-cols-3 gap-8">
                    <div class="p-6 bg-white/[0.02] border border-white/5 rounded-3xl space-y-2">
                        <span class="text-[10px] text-slate-500 font-black uppercase tracking-widest">Customer</span>
                        <p id="detail_customer" class="text-sm font-bold text-white uppercase"></p>
                    </div>
                    <div class="p-6 bg-white/[0.02] border border-white/5 rounded-3xl space-y-2">
                        <span class="text-[10px] text-slate-500 font-black uppercase tracking-widest">Line</span>
                        <p id="detail_line" class="text-sm font-bold text-indigo-400 uppercase"></p>
                    </div>
                    <div class="p-6 bg-white/[0.02] border border-white/5 rounded-3xl space-y-2">
                        <span class="text-[10px] text-slate-500 font-black uppercase tracking-widest">Priority</span>
                        <p id="detail_priority" class="text-sm font-bold uppercase"></p>
                    </div>
                </div>
                
                <div class="p-8 bg-indigo-500/5 border border-indigo-500/10 rounded-[2.5rem] grid grid-cols-2 gap-10">
                    <div class="space-y-1">
                        <span class="text-[10px] text-indigo-400 font-black uppercase tracking-[0.2em] ml-1">Scheduled Start</span>
                        <div class="flex items-center gap-3 px-6 py-4 bg-slate-950/50 rounded-2xl border border-white/5">
                            <i data-lucide="calendar" class="w-4 h-4 text-indigo-400"></i>
                            <p id="detail_start" class="text-sm font-bold text-white uppercase tracking-wider">-</p>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-[10px] text-rose-400 font-black uppercase tracking-[0.2em] ml-1">Scheduled End</span>
                        <div class="flex items-center gap-3 px-6 py-4 bg-slate-950/50 rounded-2xl border border-white/5">
                            <i data-lucide="clock" class="w-4 h-4 text-rose-400"></i>
                            <p id="detail_end" class="text-sm font-bold text-white uppercase tracking-wider">-</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="package" class="w-3 h-3"></i> Products & Items
                    </h4>
                    <div id="detail_products" class="space-y-4"></div>
                </div>

                <div class="space-y-4">
                    <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="layers" class="w-3 h-3"></i> Production Stages
                    </h4>
                    <div id="detail_stages" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
                </div>
            </div>
            
            <div class="p-8 border-t border-white/5 bg-white/[0.01] flex justify-end">
                <button onclick="closeDetailModal()" class="px-10 py-4 bg-slate-800 hover:bg-slate-700 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl transition-all">Close Window</button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.css">
<style>
    .gantt .bar-label { 
        fill: #ffffff !important; 
        font-weight: 900 !important; 
        font-size: 11px !important; 
        text-transform: uppercase; 
        letter-spacing: 0.08em; 
        paint-order: stroke;
        stroke: #000000;
        stroke-width: 0.5px;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .gantt .handle { fill: transparent; }
    .gantt-container { background: transparent; border-radius: 20px; }
    .gantt .grid-row { fill: transparent !important; stroke: rgba(255,255,255,0.02) !important; }
    .gantt .grid-row:nth-child(even) { fill: transparent !important; }
    .gantt .grid-header { fill: #0f172a !important; stroke: rgba(255,255,255,0.05) !important; }
    .gantt .upper-text { fill: #94a3b8; font-weight: 900; text-transform: uppercase; font-size: 9px; }
    .gantt .lower-text { fill: #64748b; font-weight: 600; font-size: 10px; }
    .gantt .today-highlight { fill: rgba(99, 102, 241, 0.05); }
    .gantt .bar { fill: #4f46e5; stroke: rgba(255,255,255,0.1); cursor: pointer; }
    .gantt .bar-progress { fill: rgba(255,255,255,0.2); }
    
    #gantt-container { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    #gantt svg { min-width: 100%; }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.min.js"></script>
<script>
    const workOrders = @json($workOrders);
    let gantt = null;

    function initGantt(viewMode = 'Day') {
        const tasks = workOrders
            .filter(wo => wo.scheduled_start && wo.scheduled_end)
            .map(wo => ({
                id: wo.id.toString(),
                name: `${wo.wo_number} | LINE ${wo.production_line}`,
                start: wo.scheduled_start,
                end: wo.scheduled_end,
                progress: 0,
                custom_class: `bar-priority-${wo.id}`, // Unique class per task to inject color
                priority_color: wo.priority ? wo.priority.color : '#6366f1'
            }));

        if (tasks.length === 0) {
            document.getElementById('gantt').innerHTML = '<div class="text-center py-20 text-slate-500 font-black uppercase tracking-widest opacity-30">No Scheduled Production</div>';
            return;
        }

        // Inject dynamic CSS for task colors
        let styleHtml = '';
        tasks.forEach(t => {
            styleHtml += `
                .gantt .bar-priority-${t.id} .bar { fill: ${t.priority_color} !important; opacity: 0.8; }
                .gantt .bar-priority-${t.id} .bar-progress { fill: ${t.priority_color} !important; opacity: 1; }
            `;
        });
        const styleId = 'gantt-dynamic-colors';
        let styleTag = document.getElementById(styleId);
        if (!styleTag) {
            styleTag = document.createElement('style');
            styleTag.id = styleId;
            document.head.appendChild(styleTag);
        }
        styleTag.innerHTML = styleHtml;

        gantt = new Gantt("#gantt", tasks, {
            view_mode: viewMode,
            readonly: true,
            on_click: function (task) {
                const wo = workOrders.find(w => w.id == task.id);
                if (wo) viewDetails(wo);
            }
        });
    }

    function changeGanttView(mode) {
        ['Day', 'Week', 'Month'].forEach(m => {
            const btn = document.getElementById(`view-${m.toLowerCase()}`);
            if (m === mode) {
                btn.classList.add('bg-indigo-500', 'text-white');
                btn.classList.remove('text-slate-500');
            } else {
                btn.classList.remove('bg-indigo-500', 'text-white');
                btn.classList.add('text-slate-500');
            }
        });
        if (gantt) gantt.change_view_mode(mode);
    }

    function editSchedule(wo) {
        document.getElementById('wo_id').value = wo.id;
        document.getElementById('modal_wo_number').innerText = wo.wo_number;
        
        const priorityEl = document.getElementById('schedule_priority');
        priorityEl.value = wo.priority_id || '';
        document.getElementById('schedule_line').value = wo.production_line || '1';
        
        if (wo.scheduled_start) {
            document.getElementById('schedule_start').value = wo.scheduled_start.replace(' ', 'T').slice(0,16);
        }
        if (wo.scheduled_end) {
            document.getElementById('schedule_end').value = wo.scheduled_end.replace(' ', 'T').slice(0,16);
        }

        // Status-based Locking Logic
        const isInProgress = wo.status === 'in_progress';
        priorityEl.disabled = isInProgress;
        
        // Populate Item Substitutions (Main Product)
        let mainProductsHtml = '';
        wo.products.forEach(p => {
            let options = `<option value="${p.item_id}" selected>${p.item.name} (Original)</option>`;
            if (p.item.substitutes && p.item.substitutes.length > 0) {
                p.item.substitutes.forEach(s => {
                    options += `<option value="${s.id}">${s.name} (Substitute)</option>`;
                });
            }
            
            mainProductsHtml += `
                <div class="space-y-2">
                    <label class="block text-[10px] text-slate-500 font-black uppercase tracking-widest ml-1">${p.item.name}</label>
                    <select name="product_items[${p.id}]" class="w-full bg-[#0f172a] border border-white/10 rounded-xl py-3 px-4 text-white text-xs font-bold outline-none focus:border-indigo-500 transition-all" ${isInProgress ? 'disabled' : ''}>
                        ${options}
                    </select>
                </div>
            `;
        });
        document.getElementById('item_substitutions_container').innerHTML = mainProductsHtml;

        // Populate Stages Grid (Machine + Items per Stage)
        let stagesGridHtml = '';
        wo.stages.forEach(s => {
            // Machine Options
            let machineOptions = `<option value="${s.machine_id}" selected>${s.machine ? s.machine.name : 'No Machine'} (Original)</option>`;
            if (s.machine && s.machine.substitutes && s.machine.substitutes.length > 0) {
                s.machine.substitutes.forEach(m => {
                    machineOptions += `<option value="${m.id}">${m.name} (Substitute)</option>`;
                });
            }

            // Items per Stage Options
            let stageItemsHtml = '';
            if (s.items && s.items.length > 0) {
                s.items.forEach(si => {
                    let itemOptions = `<option value="${si.item_id}" selected>${si.item.name} (Original)</option>`;
                    if (si.item && si.item.substitutes && si.item.substitutes.length > 0) {
                        si.item.substitutes.forEach(sub => {
                            itemOptions += `<option value="${sub.id}">${sub.name} (Substitute)</option>`;
                        });
                    }
                    stageItemsHtml += `
                        <div class="space-y-1 mt-3">
                            <span class="text-[9px] text-slate-500 font-bold uppercase ml-1">Bahan: ${si.item.name} (${si.type || 'Input'})</span>
                            <select name="stage_items[${si.id}]" class="w-full bg-slate-950 border border-white/5 rounded-xl py-2 px-3 text-white text-[11px] font-bold outline-none focus:border-indigo-500 transition-all" ${isInProgress ? 'disabled' : ''}>
                                ${itemOptions}
                            </select>
                        </div>
                    `;
                });
            }

            stagesGridHtml += `
                <div class="p-6 bg-slate-950/40 border border-white/5 rounded-[2.5rem] space-y-4 hover:border-indigo-500/20 transition-all">
                    <div class="flex items-center gap-3 border-b border-white/5 pb-4">
                        <div class="w-8 h-8 bg-indigo-500/10 rounded-xl flex items-center justify-center">
                            <span class="text-xs font-black text-indigo-400">${s.sequence || '#'}</span>
                        </div>
                        <h5 class="text-xs font-black text-white uppercase tracking-tight">${s.name}</h5>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1">
                            <span class="text-[9px] text-slate-500 font-bold uppercase ml-1">Mesin Produksi</span>
                            <select name="stage_machines[${s.id}]" class="w-full bg-slate-950 border border-white/5 rounded-xl py-3 px-4 text-white text-[11px] font-bold outline-none focus:border-indigo-500 transition-all" ${isInProgress ? 'disabled' : ''}>
                                ${machineOptions}
                            </select>
                        </div>
                        ${stageItemsHtml}
                    </div>
                </div>
            `;
        });
        document.getElementById('stages_substitution_grid').innerHTML = stagesGridHtml;

        document.getElementById('scheduleModal').classList.remove('hidden');
    }

    function closeScheduleModal() {
        document.getElementById('scheduleModal').classList.add('hidden');
    }

    function viewDetails(wo) {
        document.getElementById('detail_wo_number').innerText = wo.wo_number;
        document.getElementById('detail_customer').innerText = wo.customer ? wo.customer.name : '-';
        document.getElementById('detail_line').innerText = 'LINE ' + wo.production_line;
        document.getElementById('detail_priority').innerText = wo.priority ? wo.priority.name : 'NORMAL';
        document.getElementById('detail_priority').style.color = wo.priority ? wo.priority.color : '#fff';

        // Add Start/End Estimation
        document.getElementById('detail_start').innerText = wo.scheduled_start ? dayjs(wo.scheduled_start).format('DD MMM YYYY - HH:mm') : '-';
        document.getElementById('detail_end').innerText = wo.scheduled_end ? dayjs(wo.scheduled_end).format('DD MMM YYYY - HH:mm') : '-';

        // Populate Products
        let productsHtml = '';
        wo.products.forEach(p => {
            let subsHtml = '';
            if (p.item.substitutes && p.item.substitutes.length > 0) {
                subsHtml = `
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="text-[8px] text-slate-500 font-black uppercase">Substitutes:</span>
                        ${p.item.substitutes.map(s => `
                            <span class="px-2 py-0.5 bg-indigo-500/10 text-indigo-400 text-[8px] font-black rounded uppercase border border-indigo-500/20">${s.name}</span>
                        `).join('')}
                    </div>
                `;
            }

            productsHtml += `
                <div class="p-4 bg-white/[0.02] border border-white/5 rounded-2xl">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-xs font-black text-white uppercase">${p.item.name}</div>
                            <div class="text-[9px] text-slate-500 font-bold uppercase mt-1">${p.item.code || 'NO-CODE'}</div>
                        </div>
                        <div class="text-sm font-black text-indigo-400">${number_format(p.quantity)}</div>
                    </div>
                    ${subsHtml}
                </div>
            `;
        });
        document.getElementById('detail_products').innerHTML = productsHtml;

        // Populate Stages
        let stagesHtml = '';
        wo.stages.forEach(s => {
            let machineSubsHtml = '';
            if (s.machine && s.machine.substitutes && s.machine.substitutes.length > 0) {
                machineSubsHtml = `
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span class="text-[8px] text-slate-600 font-bold uppercase">Alt Machines:</span>
                        ${s.machine.substitutes.map(m => `
                            <span class="px-1.5 py-0.5 bg-slate-800 text-slate-400 text-[8px] font-bold rounded border border-white/5">${m.name}</span>
                        `).join('')}
                    </div>
                `;
            }

            let machineCapsHtml = '';
            if (s.machine && s.machine.capabilities && s.machine.capabilities.length > 0) {
                machineCapsHtml = `
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span class="text-[8px] text-emerald-500/50 font-bold uppercase tracking-widest">Capabilities:</span>
                        ${s.machine.capabilities.slice(0, 3).map(c => `
                            <span class="text-[8px] text-emerald-500/80 font-bold italic">${c.name}</span>
                        `).join('<span class="text-slate-800 text-[8px]">|</span>')}
                    </div>
                `;
            }

            stagesHtml += `
                <div class="p-5 bg-slate-950/40 border border-white/5 rounded-2xl space-y-3">
                    <div class="flex justify-between items-center border-b border-white/5 pb-2">
                        <span class="text-[10px] font-black text-white uppercase">${s.name}</span>
                        <span class="text-[9px] font-bold text-emerald-400">${s.duration_hours || 0} HRS</span>
                    </div>
                    <div class="text-[10px] text-slate-500 font-bold uppercase">
                        Mesin: <span class="text-indigo-300">${s.machine ? s.machine.name : '-'}</span>
                    </div>
                    ${machineSubsHtml}
                    ${machineCapsHtml}
                </div>
            `;
        });
        document.getElementById('detail_stages').innerHTML = stagesHtml;

        document.getElementById('detailModal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
    }

    document.getElementById('scheduleForm').onsubmit = async (e) => {
        e.preventDefault();
        const start = document.getElementById('schedule_start').value;
        const end = document.getElementById('schedule_end').value;
        const woId = document.getElementById('wo_id').value;
        const priorityId = document.getElementById('schedule_priority').value;
        const line = document.getElementById('schedule_line').value;

        // Collect substitutions
        const productItems = {};
        document.querySelectorAll('select[name^="product_items"]').forEach(el => {
            const id = el.name.match(/\[(.*?)\]/)[1];
            productItems[id] = el.value;
        });

        const stageMachines = {};
        document.querySelectorAll('select[name^="stage_machines"]').forEach(el => {
            const id = el.name.match(/\[(.*?)\]/)[1];
            stageMachines[id] = el.value;
        });

        const stageItems = {};
        document.querySelectorAll('select[name^="stage_items"]').forEach(el => {
            const id = el.name.match(/\[(.*?)\]/)[1];
            stageItems[id] = el.value;
        });

        const formData = {
            start: start.replace('T', ' '),
            end: end.replace('T', ' '),
            priority_id: priorityId,
            production_line: line,
            product_items: productItems,
            stage_machines: stageMachines,
            stage_items: stageItems,
            _token: '{{ csrf_token() }}'
        };

        const response = await fetch(`/production/scheduling/update/${woId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(formData)
        });

        if (response.ok) {
            location.reload();
        } else {
            const result = await response.json();
            alert(result.message || 'Gagal mengupdate jadwal.');
        }
    }

    function number_format(num) {
        return new Intl.NumberFormat().format(num);
    }

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (gantt && workOrders.length > 0) {
                // Determine current view mode from active buttons
                let activeMode = 'Day';
                if (document.getElementById('view-week')?.classList.contains('bg-indigo-500')) activeMode = 'Week';
                if (document.getElementById('view-month')?.classList.contains('bg-indigo-500')) activeMode = 'Month';
                initGantt(activeMode);
            }
        }, 250);
    });

    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            initGantt();
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }, 100);
    });
</script>
@endsection
