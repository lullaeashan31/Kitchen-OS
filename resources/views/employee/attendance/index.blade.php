@extends('layouts.app')

@section('header')
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">My Attendance</h1>
            <p class="text-gray-500 mt-1">Review your shift logs and working hours.</p>
        </div>
        <div class="flex gap-2">
            <form method="GET" class="flex gap-2 items-center bg-white p-2 rounded-xl shadow-sm border border-gray-100">
                <select name="month" class="bg-transparent font-bold text-gray-700 outline-none px-2">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endforeach
                </select>
                <select name="year" class="bg-transparent font-bold text-gray-700 outline-none px-2 border-l border-gray-100">
                    @foreach(range(date('Y')-1, date('Y')+1) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Days Worked</div>
            <div class="text-4xl font-black text-gray-800">{{ $daysWorked }}</div>
            <div class="text-xs text-gray-500 mt-1">This month</div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Avg. Shift Length</div>
            <div class="text-4xl font-black text-blue-600">{{ $averageHours }} <span class="text-lg">hrs</span></div>
            <div class="text-xs text-gray-500 mt-1">Based on closed sessions</div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Weekly Off</div>
            <div class="text-2xl font-black text-orange-500 uppercase">{{ auth()->user()->weekly_off_day ?? 'Not Set' }}</div>
            <div class="text-xs text-gray-500 mt-1">Fixed weekly rest day</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="p-6 font-semibold">Date</th>
                    <th class="p-6 font-semibold">Clock In</th>
                    <th class="p-6 font-semibold">Clock Out</th>
                    <th class="p-6 font-semibold">Duration</th>
                    <th class="p-6 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($attendances as $att)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="p-6">
                            <div class="font-bold text-gray-800">{{ $att->clock_in_time->format('D, M d') }}</div>
                        </td>
                        <td class="p-6">
                            <div class="text-gray-700 font-medium">{{ $att->clock_in_time->format('H:i') }}</div>
                        </td>
                        <td class="p-6">
                            @if($att->clock_out_time)
                                <div class="text-gray-700 font-medium">{{ $att->clock_out_time->format('H:i') }}</div>
                            @else
                                <span class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">On Shift</span>
                            @endif
                        </td>
                        <td class="p-6">
                            @if($att->clock_out_time)
                                <div class="text-sm text-gray-600">
                                    {{ $att->clock_in_time->diff($att->clock_out_time)->format('%h hrs %i min') }}
                                </div>
                            @else
                                <div class="text-sm text-gray-400 italic">Ongoing</div>
                            @endif
                        </td>
                        <td class="p-6">
                            @if($att->status === 'success')
                                <span class="text-green-600 flex items-center gap-1 font-bold text-sm">
                                    <i data-lucide="check-circle" class="w-4 h-4"></i> Verified
                                </span>
                            @else
                                <span class="text-red-500 flex items-center gap-1 font-bold text-sm">
                                    <i data-lucide="x-circle" class="w-4 h-4"></i> {{ ucfirst($att->status) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($attendances->isEmpty())
            <div class="p-20 text-center">
                <i data-lucide="calendar-x" class="w-16 h-16 text-gray-200 mx-auto mb-4"></i>
                <p class="text-gray-500 font-bold">No attendance logs for this period.</p>
            </div>
        @endif
    </div>
@endsection
