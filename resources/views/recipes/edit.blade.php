@extends('layouts.app')

@section('header')
<div class="flex items-center gap-4 animate-fade-in-down">
    <a href="{{ route('recipes.show', $recipe) }}"
       class="p-2.5 bg-white border border-gray-100 rounded-2xl text-gray-400 hover:text-blue-600 hover:border-blue-200 hover:shadow-xl transition-all duration-300">
        <i data-lucide="arrow-left" class="w-5 h-5 md:w-6 md:h-6"></i>
    </a>
    <div>
        <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">Edit Recipe</h1>
        <p class="text-xs md:text-sm text-gray-500 font-medium mt-1 uppercase tracking-wider">Refining <span class="text-blue-600 font-black">{{ $recipe->name }}</span></p>
    </div>
</div>
@endsection

@section('actions')
<div class="flex items-center gap-3 animate-fade-in-down">
    <button type="button" onclick="window.history.back()"
            class="px-6 py-3 bg-white border border-gray-200 text-gray-600 font-bold rounded-2xl hover:bg-gray-50 hover:border-gray-300 transition-all duration-300 text-sm">
        Cancel
    </button>
    <button type="submit" form="recipeForm"
            class="px-8 py-3 bg-blue-600 text-white font-black rounded-2xl shadow-xl hover:bg-blue-700 flex items-center gap-2 text-sm">
        <i data-lucide="save" class="w-5 h-5"></i> Update Recipe
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
        <div class="w-full lg:w-1/3 flex flex-col gap-8 animate-fade-in-left">
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="p-8 border-b bg-gray-50/30">
                    <h2 class="text-xl font-black text-gray-900 flex items-center gap-3">
                        <div class="p-2 bg-blue-600 rounded-xl text-white shadow-lg">
                            <i data-lucide="info" class="w-5 h-5"></i>
                        </div>
                        Recipe Details
                    </h2>
                </div>
                <div class="p-8 space-y-8">
                    {{-- Recipe Type Selection --}}
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 mb-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Recipe Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex flex-col items-center gap-2 p-3 rounded-xl border-2 transition-all cursor-pointer group bg-white hover:border-blue-200" id="mainTypeLabel">
                                <input type="radio" name="recipe_type_select" value="main" class="absolute top-2 right-2" onchange="updateRecipeType('main')" {{ !$recipe->is_sub_recipe ? 'checked' : '' }}>
                                <div class="p-2 bg-blue-50 rounded-lg text-blue-600 group-hover:scale-110 transition-transform">
                                    <i data-lucide="utensils" class="w-5 h-5"></i>
                                </div>
                                <span class="text-sm font-bold text-gray-700">Main Recipe</span>
                            </label>
                            <label class="relative flex flex-col items-center gap-2 p-3 rounded-xl border-2 transition-all cursor-pointer group bg-white hover:border-indigo-200" id="subTypeLabel">
                                <input type="radio" name="recipe_type_select" value="sub" class="absolute top-2 right-2" onchange="updateRecipeType('sub')" {{ $recipe->is_sub_recipe ? 'checked' : '' }}>
                                <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600 group-hover:scale-110 transition-transform">
                                    <i data-lucide="component" class="w-5 h-5"></i>
                                </div>
                                <span class="text-sm font-bold text-gray-700">Sub-Recipe</span>
                            </label>
                        </div>
                    </div>

                    {{-- Recipe Name --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Recipe Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name"
                               class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none"
                               required value="{{ old('name', $recipe->name) }}">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Category --}}
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-sm font-semibold text-gray-700">Category <span class="text-red-500">*</span></label>
                            <button type="button" onclick="openCategoryModal()" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 bg-blue-50 px-2 py-0.5 rounded transition-all">
                                <i data-lucide="plus" class="w-3 h-3"></i> Quick Add
                            </button>
                        </div>
                        <select name="category_id" id="category-select" required
                                class="w-full px-4 py-2 rounded-lg border {{ $errors->has('category_id') ? 'border-red-500' : 'border-gray-200' }} focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                            <option value="" disabled>Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $recipe->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }} @if($category->type == 'ingredient') (Ingredient) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Smart Scale Section --}}
                    <div class="bg-white rounded-xl p-5 border border-gray-100 mb-4">
                        <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2 mb-3">
                            <i data-lucide="calculator" class="w-4 h-4 text-blue-500"></i>
                            Smart Scale
                        </h3>
                        <div class="flex gap-2">
                            <button type="button" onclick="setMultiplier(0.5)"
                                class="scaling-btn flex-1 px-3 py-2 rounded-lg border-2 border-gray-200 bg-white text-gray-700 font-semibold text-sm hover:bg-gray-50 transition-all"
                                data-multiplier="0.5">0.5x</button>
                            <button type="button" onclick="setMultiplier(1)"
                                class="scaling-btn flex-1 px-3 py-2 rounded-lg border-2 border-blue-500 bg-blue-500 text-white font-semibold text-sm hover:bg-blue-600 transition-all active"
                                data-multiplier="1">1x</button>
                            <button type="button" onclick="setMultiplier(2)"
                                class="scaling-btn flex-1 px-3 py-2 rounded-lg border-2 border-gray-200 bg-white text-gray-700 font-semibold text-sm hover:bg-gray-50 transition-all"
                                data-multiplier="2">2x</button>
                        </div>
                    </div>

                    {{-- Yields Section --}}
                    <div class="bg-gray-50/80 rounded-2xl p-5 border border-gray-100 border-dashed">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Yield Configuration</label>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <label class="block text-xs text-gray-500 font-medium">Portions <span class="text-red-500">*</span></label>
                                    <button type="button" onclick="resetScaling()"
                                        class="text-[10px] text-blue-600 hover:text-blue-800 font-bold uppercase tracking-wider">Reset</button>
                                </div>
                                <input type="number" name="yield_portions" id="yield_portions" min="1" step="1"
                                    value="{{ old('yield_portions', $recipe->yield_portions ?? 1) }}" required
                                    class="w-full px-3 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 outline-none text-center font-bold text-gray-800"
                                    oninput="updateScaling()">
                                @error('yield_portions') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 font-medium mb-1.5">Batches</label>
                                <input type="number" name="yield_batches" min="1" step="1"
                                    value="{{ old('yield_batches', $recipe->yield_batches ?? 1) }}"
                                    class="w-full px-3 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 outline-none text-center font-bold text-gray-800">
                                @error('yield_batches') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Sub-Recipe Configuration --}}
                    <div id="subRecipeConfigSection" class="{{ $recipe->is_sub_recipe ? '' : 'hidden' }}">
                        <div class="bg-indigo-50/50 rounded-2xl p-5 border border-indigo-100">
                            <h3 class="text-sm font-bold text-indigo-900 mb-1">Sub-Recipe Configuration</h3>
                            <p class="text-xs text-indigo-600/80 mb-4">Link this recipe to an ingredient for use in other recipes.</p>

                            <input type="hidden" name="is_sub_recipe" id="is_sub_recipe" value="{{ $recipe->is_sub_recipe ? 1 : 0 }}">

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-indigo-800 uppercase mb-1.5">Produced Ingredient</label>
                                    <select name="produces_ingredient_id" id="produces_ingredient_id"
                                        class="w-full px-3 py-2.5 rounded-lg border border-indigo-200 focus:border-indigo-500 outline-none bg-white text-sm">
                                        <option value="">-- Auto-create ingredient with recipe name --</option>
                                        @foreach($ingredients as $ing)
                                            <option value="{{ $ing->id }}" {{ old('produces_ingredient_id', $recipe->produces_ingredient_id) == $ing->id ? 'selected' : '' }}>
                                                {{ $ing->name }} ({{ $ing->measurement_unit }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-indigo-800 uppercase mb-1.5">Output Qty</label>
                                        <input type="number" name="output_quantity" step="0.001" min="0"
                                            value="{{ old('output_quantity', $recipe->output_quantity ?? 1) }}"
                                            class="w-full px-3 py-2.5 rounded-lg border border-indigo-200 focus:border-indigo-500 outline-none bg-white font-bold">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-indigo-800 uppercase mb-1.5">Output Unit</label>
                                        <select name="output_unit"
                                            class="w-full px-3 py-2.5 rounded-lg border border-indigo-200 focus:border-indigo-500 outline-none bg-white text-sm">
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
                        <label class="block text-sm font-semibold text-gray-700 mb-1">General Method / Overview</label>
                        <textarea name="method"
                                  class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none min-h-[150px]"
                                  required>{{ old('method', $recipe->method) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Sets & Sub-Recipes --}}
        <div class="w-full lg:w-2/3 space-y-8 animate-fade-in-right">
            {{-- Sub-Recipes Used Section --}}
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-black text-gray-900 flex items-center gap-3">
                        <span class="p-2 bg-indigo-600 rounded-xl shadow-lg">
                            <i data-lucide="component" class="w-6 h-6 text-white"></i>
                        </span>
                        Sub-Recipes Used
                    </h2>
                    <button type="button" id="addSubRecipeBtnTop" onclick="openSubRecipeModal()"
                            class="px-6 py-3 bg-indigo-50 text-indigo-700 font-black rounded-2xl hover:bg-indigo-600 hover:text-white hover:shadow-xl transition-all flex items-center gap-2">
                        <i data-lucide="plus" class="w-5 h-5"></i> Add Sub‑Recipe
                    </button>
                </div>
                <div id="sub-recipes-container" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div id="no-sub-recipes-msg" class="col-span-full py-16 text-center bg-gray-50/50 rounded-3xl border-2 border-dashed border-gray-200 text-gray-400">
                        <i data-lucide="package-search" class="w-12 h-12 mx-auto mb-4 opacity-20"></i>
                        <p class="font-black text-gray-500 uppercase tracking-widest text-xs">No sub‑recipes linked</p>
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
                    <div class="stage-block border border-gray-200 rounded-3xl p-8 bg-white relative group shadow-sm hover:shadow-md transition-all" data-stage-index="{{ $index }}">
                        <input type="hidden" name="stages[{{ $index }}][id]" value="{{ data_get($stage, 'id') }}">
                        <button type="button" onclick="removeStage(this)" class="absolute top-6 right-6 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all" title="Remove Set">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                        </button>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Set Name</label>
                                <input type="text" name="stages[{{ $index }}][name]" value="{{ data_get($stage, 'name') }}" required
                                       class="w-full px-4 py-2 rounded-xl border-2 {{ $errors->has('stages.'.$index.'.name') ? 'border-red-500' : 'border-gray-100' }} focus:border-blue-500 outline-none font-bold">
                                @error('stages.' . $index . '.name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Instructions for this Set</label>
                                <textarea name="stages[{{ $index }}][method]" rows="3"
                                          class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 focus:border-blue-500 outline-none resize-y"
                                          placeholder="Describe the steps for this stage...">{{ data_get($stage, 'method') }}</textarea>
                            </div>
                        </div>

                        {{-- Ingredients Table --}}
                        <div class="bg-gray-50/50 rounded-2xl border border-gray-100 overflow-x-auto">
                            <table class="w-full min-w-[600px]">
                                <thead class="bg-gray-100/50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Item</th>
                                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Unit</th>
                                        @if(auth()->user()->isAdmin())
                                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Cost</th>
                                        @endif
                                        <th class="px-4 py-3 text-center"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 stage-ingredients-body">
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
                                        <tr class="group hover:bg-blue-50/30 transition-colors ingredient-row">
                                            <td class="px-4 py-4">
                                                <select name="stages[{{ $index }}][ingredients][{{ $ingIndex }}][ingredient_id]" class="ingredient-select w-full rounded-xl border-2 border-gray-100 focus:border-blue-500 outline-none py-2 px-3 font-medium bg-white" required>
                                                    <option value="{{ $rIngId }}" selected data-price="{{ $rPrice }}" data-unit="{{ $rMeasUnit }}">
                                                        {{ $rName }} @if($rMeasUnit) ({{ $rMeasUnit }}) @endif
                                                    </option>
                                                </select>
                                            </td>
                                            <td class="px-4 py-4">
                                                <input type="number" step="any" name="stages[{{ $index }}][ingredients][{{ $ingIndex }}][quantity]"
                                                       value="{{ $rQty }}" required data-base-qty="{{ $rQty }}"
                                                       class="quantity-input w-full h-[44px] rounded-xl border-2 border-gray-100 focus:border-blue-500 outline-none text-center font-bold text-gray-900 bg-white" />
                                            </td>
                                            <td class="px-4 py-4">
                                                <input type="hidden" name="stages[{{ $index }}][ingredients][{{ $ingIndex }}][unit]" value="{{ $rUnit }}" class="unit-value-input" />
                                                <input type="text" readonly value="{{ $rUnit }}" class="unit-display w-full h-[44px] rounded-xl border-2 border-gray-100 bg-gray-50 font-bold text-sm text-gray-500 cursor-not-allowed text-center" />
                                            </td>
                                            @if(auth()->user()->isAdmin())
                                                <td class="px-4 py-4 text-right font-black text-gray-900 cost-display">{{ number_format((float) $rCost, 2) }}</td>
                                            @endif
                                            <td class="px-4 py-4 text-center">
                                                <button type="button" onclick="removeRow(this)" class="text-gray-300 hover:text-red-500 p-2 rounded-xl hover:bg-red-50 transition-all">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-100/30 border-t border-gray-100">
                                    <tr>
                                        <td colspan="5" class="px-4 py-4">
                                            <div class="flex gap-4">
                                                <button type="button" onclick="addIngredientRow(this)" class="flex-1 py-3 bg-white border-2 border-gray-100 rounded-xl text-blue-600 font-bold hover:border-blue-500 hover:bg-blue-50 transition-all flex items-center justify-center gap-2">
                                                    <i data-lucide="plus-circle" class="w-5 h-5"></i> Add Ingredient
                                                </button>
                                                <button type="button" onclick="openIngredientModal('')" class="px-6 py-3 bg-white border-2 border-gray-100 rounded-xl text-orange-600 font-bold hover:border-orange-500 hover:bg-orange-50 transition-all flex items-center justify-center gap-2">
                                                    <i data-lucide="plus-square" class="w-5 h-5"></i> New Item
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

            <button type="button" id="addStageBtn" onclick="addNewSet()"
                class="w-full py-6 border-4 border-dashed border-gray-100 rounded-3xl text-gray-400 font-black hover:border-blue-200 hover:text-blue-500 hover:bg-blue-50/30 transition-all flex items-center justify-center gap-3 group">
                <div class="p-2 bg-gray-100 rounded-xl group-hover:bg-blue-500 group-hover:text-white transition-all">
                    <i data-lucide="plus" class="w-6 h-6"></i>
                </div>
                <span class="text-lg uppercase tracking-widest">Add Another Set</span>
            </button>
        </div>

        {{-- Sticky Summary Bar --}}
        <div class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-xl border-t border-gray-200 z-[60] shadow-[0_-10px_40px_rgba(0,0,0,0.1)] p-4 md:p-6 animate-fade-in-up">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex flex-wrap items-center gap-8">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Total Recipe Cost</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black text-gray-900">₹<span id="totalCostDisplay">0.00</span></span>
                            <span class="text-sm font-bold text-gray-400">/ Total</span>
                        </div>
                    </div>
                    <div class="flex flex-col border-l border-gray-100 pl-8 md:pl-12">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Cost Per Portion</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-blue-600">₹<span id="costPerPortionDisplay">0.00</span></span>
                            <span class="text-xs font-bold text-gray-400">/ Portion</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <button type="button" onclick="window.history.back()" class="flex-1 md:flex-none px-10 py-5 bg-white border-2 border-gray-200 text-gray-600 font-black rounded-2xl hover:bg-gray-50 hover:border-gray-300 transition-all">Cancel</button>
                    <button type="submit" class="flex-1 md:flex-none px-14 py-5 bg-gradient-to-br from-blue-600 to-indigo-700 text-white font-black rounded-2xl shadow-[0_15px_30px_rgba(37,99,235,0.3)] hover:shadow-[0_20px_40px_rgba(37,99,235,0.4)] hover:-translate-y-1 transition-all flex items-center justify-center gap-3">
                        <i data-lucide="check-circle" class="w-6 h-6"></i> Update Recipe
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Templates --}}
<template id="stageTemplate">
    <div class="stage-block border border-gray-200 rounded-3xl p-8 bg-white relative group shadow-sm hover:shadow-md transition-all">
        <input type="hidden" name="stages[STAGE_INDEX][id]" value="">
        <button type="button" onclick="removeStage(this)" class="absolute top-6 right-6 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all" title="Remove Set">
            <i data-lucide="trash-2" class="w-5 h-5"></i>
        </button>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Set Name</label>
                <input type="text" name="stages[STAGE_INDEX][name]" value="" required
                       class="w-full px-4 py-2 rounded-xl border-2 border-gray-100 focus:border-blue-500 outline-none font-bold">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-bold text-gray-700 mb-2">Instructions for this Set</label>
                <textarea name="stages[STAGE_INDEX][method]" rows="3"
                          class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 focus:border-blue-500 outline-none resize-y"
                          placeholder="Describe the steps for this stage..."></textarea>
            </div>
        </div>
        <div class="bg-gray-50/50 rounded-2xl border border-gray-100 overflow-x-auto">
            <table class="w-full min-w-[600px]">
                <thead class="bg-gray-100/50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Unit</th>
                        @if(auth()->user()->isAdmin())
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Cost</th>
                        @endif
                        <th class="px-4 py-3 text-center"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 stage-ingredients-body"></tbody>
                <tfoot class="bg-gray-100/30 border-t border-gray-100">
                    <tr>
                        <td colspan="5" class="px-4 py-4">
                            <div class="flex gap-4">
                                <button type="button" onclick="addIngredientRow(this)" class="flex-1 py-3 bg-white border-2 border-gray-100 rounded-xl text-blue-600 font-bold hover:border-blue-500 hover:bg-blue-50 transition-all flex items-center justify-center gap-2">
                                    <i data-lucide="plus-circle" class="w-5 h-5"></i> Add Ingredient
                                </button>
                                <button type="button" onclick="openIngredientModal('')" class="px-6 py-3 bg-white border-2 border-gray-100 rounded-xl text-orange-600 font-bold hover:border-orange-500 hover:bg-orange-50 transition-all flex items-center justify-center gap-2">
                                    <i data-lucide="plus-square" class="w-5 h-5"></i> New Item
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
    <tr class="group hover:bg-blue-50/30 transition-colors ingredient-row">
        <td class="px-4 py-4">
            <select name="stages[STAGE_INDEX][ingredients][ROW_INDEX][ingredient_id]" class="ingredient-select w-full rounded-xl border-2 border-gray-100 focus:border-blue-500 outline-none py-2 px-3 font-medium bg-white" required>
                <option value="">Select Item...</option>
            </select>
        </td>
        <td class="px-4 py-4">
            <input type="number" step="any" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][quantity]" required data-base-qty=""
                   class="quantity-input w-full h-[44px] rounded-xl border-2 border-gray-100 focus:border-blue-500 outline-none text-center font-bold text-gray-900 bg-white" placeholder="0" />
        </td>
        <td class="px-4 py-4">
            <input type="hidden" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][unit]" value="" class="unit-value-input" />
            <input type="text" readonly value="" class="unit-display w-full h-[44px] rounded-xl border-2 border-gray-100 bg-gray-50 font-bold text-sm text-gray-500 cursor-not-allowed text-center" placeholder="Unit" />
        </td>
        @if(auth()->user()->isAdmin())
            <td class="px-4 py-4 text-right font-black text-gray-900 cost-display">0.00</td>
        @endif
        <td class="px-4 py-4 text-center">
            <button type="button" onclick="removeRow(this)" class="text-gray-300 hover:text-red-500 p-2 rounded-xl hover:bg-red-50 transition-all">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </td>
    </tr>
