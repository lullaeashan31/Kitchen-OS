@extends('layouts.app')

@section('header')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('recipes.index') }}"
                class="p-2.5 bg-white border border-gray-200 rounded-xl text-gray-500 hover:text-blue-600 hover:border-blue-200 hover:shadow-sm transition-all">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Create Recipe</h1>
                <p class="text-gray-500 mt-1">Design a new culinary creation.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="window.history.back()"
                class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all">
                Cancel
            </button>
            <button type="button" onclick="document.getElementById('recipeForm').submit()"
                class="px-6 py-2.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:shadow-blue-600/40 transition-all flex items-center gap-2">
                <i data-lucide="save" class="w-5 h-5"></i>
                Save Recipe
            </button>
        </div>
    </div>
@endsection

@section('content')
    <form action="{{ route('recipes.store') }}" method="POST" id="recipeForm" class="flex flex-col lg:flex-row gap-8 pb-20">
        @csrf

        <!-- LEFT SIDEBAR: Basic Info & Settings -->
        <div class="w-full lg:w-1/3 space-y-6">

            <!-- Basic Details Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-6">
                <div class="p-6 border-b border-gray-50">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                            <i data-lucide="info" class="w-5 h-5"></i>
                        </div>
                        Recipe Details
                    </h2>
                </div>

                <div class="p-6 space-y-6">
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
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border {{ $errors->has('category_id') ? 'border-red-500' : 'border-gray-200' }} focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none appearance-none font-medium text-gray-700 cursor-pointer">
                                <option value="" disabled selected>Select a category...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    @push('scripts')
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
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
                    @endpush

                    <!-- Yields Section -->
                    <div class="bg-gray-50/80 rounded-xl p-5 border border-gray-100 border-dashed">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Yield
                            Configuration</label>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5 font-medium">Portions <span
                                        class="text-red-500">*</span></label>
                                <input type="number" name="yield_portions" id="yield_portions" min="1" step="1"
                                    value="{{ old('yield_portions') }}"
                                    class="w-full px-3 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none transition-all text-center font-bold text-gray-800"
                                    placeholder="10" oninput="updateScaling()">
                                @error('yield_portions') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <label class="block text-xs text-gray-500 font-medium">Batches</label>
                                    <button type="button" onclick="resetScaling()"
                                        class="text-[10px] text-blue-600 hover:text-blue-800 font-bold uppercase tracking-wider">Reset</button>
                                </div>
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

                        <div id="subRecipeFields" class="hidden space-y-4 pt-4 border-t border-indigo-100 mt-2">
                            <div>
                                <label class="block text-xs font-bold text-indigo-800 uppercase mb-1.5">Produces
                                    Ingredient</label>
                                <select name="produces_ingredient_id" id="produces_ingredient_id"
                                    class="w-full px-3 py-2.5 rounded-lg border border-indigo-200 focus:border-indigo-500 outline-none bg-white text-sm">
                                    <option value="">Select Item...</option>
                                    @foreach($ingredients as $ing)
                                        <option value="{{ $ing->id }}" {{ old('produces_ingredient_id') == $ing->id ? 'selected' : '' }}>
                                            {{ $ing->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
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
        <div class="w-full lg:w-2/3 space-y-8">

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
                                <div class="rounded-xl border border-gray-100">
                                    <table class="w-full text-sm text-left">
                                        <thead class="bg-gray-50 text-gray-500 font-semibold uppercase text-xs">
                                            <tr>
                                                <th class="px-4 py-3 w-[50%]">Item</th>
                                                <th class="px-4 py-3 w-[20%]">Qty</th>
                                                <th class="px-4 py-3 w-[20%]">Unit</th>
                                                @if(auth()->user()->isAdmin())
                                                    <th class="px-4 py-3 w-[10%] text-right">Cost</th>
                                                @endif
                                                <th class="px-4 py-3 w-[5%]"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 stage-ingredients-body bg-white">
                                            @if(isset($stage['ingredients']) && is_array($stage['ingredients']))
                                                @foreach($stage['ingredients'] as $rIndex => $ingredient)
                                                    <tr class="group hover:bg-blue-50/20 transition-colors ingredient-row">
                                                        <td class="px-4 py-2">
                                                            <div
                                                                class="w-full {{ $errors->has('stages.' . $index . '.ingredients.' . $rIndex . '.ingredient_id') ? 'border border-red-500 rounded-lg' : '' }}">
                                                                <select class="ingredient-select w-full"
                                                                    name="stages[{{ $index }}][ingredients][{{ $rIndex }}][ingredient_id]"
                                                                    required>
                                                                    <option value="">Search Ingredient...</option>
                                                                    @foreach($ingredients as $ing)
                                                                        <option value="{{ $ing->id }}"
                                                                            data-price="{{ $ing->latest_price ?? $ing->price }}"
                                                                            data-unit="{{ $ing->measurement_unit }}" {{ ($ingredient['ingredient_id'] ?? '') == $ing->id ? 'selected' : '' }}>
                                                                            {{ $ing->name }} ({{ $ing->measurement_unit }})
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            @error('stages.' . $index . '.ingredients.' . $rIndex . '.ingredient_id')
                                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                            @enderror
                                                        </td>
                                                        <td class="px-4 py-2">
                                                            <input type="number" step="any"
                                                                name="stages[{{ $index }}][ingredients][{{ $rIndex }}][quantity]"
                                                                value="{{ $ingredient['quantity'] ?? '' }}" required
                                                                data-base-qty="{{ $ingredient['quantity'] ?? '' }}"
                                                                class="quantity-input w-full h-[44px] px-3 rounded-xl border-2 {{ $errors->has('stages.' . $index . '.ingredients.' . $rIndex . '.quantity') ? 'border-red-500' : 'border-gray-300' }} focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none text-center font-bold text-base text-gray-900 transition-all bg-white">
                                                            @error('stages.' . $index . '.ingredients.' . $rIndex . '.quantity')
                                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                            @enderror
                                                        </td>
                                                        <td class="px-4 py-2">
                                                            <select name="stages[{{ $index }}][ingredients][{{ $rIndex }}][unit]" required
                                                                class="unit-select w-full h-[44px] px-3 rounded-xl border-2 {{ $errors->has('stages.' . $index . '.ingredients.' . $rIndex . '.unit') ? 'border-red-500' : 'border-gray-300' }} focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none bg-white font-bold text-base text-gray-900 appearance-none transition-all">
                                                                @foreach(\App\Enums\Unit::cases() as $unit)
                                                                    <option value="{{ $unit->value }}" {{ ($ingredient['unit'] ?? '') == $unit->value ? 'selected' : '' }}>{{ $unit->label() }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('stages.' . $index . '.ingredients.' . $rIndex . '.unit')
                                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                            @enderror
                                                        </td>
                                                        <!-- Hidden field to preserve data, not shown in UI -->
                                                        <input type="hidden"
                                                            name="stages[{{ $index }}][ingredients][{{ $rIndex }}][ingredient_group]"
                                                            value="{{ $ingredient['ingredient_group'] ?? '' }}">
                                                        @if(auth()->user()->isAdmin())
                                                            <td class="px-4 py-2 text-right font-medium text-gray-700 cost-display">0.00</td>
                                                        @endif
                                                        <td class="px-4 py-2 text-center">
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
                        <input type="text" name="stages[STAGE_INDEX][name]" value="Set 1"
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
                <div class="rounded-xl border border-gray-100">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-500 font-semibold uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3 w-[50%]">Item</th>
                                <th class="px-4 py-3 w-[20%]">Qty</th>
                                <th class="px-4 py-3 w-[20%]">Unit</th>
                                @if(auth()->user()->isAdmin())
                                    <th class="px-4 py-3 w-[10%] text-right">Cost</th>
                                @endif
                                <th class="px-4 py-3 w-[5%]"></th>
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
                <select class="ingredient-select w-full" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][ingredient_id]"
                    required>
                    <option value="">Search Ingredient...</option>
                </select>
            </td>
            <td class="px-4 py-2">
                <input type="number" step="any" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][quantity]" required
                    data-base-qty=""
                    class="quantity-input w-full h-[44px] px-3 rounded-xl border-2 border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none text-center font-bold text-base text-gray-900 transition-all bg-white">
            </td>
            <td class="px-4 py-2">
                <select name="stages[STAGE_INDEX][ingredients][ROW_INDEX][unit]" required
                    class="unit-select w-full h-[44px] px-3 rounded-xl border-2 border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none bg-white font-bold text-base text-gray-900 appearance-none transition-all">
                    @foreach(\App\Enums\Unit::cases() as $unit)
                        <option value="{{ $unit->value }}">{{ $unit->label() }}</option>
                    @endforeach
                </select>
            </td>
            <!-- Hidden field to preserve data, not shown in UI -->
            <input type="hidden" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][ingredient_group]" value="">
            @if(auth()->user()->isAdmin())
                <td class="px-4 py-2 text-right font-medium text-gray-700 cost-display">0.00</td>
            @endif
            <td class="px-4 py-2 text-center">
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
        }

        .ts-control .item {
            font-weight: 500;
            color: #1f2937;
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

        /* Unit Select Styling - High Visibility & Mobile Friendly */
        .unit-select {
            min-height: 44px !important;
            font-size: 16px !important;
            font-weight: 700 !important;
            color: #111827 !important;
            background-color: #ffffff !important;
            cursor: pointer !important;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23111827' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1.25rem;
            padding-right: 2.5rem !important;
        }

        .unit-select:focus {
            outline: none;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
        }

        .unit-select option {
            font-size: 16px !important;
            font-weight: 700 !important;
            color: #111827 !important;
            padding: 0.75rem !important;
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

        let subRecipeSelector = null;
        window.addedSubRecipes = [];

        document.addEventListener('DOMContentLoaded', () => {
            if (stageCount === 0) {
                addStage(); // Initial stage
            } else {
                // Initialize existing ingredients
                document.querySelectorAll('.ingredient-row').forEach(row => {
                    initRow(row);
                });
                lucide.createIcons();
            }
            calculateTotal();

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
                        plugins: ['dropdown_input'],
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

            // Sub-recipe toggle logic restoration
            if (document.getElementById('produces_ingredient_id').value) {
                toggleSubRecipe(true);
            }
        });

        function toggleSubRecipe(forceState = null) {
            const fields = document.getElementById('subRecipeFields');
            const bg = document.getElementById('subRecipeToggleBg');
            const dot = document.getElementById('subRecipeToggleDot');

            const isHidden = fields.classList.contains('hidden');
            const newState = forceState !== null ? forceState : isHidden;

            if (newState) {
                fields.classList.remove('hidden');
                bg.classList.remove('bg-gray-200');
                bg.classList.add('bg-indigo-500');
                dot.classList.add('translate-x-4');
                dot.classList.remove('translate-x-0');
            } else {
                fields.classList.add('hidden');
                bg.classList.remove('bg-indigo-500');
                bg.classList.add('bg-gray-200');
                dot.classList.remove('translate-x-4');
                dot.classList.add('translate-x-0');

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

            // Fix names
            stageBlock.querySelectorAll('[name*="STAGE_INDEX"]').forEach(el => {
                el.name = el.name.replace('STAGE_INDEX', stageCount);
            });

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
            ingSelect.innerHTML += ingredientOptionsHTML;

            tbody.appendChild(tr);
            initRow(tr);
            lucide.createIcons();
        }

        function initRow(row) {
            const ingSelect = row.querySelector('.ingredient-select');

            // Init TomSelect for Ingredient (only raw ingredients, no sub-recipes)
            if (ingSelect && !ingSelect.tomselect) {
                new TomSelect(ingSelect, {
                    create: true,
                    sortField: { field: "text", direction: "asc" },
                    placeholder: 'Type to search ingredient...',
                    plugins: ['dropdown_input'],
                    render: {
                        option_create: (data, escape) => `<div class="create text-blue-600 p-2">Create <strong>${escape(data.input)}</strong>...</div>`
                    },
                    create: function (input) {
                        activeSelect = ingSelect;
                        openIngredientModal(input);
                        return false;
                    },
                    onChange: () => calculateRowCost(row)
                });
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
                
                qty.addEventListener('input', () => {
                    // Manual Edit Highlight: When user manually edits the field
                    if (document.activeElement === qty) {
                        // Mark as manually edited
                        qty.dataset.manuallyEdited = 'true';
                        // Update base quantity to current value (so future scaling uses this as base)
                        qty.dataset.baseQty = qty.value;
                        // Apply manual edit visual state (Light Blue)
                        qty.classList.remove('bg-amber-100');
                        qty.classList.add('bg-blue-100');
                    }
                    calculateRowCost(row);
                });
            }
            const unit = row.querySelector('.unit-select');
            if (unit) unit.addEventListener('change', () => calculateRowCost(row));

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
            const unitSelect = row.querySelector('.unit-select');
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
            const useUnit = unitSelect.value;

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

        // --- Scaling Logic ---
        function updateScaling() {
            const yieldInput = document.getElementById('yield_portions');
            if (!yieldInput) return;

            const currentPortions = parseFloat(yieldInput.value) || 0;
            if (currentPortions <= 0) return;

            const ratio = currentPortions / (window.basePortions || 10);

            document.querySelectorAll('.quantity-input').forEach(input => {
                // Skip manually edited fields - they should remain blue
                if (input.dataset.manuallyEdited === 'true') {
                    return;
                }

                const baseQty = parseFloat(input.dataset.baseQty);
                if (!isNaN(baseQty) && baseQty > 0) {
                    const newQty = baseQty * ratio;
                    input.value = newQty.toFixed(3);

                    // Visual Highlight: Scaled (Amber background)
                    input.classList.remove('bg-blue-100');
                    if (ratio !== 1) {
                        input.classList.add('bg-amber-100');
                    } else {
                        // If ratio is 1, remove amber (back to original white)
                        input.classList.remove('bg-amber-100');
                    }
                }
            });

            // Recalculate all row costs
            document.querySelectorAll('.ingredient-row').forEach(row => calculateRowCost(row));
        }

        function resetScaling() {
            const yieldInput = document.getElementById('yield_portions');
            if (yieldInput) yieldInput.value = window.basePortions;

            document.querySelectorAll('.quantity-input').forEach(input => {
                const baseQty = input.dataset.baseQty;
                if (baseQty !== undefined && baseQty !== '') {
                    input.value = baseQty;
                }
                // Reset visual states to original (white background)
                input.classList.remove('bg-amber-100', 'bg-blue-100');
                // Clear manually edited flag
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
                            plugins: ['dropdown_input'],
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