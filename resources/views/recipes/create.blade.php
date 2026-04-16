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
        <button type="submit" form="recipeForm"
            class="px-4 md:px-6 py-2 md:py-2.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:shadow-blue-600/40 transition-all flex items-center gap-2 text-sm md:text-base">
            <i data-lucide="save" class="w-4 h-4 md:w-5 md:h-5"></i>
            <span class="hidden sm:inline">Save Recipe</span>
            <span class="sm:hidden">Save</span>
        </button>
    </div>
@endsection

@section('content')
    <!-- CRITICAL: Define function BEFORE form loads -->
    <script>
        // Simple, standalone function that works immediately
        function addNewSet() {
            try {
                console.log('addNewSet function called');

                var container = document.getElementById('stages-container');
                var template = document.getElementById('stageTemplate');

                if (!container) {
                    alert('Error: stages-container not found');
                    return false;
                }

                if (!template) {
                    alert('Error: stageTemplate not found');
                    return false;
                }

                var clone = template.content.cloneNode(true);
                var stageBlock = clone.querySelector('.stage-block');

                if (!stageBlock) {
                    alert('Error: stage-block not found in template');
                    return false;
                }

                // Count existing stages
                var existingStages = container.querySelectorAll('.stage-block');
                var nextSetNumber = existingStages.length + 1;
                var stageIndex = existingStages.length;

                stageBlock.dataset.stageIndex = stageIndex;

                // Replace STAGE_INDEX in all inputs
                var allInputs = stageBlock.querySelectorAll('[name*="STAGE_INDEX"]');
                for (var i = 0; i < allInputs.length; i++) {
                    allInputs[i].name = allInputs[i].name.replace('STAGE_INDEX', stageIndex);
                }

                // Set the set name
                var setNameInput = stageBlock.querySelector('input[name*="[name]"]');
                if (setNameInput) {
                    setNameInput.value = 'Set ' + nextSetNumber;
                }

                // Add to container
                container.appendChild(stageBlock);

                // Add initial ingredient row immediately
                setTimeout(function () {
                    var tbody = stageBlock.querySelector('.stage-ingredients-body');
                    if (tbody) {
                        // Create a simple button to trigger addIngredientRowNow
                        var addBtn = stageBlock.querySelector('button[onclick*="addIngredientRow"]');
                        // Call addIngredientRow to add first row
                        addIngredientRow(addBtn);
                    }
                }, 300);

                // Initialize icons if available
                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    setTimeout(function () {
                        lucide.createIcons();
                    }, 100);
                }

                // Scroll to new stage
                setTimeout(function () {
                    stageBlock.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 200);

                console.log('Set added successfully');
                return false;
            } catch (error) {
                console.error('Error:', error);
                alert('Error: ' + error.message);
                return false;
            }
        }

        // Make it globally accessible
        window.addNewSet = addNewSet;
        window.addStageSimple = addNewSet;
        window.addStage = addNewSet;

        // Unit labels mapping
        var UNIT_LABELS_MAP = {
            'g': 'Gram (g)',
            'kg': 'Kilogram (kg)',
            'ml': 'Milliliter (ml)',
            'l': 'Liter (l)',
            'tbsp': 'Tablespoon (tbsp)',
            'tsp': 'Teaspoon (tsp)',
            'cup': 'Cup',
            'pcs': 'Piece (pcs)',
            'oz': 'Ounce (oz)',
            'lb': 'Pound (lb)'
        };

        // Simple function to add ingredient row - available immediately
        // Simple function to add ingredient row - available immediately
        // Initialize an ingredient row with event listeners and initial cost
        function initIngredientRow(row) {
            const select = row.querySelector('.ingredient-select');
            const qtyInput = row.querySelector('.quantity-input');
            
            if (select) {
                // Update unit and calculate cost when ingredient is changed
                select.addEventListener('change', function () {
                    const selectedValue = this.value;
                    const selectedOption = this.options[this.selectedIndex];

                    if (selectedOption && selectedValue) {
                        const unitValue = selectedOption.getAttribute('data-unit') || (selectedOption.dataset ? selectedOption.dataset.unit : '');
                        if (!unitValue && selectedOption.textContent) {
                            const match = selectedOption.textContent.match(/\(([^)]+)\)/);
                            if (match && match[1]) unitValue = match[1].trim();
                        }

                        if (unitValue) {
                            const unitLabel = UNIT_LABELS_MAP[unitValue] || unitValue;
                            const unitValueInput = row.querySelector('.unit-value-input');
                            if (unitValueInput) unitValueInput.value = unitValue;
                            const unitDisplay = row.querySelector('.unit-display');
                            if (unitDisplay) {
                                unitDisplay.value = unitLabel;
                                unitDisplay.removeAttribute('placeholder');
                            }
                        }
                    } else {
                        const unitValueInput = row.querySelector('.unit-value-input');
                        const unitDisplay = row.querySelector('.unit-display');
                        if (unitValueInput) unitValueInput.value = '';
                        if (unitDisplay) {
                            unitDisplay.value = '';
                            unitDisplay.setAttribute('placeholder', 'Select item first');
                        }
                        const costDisplay = row.querySelector('.cost-display');
                        if (costDisplay) costDisplay.textContent = '0.00';
                    }
                    calculateRowCost(row);
                });
            }

            if (qtyInput) {
                // Re-calculate cost when quantity changes
                qtyInput.addEventListener('input', function () {
                    calculateRowCost(row);
                });
            }

            // Perform initial calculation for existing rows
            calculateRowCost(row);
        }

        // Simple function to add ingredient row - available immediately
        function addIngredientRow(btn) {
            try {
                console.log('addIngredientRow called');
                var stageBlock = btn.closest('.stage-block');
                if (!stageBlock) {
                    alert('Error: Could not find stage block');
                    return false;
                }

                var tbody = stageBlock.querySelector('.stage-ingredients-body');
                if (!tbody) {
                    alert('Error: Could not find tbody');
                    return false;
                }

                var stageIndex = stageBlock.dataset.stageIndex || '0';
                var template = document.getElementById('ingredientRowTemplate');
                if (!template) {
                    alert('Error: Template not found');
                    return false;
                }

                var clone = template.content.cloneNode(true);
                var tr = clone.querySelector('tr');
                if (!tr) {
                    alert('Error: Row not found in template');
                    return false;
                }

                // Generate unique row index
                var rowIndex = Date.now() + Math.random();

                // Replace STAGE_INDEX and ROW_INDEX
                var allInputs = tr.querySelectorAll('[name*="STAGE_INDEX"], [name*="ROW_INDEX"]');
                for (var i = 0; i < allInputs.length; i++) {
                    allInputs[i].name = allInputs[i].name.replace('STAGE_INDEX', stageIndex).replace('ROW_INDEX', rowIndex);
                }

                // Remove empty state row if exists
                var emptyRows = tbody.querySelectorAll('tr');
                for (var k = 0; k < emptyRows.length; k++) {
                    var td = emptyRows[k].querySelector('td[colspan]');
                    if (td) {
                        emptyRows[k].remove();
                        break;
                    }
                }

                // Add the row
                tbody.appendChild(tr);

                // Populate ingredient options
                var select = tr.querySelector('.ingredient-select');
                if (select) {
                    var ingredientOptions = document.getElementById('ingredientOptions');
                    if (ingredientOptions) {
                        var options = ingredientOptions.querySelectorAll('option');
                        for (var j = 0; j < options.length; j++) {
                            var opt = options[j].cloneNode(true);

                            // Explicitly preserve data attributes
                            var priceAttr = options[j].getAttribute('data-price');
                            var unitAttr = options[j].getAttribute('data-unit');

                            if (priceAttr) {
                                opt.setAttribute('data-price', priceAttr);
                                if (opt.dataset) opt.dataset.price = priceAttr;
                            }
                            if (unitAttr) {
                                opt.setAttribute('data-unit', unitAttr);
                                if (opt.dataset) opt.dataset.unit = unitAttr;
                            }

                            select.appendChild(opt);
                        }
                    }

                    // Use common initialization for events and cost
                    initIngredientRow(tr);
                }

                // Initialize icons
                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    setTimeout(function () { lucide.createIcons(); }, 100);
                }

                return false;
            } catch (error) {
                console.error('Error:', error);
                return false;
            }
        }

        window.addIngredientRowNow = addIngredientRow;
        window.addIngredientRow = addIngredientRow;
    </script>

    <form action="{{ route('recipes.store') }}" method="POST" id="recipeForm"
        class="flex flex-col xl:flex-row gap-6 md:gap-8 pb-20">
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
                    <!-- Recipe Type Selection -->
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 mb-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-3">What kind of recipe is this?</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex flex-col items-center gap-2 p-3 rounded-xl border-2 transition-all cursor-pointer group bg-white hover:border-blue-200" id="mainTypeLabel">
                                <input type="radio" name="recipe_type_select" value="main" class="absolute top-2 right-2" onchange="updateRecipeType('main')" {{ !old('is_sub_recipe') ? 'checked' : '' }}>
                                <div class="p-2 bg-blue-50 rounded-lg text-blue-600 group-hover:scale-110 transition-transform">
                                    <i data-lucide="utensils" class="w-5 h-5"></i>
                                </div>
                                <span class="text-sm font-bold text-gray-700">Main Recipe</span>
                            </label>
                            <label class="relative flex flex-col items-center gap-2 p-3 rounded-xl border-2 transition-all cursor-pointer group bg-white hover:border-indigo-200" id="subTypeLabel">
                                <input type="radio" name="recipe_type_select" value="sub" class="absolute top-2 right-2" onchange="updateRecipeType('sub')" {{ old('is_sub_recipe') ? 'checked' : '' }}>
                                <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600 group-hover:scale-110 transition-transform">
                                    <i data-lucide="component" class="w-5 h-5"></i>
                                </div>
                                <span class="text-sm font-bold text-gray-700">Sub-Recipe</span>
                            </label>
                        </div>
                    </div>

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
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-bold text-gray-700">Category <span class="text-red-500">*</span></label>
                            <button type="button" onclick="openCategoryModal()" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 bg-blue-50 px-2 py-1 rounded-lg transition-all">
                                <i data-lucide="plus" class="w-3 h-3"></i> Quick Add
                            </button>
                        </div>
                        <div class="relative">
                            <select name="category_id" required id="category-select"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border {{ $errors->has('category_id') ? 'border-red-500' : 'border-gray-200' }} focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none appearance-none font-medium text-gray-700 cursor-pointer pr-10">
                                <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>Select a category...
                                </option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach

                            </select>
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
                                    value="{{ old('yield_portions', 10) }}" required
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
                    </div>

                    <!-- Sub-Recipe Toggle (Hidden, controlled by radio buttons) -->
                    <div id="subRecipeConfigSection" class="{{ old('is_sub_recipe') ? '' : 'hidden' }}">
                        <div class="bg-indigo-50/50 rounded-xl p-5 border border-indigo-100">
                            <h3 class="text-sm font-bold text-indigo-900 mb-1">Sub-Recipe Configuration</h3>
                            <p class="text-xs text-indigo-600/80 mb-4">Link this recipe to an ingredient for use in other recipes.</p>

                            <input type="hidden" name="is_sub_recipe" id="is_sub_recipe" value="{{ old('is_sub_recipe', 0) }}">

                            <div id="subRecipeFields" class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-indigo-800 uppercase mb-1.5">Produced Ingredient</label>
                                    <select name="produces_ingredient_id" id="produces_ingredient_id"
                                        class="w-full px-3 py-2.5 rounded-lg border border-indigo-200 focus:border-indigo-500 outline-none bg-white text-sm">
                                        <option value="">-- Auto-create ingredient with recipe name --</option>
                                        @foreach($ingredients as $ing)
                                            <option value="{{ $ing->id }}" {{ old('produces_ingredient_id') == $ing->id ? 'selected' : '' }}>
                                                Existing: {{ $ing->name }} ({{ $ing->measurement_unit }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-indigo-800 uppercase mb-1.5">Output Qty</label>
                                        <input type="number" name="output_quantity" step="0.001" min="0"
                                            value="{{ old('output_quantity', 1) }}"
                                            class="w-full px-3 py-2.5 rounded-lg border border-indigo-200 focus:border-indigo-500 outline-none bg-white placeholder-indigo-300">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-indigo-800 uppercase mb-1.5">Output Unit</label>
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
        </div>

        <!-- RIGHT CONTENT: Sets & Methods -->
        <div class="w-full xl:w-2/3 space-y-6 md:space-y-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                    <div class="p-2 bg-orange-50 rounded-lg text-orange-500">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                    Recipe Overview / Description
                </h2>
                <textarea name="method" rows="3"
                    class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none resize-y placeholder-gray-400"
                    placeholder="Brief description, chef's notes, or general overview of the dish...">{{ old('method') }}</textarea>
            </div>

            <!-- Sets Container -->
            <div id="stages-container" class="space-y-6">
                @if(old('stages') && count(old('stages')) > 0)
                    @foreach(old('stages') as $index => $stage)
                        @if($index == 999 || (isset($stage['name']) && $stage['name'] === 'Sub-Recipes')) @continue @endif
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
                                    </div>
                                </div>
                                <button type="button" onclick="removeStage(this)"
                                    class="text-gray-400 hover:text-red-500 p-2 rounded-lg hover:bg-red-50 transition-colors">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </button>
                            </div>

                            <div class="p-6 space-y-6">
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
                                            @if(isset($stage['ingredients']) && is_array($stage['ingredients']) && count($stage['ingredients']) > 0)
                                                @foreach($stage['ingredients'] as $rIndex => $ingredient)
                                                    <tr class="group hover:bg-blue-50/20 transition-colors ingredient-row">
                                                        <td class="px-4 py-2">
                                                            <select class="ingredient-select w-full px-3 py-2 rounded-lg border-2 border-gray-200 focus:border-blue-500 outline-none bg-white cursor-pointer pr-8 text-gray-900 font-medium"
                                                                name="stages[{{ $index }}][ingredients][{{ $rIndex }}][ingredient_id]" required>
                                                                <option value="">Select Ingredient...</option>
                                                                @foreach($ingredients as $ing)
                                                                    <option value="{{ $ing->id }}"
                                                                        data-price="{{ $ing->latest_price ?? $ing->price }}"
                                                                        data-unit="{{ $ing->measurement_unit }}" {{ ($ingredient['ingredient_id'] ?? '') == $ing->id ? 'selected' : '' }}>
                                                                        {{ $ing->name }} ({{ $ing->measurement_unit }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="px-2 md:px-4 py-2">
                                                            <input type="number" step="any"
                                                                name="stages[{{ $index }}][ingredients][{{ $rIndex }}][quantity]"
                                                                value="{{ $ingredient['quantity'] ?? '' }}" required
                                                                data-base-qty="{{ $ingredient['quantity'] ?? '' }}"
                                                                class="quantity-input w-full h-[40px] md:h-[44px] px-2 md:px-3 rounded-xl border-2 border-gray-300 focus:border-blue-500 outline-none text-center font-bold text-sm md:text-base text-gray-900 transition-all bg-white" placeholder="0">
                                                        </td>
                                                        <td class="px-2 md:px-4 py-2">
                                                            <input type="hidden" name="stages[{{ $index }}][ingredients][{{ $rIndex }}][unit]" value="{{ $ingredient['unit'] ?? '' }}" class="unit-value-input">
                                                            <input type="text" readonly value="{{ $ingredient['unit'] ?? '' }}"
                                                                class="unit-display w-full h-[40px] md:h-[44px] px-2 md:px-3 rounded-xl border-2 border-gray-300 bg-gray-50 font-bold text-sm md:text-base text-gray-700 cursor-not-allowed">
                                                        </td>
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
                                    <div class="flex border-t-2 border-blue-200">
                                    <button type="button" onclick="addIngredientRow(this)"
                                        class="flex-1 py-4 bg-blue-50 hover:bg-blue-100 text-blue-700 text-base font-bold transition-all flex items-center justify-center gap-2 shadow-sm hover:shadow-md">
                                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                                        <span>+ Add Ingredient</span>
                                    </button>
                                    <button type="button" onclick="openIngredientModal('')"
                                        class="py-4 px-5 bg-orange-50 hover:bg-orange-100 text-orange-700 text-sm font-bold transition-all flex items-center justify-center gap-1.5 border-l-2 border-orange-200">
                                        <i data-lucide="plus-square" class="w-4 h-4"></i>
                                        <span class="hidden sm:inline">New Ingredient</span>
                                    </button>
                                    </div>
                                </div>


                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Instructions</label>
                                    <textarea name="stages[{{ $index }}][method]" rows="3"
                                        class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none resize-y text-gray-700"
                                        placeholder="Detailed steps for this set...">{{ $stage['method'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <button type="button" id="addStageBtn" onclick="return addNewSet();"
                class="w-full py-4 border-2 border-dashed border-gray-300 rounded-2xl text-gray-500 font-bold hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50/50 transition-all flex items-center justify-center gap-2 group cursor-pointer">
                <div class="p-1 bg-gray-200 rounded-full text-white group-hover:bg-blue-500 transition-colors">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                </div>
                <span>Add Another Set</span>
            </button>

            <!-- Sub-Recipes Used Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 overflow-hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="component" class="w-5 h-5 text-indigo-500"></i>
                        Sub-Recipes Used
                    </h2>
                    <button type="button" id="addSubRecipeBtnTop" onclick="openSubRecipeModal(); return false;"
                        class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 font-medium transition-colors flex items-center gap-2 cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Add Sub-Recipe</span>
                    </button>
                </div>

                <div id="sub-recipes-container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                    </div>
                </div>
                <button type="button" onclick="removeStage(this)"
                    class="text-gray-400 hover:text-red-500 p-2 rounded-lg hover:bg-red-50 transition-colors">
                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-6 space-y-6">
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
                        </tbody>
                    </table>
                    <div class="flex border-t-2 border-blue-200">
                        <button type="button" onclick="addIngredientRow(this)"
                            class="flex-1 py-4 bg-blue-50 hover:bg-blue-100 text-blue-700 text-base font-bold transition-all flex items-center justify-center gap-2 shadow-sm hover:shadow-md">
                            <i data-lucide="plus-circle" class="w-5 h-5"></i>
                            <span>+ Add Ingredient</span>
                        </button>
                        <button type="button" onclick="openIngredientModal('')"
                            class="py-4 px-5 bg-orange-50 hover:bg-orange-100 text-orange-700 text-sm font-bold transition-all flex items-center justify-center gap-1.5 border-l-2 border-orange-200">
                            <i data-lucide="plus-square" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">New Ingredient</span>
                        </button>
                    </div>
                </div>

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
                <div class="relative w-full">
                    <select class="ingredient-select w-full px-3 py-2 rounded-lg border-2 border-gray-200 focus:border-blue-500 outline-none bg-white cursor-pointer pr-8 text-gray-900 font-medium"
                        name="stages[STAGE_INDEX][ingredients][ROW_INDEX][ingredient_id]" required>
                        <option value="">Select Ingredient...</option>
                    </select>
                </div>
            </td>
            <td class="px-2 md:px-4 py-2">
                <input type="number" step="any" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][quantity]" required
                    data-base-qty="" value=""
                    class="quantity-input w-full h-[40px] md:h-[44px] px-2 md:px-3 rounded-xl border-2 border-gray-300 focus:border-blue-500 outline-none text-center font-bold text-sm md:text-base text-gray-900 transition-all bg-white" placeholder="0">
            </td>
            <td class="px-2 md:px-4 py-2">
                <input type="hidden" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][unit]" value="" class="unit-value-input">
                <input type="text" readonly value=""
                    class="unit-display w-full h-[40px] md:h-[44px] px-2 md:px-3 rounded-xl border-2 border-gray-300 bg-gray-50 font-bold text-sm md:text-base text-gray-700 cursor-not-allowed"
                    placeholder="Select item first">
            </td>
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

    <!-- Quick Category Modal -->
    <div id="createCategoryModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[60] hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i data-lucide="folder-plus" class="w-6 h-6 text-blue-500"></i>
                    New Recipe Category
                </h3>
                <button type="button" onclick="closeCategoryModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <div class="p-8">
                <form id="quickCategoryForm" onsubmit="event.preventDefault(); submitQuickCategory();">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Category Name</label>
                        <input type="text" id="quick_category_name" name="name" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium"
                            placeholder="e.g. Desserts">
                        <input type="hidden" name="type" value="recipe">
                    </div>
                    
                    <div class="mt-8 flex gap-3">
                        <button type="button" onclick="closeCategoryModal()"
                            class="flex-1 py-3 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg transition-all">
                            Create Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden Options -->
    <div id="ingredientOptions" style="display: none;">
        @php
            $producedIds = $subRecipes->pluck('produces_ingredient_id')->filter()->toArray();
        @endphp
        <optgroup label="Core Ingredients">
            @foreach($ingredients as $ing)
                @if(!in_array($ing->id, $producedIds))
                    <option value="{{ $ing->id }}" data-price="{{ $ing->latest_price ?? $ing->price }}"
                        data-unit="{{ $ing->measurement_unit }}">
                        {{ $ing->name }} ({{ $ing->measurement_unit }})
                    </option>
                @endif
            @endforeach
        </optgroup>
        @if(count($subRecipes) > 0)
            <optgroup label="Sub-Recipes (Internal Components)">
                @foreach($subRecipes as $sub)
                    @if($sub->produces_ingredient_id)
                        <option value="{{ $sub->produces_ingredient_id }}" 
                            data-price="{{ $sub->producesIngredient->latest_price ?? $sub->producesIngredient->price ?? 0 }}"
                            data-unit="{{ $sub->output_unit ?? $sub->producesIngredient->measurement_unit ?? 'pcs' }}">
                            {{ $sub->name }} (Recipe Component)
                        </option>
                    @endif
                @endforeach
            </optgroup>
        @endif
    </div>

    <!-- Sub-Recipe Modal -->
    <div id="addSubRecipeModal"
        class="fixed inset-0 bg-gray-900/70 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 animate-fade-in">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl transform transition-all scale-100 hover:scale-[1.01]">
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
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <div class="p-1.5 bg-indigo-100 rounded-lg">
                            <i data-lucide="search" class="w-4 h-4 text-indigo-600"></i>
                        </div>
                        Select Sub-Recipe
                    </label>
                    <div class="relative">
                        <select id="sub-recipe-selector" onchange="window.handleSubRecipeSelect(this)"
                            class="w-full h-[56px] px-5 rounded-xl border-2 border-gray-200 focus:border-indigo-500 py-3 appearance-none bg-white cursor-pointer focus:ring-4 focus:ring-indigo-500/20 outline-none transition-all text-base font-semibold shadow-sm hover:border-indigo-300">
                            <option value="">-- Select Sub-Recipe --</option>
                            @if(count($subRecipes) > 0)
                                @foreach($subRecipes as $sub)
                                    <option value="{{ $sub->id }}" data-name="{{ $sub->name }}"
                                        data-ing-id="{{ $sub->produces_ingredient_id }}"
                                        data-unit="{{ $sub->producesIngredient->measurement_unit ?? 'pcs' }}"
                                        data-price="{{ $sub->producesIngredient->latest_price ?? $sub->producesIngredient->price ?? 0 }}">
                                        {{ $sub->name }}
                                        @if($sub->producesIngredient)({{ $sub->producesIngredient->measurement_unit ?? 'pcs' }})@endif
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <div class="p-1.5 bg-blue-100 rounded-lg">
                                <i data-lucide="hash" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            Quantity
                        </label>
                        <input type="number" id="sub-recipe-qty" step="any" min="0.001" value="1"
                            class="w-full h-[56px] px-5 rounded-xl border-2 border-gray-200 focus:border-blue-500 text-center text-xl font-bold">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <div class="p-1.5 bg-purple-100 rounded-lg">
                                <i data-lucide="ruler" class="w-4 h-4 text-purple-600"></i>
                            </div>
                            Unit
                        </label>
                        <input type="text" id="sub-recipe-unit-display" readonly placeholder="Auto"
                            class="w-full h-[56px] px-5 rounded-xl border-2 border-indigo-200 bg-gray-50 text-indigo-700 text-center text-xl font-bold cursor-not-allowed">
                    </div>
                </div>
            </div>

            <div class="p-6 bg-gray-50 border-t border-gray-200 flex gap-4">
                <button type="button" onclick="closeSubRecipeModal()"
                    class="flex-1 px-6 py-4 bg-white border-2 border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-all">Cancel</button>
                <button type="button" onclick="confirmAddSubRecipe()"
                    class="flex-1 px-6 py-4 bg-indigo-600 text-white font-bold rounded-xl shadow-xl hover:bg-indigo-700 transition-all"
                    id="addSubRecipeBtn" {{ count($subRecipes) === 0 ? 'disabled' : '' }}>Add to Recipe</button>
            </div>
        </div>
    </div>

    <!-- Quick Create Ingredient Modal -->
    <div id="createIngredientModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-[70] flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all scale-100">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-6 flex justify-between items-center relative overflow-hidden">
                <div class="absolute inset-0 bg-grid-white/10 [mask-image:linear-gradient(0deg,transparent,black)]"></div>
                <div class="relative">
                    <h3 class="text-xl font-bold text-white flex items-center gap-2">
                        <i data-lucide="plus-square" class="w-6 h-6"></i>
                        Request New Ingredient
                    </h3>
                    @if(!auth()->user()->isAdmin())
                        <p class="text-blue-100 text-sm mt-0.5 opacity-90">Requires Admin approval before becoming active</p>
                    @endif
                </div>
                <button type="button" onclick="closeIngredientModal()" class="text-blue-100 hover:text-white transition-colors relative">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <div class="p-8 max-h-[75vh] overflow-y-auto">
                <form id="quickIngredientForm" class="space-y-8">
                    <!-- Section 1: Basic Info -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                            <i data-lucide="info" class="w-3.5 h-3.5"></i> Basic Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="col-span-full">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Ingredient Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="quick_name" required
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 focus:border-blue-500 outline-none transition-all font-medium placeholder-gray-400"
                                    placeholder="e.g. Extra Virgin Olive Oil">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <select name="category_id" id="quick_category_select" required
                                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 focus:border-blue-500 outline-none bg-white cursor-pointer font-medium appearance-none">
                                        <option value="">Select Category...</option>
                                        @foreach($ingredientCategories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" onclick="openCategoryModal('', (res) => { if(res){ const s = document.getElementById('quick_category_select'); const o = new Option(res.name, res.id, true, true); s.add(o); } })"
                                        class="absolute right-10 top-1/2 -translate-y-1/2 p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition-all" title="Add New Category">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                    </button>
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Usage Unit <span class="text-red-500">*</span></label>
                                <select name="measurement_unit" required
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 focus:border-blue-500 outline-none bg-white cursor-pointer font-medium">
                                    <option value="">Select Unit...</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->value }}">{{ $unit->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Purchase Details -->
                    <div class="space-y-4 p-6 bg-blue-50/50 rounded-2xl border border-blue-100">
                        <h4 class="text-xs font-bold text-blue-500 uppercase tracking-widest flex items-center gap-2">
                            <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i> Purchase Metrics
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Purchase Qty</label>
                                <input type="number" name="purchase_quantity" step="0.001" min="0" value="1"
                                    class="w-full px-3 py-2.5 rounded-lg border-2 border-white focus:border-blue-400 outline-none bg-white font-bold text-center">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Purchase Unit</label>
                                <select name="purchase_unit" 
                                    class="w-full px-3 py-2.5 rounded-lg border-2 border-white focus:border-blue-400 outline-none bg-white font-bold cursor-pointer">
                                    <option value="">Select...</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->value }}">{{ $unit->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-full md:col-span-1">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Standard Price</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                                    <input type="number" name="purchase_price" step="0.01" min="0" placeholder="0.00"
                                        class="w-full pl-8 pr-3 py-2.5 rounded-lg border-2 border-white focus:border-blue-400 outline-none bg-white font-bold">
                                </div>
                            </div>
                            <div class="col-span-full">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Primary Vendor (Optional)</label>
                                <input type="text" name="vendor" placeholder="e.g. Local Market"
                                    class="w-full px-4 py-2.5 rounded-lg border-2 border-white focus:border-blue-400 outline-none bg-white font-medium">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Alerts & Storage -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                                <i data-lucide="map-pin" class="w-4 h-4 text-gray-400"></i> Storage
                            </label>
                            <select name="storage_location" required
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 focus:border-blue-500 outline-none bg-white cursor-pointer font-medium">
                                <option value="Fridge">Fridge</option>
                                <option value="Freezer">Freezer</option>
                                <option value="Dry Store" selected>Dry Store</option>
                                <option value="Bar">Bar</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                                <i data-lucide="bell" class="w-4 h-4 text-gray-400"></i> Alert At
                            </label>
                            <input type="number" name="alert_threshold" step="0.01" min="0" value="0"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 focus:border-blue-500 outline-none font-bold text-center">
                        </div>
                    </div>

                    <!-- Section 4: Allergens -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                            <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i> Allergen Safety
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach(App\Enums\Allergen::cases() as $allergen)
                                <label class="flex items-center gap-2.5 p-2 rounded-xl bg-gray-50 hover:bg-red-50 border border-gray-100 transition-all cursor-pointer group">
                                    <input type="checkbox" name="allergen_tags[]" value="{{ $allergen->value }}" 
                                        class="rounded text-red-500 focus:ring-red-500/20 w-4 h-4 transition-all">
                                    <span class="text-xs font-bold text-gray-600 group-hover:text-red-700">{{ $allergen->label() }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" onclick="closeIngredientModal()"
                            class="flex-1 py-4 text-gray-500 font-bold hover:bg-gray-100 rounded-2xl transition-all">
                            Cancel
                        </button>
                        <button type="button" onclick="submitQuickIngredient()"
                            class="flex-[2] py-4 bg-blue-600 text-white font-bold rounded-2xl shadow-xl shadow-blue-500/30 hover:bg-blue-700 transition-all flex items-center justify-center gap-2">
                            <i data-lucide="send" class="w-5 h-5"></i>
                            Submit Request
                        </button>
                    </div>
                </form>
                <!-- Success message shown after submit -->
                <div id="ingredientSubmitSuccess" class="hidden py-12 px-6 text-center animate-fade-in-up">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="check-circle-2" class="w-10 h-10 text-green-600"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-2">Request Processed!</div>
                    <p class="text-gray-500 text-base mb-8 max-w-sm mx-auto" id="ingredientSubmitMsg"></p>
                    <button onclick="closeIngredientModal()" 
                        class="w-full py-4 bg-gray-900 text-white font-bold rounded-2xl hover:bg-gray-800 transition-all">
                        Return to Recipe
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <style>
        .ts-control { border: none !important; padding: 0 !important; background: transparent !important; box-shadow: none !important; cursor: pointer !important; }
        .ts-dropdown { z-index: 99999 !important; background: white !important; border: 1px solid #e5e7eb !important; border-radius: 0.5rem !important; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important; }
        .ts-dropdown .option.active { background-color: #3b82f6 !important; color: white !important; }
        .animate-fade-in-up { animation: fadeInUp 0.3s ease-out forwards; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    <script>
        @php
            $realStageCount = collect(old('stages', []))->filter(fn($s, $k) => $k != 999 && ($s['name'] ?? '') !== 'Sub-Recipes')->count();
        @endphp
        let stageCount = {{ $realStageCount }};
        let ingredientOptionsHTML = '';

        window.addedSubRecipes = [];

        // Sub-Recipe Mode Toggle
        let subRecipeEnabled = {{ old('is_sub_recipe', 0) ? 'true' : 'false' }};

        function updateRecipeType(type) {
            const isSub = type === 'sub';
            document.getElementById('is_sub_recipe').value = isSub ? 1 : 0;
            
            const configSection = document.getElementById('subRecipeConfigSection');
            if (configSection) {
                if (isSub) {
                    configSection.classList.remove('hidden');
                    configSection.classList.add('animate-fade-in-up');
                } else {
                    configSection.classList.add('hidden');
                }
            }

            // Update UI styles
            const mainLabel = document.getElementById('mainTypeLabel');
            const subLabel = document.getElementById('subTypeLabel');
            
            if (isSub) {
                subLabel.classList.add('border-indigo-500', 'bg-indigo-50', 'ring-4', 'ring-indigo-500/10');
                subLabel.classList.remove('border-gray-100');
                mainLabel.classList.remove('border-blue-500', 'bg-blue-50', 'ring-4', 'ring-blue-500/10');
                mainLabel.classList.add('border-gray-100');
            } else {
                mainLabel.classList.add('border-blue-500', 'bg-blue-50', 'ring-4', 'ring-blue-500/10');
                mainLabel.classList.remove('border-gray-100');
                subLabel.classList.remove('border-indigo-500', 'bg-indigo-50', 'ring-4', 'ring-indigo-500/10');
                subLabel.classList.add('border-gray-100');
            }
        }

        // Set initial toggle state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const currentType = document.querySelector('input[name="recipe_type_select"]:checked')?.value || 'main';
            updateRecipeType(currentType);
        });

        document.addEventListener('DOMContentLoaded', () => {
            const ingredientOptionsEl = document.getElementById('ingredientOptions');
            if (ingredientOptionsEl) ingredientOptionsHTML = ingredientOptionsEl.innerHTML;

            // Standard select used instead of TomSelect as requested
            window.subRecipeSelector = null;

            // Form Submit Override to inject sub-recipes
            const form = document.getElementById('recipeForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (window.addedSubRecipes.length > 0) {
                        const stageIdx = 999;
                        const container = document.createElement('div');
                        container.style.display = 'none';
                        
                        // Create a special stage name
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
                        });
                        this.appendChild(container);
                    }
                });
            }

            // Re-populate sub-recipes from old validation data
            @php
                $oldSubRecipes = old('stages.999.ingredients') ?? [];
            @endphp
            const oldSubs = @json($oldSubRecipes);
            if (oldSubs && Array.isArray(oldSubs) && oldSubs.length > 0) {
                oldSubs.forEach(s => {
                    const opt = document.querySelector(`#sub-recipe-selector option[data-ing-id="${s.ingredient_id}"]`);
                    if (opt) {
                        window.addedSubRecipes.push({
                            id: opt.value,
                            ingId: s.ingredient_id,
                            name: opt.dataset.name,
                            qty: s.quantity,
                            unit: s.unit,
                            price: parseFloat(opt.dataset.price || 0)
                        });
                    }
                });
                renderSubRecipeCards();
            }

            // Re-initialize all existing rows (from old validation data)
            document.querySelectorAll('.ingredient-row').forEach(row => {
                initIngredientRow(row);
            });

            if (stageCount === 0) addNewSet();
            calculateTotal();
        });

        function calculateRowCost(row) {
            const select = row.querySelector('.ingredient-select');
            const qtyInput = row.querySelector('.quantity-input');
            const costDisplay = row.querySelector('.cost-display');
            if (!costDisplay || !select) return;

            const opt = select.options[select.selectedIndex];
            if (!opt || !opt.value) { costDisplay.textContent = '0.00'; calculateTotal(); return; }

            const price = parseFloat(opt.getAttribute('data-price')) || 0;
            const qty = parseFloat(qtyInput.value) || 0;
            costDisplay.textContent = (price * qty).toFixed(2);
            calculateTotal();
        }

        function removeRow(btn) {
            btn.closest('tr').remove();
            calculateTotal();
        }

        function removeStage(btn) {
            const container = document.getElementById('stages-container');
            if (container.querySelectorAll('.stage-block').length <= 1) {
                alert('Recipe must have at least one set/stage.');
                return;
            }
            if (confirm('Are you sure you want to remove this entire set?')) {
                btn.closest('.stage-block').remove();
                calculateTotal();
            }
        }

        function calculateTotal() {
            let total = 0;
            // Ingredients cost
            document.querySelectorAll('.cost-display').forEach(el => {
                const val = parseFloat(el.textContent.replace(/,/g, ''));
                if (!isNaN(val)) total += val;
            });
            // Sub-recipes cost
            document.querySelectorAll('.cost-val').forEach(el => {
                const val = parseFloat(el.textContent.replace(/,/g, ''));
                if (!isNaN(val)) total += val;
            });
            
            const display = document.getElementById('totalCostDisplay');
            if (display) display.textContent = total.toFixed(2);
        }

        function openCategoryModal(name = '', callback = null) {
            window.categoryCallback = callback;
            document.getElementById('quick_category_name').value = name;
            document.getElementById('createCategoryModal').classList.remove('hidden');
            setTimeout(() => document.getElementById('quick_category_name').focus(), 100);
        }

        function closeCategoryModal() {
            document.getElementById('createCategoryModal').classList.add('hidden');
            if (window.categoryCallback) { window.categoryCallback(false); window.categoryCallback = null; }
        }

        function submitQuickCategory() {
            const name = document.getElementById('quick_category_name').value;
            if (!name) return alert('Enter name');
            fetch('{{ route("categories.storeQuick", ["kitchen_slug" => request()->route("kitchen_slug")]) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ name: name, type: 'recipe' })
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    const select = document.getElementById('category-select');
                    const option = new Option(res.name, res.id, true, true);
                    if (select) select.add(option);
                    
                    if (window.categoryCallback) {
                        window.categoryCallback(res);
                        window.categoryCallback = null; // Prevent closeCategoryModal from firing it again
                    }
                    closeCategoryModal();
                }
            }).catch(e => {
                alert('Error creating category');
                console.error(e);
            });
        }

        function openIngredientModal(name) {
            document.getElementById('quick_name').value = name;
            document.getElementById('createIngredientModal').classList.remove('hidden');
        }

        function closeIngredientModal() {
            document.getElementById('createIngredientModal').classList.add('hidden');
            // Reset form & hide success panel for next open
            document.getElementById('quickIngredientForm').classList.remove('hidden');
            document.getElementById('quickIngredientForm').reset();
            document.getElementById('ingredientSubmitSuccess').classList.add('hidden');
        }


        function submitQuickIngredient() {
            const form = document.getElementById('quickIngredientForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            // Handle multiple checkboxes for allergen_tags
            const allergenTags = formData.getAll('allergen_tags[]');
            data.allergen_tags = allergenTags;

            fetch('{{ route('ingredients.storeQuick', ['kitchen_slug' => request()->route('kitchen_slug')]) }}', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                    'Accept': 'application/json' 
                },
                body: JSON.stringify(data)
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    if (res.status === 'approved') {
                        // Admin created - add directly to all ingredient dropdowns
                        const newOptionInfo = {
                            id: res.ingredient.id,
                            name: res.ingredient.name,
                            unit: res.ingredient.unit,
                            price: res.ingredient.price
                        };

                        const formattedText = `${newOptionInfo.name} (${newOptionInfo.unit})`;

                        // Update all active ingredient selects
                        document.querySelectorAll('.ingredient-select, #produces_ingredient_id').forEach(s => {
                            const o = document.createElement('option');
                            o.value = newOptionInfo.id;
                            o.textContent = formattedText;
                            o.setAttribute('data-price', newOptionInfo.price);
                            o.setAttribute('data-unit', newOptionInfo.unit);
                            s.appendChild(o);
                        });

                        // Update the hidden options template div
                        const optDiv = document.getElementById('ingredientOptions');
                        if (optDiv) {
                            const o = document.createElement('option');
                            o.value = newOptionInfo.id;
                            o.textContent = formattedText;
                            o.setAttribute('data-price', newOptionInfo.price);
                            o.setAttribute('data-unit', newOptionInfo.unit);
                            optDiv.appendChild(o);
                        }

                        closeIngredientModal();
                    } else {
                        // Staff created - show success state in modal
                        form.classList.add('hidden');
                        document.getElementById('ingredientSubmitSuccess').classList.remove('hidden');
                        document.getElementById('ingredientSubmitMsg').textContent =
                            '"' + res.ingredient.name + '" has been sent to Admin for approval. It will appear once approved.';
                        
                        // Re-initialize icons for the success message
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                } else {
                    alert(res.message || 'Failed to submit ingredient.');
                }
            }).catch(err => {
                console.error('Error submitting ingredient:', err);
                alert('Network error. Please try again.');
            });
        }

        window.handleSubRecipeSelect = function(el) {
            const opt = el.options[el.selectedIndex];
            const unitDisplay = document.getElementById('sub-recipe-unit-display');
            if (opt && unitDisplay) {
                unitDisplay.value = opt.dataset.unit || 'pcs';
            }
        }

        function openSubRecipeModal() { document.getElementById('addSubRecipeModal').classList.remove('hidden'); }
        function closeSubRecipeModal() { 
            document.getElementById('addSubRecipeModal').classList.add('hidden'); 
            const select = document.getElementById('sub-recipe-selector');
            if (select) select.value = '';
            document.getElementById('sub-recipe-qty').value = 1;
            document.getElementById('sub-recipe-unit-display').value = '';
        }
        
        function confirmAddSubRecipe() {
            const select = document.getElementById('sub-recipe-selector');
            const qty = parseFloat(document.getElementById('sub-recipe-qty').value);
            if (isNaN(qty) || qty <= 0) return alert('Enter valid quantity');

            let val = '';
            let opt = null;

            if (window.subRecipeSelector) {
                val = window.subRecipeSelector.getValue();
                opt = window.subRecipeSelector.options[val];
            } else {
                val = select.value;
                opt = select.options[select.selectedIndex];
            }

            if (!val) return alert('Select sub-recipe');
            
            const originalOpt = document.querySelector(`#sub-recipe-selector option[value="${val}"]`);
            
            const subData = { 
                id: val,
                ingId: originalOpt?.dataset?.ingId || val,
                name: originalOpt?.dataset?.name || opt?.text || 'Unknown', 
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
            if (!container) return;
            container.querySelectorAll('.sub-recipe-card').forEach(e => e.remove());
            document.getElementById('no-sub-recipes-msg').classList.toggle('hidden', window.addedSubRecipes.length > 0);
            window.addedSubRecipes.forEach((s, i) => {
                const cost = (s.qty * s.price).toFixed(2);
                const card = document.createElement('div');
                card.className = 'sub-recipe-card bg-indigo-50 border border-indigo-100 rounded-xl p-4 flex justify-between items-center animate-fade-in-up';
                card.innerHTML = `<div class="flex items-center gap-3">
                    <div class="p-2 bg-white rounded-lg shadow-sm text-indigo-500">
                        <i data-lucide="component" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <input type="hidden" name="stages[999][ingredients][${i}][ingredient_id]" value="${s.ingId}">
                        <input type="hidden" name="stages[999][ingredients][${i}][quantity]" value="${s.qty}">
                        <input type="hidden" name="stages[999][ingredients][${i}][unit]" value="${s.unit}">
                        <input type="hidden" name="stages[999][ingredients][${i}][ingredient_group]" value="Sub-Recipe">
                        <h4 class="font-bold">${s.name}</h4>
                        <p class="text-xs text-gray-500">${s.qty} ${s.unit} • ₹${cost}</p>
                    </div>
                </div>
                <button type="button" onclick="window.addedSubRecipes.splice(${i},1);renderSubRecipeCards();calculateTotal();" class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition-all">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
                <span class="cost-val hidden">${cost}</span>`;
                container.appendChild(card);
            });
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    </script>
@endpush
