<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Traverse Inc.') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brass: { DEFAULT: '#B5975A', hover: '#9A7D47' },
                        navy: { DEFAULT: '#1C2D4F' },
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
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-page:    #F7F6F3;
            --bg-card:    #FFFFFF;
            --bg-sidebar: #FFFFFF;
            --border:     #E8E4DC;
            --border-strong: #D0C9BC;
            --text-primary:  #1C2D4F;
            --text-muted:    #7A7A72;
            --accent:        #B5975A;
            --accent-hover:  #9A7D47;
            --accent-light:  #F5EFE4;
            --success:       #16a34a;
            --danger:        #dc2626;
            --warning-bg:    #FEF9EE;
            --warning-text:  #92660A;
            --radius:        6px;
            --sidebar-width: 260px;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            background-color: var(--bg-page);
            color: var(--text-primary);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem;
            margin: 0;
            -webkit-font-smoothing: antialiased;
            color-scheme: light;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'EB Garamond', serif;
            color: var(--text-primary);
            margin-top: 0;
            line-height: 1.25;
        }

        /* ── LAYOUT ─────────────────────────────── */
        .app-container { display: flex; min-height: 100vh; }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border);
            position: fixed;
            height: 100vh;
            top: 0; left: 0;
            overflow-y: auto;
            padding: 24px 16px;
            z-index: 40;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            min-height: 100vh;
            padding: 24px 32px;
            display: flex;
            flex-direction: column;
        }

        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 30;
            background-color: var(--bg-page);
        }

        /* ── SIDEBAR LOGO ───────────────────────── */
        .sidebar-logo {
            padding: 8px 8px 24px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }
        .sidebar-logo img { height: 32px; width: auto; }

        /* ── NAV ────────────────────────────────── */
        .nav-section-label {
            padding: 16px 12px 6px;
            font-size: 0.625rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--text-muted);
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 9px 12px;
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: var(--radius);
            margin-bottom: 2px;
            transition: all 0.15s;
            gap: 10px;
        }
        .nav-link:hover {
            background: var(--accent-light);
            color: var(--accent);
        }
        .nav-link.active {
            background: var(--accent-light);
            color: var(--accent);
            font-weight: 700;
        }
        .nav-icon { width: 15px; height: 15px; flex-shrink: 0; }

        /* ── CARDS ──────────────────────────────── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        /* ── TABLES ─────────────────────────────── */
        .table-container {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            background: var(--bg-card);
        }
        .table { width: 100%; border-collapse: collapse; background: var(--bg-card); }
        .table thead tr {
            background: #F9F8F6;
            border-bottom: 1px solid var(--border);
        }
        .table th {
            color: var(--text-muted);
            font-family: 'DM Sans', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 12px 20px;
            text-align: left;
        }
        .table td {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            color: var(--text-primary);
            font-size: 0.82rem;
        }
        .table tr:last-child td { border-bottom: none; }
        .table tbody tr:hover { background: #FAFAF8; }

        /* ── FORMS ──────────────────────────────── */
        .form-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            margin-bottom: 6px;
        }
        .form-control, input[type="text"], input[type="email"], input[type="password"],
        input[type="number"], input[type="date"], input[type="time"], input[type="tel"],
        select, textarea {
            background: #FAFAF8 !important;
            border: 1px solid var(--border) !important;
            color: var(--text-primary) !important;
            border-radius: var(--radius) !important;
            padding: 10px 14px !important;
            font-size: 0.875rem !important;
            font-family: 'DM Sans', sans-serif !important;
            width: 100%;
            transition: border-color 0.15s;
        }
        .form-control:focus, input:focus, select:focus, textarea:focus {
            border-color: var(--accent) !important;
            box-shadow: 0 0 0 3px rgba(181,151,90,0.12) !important;
            outline: none !important;
            background: #fff !important;
        }
        ::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 0.6; }

        /* ── BUTTONS ────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            cursor: pointer;
            font-size: 0.78rem;
            font-weight: 700;
            border-radius: var(--radius);
            transition: all 0.15s;
            text-decoration: none;
            border: none;
            white-space: nowrap;
        }
        .btn-primary {
            background: var(--accent) !important;
            color: #fff !important;
            padding: 10px 22px !important;
        }
        .btn-primary:hover { background: var(--accent-hover) !important; }

        .btn-secondary {
            background: #fff !important;
            border: 1px solid var(--border-strong) !important;
            color: var(--text-primary) !important;
            padding: 10px 18px !important;
        }
        .btn-secondary:hover { border-color: var(--accent) !important; color: var(--accent) !important; }

        .btn-danger {
            background: #FEF2F2 !important;
            border: 1px solid #FECACA !important;
            color: var(--danger) !important;
            padding: 8px 16px !important;
        }
        .btn-danger:hover { background: #FEE2E2 !important; }

        .btn-sm { padding: 7px 14px !important; font-size: 0.72rem !important; }

        /* ── BADGES ─────────────────────────────── */
        .badge {
            display: inline-flex;
            padding: 3px 8px;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-radius: 4px;
        }
        .badge-success { background: #DCFCE7; color: #15803D; }
        .badge-warning { background: var(--warning-bg); color: var(--warning-text); }
        .badge-danger  { background: #FEF2F2; color: var(--danger); }
        .badge-gray    { background: #F3F4F6; color: #4B5563; }

        /* ── ALERTS ─────────────────────────────── */
        .alert {
            padding: 14px 20px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: #F0FDF4; border: 1px solid #BBF7D0; color: #15803D; }
        .alert-error   { background: #FEF2F2; border: 1px solid #FECACA; color: var(--danger); }

        /* ── TOM SELECT ─────────────────────────── */
        .ts-wrapper .ts-control {
            background: #FAFAF8 !important;
            border: 1px solid var(--border) !important;
            color: var(--text-primary) !important;
            border-radius: var(--radius) !important;
            min-height: 40px !important;
            padding: 6px 10px !important;
            box-shadow: none !important;
        }
        .ts-wrapper.focus .ts-control { border-color: var(--accent) !important; }
        .ts-control input { color: var(--text-primary) !important; background: transparent !important; }
        .ts-control .item { color: var(--accent) !important; font-weight: 600; }
        .ts-control .placeholder { color: var(--text-muted) !important; }
        .ts-wrapper .ts-dropdown {
            z-index: 9999 !important;
            position: absolute !important;
            background: #fff !important;
            border: 1px solid var(--border) !important;
            border-top: none !important;
            border-radius: 0 0 var(--radius) var(--radius) !important;
            box-shadow: 0 8px 24px rgba(0,0,0,0.10) !important;
            color: var(--text-primary) !important;
        }
        .ts-dropdown .option { padding: 9px 12px; color: var(--text-primary) !important; font-size: 0.84rem; cursor: pointer; }
        .ts-dropdown .option:hover, .ts-dropdown .option.active { background: var(--accent-light) !important; color: var(--accent) !important; }

        /* ── SCROLLBAR ──────────────────────────── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: var(--bg-page); }
        ::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--accent); }

        /* ── MOBILE ─────────────────────────────── */
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); box-shadow: 4px 0 20px rgba(0,0,0,0.1); }
            .main-content { margin-left: 0; padding: 16px; }
            .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.3); z-index: 35; }
            .sidebar-overlay.active { display: block; }
        }

        /* ── MISC UTILITIES ─────────────────────── */
        .text-muted { color: var(--text-muted) !important; }
        .text-accent { color: var(--accent) !important; }
        .border-subtle { border-color: var(--border) !important; }
        .bg-accent { background-color: var(--accent) !important; }
        .btn-icon-action { padding: 0 !important; background: transparent !important; border: none !important; cursor: pointer; }
    </style>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <!-- Logo -->
            <div class="sidebar-logo">
                <img src="{{ asset('images/logo.svg') }}" alt="Traverse Inc." />
            </div>

            <nav class="flex-1">
                @if(auth()->user()->isSuperAdmin() && !app()->has('current_kitchen'))
                    <div class="nav-section-label">System</div>
                    <a href="{{ route('superadmin.dashboard') }}"
                        class="nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard" class="nav-icon"></i> Dashboard
                    </a>
                    <a href="{{ route('superadmin.kitchens.index') }}"
                        class="nav-link {{ request()->routeIs('superadmin.kitchens.*') ? 'active' : '' }}">
                        <i data-lucide="layout" class="nav-icon"></i> Restaurants
                    </a>
                @endif

                @if(app()->has('current_kitchen'))
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i data-lucide="grid" class="nav-icon"></i> Dashboard
                    </a>

                    {{-- Time Clock --}}
                    @if(!auth()->user()->isStaff() || auth()->user()->hasPermissionTo('manage_attendance'))
                        <div class="nav-section-label">Time Clock</div>
                        <a href="{{ route('attendance.tablet') }}" class="nav-link">
                            <i data-lucide="clock" class="nav-icon"></i> Time Clock
                        </a>
                        @if(!auth()->user()->isStaff())
                            <a href="{{ route('admin.attendance.index') }}" class="nav-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                                <i data-lucide="list" class="nav-icon"></i> Attendance Log
                            </a>
                        @endif
                    @endif

                    {{-- My Workspace --}}
                    @if(auth()->user()->isStaff())
                        <div class="nav-section-label">My Workspace</div>
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

                    {{-- Inventory --}}
                    @if(auth()->user()->hasPermissionTo('module_inventory') || !auth()->user()->isStaff())
                        <div class="nav-section-label">Inventory</div>
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

                    {{-- Recipes --}}
                    @if(auth()->user()->hasPermissionTo('module_recipes') || !auth()->user()->isStaff())
                        <div class="nav-section-label">Recipes</div>
                        @if(!auth()->user()->isStaff())
                            <a href="{{ route('admin.ingredients.pending') }}" class="nav-link {{ request()->routeIs('admin.ingredients.pending') ? 'active' : '' }} flex justify-between items-center">
                                <span class="flex items-center gap-2">
                                    <i data-lucide="shield-check" class="nav-icon"></i> Ingredient Approvals
                                </span>
                                @php $pendingCount = \App\Models\Ingredient::where('status','pending')->count(); @endphp
                                @if($pendingCount > 0)
                                    <span style="background:#dc2626;color:#fff;font-size:0.6rem;padding:2px 7px;border-radius:99px;font-weight:700;">{{ $pendingCount }}</span>
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

                    {{-- Admin --}}
                    @if(auth()->user()->hasPermissionTo('module_staff_management') || auth()->user()->hasPermissionTo('module_hr_payroll') || !auth()->user()->isStaff())
                        <div class="nav-section-label">Admin</div>
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
                                <i data-lucide="sun" class="nav-icon"></i> Leave Requests
                            </a>
                            <a href="{{ route('admin.performance.index') }}" class="nav-link {{ request()->routeIs('admin.performance.*') ? 'active' : '' }}">
                                <i data-lucide="award" class="nav-icon"></i> Performance
                            </a>
                        @endif
                    @endif

                    {{-- Checklists (SOPs) --}}
                    @if(auth()->user()->hasPermissionTo('module_sops') || !auth()->user()->isStaff())
                        <div class="nav-section-label">Checklists</div>
                        <a href="{{ route('sop.index') }}" class="nav-link {{ request()->routeIs('sop.index') || request()->routeIs('sop.execute') ? 'active' : '' }}">
                            <i data-lucide="clipboard-list" class="nav-icon"></i> My Checklists
                        </a>
                        @if(!auth()->user()->isStaff())
                            <a href="{{ route('admin.sop.index') }}" class="nav-link {{ request()->routeIs('admin.sop.index') ? 'active' : '' }}">
                                <i data-lucide="settings" class="nav-icon"></i> Manage Checklists
                            </a>
                            <a href="{{ route('admin.shifts.index') }}" class="nav-link {{ request()->routeIs('admin.shifts.*') ? 'active' : '' }}">
                                <i data-lucide="watch" class="nav-icon"></i> Shifts
                            </a>
                            <a href="{{ route('admin.shifts.assignments.index') }}" class="nav-link {{ request()->routeIs('admin.shifts.assignments.*') ? 'active' : '' }}">
                                <i data-lucide="user-plus" class="nav-icon"></i> Assignments
                            </a>
                            <a href="{{ route('admin.sop.reviews.index') }}" class="nav-link {{ request()->routeIs('admin.sop.reviews.*') ? 'active' : '' }}">
                                <i data-lucide="check-square" class="nav-icon"></i> Checklist Reviews
                            </a>
                        @endif
                    @endif
                @endif
            </nav>

            <!-- User footer -->
            <div class="mt-auto pt-5" style="border-top: 1px solid var(--border);">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--accent-light);color:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.75rem;text-transform:uppercase;flex-shrink:0;">
                        {{ substr(auth()->user()->name, 0, 2) }}
                    </div>
                    <div style="flex:1;overflow:hidden;">
                        <div style="font-weight:700;font-size:0.8rem;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</div>
                        <div style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;">{{ auth()->user()->role->label() }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary w-full" style="width:100%;justify-content:center;font-size:0.75rem;">
                        <i data-lucide="log-out" style="width:13px;height:13px;"></i> Sign Out
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
                    <button class="lg:hidden p-2" onclick="toggleSidebar()" style="color:var(--text-muted);background:none;border:none;cursor:pointer;">
                        <i data-lucide="menu"></i>
                    </button>
                    @yield('header')
                </div>
                <div class="flex items-center gap-3">
                    @yield('actions')
                </div>
            </header>

            @if(session('success'))
                <div class="alert alert-success">
                    <i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
                    {{ session('error') }}
                </div>
            @endif

            <div class="min-h-0 flex-1">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;padding:40px;">
        <div style="background:#fff;width:100%;height:100%;border-radius:10px;overflow:hidden;display:flex;flex-direction:column;border:1px solid var(--border);box-shadow:0 20px 60px rgba(0,0,0,0.15);">
            <div style="padding:16px 24px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
                <h3 id="modalTitle" style="margin:0;font-size:1rem;">File Preview</h3>
                <button onclick="closePreview()" style="background:none;border:none;cursor:pointer;color:var(--text-muted);">
                    <i data-lucide="x" style="width:20px;height:20px;"></i>
                </button>
            </div>
            <div style="flex:1;padding:16px;">
                <iframe id="previewFrame" style="width:100%;height:100%;border:none;border-radius:6px;"></iframe>
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

        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closePreview(); });
    </script>
    @stack('modals')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    @stack('scripts')
</body>

</html>
