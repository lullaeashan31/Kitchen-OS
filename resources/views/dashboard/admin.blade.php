@extends('layouts.app')

@section('header')
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Admin Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">System Overview & Management Console</p>
    </div>
@endsection

@section('content')
    <!-- Overview Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Staff -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all">
            <div
                class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
            </div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Total Staff</h3>
                </div>
                <div class="text-3xl font-extrabold text-gray-800">{{ $stats['total_staff'] }}</div>
                <p class="text-xs text-gray-500 mt-1">Registered Employees</p>
            </div>
        </div>

        <!-- Active Now -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all">
            <div
                class="absolute right-0 top-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
            </div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
                        <i data-lucide="activity" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Active Now</h3>
                </div>
                <div class="text-3xl font-extrabold text-gray-800">{{ $stats['active_staff'] }}</div>
                <p class="text-xs text-emerald-600 font-medium mt-1 flex items-center gap-1">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Currently Clocked In
                </p>
            </div>
        </div>

        <!-- Today's Attendance -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all">
            <div
                class="absolute right-0 top-0 w-24 h-24 bg-indigo-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
            </div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-indigo-100 text-indigo-600 rounded-lg">
                        <i data-lucide="calendar-check" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Today's Shift</h3>
                </div>
                <div class="text-3xl font-extrabold text-gray-800">{{ $stats['today_clock_in'] }}</div>
                <p class="text-xs text-gray-500 mt-1">
                    <span class="font-medium text-gray-700">{{ $stats['today_clock_out'] }}</span> completed shifts
                </p>
            </div>
        </div>

        <!-- Recipes & Ingredients -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all">
            <div
                class="absolute right-0 top-0 w-24 h-24 bg-purple-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
            </div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-purple-100 text-purple-600 rounded-lg">
                        <i data-lucide="chef-hat" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Recipes</h3>
                </div>
                <div class="text-3xl font-extrabold text-gray-800">{{ $stats['total_recipes'] }}</div>
                @if($stats['pending_ingredients'] > 0)
                    <p
                        class="text-xs text-amber-600 font-bold mt-1 bg-amber-50 inline-block px-2 py-0.5 rounded-full border border-amber-100">
                        {{ $stats['pending_ingredients'] }} Ingredients Pending
                    </p>
                @else
                    <p class="text-xs text-gray-500 mt-1">All ingredients approved</p>
                @endif
            </div>
        </div>
    </div>


    @if($stats['low_stock_count'] > 0)
        <div class="bg-red-50 border border-red-200 rounded-2xl p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-red-800 flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                    Low Stock Alerts ({{ $stats['low_stock_count'] }})
                </h2>
                <a href="{{ route('admin.inventory.index') }}"
                    class="text-sm font-medium text-red-600 hover:text-red-800 underline">View Master Inventory</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($stats['low_stock_items'] as $item)
                    <div class="bg-white p-4 rounded-xl border border-red-100 flex justify-between items-center shadow-sm">
                        <div>
                            <div class="font-bold text-gray-800">{{ $item->name }}</div>
                            <div class="text-xs text-gray-500">Min: {{ $item->alert_threshold }} {{ $item->measurement_unit }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-red-600 text-lg">{{ $item->current_stock }}</div>
                            <div class="text-xs text-gray-400">{{ $item->measurement_unit }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Quick Actions Panel -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i data-lucide="zap" class="w-5 h-5 text-yellow-500"></i> Quick Actions
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('admin.attendance.index') }}"
                    class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:border-blue-200 hover:bg-blue-50/50 transition-all group">
                    <div
                        class="bg-blue-100 p-3 rounded-full text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i data-lucide="clipboard-list" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800">Manage Attendance</h4>
                        <p class="text-xs text-gray-500 line-clamp-1">View reports & fix logs</p>
                    </div>
                </a>

                <a href="{{ route('recipes.create') }}"
                    class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:border-purple-200 hover:bg-purple-50/50 transition-all group">
                    <div
                        class="bg-purple-100 p-3 rounded-full text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <i data-lucide="plus" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800">Create New Recipe</h4>
                        <p class="text-xs text-gray-500 line-clamp-1">Add dish to database</p>
                    </div>
                </a>

                <a href="{{ route('excel.import_form') }}"
                    class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:border-green-200 hover:bg-green-50/50 transition-all group">
                    <div
                        class="bg-green-100 p-3 rounded-full text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors">
                        <i data-lucide="file-spreadsheet" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800">Import Master Data</h4>
                        <p class="text-xs text-gray-500 line-clamp-1">Upload Excel sheets</p>
                    </div>
                </a>

                <a href="{{ route('admin.staff.index') }}"
                    class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/50 transition-all group">
                    <div
                        class="bg-indigo-100 p-3 rounded-full text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <i data-lucide="user-cog" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800">Manage Staff</h4>
                        <p class="text-xs text-gray-500 line-clamp-1">Add/Edit employees</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- System Status -->
        <div
            class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-3xl -mr-16 -mt-16"></div>

            <h2 class="text-lg font-bold mb-6 flex items-center gap-2 relative z-10">
                <i data-lucide="server" class="w-5 h-5 text-green-400"></i> System Status
            </h2>

            <div class="space-y-6 relative z-10">
                <div class="flex items-start gap-4">
                    <div class="w-2 h-2 mt-2 rounded-full bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.6)]"></div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-200">Database Active</h4>
                        <p class="text-xs text-gray-400">Connection stable</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-2 h-2 mt-2 rounded-full bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.6)]"></div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-200">Cloud Sync</h4>
                        <p class="text-xs text-gray-400">Drive Integration Ready</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-2 h-2 mt-2 rounded-full bg-purple-500 shadow-[0_0_10px_rgba(168,85,247,0.6)]"></div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-200">Tablet Mode</h4>
                        <p class="text-xs text-gray-400">Accessible at /time-clock</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-white/10">
                <div class="text-xs text-gray-400 uppercase tracking-widest font-bold mb-2">Current Version</div>
                <div class="text-2xl font-mono text-white">v2.1.0</div>
            </div>
        </div>
    </div>
@endsection