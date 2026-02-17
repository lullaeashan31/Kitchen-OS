@extends('layouts.app')

@section('header')
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Leave Approvals</h1>
        <p class="text-gray-500 mt-1">Review and manage staff time-off requests.</p>
    </div>
@endsection

@section('content')
    <div class="space-y-12">
        <!-- Pending Requests -->
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i data-lucide="clock" class="w-6 h-6 text-amber-500"></i>
                Pending Requests ({{ $pendingLeaves->count() }})
            </h2>

            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                            <th class="p-6 font-semibold">Staff</th>
                            <th class="p-6 font-semibold">Dates</th>
                            <th class="p-6 font-semibold">Type</th>
                            <th class="p-6 font-semibold">Reason</th>
                            <th class="p-6 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($pendingLeaves as $leave)
                            <tr class="hover:bg-amber-50/30 transition-colors">
                                <td class="p-6">
                                    <div class="font-bold text-gray-900">{{ $leave->user->name }}</div>
                                    <div class="text-xs text-gray-400">#{{ $leave->user->staff_code }}</div>
                                </td>
                                <td class="p-6">
                                    <div class="font-bold text-gray-800">
                                        {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $leave->start_date->diffInDays($leave->end_date) + 1 }} Days
                                    </div>
                                </td>
                                <td class="p-6">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $leave->type === 'sick' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($leave->type) }}
                                    </span>
                                </td>
                                <td class="p-6">
                                    <p class="text-sm text-gray-600 max-w-xs truncate" title="{{ $leave->reason }}">
                                        {{ $leave->reason }}</p>
                                </td>
                                <td class="p-6 text-right">
                                    <div class="flex justify-end gap-2">
                                        <form action="{{ route('admin.leave.approve', $leave->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="p-2 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 border border-green-200"
                                                title="Approve">
                                                <i data-lucide="check" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.leave.reject', $leave->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 border border-red-200"
                                                title="Reject">
                                                <i data-lucide="x" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($pendingLeaves->isEmpty())
                    <div class="p-12 text-center text-gray-400">
                        <i data-lucide="smile" class="w-12 h-12 mx-auto mb-2 opacity-20"></i>
                        <p>No pending leave requests!</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- History -->
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i data-lucide="history" class="w-6 h-6 text-gray-400"></i>
                Request History
            </h2>

            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                            <th class="p-6 font-semibold">Staff</th>
                            <th class="p-6 font-semibold">Dates</th>
                            <th class="p-6 font-semibold">Type</th>
                            <th class="p-6 font-semibold">Status</th>
                            <th class="p-6 font-semibold">Processed By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($historyLeaves as $leave)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-6">
                                    <div class="font-bold text-gray-700">{{ $leave->user->name }}</div>
                                </td>
                                <td class="p-6">
                                    <div class="text-sm text-gray-600">
                                        {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}
                                    </div>
                                </td>
                                <td class="p-6 text-sm text-gray-500">
                                    {{ ucfirst($leave->type) }}
                                </td>
                                <td class="p-6">
                                    @if($leave->status === 'approved')
                                        <span class="text-green-600 text-[10px] font-bold uppercase flex items-center gap-1">
                                            <i data-lucide="check" class="w-3 h-3"></i> Approved
                                        </span>
                                    @else
                                        <span class="text-red-500 text-[10px] font-bold uppercase flex items-center gap-1">
                                            <i data-lucide="x" class="w-3 h-3"></i> Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="p-6">
                                    <div class="text-sm text-gray-500">{{ $leave->approver->name ?? 'System' }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $leave->approved_at->format('M d, H:i') }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-6 border-t border-gray-50">
                    {{ $historyLeaves->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection