@extends('layouts.app')

@section('header')
    <div>
        <div class="flex items-center gap-3 md:gap-4 mb-2">
            <a href="{{ route('recipes.index') }}"
                class="p-2 md:p-2.5 bg-white border border-gray-200 rounded-xl text-gray-500 hover:text-blue-600 hover:border-blue-200 hover:shadow-sm transition-all">
                <i data-lucide="arrow-left" class="w-4 h-4 md:w-5 md:h-5"></i>
            </a>
            <div>
                <h1 class="text-xl md:text-2xl lg:text-3xl font-bold text-gray-900 tracking-tight">Create Recipe</h1>
                <p class="text-xs md:text-sm text-gray-500 mt-1">Design a new culinary creation.</p>
            </div>
        </div>
    </div>
@endsection

@section('actions')
    <div class="flex items-center gap-2 md:gap-3">
        <button type="button" onclick="window.history.back()"
            class="px-4 md:px-6 py-2 md:py-2.5 bg-white border border-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all text-sm md:text-base">
            <span class="hidden sm:inline">Cancel</span>
            <span class="sm:hidden">✕</span>
        </button>
        <button type="button" onclick="document.getElementById('recipeForm').submit()"
            class="px-4 md:px-6 py-2 md:py-2.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:shadow-blue-600/40 transition-all flex items-center gap-2 text-sm md:text-base">
            <i data-lucide="save" class="w-4 h-4 md:w-5 md:h-5"></i>
            <span class="hidden sm:inline">Save Recipe</span>
            <span class="sm:hidden">Save</span>
        </button>
    </div>
@endsection

