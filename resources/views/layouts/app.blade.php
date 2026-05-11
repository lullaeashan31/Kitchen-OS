<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Kitchen Management') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: {
                            deep: '#06101E',
                            primary: '#0A1628',
                            mid: '#1C2E4A',
                        },
                        brass: {
                            DEFAULT: '#B5975A',
                            hover: '#D4B06A',
                        },
                        ivory: '#F2EDE6',
                        primary: '#B5975A',
                        secondary: '#1C2E4A',
                        success: '#22c55e',
                        warning: '#eab308',
                        danger: '#ef4444',
                    },
                    fontFamily: {
                        heading: ['EB Garamond', 'serif'],
                        sans: ['DM Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!-- Tom Select CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Traverse Brand System - STRICT ADHERENCE */
        :root {
            --bg-primary: #06101E;
            --bg-card: #0A1628;
            --border-subtle: rgba(242, 237, 230, 0.08);
            --border-header: rgba(242, 237, 230, 0.1);
            --text-primary: #F2EDE6;
            --text-muted: rgba(242, 237, 230, 0.6);
            --accent: #B5975A;
            --accent-hover: #D4B06A;
            --radius: 4px;
            --padding: 24px;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            margin: 0;
            -webkit-font-smoothing: antialiased;
            color-scheme: dark;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'EB Garamond', serif;
            color: var(--text-primary);
            margin-top: 0;
            line-height: 1.2;
        }

        .app-container {
            display: flex;
            min-height: 100vh;
            background-color: var(--bg-primary);
        }

        .sidebar {
            width: 280px;
            background-color: var(--bg-card);
            border-right: 1px solid var(--border-subtle);
            position: fixed;
            height: 100vh;
            top: 0;
            left: 0;
            overflow-y: auto;
            padding: var(--padding);
            z-index: 40;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-content {
            margin-left: 280px;
            flex: 1;
            min-height: 100vh;
            padding: var(--padding);
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0 0 var(--padding) 0 !important;
            padding: 16px 0;
            border-bottom: 1px solid var(--border-subtle);
            position: sticky;
            top: 0;
            z-index: 30;
            background-color: var(--bg-primary);
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: var(--text-primary);
            transition: all 0.2s;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 700;
            text-decoration: none;
            border-radius: var(--radius);
            margin-bottom: 4px;
            opacity: 0.5;
        }

        .nav-link:hover {
            background-color: rgba(181, 151, 90, 0.05);
            opacity: 1;
            color: var(--accent);
        }

        .nav-link.active {
            background-color: rgba(181, 151, 90, 0.1);
            color: var(--accent);
            opacity: 1;
        }

        .nav-icon {
            margin-right: 12px;
            width: 1rem;
            height: 1rem;
        }

        /* GLOBAL OVERRIDES - AGGRESSIVE PURGING OF NON-BRAND COLORS */
        .card, 
        .bg-white,
        .bg-gray-50, .bg-gray-100, .bg-gray-200, .bg-gray-300, .bg-gray-400, .bg-gray-500,
        [class*="bg-white"], 
        [class*="bg-gray-"], 
        [class*="bg-slate-"], 
        [class*="bg-zinc-"], 
        [class*="bg-neutral-"],
        .modal-content, .dropdown-menu, .popover, .tooltip-inner { 
            background-color: var(--bg-card) !important; 
            border: 1px solid var(--border-subtle) !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5) !important;
            color: var(--text-primary) !important;
        }

        .card-header, .card-footer, .modal-header, .modal-footer {
            background-color: rgba(242, 237, 230, 0.02) !important;
            border-color: var(--border-subtle) !important;
            color: var(--text-primary) !important;
        }
        
        /* HOVER OVERRIDES */
        [class*="hover:bg-white"]:hover, 
        [class*="hover:bg-gray-"]:hover, 
        [class*="hover:bg-slate-"]:hover, 
        [class*="hover:bg-zinc-"]:hover, 
        [class*="hover:bg-neutral-"]:hover,
        .dropdown-item:hover {
            background-color: rgba(181, 151, 90, 0.1) !important;
            color: var(--accent) !important;
        }

        /* TEXT COLOR OVERRIDES */
        .text-black, .text-gray-900, .text-gray-800, .text-gray-700, .text-gray-600,
        [class*="text-gray-"], 
        [class*="text-slate-"], 
        [class*="text-zinc-"], 
        [class*="text-neutral-"],
        [class*="text-black"] {
            color: var(--text-primary) !important;
        }

        .text-muted, .text-gray-500, .text-gray-400 {
            color: var(--text-muted) !important;
        }

        .table-container {
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius);
            overflow: hidden;
            background-color: var(--bg-card) !important;
        }

        .table { 
            width: 100%;
            border-collapse: collapse;
            background-color: var(--bg-card) !important;
        }
        .table thead tr { 
            background-color: rgba(242, 237, 230, 0.02) !important; 
            border-bottom: 1px solid var(--border-subtle) !important;
        }
        .table th { 
            color: var(--accent) !important; 
            font-family: 'DM Sans', sans-serif !important; 
            text-transform: uppercase !important; 
            letter-spacing: 0.1em !important; 
            font-size: 0.65rem !important;
            font-weight: 900 !important;
            padding: 16px 24px !important;
            text-align: left;
        }
        .table td { 
            padding: 16px 24px !important;
            border-bottom: 1px solid var(--border-subtle) !important;
            color: var(--text-primary) !important;
            font-size: 0.8rem;
        }
        .table tr:last-child td { border-bottom: none !important; }

        /* FORM CONTROLS */
        .form-control, input, select, textarea { 
            background-color: var(--bg-primary) !important; 
            border: 1px solid var(--border-subtle) !important; 
            color: var(--text-primary) !important; 
            border-radius: var(--radius) !important;
            padding: 14px 16px !important;
            font-size: 0.85rem !important;
        }
        .form-control:focus, input:focus, select:focus, textarea:focus { 
            border-color: var(--accent) !important; 
            box-shadow: 0 0 0 2px rgba(181, 151, 90, 0.1) !important;
            outline: none !important;
        }

        /* Fix native browser picker icons (date/time) to be visible on dark background */
        ::-webkit-calendar-picker-indicator {
            filter: invert(1) opacity(0.5);
            cursor: pointer;
        }
        ::-webkit-calendar-picker-indicator:hover {
            filter: invert(1) opacity(0.9);
        }

        .form-label {
            display: block;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        /* BUTTONS */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            cursor: pointer;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            border-radius: var(--radius);
        }

        .btn-primary, button[type="submit"]:not(.nav-link button):not(.btn-danger):not(.btn-icon-action) { 
            background-color: var(--accent) !important; 
            color: var(--bg-primary) !important; 
            padding: 14px 28px !important;
            border: none !important;
        }
        .btn-primary:hover, button[type="submit"]:not(.nav-link button):not(.btn-danger):not(.btn-icon-action):hover { background-color: var(--accent-hover) !important; }

        .btn-danger {
            background-color: #fef2f2 !important;
            color: #dc2626 !important;
            border: 1px solid #fecaca !important;
            padding: 0 !important;
        }
        .btn-danger:hover { background-color: #fee2e2 !important; color: #b91c1c !important; }

        .btn-icon-action {
            padding: 0 !important;
            background-color: transparent !important;
            border: none !important;
        }

        .btn-secondary {
            background-color: var(--bg-card) !important;
            border: 1px solid var(--border-subtle) !important;
            color: var(--text-primary) !important;
            padding: 12px 24px !important;
        }
        .btn-secondary:hover { border-color: var(--accent) !important; color: var(--accent) !important; }

        /* BADGES */
        .badge {
            display: inline-flex;
            padding: 4px 10px;
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 2px;
        }
        .badge-success { background: rgba(34, 197, 94, 0.1) !important; color: #22c55e !important; border: 1px solid rgba(34, 197, 94, 0.2) !important; }
        .badge-warning { background: rgba(181, 151, 90, 0.1) !important; color: var(--accent) !important; border: 1px solid rgba(181, 151, 90, 0.2) !important; }
        .badge-danger { background: rgba(239, 68, 68, 0.1) !important; color: #ef4444 !important; border: 1px solid rgba(239, 68, 68, 0.2) !important; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg-primary); }
        ::-webkit-scrollbar-thumb { background: var(--bg-card); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--accent); }

        /* TOM SELECT — force dropdown above sticky/fixed elements */
        .ts-wrapper .ts-dropdown,
        .ts-dropdown {
            z-index: 9999 !important;
            position: absolute !important;
            background-color: var(--bg-card) !important;
            border: 1px solid var(--border-subtle) !important;
            border-top: none !important;
            border-radius: 0 0 var(--radius) var(--radius) !important;
            box-shadow: 0 16px 40px rgba(0,0,0,0.6) !important;
            color: var(--text-primary) !important;
        }
        .ts-dropdown .ts-dropdown-content { max-height: 220px; overflow-y: auto; }
        .ts-dropdown .option { 
            padding: 10px 14px; 
            color: var(--text-primary) !important; 
            font-size: 0.82rem;
            cursor: pointer;
        }
        .ts-dropdown .option:hover,
        .ts-dropdown .option.active { 
            background: rgba(181,151,90,0.12) !important; 
            color: var(--accent) !important; 
        }
        .ts-dropdown .option.selected { 
            background: rgba(181,151,90,0.08) !important; 
            color: var(--accent) !important; 
        }
        .ts-wrapper .ts-control {
            background-color: var(--bg-primary) !important;
            border: 1px solid var(--border-subtle) !important;
            color: var(--text-primary) !important;
            border-radius: var(--radius) !important;
            min-height: 42px !important;
            padding: 8px 12px !important;
            box-shadow: none !important;
        }
        .ts-wrapper.focus .ts-control { border-color: var(--accent) !important; }
        .ts-control input { color: var(--text-primary) !important; background: transparent !important; }
        .ts-control .item { color: var(--accent) !important; font-weight: 700; }
        .ts-control .placeholder { color: var(--text-muted) !important; }

        /* ALERTS */
        .alert {
            padding: 16px 24px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .alert-success { background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); color: #22c55e; }
        .alert-error { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; }

        /* MOBILE OVERRIDES */
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 16px; }
            .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 35; }
            .sidebar-overlay.active { display: block; }
        }
    </style>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="mb-12 flex items-center gap-4">
                <div class="p-2 bg-accent text-primary rounded-lg">
                    <i data-lucide="chef-hat" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-xl font-heading font-black text-ivory tracking-tight leading-none">KITCHEN OS</h2>
                    <p class="text-[8px] font-black text-accent uppercase tracking-[0.3em] mt-1">Traverse Inc.</p>
                </div>
            </div>

            <nav class="flex-1">
                @if(auth()->user()->isSuperAdmin() && !app()->has('current_kitchen'))
                    <a href="{{ route('superadmin.dashboard') }}"
                        class="nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard" class="nav-icon"></i> SuperAdmin Dashboard
                    </a>
                    <a href="{{ route('superadmin.kitchens.index') }}"
                        class="nav-link {{ request()->routeIs('superadmin.kitchens.*') ? 'active' : '' }}">
                        <i data-lucide="layout" class="nav-icon"></i> Kitchens
                    </a>
                @endif

                @if(app()->has('current_kitchen'))
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i data-lucide="grid" class="nav-icon"></i> Dashboard
                    </a>

                    {{-- Time Clock --}}
                    @if(!auth()->user()->isStaff() || auth()->user()->hasPermissionTo('manage_attendance')) 
                        <div class="mt-6 mb-2 px-4 text-[9px] uppercase text-accent font-black tracking-[0.2em] opacity-40">
                            Time Clock
                        </div>
                        <a href="{{ route('attendance.tablet') }}" class="nav-link">
                            <i data-lucide="clock" class="nav-icon"></i> Tablet Mode
                        </a>
                        @if(!auth()->user()->isStaff())
                            <a href="{{ route('admin.attendance.index') }}" class="nav-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                                <i data-lucide="list" class="nav-icon"></i> Attendance
                            </a>
                        @endif
                    @endif

                    {{-- My Workspace --}}
                    @if(auth()->user()->isStaff())
                        <div class="mt-6 mb-2 px-4 text-[9px] uppercase text-accent font-black tracking-[0.2em] opacity-40">
                            My Workspace
                        </div>
                        <a href="{{ route('employee.shifts.index') }}" class="nav-link {{ request()->routeIs('employee.shifts.*') ? 'active' : '' }}">
                            <i data-lucide="user" class="nav-icon"></i> My Schedule
                        </a>
                        <a href="{{ route('employee.payroll.index') }}" class="nav-link {{ request()->routeIs('employee.payroll.*') ? 'active' : '' }}">
                            <i data-lucide="credit-card" class="nav-icon"></i> My Payslips
                        </a>
                        <a href="{{ route('employee.attendance.index') }}" class="nav-link {{ request()->routeIs('employee.attendance.*') ? 'active' : '' }}">
                            <i data-lucide="history" class="nav-icon"></i> My Attendance
                        </a>
                    @endif

                    {{-- Inventory & Stock --}}
                    @if(auth()->user()->hasPermissionTo('module_inventory') || !auth()->user()->isStaff())
                        <div class="mt-6 mb-2 px-4 text-[9px] uppercase text-accent font-black tracking-[0.2em] opacity-40">
                            Inventory
                        </div>
                        <a href="{{ route('admin.inventory.index') }}" class="nav-link {{ request()->routeIs('admin.inventory.index') ? 'active' : '' }}">
                            <i data-lucide="box" class="nav-icon"></i> Inventory
                        </a>
                        <a href="{{ route('admin.reports.depletion') }}" class="nav-link {{ request()->routeIs('admin.reports.depletion') ? 'active' : '' }}">
                            <i data-lucide="trending-down" class="nav-icon"></i> Depletion Report
                        </a>
                        <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                            <i data-lucide="shopping-bag" class="nav-icon"></i> Purchases
                        </a>
                        @if(!auth()->user()->isStaff())
                            <a href="{{ route('admin.inventory.upload') }}" class="nav-link {{ request()->routeIs('admin.inventory.upload') ? 'active' : '' }}">
                                <i data-lucide="upload-cloud" class="nav-icon"></i> Inventory Upload
                            </a>
                        @endif
                    @endif

                    {{-- Recipe Management --}}
                    @if(auth()->user()->hasPermissionTo('module_recipes') || !auth()->user()->isStaff())
                        <div class="mt-6 mb-2 px-4 text-[9px] uppercase text-accent font-black tracking-[0.2em] opacity-40">
                            Recipes
                        </div>
                        @if(!auth()->user()->isStaff())
                            <a href="{{ route('admin.ingredients.pending') }}" class="nav-link {{ request()->routeIs('admin.ingredients.pending') ? 'active' : '' }} flex justify-between items-center">
                                <span class="flex items-center gap-2">
                                    <i data-lucide="shield-check" class="nav-icon"></i> Pending Ingredients
                                </span>
                                @php $pendingCount = \App\Models\Ingredient::where('status','pending')->count(); @endphp
                                @if($pendingCount > 0)
                                    <span class="bg-red-500 text-white text-[8px] px-2 py-0.5 rounded-full font-black">{{ $pendingCount }}</span>
                                @endif
                            </a>
                        @endif
                        @if(auth()->user()->hasPermissionTo('module_production') || !auth()->user()->isStaff())
                            <a href="{{ route('production.create') }}" class="nav-link {{ request()->routeIs('production.*') ? 'active' : '' }}">
                                <i data-lucide="flame" class="nav-icon"></i> Production
                            </a>
                        @endif
                        <a href="{{ route('recipes.index') }}" class="nav-link {{ request()->routeIs('recipes.*') && !request()->has('is_sub_recipe') ? 'active' : '' }}">
                            <i data-lucide="book" class="nav-icon"></i> Recipes
                        </a>
                        @if(!auth()->user()->isStaff())
                            <a href="{{ route('recipes.index', ['is_sub_recipe' => 1]) }}" class="nav-link {{ request()->query('is_sub_recipe') == 1 ? 'active' : '' }}">
                                <i data-lucide="layers" class="nav-icon"></i> Sub-Recipes
                            </a>
                            <a href="{{ route('recipes.create') }}" class="nav-link {{ request()->routeIs('recipes.create') ? 'active' : '' }}">
                                <i data-lucide="plus-circle" class="nav-icon"></i> Add Recipe
                            </a>
                            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                                <i data-lucide="tag" class="nav-icon"></i> Categories
                            </a>
                            <a href="{{ route('ingredients.index') }}" class="nav-link {{ request()->routeIs('ingredients.*') ? 'active' : '' }}">
                                <i data-lucide="database" class="nav-icon"></i> Ingredients
                            </a>
                        @endif
                    @endif

                    {{-- Administration & HR --}}
                    @if(auth()->user()->hasPermissionTo('module_staff_management') || auth()->user()->hasPermissionTo('module_hr_payroll') || !auth()->user()->isStaff())
                        <div class="mt-6 mb-2 px-4 text-[9px] uppercase text-accent font-black tracking-[0.2em] opacity-40">
                            Admin
                        </div>
                        @if(auth()->user()->hasPermissionTo('module_audit_logs') || !auth()->user()->isStaff())
                            <a href="{{ route('audit_logs.index') }}" class="nav-link {{ request()->routeIs('audit_logs.*') ? 'active' : '' }}">
                                <i data-lucide="shield" class="nav-icon"></i> Audit Logs
                            </a>
                        @endif
                        @if(auth()->user()->hasPermissionTo('module_staff_management') || !auth()->user()->isStaff())
                            <a href="{{ route('admin.staff.index') }}" class="nav-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                                <i data-lucide="users" class="nav-icon"></i> Staff
                            </a>
                            <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                <i data-lucide="briefcase" class="nav-icon"></i> Roles
                            </a>
                            <a href="{{ route('admin.schedule.index') }}" class="nav-link {{ request()->routeIs('admin.schedule.*') ? 'active' : '' }}">
                                <i data-lucide="calendar" class="nav-icon"></i> Schedule
                            </a>
                        @endif
                        @if(auth()->user()->hasPermissionTo('module_hr_payroll') || !auth()->user()->isStaff())
                            <a href="{{ route('admin.payroll.index') }}" class="nav-link {{ request()->routeIs('admin.payroll.*') ? 'active' : '' }}">
                                <i data-lucide="credit-card" class="nav-icon"></i> Payroll
                            </a>
                            <a href="{{ route('admin.leave.index') }}" class="nav-link {{ request()->routeIs('admin.leave.*') ? 'active' : '' }}">
                                <i data-lucide="sun" class="nav-icon"></i> Leaves
                            </a>
                            <a href="{{ route('admin.performance.index') }}" class="nav-link {{ request()->routeIs('admin.performance.*') ? 'active' : '' }}">
                                <i data-lucide="award" class="nav-icon"></i> Performance
                            </a>
                        @endif
                    @endif

                    {{-- Operations (SOP) --}}
                    @if(auth()->user()->hasPermissionTo('module_sops') || !auth()->user()->isStaff())
                        <div class="mt-6 mb-2 px-4 text-[9px] uppercase text-accent font-black tracking-[0.2em] opacity-40">
                            SOPs
                        </div>
                        <a href="{{ route('sop.index') }}" class="nav-link {{ request()->routeIs('sop.*') ? 'active' : '' }}">
                            <i data-lucide="clipboard-list" class="nav-icon"></i> SOPs
                        </a>
                        @if(!auth()->user()->isStaff())
                            <a href="{{ route('admin.sop.index') }}" class="nav-link {{ request()->routeIs('admin.sop.index') ? 'active' : '' }}">
                                <i data-lucide="settings" class="nav-icon"></i> SOP Management
                            </a>
                            <a href="{{ route('admin.shifts.index') }}" class="nav-link {{ request()->routeIs('admin.shifts.*') ? 'active' : '' }}">
                                <i data-lucide="watch" class="nav-icon"></i> Shifts
                            </a>
                            <a href="{{ route('admin.shifts.assignments.index') }}" class="nav-link {{ request()->routeIs('admin.shifts.assignments.*') ? 'active' : '' }}">
                                <i data-lucide="user-plus" class="nav-icon"></i> Assignments
                            </a>
                            <a href="{{ route('admin.sop.reviews.index') }}" class="nav-link {{ request()->routeIs('admin.sop.reviews.*') ? 'active' : '' }}">
                                <i data-lucide="check-square" class="nav-icon"></i> SOP Reviews
                            </a>
                        @endif
                    @endif
                @endif
            </nav>

            <div class="mt-auto pt-8 border-t border-subtle">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-10 h-10 rounded-full bg-accent text-primary flex items-center justify-center font-black text-xs uppercase">
                        {{ substr(auth()->user()->name, 0, 2) }}
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <div class="font-bold text-primary truncate text-xs">{{ auth()->user()->name }}</div>
                        <div class="text-[8px] text-accent uppercase font-black tracking-widest">{{ auth()->user()->role->label() }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary w-full py-3 text-[9px]">
                        <i data-lucide="log-out" class="w-3 h-3"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-header">
                <div class="flex items-center gap-4">
                    <button class="lg:hidden p-2 text-primary" onclick="toggleSidebar()">
                        <i data-lucide="menu"></i>
                    </button>
                    @yield('header')
                </div>
                <div class="flex items-center gap-4">
                    @yield('actions')
                </div>
            </header>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif

            <div class="min-h-0 flex-1">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" style="display: none; position: fixed; inset: 0; background: rgba(6, 16, 30, 0.9); z-index: 1000; align-items: center; justify-content: center; padding: 40px;">
        <div class="bg-navy-primary w-full h-full border border-subtle rounded-lg flex flex-direction-column overflow-hidden">
            <div class="p-6 border-b border-subtle flex justify-between items-center bg-navy-deep">
                <h3 id="modalTitle" class="text-xl font-black text-primary uppercase tracking-tight">Artifact Preview</h3>
                <button onclick="closePreview()" class="text-muted hover:text-primary transition-all">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="flex-1 bg-navy-deep p-8">
                <iframe id="previewFrame" class="w-full h-full border-none rounded-lg bg-white"></iframe>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }

        function openPreview(url, name) {
            let embedUrl = url;
            if (url.includes('/view')) embedUrl = url.replace('/view', '/preview');
            document.getElementById('modalTitle').textContent = name;
            document.getElementById('previewFrame').src = embedUrl;
            document.getElementById('previewModal').style.display = 'flex';
        }

        function closePreview() {
            document.getElementById('previewModal').style.display = 'none';
            document.getElementById('previewFrame').src = '';
        }

        document.addEventListener('keydown', (e) => { if (e.key === "Escape") closePreview(); });
    </script>
    @stack('modals')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    @stack('scripts')
</body>

</html>