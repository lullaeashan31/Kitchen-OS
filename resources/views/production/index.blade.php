@extends('layouts.app')

@section('header')
    <div>
        <h1 class="text-2xl md:text-3xl font-black tracking-tight text-primary">Daily Production</h1>
        <p class="text-xs md:text-sm text-muted font-medium mt-1 uppercase tracking-widest">Managing <span class="text-accent">Kitchen Throughput</span></p>
    </div>
@endsection

@section('actions')
    <a href="{{ route('production.create') }}" class="btn btn-primary">
        <i data-lucide="plus"></i> New Production Day
    </a>
@endsection

@section('content')
    <div class="card p-0 overflow-hidden">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Tasks</th>
                        <th class="px-6 py-4 text-center">Recipes</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Notes</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-subtle">
                    @forelse($productionDays as $day)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-primary">{{ $day->date->format('M d, Y') }}</div>
                                <div class="text-[10px] font-bold text-muted uppercase tracking-widest">{{ $day->date->format('l') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="text-[10px] font-bold text-muted uppercase tracking-widest whitespace-nowrap">
                                        {{ $day->completed_tasks_count }} / {{ $day->total_tasks_count }}
                                    </div>
                                    <div class="w-24 h-1.5 bg-white/10 rounded-full overflow-hidden">
                                        <div class="h-full bg-accent transition-all duration-500" style="width: {{ $day->progress_percentage }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 bg-primary/40 rounded-full text-[10px] font-bold text-accent uppercase tracking-widest border border-accent/20">
                                    {{ $day->items->count() }} Items
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($day->progress_percentage == 100 && $day->total_tasks_count > 0)
                                    <span class="badge badge-success">Completed</span>
                                @elseif($day->date->isToday())
                                    <span class="badge badge-warning">Active</span>
                                @elseif($day->date->isPast())
                                    <span class="badge badge-secondary">Past</span>
                                @else
                                    <span class="badge badge-secondary">Scheduled</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-[10px] font-bold text-muted uppercase tracking-widest">{{ Str::limit($day->notes, 30) ?: '—' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('production.show', $day) }}" class="btn btn-secondary btn-sm px-4">Manage</a>
                                    @can('delete', $day)
                                        <form action="{{ route('production.destroy', $day) }}" method="POST"
                                            onsubmit="return confirm('Delete this production day?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-secondary btn-sm p-2 text-red-400 hover:text-red-300">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <i data-lucide="calendar-x" class="w-12 h-12 text-muted opacity-20"></i>
                                    <p class="text-[10px] font-bold text-muted uppercase tracking-widest">No production days planned</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($productionDays->hasPages())
            <div class="p-6 border-t border-subtle">
                {{ $productionDays->links() }}
            </div>
        @endif
    </div>
@endsection