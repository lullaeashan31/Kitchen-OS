@extends('layouts.app')

@section('header')
    <h2 class="text-xl font-semibold">SOP Review Dashboard</h2>
    <p class="text-sm text-gray-500">Review and approve daily SOP checklist runs</p>
@endsection

@section('content')
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4 font-bold">Date</th>
                        <th class="px-6 py-4 font-bold">Checklist</th>
                        <th class="px-6 py-4 font-bold">Staff</th>
                        <th class="px-6 py-4 font-bold">Progress</th>
                        <th class="px-6 py-4 font-bold">Status</th>
                        <th class="px-6 py-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($runs as $run)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium">{{ $run->date->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $run->checklist->name }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm">{{ $run->user->name }}</td>
                            <td class="px-6 py-4 text-sm">
                                {{ $run->completions->count() }} / {{ $run->checklist->items->count() }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-bold {{ $run->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ ucfirst($run->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex flex-col items-end gap-1.5">
                                    <a href="{{ route('admin.sop.reviews.show', $run) }}"
                                        class="text-blue-600 hover:text-blue-800 font-bold text-sm flex items-center gap-1">
                                        <i data-lucide="eye" class="w-4 h-4"></i> Review Run
                                    </a>

                                    @if($run->status !== 'approved')
                                        <form action="{{ route('admin.sop.reviews.approve_run', $run) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="text-emerald-600 hover:text-emerald-800 font-bold text-sm flex items-center gap-1">
                                                <i data-lucide="check-circle" class="w-4 h-4"></i> Approve
                                            </button>
                                        </form>

                                        <a href="{{ route('admin.sop.reviews.show', $run) }}"
                                            class="text-rose-600 hover:text-rose-800 font-bold text-sm flex items-center gap-1">
                                            <i data-lucide="x-circle" class="w-4 h-4"></i> Reject
                                        </a>
                                    @endif

                                    <a href="{{ route('admin.sop.reviews.show', $run) }}"
                                        class="text-gray-400 hover:text-gray-600 text-xs">
                                        View Details
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">No runs found for review.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($runs->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $runs->links() }}
            </div>
        @endif
    </div>
@endsection