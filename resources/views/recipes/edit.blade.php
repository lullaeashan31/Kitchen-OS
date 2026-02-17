@extends('layouts.app')

@section('header')
    <div>
        <div class="flex items-center gap-3 md:gap-4">
            <a href="{{ route('recipes.show', $recipe) }}"
                class="p-2 md:p-2.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5 md:w-6 md:h-6"></i>
            </a>
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-800">Edit Recipe</h1>
                <p class="text-xs md:text-sm text-gray-500">Refining {{ $recipe->name }}</p>
            </div>
        </div>
    </div>
@endsection

@section('actions')
    <div class="flex items-center gap-2 md:gap-3">
        <button type="button" onclick="window.history.back()"
            class="px-4 md:px-6 py-2 md:py-2.5 bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg shadow-sm hover:bg-gray-50 transition-all text-sm md:text-base">
            <span class="hidden sm:inline">Cancel</span>
            <span class="sm:hidden">✕</span>
        </button>
        <button type="submit"
            class="px-4 md:px-6 py-2 md:py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-lg shadow-lg hover:shadow-blue-500/30 transition-all text-sm md:text-base">
            <span class="hidden sm:inline">Update Recipe</span>
            <span class="sm:hidden">Update</span>
        </button>
    </div>
@endsection

@section('content')
    <form action="{{ route('recipes.update', $recipe) }}" method="POST" id="recipeForm"
        class="flex flex-col xl:flex-row gap-4 md:gap-6">
        @csrf
        @method('PUT')

        <!-- Left Column: Primary Details -->
        <div class="w-full xl:w-1/3 flex flex-col gap-4 md:gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-6">
                <h2 class="text-base md:text-lg font-bold text-gray-800 mb-3 md:mb-4 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 md:w-5 md:h-5 text-blue-500"></i> Basic Info
                </h2>

                <div class="space-y-3 md:space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Recipe Name</label>
                        <input type="text" name="name"
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                            required value="{{ old('name', $recipe->name) }}">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="category_id" required id="category-select"
                                class="w-full px-4 py-2 rounded-lg border {{ $errors->has('category_id') ? 'border-red-500' : 'border-gray-200' }} focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all appearance-none">
                                <option value="" disabled>Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $recipe->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    @push('scripts')
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            new TomSelect('#category-select', {
                                create: false,
                                sortField: {
                                    field: "text",
                                    direction: "asc"
                                },
                                placeholder: "Search Category...",
                            });
                        });
                    </script>
                        <!-- Sub-Recipe Modal [NEW] -->
    <div id="addSubRecipeModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i data-lucide="component" class="w-6 h-6 text-indigo-500"></i>
                    Add Sub-Recipe
                </h3>
                <button type="button" onclick="closeSubRecipeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <div class="p-8 space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Select Sub-Recipe</label>
                    <select id="sub-recipe-selector" class="w-full">
                        <option value="">Search Sub-Recipes...</option>
                        @foreach($subRecipes as $sub)
                            <option value="{{ $sub->id }}" 
                                data-name="{{ $sub->name }}"
                                data-ing-id="{{ $sub->produces_ingredient_id }}"
                                data-unit="{{ $sub->producesIngredient->measurement_unit ?? 'pcs' }}"
                                data-price="{{ $sub->producesIngredient->latest_price ?? $sub->producesIngredient->price ?? 0 }}">
                                {{ $sub->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Quantity</label>
                        <input type="number" id="sub-recipe-qty" step="any" value="1"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all text-center text-lg font-bold">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Unit</label>
                        <input type="text" id="sub-recipe-unit-display" disabled
                            class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 text-gray-500 text-center text-lg font-medium">
                    </div>
                </div>
            </div>
            
            <div class="p-6 bg-gray-50 flex gap-3">
                <button type="button" onclick="closeSubRecipeModal()"
                    class="flex-1 py-3 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-all">
                    Cancel
                </button>
                <button type="button" onclick="confirmAddSubRecipe()"
                    class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                    <i data-lucide="plus" class="w-5 h-5"></i> Add to Recipe
                </button>
            </div>
        </div>
    </div>

    <script>
        // Pre-load existing sub-recipes from "Sub-Recipes" stage
        @php
            $subRecipeStage = $recipe->stages->where('name', 'Sub-Recipes')->first();
            $existingSubData = [];
            if ($subRecipeStage) {
                foreach($subRecipeStage->ingredients as $ing) {
                    $existingSubData[] = [
                        'ingId' => $ing->ingredient_id,
                        'name' => $ing->ingredient->name,
                        'qty' => (float)$ing->quantity,
                        'unit' => $ing->unit,
                        'price' => (float)($ing->ingredient->latest_price ?? $ing->ingredient->price ?? 0)
                    ];
                }
            }
        @endphp
        window.initialSubRecipes = @json($existingSubData);
        window.subRecipeStageId = {{ $subRecipeStage->id ?? 'null' }};
    </script>
@endpush

                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Yields (Fill at least
                            one)</label>
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Row 1 -->
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="block text-xs text-gray-400">Portions <span class="text-red-500">*</span></label>
                                    <button type="button" onclick="resetScaling()" class="text-[10px] text-blue-600 hover:text-blue-800 font-bold uppercase tracking-wider">Reset</button>
                                </div>
                                <input type="number" name="yield_portions" id="yield_portions"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none font-bold text-gray-800"
                                    min="1" step="1" value="{{ old('yield_portions', $recipe->yield_portions ? (int)$recipe->yield_portions : '') }}"
                                    placeholder="e.g. 10" oninput="updateScaling()">
                                @error('yield_portions') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Batches</label>
                                <input type="number" name="yield_batches"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none"
                                    min="1" step="1" value="{{ old('yield_batches', $recipe->yield_batches ? (int)$recipe->yield_batches : '') }}"
                                    placeholder="e.g. 1">
                                @error('yield_batches') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            </div>
                    </div>

                    <!-- Sub-Recipe Toggle [NEW] -->
                    <div class="bg-indigo-50/50 rounded-xl p-5 border border-indigo-100">
                        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSubRecipe()">
                            <div>
                                <h3 class="text-sm font-bold text-indigo-900">Sub-Recipe Mode</h3>
                                <p class="text-xs text-indigo-600/80">Does this recipe produce an ingredient?</p>
                            </div>
                            <div class="relative inline-block w-10 h-6 transition-colors duration-200 ease-in-out border-2 border-transparent rounded-full cursor-pointer {{ $recipe->is_sub_recipe ? 'bg-indigo-500' : 'bg-gray-200' }}"
                                id="subRecipeToggleBg">
                                <span
                                    class="{{ $recipe->is_sub_recipe ? 'translate-x-4' : 'translate-x-0' }} inline-block w-5 h-5 transition duration-200 ease-in-out transform bg-white rounded-full shadow pointer-events-none"
                                    id="subRecipeToggleDot"></span>
                            </div>
                        </div>

                        <input type="hidden" name="is_sub_recipe" id="is_sub_recipe" value="{{ old('is_sub_recipe', $recipe->is_sub_recipe ? 1 : 0) }}">

                        <div id="subRecipeFields" class="{{ $recipe->is_sub_recipe ? '' : 'hidden' }} space-y-4 pt-4 border-t border-indigo-100 mt-2">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-indigo-800 uppercase mb-1.5">Output Qty</label>
                                    <input type="number" name="output_quantity" step="0.001" min="0"
                                        value="{{ old('output_quantity', $recipe->output_quantity ?? 1) }}"
                                        class="w-full px-3 py-2.5 rounded-lg border border-indigo-200 focus:border-indigo-500 outline-none bg-white placeholder-indigo-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-indigo-800 uppercase mb-1.5">Output Unit</label>
                                    <select name="output_unit"
                                        class="w-full px-3 py-2.5 rounded-lg border border-indigo-200 focus:border-indigo-500 outline-none bg-white text-sm">
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->value }}" {{ old('output_unit', $recipe->output_unit->value ?? '') == $unit->value ? 'selected' : '' }}>
                                                {{ $unit->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Method / Instructions</label>
                        <textarea name="method"
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all min-h-[300px]"
                            required>{{ old('method', $recipe->method) }}</textarea>
                    </div>
                </div>
            </div>

            <button type="submit"
                class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                <i data-lucide="save" class="w-5 h-5"></i> Update Recipe
            </button>
        </div>

        <!-- Right Column: Ingredients -->
        <div class="w-full xl:w-2/3 space-y-4 md:space-y-6">
            <!-- Recipe Sets Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="layers" class="w-5 h-5 text-blue-500"></i>
                        Recipe Sets
                    </h2>
                    <button type="button" onclick="addStage()"
                        class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 font-medium transition-colors flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Add Set
                    </button>
                </div>

                <div id="stages-container" class="space-y-8">
                    @php
                        $stages = old('stages') ?? $recipe->stages ?? collect([]);
                        // Ensure stages is a collection
                        if (!($stages instanceof \Illuminate\Support\Collection)) {
                            $stages = collect($stages);
                        }
                    @endphp

                    @foreach($stages->where('name', '!=', 'Sub-Recipes') as $index => $stage)
                        <div class="stage-block border border-gray-200 rounded-xl p-6 bg-gray-50/50 relative group transition-all hover:border-blue-200 hover:shadow-sm"
                            data-stage-index="{{ $index }}">

                            <input type="hidden" name="stages[{{ $index }}][id]" value="{{ data_get($stage, 'id') }}">

                            <button type="button" onclick="removeStage(this)"
                                class="absolute top-4 right-4 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity"
                                title="Remove Set">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div class="col-span-1">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Set Name</label>
                                    <input type="text" name="stages[{{ $index }}][name]" value="{{ data_get($stage, 'name') }}"
                                        required
                                        class="w-full px-4 py-2 rounded-lg border {{ $errors->has('stages.'.$index.'.name') ? 'border-red-500' : 'border-gray-200' }} focus:border-blue-500 outline-none transition-colors"
                                        placeholder="e.g., Marination, Sauce, Assembly">
                                    @error('stages.' . $index . '.name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Method & Instructions</label>
                                    <textarea name="stages[{{ $index }}][method]" rows="3"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition-colors resize-y"
                                        placeholder="Describe the steps for this stage...">{{ data_get($stage, 'method') }}</textarea>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                <div class="overflow-x-auto -mx-2 md:mx-0">
                                    <table class="w-full min-w-[600px]">
                                        <thead class="bg-gray-50 border-b border-gray-200">
                                            <tr>
                                                <th
                                                    class="px-2 md:px-4 py-2 md:py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[50%]">
                                                    Item</th>
                                                <th
                                                    class="px-2 md:px-4 py-2 md:py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[20%]">
                                                    Quantity</th>
                                                <th
                                                    class="px-2 md:px-4 py-2 md:py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[20%]">
                                                    Unit</th>
                                                @if(auth()->user()->isAdmin())
                                                <th
                                                    class="px-2 md:px-4 py-2 md:py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-[10%]">
                                                    Cost</th>
                                                @endif
                                                <th
                                                    class="px-2 md:px-4 py-2 md:py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-[5%]">
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 stage-ingredients-body">
                                            @php
                                                // Safely get ingredients - handle both model and array
                                                $stageIngredients = [];
                                                try {
                                                    if (is_object($stage)) {
                                                        // Handle Eloquent model
                                                        if (isset($stage->ingredients)) {
                                                            $stageIngredients = $stage->ingredients;
                                                            // Convert collection to array if needed
                                                            if ($stageIngredients instanceof \Illuminate\Support\Collection) {
                                                                $stageIngredients = $stageIngredients->all();
                                                            }
                                                        }
                                                    } elseif (is_array($stage)) {
                                                        $stageIngredients = $stage['ingredients'] ?? [];
                                                    }
                                                    
                                                    // Ensure it's iterable
                                                    if (!is_iterable($stageIngredients)) {
                                                        $stageIngredients = [];
                                                    }
                                                } catch (\Exception $e) {
                                                    $stageIngredients = [];
                                                }
                                            @endphp
                                            @foreach($stageIngredients as $ingIndex => $rIngredient)
                                                @php
                                                    // Safely extract data
                                                    $rIngId = null;
                                                    $rName = 'Unknown';
                                                    $rUnit = 'pcs';
                                                    $rQty = 0;
                                                    $rGroup = '';
                                                    $rCost = 0;
                                                    $rPrice = 0;
                                                    $rMeasUnit = 'pcs';

                                                    if (is_object($rIngredient)) {
                                                        $rIngId = $rIngredient->ingredient_id ?? $rIngredient->id ?? null;
                                                        $rUnit = $rIngredient->unit ?? 'pcs';
                                                        $rQty = $rIngredient->quantity ?? 0;
                                                        $rGroup = $rIngredient->ingredient_group ?? '';
                                                        $rCost = $rIngredient->cost ?? 0;
                                                        
                                                        // Safely access ingredient relationship
                                                        if (isset($rIngredient->ingredient) && is_object($rIngredient->ingredient)) {
                                                            $rName = $rIngredient->ingredient->name ?? 'Unknown';
                                                            $rPrice = $rIngredient->ingredient->latest_price ?? $rIngredient->ingredient->price ?? 0;
                                                            $rMeasUnit = $rIngredient->ingredient->measurement_unit ?? 'pcs';
                                                        } else {
                                                            $rName = 'Unknown Ingredient';
                                                            $rPrice = 0;
                                                            $rMeasUnit = 'pcs';
                                                        }
                                                    } elseif (is_array($rIngredient)) {
                                                        $rIngId = $rIngredient['ingredient_id'] ?? $rIngredient['id'] ?? null;
                                                        $rUnit = $rIngredient['unit'] ?? 'pcs';
                                                        $rQty = $rIngredient['quantity'] ?? 0;
                                                        $rGroup = $rIngredient['ingredient_group'] ?? '';
                                                        $rCost = $rIngredient['cost'] ?? 0;
                                                        
                                                        // Safely access ingredient data
                                                        if (isset($rIngredient['ingredient']) && is_array($rIngredient['ingredient'])) {
                                                            $rName = $rIngredient['ingredient']['name'] ?? 'Unknown';
                                                            $rPrice = $rIngredient['ingredient']['latest_price'] ?? $rIngredient['ingredient']['price'] ?? 0;
                                                            $rMeasUnit = $rIngredient['ingredient']['measurement_unit'] ?? 'pcs';
                                                        } else {
                                                            $rName = $rIngredient['name'] ?? 'Unknown Ingredient';
                                                            $rPrice = 0;
                                                            $rMeasUnit = 'pcs';
                                                        }
                                                    }
                                                @endphp
                                                <tr class="group hover:bg-blue-50/30 transition-colors ingredient-row">
                                                    <td class="px-4 py-3">
                                                        <div class="w-full {{ $errors->has('stages.'.$index.'.ingredients.'.$ingIndex.'.ingredient_id') ? 'border border-red-500 rounded-lg' : '' }}">
                                                        <select class="ingredient-select w-full"
                                                            name="stages[{{ $index }}][ingredients][{{ $ingIndex }}][ingredient_id]"
                                                            required>
                                                            <option value="{{ $rIngId }}" selected data-price="{{ $rPrice }}"
                                                                data-unit="{{ $rMeasUnit }}">
                                                                {{ $rName }}
                                                                @if($rMeasUnit) ({{ $rMeasUnit }}) @endif
                                                            </option>
                                                            <!-- Other options injected via JS or fallback -->
                                                        </select>
                                                        </div>
                                                    <td class="px-2 md:px-4 py-2 md:py-3">
                                                            <input type="number" step="any"
                                                                name="stages[{{ $index }}][ingredients][{{ $ingIndex }}][quantity]"
                                                                value="{{ $rQty }}" required
                                                                data-base-qty="{{ $rQty }}"
                                                                class="quantity-input w-full h-[40px] md:h-[44px] px-2 md:px-3 rounded-xl border-2 {{ $errors->has('stages.'.$index.'.ingredients.'.$ingIndex.'.quantity') ? 'border-red-500' : 'border-gray-300' }} focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none text-center font-bold text-sm md:text-base text-gray-900 transition-all bg-white">
                                                        @error('stages.' . $index . '.ingredients.' . $ingIndex . '.quantity') <p
                                                        class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                                    </td>
                                                    <td class="px-2 md:px-4 py-2 md:py-3">
                                                        <!-- Hidden input for form submission -->
                                                        <input type="hidden" name="stages[{{ $index }}][ingredients][{{ $ingIndex }}][unit]" 
                                                            value="{{ $rUnit ?? '' }}" 
                                                            class="unit-value-input">
                                                        <!-- Display field (readonly) -->
                                                        @php
                                                            $unitDisplayValue = '';
                                                            if ($rUnit) {
                                                                foreach (\App\Enums\Unit::cases() as $unit) {
                                                                    if ($unit->value == $rUnit) {
                                                                        $unitDisplayValue = $unit->label();
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        @endphp
                                                        <input type="text" 
                                                            value="{{ $unitDisplayValue }}" 
                                                            readonly
                                                            class="unit-display w-full h-[40px] md:h-[44px] px-2 md:px-3 rounded-xl border-2 border-gray-300 bg-gray-50 font-bold text-sm md:text-base text-gray-600 cursor-not-allowed"
                                                            placeholder="Select item first">
                                                        @error('stages.' . $index . '.ingredients.' . $ingIndex . '.unit') <p
                                                        class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                                    </td>
                                                    <!-- Hidden field to preserve data, not shown in UI -->
                                                    <input type="hidden"
                                                        name="stages[{{ $index }}][ingredients][{{ $ingIndex }}][ingredient_group]"
                                                        value="{{ $rGroup }}">
                                                    @if(auth()->user()->isAdmin())
                                                    <td class="px-2 md:px-4 py-2 md:py-3 text-right font-medium text-gray-700 cost-display text-sm">
                                                        {{ number_format((float) $rCost, 2) }}
                                                    </td>
                                                    @endif
                                                    <td class="px-2 md:px-4 py-2 md:py-3 text-center">
                                                        <button type="button" onclick="removeRow(this)"
                                                            class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50">
                                                            <i data-lucide="x" class="w-4 h-4"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-gray-50 border-t border-gray-200">
                                            <tr>
                                                <td colspan="6" class="px-4 py-3">
                                                    <button type="button" onclick="addIngredientRow(this)"
                                                        class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                                                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                                        Add Ingredient
                                                    </button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Sub-Recipes Used Section [NEW] -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mt-6 overflow-hidden">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <i data-lucide="component" class="w-5 h-5 text-indigo-500"></i>
                            Sub-Recipes Used
                        </h2>
                        <button type="button" onclick="openSubRecipeModal()"
                            class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 font-medium transition-colors flex items-center gap-2">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Add Sub-Recipe
                        </button>
                    </div>
                    
                    <div id="sub-recipes-container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Sub-recipe cards will be injected here -->
                        <div id="no-sub-recipes-msg" class="col-span-full py-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200 text-gray-400">
                            No sub-recipes added yet.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Actions -->
            <div class="flex justify-end gap-3 sticky bottom-6 z-10">
                <button type="button" onclick="window.history.back()"
                    class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg shadow-sm hover:bg-gray-50 transition-all">
                    Cancel
                </button>
                <button type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-lg shadow-lg hover:shadow-blue-500/30 transition-all">
                    Update Recipe
                </button>
            </div>
        </div>
    </form>

    <!-- Create Ingredient Modal & Templates (Same as Create) -->
    <div id="createIngredientModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <!-- ... (Modal content same as Create) ... -->
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Create New Ingredient</h3>
                <button type="button" onclick="closeIngredientModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="quickIngredientForm">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" id="quick_name" required
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Category</label>
                        <select name="category_id" id="quick_category_id" required
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none bg-white">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Storage Location</label>
                        <select name="storage_location" id="quick_storage_location" required
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none bg-white">
                            <option value="Fridge">Fridge</option>
                            <option value="Freezer">Freezer</option>
                            <option value="Dry Store">Dry Store</option>
                            <option value="Bar">Bar</option>
                            <option value="Cellar">Cellar</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Unit</label>
                        <select name="measurement_unit" id="quick_measurement_unit" required
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none bg-white">
                            @foreach($units as $unit)
                                <option value="{{ $unit->value }}">{{ $unit->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" onclick="submitQuickIngredient()"
                        class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-md transition-all mt-4">
                        Create & Select
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Ingredient Options for New Rows -->
    <div id="ingredientOptions" style="display: none;">
        @foreach(\App\Models\Ingredient::orderBy('name')->get() as $ing)
            <option value="{{ $ing->id }}" data-price="{{ $ing->latest_price ?? $ing->price }}"
                data-unit="{{ $ing->measurement_unit }}">
                {{ $ing->name }} ({{ $ing->measurement_unit }})
            </option>
        @endforeach
    </div>

    <!-- Templates -->
    <template id="stageTemplate">
        <div
            class="stage-block border border-gray-200 rounded-xl p-6 bg-gray-50/50 relative group transition-all hover:border-blue-200 hover:shadow-sm">
            <button type="button" onclick="removeStage(this)"
                class="absolute top-4 right-4 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity"
                title="Remove Set">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
            </button>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="col-span-1">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Set Name</label>
                    <input type="text" name="stages[STAGE_INDEX][name]" value="Main" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition-colors"
                        placeholder="e.g., Marination, Sauce, Assembly">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Method & Instructions</label>
                    <textarea name="stages[STAGE_INDEX][method]" rows="3"
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition-colors resize-y"
                        placeholder="Describe the steps for this set..."></textarea>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto -mx-2 md:mx-0">
                    <table class="w-full min-w-[600px]">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-2 md:px-4 py-2 md:py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[50%]">
                                    Item</th>
                                <th
                                    class="px-2 md:px-4 py-2 md:py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[20%]">
                                    Quantity</th>
                                <th
                                    class="px-2 md:px-4 py-2 md:py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[20%]">
                                    Unit</th>
                                @if(auth()->user()->isAdmin())
                                <th
                                    class="px-2 md:px-4 py-2 md:py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-[10%]">
                                    Cost</th>
                                @endif
                                <th
                                    class="px-2 md:px-4 py-2 md:py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-[5%]">
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 stage-ingredients-body">
                            <!-- Ingredient Rows -->
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="{{ auth()->user()->isAdmin() ? 6 : 5 }}" class="px-4 py-3">
                                    <button type="button" onclick="addIngredientRow(this)"
                                        class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                        Add Ingredient
                                    </button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </template>

    <template id="ingredientRowTemplate">
        <tr class="group hover:bg-blue-50/30 transition-colors ingredient-row">
            <td class="px-4 py-3">
                <select class="ingredient-select w-full" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][ingredient_id]"
                    required>
                    <option value="">Select Ingredient...</option>
                </select>
            </td>
            <td class="px-2 md:px-4 py-2 md:py-3">
                <input type="number" step="any" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][quantity]" required
                    data-base-qty=""
                    class="quantity-input w-full h-[40px] md:h-[44px] px-2 md:px-3 rounded-xl border-2 border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none text-center font-bold text-sm md:text-base text-gray-900 transition-all bg-white">
            </td>
            <td class="px-2 md:px-4 py-2 md:py-3">
                <!-- Hidden input for form submission -->
                <input type="hidden" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][unit]" 
                    value="" 
                    class="unit-value-input">
                <!-- Display field (readonly) -->
                <input type="text" 
                    value="" 
                    readonly
                    class="unit-display w-full h-[40px] md:h-[44px] px-2 md:px-3 rounded-xl border-2 border-gray-300 bg-gray-50 font-bold text-sm md:text-base text-gray-600 cursor-not-allowed"
                    placeholder="Select item first">
            </td>
            <!-- Hidden field to preserve data, not shown in UI -->
            <input type="hidden" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][ingredient_group]" value="">
            @if(auth()->user()->isAdmin())
            <td class="px-2 md:px-4 py-2 md:py-3 text-right font-medium text-gray-700 cost-display text-sm">
                0.00
            </td>
            @endif
            <td class="px-2 md:px-4 py-2 md:py-3 text-center">
                <button type="button" onclick="removeRow(this)"
                    class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </td>
        </tr>
    </template>
    <!-- Hidden Sub-Recipe Options -->
        <div id="subRecipeOptions" style="display: none;">
            @foreach($subRecipes as $sub)
                <option value="{{ $sub->produces_ingredient_id }}"
                        data-price="{{ $sub->producesIngredient->latest_price ?? $sub->producesIngredient->price ?? 0 }}"
                        data-unit="{{ $sub->producesIngredient->measurement_unit }}">
                    {{ $sub->name }} ({{ $sub->producesIngredient->measurement_unit }})
                </option>
            @endforeach
        </div>
@endsection

@push('scripts')
    <style>
        /* Hide disabled options in Tom Select - Broad selectors for safety */
        .ts-dropdown .option[data-selectable].disabled,
        .ts-dropdown .option.disabled,
        .ts-dropdown .dropdown-item.disabled {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            opacity: 0 !important;
        }

        /* Unit Display Styling (Readonly) */
        .unit-display {
            min-height: 44px !important;
            font-size: 16px !important;
            font-weight: 700 !important;
            color: #4b5563 !important;
            background-color: #f9fafb !important;
            cursor: not-allowed !important;
        }

        /* TomSelect Control Styling */
        .ts-control {
            border: none !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            cursor: pointer !important;
            position: relative !important;
        }

        .ts-control .item {
            font-weight: 500;
            color: #1f2937;
            cursor: pointer !important;
            pointer-events: auto !important;
        }

        .ts-control .item:hover {
            background-color: #f3f4f6 !important;
        }

        /* Ensure TomSelect control is clickable even when item is selected */
        .ts-control,
        .ts-control input,
        .ts-control .item,
        .ts-wrapper {
            pointer-events: auto !important;
            cursor: pointer !important;
        }

        /* Make input field clickable */
        .ts-wrapper input.ts-input {
            cursor: pointer !important;
            pointer-events: auto !important;
        }

        /* TomSelect Dropdown Visibility Fix */
        .ts-dropdown {
            z-index: 9999 !important;
            position: absolute !important;
            max-height: 300px !important;
            overflow-y: auto !important;
            background: white !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }

        .ts-dropdown.hidden {
            display: none !important;
        }

        /* Ensure equal visual weight with quantity input */
        .quantity-input {
            min-height: 44px !important;
            font-size: 16px !important;
            font-weight: 700 !important;
        }
    </style>
    <script>
        let stageCount = {{ count(old('stages') ?? $recipe->stages) }};
        let activeSelect = null; // Track which select triggered the modal
        const ingredientOptionsHTML = document.getElementById('ingredientOptions').innerHTML;
        const UNIT_FACTORS = { 'g': 1, 'kg': 1000, 'ml': 1, 'l': 1000, 'tbsp': 15, 'tsp': 5, 'cup': 240, 'pcs': 1, 'oz': 28.35, 'lb': 453.6 };
        const UNIT_LABELS = {
            'g': 'Gram (g)',
            'kg': 'Kilogram (kg)',
            'ml': 'Milliliter (ml)',
            'l': 'Liter (l)',
            'tbsp': 'Tablespoon (tbsp)',
            'tsp': 'Teaspoon (tsp)',
            'cup': 'Cup',
            'pcs': 'Piece (pcs)'
        };
        
        let subRecipeSelector = null;
        window.addedSubRecipes = window.initialSubRecipes || [];
        window.userIsAdmin = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};

        document.addEventListener('DOMContentLoaded', () => {
            // Initialize existing rows
            document.querySelectorAll('.ingredient-row').forEach(row => {
                initRow(row);
                
                // Auto-populate unit for already selected ingredients
                const ingSelect = row.querySelector('.ingredient-select');
                const unitValueInput = row.querySelector('.unit-value-input');
                const unitDisplay = row.querySelector('.unit-display');
                
                if (ingSelect && ingSelect.value && unitValueInput && unitDisplay) {
                    const selectedOption = ingSelect.querySelector(`option[value="${ingSelect.value}"]`);
                    if (selectedOption && selectedOption.dataset.unit) {
                        const ingredientUnit = selectedOption.dataset.unit;
                        const unitLabel = UNIT_LABELS[ingredientUnit] || ingredientUnit;
                        
                        // Only update if unit is not already set
                        if (!unitValueInput.value || unitValueInput.value === '') {
                            unitValueInput.value = ingredientUnit;
                            unitDisplay.value = unitLabel;
                        }
                    }
                }
            });
            calculateTotal();

            // Set initial base portions from recipe (ensure integer)
            window.basePortions = {{ $recipe->yield_portions ? (int)$recipe->yield_portions : 10 }};

            // STEP 1: Base quantity + original state on form load
            document.querySelectorAll('.quantity-input').forEach(input => {
                if (!input.dataset.baseQty || input.dataset.baseQty === '') {
                    const v = input.value || '';
                    if (v) input.dataset.baseQty = v;
                }
                setQuantityHighlight(input, 'original');
            });

            // Initialize Sub-Recipe Selector
            const subSelEl = document.getElementById('sub-recipe-selector');
            if (subSelEl) {
                subRecipeSelector = new TomSelect(subSelEl, {
                    create: false,
                    sortField: { field: "text", direction: "asc" },
                    placeholder: 'Search for a sub-recipe...',
                    plugins: ['dropdown_input'],
                    onChange: function(val) {
                        const opt = this.options[val];
                        const originalOpt = document.querySelector(`#sub-recipe-selector option[value="${val}"]`);
                        if (opt || originalOpt) {
                            const unitDisplay = document.getElementById('sub-recipe-unit-display');
                            if (unitDisplay) {
                                unitDisplay.value = originalOpt?.dataset?.unit || opt?.dataset?.unit || 'pcs';
                            }
                        }
                    }
                });
            }

            // Pre-load existing cards
            renderSubRecipeCards();

            // Form Submit Override
            const form = document.getElementById('recipeForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Inject Sub-Recipes as a special stage if any exist
                    if (window.addedSubRecipes.length > 0) {
                        const stageIdx = 999;
                        const container = document.createElement('div');
                        container.style.display = 'none';
                        
                        const nameInput = document.createElement('input');
                        nameInput.type = 'hidden';
                        nameInput.name = `stages[${stageIdx}][name]`;
                        nameInput.value = 'Sub-Recipes';
                        container.appendChild(nameInput);
                        
                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = `stages[${stageIdx}][method]`;
                        methodInput.value = 'Included sub-recipes';
                        container.appendChild(methodInput);

                        window.addedSubRecipes.forEach((sub, idx) => {
                            const idInput = document.createElement('input');
                            idInput.type = 'hidden';
                            idInput.name = `stages[${stageIdx}][ingredients][${idx}][ingredient_id]`;
                            idInput.value = sub.ingId;
                            container.appendChild(idInput);
                            
                            const qtyInput = document.createElement('input');
                            qtyInput.type = 'hidden';
                            qtyInput.name = `stages[${stageIdx}][ingredients][${idx}][quantity]`;
                            qtyInput.value = sub.qty;
                            container.appendChild(qtyInput);
                            
                            const unitInput = document.createElement('input');
                            unitInput.type = 'hidden';
                            unitInput.name = `stages[${stageIdx}][ingredients][${idx}][unit]`;
                            unitInput.value = sub.unit;
                            container.appendChild(unitInput);

                            const groupInput = document.createElement('input');
                            groupInput.type = 'hidden';
                            groupInput.name = `stages[${stageIdx}][ingredients][${idx}][ingredient_group]`;
                            groupInput.value = 'Sub-Recipe';
                            container.appendChild(groupInput);
                        });
                        form.appendChild(container);
                    }
                });
            }
        });

        function toggleSubRecipe(forceState = null) {
            const fields = document.getElementById('subRecipeFields');
            const bg = document.getElementById('subRecipeToggleBg');
            const dot = document.getElementById('subRecipeToggleDot');
            const input = document.getElementById('is_sub_recipe');

            const isHidden = fields.classList.contains('hidden');
            const newState = forceState !== null ? forceState : isHidden;

            if (newState) {
                fields.classList.remove('hidden');
                bg.classList.remove('bg-gray-200');
                bg.classList.add('bg-indigo-500');
                dot.classList.add('translate-x-4');
                dot.classList.remove('translate-x-0');
                input.value = "1";
            } else {
                fields.classList.add('hidden');
                bg.classList.remove('bg-indigo-500');
                bg.classList.add('bg-gray-200');
                dot.classList.remove('translate-x-4');
                dot.classList.add('translate-x-0');
                input.value = "0";
            }
        }

        function addStage() {
            const container = document.getElementById('stages-container');
            const template = document.getElementById('stageTemplate');
            const clone = template.content.cloneNode(true);

            const stageBlock = clone.querySelector('.stage-block');
            stageBlock.dataset.stageIndex = stageCount;

            // Update names
            const stageInputs = stageBlock.querySelectorAll('input, textarea');
            stageInputs.forEach(input => {
                if (input.name) {
                    input.name = input.name.replace('STAGE_INDEX', stageCount);
                }
            });

            // Add one default ingredient row
            const tbody = stageBlock.querySelector('.stage-ingredients-body');
            addIngredientRowToTbody(tbody, stageCount);

            container.appendChild(stageBlock);
            stageCount++;
            lucide.createIcons();
        }

        function removeStage(btn) {
            const stages = document.getElementById('stages-container').children;
            if (stages.length <= 1) {
                alert('At least one stage is required.');
                return;
            }
            if (confirm('Are you sure you want to remove this stage?')) {
                btn.closest('.stage-block').remove();
                calculateTotal();
            }
        }

        function addIngredientRow(btn) {
            const tbody = btn.closest('table').querySelector('tbody');
            const stageBlock = btn.closest('.stage-block');
            const stageIndex = stageBlock.dataset.stageIndex;
            addIngredientRowToTbody(tbody, stageIndex);
        }

        function addIngredientRowToTbody(tbody, stageIndex) {
            const template = document.getElementById('ingredientRowTemplate');
            const clone = template.content.cloneNode(true);
            const tr = clone.querySelector('tr');

            // Generate unique row ID/Index for unique naming
            const rowIndex = Date.now() + Math.random().toString(36).substr(2, 5);

            tr.querySelectorAll('[name*="STAGE_INDEX"]').forEach(el => {
                el.name = el.name.replace('STAGE_INDEX', stageIndex).replace('ROW_INDEX', rowIndex);
            });

            // Populate Select (only ingredients, no sub-recipes)
            const ingSelect = tr.querySelector('.ingredient-select');
            ingSelect.innerHTML += ingredientOptionsHTML;

            tbody.appendChild(tr);
            initRow(tr);
            lucide.createIcons();
        }

        // --- Sub-Recipe Modal & Logic ---
        function openSubRecipeModal() {
            document.getElementById('addSubRecipeModal').classList.remove('hidden');
            if (subRecipeSelector) subRecipeSelector.focus();
        }

        function closeSubRecipeModal() {
            document.getElementById('addSubRecipeModal').classList.add('hidden');
            if (subRecipeSelector) subRecipeSelector.clear();
            document.getElementById('sub-recipe-qty').value = 1;
            document.getElementById('sub-recipe-unit-display').value = '';
        }

        function confirmAddSubRecipe() {
            const val = subRecipeSelector.getValue();
            if (!val) return alert('Please select a sub-recipe');
            
            const qty = parseFloat(document.getElementById('sub-recipe-qty').value);
            if (isNaN(qty) || qty <= 0) return alert('Please enter a valid quantity');
            
            const opt = subRecipeSelector.options[val];
            const originalOpt = document.querySelector(`#sub-recipe-selector option[value="${val}"]`);
            
            const subData = {
                id: val,
                name: originalOpt?.dataset?.name || opt?.text || 'Unknown',
                ingId: originalOpt?.dataset?.ingId || val,
                qty: qty,
                unit: originalOpt?.dataset?.unit || opt?.dataset?.unit || 'pcs',
                price: parseFloat(originalOpt?.dataset?.price || opt?.dataset?.price || 0)
            };
            
            window.addedSubRecipes.push(subData);
            renderSubRecipeCards();
            calculateTotal();
            closeSubRecipeModal();
        }

        function renderSubRecipeCards() {
            const container = document.getElementById('sub-recipes-container');
            const msg = document.getElementById('no-sub-recipes-msg');
            
            if (!container) return;

            // Clear existing (except msg)
            container.querySelectorAll('.sub-recipe-card').forEach(el => el.remove());
            
            if (window.addedSubRecipes.length === 0) {
                msg.classList.remove('hidden');
                return;
            }
            
            msg.classList.add('hidden');
            
            window.addedSubRecipes.forEach((sub, index) => {
                const cost = (sub.qty * sub.price).toFixed(2);
                const showCost = window.userIsAdmin === true || window.userIsAdmin === 'true';
                const card = document.createElement('div');
                card.className = 'sub-recipe-card bg-indigo-50/30 border border-indigo-100 rounded-xl p-4 flex justify-between items-center group hover:bg-indigo-50 transition-colors animate-fade-in-up';
                card.innerHTML = `
                    <div class="flex items-center gap-4">
                        <div class="p-2 bg-white rounded-lg shadow-sm text-indigo-500">
                            <i data-lucide="component" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">${sub.name}</h4>
                            <p class="text-xs text-gray-500 font-medium">${sub.qty} ${sub.unit}${showCost ? ' • ₹' + cost : ''}</p>
                        </div>
                    </div>
                    <button type="button" onclick="removeSubRecipe(${index})" 
                        class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all opacity-0 group-hover:opacity-100">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                    ${showCost ? '<span class="cost-val hidden">' + cost + '</span>' : ''}
                `;
                container.appendChild(card);
            });
            
            if (window.lucide) lucide.createIcons();
        }

        function removeSubRecipe(index) {
            window.addedSubRecipes.splice(index, 1);
            renderSubRecipeCards();
            calculateTotal();
        }
        
        // Override calculateTotal to include sub-recipes
        const originalCalculateTotal = calculateTotal;
        calculateTotal = function() {
            let total = 0;
            // Raw ingredients
            document.querySelectorAll('.cost-display').forEach(el => {
                let val = parseFloat(el.textContent.replace(/,/g, ''));
                if (!isNaN(val)) total += val;
            });
            // Sub-recipes
            document.querySelectorAll('.cost-val').forEach(el => {
                let val = parseFloat(el.textContent.replace(/,/g, ''));
                if (!isNaN(val)) total += val;
            });
            
            const display = document.getElementById('totalCostDisplay');
            if(display) display.textContent = total.toFixed(2);
        };

        // Helper function to update unit when ingredient is selected
        function updateUnitForIngredient(value, row, ingSelect, optionDataMap, tomSelectInstance) {
            if (!value) {
                // Clear unit if no value
                const unitValueInput = row.querySelector('.unit-value-input');
                const unitDisplay = row.querySelector('.unit-display');
                if (unitValueInput) unitValueInput.value = '';
                if (unitDisplay) {
                    unitDisplay.value = '';
                    unitDisplay.setAttribute('placeholder', 'Select item first');
                }
                return;
            }
            
            console.log('updateUnitForIngredient called with value:', value);
            let ingredientUnit = null;
            
            // Method 1: Get from our data map
            if (optionDataMap && optionDataMap[value]) {
                ingredientUnit = optionDataMap[value].unit;
                console.log('✓ Unit from data map:', ingredientUnit);
            }
            
            // Method 2: Get from original select element (most reliable)
            if (!ingredientUnit) {
                const selectedOption = ingSelect.querySelector(`option[value="${value}"]`);
                console.log('Selected option:', selectedOption);
                if (selectedOption) {
                    // Try getAttribute first
                    ingredientUnit = selectedOption.getAttribute('data-unit');
                    console.log('Unit from getAttribute:', ingredientUnit);
                    
                    // Try dataset
                    if (!ingredientUnit && selectedOption.dataset) {
                        ingredientUnit = selectedOption.dataset.unit;
                        console.log('Unit from dataset:', ingredientUnit);
                    }
                    
                    // Fallback: Extract from text like "Milk (l)"
                    if (!ingredientUnit && selectedOption.textContent) {
                        const match = selectedOption.textContent.match(/\(([^)]+)\)/);
                        if (match && match[1]) {
                            ingredientUnit = match[1].trim();
                            console.log('✓ Unit from text parsing:', ingredientUnit);
                        }
                    }
                } else {
                    console.warn('Option element not found for value:', value);
                }
            }
            
            if (ingredientUnit) {
                const unitLabel = UNIT_LABELS[ingredientUnit] || ingredientUnit;
                console.log('Setting unit label:', unitLabel);
                
                // Update hidden input
                const unitValueInput = row.querySelector('.unit-value-input');
                if (unitValueInput) {
                    unitValueInput.value = ingredientUnit;
                    console.log('✓ Hidden input updated:', unitValueInput.value);
                } else {
                    console.error('❌ Unit value input not found');
                }
                
                // Update display field
                const unitDisplay = row.querySelector('.unit-display');
                if (unitDisplay) {
                    unitDisplay.value = unitLabel;
                    unitDisplay.removeAttribute('placeholder');
                    console.log('✓ Unit display updated to:', unitDisplay.value);
                } else {
                    console.error('❌ Unit display field not found in row');
                }
            } else {
                console.warn('⚠ Unit not found for ingredient:', value);
            }
        }

        function initRow(row) {
            const ingSelect = row.querySelector('.ingredient-select');

            // Init TomSelect for Ingredient (only raw ingredients, no sub-recipes)
            if (ingSelect && !ingSelect.tomselect) {
                // Extract data attributes from options before TomSelect initialization
                const options = ingSelect.querySelectorAll('option');
                const optionDataMap = {};
                console.log('Total options found:', options.length);
                options.forEach(opt => {
                    if (opt.value) {
                        // Read data-unit attribute (try multiple methods)
                        let unitAttr = opt.getAttribute('data-unit');
                        if (!unitAttr && opt.dataset) {
                            unitAttr = opt.dataset.unit;
                        }
                        // Fallback: Extract from text content like "Milk (l)"
                        if (!unitAttr && opt.textContent) {
                            const match = opt.textContent.match(/\(([^)]+)\)/);
                            if (match && match[1]) {
                                unitAttr = match[1].trim();
                            }
                        }
                        
                        let priceAttr = opt.getAttribute('data-price');
                        if (!priceAttr && opt.dataset) {
                            priceAttr = opt.dataset.price;
                        }
                        
                        if (unitAttr) {
                            optionDataMap[opt.value] = {
                                unit: unitAttr,
                                price: priceAttr
                            };
                            console.log('Mapped option:', opt.value, '-> unit:', unitAttr);
                        } else {
                            console.warn('No unit found for option:', opt.value, 'text:', opt.textContent);
                        }
                    }
                });
                console.log('Option data map:', optionDataMap);
                
                const tomSelectInstance = new TomSelect(ingSelect, {
                    create: true,
                    sortField: { field: "text", direction: "asc" },
                    placeholder: 'Type to search ingredient...',
                    // Remove dropdown_input plugin to avoid double dropdown
                    plugins: [],
                    // Allow opening dropdown even when item is selected
                    openOnFocus: true,
                    closeAfterSelect: false, // Don't auto-close, let our handlers manage it
                    maxItems: 1, // Single selection
                    allowEmptyOption: false, // Don't allow empty selection
                    render: {
                        option_create: (data, escape) => `<div class="create text-blue-600 p-2">Create <strong>${escape(data.input)}</strong>...</div>`
                    },
                    create: function (input) {
                        activeSelect = ingSelect;
                        openIngredientModal(input);
                        return false;
                    },
                    onInitialize: function() {
                        console.log('TomSelect initialized');
                        // Ensure all options are visible
                        const options = ingSelect.querySelectorAll('option');
                        console.log('Total options:', options.length);
                    },
                    onDropdownOpen: function() {
                        console.log('Dropdown opened');
                        const self = this;
                        // Ensure dropdown is visible and properly positioned
                        setTimeout(() => {
                            const dropdown = document.querySelector('.ts-dropdown');
                            if (dropdown) {
                                dropdown.style.zIndex = '9999';
                                dropdown.style.visibility = 'visible';
                                dropdown.style.opacity = '1';
                                dropdown.style.display = 'block';
                                dropdown.style.position = 'absolute';
                                // Ensure options are visible
                                const options = dropdown.querySelectorAll('.option');
                                options.forEach(opt => {
                                    opt.style.display = 'block';
                                    opt.style.visibility = 'visible';
                                    opt.style.opacity = '1';
                                });
                                console.log('Dropdown options visible:', options.length);
                            }
                        }, 50);
                    },
                    onItemAdd: function(value) {
                        // This fires when an item is added/selected
                        console.log('onItemAdd triggered:', value);
                        const self = this;
                        // Update unit first
                        updateUnitForIngredient(value, row, ingSelect, optionDataMap, tomSelectInstance);
                        
                        // Close dropdown after selection is complete
                        setTimeout(() => {
                            try {
                                if (self.isOpen) {
                                    self.close();
                                }
                                self.blur();
                            } catch(e) {
                                console.log('Error closing dropdown:', e);
                            }
                            
                            // Hide dropdown manually
                            const dropdown = document.querySelector('.ts-dropdown');
                            if (dropdown) {
                                dropdown.style.display = 'none';
                                dropdown.style.visibility = 'hidden';
                                dropdown.style.opacity = '0';
                                dropdown.classList.remove('active');
                                dropdown.classList.add('hidden');
                            }
                        }, 150);
                    },
                    onItemRemove: function(value) {
                        // Clear unit when item is removed
                        console.log('onItemRemove triggered:', value);
                        const unitValueInput = row.querySelector('.unit-value-input');
                        const unitDisplay = row.querySelector('.unit-display');
                        if (unitValueInput) unitValueInput.value = '';
                        if (unitDisplay) {
                            unitDisplay.value = '';
                            unitDisplay.setAttribute('placeholder', 'Select item first');
                        }
                    },
                    onChange: function(value) {
                        console.log('=== onChange triggered ===', value);
                        const self = this;
                        // Update unit and cost first
                        updateUnitForIngredient(value, row, ingSelect, optionDataMap, tomSelectInstance);
                        calculateRowCost(row);
                        
                        // Close dropdown when value changes
                        setTimeout(() => {
                            if (value) {
                                // Force close dropdown
                                try {
                                    if (self.isOpen) {
                                        self.close();
                                    }
                                    self.blur();
                                } catch(e) {
                                    console.log('Error closing dropdown:', e);
                                }
                                
                                // Force hide all dropdowns
                                const dropdowns = document.querySelectorAll('.ts-dropdown');
                                dropdowns.forEach(dropdown => {
                                    dropdown.style.display = 'none';
                                    dropdown.style.visibility = 'hidden';
                                    dropdown.style.opacity = '0';
                                    dropdown.classList.remove('active');
                                    dropdown.classList.add('hidden');
                                });
                            }
                        }, 100);
                    },
                    onFocus: function() {
                        console.log('TomSelect focused - opening dropdown');
                        const self = this;
                        // Open dropdown when focused (even if item is selected)
                        setTimeout(() => {
                            if (!self.isOpen) {
                                self.open();
                            }
                        }, 10);
                    },
                    onBlur: function() {
                        console.log('TomSelect blurred');
                        // Ensure dropdown is closed (with delay to allow option click)
                        setTimeout(() => {
                            const dropdown = document.querySelector('.ts-dropdown');
                            if (dropdown) {
                                dropdown.style.display = 'none';
                                dropdown.style.visibility = 'hidden';
                                dropdown.style.opacity = '0';
                            }
                        }, 200);
                    }
                });
                
                // Store tomSelectInstance reference for global access
                ingSelect.tomSelectInstance = tomSelectInstance;
                
                // Make TomSelect control clickable to open dropdown even when item is selected
                setTimeout(() => {
                    const tsWrapper = ingSelect.closest('.ts-wrapper');
                    if (!tsWrapper) return;
                    
                    const tsControl = tsWrapper.querySelector('.ts-control');
                    const tsInput = tsWrapper.querySelector('input.ts-input');
                    
                    // Function to open dropdown
                    const openDropdown = function(e) {
                        // Don't prevent if clicking remove button
                        if (e && (e.target.classList.contains('item-remove') || e.target.closest('.item-remove'))) {
                            return;
                        }
                        console.log('Opening TomSelect dropdown');
                        if (e) {
                            e.stopPropagation();
                            e.preventDefault();
                        }
                        
                        // Focus the input first
                        if (tsInput) {
                            tsInput.focus();
                            tsInput.click(); // Trigger click to ensure focus
                        }
                        
                        // Open dropdown
                        setTimeout(() => {
                            if (!tomSelectInstance.isOpen) {
                                tomSelectInstance.open();
                            }
                            tomSelectInstance.focus();
                        }, 10);
                    };
                    
                    // Add click handler to wrapper (catches all clicks)
                    if (tsWrapper) {
                        tsWrapper.style.cursor = 'pointer';
                        tsWrapper.addEventListener('click', function(e) {
                            // Skip if clicking remove button
                            if (e.target.classList.contains('item-remove') || e.target.closest('.item-remove')) {
                                return;
                            }
                            openDropdown(e);
                        });
                    }
                    
                    // Add click handler to control
                    if (tsControl) {
                        tsControl.style.cursor = 'pointer';
                        tsControl.addEventListener('click', openDropdown, true);
                    }
                    
                    // Add click handler to input
                    if (tsInput) {
                        tsInput.style.cursor = 'pointer';
                        tsInput.addEventListener('click', openDropdown, true);
                    }
                    
                    // Also handle mousedown for better compatibility
                    if (tsControl) {
                        tsControl.addEventListener('mousedown', function(e) {
                            if (e.target.classList.contains('item-remove') || e.target.closest('.item-remove')) {
                                return;
                            }
                            e.preventDefault(); // Prevent blur
                            setTimeout(() => openDropdown(e), 10);
                        });
                    }
                }, 300);
            }

            const qty = row.querySelector('.quantity-input');
            if (qty) {
                // Initialize base quantity if not set
                if (!qty.dataset.baseQty || qty.dataset.baseQty === '') {
                    const currentValue = qty.value || '';
                    if (currentValue) {
                        qty.dataset.baseQty = currentValue;
                    }
                }
                setQuantityHighlight(qty, 'original');
                qty.addEventListener('input', () => {
                    if (document.activeElement === qty) {
                        qty.dataset.manuallyEdited = 'true';
                        qty.dataset.baseQty = qty.value;
                        setQuantityHighlight(qty, 'manual');
                    }
                    calculateRowCost(row);
                });
            }

            calculateRowCost(row);
        }

        function removeRow(btn) {
            btn.closest('tr').remove();
            calculateTotal();
        }

        function calculateRowCost(row) {
            const select = row.querySelector('.ingredient-select');
            const qtyInput = row.querySelector('.quantity-input');
            const unitValueInput = row.querySelector('.unit-value-input');
            const costDisplay = row.querySelector('.cost-display');

            if (!costDisplay || !select) return; 

            const opt = select.options[select.selectedIndex];

            if (!opt || !opt.dataset.price) {
                costDisplay.textContent = '0.00';
                calculateTotal();
                return;
            }

            const price = parseFloat(opt.dataset.price); 
            const invUnit = opt.dataset.unit;
            const qty = parseFloat(qtyInput.value) || 0;
            const useUnit = unitValueInput ? unitValueInput.value : '';

            const priceFactor = UNIT_FACTORS[invUnit] || 1;
            const useFactor = UNIT_FACTORS[useUnit] || 1;

            let finalCost = 0;

            if(invUnit === useUnit) {
                finalCost = price * qty;
            } else {
                 const basePrice = price / priceFactor;
                 const baseQty = qty * useFactor;
                 finalCost = basePrice * baseQty;
            }

            costDisplay.textContent = finalCost.toFixed(2);

            // Add Formula Tooltip/Text
            const existingInfo = row.querySelector('.cost-formula');
            if(existingInfo) existingInfo.remove();

            if (finalCost > 0) {
                 const info = document.createElement('div');
                 info.className = 'cost-formula text-xs text-gray-400 mt-1';
                 info.style.fontSize = '0.7rem';
                 info.textContent = `${qty} ${useUnit} @ ${price}/${invUnit}`;
                 costDisplay.appendChild(info);
            }
            calculateTotal();
        }

        // --- Scaling Highlight (per spec: original=white/grey, scaled=yellow/orange, manual=blue) ---
        function setQuantityHighlight(input, state) {
            input.classList.remove('bg-yellow-100', 'border-orange-400', 'bg-blue-100', 'border-blue-400', 'bg-white', 'border-gray-300');
            if (state === 'scaled') {
                input.classList.add('bg-yellow-100', 'border-orange-400');
            } else if (state === 'manual') {
                input.classList.add('bg-blue-100', 'border-blue-400');
            } else {
                input.classList.add('bg-white', 'border-gray-300');
            }
        }

        // --- Scaling Logic ---
        function updateScaling() {
            const yieldInput = document.getElementById('yield_portions');
            if (!yieldInput) return;

            const currentPortions = parseFloat(yieldInput.value) || 0;
            if (currentPortions <= 0) return;

            const ratio = currentPortions / (window.basePortions || 10);

            document.querySelectorAll('.quantity-input').forEach(input => {
                if (input.dataset.manuallyEdited === 'true') return;

                const baseQty = parseFloat(input.dataset.baseQty);
                if (!isNaN(baseQty) && baseQty > 0) {
                    const newQty = baseQty * ratio;
                    input.value = newQty.toFixed(3);
                    setQuantityHighlight(input, ratio !== 1 ? 'scaled' : 'original');
                }
            });

            document.querySelectorAll('.ingredient-row').forEach(row => calculateRowCost(row));
        }

        function resetScaling() {
            const yieldInput = document.getElementById('yield_portions');
            if (yieldInput) yieldInput.value = window.basePortions;

            document.querySelectorAll('.quantity-input').forEach(input => {
                const baseQty = input.dataset.baseQty;
                if (baseQty !== undefined && baseQty !== '') input.value = baseQty;
                setQuantityHighlight(input, 'original');
                input.removeAttribute('data-manually-edited');
            });

            document.querySelectorAll('.ingredient-row').forEach(row => calculateRowCost(row));
        }

        function openIngredientModal(name = '') {
            document.getElementById('quick_name').value = name;
            document.getElementById('createIngredientModal').classList.remove('hidden');
        }

        function closeIngredientModal() {
            document.getElementById('createIngredientModal').classList.add('hidden');
            document.getElementById('quickIngredientForm').reset();
            activeSelect = null;
        }

        function submitQuickIngredient() {
            const form = document.getElementById('quickIngredientForm');
            const data = Object.fromEntries(new FormData(form).entries());

            if (!data.name || !data.category_id || !data.storage_location || !data.measurement_unit) {
                alert('Please fill all fields');
                return;
            }

            fetch('{{ route('ingredients.storeQuick') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        const newOpt = { value: result.ingredient.id, text: result.ingredient.name + ' (' + result.ingredient.unit + ')', price: result.ingredient.price, unit: result.ingredient.unit };

                        document.querySelectorAll('.ingredient-select').forEach(select => {
                            if (select.tomselect) {
                                select.tomselect.addOption(newOpt);
                            }
                        });

                        // Add to HTML buffer
                        const option = document.createElement('option');
                        option.value = result.ingredient.id;
                        option.dataset.price = result.ingredient.price;
                        option.dataset.unit = result.ingredient.unit;
                        option.text = result.ingredient.name + ' (' + result.ingredient.unit + ')';
                        document.getElementById('ingredientOptions').appendChild(option);

                        if (activeSelect && activeSelect.tomselect) {
                            activeSelect.tomselect.setValue(result.ingredient.id);
                        }
                        closeIngredientModal();
                    } else {
                        alert('Error: ' + (result.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to create ingredient');
                });
        }

        function updateIngredientAvailability() {
            const allSelects = document.querySelectorAll('.ingredient-select');
            const selectedValues = Array.from(allSelects).map(s => s.value).filter(v => v);

            allSelects.forEach(select => {
                if (!select.tomselect) return;
                const ts = select.tomselect;
                const myValue = select.value;

                Object.keys(ts.options).forEach(optVal => {
                    if (optVal === myValue) return;

                    const shouldDisable = selectedValues.includes(optVal);
                    // TomSelect doesn't have a simple disable option api for options easily without refresh
                    // but we can try updating
                    // Simple approach: we rely on 'disabled' class in CSS we added
                    // check if option exists in validation
                });

                // For simplicity in this iteration, we skip complex disable logic 
                // to avoid TomSelect recursion bugs, but we can re-enable later.
            });
        }
    </script>
@endpush