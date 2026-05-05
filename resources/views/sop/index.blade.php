@extends('layouts.app')

@section('header')
    <h2 class="text-xl font-semibold">My SOP Checklists</h2>
    <p class="text-sm text-gray-500">
        {{ auth()->user()->shift ? ucfirst(auth()->user()->shift) : 'No' }} Shift |
        {{ auth()->user()->role->label() }}
    </p>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($checklists as $checklist)
            @php
                $run = $checklist->todayRun;
                $hasRejections = $run && $run->completions()->where('status', 'rejected')->exists();
                $isApproved = $run && $run->status === 'approved';
                $isSubmitted = $run && $run->completed_at && $run->status !== 'approved' && !$hasRejections;
                $isDone = $isApproved || $isSubmitted;
            @endphp
            <div
                class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden {{ $isApproved || $isSubmitted ? 'opacity-75' : '' }}">
                <div class="p-5">
                    <div class="flex justify-between items-start mb-3">
                        <span
                            class="px-2 py-0.5 rounded-full text-xs font-bold 
                            {{ $isApproved ? 'bg-green-100 text-green-700' : ($isSubmitted ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                            @if($isApproved)
                                Completed
                            @elseif($hasRejections)
                                <span class="text-red-600">Needs Attention</span>
                            @elseif($isSubmitted)
                                Submitted
                            @else
                                Today's Task
                            @endif
                        </span>
                        @if($checklist->assignments->isNotEmpty())
                            <span class="text-xs text-gray-500 flex items-center gap-1">
                                <i data-lucide="clock" class="w-3 h-3"></i>
                                By {{ Carbon\Carbon::parse($checklist->assignments->first()->deadline_time)->format('g:i A') }}
                            </span>
                        @endif
                    </div>

                    <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $checklist->name }}</h3>
                    <p class="text-sm text-gray-500 mb-4">{{ Str::limit($checklist->description, 80) }}</p>

                    <div class="flex items-center justify-between mt-4">
                        <span class="text-xs font-medium text-gray-500">
                            {{ $checklist->items_count }} Steps to complete
                        </span>
                        @if($isApproved)
                            <div class="text-green-600 flex items-center gap-1 text-sm font-bold">
                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                Done
                            </div>
                        @elseif($isSubmitted)
                            <div class="text-amber-600 flex items-center gap-1 text-sm font-bold">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                Review Pending
                            </div>
                        @else
                            <a href="{{ route('sop.execute', $checklist) }}"
                                class="px-4 py-2 bg-primary text-white text-sm font-bold rounded-lg shadow-md shadow-blue-100 hover:bg-blue-600 transition-colors">
                                Start Checklist
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center bg-white rounded-xl border border-gray-200">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="clipboard-check" class="w-8 h-8 text-gray-300"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">No Checklists Assigned</h3>
                <p class="text-gray-500">You don't have any checklists assigned to your current shift or role today.</p>
            </div>
        @endforelse
    </div>
@endsection