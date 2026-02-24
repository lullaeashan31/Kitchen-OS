@extends('layouts.app')

@section('header')
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">My Schedule</h1>
        <p class="text-gray-500 mt-1">View your upcoming shifts and weekly offs.</p>
    </div>
@endsection

@section('content')
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-7 divide-y md:divide-y-0 md:divide-x divide-gray-100">
            @php
                $startDate = today();
            @endphp
            @foreach(range(0, 6) as $i)
                @php
                    $date = $startDate->copy()->addDays($i);
                    $assignment = $assignments->get($date->toDateString());
                @endphp
                <div class="p-6 {{ $date->isToday() ? 'bg-blue-50/50' : '' }}">
                    <div
                        class="text-xs font-bold uppercase tracking-widest {{ $date->isToday() ? 'text-blue-600' : 'text-gray-400' }} mb-1">
                        {{ $date->format('D') }}
                    </div>
                    <div class="text-xl font-black text-gray-900 mb-4">{{ $date->format('d M') }}</div>

                    @if($assignment)
                        <div class="bg-white p-3 rounded-2xl border border-blue-100 shadow-sm">
                            <div class="text-sm font-bold text-blue-900">{{ $assignment->role->name ?? 'Scheduled' }}</div>
                            <div class="text-[10px] text-blue-500 mt-1 uppercase font-bold tracking-tighter">
                                Slot #{{ $assignment->slot_index }}
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 p-3 rounded-2xl border border-dashed border-gray-200">
                            <div class="text-xs font-bold text-gray-400">Weekly Off / Not Scheduled</div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-8 bg-amber-50 rounded-2xl p-6 border border-amber-100 flex items-start gap-4">
        <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center text-white shrink-0">
            <i data-lucide="info" class="w-6 h-6"></i>
        </div>
        <div>
            <h4 class="font-bold text-amber-900">Shift Policy</h4>
            <p class="text-sm text-amber-700 mt-1">Please be present at the kitchen at least 15 minutes before your shift
                starts. All shifts require GPS-verified clock-in via the Time Clock tablet.</p>
        </div>
    </div>
@endsection