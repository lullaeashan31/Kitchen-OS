@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800">Daily Shift Assignments</h2>
        <div class="flex items-center gap-3">
            <input type="date" id="date-picker" value="{{ $date }}"
                class="rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm"
                onchange="window.location.href='?date='+this.value">
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl mb-6 flex items-start gap-3">
            <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5"></i>
            <p class="text-sm text-blue-800">Assign staff to shifts for
                <strong>{{ \Carbon\Carbon::parse($date)->format('l, F d') }}</strong>. Staff will only see checklists linked
                to their assigned shifts.</p>
        </div>

        <form action="{{ route('admin.shifts.assignments.store') }}" method="POST">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="grid gap-6">
                @foreach($shifts as $shift)
                    <div
                        class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between gap-6">
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900">{{ $shift->name }}</h3>
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mt-0.5">
                                {{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} -
                                {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }}
                            </p>
                        </div>

                        <div class="w-72">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Assigned Staff</label>
                            <select name="assignments[{{ $shift->id }}]"
                                class="w-full rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Unassigned --</option>
                                @foreach($users as $user)
                                    @php
                                        $assignedUserId = $assignments->get($shift->id)?->first()?->user_id;
                                    @endphp
                                    <option value="{{ $user->id }}" {{ $assignedUserId == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->staff_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit"
                    class="px-8 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 shadow-md transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Save Assignments
                </button>
            </div>
        </form>
    </div>
@endsection