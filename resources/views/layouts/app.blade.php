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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#64748b',
                        success: '#22c55e',
                        warning: '#eab308',
                        danger: '#ef4444',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!-- Tom Select CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Custom Overrides */
        .app-container {
            display: flex;
            min-height: 100vh;
            background-color: #f8fafc;
        }

        .sidebar {
            width: 310px;
            background-color: white;
            border-right: 1px solid #e2e8f0;
            position: fixed;
            height: 100vh;
            top: 0;
            left: 0;
            overflow-y: auto;
            padding: 1.5rem;
            z-index: 40;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            margin-left: 310px;
            flex: 1;
            min-height: 100vh;
            padding: 2rem;
            display: flex;
            flex-direction: column;
        }

        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0 0 2rem 0 !important;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            background-color: white;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .nav-link.active {
            background-color: #eff6ff;
            color: #3b82f6;
            border-right: 3px solid #3b82f6;
            font-weight: 600;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #64748b;
            transition: all 0.2s;
            font-size: 0.85rem;
            text-decoration: none;
            border-radius: 0.375rem;
            margin-bottom: 0.25rem;
        }

        .nav-link:hover {
            background-color: #f8fafc;
            color: #1e293b;
        }

        .nav-icon {
            margin-right: 0.75rem;
            width: 1.15rem;
            height: 1.15rem;
        }

        body {
            font-size: 0.9rem;
        }

        /* Global slight reduction */
    </style>
    <script src="https://unpkg.com/lucide@latest"></script> <!-- Icons -->
</head>

