@extends('layouts.app')

@section('header')
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Manager Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Operational Overview</p>
    </div>
@endsection

@section('content')
    <!-- Overview Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Active Staff -->
        <a href="{{ route('ingredients.index') }}" class="block group">
            <div
                class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all h-full">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-green-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-green-100 text-green-600 rounded-lg">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Kitchen Team</h3>
                    </div>
                    <div class="text-3xl font-extrabold text-gray-800">{{ $stats['active_staff'] }}</div>
                    <p class="text-xs text-green-600 font-medium mt-1 flex items-center gap-1">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        Active on Shift
                    </p>
                </div>
            </div>
        </a>

        <!-- Today's Check-ins -->
        <a href="{{ route('admin.attendance.index') }}" class="block group">
            <div
                class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all h-full">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Total Check-ins</h3>
                    </div>
                    <div class="text-3xl font-extrabold text-gray-800">{{ $stats['today_attendance'] }}</div>
                    <p class="text-xs text-gray-500 mt-1">Staff present today</p>
                </div>
            </div>
        </a>

        <!-- Total Recipes -->
        <a href="{{ route('recipes.index') }}" class="block group">
            <div
                class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all h-full">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-purple-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-purple-100 text-purple-600 rounded-lg">
                            <i data-lucide="book-open" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Recipe Book</h3>
                    </div>
                    <div class="text-3xl font-extrabold text-gray-800">{{ $stats['total_recipes'] }}</div>
                    <p class="text-xs text-gray-500 mt-1">Approved dishes</p>
                </div>
            </div>
        </a>

        <!-- Pending Items -->
        <a href="{{ route('ingredients.index') }}" class="block group">
            <div
                class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all h-full">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-amber-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
                            <i data-lucide="alert-circle" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Needs Review</h3>
                    </div>
                    <div class="text-3xl font-extrabold text-gray-800">{{ $stats['pending_ingredients'] }}</div>
                    <p class="text-xs text-amber-600 font-medium mt-1">Ingredients Pending</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Command Centre Section -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i data-lucide="layout-dashboard" class="w-6 h-6 text-indigo-600"></i>
            Command Centre
        </h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- SOP Progress -->
            <a href="{{ route('admin.sop.reviews.index') }}" class="block bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-2">
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                        <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">SOP Status</span>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <div class="text-2xl font-black text-gray-800">
                            {{ $stats['completed_checklists'] }}/{{ $stats['total_checklists'] }}
                        </div>
                        <p class="text-xs text-gray-500">Checklists done</p>
                    </div>
                    @php 
                        $sopPercent = $stats['total_checklists'] > 0 ? ($stats['completed_checklists'] / $stats['total_checklists']) * 100 : 0;
                    @endphp
                    <div class="w-12 h-12 relative flex items-center justify-center">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="4" fill="transparent" class="text-gray-100" />
                            <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="4" fill="transparent" stroke-dasharray="125.6" stroke-dashoffset="{{ 125.6 * (1 - $sopPercent / 100) }}" class="text-blue-500 transition-all duration-500" />
                        </svg>
                        <span class="absolute text-[10px] font-bold text-blue-600">{{ round($sopPercent) }}%</span>
                    </div>
                </div>
            </a>

            <!-- Overdue Count -->
            <a href="{{ route('admin.sop.reviews.index') }}" class="block bg-white rounded-2xl p-4 shadow-sm border border-gray-100 {{ $stats['overdue_sop_count'] > 0 ? 'ring-2 ring-red-500 ring-inset hover:bg-red-50' : 'hover:bg-gray-50' }} transition-all">
                <div class="flex items-center justify-between mb-2">
                    <div class="p-2 {{ $stats['overdue_sop_count'] > 0 ? 'bg-red-100 text-red-600' : 'bg-gray-50 text-gray-400' }} rounded-lg">
                        <i data-lucide="clock-alert" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Overdue</span>
                </div>
                <div>
                    <div class="text-2xl font-black {{ $stats['overdue_sop_count'] > 0 ? 'text-red-600' : 'text-gray-800' }}">
                        {{ $stats['overdue_sop_count'] }}
                    </div>
                    <p class="text-xs text-gray-500">Checklists missed</p>
                </div>
            </a>

            <!-- Inventory Alerts -->
            <a href="{{ route('ingredients.index') }}" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-2">
                    <div class="p-2 {{ $stats['low_stock_count'] > 0 ? 'bg-amber-100 text-amber-600' : 'bg-gray-50 text-gray-400' }} rounded-lg">
                        <i data-lucide="package-search" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Inventory</span>
                </div>
                <div>
                    <div class="text-2xl font-black {{ $stats['low_stock_count'] > 0 ? 'text-amber-600' : 'text-gray-800' }}">
                        {{ $stats['low_stock_count'] }}
                    </div>
                    <p class="text-xs text-gray-500">Low stock alerts</p>
                </div>
            </a>

            <!-- Purchase Pending -->
            <a href="{{ route('purchases.index') }}" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-2">
                    <div class="p-2 {{ $stats['pending_purchases_count'] > 0 ? 'bg-purple-100 text-purple-600' : 'bg-gray-50 text-gray-400' }} rounded-lg">
                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Purchases</span>
                </div>
                <div>
                    <div class="text-2xl font-black {{ $stats['pending_purchases_count'] > 0 ? 'text-purple-600' : 'text-gray-800' }}">
                        {{ $stats['pending_purchases_count'] }}
                    </div>
                    <p class="text-xs text-gray-500">Pending approval</p>
                </div>
            </a>

            <!-- POS Sync -->
            <a href="{{ route('pos.upload') }}" class="block bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-2">
                    <div class="p-2 {{ $stats['pos_synced_today'] ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} rounded-lg">
                        <i data-lucide="{{ $stats['pos_synced_today'] ? 'refresh-cw' : 'alert-circle' }}" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">POS Sync</span>
                </div>
                <div>
                    <div class="text-lg font-black {{ $stats['pos_synced_today'] ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $stats['pos_synced_today'] ? 'Synced' : 'Required' }}
                    </div>
                    <p class="text-xs text-gray-500">{{ $stats['pos_synced_today'] ? 'Sales data up to date' : 'Upload daily sales CSV' }}</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Manager Actions -->
    <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100">
        <h2 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
            <i data-lucide="briefcase" class="w-5 h-5 text-gray-600"></i> Management Tools
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ route('admin.attendance.index') }}"
                class="flex flex-col items-center justify-center p-6 rounded-2xl border-2 border-dashed border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition-all group text-center">
                <div
                    class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <h4 class="font-bold text-gray-800">View Staff Attendance</h4>
                <p class="text-xs text-gray-500 mt-1">Monitor shift logs (Read Only)</p>
            </a>

            <a href="{{ route('recipes.create') }}"
                class="flex flex-col items-center justify-center p-6 rounded-2xl border-2 border-dashed border-gray-200 hover:border-purple-400 hover:bg-purple-50 transition-all group text-center">
                <div
                    class="w-12 h-12 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="plus-circle" class="w-6 h-6"></i>
                </div>
                <h4 class="font-bold text-gray-800">Add New Recipe</h4>
                <p class="text-xs text-gray-500 mt-1">Create standard operating procedures</p>
            </a>

            <a href="{{ route('ingredients.index') }}"
                class="flex flex-col items-center justify-center p-6 rounded-2xl border-2 border-dashed border-gray-200 hover:border-amber-400 hover:bg-amber-50 transition-all group text-center">
                <div
                    class="w-12 h-12 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="database" class="w-6 h-6"></i>
                </div>
                <h4 class="font-bold text-gray-800">Master Ingredients</h4>
                <p class="text-xs text-gray-500 mt-1">Review & approve database items</p>
            </a>
        </div>
    </div>
@endsection