@section('content')
    <form action="{{ route('recipes.store') }}" method="POST" id="recipeForm" class="flex flex-col xl:flex-row gap-6 md:gap-8 pb-20">
        @csrf

        <!-- LEFT SIDEBAR: Basic Info & Settings -->
        <div class="w-full xl:w-1/3 space-y-4 md:space-y-6">

            <!-- Basic Details Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-6">
                <div class="p-4 md:p-6 border-b border-gray-50">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                            <i data-lucide="info" class="w-5 h-5"></i>
                        </div>
                        Recipe Details
                    </h2>
                </div>

                <div class="p-4 md:p-6 space-y-4 md:space-y-6">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Recipe Name <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none font-medium placeholder-gray-400"
                            placeholder="e.g. Truffle Mushroom Risotto">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Category <span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="category_id" required id="category-select"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border {{ $errors->has('category_id') ? 'border-red-500' : 'border-gray-200' }} focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none appearance-none font-medium text-gray-700 cursor-pointer pr-10">
                                <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>Select a category...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <!-- Dropdown arrow icon -->
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                        @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Smart Scale Section -->
                    <div class="bg-white rounded-xl p-5 border border-gray-100 mb-4">
                        <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2 mb-3">
                            <i data-lucide="calculator" class="w-4 h-4"></i>
                            Smart Scale
                        </h3>
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Scaling Mode</label>
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
                                <button type="button" onclick="setMultiplier(3)" 
                                    class="scaling-btn flex-1 px-3 py-2 rounded-lg border-2 border-gray-200 bg-white text-gray-700 font-semibold text-sm hover:bg-gray-50 transition-all"
                                    data-multiplier="3">3x</button>
                            </div>
                        </div>
                    </div>

                    <!-- Yields Section -->
                    <div class="bg-gray-50/80 rounded-xl p-5 border border-gray-100 border-dashed">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Yield
                            Configuration</label>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <label class="block text-xs text-gray-500 font-medium">Portions <span
                                            class="text-red-500">*</span></label>
                                    <button type="button" onclick="resetScaling()"
                                        class="text-[10px] text-blue-600 hover:text-blue-800 font-bold uppercase tracking-wider">Reset</button>
                                </div>
                                <input type="number" name="yield_portions" id="yield_portions" min="1" step="1"
                                    value="{{ old('yield_portions', 10) }}"
                                    class="w-full px-3 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none transition-all text-center font-bold text-gray-800"
                                    placeholder="10" oninput="updateScaling()">
                                @error('yield_portions') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 font-medium mb-1.5">Batches</label>
                                <input type="number" name="yield_batches" min="1" step="1"
                                    value="{{ old('yield_batches') }}"
                                    class="w-full px-3 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none transition-all text-center font-bold text-gray-800"
                                    placeholder="1">
                                @error('yield_batches') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Weight & Volume Hidden as per request -->
                    </div>

                    <!-- Prep Time (Bonus) -->
                    <!-- Add more fields if needed -->

                    <!-- Sub-Recipe Toggle -->
                    <div class="bg-indigo-50/50 rounded-xl p-5 border border-indigo-100">
                        <div class="flex items-center justify-between mb-3 cursor-pointer" onclick="toggleSubRecipe()">
                            <div>
                                <h3 class="text-sm font-bold text-indigo-900">Sub-Recipe Mode</h3>
                                <p class="text-xs text-indigo-600/80">Does this recipe produce an ingredient?</p>
                            </div>
                            <div class="relative inline-block w-10 h-6 transition-colors duration-200 ease-in-out border-2 border-transparent rounded-full cursor-pointer bg-gray-200"
                                id="subRecipeToggleBg">
                                <span
                                    class="translate-x-0 inline-block w-5 h-5 transition duration-200 ease-in-out transform bg-white rounded-full shadow pointer-events-none"
                                    id="subRecipeToggleDot"></span>
                            </div>
                        </div>

                        <input type="hidden" name="is_sub_recipe" id="is_sub_recipe" value="{{ old('is_sub_recipe', 0) }}">

                        <div id="subRecipeFields" class="{{ old('is_sub_recipe') ? '' : 'hidden' }} space-y-4 pt-4 border-t border-indigo-100 mt-2">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-indigo-800 uppercase mb-1.5">Output
                                        Qty</label>
                                    <input type="number" name="output_quantity" step="0.001" min="0"
                                        value="{{ old('output_quantity', 1) }}"
                                        class="w-full px-3 py-2.5 rounded-lg border border-indigo-200 focus:border-indigo-500 outline-none bg-white placeholder-indigo-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-indigo-800 uppercase mb-1.5">Output
                                        Unit</label>
                                    <select name="output_unit"
                                        class="w-full px-3 py-2.5 rounded-lg border border-indigo-200 focus:border-indigo-500 outline-none bg-white text-sm">
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->value }}" {{ old('output_unit') == $unit->value ? 'selected' : '' }}>
                                                {{ $unit->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT CONTENT: Sets & Methods -->
        <div class="w-full xl:w-2/3 space-y-6 md:space-y-8">

            <!-- Global Method (Optional, usually per stage but some like a summary) -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                    <div class="p-2 bg-orange-50 rounded-lg text-orange-500">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                    Recipe Overview / Description
                </h2>
                <textarea name="method" rows="3"
                    class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none resize-y placeholder-gray-400"
                    placeholder="Brief description, chef's notes, or general overview of the dish..."></textarea>
            </div>

            <!-- Sets Container -->
            <div id="stages-container" class="space-y-6">
                @if(old('stages'))
                    @foreach(old('stages') as $index => $stage)
                        <div class="stage-block bg-white rounded-2xl shadow-sm border border-gray-100 animate-fade-in-up"
                            data-stage-index="{{ $index }}">
                            <div class="bg-gray-50/50 border-b border-gray-100 p-4 flex justify-between items-center">
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="cursor-move text-gray-300 hover:text-gray-500">
                                        <i data-lucide="grip-vertical" class="w-5 h-5"></i>
                                    </div>
                                    <div class="w-full">
                                        <input type="text" name="stages[{{ $index }}][name]" value="{{ $stage['name'] ?? '' }}"
                                            class="bg-transparent border-none text-lg font-bold text-gray-800 focus:ring-0 placeholder-gray-400 w-full {{ $errors->has('stages.' . $index . '.name') ? 'border-red-500' : '' }}"
                                            placeholder="Set Name (e.g. Sauce Prep)">
                                        @error('stages.' . $index . '.name')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <button type="button" onclick="removeStage(this)"
                                    class="text-gray-400 hover:text-red-500 p-2 rounded-lg hover:bg-red-50 transition-colors">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </button>
                            </div>

                            <div class="p-6 space-y-6">
                                <!-- Ingredients Table -->
                                <div class="rounded-xl border border-gray-100 overflow-x-auto -mx-2 md:mx-0">
                                    <table class="w-full text-sm text-left min-w-[600px]">
                                        <thead class="bg-gray-50 text-gray-500 font-semibold uppercase text-xs">
                                            <tr>
                                                <th class="px-2 md:px-4 py-2 md:py-3 w-[50%]">Item</th>
                                                <th class="px-2 md:px-4 py-2 md:py-3 w-[20%]">Qty</th>
                                                <th class="px-2 md:px-4 py-2 md:py-3 w-[20%]">Unit</th>
                                                @if(auth()->user()->isAdmin())
                                                    <th class="px-2 md:px-4 py-2 md:py-3 w-[10%] text-right">Cost</th>
                                                @endif
                                                <th class="px-2 md:px-4 py-2 md:py-3 w-[5%]"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 stage-ingredients-body bg-white">
                                            @if(isset($stage['ingredients']) && is_array($stage['ingredients']))
                                                @foreach($stage['ingredients'] as $rIndex => $ingredient)
                                                    <tr class="group hover:bg-blue-50/20 transition-colors ingredient-row">
                                                        <td class="px-4 py-2">
                                                            <div
                                                                class="w-full {{ $errors->has('stages.' . $index . '.ingredients.' . $rIndex . '.ingredient_id') ? 'border border-red-500 rounded-lg' : '' }}">
                                                                <div class="relative">
                                                                    <select class="ingredient-select w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none bg-white cursor-pointer pr-8"
                                                                        name="stages[{{ $index }}][ingredients][{{ $rIndex }}][ingredient_id]"
                                                                        required>
                                                                        <option value="">Select Ingredient...</option>
                                                                        @foreach($ingredients as $ing)
                                                                            <option value="{{ $ing->id }}"
                                                                                data-price="{{ $ing->latest_price ?? $ing->price }}"
                                                                                data-unit="{{ $ing->measurement_unit }}" {{ ($ingredient['ingredient_id'] ?? '') == $ing->id ? 'selected' : '' }}>
                                                                                {{ $ing->name }} ({{ $ing->measurement_unit }})
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <!-- Dropdown arrow -->
                                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @error('stages.' . $index . '.ingredients.' . $rIndex . '.ingredient_id')
                                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                            @enderror
                                                        </td>
                                                        <td class="px-2 md:px-4 py-2">
                                                            <input type="number" step="any"
                                                                name="stages[{{ $index }}][ingredients][{{ $rIndex }}][quantity]"
                                                                value="{{ $ingredient['quantity'] ?? '' }}" required
                                                                data-base-qty="{{ $ingredient['quantity'] ?? '' }}"
                                                                class="quantity-input w-full h-[40px] md:h-[44px] px-2 md:px-3 rounded-xl border-2 {{ $errors->has('stages.' . $index . '.ingredients.' . $rIndex . '.quantity') ? 'border-red-500' : 'border-gray-300' }} focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none text-center font-bold text-sm md:text-base text-gray-900 transition-all bg-white">
                                                            @error('stages.' . $index . '.ingredients.' . $rIndex . '.quantity')
                                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                            @enderror
                                                        </td>
                                                        <td class="px-2 md:px-4 py-2">
                                                            <!-- Hidden input for form submission -->
                                                            <input type="hidden" name="stages[{{ $index }}][ingredients][{{ $rIndex }}][unit]" 
                                                                value="{{ $ingredient['unit'] ?? '' }}" 
                                                                class="unit-value-input">
                                                            <!-- Display field (readonly) -->
                                                            @php
                                                                $unitDisplayValue = '';
                                                                if (isset($ingredient['unit']) && $ingredient['unit']) {
                                                                    foreach (\App\Enums\Unit::cases() as $unit) {
                                                                        if ($unit->value == $ingredient['unit']) {
                                                                            $unitDisplayValue = $unit->label();
                                                                            break;
                                                                        }
                                                                    }
                                                                }
                                                            @endphp
                                                            <input type="text" 
                                                                readonly
                                                                value="{{ $unitDisplayValue }}"
                                                                class="unit-display w-full h-[40px] md:h-[44px] px-2 md:px-3 rounded-xl border-2 {{ $errors->has('stages.' . $index . '.ingredients.' . $rIndex . '.unit') ? 'border-red-500' : 'border-gray-300' }} bg-gray-50 font-bold text-sm md:text-base text-gray-700 cursor-not-allowed"
                                                                placeholder="Select item first">
                                                            @error('stages.' . $index . '.ingredients.' . $rIndex . '.unit')
                                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                            @enderror
                                                        </td>
                                                        <!-- Hidden field to preserve data, not shown in UI -->
                                                        <input type="hidden"
                                                            name="stages[{{ $index }}][ingredients][{{ $rIndex }}][ingredient_group]"
                                                            value="{{ $ingredient['ingredient_group'] ?? '' }}">
                                                        @if(auth()->user()->isAdmin())
                                                            <td class="px-2 md:px-4 py-2 text-right font-medium text-gray-700 cost-display text-sm">0.00</td>
                                                        @endif
                                                        <td class="px-2 md:px-4 py-2 text-center">
                                                            <button type="button" onclick="removeRow(this)"
                                                                class="text-gray-300 hover:text-red-500 transition-colors p-1 rounded-md">
                                                                <i data-lucide="x" class="w-4 h-4"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                    <button type="button" onclick="addIngredientRow(this)"
                                        class="w-full py-3 bg-gray-50/50 hover:bg-gray-100 text-blue-600 text-sm font-semibold border-t border-gray-100 transition-colors flex items-center justify-center gap-2">
                                        <i data-lucide="plus" class="w-4 h-4"></i> Add Ingredient
                                    </button>
                                </div>

                                <!-- Method Textarea -->
                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Instructions</label>
                                    <textarea name="stages[{{ $index }}][method]" rows="3"
                                        class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none resize-y text-gray-700"
                                        placeholder="Detailed steps for this set...">{{ $stage['method'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Add Stage Button -->
            <button type="button" onclick="addStage()"
                class="w-full py-4 border-2 border-dashed border-gray-300 rounded-2xl text-gray-500 font-bold hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50/50 transition-all flex items-center justify-center gap-2 group">
                <div class="p-1 bg-gray-200 rounded-full text-white group-hover:bg-blue-500 transition-colors">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                </div>
                Add Another Set
            </button>

            <!-- Sub-Recipes Used Section [NEW] -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 overflow-hidden">
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
                    <div id="no-sub-recipes-msg"
                        class="col-span-full py-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200 text-gray-400">
                        No sub-recipes added yet.
                    </div>
                </div>
            </div>

            @if(auth()->user()->isAdmin())
                <div class="flex justify-end items-center gap-4 bg-gray-900 text-white p-6 rounded-2xl shadow-xl mt-8">
                    <div class="text-right">
                        <p class="text-gray-400 text-sm font-medium uppercase tracking-wider">Total Estimated Cost</p>
                        <div class="text-3xl font-bold tracking-tight flex items-baseline justify-end gap-1">
                            <span class="text-lg text-gray-500 font-normal">₹</span>
                            <span id="totalCostDisplay">0.00</span>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </form>

    <!-- Templates & Modals -->

    <!-- Stage Template -->
    <template id="stageTemplate">
        <div class="stage-block bg-white rounded-2xl shadow-sm border border-gray-100 animate-fade-in-up">
            <div class="bg-gray-50/50 border-b border-gray-100 p-4 flex justify-between items-center">
                <div class="flex items-center gap-3 flex-1">
                    <div class="cursor-move text-gray-300 hover:text-gray-500">
                        <i data-lucide="grip-vertical" class="w-5 h-5"></i>
                    </div>
                    <div class="w-full">
                        <input type="text" name="stages[STAGE_INDEX][name]" value="SET_NUMBER_PLACEHOLDER"
                            class="bg-transparent border-none text-lg font-bold text-gray-800 focus:ring-0 placeholder-gray-400 w-full"
                            placeholder="Set Name (e.g. Sauce Prep)">
                        <!-- Note: JS generated validation errors for new stages are hard to target server-side before submit, 
                                      but on re-render (old), they will rely on the main loop. 
                                      For JS-added stages that haven't been submitted, no server errors exist yet. -->
                    </div>
                </div>
                <button type="button" onclick="removeStage(this)"
                    class="text-gray-400 hover:text-red-500 p-2 rounded-lg hover:bg-red-50 transition-colors">
                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-6 space-y-6">
                                <!-- Ingredients Table -->
                                <div class="rounded-xl border border-gray-100 overflow-x-auto -mx-2 md:mx-0">
                                    <table class="w-full text-sm text-left min-w-[600px]">
                        <thead class="bg-gray-50 text-gray-500 font-semibold uppercase text-xs">
                            <tr>
                                <th class="px-2 md:px-4 py-2 md:py-3 w-[50%]">Item</th>
                                <th class="px-2 md:px-4 py-2 md:py-3 w-[20%]">Qty</th>
                                <th class="px-2 md:px-4 py-2 md:py-3 w-[20%]">Unit</th>
                                @if(auth()->user()->isAdmin())
                                    <th class="px-2 md:px-4 py-2 md:py-3 w-[10%] text-right">Cost</th>
                                @endif
                                <th class="px-2 md:px-4 py-2 md:py-3 w-[5%]"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 stage-ingredients-body bg-white">
                            <!-- Rows go here -->
                        </tbody>
                    </table>
                    <button type="button" onclick="addIngredientRow(this)"
                        class="w-full py-3 bg-gray-50/50 hover:bg-gray-100 text-blue-600 text-sm font-semibold border-t border-gray-100 transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add Ingredient
                    </button>
                </div>

                <!-- Method Textarea -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Instructions</label>
                    <textarea name="stages[STAGE_INDEX][method]" rows="3"
                        class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none resize-y text-gray-700"
                        placeholder="Detailed steps for this stage..."></textarea>
                </div>
            </div>
        </div>
    </template>

    <!-- Ingredient Row Template -->
    <template id="ingredientRowTemplate">
        <tr class="group hover:bg-blue-50/20 transition-colors ingredient-row">
            <td class="px-4 py-2">
                <!-- Ingredient Select -->
                <div class="relative">
                    <select class="ingredient-select w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none bg-white cursor-pointer pr-8"
                        name="stages[STAGE_INDEX][ingredients][ROW_INDEX][ingredient_id]"
                        required>
                        <option value="">Select Ingredient...</option>
                    </select>
                    <!-- Dropdown arrow -->
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </td>
            <td class="px-2 md:px-4 py-2">
                <input type="number" step="any" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][quantity]" required
                    data-base-qty=""
                    class="quantity-input w-full h-[40px] md:h-[44px] px-2 md:px-3 rounded-xl border-2 border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none text-center font-bold text-sm md:text-base text-gray-900 transition-all bg-white">
            </td>
            <td class="px-2 md:px-4 py-2">
                <!-- Hidden input for form submission -->
                <input type="hidden" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][unit]" 
                    value="" 
                    class="unit-value-input">
                <!-- Display field (readonly) -->
                <input type="text" 
                    readonly
                    value=""
                    class="unit-display w-full h-[40px] md:h-[44px] px-2 md:px-3 rounded-xl border-2 border-gray-300 bg-gray-50 font-bold text-sm md:text-base text-gray-700 cursor-not-allowed"
                    placeholder="Select item first">
            </td>
            <!-- Hidden field to preserve data, not shown in UI -->
            <input type="hidden" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][ingredient_group]" value="">
            @if(auth()->user()->isAdmin())
                <td class="px-2 md:px-4 py-2 text-right font-medium text-gray-700 cost-display text-sm">0.00</td>
            @endif
            <td class="px-2 md:px-4 py-2 text-center">
                <button type="button" onclick="removeRow(this)"
                    class="text-gray-300 hover:text-red-500 transition-colors p-1 rounded-md">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </td>
        </tr>
    </template>

    <!-- Hidden Options -->
    <div id="ingredientOptions" style="display: none;">
        @foreach($ingredients as $ing)
            <option value="{{ $ing->id }}" data-price="{{ $ing->latest_price ?? $ing->price }}"
                data-unit="{{ $ing->measurement_unit }}">
                {{ $ing->name }} ({{ $ing->measurement_unit }})
            </option>
        @endforeach
    </div>

    <!-- Sub-Recipe Modal -->
    <div id="addSubRecipeModal" class="fixed inset-0 bg-gray-900/70 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 animate-fade-in">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all scale-100 hover:scale-[1.01]">
            <!-- Enhanced Header with Gradient -->
            <div class="relative p-8 bg-gradient-to-br from-indigo-600 via-indigo-500 to-purple-600 overflow-hidden">
                <div class="absolute inset-0 bg-grid-white/10 [mask-image:linear-gradient(0deg,transparent,black)]"></div>
                <div class="relative flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-bold text-white flex items-center gap-3 mb-2">
                            <div class="p-2.5 bg-white/20 backdrop-blur-sm rounded-xl">
                                <i data-lucide="component" class="w-7 h-7 text-white"></i>
                            </div>
                            Add Sub-Recipe
                        </h3>
                        <p class="text-indigo-100 text-sm">Include a pre-made recipe component</p>
                    </div>
                    <button type="button" onclick="closeSubRecipeModal()" 
                        class="p-2 text-white/80 hover:text-white hover:bg-white/20 rounded-xl transition-all">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-8 space-y-8">
                <!-- Sub-Recipe Selector -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <div class="p-1.5 bg-indigo-100 rounded-lg">
                            <i data-lucide="search" class="w-4 h-4 text-indigo-600"></i>
                        </div>
                        Select Sub-Recipe
                    </label>
                    <div class="relative">
                        <select id="sub-recipe-selector" class="w-full h-[56px] px-5 rounded-xl border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition-all text-base font-semibold shadow-sm hover:border-indigo-300">
                            <option value="">🔍 Search Sub-Recipes...</option>
                            @if(count($subRecipes) > 0)
                                @foreach($subRecipes as $sub)
                                    <option value="{{ $sub->id }}" 
                                        data-name="{{ $sub->name }}"
                                        data-ing-id="{{ $sub->produces_ingredient_id }}"
                                        data-unit="{{ $sub->producesIngredient->measurement_unit ?? 'pcs' }}"
                                        data-price="{{ $sub->producesIngredient->latest_price ?? $sub->producesIngredient->price ?? 0 }}">
                                        {{ $sub->name }} @if($sub->producesIngredient)({{ $sub->producesIngredient->measurement_unit ?? 'pcs' }})@endif
                                    </option>
                                @endforeach
                            @else
                                <option value="" disabled>No sub-recipes available</option>
                            @endif
                        </select>
                    </div>
                    
                    <!-- Enhanced Empty State -->
                    @if(count($subRecipes) === 0)
                        <div class="mt-4 p-6 bg-gradient-to-br from-amber-50 to-orange-50 border-2 border-amber-200 rounded-2xl shadow-sm">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-amber-100 rounded-xl flex-shrink-0">
                                    <i data-lucide="alert-circle" class="w-6 h-6 text-amber-600"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-amber-900 text-base mb-2">No Sub-Recipes Available</p>
                                    <p class="text-sm text-amber-800 mb-4 leading-relaxed">
                                        Sub-recipes are recipes that produce ingredients. To create one:
                                    </p>
                                    <ol class="text-sm text-amber-800 space-y-2 mb-4 ml-4 list-decimal">
                                        <li>Enable <strong>"Sub-Recipe Mode"</strong> when creating a recipe</li>
                                        <li>Select which ingredient it produces</li>
                                        <li>Save the recipe</li>
                                    </ol>
                                    <a href="{{ route('recipes.create') }}" target="_blank"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl shadow-lg shadow-amber-600/30 transition-all">
                                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                        Create Sub-Recipe Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Quantity and Unit Fields -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <div class="p-1.5 bg-blue-100 rounded-lg">
                                <i data-lucide="hash" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            Quantity
                        </label>
                        <input type="number" id="sub-recipe-qty" step="any" min="0.001" value="1"
                            class="w-full h-[56px] px-5 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 outline-none transition-all text-center text-xl font-bold shadow-sm hover:border-blue-300">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <div class="p-1.5 bg-purple-100 rounded-lg">
                                <i data-lucide="ruler" class="w-4 h-4 text-purple-600"></i>
                            </div>
                            Unit
                        </label>
                        <input type="text" id="sub-recipe-unit-display" readonly placeholder="Auto"
                            class="w-full h-[56px] px-5 rounded-xl border-2 border-indigo-200 bg-gradient-to-br from-indigo-50 to-purple-50 text-indigo-700 text-center text-xl font-bold cursor-not-allowed shadow-sm">
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Footer -->
            <div class="p-6 bg-gradient-to-br from-gray-50 to-gray-100 border-t border-gray-200 flex gap-4">
                <button type="button" onclick="closeSubRecipeModal()"
                    class="flex-1 px-6 py-4 bg-white border-2 border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 hover:border-gray-400 hover:shadow-md transition-all flex items-center justify-center gap-2">
                    <i data-lucide="x" class="w-5 h-5"></i>
                    Cancel
                </button>
                <button type="button" onclick="confirmAddSubRecipe()"
                    class="flex-1 px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl shadow-xl shadow-indigo-500/40 hover:shadow-2xl hover:shadow-indigo-500/50 hover:from-indigo-700 hover:to-purple-700 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none"
                    id="addSubRecipeBtn" {{ count($subRecipes) === 0 ? 'disabled' : '' }}>
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    Add to Recipe
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Create Ingredient Modal (Same as before but styled) -->
    <div id="createIngredientModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all scale-100">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">New Ingredient</h3>
                <button type="button" onclick="closeIngredientModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6">
                <form id="quickIngredientForm" class="space-y-4">
                    <!-- Fields consistent with previous design but styled -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Name</label>
                        <input type="text" name="name" id="quick_name" required
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none">
                    </div>
                    <!-- Reuse categories/units passed to view -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category</label>
                            <select name="category_id" required
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none bg-white">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Unit</label>
                            <select name="measurement_unit" required
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none bg-white">
                                @foreach($units as $unit)
                                    <option value="{{ $unit->value }}">{{ $unit->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Storage</label>
                        <select name="storage_location" required
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none bg-white">
                            <option value="Fridge">Fridge</option>
                            <option value="Freezer">Freezer</option>
                            <option value="Dry Store">Dry Store</option>
                            <option value="Bar">Bar</option>
                        </select>
                    </div>

                    <button type="button" onclick="submitQuickIngredient()"
                        class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl shadow-lg mt-4 hover:bg-blue-700">Create
                        Ingredient</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <style>
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
            user-select: none !important;
            -webkit-user-select: none !important;
        }

        .ts-control .item:hover {
            background-color: #f3f4f6 !important;
        }
        
        /* Make sure selected items are clickable */
        .ts-control .item[data-value] {
            cursor: pointer !important;
            pointer-events: auto !important;
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

        /* TomSelect Dropdown Visibility Fix - Apply to ALL dropdowns */
        .ts-dropdown {
            z-index: 99999 !important;
            position: absolute !important;
            max-height: 300px !important;
            overflow-y: auto !important;
            background: white !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .ts-dropdown.hidden {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
        }

        .ts-dropdown-content {
            max-height: 300px !important;
            overflow-y: auto !important;
            display: block !important;
            visibility: visible !important;
        }

        .ts-dropdown .option {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            padding: 0.5rem 0.75rem !important;
            cursor: pointer !important;
            color: #1f2937 !important;
            background-color: white !important;
        }

        .ts-dropdown .option:hover,
        .ts-dropdown .option.active,
        .ts-dropdown .option.selected {
            background-color: #3b82f6 !important;
            color: white !important;
        }

        .ts-dropdown .option[data-selectable="false"] {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
        }
        
        /* Ensure all TomSelect wrappers are clickable */
        .ts-wrapper {
            cursor: pointer !important;
            position: relative !important;
        }
        
        /* Fix for category and sub-recipe selectors */
        #category-select + .ts-wrapper,
        #sub-recipe-selector + .ts-wrapper {
            cursor: pointer !important;
        }

        /* Ensure parent containers don't hide dropdown */
        .stage-block {
            position: relative !important;
            overflow: visible !important;
        }

        .stage-block .rounded-xl {
            overflow: visible !important;
        }

        .ingredient-select {
            position: relative !important;
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.3s ease-out forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Unit Display Styling - Readonly field */
        .unit-display {
            min-height: 44px !important;
            font-size: 16px !important;
            font-weight: 700 !important;
            color: #374151 !important;
            background-color: #f9fafb !important;
            cursor: not-allowed !important;
        }

        /* Ensure equal visual weight with quantity input */
        .quantity-input {
            min-height: 44px !important;
            font-size: 16px !important;
            font-weight: 700 !important;
        }
    </style>
    <script>
        // --- Core Application Logic ---
        let stageCount = {{ count(old('stages', [])) }};
        let activeSelect = null;
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
        window.addedSubRecipes = [];

        document.addEventListener('DOMContentLoaded', () => {
            if (stageCount === 0) {
                addStage(); // Initial stage
            } else {
                // Initialize existing stages - ONLY set default names if completely empty
                // IMPORTANT: Never modify existing stage names - preserve "Set 1", "Set 2", etc. as-is
                const existingStages = document.querySelectorAll('.stage-block');
                existingStages.forEach((stage, index) => {
                    const nameInput = stage.querySelector('input[name*="[name]"]');
                    // Only set default name if field is completely empty or just whitespace
                    // If name already exists (even "Set 1", "Set 2"), DO NOT CHANGE IT
                    if (nameInput && (!nameInput.value || nameInput.value.trim() === '')) {
                        nameInput.value = `Set ${index + 1}`;
                    }
                });
                // Initialize existing ingredients and auto-populate units
                document.querySelectorAll('.ingredient-row').forEach(row => {
                    initRow(row);
                    
                    // Auto-populate unit for already selected ingredients (native select)
                    const ingSelect = row.querySelector('.ingredient-select');
                    if (ingSelect && ingSelect.value) {
                        const selectedOption = ingSelect.querySelector(`option[value="${ingSelect.value}"]`);
                        if (selectedOption) {
                            const unitValue = selectedOption.getAttribute('data-unit') || selectedOption.dataset.unit;
                            if (unitValue) {
                                const unitValueInput = row.querySelector('.unit-value-input');
                                const unitDisplay = row.querySelector('.unit-display');
                                const unitLabel = UNIT_LABELS[unitValue] || unitValue;
                                
                                if (unitValueInput && (!unitValueInput.value || unitValueInput.value === '')) {
                                    unitValueInput.value = unitValue;
                                }
                                if (unitDisplay && (!unitDisplay.value || unitDisplay.value === '')) {
                                    unitDisplay.value = unitLabel;
                                }
                            }
                        }
                    }
                });
                lucide.createIcons();
            }
            calculateTotal();

            // Initialize Scaling Mode buttons
            const yieldInput = document.getElementById('yield_portions');
            if (yieldInput) {
                window.basePortions = parseFloat(yieldInput.value) || 10;
                if (!yieldInput.value) {
                    yieldInput.value = 10;
                    window.basePortions = 10;
                }
                // Highlight the correct button based on current ratio
                const currentPortions = parseFloat(yieldInput.value) || 10;
                const ratio = currentPortions / window.basePortions;
                document.querySelectorAll('.scaling-btn').forEach(btn => {
                    const btnMultiplier = parseFloat(btn.getAttribute('data-multiplier'));
                    if (Math.abs(btnMultiplier - ratio) < 0.01) {
                        btn.classList.remove('border-gray-200', 'bg-white', 'text-gray-700');
                        btn.classList.add('border-blue-500', 'bg-blue-500', 'text-white', 'active');
                    } else {
                        btn.classList.remove('border-blue-500', 'bg-blue-500', 'text-white', 'active');
                        btn.classList.add('border-gray-200', 'bg-white', 'text-gray-700');
                    }
                });
            }

            // Initialize Sub-Recipe Selector
            const subSelEl = document.getElementById('sub-recipe-selector');
            const addBtn = document.getElementById('addSubRecipeBtn');
            
            if (subSelEl) {
                // Check if there are any options (excluding the empty placeholder and disabled options)
                const availableOptions = subSelEl.querySelectorAll('option:not([value=""]):not([disabled])');
                const hasOptions = availableOptions.length > 0;
                
                if (hasOptions) {
                    subRecipeSelector = new TomSelect(subSelEl, {
                        create: false,
                        sortField: { field: "text", direction: "asc" },
                        placeholder: 'Search for a sub-recipe...',
                        plugins: [],
                        openOnFocus: true,
                        onChange: function (val) {
                            if (!val) {
                                const unitDisplay = document.getElementById('sub-recipe-unit-display');
                                if (unitDisplay) unitDisplay.value = '';
                                if (addBtn) {
                                    addBtn.disabled = true;
                                    addBtn.classList.add('opacity-50', 'cursor-not-allowed');
                                }
                                return;
                            }
                            const originalOpt = document.querySelector(`#sub-recipe-selector option[value="${val}"]`);
                            if (originalOpt) {
                                const unitDisplay = document.getElementById('sub-recipe-unit-display');
                                if (unitDisplay) {
                                    unitDisplay.value = originalOpt.dataset?.unit || 'pcs';
                                }
                                if (addBtn) {
                                    addBtn.disabled = false;
                                    addBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                }
                            }
                            
                            // Close dropdown after selection
                            setTimeout(() => {
                                if (this.isOpen) {
                                    this.close();
                                }
                                this.blur();
                            }, 150);
                        },
                        onFocus: function() {
                            if (!this.isOpen) {
                                this.open();
                            }
                        },
                        onBlur: function() {
                            setTimeout(() => {
                                const dropdown = document.querySelector('#sub-recipe-selector + .ts-dropdown');
                                if (dropdown) {
                                    dropdown.style.display = 'none';
                                    dropdown.style.visibility = 'hidden';
                                    dropdown.style.opacity = '0';
                                }
                            }, 200);
                        },
                        onInitialize: function() {
                            // Enable/disable add button based on selection
                            const val = this.getValue();
                            if (addBtn) {
                                addBtn.disabled = !val;
                                if (!val) {
                                    addBtn.classList.add('opacity-50', 'cursor-not-allowed');
                                } else {
                                    addBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                }
                            }
                        }
                    });
                    
                    // Store reference globally for global click handler
                    window.subRecipeSelector = subRecipeSelector;
                    
                    // Click handler for sub-recipe selector - force open dropdown
                    setTimeout(() => {
                        const subWrapper = subSelEl.closest('.ts-wrapper');
                        const subControl = subWrapper ? subWrapper.querySelector('.ts-control') : null;
                        const subInput = subWrapper ? subWrapper.querySelector('input.ts-input') : null;
                        
                        const openSubRecipe = function(e) {
                            e.stopPropagation();
                            e.preventDefault();
                            
                            // Focus first, then open
                            if (subInput) {
                                subInput.focus();
                            }
                            
                            setTimeout(() => {
                                subRecipeSelector.focus();
                                subRecipeSelector.open();
                            }, 10);
                        };
                        
                        if (subWrapper) {
                            subWrapper.style.cursor = 'pointer';
                            subWrapper.addEventListener('click', openSubRecipe);
                        }
                        
                        if (subControl) {
                            subControl.style.cursor = 'pointer';
                            subControl.addEventListener('click', openSubRecipe);
                            
                            // Also handle mousedown to prevent blur
                            subControl.addEventListener('mousedown', function(e) {
                                e.preventDefault();
                                setTimeout(() => openSubRecipe(e), 10);
                            });
                        }
                    }, 300);
                } else {
                    // No sub-recipes available - disable the selector and show message
                    subSelEl.disabled = true;
                    subSelEl.classList.add('bg-gray-100', 'cursor-not-allowed', 'opacity-60');
                    if (addBtn) {
                        addBtn.disabled = true;
                        addBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                }
            }

            // Form Submit Override
            const form = document.getElementById('recipeForm');
            if (form) {
                form.addEventListener('submit', function (e) {
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

            // Set initial base portions if yield_portions has value
            const yieldInput = document.getElementById('yield_portions');
            if (yieldInput && yieldInput.value) {
                window.basePortions = parseFloat(yieldInput.value) || 10;
            } else {
                window.basePortions = 10; // Default fallback
            }

            // STEP 1: Base quantity + original state on form load
            document.querySelectorAll('.quantity-input').forEach(input => {
                if (!input.dataset.baseQty || input.dataset.baseQty === '') {
                    const v = input.value || '';
                    if (v) input.dataset.baseQty = v;
                }
                setQuantityHighlight(input, 'original');
            });

            // Sub-recipe toggle logic restoration
            if (document.getElementById('produces_ingredient_id').value) {
                toggleSubRecipe(true);
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

                // Clear values if hiding
                if (forceState === null) {
                    document.getElementById('produces_ingredient_id').value = '';
                }
            }
        }

        // --- Stage Management ---
        function addStage() {
            const container = document.getElementById('stages-container');
            const template = document.getElementById('stageTemplate');
            const clone = template.content.cloneNode(true);

            const stageBlock = clone.querySelector('.stage-block');
            stageBlock.dataset.stageIndex = stageCount;

            // Calculate next set number based on existing stages
            // IMPORTANT: We only modify the NEW stage's name, never touch existing stage names
            const existingStages = container.querySelectorAll('.stage-block');
            const nextSetNumber = existingStages.length + 1;

            // Fix names (only for the NEW stage being added)
            stageBlock.querySelectorAll('[name*="STAGE_INDEX"]').forEach(el => {
                el.name = el.name.replace('STAGE_INDEX', stageCount);
            });

            // Update set name with correct number (ONLY for the NEW stage)
            // Existing stages keep their names unchanged ("Set 1", "Set 2", etc. remain as-is)
            const setNameInput = stageBlock.querySelector('input[name*="[name]"]');
            if (setNameInput) {
                setNameInput.value = setNameInput.value.replace('SET_NUMBER_PLACEHOLDER', `Set ${nextSetNumber}`);
            }

            // Initial Ingredient Row
            const tbody = stageBlock.querySelector('.stage-ingredients-body');
            addIngredientRowToTbody(tbody, stageCount);

            container.appendChild(stageBlock);
            stageCount++;
            lucide.createIcons();

            // Scroll to new stage lightly
            if (stageCount > 1) {
                stageBlock.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        function removeStage(btn) {
            const container = document.getElementById('stages-container');
            if (container.children.length <= 1) {
                alert('You need at least one stage!');
                return;
            }
            if (confirm('Remove this stage?')) {
                btn.closest('.stage-block').remove();
                calculateTotal();
            }
        }

        // --- Ingredient Row Management ---
        function addIngredientRow(btn) {
            const tbody = btn.closest('.stage-block').querySelector('tbody');
            const stageIndex = btn.closest('.stage-block').dataset.stageIndex;
            addIngredientRowToTbody(tbody, stageIndex);
        }

        function addIngredientRowToTbody(tbody, stageIndex) {
            const template = document.getElementById('ingredientRowTemplate');
            const clone = template.content.cloneNode(true);
            const tr = clone.querySelector('tr');

            const rowIndex = Date.now() + Math.floor(Math.random() * 1000);

            tr.querySelectorAll('[name*="STAGE_INDEX"]').forEach(el => {
                el.name = el.name.replace('STAGE_INDEX', stageIndex).replace('ROW_INDEX', rowIndex);
            });

            // Populate select options (only ingredients, no sub-recipes)
            const ingSelect = tr.querySelector('.ingredient-select');
            
            // Clear existing options first (except placeholder)
            const placeholderOption = ingSelect.querySelector('option[value=""]');
            ingSelect.innerHTML = '';
            if (placeholderOption) {
                ingSelect.appendChild(placeholderOption);
            }
            
            // Add ingredient options using proper DOM methods
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = ingredientOptionsHTML;
            const optionsToAdd = tempDiv.querySelectorAll('option');
            optionsToAdd.forEach(opt => {
                if (opt.value) {
                    // Clone and ensure data attributes are preserved
                    const clonedOpt = opt.cloneNode(true);
                    
                    // Ensure data-unit attribute is preserved
                    const unitAttr = opt.getAttribute('data-unit');
                    if (unitAttr) {
                        clonedOpt.setAttribute('data-unit', unitAttr);
                    } else if (opt.dataset && opt.dataset.unit) {
                        clonedOpt.setAttribute('data-unit', opt.dataset.unit);
                    } else {
                        // Fallback: Extract from text like "Milk (l)"
                        const match = opt.textContent.match(/\(([^)]+)\)/);
                        if (match && match[1]) {
                            clonedOpt.setAttribute('data-unit', match[1].trim());
                        }
                    }
                    
                    // Ensure data-price attribute is preserved
                    const priceAttr = opt.getAttribute('data-price');
                    if (priceAttr) {
                        clonedOpt.setAttribute('data-price', priceAttr);
                    } else if (opt.dataset && opt.dataset.price) {
                        clonedOpt.setAttribute('data-price', opt.dataset.price);
                    }
                    
                    ingSelect.appendChild(clonedOpt);
                }
            });

            tbody.appendChild(tr);
            initRow(tr);
            lucide.createIcons();
        }

        // Helper function to update unit when ingredient is selected (for native select)
        function updateUnitForIngredient(value, row, ingSelect) {
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
            
            // Get unit from selected option
            const selectedOption = ingSelect.querySelector(`option[value="${value}"]`);
            if (selectedOption) {
                const unitValue = selectedOption.getAttribute('data-unit') || selectedOption.dataset.unit;
                if (unitValue) {
                    const unitLabel = UNIT_LABELS[unitValue] || unitValue;
                    
                    // Update hidden input
                    const unitValueInput = row.querySelector('.unit-value-input');
                    if (unitValueInput) {
                        unitValueInput.value = unitValue;
                    }
                    
                    // Update display field
                    const unitDisplay = row.querySelector('.unit-display');
                    if (unitDisplay) {
                        unitDisplay.value = unitLabel;
                        unitDisplay.removeAttribute('placeholder');
                    }
                }
            }
        }

        function initRow(row) {
            const ingSelect = row.querySelector('.ingredient-select');
            
            // Simple native select - no TomSelect
            if (ingSelect && !ingSelect.hasAttribute('data-initialized')) {
                ingSelect.setAttribute('data-initialized', 'true');
                
                // Populate options from ingredientOptions if empty
                if (ingSelect.querySelectorAll('option').length <= 1) {
                    const ingredientOptionsHTML = document.getElementById('ingredientOptions');
                    if (ingredientOptionsHTML) {
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = ingredientOptionsHTML.innerHTML;
                        const options = tempDiv.querySelectorAll('option');
                        options.forEach(opt => {
                            if (opt.value) {
                                const newOpt = opt.cloneNode(true);
                                
                                // Ensure data-unit attribute is preserved
                                const unitAttr = opt.getAttribute('data-unit');
                                if (unitAttr) {
                                    newOpt.setAttribute('data-unit', unitAttr);
                                } else if (opt.dataset && opt.dataset.unit) {
                                    newOpt.setAttribute('data-unit', opt.dataset.unit);
                                } else {
                                    // Fallback: Extract from text like "Milk (l)"
                                    const match = opt.textContent.match(/\(([^)]+)\)/);
                                    if (match && match[1]) {
                                        newOpt.setAttribute('data-unit', match[1].trim());
                                    }
                                }
                                
                                // Ensure data-price attribute is preserved
                                const priceAttr = opt.getAttribute('data-price');
                                if (priceAttr) {
                                    newOpt.setAttribute('data-price', priceAttr);
                                } else if (opt.dataset && opt.dataset.price) {
                                    newOpt.setAttribute('data-price', opt.dataset.price);
                                }
                                
                                ingSelect.appendChild(newOpt);
                            }
                        });
                    }
                }
                
                // Add change handler for unit and cost update
                // Use arrow function to preserve 'row' reference, or find row dynamically
                ingSelect.addEventListener('change', function() {
                    const selectedValue = this.value;
                    // Find the row dynamically to ensure we get the correct row
                    const currentRow = this.closest('tr.ingredient-row') || this.closest('tr');
                    
                    if (!currentRow) {
                        console.error('Row not found for ingredient select');
                        return;
                    }
                    
                    console.log('Item changed to:', selectedValue, 'in row:', currentRow);
                    
                    if (selectedValue) {
                        const selectedOption = this.querySelector(`option[value="${selectedValue}"]`);
                        console.log('Selected option:', selectedOption);
                        
                        if (selectedOption) {
                            // Try multiple methods to get unit
                            let unitValue = selectedOption.getAttribute('data-unit');
                            if (!unitValue && selectedOption.dataset) {
                                unitValue = selectedOption.dataset.unit;
                            }
                            
                            // Fallback: Extract from text like "Milk (l)"
                            if (!unitValue && selectedOption.textContent) {
                                const match = selectedOption.textContent.match(/\(([^)]+)\)/);
                                if (match && match[1]) {
                                    unitValue = match[1].trim();
                                    console.log('Unit extracted from text:', unitValue);
                                }
                            }
                            
                            console.log('Unit value found:', unitValue);
                            
                            if (unitValue) {
                                const unitValueInput = currentRow.querySelector('.unit-value-input');
                                const unitDisplay = currentRow.querySelector('.unit-display');
                                
                                console.log('Unit input found:', unitValueInput, 'in row:', currentRow);
                                console.log('Unit display found:', unitDisplay, 'in row:', currentRow);
                                
                                if (unitValueInput) {
                                    unitValueInput.value = unitValue;
                                    console.log('Unit input set to:', unitValue);
                                } else {
                                    console.error('Unit value input not found in row');
                                }
                                
                                if (unitDisplay) {
                                    const unitLabel = UNIT_LABELS[unitValue] || unitValue;
                                    unitDisplay.value = unitLabel;
                                    unitDisplay.removeAttribute('placeholder');
                                    console.log('Unit display set to:', unitLabel);
                                } else {
                                    console.error('Unit display not found in row');
                                }
                            } else {
                                console.warn('No unit found for selected option');
                            }
                            
                            // Calculate cost
                            calculateRowCost(currentRow);
                        } else {
                            console.error('Selected option not found for value:', selectedValue);
                        }
                    } else {
                        // Clear unit if no selection
                        const unitValueInput = currentRow.querySelector('.unit-value-input');
                        const unitDisplay = currentRow.querySelector('.unit-display');
                        if (unitValueInput) unitValueInput.value = '';
                        if (unitDisplay) {
                            unitDisplay.value = '';
                            unitDisplay.setAttribute('placeholder', 'Select item first');
                        }
                        calculateRowCost(currentRow);
                    }
                });
                
                // Add global click handler to close TomSelect dropdowns when clicking outside (only for sub-recipe selector now)
                if (!window.dropdownCloseHandlerAdded) {
                    window.dropdownCloseHandlerAdded = true;
                    document.addEventListener('click', function(e) {
                        // Check what was clicked
                        const isDropdown = e.target.closest('.ts-dropdown');
                        const isTomSelectControl = e.target.closest('.ts-control');
                        const isTomSelectWrapper = e.target.closest('.ts-wrapper');
                        const isOption = e.target.classList.contains('option') || e.target.closest('.option');
                        
                        // If clicking on option, let TomSelect handle it
                        if (isOption) {
                            return;
                        }
                        
                        // If clicking outside TomSelect area, close sub-recipe selector dropdown
                        if (!isDropdown && !isTomSelectControl && !isTomSelectWrapper) {
                            setTimeout(() => {
                                // Close sub-recipe selector
                                if (window.subRecipeSelector) {
                                    try {
                                        if (window.subRecipeSelector.isOpen) {
                                            window.subRecipeSelector.close();
                                        }
                                        window.subRecipeSelector.blur();
                                    } catch(err) {
                                        console.log('Error closing sub-recipe:', err);
                                    }
                                }
                                
                                // Hide all TomSelect dropdowns manually
                                const dropdowns = document.querySelectorAll('.ts-dropdown');
                                dropdowns.forEach(dropdown => {
                                    dropdown.style.display = 'none';
                                    dropdown.style.visibility = 'hidden';
                                    dropdown.style.opacity = '0';
                                    dropdown.classList.remove('active');
                                    dropdown.classList.add('hidden');
                                });
                            }, 150);
                        }
                    }, false); // Bubble phase
                }
            }

            // Listeners for cost recalc and highlights
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
            // Unit is now readonly, no need for change listeners

            // Calculate initial cost
            calculateRowCost(row);
        }

        function removeRow(btn) {
            btn.closest('tr').remove();
            calculateTotal();
        }

        // --- Cost Calculation Logic ---
        function calculateRowCost(row) {
            const select = row.querySelector('.ingredient-select');
            const qtyInput = row.querySelector('.quantity-input');
            const unitValueInput = row.querySelector('.unit-value-input');
            const costDisplay = row.querySelector('.cost-display');

            if (!costDisplay || !select) return;

            // Logic: Get Price per Base Unit -> Convert Qty to Base Unit -> Multiply
            const opt = select.options[select.selectedIndex];
            if (!opt || !opt.dataset.price) {
                costDisplay.textContent = '0.00';
                calculateTotal();
                return;
            }

            const price = parseFloat(opt.dataset.price); // Price per Inventory Unit
            const invUnit = opt.dataset.unit;
            const qty = parseFloat(qtyInput.value) || 0;
            const useUnit = unitValueInput ? unitValueInput.value : '';

            // Simple Factor Conversion
            const priceFactor = UNIT_FACTORS[invUnit] || 1;
            const useFactor = UNIT_FACTORS[useUnit] || 1;

            let finalCost = 0;

            // If units match or are compatible
            if (invUnit === useUnit) {
                finalCost = price * qty;
            } else {
                // Price per 1 base unit
                const basePrice = price / priceFactor;
                // Qty in base units
                const baseQty = qty * useFactor;
                finalCost = basePrice * baseQty;
            }

            costDisplay.textContent = finalCost.toFixed(2);
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.cost-display').forEach(el => total += parseFloat(el.textContent));
            const display = document.getElementById('totalCostDisplay');
            if (display) display.textContent = total.toFixed(2);
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

        // --- Scaling Mode Multiplier Function ---
        function setMultiplier(multiplier) {
            // Update active button styling
            document.querySelectorAll('.scaling-btn').forEach(btn => {
                const btnMultiplier = parseFloat(btn.getAttribute('data-multiplier'));
                if (btnMultiplier === multiplier) {
                    // Active state: blue background, white text, blue border
                    btn.classList.remove('border-gray-200', 'bg-white', 'text-gray-700');
                    btn.classList.add('border-blue-500', 'bg-blue-500', 'text-white', 'active');
                } else {
                    // Inactive state: white background, gray text, gray border
                    btn.classList.remove('border-blue-500', 'bg-blue-500', 'text-white', 'active');
                    btn.classList.add('border-gray-200', 'bg-white', 'text-gray-700');
                }
            });

            // Update yield_portions input based on base portions
            const basePortions = window.basePortions || 10;
            const newPortions = basePortions * multiplier;
            const yieldInput = document.getElementById('yield_portions');
            if (yieldInput) {
                yieldInput.value = Math.round(newPortions);
                updateScaling();
            }
        }

        // --- Scaling Logic ---
        function updateScaling() {
            const yieldInput = document.getElementById('yield_portions');
            if (!yieldInput) return;

            const currentPortions = parseFloat(yieldInput.value) || 0;
            if (currentPortions <= 0) return;

            const ratio = currentPortions / (window.basePortions || 10);
            
            // Update active button based on current ratio
            const multiplier = ratio;
            document.querySelectorAll('.scaling-btn').forEach(btn => {
                const btnMultiplier = parseFloat(btn.getAttribute('data-multiplier'));
                // Check if this button matches the current multiplier (with small tolerance for rounding)
                if (Math.abs(btnMultiplier - multiplier) < 0.01) {
                    btn.classList.remove('border-gray-200', 'bg-white', 'text-gray-700');
                    btn.classList.add('border-blue-500', 'bg-blue-500', 'text-white', 'active');
                } else {
                    btn.classList.remove('border-blue-500', 'bg-blue-500', 'text-white', 'active');
                    btn.classList.add('border-gray-200', 'bg-white', 'text-gray-700');
                }
            });

            document.querySelectorAll('.quantity-input').forEach(input => {
                if (input.dataset.manuallyEdited === 'true') return;

                const baseQty = parseFloat(input.dataset.baseQty);
                if (!isNaN(baseQty) && baseQty > 0) {
                    const newQty = baseQty * ratio;
                    input.value = newQty.toFixed(3);
                    // Apply highlight inside scaling (no event wait): remove original/manual, apply scaled
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

        // --- Sub-Recipe Modal & Logic ---
        function openSubRecipeModal() {
            const modal = document.getElementById('addSubRecipeModal');
            if (modal) {
                modal.classList.remove('hidden');
                // Re-initialize TomSelect if not already initialized
                const subSelEl = document.getElementById('sub-recipe-selector');
                if (subSelEl && !subRecipeSelector) {
                    const availableOptions = subSelEl.querySelectorAll('option:not([value=""]):not([disabled])');
                    if (availableOptions.length > 0) {
                        subRecipeSelector = new TomSelect(subSelEl, {
                            create: false,
                            sortField: { field: "text", direction: "asc" },
                            placeholder: 'Search for a sub-recipe...',
                            plugins: [],
                            onChange: function (val) {
                                if (!val) {
                                    const unitDisplay = document.getElementById('sub-recipe-unit-display');
                                    if (unitDisplay) unitDisplay.value = '';
                                    const addBtn = document.getElementById('addSubRecipeBtn');
                                    if (addBtn) {
                                        addBtn.disabled = true;
                                        addBtn.classList.add('opacity-50', 'cursor-not-allowed');
                                    }
                                    return;
                                }
                                const originalOpt = document.querySelector(`#sub-recipe-selector option[value="${val}"]`);
                                if (originalOpt) {
                                    const unitDisplay = document.getElementById('sub-recipe-unit-display');
                                    if (unitDisplay) {
                                        unitDisplay.value = originalOpt.dataset?.unit || 'pcs';
                                    }
                                    const addBtn = document.getElementById('addSubRecipeBtn');
                                    if (addBtn) {
                                        addBtn.disabled = false;
                                        addBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                    }
                                }
                            }
                        });
                    }
                }
                // Focus after a short delay to ensure modal is visible
                setTimeout(() => {
                    if (subRecipeSelector) {
                        subRecipeSelector.focus();
                    }
                }, 100);
            }
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
                name: originalOpt?.dataset?.name || opt.text || 'Unknown',
                ingId: originalOpt?.dataset?.ingId || val,
                qty: qty,
                unit: originalOpt?.dataset?.unit || opt.dataset?.unit || 'pcs',
                price: parseFloat(originalOpt?.dataset?.price || opt.dataset?.price || 0)
            };

            window.addedSubRecipes.push(subData);
            renderSubRecipeCards();
            calculateTotal();
            closeSubRecipeModal();
        }

        function renderSubRecipeCards() {
            const container = document.getElementById('sub-recipes-container');
            const msg = document.getElementById('no-sub-recipes-msg');

            // Clear existing (except msg)
            container.querySelectorAll('.sub-recipe-card').forEach(el => el.remove());

            if (window.addedSubRecipes.length === 0) {
                msg.classList.remove('hidden');
                return;
            }

            msg.classList.add('hidden');

            window.addedSubRecipes.forEach((sub, index) => {
                const cost = (sub.qty * sub.price).toFixed(2);
                const card = document.createElement('div');
                card.className = 'sub-recipe-card bg-indigo-50/30 border border-indigo-100 rounded-xl p-4 flex justify-between items-center group hover:bg-indigo-50 transition-colors animate-fade-in-up';
                card.innerHTML = `
                                    <div class="flex items-center gap-4">
                                        <div class="p-2 bg-white rounded-lg shadow-sm text-indigo-500">
                                            <i data-lucide="component" class="w-5 h-5"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-800">${sub.name}</h4>
                                            <p class="text-xs text-gray-500 font-medium">${sub.qty} ${sub.unit} • ₹${cost}</p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="removeSubRecipe(${index})" 
                                        class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all opacity-0 group-hover:opacity-100">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                    <span class="cost-val hidden">${cost}</span>
                                `;
                container.appendChild(card);
            });

            lucide.createIcons();
        }

        function removeSubRecipe(index) {
            window.addedSubRecipes.splice(index, 1);
            renderSubRecipeCards();
            calculateTotal();
        }

        // Override calculateTotal to include sub-recipes
        const originalCalculateTotal = calculateTotal;
        calculateTotal = function () {
            let total = 0;
            // Raw ingredients
            document.querySelectorAll('.cost-display').forEach(el => total += parseFloat(el.textContent));
            // Sub-recipes
            document.querySelectorAll('.cost-val').forEach(el => total += parseFloat(el.textContent));

            const display = document.getElementById('totalCostDisplay');
            if (display) display.textContent = total.toFixed(2);
        };

        // --- Quick Creature Modal ---
        function openIngredientModal(name) {
            document.getElementById('quick_name').value = name;
            document.getElementById('createIngredientModal').classList.remove('hidden');
        }
        function closeIngredientModal() {
            document.getElementById('createIngredientModal').classList.add('hidden');
        }
        function submitQuickIngredient() {
            const form = document.getElementById('quickIngredientForm');
            const data = Object.fromEntries(new FormData(form).entries());

            fetch('{{ route('ingredients.storeQuick') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const newOpt = { value: res.ingredient.id, text: res.ingredient.name, price: res.ingredient.price, unit: res.ingredient.unit };

                        // Add to all ingredient selects
                        document.querySelectorAll('.ingredient-select').forEach(s => {
                            if (s.tomselect) s.tomselect.addOption(newOpt);
                        });

                        // Select in active
                        if (activeSelect) activeSelect.tomselect.setValue(res.ingredient.id);

                        // Add to HTML buffer
                        const opt = document.createElement('option');
                        opt.value = res.ingredient.id;
                        opt.text = res.ingredient.name;
                        opt.dataset.price = res.ingredient.price;
                        opt.dataset.unit = res.ingredient.unit;
                        document.getElementById('ingredientOptions').appendChild(opt);

                        closeIngredientModal();
                    } else {
                        alert(res.message);
                    }
                });
        }


    </script>
@endpush