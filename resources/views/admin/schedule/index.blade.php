@extends('layouts.app')

@section('header')
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Staff Schedule</h1>
            <p class="text-gray-500 mt-1">Set weekly role requirements and assign staff by day.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.schedule.index', ['week' => $prevWeek]) }}" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 font-medium text-gray-700">← Prev</a>
            <span class="px-4 py-2 bg-gray-100 rounded-xl font-bold text-gray-800">
                Week of {{ $start->format('M d') }}
            </span>
            <a href="{{ route('admin.schedule.index', ['week' => $nextWeek]) }}" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 font-medium text-gray-700">Next →</a>
        </div>
    </div>
@endsection

@section('content')
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i> {{ session('success') }}
        </div>
    @endif

    @if($roles->isEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center">
            <p class="text-amber-800 font-bold">No roles created yet.</p>
            <p class="text-sm text-amber-700 mt-1">Create roles (e.g. Manager, Cook) from <a href="{{ route('admin.roles.index') }}" class="underline font-bold">Roles</a> first, then set schedule requirements here.</p>
        </div>
    @else

    <!-- 1. Weekly requirements: how many of each role per day -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i data-lucide="settings" class="w-5 h-5 text-blue-500"></i>
            Weekly Requirements (how many per role per day)
        </h2>
        <form action="{{ route('admin.schedule.requirements') }}" method="POST">
            @csrf
            @php $idx = 0; @endphp
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                            <th class="p-3 text-left font-semibold">Day</th>
                            @foreach($roles as $role)
                                <th class="p-3 text-center font-semibold">{{ $role->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'] as $dow => $dayName)
                            <tr class="border-t border-gray-100 hover:bg-gray-50/50">
                                <td class="p-3 font-medium text-gray-800">{{ $dayName }}</td>
                                @foreach($roles as $role)
                                    @php
                                        $req = $requirements->get($dow)?->firstWhere('role_id', $role->id);
                                        $count = $req ? $req->required_count : 0;
                                    @endphp
                                    <td class="p-2">
                                        <input type="hidden" name="requirements[{{ $idx }}][day_of_week]" value="{{ $dow }}">
                                        <input type="hidden" name="requirements[{{ $idx }}][role_id]" value="{{ $role->id }}">
                                        <input type="number" name="requirements[{{ $idx }}][required_count]" value="{{ $count }}" min="0" max="20"
                                            class="w-16 text-center rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none py-2">
                                    </td>
                                    @php $idx++; @endphp
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg transition-all">Save Requirements</button>
            </div>
        </form>
    </div>

    <!-- 2. Assign staff for this week -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i data-lucide="users" class="w-5 h-5 text-green-500"></i>
            Assign Staff (this week)
        </h2>
        <form action="{{ route('admin.schedule.assignments') }}" method="POST" id="scheduleForm">
            @csrf
            @php $aIdx = 0; @endphp
            <div class="space-y-6">
                @foreach($days as $date)
                    @php
                        $dow = $date->dayOfWeekIso; // 1=Mon .. 7=Sun
                        $dayReqs = $requirements->get($dow) ?? collect();
                    @endphp
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 font-bold text-gray-800 border-b border-gray-200">
                            {{ $date->format('l') }}, {{ $date->format('M d') }}
                        </div>
                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($dayReqs as $req)
                                @for($slot = 1; $slot <= $req->required_count; $slot++)
                                    @php
                                        $key = $date->format('Y-m-d') . '_' . $req->role_id . '_' . $slot;
                                        $assigned = $assignments->get($key)?->first();
                                    @endphp
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm font-medium text-gray-600 shrink-0">{{ $req->role->name }} #{{ $slot }}</span>
                                        <input type="hidden" name="assignments[{{ $aIdx }}][date]" value="{{ $date->format('Y-m-d') }}">
                                        <input type="hidden" name="assignments[{{ $aIdx }}][role_id]" value="{{ $req->role_id }}">
                                        <input type="hidden" name="assignments[{{ $aIdx }}][slot_index]" value="{{ $slot }}">
                                        <select name="assignments[{{ $aIdx }}][user_id]" class="flex-1 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none py-2 text-sm">
                                            <option value="">— Unassigned —</option>
                                            @foreach($staff as $u)
                                                <option value="{{ $u->id }}" {{ $assigned && $assigned->user_id == $u->id ? 'selected' : '' }}>
                                                    {{ $u->name }} ({{ $u->staff_code }})@if($u->jobRole) - {{ $u->jobRole->name }}@endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @php $aIdx++; @endphp
                                @endfor
                            @endforeach
                            @if($dayReqs->isEmpty())
                                <p class="text-gray-400 text-sm col-span-full">Set requirements above for this day.</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6">
                <button type="submit" class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl shadow-lg transition-all">Save Assignments</button>
            </div>
        </form>
    </div>
    @endif
@endsection
