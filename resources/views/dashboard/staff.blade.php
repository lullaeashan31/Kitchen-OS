@extends('layouts.app')

@section('header')
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Staff Portal</h1>
        <p class="text-gray-500 mt-1">Manage your shifts, attendance, and leave requests.</p>
    </div>
@endsection

@section('content')
    <!-- Dashboard Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <!-- Today's Shift Card -->
        <div class="bg-white p-6 rounded-3xl shadow-xl border border-gray-100 relative overflow-hidden group">
            <div
                class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50 rounded-full opacity-50 transition-transform group-hover:scale-125">
            </div>
            <div class="relative">
                <div
                    class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center mb-4 text-white shadow-lg shadow-blue-200">
                    <i data-lucide="sun" class="w-6 h-6"></i>
                </div>
                <div class="text-xs uppercase tracking-widest text-gray-400 font-bold mb-1">Today's Shift</div>
                <div class="text-xl font-black text-gray-900">
                    {{ $todayShift ? $todayShift->shift->name : 'No Shift' }}
                </div>
                <div class="text-xs text-gray-400 mt-1">
                    {{ $todayShift ? Carbon\Carbon::parse($todayShift->shift->start_time)->format('g:i A') . ' - ' . Carbon\Carbon::parse($todayShift->shift->end_time)->format('g:i A') : 'Weekly Off / Unscheduled' }}
                </div>
            </div>
        </div>

        <!-- Attendance Card -->
        <div class="bg-white p-6 rounded-3xl shadow-xl border border-gray-100 relative overflow-hidden group">
            <div
                class="absolute -right-4 -top-4 w-24 h-24 bg-green-50 rounded-full opacity-50 transition-transform group-hover:scale-125">
            </div>
            <div class="relative">
                <div
                    class="w-12 h-12 bg-green-600 rounded-2xl flex items-center justify-center mb-4 text-white shadow-lg shadow-green-200">
                    <i data-lucide="calendar-check" class="w-6 h-6"></i>
                </div>
                <div class="text-xs uppercase tracking-widest text-gray-400 font-bold mb-1">Attendance</div>
                <div class="text-xl font-black text-gray-900">{{ $monthDaysWorked }} Days</div>
                <div class="text-xs text-gray-400 mt-1">Present this month</div>
            </div>
        </div>

        <!-- Leave Card -->
        <div class="bg-white p-6 rounded-3xl shadow-xl border border-gray-100 relative overflow-hidden group">
            <div
                class="absolute -right-4 -top-4 w-24 h-24 bg-orange-50 rounded-full opacity-50 transition-transform group-hover:scale-125">
            </div>
            <div class="relative">
                <div
                    class="w-12 h-12 bg-orange-600 rounded-2xl flex items-center justify-center mb-4 text-white shadow-lg shadow-orange-200">
                    <i data-lucide="send" class="w-6 h-6"></i>
                </div>
                <div class="text-xs uppercase tracking-widest text-gray-400 font-bold mb-1">Leave Requests</div>
                <div class="text-xl font-black text-gray-900">{{ $pendingLeavesCount }} Pending</div>
                <div class="text-xs text-gray-400 mt-1">Awaiting approval</div>
            </div>
        </div>

        <!-- Recipe Card -->
        <div class="bg-white p-6 rounded-3xl shadow-xl border border-gray-100 relative overflow-hidden group">
            <div
                class="absolute -right-4 -top-4 w-24 h-24 bg-purple-50 rounded-full opacity-50 transition-transform group-hover:scale-125">
            </div>
            <div class="relative">
                <div
                    class="w-12 h-12 bg-purple-600 rounded-2xl flex items-center justify-center mb-4 text-white shadow-lg shadow-purple-200">
                    <i data-lucide="book-open" class="w-6 h-6"></i>
                </div>
                <div class="text-xs uppercase tracking-widest text-gray-400 font-bold mb-1">My Recipes</div>
                <div class="text-xl font-black text-gray-900">{{ $myRecipesCount }} Items</div>
                <div class="text-xs text-gray-400 mt-1">Created by me</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
        <i data-lucide="zap" class="w-6 h-6 text-yellow-400"></i>
        Quick Actions
    </h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <a href="{{ route('attendance.tablet') }}"
            class="flex flex-col items-center justify-center p-8 bg-blue-600 rounded-3xl shadow-lg hover:bg-blue-700 transition-all text-white group transform hover:scale-[1.05]">
            <i data-lucide="clock" class="w-10 h-10 mb-3 group-hover:rotate-12 transition-transform"></i>
            <span class="font-bold">Time Clock</span>
        </a>
        <a href="{{ route('recipes.index') }}"
            class="flex flex-col items-center justify-center p-8 bg-red-600 rounded-3xl shadow-lg hover:bg-red-700 transition-all text-white group transform hover:scale-[1.05]">
            <i data-lucide="book-open" class="w-10 h-10 mb-3 group-hover:rotate-12 transition-transform"></i>
            <span class="font-bold">My Recipes</span>
        </a>
        <a href="{{ route('employee.leave.create') }}"
            class="flex flex-col items-center justify-center p-8 bg-orange-600 rounded-3xl shadow-lg hover:bg-orange-700 transition-all text-white group transform hover:scale-[1.05]">
            <i data-lucide="calendar-plus" class="w-10 h-10 mb-3 group-hover:rotate-12 transition-transform"></i>
            <span class="font-bold">Request Leave</span>
        </a>
        <a href="{{ route('employee.attendance.index') }}"
            class="flex flex-col items-center justify-center p-8 bg-green-600 rounded-3xl shadow-lg hover:bg-green-700 transition-all text-white group transform hover:scale-[1.05]">
            <i data-lucide="file-text" class="w-10 h-10 mb-3 group-hover:rotate-12 transition-transform"></i>
            <span class="font-bold">My Logs</span>
        </a>
    </div>
@endsection