<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div style="margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                <i data-lucide="chef-hat" style="color: var(--primary-color);"></i>
                <h2 style="font-size: 1.25rem;">Kitchen OS</h2>
            </div>

            <nav>
                @if(auth()->user()->isStaff())
                    <!-- Staff Sidebar: Minimal -->
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard" class="nav-icon"></i> Dashboard
                    </a>
                    <a href="{{ route('attendance.tablet') }}" class="nav-link">
                        <i data-lucide="clock" class="nav-icon"></i> Time Clock
                    </a>
                    <a href="{{ route('recipes.create') }}"
                        class="nav-link {{ request()->routeIs('recipes.create') ? 'active' : '' }}">
                        <i data-lucide="plus-circle" class="nav-icon"></i> New Recipe
                    </a>
                    <a href="{{ route('recipes.index') }}"
                        class="nav-link {{ request()->routeIs('recipes.index') ? 'active' : '' }}">
                        <i data-lucide="book-open" class="nav-icon"></i> My Recipes
                    </a>
                    <a href="{{ route('production.create') }}"
                        class="nav-link {{ request()->routeIs('production.create') ? 'active' : '' }}">
                        <i data-lucide="chef-hat" class="nav-icon text-red-500"></i> Cook / Production
                    </a>
                    <a href="{{ route('sop.index') }}" class="nav-link {{ request()->routeIs('sop.*') ? 'active' : '' }}">
                        <i data-lucide="clipboard-list" class="nav-icon text-blue-500"></i> SOP Checklists
                    </a>

                @else
                    <!-- Admin & Manager Sidebar -->
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard" class="nav-icon"></i> Dashboard
                    </a>

                    <!-- Time Clock Section -->
                    <div
                        style="margin: 1rem 0 0.5rem 1rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">
                        Time Clock
                    </div>
                    <a href="{{ route('attendance.tablet') }}" class="nav-link">
                        <i data-lucide="clock" class="nav-icon"></i> Launch Tablet Mode
                    </a>
                    <a href="{{ route('admin.attendance.index') }}"
                        class="nav-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                        <i data-lucide="list" class="nav-icon"></i> Attendance List
                    </a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.settings.location') }}"
                            class="nav-link {{ request()->routeIs('admin.settings.location') ? 'active' : '' }}">
                            <i data-lucide="map-pin" class="nav-icon"></i> Location Settings
                        </a>
                        <a href="{{ route('admin.settings.devices') }}"
                            class="nav-link {{ request()->routeIs('admin.settings.devices') ? 'active' : '' }}">
                            <i data-lucide="smartphone" class="nav-icon"></i> Device List
                        </a>
                    @endif

                    <!-- Inventory & Stock Section -->
                    <div
                        style="margin: 1rem 0 0.5rem 1rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">
                        Inventory & Stock
                    </div>
                    <a href="{{ route('admin.inventory.index') }}"
                        class="nav-link {{ request()->routeIs('admin.inventory.index') ? 'active' : '' }}">
                        <i data-lucide="package" class="nav-icon"></i> Master Inventory
                    </a>
                    <a href="{{ route('purchases.index') }}"
                        class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                        <i data-lucide="shopping-cart" class="nav-icon"></i> Purchases
                    </a>
                    <a href="{{ route('admin.inventory.upload') }}"
                        class="nav-link {{ request()->routeIs('admin.inventory.upload') ? 'active' : '' }}">
                        <i data-lucide="file-spreadsheet" class="nav-icon"></i> Inventory Upload
                    </a>

                    <!-- Recipe Management Section -->
                    <div
                        style="margin: 1rem 0 0.5rem 1rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">
                        Recipe Management
                    </div>
                    <a href="{{ route('admin.ingredients.pending') }}"
                        class="nav-link {{ request()->routeIs('admin.ingredients.pending') ? 'active' : '' }}">
                        <i data-lucide="alert-circle" class="nav-icon text-orange-500"></i> Pending Ingredients
                    </a>
                    <a href="{{ route('production.create') }}"
                        class="nav-link {{ request()->routeIs('production.*') ? 'active' : '' }}">
                        <i data-lucide="chef-hat" class="nav-icon text-red-500"></i> Cook / Production
                    </a>
                    <a href="{{ route('recipes.index') }}"
                        class="nav-link {{ request()->routeIs('recipes.*') ? 'active' : '' }}">
                        <i data-lucide="book-open" class="nav-icon"></i> All Recipes
                    </a>
                    <a href="{{ route('recipes.create') }}"
                        class="nav-link {{ request()->routeIs('recipes.create') ? 'active' : '' }}">
                        <i data-lucide="plus-circle" class="nav-icon"></i> Create Recipe
                    </a>
                    <a href="{{ route('categories.index') }}"
                        class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <i data-lucide="tag" class="nav-icon"></i> Categories
                    </a>
                    <a href="{{ route('ingredients.index') }}"
                        class="nav-link {{ request()->routeIs('ingredients.*') ? 'active' : '' }}">
                        <i data-lucide="database" class="nav-icon"></i> Master Ingredients
                    </a>
                    <a href="{{ route('ingredients.index', ['status' => 'pending']) }}" class="nav-link">
                        <i data-lucide="alert-circle" class="nav-icon"></i> Pending Ingredients
                    </a>
                    <a href="{{ route('excel.import_form') }}"
                        class="nav-link {{ request()->routeIs('excel.*') ? 'active' : '' }}">
                        <i data-lucide="upload" class="nav-icon"></i> Excel Upload
                    </a>

                    @if(auth()->user()->isAdmin())
                        <!-- Admin Only Section -->
                        <div
                            style="margin: 1rem 0 0.5rem 1rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">
                            Administration
                        </div>
                        <a href="{{ route('admin.staff.index') }}"
                            class="nav-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                            <i data-lucide="users" class="nav-icon"></i> Staff Profiles
                        </a>
                        <a href="{{ route('audit_logs.index') }}"
                            class="nav-link {{ request()->routeIs('audit_logs.*') ? 'active' : '' }}">
                            <i data-lucide="shield-alert" class="nav-icon"></i> Audit Logs
                        </a>
                        <a href="https://drive.google.com" target="_blank" class="nav-link">
                            <i data-lucide="hard-drive" class="nav-icon"></i> Google Drive
                        </a>
                    @endif

                    <!-- SOP Management Section -->
                    <div
                        style="margin: 1rem 0 0.5rem 1rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">
                        Operations (SOP)
                    </div>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.sop.index') }}"
                            class="nav-link {{ request()->routeIs('admin.sop.index') ? 'active' : '' }}">
                            <i data-lucide="settings" class="nav-icon"></i> Manage Checklists
                        </a>
                        <a href="{{ route('admin.shifts.index') }}"
                            class="nav-link {{ request()->routeIs('admin.shifts.*') ? 'active' : '' }}">
                            <i data-lucide="clock" class="nav-icon text-indigo-500"></i> Manage Shifts
                        </a>
                        <a href="{{ route('admin.shifts.assignments.index') }}"
                            class="nav-link {{ request()->routeIs('admin.shifts.assignments.*') ? 'active' : '' }}">
                            <i data-lucide="user-plus" class="nav-icon text-purple-500"></i> Daily Assignments
                        </a>
                    @endif
                    <a href="{{ route('admin.sop.reviews.index') }}"
                        class="nav-link {{ request()->routeIs('admin.sop.reviews.*') ? 'active' : '' }}">
                        <i data-lucide="check-square" class="nav-icon text-green-500"></i> Review SOPs
                    </a>

                @endif

                <!-- Bottom Profile Section preserved -->
            </nav>

            <div
                style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-color); display: flex; align-items: center; gap: 1rem;">
                <div style="flex: 1;">
                    <div style="font-weight: 500;">{{ auth()->user()->name }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ auth()->user()->role->label() }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        style="background: none; border: none; cursor: pointer; color: var(--text-muted);">
                        <i data-lucide="log-out"></i>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-header">
                <div>
                    <!-- Breadcrumbs placeholder or page title logic -->
                    @yield('header')
                </div>
                <div>
                    <!-- Actions -->
                    @yield('actions')
                </div>
            </header>

            @if(session('success'))
                <div class="alert alert-success fade-in">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error fade-in">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error fade-in">
                    <ul style="list-style: none;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="fade-in">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
        <div
            style="background: white; width: 90%; height: 90%; border-radius: var(--radius-lg); overflow: hidden; display: flex; flex-direction: column;">
            <div
                style="padding: 1rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <h3 id="modalTitle" style="margin: 0;">Preview</h3>
                <button onclick="closePreview()"
                    style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div style="flex: 1; background: #f3f4f6;">
                <iframe id="previewFrame" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>

    <!-- Initialization Scripts -->
    <script>
        lucide.createIcons();

        function openPreview(url, name) {
            // Google Drive Embed Hack: Replace 'view' with 'preview' if needed, or rely on URL
            // Many Drive URLs are like https://drive.google.com/file/d/XXX/view?usp=sharing
            // Embed URL is usually https://drive.google.com/file/d/XXX/preview
            let embedUrl = url;
            if (url.includes('/view')) {
                embedUrl = url.replace('/view', '/preview');
            }

            document.getElementById('modalTitle').textContent = name;
            document.getElementById('previewFrame').src = embedUrl;
            document.getElementById('previewModal').style.display = 'flex';
        }

        function closePreview() {
            document.getElementById('previewModal').style.display = 'none';
            document.getElementById('previewFrame').src = '';
        }

        // Close on escape
        document.addEventListener('keydown', function (event) {
            if (event.key === "Escape") {
                closePreview();
            }
        });
    </script>
    @stack('scripts')
    <!-- Tom Select JS -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
</body>

</html>