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
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all">
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

        <!-- Today's Check-ins -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all">
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

        <!-- Total Recipes -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all">
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

        <!-- Pending Items -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all">
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