<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aori | Inventory Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #0f172a; color: #f8fafc; height: 100vh; overflow: hidden; }
        .sidebar { 
            width: 288px;
            background-color: #1e293b; 
            border-right: 1px solid rgba(255,255,255,0.05); 
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            height: 100%; 
            display: flex; 
            flex-direction: column; 
        }
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
        
        /* Collapsed Sidebar (Tray) Premium Styling */
        @media (min-width: 1024px) {
            .sidebar.collapsed {
                width: 88px !important;
                overflow: visible !important;
            }
            .sidebar.collapsed #sidebarNav {
                overflow: visible !important;
            }
            .sidebar.collapsed .logo-text,
            .sidebar.collapsed button span span,
            .sidebar.collapsed button [id^="chevron-"],
            .sidebar.collapsed div[id^="dropdown-"],
            .sidebar.collapsed .profile-info,
            .sidebar.collapsed .sign-out-text {
                display: none !important;
            }
            .sidebar.collapsed .brand-container {
                justify-content: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            .sidebar.collapsed .sidebar-item-text {
                display: none !important;
            }
            .sidebar.collapsed .sidebar-item {
                justify-content: center !important;
                padding: 12px !important;
                gap: 0 !important;
            }
            .sidebar.collapsed button {
                justify-content: center !important;
                padding: 12px !important;
                gap: 0 !important;
            }
            .sidebar.collapsed .sidebar-dropdown-wrapper {
                text-align: center !important;
            }
            .sidebar.collapsed .sidebar-profile-card {
                justify-content: center !important;
                padding: 8px !important;
            }
            .sidebar.collapsed .sidebar-profile-container {
                padding: 12px !important;
            }
            .sidebar.collapsed .collapsed-logout-btn {
                justify-content: center !important;
                padding: 12px !important;
                gap: 0 !important;
            }
            
            /* Collapsed Hover Popovers */
            .sidebar.collapsed .sidebar-item {
                position: relative !important;
            }
            .sidebar.collapsed .sidebar-item:hover .sidebar-popover {
                display: block !important;
                position: absolute !important;
                left: 100% !important;
                top: 50% !important;
                transform: translateY(-50%) !important;
                margin-left: 12px !important;
                background-color: #1e293b !important;
                border: 1px solid rgba(255, 255, 255, 0.05) !important;
                color: #ffffff !important;
                font-size: 0.85rem !important;
                font-weight: 700 !important;
                padding: 10px 16px !important;
                border-radius: 10px !important;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5) !important;
                white-space: nowrap !important;
                z-index: 999 !important;
                animation: popoverFadeIn 0.2s cubic-bezier(0.4, 0, 0.2, 1) forwards !important;
            }
            .sidebar.collapsed .sidebar-item:hover .sidebar-popover::before {
                content: '' !important;
                position: absolute !important;
                left: -16px !important;
                top: 0 !important;
                width: 16px !important;
                height: 100% !important;
                background: transparent !important;
                z-index: -1 !important;
            }
            
            .sidebar.collapsed .sidebar-dropdown-wrapper {
                position: relative !important;
            }
            .sidebar.collapsed .sidebar-dropdown-wrapper:hover div[id^="dropdown-"] {
                display: block !important;
                position: absolute !important;
                left: 100% !important;
                top: 0 !important;
                margin-left: 12px !important;
                width: 240px !important;
                background-color: #1e293b !important;
                border: 1px solid rgba(255, 255, 255, 0.05) !important;
                border-radius: 12px !important;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.4) !important;
                z-index: 999 !important;
                padding: 6px !important;
                margin-top: 0 !important;
                border-left: none !important;
                animation: popoverFadeInDropdown 0.2s cubic-bezier(0.4, 0, 0.2, 1) forwards !important;
            }
            .sidebar.collapsed .sidebar-dropdown-wrapper:hover div[id^="dropdown-"]::before {
                content: '' !important;
                position: absolute !important;
                left: -16px !important;
                top: 0 !important;
                width: 16px !important;
                height: 100% !important;
                background: transparent !important;
                z-index: -1 !important;
            }
            .sidebar.collapsed .sidebar-dropdown-wrapper:hover .collapsed-popover-header {
                display: block !important;
            }
            .sidebar.collapsed .sidebar-dropdown-wrapper:hover div[id^="dropdown-"] .sidebar-item {
                justify-content: start !important;
                padding: 10px 14px !important;
                gap: 12px !important;
                font-size: 0.85rem !important;
                background-color: transparent !important;
            }
            .sidebar.collapsed .sidebar-dropdown-wrapper:hover div[id^="dropdown-"] .sidebar-item .sidebar-item-text {
                display: inline-block !important;
                color: #94a3b8 !important;
            }
            .sidebar.collapsed .sidebar-dropdown-wrapper:hover div[id^="dropdown-"] .sidebar-item:hover {
                background-color: rgba(99, 102, 241, 0.1) !important;
                color: #818cf8 !important;
            }
            .sidebar.collapsed .sidebar-dropdown-wrapper:hover div[id^="dropdown-"] .sidebar-item:hover .sidebar-item-text {
                color: #818cf8 !important;
            }
        }
        
        .sidebar-popover {
            display: none;
        }
        
        @keyframes popoverFadeIn {
            from { opacity: 0; transform: translateY(-50%) translateX(-8px); }
            to { opacity: 1; transform: translateY(-50%) translateX(0); }
        }
        @keyframes popoverFadeInDropdown {
            from { opacity: 0; transform: translateX(-8px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @media (max-width: 1024px) {
            .sidebar { position: fixed; top: 0; left: 0; bottom: 0; z-index: 50; transform: translateX(-100%); width: 280px; }
            .sidebar.open { transform: translateX(0); }
            .content-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 40; }
            .content-overlay.open { display: block; }
        }

        /* SweetAlert2 Custom Styling */
        .swal2-popup {
            background: rgba(15, 23, 42, 0.8) !important;
            backdrop-filter: blur(20px) saturate(180%) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 2.5rem !important;
            color: #f8fafc !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
            padding: 2rem !important;
        }
        .swal2-title { 
            color: #f8fafc !important; 
            font-weight: 800 !important; 
            text-transform: uppercase !important; 
            letter-spacing: 0.1em !important; 
            font-size: 1.1rem !important;
            margin-bottom: 1rem !important;
        }
        .swal2-html-container { 
            color: #94a3b8 !important; 
            font-size: 0.875rem !important; 
            font-weight: 500 !important;
            line-height: 1.6 !important;
        }
        .swal2-icon {
            border-width: 2px !important;
            margin: 1rem auto 2rem !important;
        }
        .swal2-confirm { 
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important; 
            border-radius: 1.25rem !important; 
            font-weight: 800 !important; 
            text-transform: uppercase !important; 
            font-size: 0.75rem !important; 
            letter-spacing: 0.15em !important; 
            padding: 1rem 2.5rem !important;
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4) !important;
            border: none !important;
        }
        .swal2-cancel { 
            background: rgba(255, 255, 255, 0.03) !important; 
            color: #94a3b8 !important; 
            border-radius: 1.25rem !important; 
            font-weight: 800 !important; 
            text-transform: uppercase !important; 
            font-size: 0.75rem !important; 
            letter-spacing: 0.15em !important;
            padding: 1rem 2rem !important;
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
        }
        .swal2-timer-progress-bar {
            background: #6366f1 !important;
        }

        /* Custom Premium Dark Pagination Style */
        nav[role="navigation"] {
            background: transparent !important;
        }
        
        /* Targets the button container span */
        .relative.z-0.inline-flex,
        nav .relative.z-0 {
            display: inline-flex !important;
            border-radius: 12px !important;
            background: rgba(30, 41, 59, 0.5) !important;
            backdrop-filter: blur(10px) !important;
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
            padding: 4px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        }

        /* Direct targets to wipe out white and light gray backgrounds from pagination buttons */
        nav[role="navigation"] a, 
        nav[role="navigation"] span, 
        nav[role="navigation"] button,
        .relative.z-0.inline-flex a,
        .relative.z-0.inline-flex span {
            background-color: transparent !important;
            background: transparent !important;
            border-color: rgba(255, 255, 255, 0.05) !important;
            border-width: 1px !important;
            color: #94a3b8 !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            min-width: 36px !important;
            height: 36px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 2px !important;
            transition: all 0.2s ease !important;
            box-shadow: none !important;
        }

        /* Active page indicator nested structure override */
        nav[role="navigation"] span[aria-current="page"] span,
        nav[role="navigation"] span[aria-current="page"],
        .relative.z-0.inline-flex span[aria-current="page"] span,
        .relative.z-0.inline-flex span[aria-current="page"] {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
            background-color: #6366f1 !important;
            color: #fff !important;
            box-shadow: 0 4px 12px -3px rgba(79, 70, 229, 0.5) !important;
            border-radius: 8px !important;
            border: none !important;
        }

        nav[role="navigation"] a:hover,
        .relative.z-0.inline-flex a:hover {
            background: rgba(255, 255, 255, 0.05) !important;
            color: #fff !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        /* Mobile Pagination Styling */
        nav[role="navigation"] .flex.justify-between.flex-1 {
            background: rgba(30, 41, 59, 0.5) !important;
            backdrop-filter: blur(10px) !important;
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
            border-radius: 12px !important;
            padding: 8px 12px !important;
        }

        nav[role="navigation"] p {
            color: #64748b !important;
            font-size: 0.85rem !important;
            font-weight: 500 !important;
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden">
    <div id="overlay" class="content-overlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar flex flex-col shrink-0">
        <div class="p-8 flex items-center justify-between brand-container">
            <div class="flex items-center gap-3">
                <div class="relative w-12 h-12 bg-[#1d3557] rounded-2xl flex items-center justify-center border border-white/10 shadow-inner shrink-0">
                    <div class="w-7 h-7 relative">
                        <div class="absolute inset-0 border-[3px] border-[#e63946] rounded-full"></div>
                        <div class="absolute inset-0 border-[3px] border-[#2a9d8f] rounded-full rotate-45 border-t-transparent border-r-transparent"></div>
                        <div class="absolute inset-0 flex items-center justify-center font-black text-white text-[12px]">A</div>
                    </div>
                </div>
                <span class="font-extrabold text-xl tracking-tight text-white logo-text">Aori <span class="text-indigo-500">System</span></span>
            </div>
            <button onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <nav id="sidebarNav" class="flex-1 px-4 space-y-1 overflow-y-auto custom-scroll pb-20">
            @if(Auth::user()->hasPermission('dashboard_view'))
            <a href="{{ url('/') }}" class="sidebar-item {{ Request::is('/') ? 'active' : '' }} flex items-center gap-3 p-4 rounded-xl relative group">
                <i data-lucide="layout-dashboard" class="w-6 h-6 shrink-0"></i> <span class="sidebar-item-text">Dashboard</span>
                <div class="sidebar-popover">Dashboard</div>
            </a>
            @endif

            @php
                $isMasterActive = Request::is('master*');
                $isProductionActive = Request::is('production/templates*') || Request::is('production/work-orders*') || Request::is('production/scheduling*');
                $isShopFloorActive = Request::is('shop-floor*') || Request::is('production/reports*');
                $isTransaksiActive = Request::is('transactions/*');
                $isPurchasingActive = Request::is('orders/*');
                $isLogisticsActive = Request::is('logistics*');
                $isSecurityActive = Request::is('security*') || Request::is('roles*') || Request::is('accounts*');

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

                $hasProductionPermission = Auth::user()->hasPermission('production_template_view') || 
                                         Auth::user()->hasPermission('production_wo_view') || 
                                         Auth::user()->hasPermission('production_scheduling_view');

                $hasPurchasingPermission = Auth::user()->hasPermission('order_request_view') || 
                                         Auth::user()->hasPermission('order_approval_view') || 
                                         Auth::user()->hasPermission('order_po_view') || 
                                         Auth::user()->hasPermission('order_receive_view');

                $hasLogisticsPermission = Auth::user()->hasPermission('logistics_packing_view') || 
                                        Auth::user()->hasPermission('logistics_delivery_view') ||
                                        Auth::user()->hasPermission('logistics_tracking_view');

                $hasSecurityPermission = Auth::user()->hasPermission('security_role_view') || 
                                       Auth::user()->hasPermission('security_account_view');
            @endphp

            @if($hasMasterPermission)
            <div class="sidebar-dropdown-wrapper mb-2">
                <button type="button" onclick="toggleSidebarDropdown('master')" class="w-full flex items-center justify-between p-4 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/30 transition-all font-bold text-xs uppercase tracking-[0.2em] text-left {{ $isMasterActive ? 'text-indigo-400 bg-indigo-500/5' : '' }}">
                    <span class="flex items-center gap-3 text-left">
                        <i data-lucide="database" class="w-5 h-5 shrink-0 {{ $isMasterActive ? 'text-indigo-400' : 'text-slate-500' }}"></i> <span>Master Data</span>
                    </span>
                    <i data-lucide="chevron-down" id="chevron-master" class="w-4 h-4 text-slate-500 transition-transform duration-300 shrink-0 {{ $isMasterActive ? 'rotate-180' : '' }}"></i>
                </button>
                <div id="dropdown-master" class="pl-2 mt-1 border-l border-white/5 ml-4 grid transition-[grid-template-rows] duration-300 ease-in-out {{ $isMasterActive ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]' }}">
                    <div class="overflow-hidden space-y-1">
                        <div class="collapsed-popover-header hidden px-4 py-2.5 text-white font-black text-[10px] uppercase tracking-[0.25em] border-b border-white/5 mb-1.5 bg-indigo-600/10 rounded-t-xl text-left">
                            Master Data
                        </div>
                        @if(Auth::user()->hasPermission('master_item_view'))
                        <a href="{{ route('items.index') }}" class="sidebar-item {{ Request::is('master/items*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="package" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Item</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('master_category_view'))
                        <a href="{{ route('categories.index') }}" class="sidebar-item {{ Request::is('master/categories*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="tag" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Kategori</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('master_type_view'))
                        <a href="{{ route('types.index') }}" class="sidebar-item {{ Request::is('master/types*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="box" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Tipe Item</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('master_unit_view'))
                        <a href="{{ route('units.index') }}" class="sidebar-item {{ Request::is('master/units*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="ruler" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Satuan</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('master_manufacturer_view'))
                        <a href="{{ route('manufacturers.index') }}" class="sidebar-item {{ Request::is('master/manufacturers*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="factory" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Manufaktur</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('master_supplier_view'))
                        <a href="{{ route('suppliers.index') }}" class="sidebar-item {{ Request::is('master/suppliers*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="truck" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Supplier</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('master_customer_view'))
                        <a href="{{ route('customers.index') }}" class="sidebar-item {{ Request::is('master/customers*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="user-circle" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Customer</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('master_machine_category_view'))
                        <a href="{{ route('machine_categories.index') }}" class="sidebar-item {{ Request::is('master/machine-categories*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="layers" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Kategori Mesin</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('master_machine_view'))
                        <a href="{{ route('machines.index') }}" class="sidebar-item {{ Request::is('master/machines*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="cpu" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Mesin</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('master_warehouse_view'))
                        <a href="{{ route('warehouses.index') }}" class="sidebar-item {{ Request::is('master/warehouses*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="home" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Gudang</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('master_priority_view'))
                        <a href="{{ route('priorities.index') }}" class="sidebar-item {{ Request::is('master/priorities*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="list-ordered" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Prioritas</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('master_substitution_view'))
                        <a href="{{ route('substitutions.index') }}" class="sidebar-item {{ Request::is('master/substitutions*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="git-compare" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Substitusi & Capability</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('master_price_list_view'))
                        <a href="{{ route('price_lists.index') }}" class="sidebar-item {{ Request::is('master/price-lists*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="dollar-sign" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Daftar Harga</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @if($hasProductionPermission)
            <div class="sidebar-dropdown-wrapper mb-2">
                <button type="button" onclick="toggleSidebarDropdown('production')" class="w-full flex items-center justify-between p-4 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/30 transition-all font-bold text-xs uppercase tracking-[0.2em] text-left {{ $isProductionActive ? 'text-indigo-400 bg-indigo-500/5' : '' }}">
                    <span class="flex items-center gap-3 text-left">
                        <i data-lucide="cog" class="w-5 h-5 shrink-0 {{ $isProductionActive ? 'text-indigo-400' : 'text-slate-500' }}"></i> <span>Production</span>
                    </span>
                    <i data-lucide="chevron-down" id="chevron-production" class="w-4 h-4 text-slate-500 transition-transform duration-300 shrink-0 {{ $isProductionActive ? 'rotate-180' : '' }}"></i>
                </button>
                <div id="dropdown-production" class="pl-2 mt-1 border-l border-white/5 ml-4 grid transition-[grid-template-rows] duration-300 ease-in-out {{ $isProductionActive ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]' }}">
                    <div class="overflow-hidden space-y-1">
                        <div class="collapsed-popover-header hidden px-4 py-2.5 text-white font-black text-[10px] uppercase tracking-[0.25em] border-b border-white/5 mb-1.5 bg-indigo-600/10 rounded-t-xl text-left">
                            Production
                        </div>
                        @if(Auth::user()->hasPermission('production_template_view'))
                        <a href="{{ route('production.templates.index') }}" class="sidebar-item {{ Request::is('production/templates*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="scroll-text" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Master Template</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('production_wo_view'))
                        <a href="{{ route('production.work_orders.index') }}" class="sidebar-item {{ Request::is('production/work-orders*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="cog" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Work Orders</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('production_scheduling_view'))
                        <a href="{{ route('production.scheduling.index') }}" class="sidebar-item {{ Request::is('production/scheduling*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="calendar-range" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Scheduling Production</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="sidebar-dropdown-wrapper mb-2">
                <button type="button" onclick="toggleSidebarDropdown('shop-floor')" class="w-full flex items-center justify-between p-4 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/30 transition-all font-bold text-xs uppercase tracking-[0.2em] text-left {{ $isShopFloorActive ? 'text-indigo-400 bg-indigo-500/5' : '' }}">
                    <span class="flex items-center gap-3 text-left">
                        <i data-lucide="monitor" class="w-5 h-5 shrink-0 {{ $isShopFloorActive ? 'text-indigo-400' : 'text-slate-500' }}"></i> <span>Shop Floor & Laporan</span>
                    </span>
                    <i data-lucide="chevron-down" id="chevron-shop-floor" class="w-4 h-4 text-slate-500 transition-transform duration-300 shrink-0 {{ $isShopFloorActive ? 'rotate-180' : '' }}"></i>
                </button>
                <div id="dropdown-shop-floor" class="pl-2 mt-1 border-l border-white/5 ml-4 grid transition-[grid-template-rows] duration-300 ease-in-out {{ $isShopFloorActive ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]' }}">
                    <div class="overflow-hidden space-y-1">
                        <div class="collapsed-popover-header hidden px-4 py-2.5 text-white font-black text-[10px] uppercase tracking-[0.25em] border-b border-white/5 mb-1.5 bg-indigo-600/10 rounded-t-xl text-left">
                            Shop Floor & Laporan
                        </div>
                        <a href="{{ route('shop_floor.index') }}" class="sidebar-item {{ Request::is('shop-floor*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="monitor" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Shop Floor Dashboard</span>
                        </a>
                        <a href="{{ route('production.reports.lhp') }}" class="sidebar-item {{ Request::is('production/reports/lhp*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="clipboard" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Laporan LHP</span>
                        </a>
                        <a href="{{ route('production.reports.handover') }}" class="sidebar-item {{ Request::is('production/reports/handover*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="send" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Serah Terima (NPB/PHP)</span>
                        </a>
                    </div>
                </div>
            </div>
            @endif

            @if(Auth::user()->hasPermission('inventory_view') || Auth::user()->hasPermission('stock_opname_view') || Auth::user()->hasPermission('stock_opname_approval_view') || Auth::user()->hasPermission('stock_mutation_view') || Auth::user()->hasPermission('stock_mutation_approval_view') || Auth::user()->hasPermission('stock_card_view'))
            <div class="sidebar-dropdown-wrapper mb-2">
                <button type="button" onclick="toggleSidebarDropdown('transaksi')" class="w-full flex items-center justify-between p-4 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/30 transition-all font-bold text-xs uppercase tracking-[0.2em] text-left {{ $isTransaksiActive ? 'text-indigo-400 bg-indigo-500/5' : '' }}">
                    <span class="flex items-center gap-3 text-left">
                        <i data-lucide="arrow-left-right" class="w-5 h-5 shrink-0 {{ $isTransaksiActive ? 'text-indigo-400' : 'text-slate-500' }}"></i> <span>Transaksi</span>
                    </span>
                    <i data-lucide="chevron-down" id="chevron-transaksi" class="w-4 h-4 text-slate-500 transition-transform duration-300 shrink-0 {{ $isTransaksiActive ? 'rotate-180' : '' }}"></i>
                </button>
                <div id="dropdown-transaksi" class="pl-2 mt-1 border-l border-white/5 ml-4 grid transition-[grid-template-rows] duration-300 ease-in-out {{ $isTransaksiActive ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]' }}">
                    <div class="overflow-hidden space-y-1">
                        <div class="collapsed-popover-header hidden px-4 py-2.5 text-white font-black text-[10px] uppercase tracking-[0.25em] border-b border-white/5 mb-1.5 bg-indigo-600/10 rounded-t-xl text-left">
                            Transaksi
                        </div>
                        @if(Auth::user()->hasPermission('inventory_view'))
                        <a href="{{ route('inventory.index') }}" class="sidebar-item {{ Request::is('transactions/inventory*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="arrow-left-right" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Inventory</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('stock_card_view'))
                        <a href="{{ route('stock_card.index') }}" class="sidebar-item {{ Request::is('transactions/stock-card*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="scroll-text" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Kartu Stock</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('stock_mutation_view'))
                        <a href="{{ route('mutations.request.index') }}" class="sidebar-item {{ Request::is('transactions/mutations/request*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="file-plus" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Request Mutasi</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('stock_mutation_approval_view'))
                        <a href="{{ route('mutations.approval.index') }}" class="sidebar-item {{ Request::is('transactions/mutations/approval*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="check-square" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Approval Mutasi</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('stock_mutation_view'))
                        <a href="{{ route('mutations.index') }}" class="sidebar-item {{ Request::is('transactions/mutations/index*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="truck" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Mutasi Gudang</span>
                        </a>
                        <a href="{{ route('mutations.rekap.index') }}" class="sidebar-item {{ Request::is('transactions/mutations/rekap*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="bar-chart-3" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Rekap PM & Realisasi</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('stock_opname_view'))
                        <a href="{{ route('opname.index') }}" class="sidebar-item {{ Request::is('transactions/stock-opname') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="clipboard-list" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Stock Opname</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('stock_opname_approval_view'))
                        <a href="{{ route('opname.approval.index') }}" class="sidebar-item {{ Request::is('transactions/stock-opname/approval*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Approval Stock Opname</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @if($hasPurchasingPermission)
            <div class="sidebar-dropdown-wrapper mb-2">
                <button type="button" onclick="toggleSidebarDropdown('purchasing')" class="w-full flex items-center justify-between p-4 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/30 transition-all font-bold text-xs uppercase tracking-[0.2em] text-left {{ $isPurchasingActive ? 'text-indigo-400 bg-indigo-500/5' : '' }}">
                    <span class="flex items-center gap-3 text-left">
                        <i data-lucide="shopping-cart" class="w-5 h-5 shrink-0 {{ $isPurchasingActive ? 'text-indigo-400' : 'text-slate-500' }}"></i> <span>Order & Purchasing</span>
                    </span>
                    <i data-lucide="chevron-down" id="chevron-purchasing" class="w-4 h-4 text-slate-500 transition-transform duration-300 shrink-0 {{ $isPurchasingActive ? 'rotate-180' : '' }}"></i>
                </button>
                <div id="dropdown-purchasing" class="pl-2 mt-1 border-l border-white/5 ml-4 grid transition-[grid-template-rows] duration-300 ease-in-out {{ $isPurchasingActive ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]' }}">
                    <div class="overflow-hidden space-y-1">
                        <div class="collapsed-popover-header hidden px-4 py-2.5 text-white font-black text-[10px] uppercase tracking-[0.25em] border-b border-white/5 mb-1.5 bg-indigo-600/10 rounded-t-xl text-left">
                            Order & Purchasing
                        </div>
                        @if(Auth::user()->hasPermission('order_request_view'))
                        <a href="{{ route('orders.requests.index') }}" class="sidebar-item {{ Request::is('orders/requests*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="file-text" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Request Items</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('order_approval_view'))
                        <a href="{{ route('orders.approvals.index') }}" class="sidebar-item {{ Request::is('orders/approvals*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="check-square" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Approval Request</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('order_po_view'))
                        <a href="{{ route('orders.po.index') }}" class="sidebar-item {{ Request::is('orders/purchase-orders*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="shopping-cart" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Create PO</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('order_receive_view'))
                        <a href="{{ route('orders.receives.index') }}" class="sidebar-item {{ Request::is('orders/receives*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="package-check" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Receive Material</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @if($hasLogisticsPermission)
            <div class="sidebar-dropdown-wrapper mb-2">
                <button type="button" onclick="toggleSidebarDropdown('logistics')" class="w-full flex items-center justify-between p-4 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/30 transition-all font-bold text-xs uppercase tracking-[0.2em] text-left {{ $isLogisticsActive ? 'text-indigo-400 bg-indigo-500/5' : '' }}">
                    <span class="flex items-center gap-3 text-left">
                        <i data-lucide="truck" class="w-5 h-5 shrink-0 {{ $isLogisticsActive ? 'text-indigo-400' : 'text-slate-500' }}"></i> <span>Logistics & Delivery</span>
                    </span>
                    <i data-lucide="chevron-down" id="chevron-logistics" class="w-4 h-4 text-slate-500 transition-transform duration-300 shrink-0 {{ $isLogisticsActive ? 'rotate-180' : '' }}"></i>
                </button>
                <div id="dropdown-logistics" class="pl-2 mt-1 border-l border-white/5 ml-4 grid transition-[grid-template-rows] duration-300 ease-in-out {{ $isLogisticsActive ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]' }}">
                    <div class="overflow-hidden space-y-1">
                        <div class="collapsed-popover-header hidden px-4 py-2.5 text-white font-black text-[10px] uppercase tracking-[0.25em] border-b border-white/5 mb-1.5 bg-indigo-600/10 rounded-t-xl text-left">
                            Logistics & Delivery
                        </div>
                        @if(Auth::user()->hasPermission('logistics_packing_view'))
                        <a href="{{ route('logistics.packing.index') }}" class="sidebar-item {{ Request::is('logistics/packing*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="box" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Packing List</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('logistics_delivery_view'))
                        <a href="{{ route('logistics.delivery.index') }}" class="sidebar-item {{ Request::is('logistics/delivery*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="truck" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Delivery Batch</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('logistics_tracking_view'))
                        <a href="{{ route('logistics.tracking.index') }}" class="sidebar-item {{ Request::is('logistics/tracking*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="map-pin" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Tracking Delivery</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @if($hasSecurityPermission)
            <div class="sidebar-dropdown-wrapper mb-2">
                <button type="button" onclick="toggleSidebarDropdown('security')" class="w-full flex items-center justify-between p-4 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/30 transition-all font-bold text-xs uppercase tracking-[0.2em] text-left {{ $isSecurityActive ? 'text-indigo-400 bg-indigo-500/5' : '' }}">
                    <span class="flex items-center gap-3 text-left">
                        <i data-lucide="shield-check" class="w-5 h-5 shrink-0 {{ $isSecurityActive ? 'text-indigo-400' : 'text-slate-500' }}"></i> <span>Security & Access</span>
                    </span>
                    <i data-lucide="chevron-down" id="chevron-security" class="w-4 h-4 text-slate-500 transition-transform duration-300 shrink-0 {{ $isSecurityActive ? 'rotate-180' : '' }}"></i>
                </button>
                <div id="dropdown-security" class="pl-2 mt-1 border-l border-white/5 ml-4 grid transition-[grid-template-rows] duration-300 ease-in-out {{ $isSecurityActive ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]' }}">
                    <div class="overflow-hidden space-y-1">
                        <div class="collapsed-popover-header hidden px-4 py-2.5 text-white font-black text-[10px] uppercase tracking-[0.25em] border-b border-white/5 mb-1.5 bg-indigo-600/10 rounded-t-xl text-left">
                            Security & Access
                        </div>
                        @if(Auth::user()->hasPermission('security_role_view'))
                        <a href="{{ route('roles.index') }}" class="sidebar-item {{ Request::is('security/roles*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="shield-check" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Access Roles</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('security_account_view'))
                        <a href="{{ route('accounts.index') }}" class="sidebar-item {{ Request::is('security/accounts*') ? 'active' : '' }} flex items-center gap-3 py-3 px-4 rounded-xl text-sm">
                            <i data-lucide="users" class="w-4 h-4 shrink-0"></i> <span class="sidebar-item-text">Account Management</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </nav>

        <div class="p-6 border-t border-white/5 bg-slate-900/20 sidebar-profile-container">
            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/50 sidebar-profile-card">
                <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center font-black text-xs uppercase text-white shadow-lg shadow-indigo-500/10 shrink-0">{{ substr(Auth::user()->name, 0, 1) }}</div>
                <div class="overflow-hidden profile-info">
                    <p class="text-white text-xs font-black truncate">{{ Auth::user()->name }}</p>
                    <p class="text-slate-500 text-[12px] font-bold uppercase tracking-widest">{{ Auth::user()->role->name ?? 'User' }}</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="mt-4">
                @csrf
                <button type="submit" class="w-full text-left text-[12px] font-black text-rose-500 hover:text-rose-400 uppercase tracking-[0.2em] px-4 py-2 flex items-center gap-3 transition-colors justify-start collapsed-logout-btn relative group">
                    <i data-lucide="log-out" class="w-4 h-4 shrink-0"></i> <span class="sign-out-text">Sign Out</span>
                    <div class="sidebar-popover bg-rose-950/80 border border-rose-500/20 text-rose-400">Sign Out</div>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col bg-[#0f172a] h-screen overflow-hidden relative">
        <header class="p-6 lg:px-10 flex justify-between items-center bg-[#0f172a]/80 backdrop-blur-xl sticky top-0 z-10 border-b border-white/5">
            <div class="flex items-center gap-4">
                <button onclick="handleSidebarToggle()" class="p-2.5 bg-slate-800/40 border border-white/5 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition-all flex items-center justify-center shrink-0">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div>
                    <h2 class="text-xl lg:text-2xl font-black text-white tracking-tight">{{ $title ?? 'Dashboard' }}</h2>
                    <p class="hidden sm:block text-slate-500 text-[12px] font-bold uppercase tracking-[0.2em] mt-1">Aori Manufacture</p>
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

            @if($errors->any())
            <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl text-rose-500 text-xs font-bold">
                <div class="flex items-center gap-3 mb-2">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i> Terdapat kesalahan pengisian form:
                </div>
                <ul class="list-disc pl-8 space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
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

        // SweetAlert2 for Flash Messages
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'BERHASIL',
                text: "{{ session('success') }}",
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'ERROR',
                text: "{{ session('error') }}",
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        @endif

        // Global Confirm Function
        window.confirmAction = function(message, callback) {
            Swal.fire({
                title: 'KONFIRMASI',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'YA, LANJUTKAN',
                cancelButtonText: 'BATAL',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    callback();
                }
            });
        };

        // Override native window.alert
        window.alert = function(message) {
            Swal.fire({
                title: 'NOTIFIKASI',
                text: message,
                icon: 'info',
                confirmButtonText: 'MENGERTI'
            });
        };

        // Global Interceptor for native confirm in onsubmit
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const onsubmit = form.getAttribute('onsubmit');
            
            if (onsubmit && onsubmit.includes('confirm(')) {
                e.preventDefault();
                
                // Extract message from confirm('...') or confirm("...")
                const match = onsubmit.match(/confirm\(['"](.+)['"]\)/);
                const message = match ? match[1] : 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
                
                Swal.fire({
                    title: 'KONFIRMASI',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'YA, LANJUTKAN',
                    cancelButtonText: 'BATAL',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Temporarily remove onsubmit to avoid infinite loop
                        form.removeAttribute('onsubmit');
                        form.submit();
                    }
                });
            }
        });

        function toggleSidebarDropdown(id) {
            const sidebar = document.getElementById('sidebar');
            // If sidebar is collapsed on desktop, don't toggle dropdown internally
            if (sidebar && sidebar.classList.contains('collapsed') && window.innerWidth >= 1024) {
                sidebar.classList.remove('collapsed');
                localStorage.setItem('sidebar-collapsed', 'false');
            }
            
            const dropdown = document.getElementById('dropdown-' + id);
            const chevron = document.getElementById('chevron-' + id);
            if (dropdown) {
                if (dropdown.classList.contains('grid-rows-[0fr]')) {
                    dropdown.classList.remove('grid-rows-[0fr]');
                    dropdown.classList.add('grid-rows-[1fr]');
                    if (chevron) chevron.classList.add('rotate-180');
                } else {
                    dropdown.classList.remove('grid-rows-[1fr]');
                    dropdown.classList.add('grid-rows-[0fr]');
                    if (chevron) chevron.classList.remove('rotate-180');
                }
            }
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('overlay').classList.toggle('open');
        }

        function handleSidebarToggle() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth < 1024) {
                // Mobile behavior: drawer toggle
                sidebar.classList.toggle('open');
                document.getElementById('overlay').classList.toggle('open');
            } else {
                // Desktop behavior: collapse tray
                sidebar.classList.toggle('collapsed');
                
                // Store collapsed preference in localStorage
                if (sidebar.classList.contains('collapsed')) {
                    localStorage.setItem('sidebar-collapsed', 'true');
                } else {
                    localStorage.setItem('sidebar-collapsed', 'false');
                }
            }
        }

        // Restore sidebar preference immediately on page load to prevent flicker
        (function() {
            if (window.innerWidth >= 1024) {
                const collapsed = localStorage.getItem('sidebar-collapsed');
                const sidebar = document.getElementById('sidebar');
                if (collapsed === 'true' && sidebar) {
                    sidebar.classList.add('collapsed');
                }
            }
        })();
    </script>
</body>
</html>
