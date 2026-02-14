@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-2">
        <a href="{{ route('recipes.index') }}" class="text-muted"><i data-lucide="arrow-left"></i></a>
        <h1>{{ $recipe->name }}</h1>
        <span class="badge {{ $recipe->status->value === 'permanent' ? 'badge-success' : 'badge-warning' }}">
            {{ $recipe->status->label() }}
        </span>
        <span class="text-muted text-sm">v{{ $recipe->version }}</span>
    </div>
@endsection

@section('actions')
    <div class="flex gap-2">
        @if($recipe->isDraft() && auth()->user()->canApproveRecipes())
            <form action="{{ route('recipes.approve', $recipe) }}" method="POST"
                onsubmit="return confirm('Approve this recipe generic permanent version?')">
                @csrf
                <button type="submit" class="btn btn-primary" style="background-color: var(--secondary-color);">
                    <i data-lucide="check-circle"></i> Approve
                </button>
            </form>
        @endif

        @can('update', $recipe)
            <a href="{{ route('recipes.edit', $recipe) }}" class="btn btn-secondary">
                <i data-lucide="edit-2"></i> Edit
            </a>
        @endcan

        @can('delete', $recipe)
            <form action="{{ route('recipes.destroy', $recipe) }}" method="POST"
                onsubmit="return confirm('Delete this recipe?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i data-lucide="trash-2"></i> Delete
                </button>
            </form>
        @endcan

        <a href="{{ route('recipes.print', $recipe) }}" target="_blank" class="btn btn-secondary">
            <i data-lucide="printer"></i> Print
        </a>
    </div>
@endsection

@section('content')
    <div class="flex gap-4" style="flex-wrap: wrap;">
        <!-- Recipe Info -->
        <div style="flex: 2; min-width: 400px;">
            <div class="card">
                <div class="flex justify-between"
                    style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1rem;">
                    <div>
                        <div class="text-muted text-sm">Category</div>
                        <div style="font-weight: 500;">{{ $recipe->category->name ?? 'None' }}</div>
                    </div>
                    <div>
                        <div class="text-muted text-sm">Base Yield</div>
                        <div style="font-weight: 500;">{{ $recipe->yields }} Portions</div>
                    </div>
                    @if(auth()->user()->isAdmin())
                        <div>
                            <div class="text-muted text-sm">Cost / Portion</div>
                            <div style="font-weight: 600; color: var(--secondary-color);">
                                ₹{{ number_format($costPerPortion, 2) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-muted text-sm">Total Cost</div>
                            <div style="font-weight: 500;">₹{{ number_format((float) $recipe->total_cost, 2) }}</div>
                        </div>
                    @endif
                </div>

                @if($recipe->isSubRecipe())
                    <div
                        style="background-color: #f0fdf4; border: 1px solid #86efac; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
                        <div
                            style="font-weight: 600; color: #16a34a; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i data-lucide="package" style="width: 16px;"></i> Production Output
                        </div>
                        <div style="color: #15803d;">
                            This recipe produces <strong>{{ number_format($recipe->output_quantity, 3) }}
                                {{ $recipe->output_unit }}</strong>
                            of <strong>{{ $recipe->producesIngredient->name }}</strong> per {{ $recipe->yields }} portion(s).
                        </div>
                    </div>
                @endif



                @if($recipe->stages->isNotEmpty())
                    @foreach($recipe->stages as $stage)
                        <div class="mb-8">
                            <h3 class="flex items-center gap-2">
                                <span class="bg-indigo-100 text-indigo-700 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">{{ $loop->iteration }}</span>
                                {{ $stage->name }}
                            </h3>
                            
                            <div class="table-container mt-4">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="w-5/12">Ingredient</th>
                                            <th class="w-3/12">Quantity (Base)</th>
                                            <th class="w-2/12">Unit</th>
                                            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                                <th class="w-2/12 text-right">Cost</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stage->ingredients as $ri)
                                            <tr class="ingredient-row">
                                                <td class="pl-6">
                                                    @if($ri->ingredient->producedByRecipes->isNotEmpty())
                                                        @php $subRecipe = $ri->ingredient->producedByRecipes->first(); @endphp
                                                        <div class="flex items-center justify-between group/sub">
                                                            <a href="{{ route('recipes.show', $subRecipe) }}"
                                                                class="text-blue-600 hover:underline flex items-center gap-2"
                                                                title="View Sub-Recipe: {{ $subRecipe->name }}">
                                                                <i data-lucide="link" class="w-3 h-3 text-blue-400"></i>
                                                                <span class="font-medium">{{ $ri->ingredient->name }}</span>
                                                                @if($subRecipe->status->value === 'draft')
                                                                    <span class="text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full font-bold ml-1 uppercase tracking-tighter">Draft</span>
                                                                @endif
                                                            </a>
                                                            <div class="flex items-center gap-1 opacity-0 group-hover/sub:opacity-100 transition-opacity">
                                                                @if($subRecipe->isDraft() && auth()->user()->canApproveRecipes())
                                                                    <form action="{{ route('recipes.approve', $subRecipe) }}" method="POST" class="inline" onsubmit="return confirm('Approve sub-recipe: {{ $subRecipe->name }}?')">
                                                                        @csrf
                                                                        <button type="submit" class="p-1 text-green-600 hover:bg-green-50 rounded transition-colors" title="Approve Sub-Recipe">
                                                                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                                                                        </button>
                                                                    </form>
                                                                    <form action="{{ route('recipes.reject', $subRecipe) }}" method="POST" class="inline" onsubmit="return confirm('Reject sub-recipe: {{ $subRecipe->name }}?')">
                                                                        @csrf
                                                                        <button type="submit" class="p-1 text-red-600 hover:bg-red-50 rounded transition-colors" title="Reject Sub-Recipe">
                                                                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                                <a href="{{ route('recipes.print', $subRecipe) }}" target="_blank" class="p-1 text-gray-500 hover:bg-gray-100 rounded transition-colors" title="Print Sub-Recipe">
                                                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                                                </a>
                                                                @can('update', $subRecipe)
                                                                    <a href="{{ route('recipes.edit', $subRecipe) }}" class="p-1 text-blue-500 hover:bg-blue-50 rounded transition-colors" title="Edit Sub-Recipe">
                                                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                                                    </a>
                                                                @endcan
                                                            </div>
                                                        </div>
                                                    @else
                                                        {{ $ri->ingredient->name }}
                                                    @endif

                                                    @if($ri->ingredient->allergen_tags && count($ri->ingredient->allergen_tags) > 0)
                                                        <div class="flex flex-wrap gap-1 mt-1">
                                                            @foreach($ri->ingredient->allergen_tags as $tag)
                                                                <span
                                                                    class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-red-50 text-red-600 border border-red-100">
                                                                    {{ $tag }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="qty-display"
                                                        data-base="{{ $ri->quantity }}">{{ number_format($ri->quantity, 3) }}</span>
                                                </td>
                                                <td>{{ $ri->unit }}</td>
                                                @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                                    <td class="text-right">
                                                        <span class="cost-display" data-base="{{ $ri->cost }}">
                                                            ₹{{ number_format($ri->cost, 2) }}
                                                        </span>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($stage->method)
                                <div class="mt-4 p-4 bg-indigo-50/30 border border-indigo-100 rounded-xl">
                                    <h4 class="text-indigo-900 font-bold mb-2 flex items-center gap-2">
                                        <i data-lucide="info" class="w-4 h-4"></i>
                                        Instructions for {{ $stage->name }}
                                    </h4>
                                    <div class="text-sm leading-relaxed text-indigo-800/80 whitespace-pre-wrap">{{ $stage->method }}</div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <h3>Ingredients</h3>
                    <div class="table-container" style="margin-top: 1rem;">
                        @php
                            $groupedIngredients = $recipe->recipeIngredients->groupBy('ingredient_group');
                            // Handle ungrouped items
                            $ungrouped = $groupedIngredients->get('');
                            if ($ungrouped) {
                                $groupedIngredients->forget('');
                                $groupedIngredients->put('Main', $ungrouped);
                            }
                            $nullGroup = $groupedIngredients->get(null);
                            if ($nullGroup) {
                                $groupedIngredients->forget(null);
                                if ($groupedIngredients->has('Main')) {
                                    $groupedIngredients['Main'] = $groupedIngredients['Main']->merge($nullGroup);
                                } else {
                                    $groupedIngredients->put('Main', $nullGroup);
                                }
                            }
                        @endphp

                        <table class="table" id="recipeTable">
                            <thead>
                                <tr>
                                    <th class="w-5/12">Ingredient</th>
                                    <th class="w-3/12">Quantity (Base)</th>
                                    <th class="w-2/12">Unit</th>
                                    @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                        <th class="w-2/12 text-right">Cost</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedIngredients as $group => $items)
                                    <tr class="bg-gray-50 border-b border-gray-100">
                                        <td colspan="{{ (auth()->user()->isAdmin() || auth()->user()->isManager()) ? 4 : 3 }}"
                                            class="py-2 px-3 font-bold text-gray-700 uppercase text-xs tracking-wider">
                                            @php
                                                $displayGroup = $group ?: 'Main Ingredients';
                                                if (is_numeric($displayGroup)) {
                                                    $displayGroup = 'Set ' . $displayGroup;
                                                }
                                            @endphp
                                            {{ $displayGroup }}
                                        </td>
                                    </tr>
                                    @foreach($items as $ri)
                                        <tr class="ingredient-row">
                                            <td class="pl-6">
                                                @if($ri->ingredient->producedByRecipes->isNotEmpty())
                                                    @php $subRecipe = $ri->ingredient->producedByRecipes->first(); @endphp
                                                    <div class="flex items-center justify-between group/sub">
                                                        <a href="{{ route('recipes.show', $subRecipe) }}"
                                                            class="text-blue-600 hover:underline flex items-center gap-2"
                                                            title="View Sub-Recipe: {{ $subRecipe->name }}">
                                                            <i data-lucide="link" class="w-3 h-3 text-blue-400"></i>
                                                            <span class="font-medium">{{ $ri->ingredient->name }}</span>
                                                            @if($subRecipe->status->value === 'draft')
                                                                <span class="text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full font-bold ml-1 uppercase tracking-tighter">Draft</span>
                                                            @endif
                                                        </a>
                                                        <div class="flex items-center gap-1 opacity-0 group-hover/sub:opacity-100 transition-opacity">
                                                            @if($subRecipe->isDraft() && auth()->user()->canApproveRecipes())
                                                                <form action="{{ route('recipes.approve', $subRecipe) }}" method="POST" class="inline" onsubmit="return confirm('Approve sub-recipe: {{ $subRecipe->name }}?')">
                                                                    @csrf
                                                                    <button type="submit" class="p-1 text-green-600 hover:bg-green-50 rounded transition-colors" title="Approve Sub-Recipe">
                                                                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                                                                    </button>
                                                                </form>
                                                                <form action="{{ route('recipes.reject', $subRecipe) }}" method="POST" class="inline" onsubmit="return confirm('Reject sub-recipe: {{ $subRecipe->name }}?')">
                                                                    @csrf
                                                                    <button type="submit" class="p-1 text-red-600 hover:bg-red-50 rounded transition-colors" title="Reject Sub-Recipe">
                                                                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            <a href="{{ route('recipes.print', $subRecipe) }}" target="_blank" class="p-1 text-gray-500 hover:bg-gray-100 rounded transition-colors" title="Print Sub-Recipe">
                                                                <i data-lucide="printer" class="w-4 h-4"></i>
                                                            </a>
                                                            @can('update', $subRecipe)
                                                                <a href="{{ route('recipes.edit', $subRecipe) }}" class="p-1 text-blue-500 hover:bg-blue-50 rounded transition-colors" title="Edit Sub-Recipe">
                                                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                                                </a>
                                                            @endcan
                                                        </div>
                                                    </div>
                                                @else
                                                    {{ $ri->ingredient->name }}
                                                @endif

                                                @if($ri->ingredient->allergen_tags && count($ri->ingredient->allergen_tags) > 0)
                                                    <div class="flex flex-wrap gap-1 mt-1">
                                                        @foreach($ri->ingredient->allergen_tags as $tag)
                                                            <span
                                                                class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-red-50 text-red-600 border border-red-100">
                                                                {{ $tag }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="qty-display"
                                                    data-base="{{ $ri->quantity }}">{{ number_format($ri->quantity, 3) }}</span>
                                            </td>
                                            <td>{{ $ri->unit }}</td>
                                            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                                <td class="text-right">
                                                    <span class="cost-display" data-base="{{ $ri->cost }}">
                                                        ₹{{ number_format($ri->cost, 2) }}
                                                    </span>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if($recipe->method)
                    <h3 style="margin-top: 2rem;">Recipe Overview / Description</h3>
                    <div style="white-space: pre-wrap; margin-top: 1rem; line-height: 1.8; color: var(--text-main); font-size: 1.1rem;" class="bg-gray-50/50 p-6 rounded-2xl border border-gray-100">
                        {{ $recipe->method }}
                    </div>
                @endif

                @if(count($recipe->allergens) > 0)
                    <div class="mt-8 p-4 bg-red-50 border border-red-100 rounded-xl">
                        <div class="flex items-center gap-2 mb-2">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                            <h4 class="font-bold text-red-700">Allergen Information</h4>
                        </div>
                        <p class="text-sm text-red-600 mb-3">This recipe contains the following allergens:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($recipe->allergens as $allergen)
                                <span
                                    class="bg-white text-red-600 border border-red-200 px-3 py-1 rounded-full text-xs font-bold uppercase shadow-sm">
                                    {{ App\Enums\Allergen::tryFrom($allergen)?->label() ?? ucfirst($allergen) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar Tools -->
        <div style="flex: 1; min-width: 300px;">
            <!-- Scaling Calculator -->
            <div class="card" style="background-color: #f8fafc;">
                <h3><i data-lucide="calculator" style="vertical-align: middle; width: 1.25rem;"></i> Smart Scale</h3>
                <div style="margin-top: 1rem;">

                    <div class="mb-4">
                        <label class="form-label text-xs uppercase text-gray-500 font-bold">Scaling Mode</label>
                        <div class="flex gap-2">
                            <button onclick="setMultiplier(0.5)" class="btn btn-sm btn-outline-secondary">0.5x</button>
                            <button onclick="setMultiplier(1)" class="btn btn-sm btn-primary">1x</button>
                            <button onclick="setMultiplier(2)" class="btn btn-sm btn-outline-secondary">2x</button>
                            <button onclick="setMultiplier(3)" class="btn btn-sm btn-outline-secondary">3x</button>
                        </div>
                    </div>

                    <label class="form-label">Target Portions</label>
                    <div class="flex gap-2 mb-4">
                        <input type="number" id="desiredPortions" class="form-control font-bold text-lg"
                            value="{{ $recipe->yield_portions ?? $recipe->yields }}" min="0.1" step="0.1"
                            oninput="updateScaling()">
                        <div class="py-2 px-3 bg-gray-100 rounded text-gray-500 text-sm flex items-center">
                            / {{ $recipe->yield_portions ?? $recipe->yields }} Base
                        </div>
                    </div>

                    <div id="scalingResult" class="p-4 bg-white rounded border border-gray-200">
                        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-bold text-gray-500 uppercase">Est. Total Cost</span>
                            </div>
                            <div class="text-2xl font-bold text-green-600 mb-2">
                                ₹<span id="scaledCostDisplay">{{ number_format((float) $recipe->total_cost, 2) }}</span>
                            </div>
                        @endif
                        <div class="text-sm text-gray-600 border-t pt-2 mt-2">
                            <i data-lucide="clock" class="w-3 h-3 inline"></i> Prep: <span
                                id="scaledTime">{{ $recipe->prep_time_minutes }}</span> mins
                        </div>

                        <div class="mt-4 pt-2 border-t">
                            <form action="{{ route('recipes.scale', $recipe) }}" method="POST"
                                onsubmit="return confirm('Create a NEW version of this recipe scaled to ' + document.getElementById('desiredPortions').value + ' portions?')">
                                @csrf
                                <input type="hidden" name="yield_type" value="portions">
                                <input type="hidden" name="new_yield" id="formNewYield"
                                    value="{{ $recipe->yield_portions ?? $recipe->yields }}">
                                <button type="submit" class="btn btn-sm btn-primary w-full">
                                    <i data-lucide="copy"></i> Create Scaled Version
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Drive Files -->
            <div class="card">
                <h3>Attached Files</h3>
                <div style="margin-top: 1rem;">
                    @if($recipe->driveFiles->isEmpty())
                        <div class="text-muted text-sm">No files attached.</div>
                    @else
                        <div class="flex flex-col gap-2">
                            @foreach($recipe->driveFiles as $file)
                                <div class="flex justify-between items-center p-2 border rounded bg-white">
                                    <a href="{{ $file->preview_url ?? $file->drive_url }}" target="_blank"
                                        class="flex items-center gap-2 text-sm text-blue-600 hover:underline">
                                        <i data-lucide="file"></i> {{ $file->name }}
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
                        </div>
                    @endif

                    @can('create', App\Models\DriveFile::class)
                        @can('create', App\Models\DriveFile::class)
                            <form action="{{ route('drive_files.store') }}" method="POST" enctype="multipart/form-data"
                                style="margin-top: 1rem;">
                                @csrf
                                <input type="hidden" name="linked_type" value="recipe">
                                <input type="hidden" name="linked_id" value="{{ $recipe->id }}">
                                <div class="form-group">
                                    <input type="text" name="name" class="form-control" placeholder="File Name" required
                                        style="font-size: 0.8rem; padding: 0.4rem;">
                                </div>
                                <div class="form-group">
                                    <input type="file" name="file" class="form-control" required
                                        style="font-size: 0.8rem; padding: 0.4rem;">
                                </div>
                                <button type="submit" class="btn btn-sm btn-secondary w-full">Upload File</button>
                            </form>
                        @endcan
                    @endcan
                </div>
            </div>

            <!-- Version History -->
            <div class="card">
                <h3>Versions</h3>
                <ul style="list-style: none; margin-top: 1rem; max-height: 200px; overflow-y: auto;">
                    @foreach($recipe->versions as $version)
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-color); font-size: 0.875rem;">
                            <div class="flex justify-between">
                                <strong>v{{ $version->version }}</strong>
                                <span class="text-muted">{{ $version->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="text-muted" style="font-size: 0.75rem;">
                                Edited by {{ $version->editor->name ?? 'Unknown' }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const basePortions = {{ $recipe->yield_portions ?? $recipe->yields ?? 1 }};
        const baseCost = {{ $recipe->total_cost }};
        const basePrepTime = {{ $recipe->prep_time_minutes ?? 0 }};

        function setMultiplier(m) {
            const target = basePortions * m;
            document.getElementById('desiredPortions').value = target;
            updateScaling();
        }

        function updateScaling() {
            const targetPortions = parseFloat(document.getElementById('desiredPortions').value) || basePortions;
            const ratio = targetPortions / basePortions;

            // Update Form Input
            document.getElementById('formNewYield').value = targetPortions;

            // Update Cost Display
            const newCost = baseCost * ratio;
            const costEl = document.getElementById('scaledCostDisplay');
            if (costEl) {
                costEl.innerText = newCost.toFixed(2);
            }

            // Update Time Display
            if (basePrepTime > 0) {
                // ...
            }

            // Update Ingredient Table
            document.querySelectorAll('.qty-display').forEach(el => {
                const base = parseFloat(el.dataset.base);
                el.innerText = (base * ratio).toFixed(3);
            });

            document.querySelectorAll('.cost-display').forEach(el => {
                const base = parseFloat(el.dataset.base);
                el.innerText = (base * ratio).toFixed(2);
            });
        }
    </script>
@endpush