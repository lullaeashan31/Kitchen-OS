@extends('layouts.app')

@section('header')
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Shift Assignments</h1>
            <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mt-1">Calendar Distribution — {{ \Carbon\Carbon::parse($date)->format('l, jS F Y') }}</p>
        </div>
        <div class="flex items-center gap-3 bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center gap-2 px-3 text-slate-400">
                <i data-lucide="calendar" class="w-4 h-4"></i>
                <span class="text-xs font-black uppercase tracking-wider">Select Date</span>
            </div>
            <input type="date" id="date-picker" value="{{ $date }}"
                class="rounded-xl border-slate-100 bg-slate-50 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold"
                onchange="window.location.href='?date='+this.value">
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                <p class="text-sm font-bold text-emerald-800">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-blue-600 rounded-[32px] p-8 text-white relative overflow-hidden shadow-xl shadow-blue-500/20 mb-8">
            <div class="absolute top-0 right-0 p-8 opacity-10">
                <i data-lucide="clock" class="w-32 h-32"></i>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="space-y-2">
                    <h3 class="text-2xl font-black tracking-tight">Daily Deployment Strategy</h3>
                    <p class="text-blue-100/80 font-bold text-sm">Assign staff members to specific operational slots. Changes are reflected instantly on staff tablets.</p>
                </div>
                <form action="{{ route('admin.shifts.auto_generate') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-white/20 hover:bg-white/30 backdrop-blur-md px-6 py-3 rounded-2xl text-white font-black uppercase tracking-widest text-xs transition-all flex items-center gap-2 border border-white/20">
                        <i data-lucide="sparkles" class="w-4 h-4"></i> Auto-Balance Week
                    </button>
                </form>
            </div>
        </div>

        <form action="{{ route('admin.shifts.assignments.store') }}" method="POST">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="grid gap-6">
                @foreach($shifts as $shift)
                    <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition-all group">
                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-8">
                            <div class="flex items-center gap-6">
                                <div class="w-16 h-16 rounded-3xl bg-slate-900 text-white flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                                    <i data-lucide="sun" class="w-8 h-8"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ $shift->name }}</h3>
                                    <div class="flex items-center gap-2 text-slate-500 font-bold text-xs uppercase tracking-widest mt-1">
                                        <i data-lucide="clock-4" class="w-3 h-3"></i>
                                        {{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} -
                                        {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }}
                                    </div>
                                </div>
                            </div>

                            <div class="w-full md:w-96 space-y-3">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Assigned Operational Staff</label>
                                <div class="relative">
                                    <select name="assignments[{{ $shift->id }}]"
                                        class="w-full pl-5 pr-12 py-4 rounded-2xl bg-slate-50 border-2 border-slate-100 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/5 transition-all outline-none font-bold text-slate-800 appearance-none">
                                        <option value="">— Unassigned —</option>
                                        @foreach($users as $user)
                                            @php
                                                $assignedUserId = $assignments->get($shift->id)?->first()?->user_id;
                                            @endphp
                                            <option value="{{ $user->id }}" {{ $assignedUserId == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->staff_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                        <i data-lucide="chevron-down" class="w-5 h-5"></i>
                                    </div>
                                </div>
                                <div class="flex justify-between px-2">
                                    <span class="text-[9px] font-black text-blue-500 uppercase tracking-widest">Requirement: 1-2 Staff</span>
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Status: Ready</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 flex justify-center">
                <button type="submit"
                    class="group bg-slate-900 hover:bg-black px-12 py-5 rounded-3xl text-white font-black uppercase tracking-[0.2em] text-sm shadow-2xl shadow-slate-900/20 transition-all active:scale-95 flex items-center gap-4">
                    <i data-lucide="save" class="w-6 h-6 group-hover:rotate-12 transition-transform"></i>
                    Deploy Schedule
                </button>
            </div>
        </form>
    </div>
@endsection