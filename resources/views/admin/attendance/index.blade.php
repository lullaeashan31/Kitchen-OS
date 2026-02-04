@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-800 tracking-tight">Attendance Report</h1>
            <p class="text-sm text-gray-500 mt-1">Daily shift logs and location verification</p>
        </div>
        <div class="flex gap-2">
            <form method="GET" class="flex gap-2 items-center">
                <input type="date" name="date" value="{{ $date }}" 
                       class="px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                <button type="submit" class="btn btn-secondary">
                    <i data-lucide="filter" class="w-4 h-4"></i> Filter
                </button>
            </form>
        </div>
    </div>
@endsection

@section('content')
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="p-5 font-semibold text-gray-600 text-sm uppercase tracking-wider">Staff Member</th>
                        <th class="p-5 font-semibold text-gray-600 text-sm uppercase tracking-wider">Clock In</th>
                        <th class="p-5 font-semibold text-gray-600 text-sm uppercase tracking-wider">Clock Out</th>
                        <th class="p-5 font-semibold text-gray-600 text-sm uppercase tracking-wider">Location</th>
                        <th class="p-5 font-semibold text-gray-600 text-sm uppercase tracking-wider">Status</th>
                        <th class="p-5 font-semibold text-gray-600 text-sm uppercase tracking-wider">Evidence</th>
                        <th class="p-5 font-semibold text-gray-600 text-sm uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($attendances as $attendance)
                        <tr class="hover:bg-blue-50/50 transition-colors">
                            <td class="p-5">
                                <div class="font-bold text-gray-800">{{ $attendance->user->name ?? 'Unknown' }}</div>
                                <div class="text-xs font-mono text-gray-400 bg-gray-100 inline-block px-1.5 py-0.5 rounded mt-1">
                                    {{ $attendance->staff_code }}
                                </div>
                            </td>
                            <td class="p-5">
                                <div class="font-medium text-gray-700">{{ $attendance->clock_in_time->format('H:i') }}</div>
                                <div class="text-xs text-gray-400">{{ $attendance->clock_in_time->format('M d') }}</div>
                            </td>
                            <td class="p-5">
                                @if($attendance->clock_out_time)
                                    <div class="font-medium text-gray-700">{{ $attendance->clock_out_time->format('H:i') }}</div>
                                @else
                                    <span class="text-xs font-bold text-amber-500 bg-amber-50 px-2 py-1 rounded-full animate-pulse">
                                        On Shift
                                    </span>
                                @endif
                            </td>
                            <td class="p-5 text-sm">
                                <div class="flex items-center gap-1 text-gray-500">
                                    <i data-lucide="map-pin" class="w-3 h-3"></i>
                                    {{ number_format($attendance->gps_latitude_in, 4) }}, {{ number_format($attendance->gps_longitude_in, 4) }}
                                </div>
                            </td>
                            <td class="p-5">
                                @if($attendance->status === 'success')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i> Verified
                                    </span>
                                @elseif($attendance->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                        <i data-lucide="x-circle" class="w-3 h-3"></i> Rejected
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-5">
                                <div class="flex gap-2">
                                    @if($attendance->selfie_path_in)
                                        <a href="{{ $attendance->selfie_path_in }}" target="_blank" 
                                           class="p-1.5 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition-colors" title="In Selfie">
                                            <i data-lucide="camera" class="w-4 h-4"></i>
                                        </a>
                                    @endif
                                    @if($attendance->selfie_path_out)
                                        <a href="{{ $attendance->selfie_path_out }}" target="_blank" 
                                           class="p-1.5 bg-purple-50 text-purple-600 rounded hover:bg-purple-100 transition-colors" title="Out Selfie">
                                            <i data-lucide="camera" class="w-4 h-4"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="p-5 text-right">
                                <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="{{ $attendance->status === 'success' ? 'rejected' : 'success' }}">
                                    @if($attendance->status === 'success')
                                        <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800 hover:underline">
                                            Reject
                                        </button>
                                    @else
                                        <button type="submit" class="text-xs font-bold text-green-600 hover:text-green-800 hover:underline">
                                            Approve
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($attendances->isEmpty())
            <div class="p-12 text-center text-gray-500">
                <i data-lucide="calendar-off" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
                <p>No attendance records found for this date.</p>
            </div>
        @endif
    </div>
@endsection