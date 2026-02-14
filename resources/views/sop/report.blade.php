@extends('layouts.app')

@section('header')
    <h2 class="text-xl font-semibold">SOP Execution Report</h2>
    <p class="text-sm text-gray-500">View daily checklist completions for
        {{ Carbon\Carbon::parse($date)->format('M d, Y') }}</p>
@endsection

@section('content')
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-bottom border-gray-100 bg-gray-50">
            <form action="{{ route('sop.report') }}" method="GET" class="flex items-center gap-4">
                <input type="date" name="date" value="{{ $date }}" class="px-4 py-2 border rounded-lg outline-none">
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg font-bold">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4 font-bold">Checklist</th>
                        <th class="px-6 py-4 font-bold">Assigned To</th>
                        <th class="px-6 py-4 font-bold">Status</th>
                        <th class="px-6 py-4 font-bold">Progress</th>
                        <th class="px-6 py-4 font-bold">Completed At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($runs as $run)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $run->checklist->name }}</td>
                            <td class="px-6 py-4">{{ $run->user->name }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-bold {{ $run->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ ucfirst($run->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                {{ $run->completions->count() }} / {{ $run->checklist->items->count() }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $run->completed_at ? $run->completed_at->format('g:i A') : 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">No data found for this date.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection