@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-2">
        <a href="{{ route('production.index') }}" class="text-muted"><i data-lucide="arrow-left"></i></a>
        <h1>Production: {{ $productionDay->date->format('D, M d Y') }}</h1>
    </div>
@endsection

@section('actions')
    <div class="flex gap-2">
        <a href="{{ route('production.print', $productionDay) }}" target="_blank" class="btn btn-secondary">
            <i data-lucide="printer"></i> Print Sheet
        </a>
    </div>
@endsection

@section('content')
    <div class="flex gap-4" style="flex-wrap: wrap;">
        <!-- Left Column: Production Items & Ingredients -->
        <div style="flex: 2; min-width: 400px;">
            <!-- Recipes List -->
            <div class="card">
                <div class="flex justify-between items-center mb-4" style="margin-bottom: 1rem;">
                    <h2>Production Items</h2>
                </div>

                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Recipe</th>
                                <th>Portions</th>
                                <th>Cost</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productionDay->items as $item)
                                <tr>
                                    <td>
                                        <a href="{{ route('recipes.show', $item->recipe) }}"
                                            style="color: var(--primary-color); text-decoration: none;">
                                            {{ $item->recipe->name }}
                                        </a>
                                    </td>
                                    <td>{{ $item->portions }}</td>
                                    <td>{{ number_format($item->total_cost, 2) }}</td>
                                    <td>
                                    <td>
                                        <form action="{{ route('production_items.status', $item) }}" method="POST">
                                            @csrf
                                            @php
                                                $statusColor = match($item->status) {
                                                    \App\Enums\ProductionStatus::Pending => '#e5e7eb', // gray
                                                    \App\Enums\ProductionStatus::InProgress => '#fef3c7', // amber
                                                    \App\Enums\ProductionStatus::Completed => '#dcfce7', // green
                                                };
                                            @endphp
                                            <select name="status" onchange="this.form.submit()" class="form-control" 
                                                style="padding: 8px 12px; font-size: 1rem; border: 1px solid #ccc; border-radius: 6px; background-color: {{ $statusColor }}; cursor: pointer; min-width: 140px;">
                                                @foreach(\App\Enums\ProductionStatus::cases() as $status)
                                                    <option value="{{ $status->value }}" {{ $item->status === $status ? 'selected' : '' }}>
                                                        {{ $status->label() }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @can('update', $productionDay)
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <form action="{{ route('production.add_item', $productionDay) }}" method="POST"
                            class="flex gap-2 items-end">
                            @csrf
                            <div style="flex: 2;">
                                <label class="form-label text-sm">Add Recipe</label>
                                <select name="recipe_id" class="form-control" required>
                                    <option value="">Select Recipe...</option>
                                    @foreach($recipes as $recipe)
                                        <option value="{{ $recipe->id }}">{{ $recipe->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="flex: 1;">
                                <label class="form-label text-sm">Portions</label>
                                <input type="number" name="portions" class="form-control" placeholder="Qty" required min="1">
                            </div>
                            <button type="submit" class="btn btn-secondary">Add</button>
                        </form>
                    </div>
                @endcan
            </div>

            <!-- Total Ingredients -->
            <div class="card">
                <h2>Total Ingredients Required</h2>
<!-- ... (unchanged) ... -->
            </div>
        </div>

        <!-- Right Column: Tasks & Files -->
        <div style="flex: 1; min-width: 300px;">
            <!-- Tasks Checklist -->
            <div class="card">
                <h2>Tasks Checklist</h2>
                <div id="taskList" style="margin-top: 1rem;">
                    @foreach($productionDay->tasks as $task)
                        <div
                            class="flex items-center justify-between p-2 hover:bg-gray-50 border-b border-gray-100 task-item {{ $task->isCompleted() ? 'opacity-50' : '' }}">
                            <div class="flex items-center gap-2">
                                <form
                                    action="{{ $task->isCompleted() ? route('tasks.reopen', $task) : route('tasks.complete', $task) }}"
                                    method="POST" style="display: inline;">
                                    @csrf
                                    <input type="checkbox" onchange="this.form.submit()" {{ $task->isCompleted() ? 'checked' : '' }} class="cursor-pointer">
                                </form>
                                <span style="{{ $task->isCompleted() ? 'text-decoration: line-through;' : '' }}">
                                    {{ $task->title }}
                                </span>
                            </div>
                            <div class="text-xs text-muted">
                                {{ $task->assignedUser->name ?? '' }}
                            </div>
                            @can('delete', $task)
                                <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                                    onsubmit="return confirm('Delete task?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-600">
                                        <i data-lucide="x" style="width: 12px;"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    @endforeach
                </div>

                @can('create', App\Models\Task::class)
                    <form action="{{ route('tasks.store') }}" method="POST" style="margin-top: 1rem;" class="flex gap-2 items-center">
                        @csrf
                        <input type="hidden" name="production_day_id" value="{{ $productionDay->id }}">
                        <div style="flex: 1;">
                            <input type="text" name="title" class="form-control" placeholder="New Task..." required>
                        </div>
                        <div style="width: 120px;">
                             <select name="assigned_to" class="form-control" style="font-size: 0.8rem;">
                                <option value="">Assign To...</option>
                                @foreach(\App\Models\User::all() as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-secondary">
                            <i data-lucide="plus"></i>
                        </button>
                    </form>
                @endcan
            </div>

            <!-- Drive Files (Production specific) -->
            <div class="card">
                <h3>Files / Photos</h3>
                <div style="margin-top: 1rem;">
                    @if($productionDay->driveFiles->isEmpty())
                        <div class="text-muted text-sm">No files attached.</div>
                    @else
                        @foreach($productionDay->driveFiles as $file)
                            <div class="flex justify-between items-center p-2 border rounded bg-white mb-2">
                                <a href="javascript:void(0)"
                                    onclick="openPreview('{{ $file->drive_url }}', '{{ $file->name }}')"
                                    class="flex items-center gap-2 text-sm text-blue-600 hover:underline">
                                    <i data-lucide="image"></i> {{ $file->name }}
                                </a>
                                @can('delete', $file)
                                    <form action="{{ route('drive_files.destroy', $file) }}" method="POST"
                                        onsubmit="return confirm('Remove file?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700">
                                            <i data-lucide="x" style="width: 14px;"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        @endforeach
                    @endif

                    @can('create', App\Models\DriveFile::class)
                        <form action="{{ route('drive_files.store') }}" method="POST" style="margin-top: 1rem;">
                            @csrf
                            <input type="hidden" name="linked_type" value="production">
                            <input type="hidden" name="linked_id" value="{{ $productionDay->id }}">
                            <input type="text" name="name" class="form-control mb-2" placeholder="Title" required
                                style="margin-bottom: 0.5rem; font-size: 0.8rem;">
                            <input type="url" name="drive_url" class="form-control mb-2" placeholder="Google Drive Link"
                                required style="margin-bottom: 0.5rem; font-size: 0.8rem;">
                            <button type="submit" class="btn btn-sm btn-secondary w-full">Attach</button>
                        </form>
                    @endcan
                </div>
            </div>

            @if($productionDay->notes)
                <div class="card">
                    <h3>Notes</h3>
                    <p class="text-muted text-sm mt-2">{{ $productionDay->notes }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection