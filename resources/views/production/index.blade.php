@extends('layouts.app')

@section('header')
    <h1>Daily Production</h1>
@endsection

@section('actions')
    <a href="{{ route('production.create') }}" class="btn btn-primary">
        <i data-lucide="plus"></i> New Production Day
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Tasks</th>
                        <th>Recipes</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productionDays as $day)
                        <tr>
                            <td>
                                <div style="font-weight: 500;">{{ $day->date->format('M d, Y') }}</div>
                                <div class="text-muted text-sm">{{ $day->date->format('l') }}</div>
                            </td>
                            <td>
                                {{ $day->completed_tasks_count }} / {{ $day->total_tasks_count }}
                                <div
                                    style="width: 100px; height: 4px; background: #e5e7eb; border-radius: 2px; margin-top: 4px;">
                                    <div
                                        style="width: {{ $day->progress_percentage }}%; height: 100%; background: var(--primary-color); border-radius: 2px;">
                                    </div>
                                </div>
                            </td>
                            <td>{{ $day->items->count() }} Items</td>
                            <td>
                                @if($day->progress_percentage == 100 && $day->total_tasks_count > 0)
                                    <span class="badge badge-success">Completed</span>
                                @elseif($day->date->isToday())
                                    <span class="badge badge-warning">Active</span>
                                @elseif($day->date->isPast())
                                    <span class="badge badge-gray">Past</span>
                                @else
                                    <span class="badge badge-gray">Scheduled</span>
                                @endif
                            </td>
                            <td class="text-muted text-sm">{{ Str::limit($day->notes, 30) }}</td>
                            <td>
                                <div class="flex gap-2">
                                    <a href="{{ route('production.show', $day) }}" class="btn btn-sm btn-secondary">Manage</a>
                                    @can('delete', $day)
                                        <form action="{{ route('production.destroy', $day) }}" method="POST"
                                            onsubmit="return confirm('Delete this production day?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm" style="color: var(--danger-color);">
                                                <i data-lucide="trash-2" style="width: 14px;"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                No production days planned.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $productionDays->links() }}
        </div>
    </div>
@endsection