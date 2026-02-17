@extends('layouts.app')

@section('header')
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Leave Management</h1>
            <p class="text-gray-500 mt-1">Request time off and track your approval status.</p>
        </div>
        <a href="{{ route('employee.leave.create') }}" 
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg transform hover:scale-105 transition-all text-sm">
            <i data-lucide="plus-circle" class="w-5 h-5"></i>
            <span>Request Leave</span>
        </a>
    </div>
@endsection

@section('content')
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="p-6 font-semibold">Dates</th>
                    <th class="p-6 font-semibold">Type</th>
                    <th class="p-6 font-semibold">Reason</th>
                    <th class="p-6 font-semibold">Status</th>
                    <th class="p-6 font-semibold text-right">Submitted</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($leaves as $leave)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="p-6">
                            <div class="font-bold text-gray-800">
                                {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ $leave->start_date->diffInDays($leave->end_date) + 1 }} Days
                            </div>
                        </td>
                        <td class="p-6">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $leave->type === 'sick' ? 'bg-red-100 text-red-800' : ($leave->type === 'casual' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                                {{ ucfirst($leave->type) }}
                            </span>
                        </td>
                        <td class="p-6">
                            <p class="text-sm text-gray-600 max-w-xs truncate" title="{{ $leave->reason }}">{{ $leave->reason }}</p>
                        </td>
                        <td class="p-6">
                            @if($leave->status === 'pending')
                                <span class="bg-yellow-100 text-yellow-700 text-[10px] font-bold px-2 py-1 rounded-full uppercase">Pending</span>
                            @elseif($leave->status === 'approved')
                                <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded-full uppercase">Approved</span>
                            @else
                                <span class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-1 rounded-full uppercase">Rejected</span>
                            @endif
                        </td>
                        <td class="p-6 text-right text-xs text-gray-400">
                            {{ $leave->created_at->diffForHumans() }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($leaves->isEmpty())
            <div class="p-20 text-center">
                <i data-lucide="calendar-off" class="w-16 h-16 text-gray-200 mx-auto mb-4"></i>
                <p class="text-gray-500 font-bold">No leave requests found.</p>
                <a href="{{ route('employee.leave.create') }}" class="text-blue-600 hover:underline text-sm font-bold mt-2 inline-block">Request your first leave</a>
            </div>
        @endif
    </div>
@endsection
