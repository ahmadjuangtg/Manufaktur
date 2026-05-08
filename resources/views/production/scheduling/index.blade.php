@extends('layouts.app', ['title' => 'Production Scheduling'])

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-black text-white tracking-tight">Scheduling Production</h1>
            <p class="text-slate-500 text-xs font-bold uppercase tracking-[0.2em] mt-1">Gantt View & WO Prioritization</p>
        </div>
        <div class="flex gap-2">
            <div class="flex items-center gap-2 px-4 py-2 bg-slate-800/50 rounded-xl border border-white/5">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Legend:</span>
                @foreach($priorities as $p)
                <div class="flex items-center gap-1">
                    <div class="w-2 h-2 rounded-full" style="background-color: {{ $p->color }}"></div>
                    <span class="text-[9px] font-bold text-slate-300 uppercase">{{ $p->name }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Gantt Chart Container -->
    <div class="glass-card rounded-3xl overflow-hidden p-6 min-h-[600px]">
        <div id="gantt-container" class="w-full overflow-x-auto">
            <svg id="gantt"></svg>
        </div>
    </div>

    <!-- WO List for Scheduling -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-4">
            <h3 class="text-white font-black text-sm uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="list-todo" class="w-4 h-4 text-indigo-500"></i> Unscheduled / Pending
            </h3>
            <div class="space-y-3 max-h-[500px] overflow-y-auto custom-scroll pr-2">
                @foreach($workOrders as $wo)
                    @if(!$wo->scheduled_start)
                    <div class="glass-card p-4 rounded-2xl border-l-4 border-indigo-500 hover:bg-white/5 transition-all cursor-pointer group" onclick="editSchedule({{ $wo->toJson() }})">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-white font-bold text-xs">{{ $wo->wo_number }}</span>
                            @if($wo->priority)
                            <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-tighter" style="background-color: {{ $wo->priority->color }}20; color: {{ $wo->priority->color }}; border: 1px solid {{ $wo->priority->color }}40">
                                {{ $wo->priority->name }}
                            </span>
                            @endif
                        </div>
                        <p class="text-slate-400 text-[10px] font-bold mb-3">{{ $wo->customer->name ?? 'No Customer' }}</p>
                        <div class="flex items-center justify-between text-[9px] text-slate-500 font-bold uppercase">
                            <span>Line {{ $wo->production_line }}</span>
                            <span class="flex items-center gap-1"><i data-lucide="clock" class="w-3 h-3"></i> {{ $wo->duration }} Hrs</span>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        
        <div class="lg:col-span-2 space-y-4">
            <h3 class="text-white font-black text-sm uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="calendar" class="w-4 h-4 text-emerald-500"></i> Active Schedule
            </h3>
            <div class="glass-card rounded-2xl overflow-hidden border border-white/5">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-900/50 text-slate-500 uppercase tracking-widest font-black text-[9px]">
                            <th class="p-4 border-b border-white/5">WO Number</th>
                            <th class="p-4 border-b border-white/5">Priority</th>
                            <th class="p-4 border-b border-white/5">Scheduled Start</th>
                            <th class="p-4 border-b border-white/5">Scheduled End</th>
                            <th class="p-4 border-b border-white/5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($workOrders as $wo)
                            @if($wo->scheduled_start)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="p-4 text-white font-bold">{{ $wo->wo_number }}</td>
                                <td class="p-4">
                                    <span class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full" style="background-color: {{ $wo->priority->color ?? '#ccc' }}"></div>
                                        <span class="font-bold text-slate-300">{{ $wo->priority->name ?? 'None' }}</span>
                                    </span>
                                </td>
                                <td class="p-4 text-slate-400 font-mono">{{ \Carbon\Carbon::parse($wo->scheduled_start)->format('d M, H:i') }}</td>
                                <td class="p-4 text-slate-400 font-mono">{{ \Carbon\Carbon::parse($wo->scheduled_end)->format('d M, H:i') }}</td>
                                <td class="p-4 text-right">
                                    <button onclick="editSchedule({{ $wo->toJson() }})" class="p-2 bg-slate-800 rounded-lg text-slate-400 hover:text-white transition-colors">
                                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scheduling Modal -->
<div id="scheduleModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-[#1e293b] border border-white/10 w-full max-w-4xl rounded-2xl shadow-2xl p-10">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-white">Update Schedule</h3>
            <button onclick="closeScheduleModal()" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="scheduleForm" class="space-y-4">
            @csrf
            <input type="hidden" id="wo_id">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">WO Number</label>
                    <input type="text" id="wo_number_display" class="w-full bg-[#0f172a]/50 border border-white/5 rounded-lg py-3 px-4 text-slate-500 font-bold" readonly>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Priority Level*</label>
                    <select id="schedule_priority" class="w-full bg-[#0f172a] border border-white/10 rounded-lg py-3 px-4 focus:border-indigo-500 outline-none text-white font-semibold" required>
                        <option value="">-- Pilih Priority --</option>
                        @foreach($priorities as $p)
                        <option value="{{ $p->id }}">Level {{ $p->level }} - {{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Start Date*</label>
                    <input type="datetime-local" id="schedule_start" class="w-full bg-[#0f172a] border border-white/10 rounded-lg py-3 px-4 focus:border-indigo-500 outline-none text-white font-semibold" required>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">End Date*</label>
                    <input type="datetime-local" id="schedule_end" class="w-full bg-[#0f172a] border border-white/10 rounded-lg py-3 px-4 focus:border-indigo-500 outline-none text-white font-semibold" required>
                </div>
            </div>

            <div id="scheduleError" class="hidden p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-rose-500 text-[10px] font-bold uppercase tracking-widest mb-4"></div>

            <!-- Stage & Substitution Section -->
            <div class="mt-8 border-t border-white/5 pt-6">
                <div class="flex items-center gap-2 mb-4">
                    <i data-lucide="git-branch" class="w-3.5 h-3.5 text-indigo-400"></i>
                    <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Detail Tahapan & Substitusi</h4>
                </div>
                <div id="modal_stages_container" class="space-y-4 max-h-[300px] overflow-y-auto custom-scroll pr-2">
                    <!-- Stages injected via JS -->
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="closeScheduleModal()" class="px-6 py-2 text-slate-400 font-bold">Batal</button>
                <button type="submit" class="bg-indigo-600 text-white px-8 py-2.5 rounded-lg font-bold shadow-lg shadow-indigo-500/20">Save Schedule</button>
            </div>
        </form>
    </div>
</div>

<!-- Frappe Gantt JS & CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.min.js"></script>

<style>
    .gantt .bar-label { fill: #fff; font-weight: bold; font-size: 10px; }
    .gantt .bar { fill: #6366f1; stroke: none; rx: 10; ry: 10; }
    .gantt .grid-row:nth-child(even) { fill: #1e293b; }
    .gantt .grid-row { fill: #0f172a; }
    .gantt .grid-header { fill: #1e293b; stroke: rgba(255,255,255,0.05); }
    .gantt .upper-text { fill: #94a3b8; font-weight: bold; }
    .gantt .lower-text { fill: #64748b; }
    .gantt .today-highlight { fill: rgba(99, 102, 241, 0.1); }

    /* Dynamic Priority Colors */
    @foreach($priorities as $p)
    .gantt .bar-wrapper.priority-{{ $p->id }} .bar { fill: {{ $p->color }} !important; }
    .gantt .bar-wrapper.priority-{{ $p->id }} .bar-progress { fill: {{ $p->color }} !important; opacity: 0.5; }
    @endforeach

    /* Custom Searchable Select - Premium Version */
    .searchable-select-container {
        position: relative;
        width: 100%;
    }
    .custom-select-trigger {
        width: 100%;
        background: #0f172a;
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 1rem;
        padding: 0.75rem 1rem;
        color: white;
        font-size: 0.75rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .custom-select-trigger:hover {
        background: rgba(255,255,255,0.02);
        border-color: rgba(255,255,255,0.1);
    }
    .custom-select-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #1e293b;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 1rem;
        margin-top: 0.5rem;
        z-index: 100;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        display: none;
        overflow: hidden;
    }
    .custom-select-dropdown.show {
        display: block;
        animation: slideDown 0.2s ease-out;
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .custom-select-search {
        padding: 0.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .custom-select-search input {
        width: 100%;
        background: rgba(0,0,0,0.2);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 0.5rem;
        padding: 0.4rem 0.75rem;
        color: white;
        font-size: 0.75rem;
        outline: none;
    }
    .custom-select-options-list {
        max-height: 150px;
        overflow-y: auto;
    }
    .custom-option {
        padding: 0.6rem 1rem;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.75rem;
        color: #94a3b8;
    }
    .custom-option:hover {
        background: rgba(255,255,255,0.05);
        color: white;
    }
    .custom-option.selected {
        background: rgba(99, 102, 241, 0.1);
        color: #818cf8;
        font-weight: bold;
    }
</style>

<script>
    let gantt;
    const workOrders = @json($workOrders);
    
    function initGantt() {
        const tasks = workOrders
            .filter(wo => wo.scheduled_start && wo.scheduled_end)
            .map(wo => ({
                id: 'WO-' + wo.id,
                name: wo.wo_number,
                start: wo.scheduled_start,
                end: wo.scheduled_end,
                progress: wo.status === 'completed' ? 100 : (wo.status === 'in_progress' ? 50 : 0),
                custom_class: 'priority-' + (wo.priority_id || 0)
            }));

        if (tasks.length === 0) {
            document.getElementById('gantt-container').innerHTML = `
                <div class="flex flex-col items-center justify-center h-full py-20 text-slate-500">
                    <i data-lucide="calendar-off" class="w-16 h-16 mb-4 opacity-20"></i>
                    <p class="font-bold uppercase tracking-widest">No Active Schedule Found</p>
                    <p class="text-[10px] mt-1">Schedule work orders to see them in Gantt view</p>
                </div>
            `;
            lucide.createIcons();
            return;
        }

        gantt = new Gantt("#gantt", tasks, {
            header_height: 50,
            column_width: 30,
            step: 24,
            view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
            bar_height: 30,
            bar_corner_radius: 8,
            arrow_curve: 5,
            padding: 18,
            view_mode: 'Day',
            date_format: 'YYYY-MM-DD',
            on_date_change: (task, start, end) => {
                if (end < start) {
                    alert('Error: Tanggal Selesai tidak boleh sebelum Tanggal Mulai!');
                    initGantt(); // Reset Gantt
                    return;
                }
                const woId = task.id.replace('WO-', '');
                updateWO(woId, {
                    start: formatDate(start),
                    end: formatDate(end)
                });
            },
            on_progress_change: (task, progress) => {
                console.log(task, progress);
            },
            on_click: (task) => {
                const woId = task.id.replace('WO-', '');
                const wo = workOrders.find(w => w.id == woId);
                editSchedule(wo);
            }
        });
    }

    function formatDate(date) {
        return date.toISOString().slice(0, 19).replace('T', ' ');
    }

    async function editSchedule(wo) {
        document.getElementById('wo_id').value = wo.id;
        document.getElementById('wo_number_display').value = wo.wo_number;
        document.getElementById('schedule_priority').value = wo.priority_id || '';
        document.getElementById('schedule_start').value = wo.scheduled_start ? wo.scheduled_start.replace(' ', 'T').slice(0,16) : '';
        document.getElementById('schedule_end').value = wo.scheduled_end ? wo.scheduled_end.replace(' ', 'T').slice(0,16) : '';
        document.getElementById('scheduleError').classList.add('hidden');

        // --- Populate Stages & Substitutes ---
        const container = document.getElementById('modal_stages_container');
        container.innerHTML = '<div class="text-center py-8 text-[10px] text-slate-500 animate-pulse font-black uppercase tracking-widest">Memuat Tahapan...</div>';
        
        const fullWo = workOrders.find(w => w.id == wo.id);
        container.innerHTML = '';

        if (fullWo && fullWo.stages) {
            fullWo.stages.forEach(stage => {
                const stageDiv = document.createElement('div');
                stageDiv.className = 'p-6 bg-slate-900/50 rounded-2xl border border-white/5 space-y-6';
                stageDiv.innerHTML = `
                    <div class="flex justify-between items-center border-b border-white/5 pb-3">
                        <span class="text-sm font-black text-white uppercase tracking-wider">${stage.name}</span>
                        <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 rounded-lg text-xs font-black">${stage.duration_hours || 0} Hrs</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] text-slate-500 font-black uppercase tracking-widest ml-1">Mesin Operasional</label>
                            <select class="w-full bg-slate-900 border-white/10 rounded-xl py-3 px-4 text-xs text-indigo-300 font-bold outline-none focus:ring-1 focus:ring-indigo-500/50 machine-sub-select" data-stage-id="${stage.id}" data-current="${stage.machine_id}">
                                <option value="${stage.machine_id}">${stage.machine ? stage.machine.name : 'Pilih Mesin'}</option>
                            </select>
                        </div>
                        <div class="item-sub-list space-y-3"></div>
                    </div>
                `;
                
                const mSelect = stageDiv.querySelector('.machine-sub-select');
                fetchAndAppendSubstitutes('machine', stage.machine_id, mSelect);
                
                const itemArea = stageDiv.querySelector('.item-sub-list');
                if (stage.items) {
                    stage.items.forEach(si => {
                        const row = document.createElement('div');
                        row.className = 'space-y-1.5';
                        row.innerHTML = `
                            <label class="text-[10px] text-slate-500 font-black uppercase tracking-tight ml-1">Bahan: ${si.item ? si.item.name : 'Unknown'}</label>
                            <select class="w-full bg-slate-900/80 border-white/5 rounded-xl py-3 px-4 text-xs text-emerald-400 font-bold outline-none item-sub-select" data-stage-item-id="${si.id}">
                                <option value="${si.item_id}">-- Tetap (${si.item ? si.item.name : '?'}) --</option>
                            </select>
                        `;
                        itemArea.appendChild(row);
                        fetchAndAppendSubstitutes('item', si.item_id, row.querySelector('.item-sub-select'));
                    });
                }
                container.appendChild(stageDiv);
            });
        }
        
        document.getElementById('scheduleModal').classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Initialize searchable selects after modal content is ready
        setTimeout(initSearchableSelects, 100);
    }

    async function fetchAndAppendSubstitutes(type, id, selectElement) {
        if (!id) return;
        try {
            const response = await fetch(`/production/scheduling/get-substitutes?type=${type}&id=${id}`);
            const subs = await response.json();
            subs.forEach(sub => {
                const opt = document.createElement('option');
                opt.value = sub.id;
                opt.textContent = `PENGGANTI: ${sub.name}`;
                selectElement.appendChild(opt);
            });
        } catch (err) { console.error(err); }
    }

    function closeScheduleModal() {
        document.getElementById('scheduleModal').classList.add('hidden');
    }

    document.getElementById('scheduleForm').onsubmit = async (e) => {
        e.preventDefault();
        const start = document.getElementById('schedule_start').value;
        const end = document.getElementById('schedule_end').value;
        const errorDiv = document.getElementById('scheduleError');
        errorDiv.classList.add('hidden');

        if (new Date(end) < new Date(start)) {
            errorDiv.innerText = 'Error: Tanggal Selesai tidak boleh sebelum Tanggal Mulai!';
            errorDiv.classList.remove('hidden');
            return;
        }

        const woId = document.getElementById('wo_id').value;
        const formData = {
            start: start.replace('T', ' '),
            end: end.replace('T', ' '),
            priority_id: document.getElementById('schedule_priority').value,
            stage_machines: {},
            item_substitutions: {},
            _token: '{{ csrf_token() }}'
        };

        document.querySelectorAll('.machine-sub-select').forEach(sel => {
            formData.stage_machines[sel.dataset.stageId] = sel.value;
        });

        document.querySelectorAll('.item-sub-select').forEach(sel => {
            formData.item_substitutions[sel.dataset.stageItemId] = sel.value;
        });

        const success = await updateWO(woId, formData);
        if (success) {
            location.reload();
        }
    }

    async function updateWO(id, data) {
        try {
            const response = await fetch(`/production/scheduling/update/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });
            
            if (response.ok) {
                const result = await response.json();
                return result.success;
            } else if (response.status === 422) {
                const result = await response.json();
                const errorDiv = document.getElementById('scheduleError');
                if (errorDiv) {
                    errorDiv.innerText = 'Validation Error: ' + Object.values(result.errors).flat().join(', ');
                    errorDiv.classList.remove('hidden');
                }
                return false;
            } else {
                const result = await response.json();
                alert('Error: ' + (result.message || 'Terjadi kesalahan pada server (500).'));
                return false;
            }
        } catch (error) {
            console.error('Update failed:', error);
            return false;
        }
    }

    document.getElementById('schedule_start').addEventListener('change', function() {
        document.getElementById('schedule_end').min = this.value;
    });

    // Premium Searchable Select Logic
    function initSearchableSelects() {
        document.querySelectorAll('select').forEach(select => {
            if (select.closest('.searchable-select-container')) return;

            const container = document.createElement('div');
            container.className = 'searchable-select-container';
            
            const trigger = document.createElement('div');
            trigger.className = 'custom-select-trigger';
            trigger.innerHTML = `
                <span class="trigger-text text-[10px] font-bold uppercase tracking-tight">${select.options[select.selectedIndex]?.text || '-- Pilih --'}</span>
                <i data-lucide="chevron-down" class="w-3 h-3 text-slate-500"></i>
            `;
            
            const dropdown = document.createElement('div');
            dropdown.className = 'custom-select-dropdown';
            
            const searchWrap = document.createElement('div');
            searchWrap.className = 'custom-select-search';
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = 'Cari...';
            searchWrap.appendChild(searchInput);
            
            const optionsList = document.createElement('div');
            optionsList.className = 'custom-select-options-list custom-scrollbar';
            
            function renderOptions(filter = '') {
                optionsList.innerHTML = '';
                Array.from(select.options).forEach(opt => {
                    if (filter && !opt.text.toLowerCase().includes(filter.toLowerCase()) && opt.value !== "") return;
                    
                    const customOpt = document.createElement('div');
                    customOpt.className = 'custom-option' + (select.value === opt.value ? ' selected' : '');
                    customOpt.innerText = opt.text;
                    customOpt.onclick = () => {
                        select.value = opt.value;
                        trigger.querySelector('.trigger-text').innerText = opt.text;
                        dropdown.classList.remove('show');
                        select.dispatchEvent(new Event('change'));
                        renderOptions();
                    };
                    optionsList.appendChild(customOpt);
                });
            }

            trigger.onclick = (e) => {
                e.stopPropagation();
                document.querySelectorAll('.custom-select-dropdown.show').forEach(d => {
                    if (d !== dropdown) d.classList.remove('show');
                });
                dropdown.classList.toggle('show');
                if (dropdown.classList.contains('show')) searchInput.focus();
            };

            searchInput.onclick = (e) => e.stopPropagation();
            searchInput.onkeyup = () => renderOptions(searchInput.value);

            document.addEventListener('click', () => dropdown.classList.remove('show'));

            container.appendChild(trigger);
            dropdown.appendChild(searchWrap);
            dropdown.appendChild(optionsList);
            container.appendChild(dropdown);
            
            // Move original select inside container to prevent re-initialization
            select.style.display = 'none';
            select.parentNode.insertBefore(container, select);
            container.appendChild(select);
            
            renderOptions();
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initGantt();
        initSearchableSelects();
    });
</script>
@endsection
