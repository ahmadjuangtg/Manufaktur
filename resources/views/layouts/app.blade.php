<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aori | Inventory Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #0f172a; color: #f8fafc; height: 100vh; overflow: hidden; }
        .sidebar { background-color: #1e293b; border-right: 1px solid rgba(255,255,255,0.05); transition: all 0.3s ease; height: 100%; display: flex; flex-direction: column; }
        .sidebar-item { color: #94a3b8; transition: all 0.3s; font-weight: 600; font-size: 1rem; }
        .sidebar-item:hover, .sidebar-item.active { 
            background-color: rgba(99, 102, 241, 0.1); 
            color: #818cf8; 
            box-shadow: inset 3px 0 0 #818cf8;
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .custom-scroll { overflow-y: auto; scrollbar-width: thin; scrollbar-color: #334155 transparent; }
        .custom-scroll::-webkit-scrollbar { width: 4px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        
        @media (max-width: 1024px) {
            .sidebar { position: fixed; top: 0; left: 0; bottom: 0; z-index: 50; transform: translateX(-100%); width: 280px; }
            .sidebar.open { transform: translateX(0); }
            .content-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 40; }
            .content-overlay.open { display: block; }
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden">
    <div id="overlay" class="content-overlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar w-72 flex flex-col shrink-0">
        <div class="p-8 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative w-12 h-12 bg-[#1d3557] rounded-2xl flex items-center justify-center border border-white/10 shadow-inner">
                    <div class="w-7 h-7 relative">
                        <div class="absolute inset-0 border-[3px] border-[#e63946] rounded-full"></div>
                        <div class="absolute inset-0 border-[3px] border-[#2a9d8f] rounded-full rotate-45 border-t-transparent border-r-transparent"></div>
                        <div class="absolute inset-0 flex items-center justify-center font-black text-white text-[12px]">A</div>
                    </div>
                </div>
                <span class="font-extrabold text-xl tracking-tight text-white">Aori <span class="text-indigo-500">System</span></span>
            </div>
            <button onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <nav id="sidebarNav" class="flex-1 px-4 space-y-1 overflow-y-auto custom-scroll pb-20">
            @if(Auth::user()->hasPermission('dashboard_view'))
            <a href="{{ url('/') }}" class="sidebar-item {{ Request::is('/') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="layout-dashboard" class="w-6 h-6"></i> Dashboard
            </a>
            @endif

            
            @php
                $hasMasterPermission = Auth::user()->hasPermission('master_item_view') || 
                                     Auth::user()->hasPermission('master_category_view') || 
                                     Auth::user()->hasPermission('master_type_view') || 
                                     Auth::user()->hasPermission('master_manufacturer_view') || 
                                     Auth::user()->hasPermission('master_unit_view') || 
                                     Auth::user()->hasPermission('master_customer_view') || 
                                     Auth::user()->hasPermission('master_machine_category_view') || 
                                     Auth::user()->hasPermission('master_machine_view') || 
                                     Auth::user()->hasPermission('master_warehouse_view') || 
                                     Auth::user()->hasPermission('master_supplier_view') || 
                                     Auth::user()->hasPermission('master_priority_view') || 
                                     Auth::user()->hasPermission('master_substitution_view');
            @endphp

            @if($hasMasterPermission)
            <div class="pt-8 pb-3 px-4 text-[12px] font-black text-slate-500 uppercase tracking-[0.2em]">Master Data</div>
            @if(Auth::user()->hasPermission('master_item_view'))
            <a href="{{ route('items.index') }}" class="sidebar-item {{ Request::is('master/items*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="package" class="w-6 h-6"></i> Master Item
            </a>
            @endif
            @if(Auth::user()->hasPermission('master_category_view'))
            <a href="{{ route('categories.index') }}" class="sidebar-item {{ Request::is('master/categories*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="tag" class="w-6 h-6"></i> Kategori
            </a>
            @endif
            @if(Auth::user()->hasPermission('master_type_view'))
            <a href="{{ route('types.index') }}" class="sidebar-item {{ Request::is('master/types*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="box" class="w-6 h-6"></i> Tipe Item
            </a>
            @endif
            @if(Auth::user()->hasPermission('master_manufacturer_view'))
            <a href="{{ route('manufacturers.index') }}" class="sidebar-item {{ Request::is('master/manufacturers*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="factory" class="w-6 h-6"></i> Manufaktur
            </a>
            @endif
            @if(Auth::user()->hasPermission('master_unit_view'))
            <a href="{{ route('units.index') }}" class="sidebar-item {{ Request::is('master/units*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="ruler" class="w-6 h-6"></i> Satuan
            </a>
            @endif
            @if(Auth::user()->hasPermission('master_customer_view'))
            <a href="{{ route('customers.index') }}" class="sidebar-item {{ Request::is('master/customers*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="user-circle" class="w-6 h-6"></i> Master Customer
            </a>
            @endif
            @if(Auth::user()->hasPermission('master_machine_category_view'))
            <a href="{{ route('machine_categories.index') }}" class="sidebar-item {{ Request::is('master/machine-categories*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="layers" class="w-6 h-6"></i> Kategori Mesin
            </a>
            @endif
            @if(Auth::user()->hasPermission('master_machine_view'))
            <a href="{{ route('machines.index') }}" class="sidebar-item {{ Request::is('master/machines*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="cpu" class="w-6 h-6"></i> Master Mesin
            </a>
            @endif
            @if(Auth::user()->hasPermission('master_warehouse_view'))
            <a href="{{ route('warehouses.index') }}" class="sidebar-item {{ Request::is('master/warehouses*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="home" class="w-6 h-6"></i> Master Gudang
            </a>
            @endif
            @if(Auth::user()->hasPermission('master_supplier_view'))
            <a href="{{ route('suppliers.index') }}" class="sidebar-item {{ Request::is('master/suppliers*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="truck" class="w-6 h-6"></i> Master Supplier
            </a>
            @endif
            @if(Auth::user()->hasPermission('master_priority_view'))
            <a href="{{ route('priorities.index') }}" class="sidebar-item {{ Request::is('master/priorities*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="list-ordered" class="w-6 h-6"></i> Master Prioritas
            </a>
            @endif
            @if(Auth::user()->hasPermission('master_substitution_view'))
            <a href="{{ route('substitutions.index') }}" class="sidebar-item {{ Request::is('master/substitutions*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="git-compare" class="w-6 h-6"></i> Substitusi & Capability
            </a>
            @endif
            @if(Auth::user()->hasPermission('master_price_list_view'))
            <a href="{{ route('price_lists.index') }}" class="sidebar-item {{ Request::is('master/price-lists*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="dollar-sign" class="w-6 h-6"></i> Daftar Harga
            </a>
            @endif
            @endif

            @php
                $hasProductionPermission = Auth::user()->hasPermission('production_template_view') || 
                                         Auth::user()->hasPermission('production_wo_view') || 
                                         Auth::user()->hasPermission('production_scheduling_view');
            @endphp
            @if($hasProductionPermission)
            <div class="pt-8 pb-3 px-4 text-[12px] font-black text-slate-500 uppercase tracking-[0.2em]">Production</div>
            @if(Auth::user()->hasPermission('production_template_view'))
            <a href="{{ route('production.templates.index') }}" class="sidebar-item {{ Request::is('production/templates*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="scroll-text" class="w-6 h-6"></i> Master Template
            </a>
            @endif
            @if(Auth::user()->hasPermission('production_wo_view'))
            <a href="{{ route('production.work_orders.index') }}" class="sidebar-item {{ Request::is('production/work-orders*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="cog" class="w-6 h-6"></i> Work Orders
            </a>
            @endif
            @if(Auth::user()->hasPermission('production_scheduling_view'))
            <a href="{{ route('production.scheduling.index') }}" class="sidebar-item {{ Request::is('production/scheduling*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="calendar-range" class="w-6 h-6"></i> Scheduling Production
            </a>
            @endif
            @endif

            @if(Auth::user()->hasPermission('inventory_view') || Auth::user()->hasPermission('stock_opname_view') || Auth::user()->hasPermission('stock_card_view'))
            <div class="pt-8 pb-3 px-4 text-[12px] font-black text-slate-500 uppercase tracking-[0.2em]">Transaksi</div>
            @if(Auth::user()->hasPermission('inventory_view'))
            <a href="{{ route('inventory.index') }}" class="sidebar-item {{ Request::is('transactions/inventory*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="arrow-left-right" class="w-6 h-6"></i> Inventory
            </a>
            @endif
            @if(Auth::user()->hasPermission('stock_mutation_view'))
            <a href="{{ route('mutations.request.index') }}" class="sidebar-item {{ Request::is('transactions/mutations/request*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="file-plus" class="w-6 h-6"></i> Request Mutasi
            </a>
            @endif
            @if(Auth::user()->hasPermission('stock_mutation_approval_view'))
            <a href="{{ route('mutations.approval.index') }}" class="sidebar-item {{ Request::is('transactions/mutations/approval*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="check-square" class="w-6 h-6"></i> Approval Mutasi
            </a>
            @endif
            @if(Auth::user()->hasPermission('stock_mutation_view'))
            <a href="{{ route('mutations.index') }}" class="sidebar-item {{ Request::is('transactions/mutations/index*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="truck" class="w-6 h-6"></i> Mutasi Gudang
            </a>
            @endif
            @if(Auth::user()->hasPermission('stock_opname_view'))
            <a href="{{ route('opname.index') }}" class="sidebar-item {{ Request::is('transactions/stock-opname') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="clipboard-list" class="w-6 h-6"></i> Stock Opname
            </a>
            @endif
            @if(Auth::user()->hasPermission('stock_opname_approval_view'))
            <a href="{{ route('opname.approval.index') }}" class="sidebar-item {{ Request::is('transactions/stock-opname/approval*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="check-circle" class="w-6 h-6"></i> Approval Stock Opname
            </a>
            @endif
            @if(Auth::user()->hasPermission('stock_card_view'))
            <a href="{{ route('stock_card.index') }}" class="sidebar-item {{ Request::is('transactions/stock-card*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="scroll-text" class="w-6 h-6"></i> Kartu Stock
            </a>
            @endif
            @endif

            @php
                $hasPurchasingPermission = Auth::user()->hasPermission('order_request_view') || 
                                         Auth::user()->hasPermission('order_approval_view') || 
                                         Auth::user()->hasPermission('order_po_view') || 
                                         Auth::user()->hasPermission('order_receive_view');
            @endphp
            @if($hasPurchasingPermission)
            <div class="pt-8 pb-3 px-4 text-[12px] font-black text-slate-500 uppercase tracking-[0.2em]">Order & Purchasing</div>
            @if(Auth::user()->hasPermission('order_request_view'))
            <a href="{{ route('orders.requests.index') }}" class="sidebar-item {{ Request::is('orders/requests*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="file-text" class="w-6 h-6"></i> Request Items
            </a>
            @endif
            @if(Auth::user()->hasPermission('order_approval_view'))
            <a href="{{ route('orders.approvals.index') }}" class="sidebar-item {{ Request::is('orders/approvals*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="check-square" class="w-6 h-6"></i> Approval Request
            </a>
            @endif
            @if(Auth::user()->hasPermission('order_po_view'))
            <a href="{{ route('orders.po.index') }}" class="sidebar-item {{ Request::is('orders/purchase-orders*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="shopping-cart" class="w-6 h-6"></i> Create PO
            </a>
            @endif
            @if(Auth::user()->hasPermission('order_receive_view'))
            <a href="{{ route('orders.receives.index') }}" class="sidebar-item {{ Request::is('orders/receives*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="package-check" class="w-6 h-6"></i> Receive Material
            </a>
            @endif
            @endif

            @php
                $hasSecurityPermission = Auth::user()->hasPermission('security_role_view') || 
                                       Auth::user()->hasPermission('security_account_view');
            @endphp
            @if($hasSecurityPermission)
            <div class="pt-8 pb-3 px-4 text-[12px] font-black text-slate-500 uppercase tracking-[0.2em]">Security & Access</div>
            @if(Auth::user()->hasPermission('security_role_view'))
            <a href="{{ route('roles.index') }}" class="sidebar-item {{ Request::is('security/roles*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="shield-check" class="w-6 h-6"></i> Access Roles
            </a>
            @endif
            @if(Auth::user()->hasPermission('security_account_view'))
            <a href="{{ route('accounts.index') }}" class="sidebar-item {{ Request::is('security/accounts*') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl">
                <i data-lucide="users" class="w-6 h-6"></i> Account Management
            </a>
            @endif
            @endif
        </nav>

        <div class="p-6 border-t border-white/5 bg-slate-900/20">
            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/50">
                <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center font-black text-xs uppercase text-white shadow-lg shadow-indigo-500/10">{{ substr(Auth::user()->name, 0, 1) }}</div>
                <div class="overflow-hidden">
                    <p class="text-white text-xs font-black truncate">{{ Auth::user()->name }}</p>
                    <p class="text-slate-500 text-[12px] font-bold uppercase tracking-widest">{{ Auth::user()->role->name ?? 'User' }}</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="mt-4">
                @csrf
                <button type="submit" class="w-full text-left text-[12px] font-black text-rose-500 hover:text-rose-400 uppercase tracking-[0.2em] px-4 py-2 flex items-center gap-3 transition-colors">
                    <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col bg-[#0f172a] h-screen overflow-hidden relative">
        <header class="p-6 lg:px-10 flex justify-between items-center bg-[#0f172a]/80 backdrop-blur-xl sticky top-0 z-10 border-b border-white/5">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 bg-slate-800 rounded-lg text-slate-300">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div>
                    <h2 class="text-xl lg:text-2xl font-black text-white tracking-tight">{{ $title ?? 'Dashboard' }}</h2>
                    <p class="hidden sm:block text-slate-500 text-[12px] font-bold uppercase tracking-[0.2em] mt-1">Aori Inventory Intelligence</p>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <div class="hidden md:block text-right">
                    <p class="text-white text-xs font-black">{{ Auth::user()->name }}</p>
                    <p class="text-emerald-500 text-[9px] font-black uppercase tracking-widest flex items-center justify-end gap-1">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span> Online
                    </p>
                </div>
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-black text-sm uppercase shadow-lg shadow-indigo-500/20">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 lg:p-10 custom-scroll">
            @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-500 text-xs font-bold flex items-center gap-3">
                <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}
            </div>
            @endif
            @yield('content')
        </div>
    </main>

    <script>
        lucide.createIcons();

        function showToast(message, type = 'error') {
            const toastContainer = document.getElementById('toast-container') || createToastContainer();
            const toast = document.createElement('div');
            
            const colors = {
                success: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-500',
                error: 'border-rose-500/20 bg-rose-500/10 text-rose-500',
                warning: 'border-amber-500/20 bg-amber-500/10 text-amber-500',
                info: 'border-indigo-500/20 bg-indigo-500/10 text-indigo-400'
            };

            const icons = {
                success: 'check-circle',
                error: 'alert-circle',
                warning: 'alert-triangle',
                info: 'info'
            };

            toast.className = `flex items-center gap-3 px-6 py-4 rounded-2xl border backdrop-blur-xl shadow-2xl transition-all duration-500 transform translate-y-10 opacity-0 ${colors[type]}`;
            toast.innerHTML = `<i data-lucide="${icons[type]}" class="w-5 h-5"></i> <span class="text-xs font-black uppercase tracking-widest">${message}</span>`;
            
            toastContainer.appendChild(toast);
            lucide.createIcons();

            // Animate In
            setTimeout(() => {
                toast.classList.remove('translate-y-10', 'opacity-0');
            }, 10);

            // Animate Out & Remove
            setTimeout(() => {
                toast.classList.add('translate-y-[-10px]', 'opacity-0');
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }

        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed bottom-10 left-1/2 transform -translate-x-1/2 z-[9999] flex flex-col gap-3 items-center pointer-events-none';
            document.body.appendChild(container);
            return container;
        }

        // Persist Sidebar Scroll Position
        const sidebarNav = document.getElementById('sidebarNav');
        if (sidebarNav) {
            // Restore position
            const scrollPos = localStorage.getItem('sidebarScrollPos');
            if (scrollPos) {
                sidebarNav.scrollTop = scrollPos;
            }

            // Save position on scroll (throttled)
            sidebarNav.addEventListener('scroll', () => {
                localStorage.setItem('sidebarScrollPos', sidebarNav.scrollTop);
            });
            
            // Also save on link click to be sure
            document.querySelectorAll('.sidebar-item').forEach(item => {
                item.addEventListener('click', () => {
                    localStorage.setItem('sidebarScrollPos', sidebarNav.scrollTop);
                });
            });
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('overlay').classList.toggle('open');
        }
    </script>
</body>
</html>
