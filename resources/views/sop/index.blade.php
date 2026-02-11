@extends('layouts.app')

@section('header')
    <h1 class="text-2xl font-bold text-gray-800">SOP Checklists</h1>
    <p class="text-sm text-gray-500">Daily operational checklists for today: {{ date('D, M d Y') }}.</p>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($checklists as $checklist)
            @php
                $status = 'pending';
                if ($checklist->todayLog) {
                    $itemCount = $checklist->items->count();
                    $completedCount = $checklist->todayLog->itemLogs->where('is_completed', true)->count();
                    $status = ($completedCount >= $itemCount && $itemCount > 0) ? 'completed' : 'in_progress';
                }
                $deadline = $checklist->deadline_time ? \Carbon\Carbon::parse($checklist->deadline_time) : null;
                $isLate = $deadline && now()->gt($deadline) && $status !== 'completed';
            @endphp

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide
                                    {{ $checklist->shift === 'morning' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $checklist->shift === 'closing' ? 'bg-gray-100 text-gray-700' : '' }}
                                    {{ $checklist->shift === 'mid' ? 'bg-blue-100 text-blue-700' : '' }}">
                            {{ ucfirst($checklist->shift) }}
                        </span>
                        @if($status === 'completed')
                            <i data-lucide="check-circle" class="text-green-500 w-6 h-6"></i>
                        @elseif($isLate)
                            <i data-lucide="alert-circle" class="text-red-500 w-6 h-6" title="Late!"></i>
                        @else
                            <i data-lucide="circle" class="text-gray-300 w-6 h-6"></i>
                        @endif
                    </div>

                    <h3 class="text-lg font-bold text-gray-800 mb-2">{{ $checklist->name }}</h3>

                    @if($checklist->deadline_time)
                        <div
                            class="text-sm {{ $isLate ? 'text-red-600 font-bold' : 'text-gray-500' }} mb-4 flex items-center gap-1">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                            Deadline: {{ \Carbon\Carbon::parse($checklist->deadline_time)->format('h:i A') }}
                        </div>
                    @endif

                    <div class="w-full bg-gray-100 rounded-full h-2 mb-4">
                        @php
                            $perc = 0;
                            if ($checklist->todayLog && $checklist->items->count() > 0) {
                                $perc = ($checklist->todayLog->itemLogs->where('is_completed', true)->count() / $checklist->items->count()) * 100;
                            }
                        @endphp
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $perc }}%"></div>
                    </div>

                    <a href="{{ route('sop.execute', $checklist) }}"
                        class="block w-full text-center py-2 rounded-lg border 
                                {{ $status === 'completed' ? 'border-green-200 bg-green-50 text-green-700' : 'border-blue-200 bg-blue-50 text-blue-700 host:bg-blue-100' }}">
                        {{ $status === 'completed' ? 'Review Checklist' : 'Start Checklist' }}
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endsection