@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-4">
        <a href="{{ route('production.index') }}" class="btn btn-secondary p-3">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-primary">Production Plan</h1>
            <p class="text-xs md:text-sm text-muted font-medium mt-1 uppercase tracking-widest">Date: <span class="text-accent">{{ $productionDay->date->format('l, M d, Y') }}</span></p>
        </div>
    </div>
@endsection

@section('actions')
    <div class="flex gap-3">
        <a href="{{ route('production.print', $productionDay) }}" target="_blank" class="btn btn-secondary">
            <i data-lucide="printer"></i> Print Sheet
        </a>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Production Items & Ingredients --}}
        <div class="lg:col-span-2 space-y-8">
            {{-- Production Items --}}
            <div class="card p-0 overflow-hidden">
                <div class="p-8 border-b border-subtle bg-white/5 flex justify-between items-center">
                    <h2 class="flex items-center gap-3">
                        <i data-lucide="utensils" class="text-accent"></i>
                        Production Items
                    </h2>
                </div>

                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="px-6 py-4">Recipe</th>
                                <th class="px-6 py-4 text-center">Portions</th>
                                <th class="px-6 py-4 text-right">Est. Cost</th>
                                <th class="px-6 py-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-subtle">
                            @foreach($productionDay->items as $item)
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-primary">{{ $item->recipe->name }}</div>
                                        <div class="text-[10px] font-bold text-muted uppercase tracking-widest">
                                            Yield: {{ $item->recipe->yield_portions ?? '—' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-black text-primary">{{ $item->portions }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-accent">₹{{ number_format($item->total_cost, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <form action="{{ route('production_items.status', $item) }}" method="POST" class="flex justify-center">
                                            @csrf
                                            @php
                                                $statusClass = match($item->status) {
                                                    \App\Enums\ProductionStatus::Pending => 'border-muted text-muted',
                                                    \App\Enums\ProductionStatus::InProgress => 'border-amber-500 text-amber-500 bg-amber-500/5',
                                                    \App\Enums\ProductionStatus::Completed => 'border-green-500 text-green-500 bg-green-500/5',
                                                };
                                            @endphp
                                            <select name="status" onchange="this.form.submit()" 
                                                class="text-[10px] font-bold uppercase tracking-widest rounded-lg border px-3 py-1.5 focus:ring-0 cursor-pointer transition-all {{ $statusClass }} bg-primary">
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
                    <div class="p-8 bg-white/5 border-t border-subtle">
                        <form action="{{ route('production.add_item', $productionDay) }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            @csrf
                            <div class="md:col-span-2">
                                <label class="form-label text-[10px] uppercase">Add Recipe</label>
                                <select name="recipe_id" class="form-control" required>
                                    <option value="">Select Recipe...</option>
                                    @foreach($recipes as $recipe)
                                        <option value="{{ $recipe->id }}">{{ $recipe->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label text-[10px] uppercase">Portions</label>
                                <input type="number" name="portions" class="form-control font-bold" placeholder="Qty" required min="1">
                            </div>
                            <button type="submit" class="btn btn-primary py-3.5">Add Item</button>
                        </form>
                    </div>
                @endcan
            </div>

            {{-- Total Ingredients Required --}}
            <div class="card p-0 overflow-hidden">
                <div class="p-8 border-b border-subtle bg-white/5">
                    <h2 class="flex items-center gap-3">
                        <i data-lucide="shopping-basket" class="text-accent"></i>
                        Ingredient Consolidation
                    </h2>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="px-6 py-4">Ingredient</th>
                                <th class="px-6 py-4 text-center">Required Qty</th>
                                <th class="px-6 py-4 text-right">Est. Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-subtle">
                            @forelse($totalIngredients as $ing)
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4 font-bold text-primary">{{ $ing['name'] }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 bg-primary rounded-full border border-subtle font-black text-accent">
                                            {{ number_format($ing['total_quantity'], 2) }} {{ $ing['unit'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-muted tracking-tight">₹{{ number_format($ing['total_cost'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-[10px] font-bold text-muted uppercase tracking-widest opacity-40">No ingredients required</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right Column: Tasks & Files --}}
        <div class="space-y-8">
            {{-- Tasks Checklist --}}
            <div class="card">
                <div class="mb-6">
                    <h3 class="flex items-center gap-2 mb-1">
                        <i data-lucide="check-square" class="w-5 h-5 text-accent"></i>
                        Tasks Checklist
                    </h3>
                    <p class="text-[10px] font-bold text-muted uppercase tracking-widest">Daily SOP Compliance</p>
                </div>

                <div class="space-y-2">
                    @forelse($productionDay->tasks as $task)
                        <div class="flex items-center justify-between p-4 rounded-xl bg-white/5 border border-subtle group transition-all {{ $task->isCompleted() ? 'opacity-40' : '' }}">
                            <div class="flex items-center gap-4">
                                <form action="{{ $task->isCompleted() ? route('tasks.reopen', $task) : route('tasks.complete', $task) }}" method="POST">
                                    @csrf
                                    <input type="checkbox" onchange="this.form.submit()" {{ $task->isCompleted() ? 'checked' : '' }} 
                                        class="w-5 h-5 rounded border-subtle bg-primary text-accent focus:ring-accent transition-all cursor-pointer">
                                </form>
                                <div>
                                    <div class="text-sm font-bold text-primary {{ $task->isCompleted() ? 'line-through' : '' }}">{{ $task->title }}</div>
                                    <div class="text-[9px] font-bold text-muted uppercase tracking-widest">Assigned to: {{ $task->assignedUser->name ?? 'Unassigned' }}</div>
                                </div>
                            </div>
                            @can('delete', $task)
                                <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Delete task?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-muted hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    @empty
                        <div class="py-8 text-center bg-white/5 rounded-xl border border-dashed border-subtle">
                            <p class="text-[10px] font-bold text-muted uppercase tracking-widest opacity-40">No tasks defined</p>
                        </div>
                    @endforelse
                </div>

                @can('create', App\Models\Task::class)
                    <form action="{{ route('tasks.store') }}" method="POST" class="mt-6 space-y-3 p-4 bg-primary/20 rounded-xl border border-subtle border-dashed">
                        @csrf
                        <input type="hidden" name="production_day_id" value="{{ $productionDay->id }}">
                        <input type="text" name="title" class="form-control text-[10px] py-2.5" placeholder="New Task Title..." required>
                        <div class="flex gap-2">
                            <select name="assigned_to" class="form-control text-[10px] py-2 flex-1">
                                <option value="">Assign To...</option>
                                @foreach(\App\Models\User::all() as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-secondary px-4 py-2">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </form>
                @endcan
            </div>

            {{-- Files & Photos --}}
            <div class="card">
                <div class="mb-6">
                    <h3 class="flex items-center gap-2 mb-1">
                        <i data-lucide="paperclip" class="w-5 h-5 text-accent"></i>
                        Verification Files
                    </h3>
                    <p class="text-[10px] font-bold text-muted uppercase tracking-widest">Quality Control Artifacts</p>
                </div>

                <div class="space-y-3">
                    @forelse($productionDay->driveFiles as $file)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white/5 border border-subtle group">
                            <a href="javascript:void(0)" onclick="openPreview('{{ $file->drive_url }}', '{{ $file->name }}')" 
                                class="flex items-center gap-3 text-[10px] font-bold text-primary hover:text-accent uppercase tracking-widest transition-all">
                                <i data-lucide="file-text" class="w-4 h-4 text-accent"></i>
                                {{ $file->name }}
                            </a>
                            @can('delete', $file)
                                <form action="{{ route('drive_files.destroy', $file) }}" method="POST" onsubmit="return confirm('Remove file?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-muted hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    @empty
                        <div class="py-8 text-center bg-white/5 rounded-xl border border-dashed border-subtle">
                            <p class="text-[10px] font-bold text-muted uppercase tracking-widest opacity-40">No files attached</p>
                        </div>
                    @endforelse
                </div>

                @can('create', App\Models\DriveFile::class)
                    <form action="{{ route('drive_files.store') }}" method="POST" class="mt-6 space-y-2 p-4 bg-primary/20 rounded-xl border border-subtle border-dashed">
                        @csrf
                        <input type="hidden" name="linked_type" value="production">
                        <input type="hidden" name="linked_id" value="{{ $productionDay->id }}">
                        <input type="text" name="name" class="form-control text-[10px] py-2" placeholder="File Title (e.g. Prep Photo)" required>
                        <input type="url" name="drive_url" class="form-control text-[10px] py-2" placeholder="Google Drive Link" required>
                        <button type="submit" class="btn btn-secondary w-full py-2 text-[10px] uppercase">Attach File</button>
                    </form>
                @endcan
            </div>

            @if($productionDay->notes)
                <div class="card bg-accent/5 border-accent/20">
                    <h3 class="text-[10px] font-bold text-accent uppercase tracking-widest mb-3">Kitchen Notes</h3>
                    <p class="text-sm italic text-primary opacity-80 font-serif leading-relaxed">"{{ $productionDay->notes }}"</p>
                </div>
            @endif
        </div>
    </div>
@endsection