</template>

{{-- Modals --}}
@include('recipes.partials.modals')

@push('scripts')
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
                    'price' => (float)($ing->ingredient->latest_price ?? $ing->ingredient->price ?? 0),
                ];
            }
        }
    @endphp
    window.addedSubRecipes = @json($existingSubData);
    let scaleMultiplier = 1;

    // Initialization
    document.addEventListener('DOMContentLoaded', () => {
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
            btn.classList.toggle('bg-blue-500', parseFloat(btn.dataset.multiplier) === val);
            btn.classList.toggle('text-white', parseFloat(btn.dataset.multiplier) === val);
            btn.classList.toggle('active', parseFloat(btn.dataset.multiplier) === val);
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
            subLabel.className = 'relative flex flex-col items-center gap-2 p-3 rounded-xl border-2 transition-all cursor-pointer group bg-indigo-50 border-indigo-500 ring-4 ring-indigo-500/10';
            mainLabel.className = 'relative flex flex-col items-center gap-2 p-3 rounded-xl border-2 transition-all cursor-pointer group bg-white border-gray-100 hover:border-blue-200';
        } else {
            mainLabel.className = 'relative flex flex-col items-center gap-2 p-3 rounded-xl border-2 transition-all cursor-pointer group bg-blue-50 border-blue-500 ring-4 ring-blue-500/10';
            subLabel.className = 'relative flex flex-col items-center gap-2 p-3 rounded-xl border-2 transition-all cursor-pointer group bg-white border-gray-100 hover:border-indigo-200';
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

    function calculateRowCost(row) {
        const select = row.querySelector('.ingredient-select');
        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const costDisplay = row.querySelector('.cost-display');
        if (!costDisplay || !select) return;

        const opt = select.options[select.selectedIndex];
        const price = parseFloat(opt?.dataset?.price) || 0;
        costDisplay.textContent = (price * qty).toFixed(2);
        calculateTotal();
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
            total += (s.qty * s.price);
        });

        const portions = parseFloat(document.getElementById('yield_portions').value) || 1;
        document.getElementById('totalCostDisplay').textContent = total.toFixed(2);
        document.getElementById('costPerPortionDisplay').textContent = (total / portions).toFixed(2);
    }

    // Sub-Recipe Modal Functions
    function openSubRecipeModal() { document.getElementById('addSubRecipeModal').classList.remove('hidden'); }
    function closeSubRecipeModal() { document.getElementById('addSubRecipeModal').classList.add('hidden'); }
    
    function confirmAddSubRecipe() {
        const select = document.getElementById('sub-recipe-selector');
        const opt = select.options[select.selectedIndex];
        const qty = parseFloat(document.getElementById('sub-recipe-qty').value);
        
        if (!opt.value || isNaN(qty)) return alert('Please select a sub-recipe and quantity');
        
        window.addedSubRecipes.push({
            id: opt.value,
            ingId: opt.dataset.ingId,
            name: opt.dataset.name,
            qty: qty,
            unit: opt.dataset.unit,
            price: parseFloat(opt.dataset.price)
        });
        
        renderSubRecipeCards();
        calculateTotal();
        closeSubRecipeModal();
    }

    function renderSubRecipeCards() {
        const container = document.getElementById('sub-recipes-container');
        container.querySelectorAll('.sub-recipe-card').forEach(c => c.remove());
        document.getElementById('no-sub-recipes-msg').classList.toggle('hidden', window.addedSubRecipes.length > 0);

        window.addedSubRecipes.forEach((s, i) => {
            const cost = (s.qty * s.price).toFixed(2);
            const card = document.createElement('div');
            card.className = 'sub-recipe-card bg-indigo-50 border border-indigo-100 rounded-2xl p-5 flex justify-between items-center animate-fade-in-up';
            card.innerHTML = `
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-white rounded-xl shadow-sm text-indigo-500">
                        <i data-lucide="component" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-black text-gray-900">${s.name}</h4>
                        <p class="text-sm font-bold text-indigo-600">${s.qty} ${s.unit} • ₹${cost}</p>
                    </div>
                </div>
                <button type="button" onclick="removeSubRecipe(${i})" class="text-gray-400 hover:text-red-500 p-2 rounded-xl hover:bg-white transition-all">
                    <i data-lucide="trash-2" class="w-5 h-5"></i>
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