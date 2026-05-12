@extends('layouts.app')

@section('header')
    <h1>Dashboard</h1>
@endsection

@section('content')
    <div class="flex gap-4" style="flex-wrap: wrap; margin-bottom: 2rem;">
        @if(auth()->user()->isStaff())
            <!-- STAFF VIEW: Big Active Cards -->
            @if($todaysProduction)
                <a href="{{ route('production.show', $todaysProduction) }}"
                    style="text-decoration: none; flex: 1; min-width: 300px;">
                    <div class="card"
                        style="transition: transform 0.2s; border: 1px solid var(--accent);">
                        <div class="flex items-center gap-3 mb-2">
                            <i data-lucide="chef-hat" style="width: 32px; height: 32px;"></i>
                            <h2 style="margin: 0; font-size: 1.5rem;">Today's Production</h2>
                        </div>
                        <div style="opacity: 0.9;">{{ $todaysProduction->items->count() }} Recipes Scheduled</div>
                    </div>
                </a>
            @else
                <div class="card flex-1 min-w-[300px]" style="border-style: dashed;">
                    <h3 style="margin: 0;">Nothing scheduled for today</h3>
                </div>
            @endif

            <div class="card flex-1 min-w-[300px]">
                <div class="flex items-center justify-between mb-2">
                    <h3 style="margin: 0;">My Active Tasks</h3>
                    <span class="badge"
                        style="font-size: 1rem; padding: 0.5rem 1rem;">{{ $myTasks->count() }}</span>
                </div>
                @if($myTasks->isEmpty())
                    <p class="text-muted">All caught up! 🎉</p>
                @else
                    <ul style="list-style: none; margin-top: 0.5rem;">
                        @foreach($myTasks->take(3) as $task)
                            <li style="border-bottom: 1px solid var(--border-subtle); padding: 0.5rem 0; font-size: 0.9rem;">
                                {{ $task->title }} <span
                                    class="text-muted text-xs">({{ $task->productionDay->date->format('M d') }})</span>
                            </li>
                        @endforeach
                        @if($myTasks->count() > 3)
                            <li class="text-sm text-muted mt-2">...and {{ $myTasks->count() - 3 }} more</li>
                        @endif
                    </ul>
                @endif
            </div>

        @else
            <!-- ADMIN/MANAGER VIEW: Stats -->
            <div class="card flex-1 min-w-[200px]">
                <div class="text-muted text-sm uppercase">Total Recipes</div>
                <div style="font-size: 2.5rem; font-weight: 700; color: var(--accent);">{{ $stats['total_recipes'] }}</div>
            </div>
            <div class="card flex-1 min-w-[200px]">
                <div class="text-muted text-sm uppercase">Pending Drafts</div>
                <div style="font-size: 2.5rem; font-weight: 700; color: var(--accent);">{{ $stats['pending_drafts'] }}</div>
                <div class="text-sm text-muted">My Drafts: {{ $stats['my_drafts'] }}</div>
            </div>
            <div class="card flex-1 min-w-[200px]">
                <div class="text-muted text-sm uppercase">Production Days (This Month)</div>
                <div style="font-size: 2.5rem; font-weight: 700; color: var(--accent);">
                    {{ $stats['production_days_this_month'] }}
                </div>
            </div>
            <div class="card flex-1 min-w-[200px]">
                <div class="text-muted text-sm uppercase">My Pending Tasks</div>
                <div style="font-size: 2rem; font-weight: 700;">{{ $stats['pending_tasks'] }}</div>
            </div>
            <div class="card flex-1 min-w-[200px]">
                <div class="text-muted text-sm uppercase">Pending Onboardings</div>
                <div style="font-size: 2rem; font-weight: 700;">{{ $stats['pending_onboarding_count'] }}</div>
                <a href="{{ route('admin.staff.index') }}" class="text-xs hover:underline">View All</a>
            </div>
            <div class="card flex-1 min-w-[200px]">
                <div class="text-muted text-sm uppercase">Leave Requests</div>
                <div style="font-size: 2rem; font-weight: 700;">{{ $stats['pending_leaves_count'] }}</div>
                <a href="{{ route('admin.leave.index') }}" class="text-xs hover:underline">Manage</a>
            </div>
        @endif
    </div>

    <div class="flex gap-4" style="flex-wrap: wrap;">
        <div style="flex: 2; min-width: 400px;">
            <div class="card">
                <h3>Recent Production Activity</h3>
                <div class="table-container" style="margin-top: 1rem;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivity as $day)
                                <tr>
                                    <td>
                                        <div>{{ $day->date->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ $day->date->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        @if($day->progress_percentage == 100 && $day->total_tasks_count > 0)
                                            <span class="badge badge-success">Completed</span>
                                        @elseif($day->date->isToday())
                                            <span class="badge badge-warning">Active</span>
                                        @else
                                            <span class="badge badge-gray">Scheduled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div style="flex: 1; height: 6px; background: var(--border-subtle); border-radius: 3px;">
                                                <div
                                                    style="width: {{ $day->progress_percentage }}%; height: 100%; background: var(--accent); border-radius: 3px;">
                                                </div>
                                            </div>
                                            <span class="text-xs">{{ $day->progress_percentage }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('production.show', $day) }}" class="btn btn-sm btn-secondary">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted text-center p-4">No recent activity.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div style="flex: 1; min-width: 300px;">
            <div class="card">
                <h3>Quick Actions</h3>
                <div class="flex flex-col gap-2 mt-4">
                    <a href="{{ route('recipes.create') }}" class="btn btn-secondary w-full"
                        style="justify-content: flex-start;">
                        <i data-lucide="plus-circle"></i> Create Recipe
                    </a>
                    <a href="{{ route('production.create') }}" class="btn btn-secondary w-full"
                        style="justify-content: flex-start;">
                        <i data-lucide="calendar"></i> Plan Production
                    </a>
                    <a href="{{ route('excel.import_form') }}" class="btn btn-secondary w-full"
                        style="justify-content: flex-start;">
                        <i data-lucide="upload"></i> Import Data
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection