@extends('layouts.app')

@section('header')
<div class="flex items-center gap-4">
    <a href="{{ route('recipes.show', $recipe) }}" class="btn btn-secondary p-3">
        <i data-lucide="arrow-left" class="w-5 h-5"></i>
    </a>
    <div>
        <h1 class="text-2xl md:text-3xl font-black tracking-tight text-primary">Edit Recipe</h1>
        <p class="text-xs md:text-sm text-muted font-medium mt-1 uppercase tracking-widest">Editing <span class="text-accent">{{ $recipe->name }}</span></p>
    </div>
</div>
@endsection

@section('actions')
<div class="flex items-center gap-3">
    <button type="button" onclick="window.history.back()" class="btn btn-secondary">
        Cancel
    </button>
    <button type="submit" form="recipeForm" class="btn btn-primary">
        <i data-lucide="save"></i> Update Recipe
    </button>
</div>
@endsection

@section('content')
<div class="min-h-screen pb-32">
    <form action="{{ route('recipes.update', $recipe) }}" method="POST" id="recipeForm"
          class="flex flex-col lg:flex-row gap-8 lg:gap-10">
        @csrf
        @method('PUT')

        {{-- Left Column: Primary Details & Configuration --}}
        <div class="w-full lg:w-1/3 flex flex-col gap-8">
            <div class="card p-0 overflow-hidden">
                <div class="p-8 border-b border-subtle bg-white/5">
                    <h2 class="flex items-center gap-3">
                        <i data-lucide="info" class="text-accent"></i>
                        Recipe Details
                    </h2>
                </div>
                <div class="p-8 space-y-8">
                    {{-- Recipe Type Selection --}}
                    <div class="bg-primary/20 p-4 rounded-2xl border border-subtle mb-2">
                        <label class="block text-[10px] font-bold text-muted uppercase tracking-widest mb-3">Recipe Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex flex-col items-center gap-2 p-3 rounded-xl border-2 transition-all cursor-pointer group border-subtle bg-white/5 hover:border-accent/50" id="mainTypeLabel">
                                <input type="radio" name="recipe_type_select" value="main" class="absolute top-2 right-2 accent-brass" onchange="updateRecipeType('main')" {{ !$recipe->is_sub_recipe ? 'checked' : '' }}>
                                <div class="p-2 bg-primary/40 rounded-lg text-accent group-hover:scale-110 transition-transform">
                                    <i data-lucide="utensils" class="w-5 h-5"></i>
                                </div>
                                <span class="text-[10px] font-bold text-muted uppercase tracking-widest">Main Recipe</span>
                            </label>
                            <label class="relative flex flex-col items-center gap-2 p-3 rounded-xl border-2 transition-all cursor-pointer group border-subtle bg-white/5 hover:border-accent/50" id="subTypeLabel">
                                <input type="radio" name="recipe_type_select" value="sub" class="absolute top-2 right-2 accent-brass" onchange="updateRecipeType('sub')" {{ $recipe->is_sub_recipe ? 'checked' : '' }}>
                                <div class="p-2 bg-primary/40 rounded-lg text-accent group-hover:scale-110 transition-transform">
                                    <i data-lucide="component" class="w-5 h-5"></i>
                                </div>
                                <span class="text-[10px] font-bold text-muted uppercase tracking-widest">Sub-Recipe</span>
                            </label>
                        </div>
                    </div>

                    {{-- Recipe Name --}}
                    <div>
                        <label class="form-label">Recipe Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name', $recipe->name) }}">
                        @error('name') <p class="text-red-500 text-[10px] mt-1 font-bold uppercase">{{ $message }}</p> @enderror
                    </div>

                    {{-- Category --}}
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="form-label">Category <span class="text-red-500">*</span></label>
                            <button type="button" onclick="openCategoryModal()" class="text-[10px] font-bold text-accent hover:text-accent-hover uppercase tracking-widest flex items-center gap-1 bg-white/5 px-2 py-1 rounded transition-all">
                                <i data-lucide="plus" class="w-3 h-3"></i> Quick Add
                            </button>
                        </div>
                        <select name="category_id" id="category-select" required class="form-control"
                                style="background-color: var(--bg-primary); color: var(--text-primary); border: 1px solid var(--border-subtle); border-radius: var(--radius); padding: 10px 14px; width: 100%; cursor: pointer; appearance: auto;">
                            <option value="" disabled>Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $recipe->category_id) == $category->id ? 'selected' : '' }}
                                        style="background-color: var(--bg-card); color: var(--text-primary);">
                                    {{ $category->name }} @if($category->type == 'ingredient') (Ingredient) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-red-500 text-[10px] mt-1 font-bold uppercase">{{ $message }}</p> @enderror
                    </div>

                    {{-- Smart Scale Section --}}
                    <div class="bg-white/5 rounded-xl p-5 border border-subtle mb-4">
                        <h3 class="text-[10px] font-bold text-muted uppercase tracking-widest flex items-center gap-2 mb-4">
                            <i data-lucide="calculator" class="w-4 h-4 text-accent"></i>
                            Smart Scale
                        </h3>
                        <div class="flex gap-2">
                            <button type="button" onclick="setMultiplier(0.5)"
                                class="scaling-btn flex-1 px-3 py-3 rounded-lg border border-subtle bg-white/5 text-muted font-bold text-[10px] uppercase tracking-widest hover:bg-white/10 transition-all"
                                data-multiplier="0.5">0.5x</button>
                            <button type="button" onclick="setMultiplier(1)"
                                class="scaling-btn flex-1 px-3 py-3 rounded-lg border border-accent bg-accent text-primary font-bold text-[10px] uppercase tracking-widest hover:bg-accent-hover transition-all active"
                                data-multiplier="1">1x</button>
                            <button type="button" onclick="setMultiplier(2)"
                                class="scaling-btn flex-1 px-3 py-3 rounded-lg border border-subtle bg-white/5 text-muted font-bold text-[10px] uppercase tracking-widest hover:bg-white/10 transition-all"
                                data-multiplier="2">2x</button>
                        </div>
                    </div>

                    {{-- Yields Section --}}
                    <div class="bg-primary/20 rounded-2xl p-5 border border-subtle border-dashed">
                        <label class="block text-[10px] font-bold text-muted uppercase tracking-widest mb-4">Yield Configuration</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <label class="text-[10px] text-muted font-bold uppercase tracking-widest">Portions <span class="text-red-500">*</span></label>
                                    <button type="button" onclick="resetScaling()" class="text-[10px] text-accent hover:text-accent-hover font-bold uppercase tracking-widest">Reset</button>
                                </div>
                                <input type="number" name="yield_portions" id="yield_portions" min="1" step="1"
                                    value="{{ old('yield_portions', $recipe->yield_portions ?? 1) }}" required
                                    class="form-control text-center font-bold" oninput="updateScaling()">
                                @error('yield_portions') <p class="text-red-500 text-[10px] mt-1 font-bold uppercase">{{ $message }}</p> @enderror
                                <input type="hidden" name="yield_batches" value="{{ old('yield_batches', $recipe->yield_batches ?? 1) }}">
                            </div>
                        </div>
                    </div>

                    {{-- Sub-Recipe Configuration --}}
                    <div id="subRecipeConfigSection" class="{{ $recipe->is_sub_recipe ? '' : 'hidden' }}">
                        <div class="bg-white/5 rounded-2xl p-5 border border-subtle">
                            <h3 class="text-[10px] font-bold text-accent uppercase tracking-widest mb-1">Sub-Recipe Configuration</h3>
                            <p class="text-[10px] text-muted mb-4">Link this recipe to an ingredient for use in other recipes.</p>
                            <input type="hidden" name="is_sub_recipe" id="is_sub_recipe" value="{{ $recipe->is_sub_recipe ? 1 : 0 }}">
                            <div class="space-y-4">
                                <div>
                                    <label class="form-label text-[10px] uppercase">Produced Ingredient</label>
                                    <select name="produces_ingredient_id" id="produces_ingredient_id" class="form-control">
                                        <option value="">-- Auto-create with recipe name --</option>
                                        @foreach($ingredients as $ing)
                                            <option value="{{ $ing->id }}" {{ old('produces_ingredient_id', $recipe->produces_ingredient_id) == $ing->id ? 'selected' : '' }}>
                                                {{ $ing->name }} ({{ $ing->measurement_unit }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="form-label text-[10px] uppercase">Output Qty</label>
                                        <input type="number" name="output_quantity" step="0.001" min="0"
                                            value="{{ old('output_quantity', $recipe->output_quantity ?? 1) }}"
                                            class="form-control font-bold">
                                    </div>
                                    <div>
                                        <label class="form-label text-[10px] uppercase">Output Unit</label>
                                        <select name="output_unit" class="form-control">
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->value }}" {{ old('output_unit', $recipe->output_unit ?? 'pcs') == $unit->value ? 'selected' : '' }}>
                                                    {{ $unit->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Method / Instructions --}}
                    <div>
                        <label class="form-label">General Method / Overview</label>
                        <textarea name="method" class="form-control min-h-[150px]" required>{{ old('method', $recipe->method) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Sets & Sub-Recipes --}}
        <div class="w-full lg:w-2/3 space-y-8">
            {{-- Sub-Recipes Used Section --}}
            <div class="card p-8">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="flex items-center gap-3">
                        <i data-lucide="component" class="text-accent"></i>
                        Sub-Recipes Used
                    </h2>
                    <button type="button" id="addSubRecipeBtnTop" onclick="openSubRecipeModal()" class="btn btn-secondary py-2 px-4">
                        <i data-lucide="plus"></i> Add Sub‑Recipe
                    </button>
                </div>
                <div id="sub-recipes-container" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div id="no-sub-recipes-msg" class="col-span-full py-16 text-center bg-white/5 rounded-3xl border-2 border-dashed border-subtle text-muted">
                        <i data-lucide="package-search" class="w-12 h-12 mx-auto mb-4 opacity-20"></i>
                        <p class="font-bold uppercase tracking-widest text-[10px]">No sub‑recipes linked</p>
                    </div>
                </div>
            </div>

            {{-- Sets Container --}}
            <div id="stages-container" class="space-y-10">
                @php
                    $stages = old('stages') ?? $recipe->stages ?? collect([]);
                    if (!($stages instanceof \Illuminate\Support\Collection)) {
                        $stages = collect($stages);
                    }
                @endphp
                @foreach($stages->where('name', '!=', 'Sub-Recipes') as $index => $stage)
                    <div class="card p-8 relative group stage-block" data-stage-index="{{ $index }}">
                        <input type="hidden" name="stages[{{ $index }}][id]" value="{{ data_get($stage, 'id') }}">
                        <button type="button" onclick="removeStage(this)" class="absolute top-6 right-6 text-muted hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all" title="Remove Set">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                        </button>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <label class="form-label">Set Name</label>
                                <input type="text" name="stages[{{ $index }}][name]" value="{{ data_get($stage, 'name') }}" required class="form-control font-bold">
                                @error('stages.' . $index . '.name') <p class="text-red-500 text-[10px] mt-1 font-bold uppercase">{{ $message }}</p> @enderror
                            </div>
                            <div class="col-span-2">
                                <label class="form-label">Instructions for this Set</label>
                                <textarea name="stages[{{ $index }}][method]" rows="3" class="form-control resize-y" placeholder="Describe the steps for this stage...">{{ data_get($stage, 'method') }}</textarea>
                            </div>
                        </div>

                        {{-- Ingredients Table --}}
                        <div class="overflow-x-auto rounded-xl border border-subtle">
                            <table class="table min-w-[600px]">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left">Item</th>
                                        <th class="px-4 py-3 text-center w-32">Quantity</th>
                                        <th class="px-4 py-3 text-left w-32">Unit</th>
                                        @if(auth()->user()->isAdmin())
                                            <th class="px-4 py-3 text-right">Cost</th>
                                        @endif
                                        <th class="px-4 py-3 text-center w-16"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-subtle stage-ingredients-body">
                                    @php
                                        $stageIngredients = [];
                                        if (is_object($stage) && isset($stage->ingredients)) {
                                            $stageIngredients = $stage->ingredients instanceof \Illuminate\Support\Collection ? $stage->ingredients->all() : $stage->ingredients;
                                        } elseif (is_array($stage)) {
                                            $stageIngredients = $stage['ingredients'] ?? [];
                                        }
                                    @endphp
                                    @foreach($stageIngredients as $ingIndex => $rIngredient)
                                        @php
                                            $rIngId = $rIngredient->ingredient_id ?? $rIngredient['ingredient_id'] ?? null;
                                            $rName = $rIngredient->ingredient->name ?? $rIngredient['ingredient']['name'] ?? 'Unknown';
                                            $rQty = $rIngredient->quantity ?? $rIngredient['quantity'] ?? 0;
                                            $rUnit = $rIngredient->unit ?? $rIngredient['unit'] ?? 'pcs';
                                            $rPrice = $rIngredient->ingredient->latest_price ?? $rIngredient->ingredient->price ?? 0;
                                            $rCost = $rIngredient->cost ?? $rIngredient['cost'] ?? 0;
                                            $rMeasUnit = $rIngredient->ingredient->measurement_unit ?? $rIngredient['ingredient']['measurement_unit'] ?? 'pcs';
                                        @endphp
                                        <tr class="group hover:bg-white/5 transition-colors ingredient-row">
                                            <td class="px-4 py-4">
                                                <select name="stages[{{ $index }}][ingredients][{{ $ingIndex }}][ingredient_id]" class="ingredient-select form-control" required>
                                                    <option value="{{ $rIngId }}" selected data-price="{{ $rPrice }}" data-unit="{{ $rMeasUnit }}">
                                                        {{ $rName }} @if($rMeasUnit) ({{ $rMeasUnit }}) @endif
                                                    </option>
                                                </select>
                                            </td>
                                            <td class="px-4 py-4">
                                                <input type="number" step="any" name="stages[{{ $index }}][ingredients][{{ $ingIndex }}][quantity]"
                                                       value="{{ $rQty }}" required data-base-qty="{{ $rQty }}" class="quantity-input form-control text-center font-bold" />
                                            </td>
                                            <td class="px-4 py-4">
                                                <input type="hidden" name="stages[{{ $index }}][ingredients][{{ $ingIndex }}][unit]" value="{{ $rUnit }}" class="unit-value-input" />
                                                <input type="text" readonly value="{{ $rUnit }}" class="unit-display form-control bg-primary/20 text-muted cursor-not-allowed text-center font-bold" />
                                            </td>
                                            @if(auth()->user()->isAdmin())
                                                <td class="px-4 py-4 text-right font-bold text-accent cost-display">{{ number_format((float) $rCost, 2) }}</td>
                                            @endif
                                            <td class="px-4 py-4 text-center">
                                                <button type="button" onclick="removeRow(this)" class="text-muted hover:text-red-500 transition-colors">
                                                    <i data-lucide="x"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-white/5 border-t border-subtle">
                                    <tr>
                                        <td colspan="5" class="px-4 py-4">
                                            <div class="flex gap-4">
                                                <button type="button" onclick="addIngredientRow(this)" class="btn btn-secondary flex-1 py-3 text-[10px] uppercase">
                                                    <i data-lucide="plus-circle"></i> Add Ingredient
                                                </button>
                                                <button type="button" onclick="openIngredientModal('')" class="btn btn-secondary flex-1 py-3 text-[10px] uppercase text-accent border-accent/20">
                                                    <i data-lucide="plus-square"></i> New Item
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" id="addStageBtn" onclick="addNewSet()" class="w-full py-8 border-2 border-dashed border-subtle rounded-3xl text-muted font-bold hover:border-accent/50 hover:bg-white/5 transition-all flex items-center justify-center gap-3 group">
                <i data-lucide="plus" class="group-hover:text-accent transition-colors"></i>
                <span class="text-[10px] uppercase tracking-widest">Add Another Set</span>
            </button>
        </div>

        {{-- Sticky Summary Bar --}}
        <div class="fixed bottom-0 left-0 right-0 bg-primary/95 backdrop-blur-xl border-t border-subtle z-[60] p-4 md:p-6 shadow-2xl">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex flex-wrap items-center gap-8">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold text-muted uppercase tracking-widest mb-1">Total Recipe Cost</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black text-primary">₹<span id="totalCostDisplay">0.00</span></span>
                        </div>
                    </div>
                    <div class="flex flex-col border-l border-subtle pl-8">
                        <span class="text-[10px] font-bold text-muted uppercase tracking-widest mb-1">Cost Per Portion</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-accent">₹<span id="costPerPortionDisplay">0.00</span></span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <button type="button" onclick="window.history.back()" class="btn btn-secondary flex-1 px-10">Cancel</button>
                    <button type="submit" class="btn btn-primary flex-1 px-14">
                        <i data-lucide="check-circle"></i> Update Recipe
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Templates --}}
<template id="stageTemplate">
    <div class="card p-8 relative group stage-block">
        <input type="hidden" name="stages[STAGE_INDEX][id]" value="">
        <button type="button" onclick="removeStage(this)" class="absolute top-6 right-6 text-muted hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all">
            <i data-lucide="trash-2"></i>
        </button>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="form-label">Set Name</label>
                <input type="text" name="stages[STAGE_INDEX][name]" value="" required class="form-control font-bold">
            </div>
            <div class="col-span-2">
                <label class="form-label">Instructions for this Set</label>
                <textarea name="stages[STAGE_INDEX][method]" rows="3" class="form-control resize-y" placeholder="Describe the steps..."></textarea>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl border border-subtle">
            <table class="table min-w-[600px]">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left">Item</th>
                        <th class="px-4 py-3 text-center w-32">Quantity</th>
                        <th class="px-4 py-3 text-left w-32">Unit</th>
                        @if(auth()->user()->isAdmin())
                            <th class="px-4 py-3 text-right">Cost</th>
                        @endif
                        <th class="px-4 py-3 text-center w-16"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-subtle stage-ingredients-body"></tbody>
                <tfoot class="bg-white/5 border-t border-subtle">
                    <tr>
                        <td colspan="5" class="px-4 py-4">
                            <div class="flex gap-4">
                                <button type="button" onclick="addIngredientRow(this)" class="btn btn-secondary flex-1 py-3 text-[10px] uppercase">
                                    <i data-lucide="plus-circle"></i> Add Ingredient
                                </button>
                                <button type="button" onclick="openIngredientModal('')" class="btn btn-secondary flex-1 py-3 text-[10px] uppercase text-accent border-accent/20">
                                    <i data-lucide="plus-square"></i> New Item
                                </button>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</template>

<template id="ingredientRowTemplate">
    <tr class="group hover:bg-white/5 transition-colors ingredient-row">
        <td class="px-4 py-4">
            <select name="stages[STAGE_INDEX][ingredients][ROW_INDEX][ingredient_id]" class="ingredient-select form-control" required>
                <option value="">Select Item...</option>
            </select>
        </td>
        <td class="px-4 py-4">
            <input type="number" step="any" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][quantity]" required data-base-qty="" class="quantity-input form-control text-center font-bold" placeholder="0" />
        </td>
        <td class="px-4 py-4">
            <input type="hidden" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][unit]" value="" class="unit-value-input" />
            <input type="text" readonly value="" class="unit-display form-control bg-primary/20 text-muted cursor-not-allowed text-center font-bold" placeholder="Unit" />
        </td>
        @if(auth()->user()->isAdmin())
            <td class="px-4 py-4 text-right font-bold text-accent cost-display">0.00</td>
        @endif
        <td class="px-4 py-4 text-center">
            <button type="button" onclick="removeRow(this)" class="text-muted hover:text-red-500 transition-colors">
                <i data-lucide="x"></i>
            </button>
        </td>
    </tr>
</template>

{{-- Hidden options for dynamic population --}}
<div id="ingredientOptions" style="display: none;">
    @php
        $producedIds = $recipe->stages->where('name', 'Sub-Recipes')->first() ? 
                       $recipe->stages->where('name', 'Sub-Recipes')->first()->ingredients->pluck('ingredient_id')->toArray() : [];
    @endphp
    <optgroup label="Core Ingredients">
        @foreach($ingredients as $ing)
            <option value="{{ $ing->id }}" data-price="{{ $ing->latest_price ?? $ing->price ?? 0 }}" data-unit="{{ $ing->measurement_unit }}">
                {{ $ing->name }} ({{ $ing->measurement_unit }})
            </option>
        @endforeach
    </optgroup>
    <optgroup label="Sub-Recipes">
        @foreach($ingredients->whereIn('id', $producedIds) as $sub)
            <option value="{{ $sub->id }}" data-price="{{ $sub->latest_price ?? $sub->price ?? 0 }}" data-unit="{{ $sub->measurement_unit }}">
                {{ $sub->name }} ({{ $sub->measurement_unit }})
            </option>
        @endforeach
    </optgroup>
</div>

{{-- Modals --}}
@include('recipes.partials.modals')

@push('scripts')
<style>
    .ts-dropdown { z-index: 99999 !important; background: var(--navy-primary) !important; border: 1px solid var(--color-border) !important; border-radius: 0.75rem !important; box-shadow: 0 20px 40px rgba(0,0,0,0.4) !important; }
    .ts-dropdown .option.active { background-color: var(--brass) !important; color: white !important; }
    .ts-dropdown .option { color: var(--ivory) !important; border-bottom: 1px solid rgba(242,237,230,0.05) !important; }
    .ts-control { background: transparent !important; border: none !important; color: var(--ivory) !important; padding: 0 !important; }
    body > .ts-dropdown { opacity: 1 !important; visibility: visible !important; display: block; }
</style>
<script>
    // Configuration & Data
    const UNIT_LABELS_MAP = {
        'g': 'Gram (g)', 'kg': 'Kilogram (kg)', 'ml': 'Milliliter (ml)', 'l': 'Liter (l)',
        'tbsp': 'Tablespoon (tbsp)', 'tsp': 'Teaspoon (tsp)', 'cup': 'Cup', 'pcs': 'Piece (pcs)',
        'oz': 'Ounce (oz)', 'lb': 'Pound (lb)'
    };

    @php
        $subRecipeStage = $recipe->stages->where('name', 'Sub-Recipes')->first();
        $existingSubData = [];
        if ($subRecipeStage) {
            foreach($subRecipeStage->ingredients as $ing) {
                $existingSubData[] = [
                    'ingId' => $ing->ingredient_id,
                    'name' => $ing->ingredient->name ?? 'Unknown',
                    'qty' => (float)$ing->quantity,
                    'unit' => $ing->unit,
                    'cost' => (float)$ing->cost,
                ];
            }
        }
    @endphp
    window.addedSubRecipes = @json($existingSubData);
    let scaleMultiplier = 1;

    // Initialization
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize Tom Select for searchable dropdowns
        if (typeof TomSelect !== 'undefined') {
            // NOTE: Category uses native <select> — no TomSelect needed (static list)

            // Sub-Recipe Selector in Modal
            const subSelectorEl = document.getElementById('sub-recipe-selector');
            if (subSelectorEl) {
                window.subRecipeSelector = new TomSelect(subSelectorEl, {
                    create: false,
                    placeholder: "-- Search Sub-Recipe --",
                    allowEmptyOption: true,
                    maxOptions: null,
                    dropdownParent: 'body'
                });
            }
        }

        // Initialize existing rows
        document.querySelectorAll('.ingredient-row').forEach(row => initIngredientRow(row));
        
        // Initial cost calculation
        calculateTotal();
        renderSubRecipeCards();

        // Update Recipe Type UI
        const currentType = document.querySelector('input[name="recipe_type_select"]:checked')?.value || 'main';
        updateRecipeType(currentType);

        // Form override to inject sub-recipes
        const form = document.getElementById('recipeForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (window.addedSubRecipes.length > 0) {
                    const stageIdx = 999;
                    const container = document.createElement('div');
                    container.style.display = 'none';
                    
                    container.innerHTML += `<input type="hidden" name="stages[${stageIdx}][name]" value="Sub-Recipes">`;
                    container.innerHTML += `<input type="hidden" name="stages[${stageIdx}][method]" value="Included sub-recipes">`;

                    window.addedSubRecipes.forEach((sub, idx) => {
                        container.innerHTML += `<input type="hidden" name="stages[${stageIdx}][ingredients][${idx}][ingredient_id]" value="${sub.ingId}">`;
                        container.innerHTML += `<input type="hidden" name="stages[${stageIdx}][ingredients][${idx}][quantity]" value="${sub.qty}">`;
                        container.innerHTML += `<input type="hidden" name="stages[${stageIdx}][ingredients][${idx}][unit]" value="${sub.unit}">`;
                        container.innerHTML += `<input type="hidden" name="stages[${stageIdx}][ingredients][${idx}][ingredient_group]" value="Sub-Recipe">`;
                    });
                    this.appendChild(container);
                }
            });
        }
    });

    // Scaling Logic
    function setMultiplier(val) {
        scaleMultiplier = val;
        document.querySelectorAll('.scaling-btn').forEach(btn => {
            const isMatch = parseFloat(btn.dataset.multiplier) === val;
            btn.classList.toggle('bg-accent', isMatch);
            btn.classList.toggle('text-primary', isMatch);
            btn.classList.toggle('border-accent', isMatch);
            btn.classList.toggle('active', isMatch);
        });
        
        document.querySelectorAll('.ingredient-row').forEach(row => {
            const qtyInput = row.querySelector('.quantity-input');
            const baseQty = parseFloat(qtyInput.dataset.baseQty) || 0;
            if (baseQty > 0) {
                qtyInput.value = (baseQty * scaleMultiplier).toFixed(3);
                calculateRowCost(row);
            }
        });
    }

    function updateScaling() {
        const portions = parseFloat(document.getElementById('yield_portions').value) || 1;
        const basePortions = {{ $recipe->yield_portions ?? 1 }};
        const multiplier = portions / basePortions;
        
        document.querySelectorAll('.ingredient-row').forEach(row => {
            const qtyInput = row.querySelector('.quantity-input');
            const baseQty = parseFloat(qtyInput.dataset.baseQty) || 0;
            if (baseQty > 0) {
                qtyInput.value = (baseQty * multiplier).toFixed(3);
                calculateRowCost(row);
            }
        });
    }

    function resetScaling() {
        document.getElementById('yield_portions').value = {{ $recipe->yield_portions ?? 1 }};
        setMultiplier(1);
    }

    // Recipe Type Logic
    function updateRecipeType(type) {
        const isSub = type === 'sub';
        document.getElementById('is_sub_recipe').value = isSub ? 1 : 0;
        
        const configSection = document.getElementById('subRecipeConfigSection');
        if (configSection) configSection.classList.toggle('hidden', !isSub);

        const mainLabel = document.getElementById('mainTypeLabel');
        const subLabel = document.getElementById('subTypeLabel');
        
        if (isSub) {
            subLabel.classList.add('border-accent', 'bg-white/5', 'ring-2', 'ring-accent/20');
            subLabel.classList.remove('border-subtle');
            mainLabel.classList.remove('border-accent', 'bg-white/5', 'ring-2', 'ring-accent/20');
            mainLabel.classList.add('border-subtle');
        } else {
            mainLabel.classList.add('border-accent', 'bg-white/5', 'ring-2', 'ring-accent/20');
            mainLabel.classList.remove('border-subtle');
            subLabel.classList.remove('border-accent', 'bg-white/5', 'ring-2', 'ring-accent/20');
            subLabel.classList.add('border-subtle');
        }
    }

    // Stage & Row Logic
    function addNewSet() {
        const container = document.getElementById('stages-container');
        const template = document.getElementById('stageTemplate');
        const index = Date.now();
        
        let html = template.innerHTML.replace(/STAGE_INDEX/g, index);
        const div = document.createElement('div');
        div.innerHTML = html;
        const block = div.firstElementChild;
        block.dataset.stageIndex = index;
        
        container.appendChild(block);
        addIngredientRow(block.querySelector('button[onclick*="addIngredientRow"]'));
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function removeStage(btn) {
        if (confirm('Remove this entire set?')) {
            btn.closest('.stage-block').remove();
            calculateTotal();
        }
    }

    function addIngredientRow(btn) {
        const stageBlock = btn.closest('.stage-block');
        const tbody = stageBlock.querySelector('.stage-ingredients-body');
        const stageIndex = stageBlock.dataset.stageIndex;
        const rowIndex = Date.now();
        const template = document.getElementById('ingredientRowTemplate');
        
        let html = template.innerHTML.replace(/STAGE_INDEX/g, stageIndex).replace(/ROW_INDEX/g, rowIndex);
        const trWrap = document.createElement('tbody');
        trWrap.innerHTML = html;
        const tr = trWrap.firstElementChild;
        
        // Populate select
        const select = tr.querySelector('.ingredient-select');
        const options = document.getElementById('ingredientOptions').innerHTML;
        select.innerHTML += options;
        
        tbody.appendChild(tr);
        initIngredientRow(tr);
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function initIngredientRow(row) {
        const select = row.querySelector('.ingredient-select');
        const qtyInput = row.querySelector('.quantity-input');
        
        // Initialize Tom Select for searchability
        if (select && typeof TomSelect !== 'undefined' && !select.tomselect) {
            const ts = new TomSelect(select, {
                create: false,
                placeholder: "Select Ingredient...",
                allowEmptyOption: true,
                maxOptions: null,
                dropdownParent: 'body'
            });
            select.tomselect = ts;
        }

        if (select) {
            select.addEventListener('change', () => {
                const opt = select.options[select.selectedIndex];
                if (opt && opt.value) {
                    const unit = opt.dataset.unit || '';
                    row.querySelector('.unit-value-input').value = unit;
                    row.querySelector('.unit-display').value = unit;
                }
                calculateRowCost(row);
            });
        }
        
        if (qtyInput) {
            qtyInput.addEventListener('input', () => {
                qtyInput.dataset.baseQty = qtyInput.value;
                calculateRowCost(row);
            });
        }
        calculateRowCost(row);
    }

    async function calculateRowCost(row) {
        const select = row.querySelector('.ingredient-select');
        const qtyInput = row.querySelector('.quantity-input');
        const costDisplay = row.querySelector('.cost-display');
        if (!costDisplay || !select) return;

        const ingredientId = select.value;
        const qty = parseFloat(qtyInput.value) || 0;
        
        if (!ingredientId || qty <= 0) {
            costDisplay.textContent = '0.00';
            calculateTotal();
            return;
        }

        try {
            const unitInput = row.querySelector('.unit-value-input');
            const unit = unitInput ? unitInput.value : '';
            
            const kitchenSlug = '{{ request()->route("kitchen_slug") }}';
            const url = `/k/${kitchenSlug}/ingredients/${ingredientId}/fifo-cost?quantity=${qty}&unit=${unit}`;
            
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.cost !== undefined) {
                costDisplay.textContent = parseFloat(data.cost).toFixed(2);
                calculateTotal();
            }
        } catch (error) {
            console.error('Error fetching FIFO cost:', error);
            // Display error instead of falling back to average price
            costDisplay.textContent = 'Insufficient Stock';
            costDisplay.classList.add('text-red-500', 'text-[10px]');
            calculateTotal();
        }
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.cost-display').forEach(el => {
            total += parseFloat(el.textContent) || 0;
        });
        
        // Sub-recipes cost
        window.addedSubRecipes.forEach(s => {
            total += parseFloat(s.cost || 0);
        });

        const portions = parseFloat(document.getElementById('yield_portions').value) || 1;
        document.getElementById('totalCostDisplay').textContent = total.toFixed(2);
        document.getElementById('costPerPortionDisplay').textContent = (total / portions).toFixed(2);
    }

    // Sub-Recipe Modal Functions
    function openSubRecipeModal() { document.getElementById('addSubRecipeModal').classList.remove('hidden'); }
    function closeSubRecipeModal() { document.getElementById('addSubRecipeModal').classList.add('hidden'); }
    
    async function confirmAddSubRecipe() {
        let val = '';
        let opt = null;

        if (window.subRecipeSelector) {
            val = window.subRecipeSelector.getValue();
            opt = window.subRecipeSelector.options[val];
        } else {
            const select = document.getElementById('sub-recipe-selector');
            val = select.value;
            opt = select.options[select.selectedIndex];
        }
        
        const qty = parseFloat(document.getElementById('sub-recipe-qty').value);
        if (!val || isNaN(qty)) return alert('Please select a sub-recipe and quantity');
        
        const originalOpt = document.querySelector(`#sub-recipe-selector option[value="${val}"]`);
        
        const subData = { 
            id: val,
            ingId: originalOpt?.dataset?.ingId || opt?.ingId || val,
            name: originalOpt?.dataset?.name || opt?.name || opt?.text || 'Unknown', 
            qty: qty, 
            unit: originalOpt?.dataset?.unit || opt?.unit || 'pcs', 
            cost: 0 // Will be updated by FIFO fetch
        };

        // Fetch FIFO cost for the sub-recipe (which is an ingredient)
        try {
            const kitchenSlug = '{{ request()->route("kitchen_slug") }}';
            const url = `/k/${kitchenSlug}/ingredients/${subData.ingId}/fifo-cost?quantity=${qty}&unit=${subData.unit}`;
            const response = await fetch(url);
            const data = await response.json();
            if (data.cost !== undefined) {
                subData.cost = parseFloat(data.cost);
            } else {
                subData.cost = qty * parseFloat(originalOpt?.dataset?.price || 0);
            }
        } catch (e) {
            console.error("FIFO sub-recipe cost fetch failed", e);
            subData.cost = qty * parseFloat(originalOpt?.dataset?.price || 0);
        }

        window.addedSubRecipes.push(subData);
        
        renderSubRecipeCards();
        calculateTotal();
        closeSubRecipeModal();
    }

    function renderSubRecipeCards() {
        const container = document.getElementById('sub-recipes-container');
        container.querySelectorAll('.sub-recipe-card').forEach(c => c.remove());
        document.getElementById('no-sub-recipes-msg').classList.toggle('hidden', window.addedSubRecipes.length > 0);

        window.addedSubRecipes.forEach((s, i) => {
            const cost = parseFloat(s.cost || 0).toFixed(2);
            const card = document.createElement('div');
            card.className = 'sub-recipe-card bg-white/5 border border-subtle rounded-2xl p-5 flex justify-between items-center';
            card.innerHTML = `
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-primary/40 rounded-xl text-accent">
                        <i data-lucide="component" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-primary">${s.name}</h4>
                        <p class="text-[10px] font-bold text-muted uppercase tracking-widest">${s.qty} ${s.unit} • <span class="text-accent">₹${cost}</span></p>
                    </div>
                </div>
                <button type="button" onclick="removeSubRecipe(${i})" class="text-muted hover:text-red-500 transition-all p-2">
                    <i data-lucide="trash-2"></i>
                </button>
            `;
            container.appendChild(card);
        });
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function removeSubRecipe(index) {
        window.addedSubRecipes.splice(index, 1);
        renderSubRecipeCards();
        calculateTotal();
    }

    function handleSubRecipeSelect(el) {
        const opt = el.options[el.selectedIndex];
        document.getElementById('sub-recipe-unit-display').value = opt.dataset.unit || '';
    }

    // Category & Ingredient Quick Add
    function openCategoryModal() { document.getElementById('createCategoryModal').classList.remove('hidden'); }
    function closeCategoryModal() { document.getElementById('createCategoryModal').classList.add('hidden'); }
    
    function submitQuickCategory() {
        const name = document.getElementById('quick_category_name').value;
        fetch('{{ route("categories.storeQuick", ["kitchen_slug" => request()->route("kitchen_slug")]) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ name: name, type: 'recipe' })
        }).then(r => r.json()).then(res => {
            if (res.success) {
                const select = document.getElementById('category-select');
                select.add(new Option(res.name, res.id, true, true));
                closeCategoryModal();
            }
        });
    }

    function openIngredientModal(name) {
        document.getElementById('quick_name').value = name;
        document.getElementById('createIngredientModal').classList.remove('hidden');
    }
    function closeIngredientModal() { document.getElementById('createIngredientModal').classList.add('hidden'); }

    function submitQuickIngredient() {
        const form = document.getElementById('quickIngredientForm');
        const data = Object.fromEntries(new FormData(form).entries());
        fetch('{{ route("ingredients.storeQuick", ["kitchen_slug" => request()->route("kitchen_slug")]) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(data)
        }).then(r => r.json()).then(res => {
            if (res.success) {
                // Update all selects
                document.querySelectorAll('.ingredient-select').forEach(s => {
                    s.add(new Option(`${res.ingredient.name} (${res.ingredient.unit})`, res.ingredient.id));
                });
                closeIngredientModal();
            }
        });
    }
</script>
@endpush
@endsection