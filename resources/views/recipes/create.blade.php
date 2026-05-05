@extends('layouts.app')

@section('header')
    <div>
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('recipes.index') }}" class="btn btn-secondary p-2">
                <i data-lucide="arrow-left"></i>
            </a>
            <div>
                <h1>Create Recipe</h1>
                <p class="text-muted text-sm mt-1">Design a new culinary creation.</p>
            </div>
        </div>
    </div>
@endsection

@section('actions')
    <div class="flex items-center gap-3">
        <button type="button" onclick="window.history.back()" class="btn btn-secondary">
            Cancel
        </button>
        <button type="submit" form="recipeForm" class="btn btn-primary">
            <i data-lucide="save"></i>
            <span>Save Recipe</span>
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

            // Initialize Tom Select for searchability
            if (select && typeof TomSelect !== 'undefined' && !select.tomselect) {
                const ts = new TomSelect(select, {
                    valueField: 'id',
                    labelField: 'name',
                    searchField: 'name',
                    create: false,
                    placeholder: "Type 3+ chars to search...",
                    allowEmptyOption: true,
                    maxOptions: 50,
                    dropdownParent: 'body',
                    load: function(query, callback) {
                        if (query.length < 3) return callback();
                        const kitchenSlug = '{{ request()->route("kitchen_slug") }}';
                        fetch(`/k/${kitchenSlug}/ingredients/search?q=` + encodeURIComponent(query))
                            .then(response => response.json())
                            .then(json => {
                                callback(json);
                            }).catch(() => {
                                callback();
                            });
                    },
                    render: {
                        option: function(item, escape) {
                            return `<div class="py-2 px-3 border-b border-subtle hover:bg-white/5 transition-colors">
                                <div class="font-bold text-primary">${escape(item.name)}</div>
                                <div class="text-[10px] text-muted uppercase font-bold tracking-widest">${escape(item.unit)} • ₹${escape(item.price)}</div>
                            </div>`;
                        },
                        item: function(item, escape) {
                            return `<div class="font-bold text-accent">${escape(item.name)} <span class="text-[10px] text-muted font-normal">(${escape(item.unit)})</span></div>`;
                        }
                    },
                    onChange: function(value) {
                        const item = this.options[value];
                        if (item) {
                            const unitValue = item.unit;
                            const unitLabel = UNIT_LABELS_MAP[unitValue] || unitValue;
                            const unitValueInput = row.querySelector('.unit-value-input');
                            if (unitValueInput) unitValueInput.value = unitValue;
                            const unitDisplay = row.querySelector('.unit-display');
                            if (unitDisplay) {
                                unitDisplay.value = unitLabel;
                                unitDisplay.removeAttribute('placeholder');
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
                    }
                });
                select.tomselect = ts;
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
            <div class="card p-0 sticky top-6">
                <div class="p-6 border-b border-subtle">
                    <h2 class="flex items-center gap-2 text-primary">
                        <i data-lucide="info" class="text-accent"></i>
                        Recipe Details
                    </h2>
                </div>

                <div class="p-4 md:p-6 space-y-4 md:space-y-6">
                    <!-- Recipe Type Selection -->
                    <div class="bg-white/5 p-4 rounded-xl border border-subtle mb-2">
                        <label class="form-label mb-3">Recipe Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex flex-col items-center gap-2 p-4 rounded-xl border border-subtle transition-all cursor-pointer group hover:border-accent" id="mainTypeLabel">
                                <input type="radio" name="recipe_type_select" value="main" class="absolute top-3 right-3 accent-brass" onchange="updateRecipeType('main')" {{ !old('is_sub_recipe') ? 'checked' : '' }}>
                                <i data-lucide="utensils" class="text-accent group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm font-bold text-primary">Main Recipe</span>
                            </label>
                            <label class="relative flex flex-col items-center gap-2 p-4 rounded-xl border border-subtle transition-all cursor-pointer group hover:border-accent" id="subTypeLabel">
                                <input type="radio" name="recipe_type_select" value="sub" class="absolute top-3 right-3 accent-brass" onchange="updateRecipeType('sub')" {{ old('is_sub_recipe') ? 'checked' : '' }}>
                                <i data-lucide="component" class="text-accent group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm font-bold text-primary">Sub-Recipe</span>
                            </label>
                        </div>
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="form-label">Recipe Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                            class="form-control"
                            placeholder="e.g. Truffle Mushroom Risotto">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Category -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="form-label">Category <span class="text-red-500">*</span></label>
                            <button type="button" onclick="openCategoryModal()" class="text-[10px] font-bold text-accent uppercase tracking-widest bg-white/5 px-2 py-1 rounded transition-colors">
                                + Quick Add
                            </button>
                        </div>
                        <select name="category_id" required id="category-select" class="form-control">
                            <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>Select category...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Smart Scale Section -->
                    <div class="bg-white/5 rounded-xl p-5 border border-subtle mb-4">
                        <h3 class="text-sm font-bold text-primary flex items-center gap-2 mb-4 uppercase tracking-widest">
                            <i data-lucide="calculator" class="text-accent"></i>
                            Smart Scale
                        </h3>
                        <div>
                            <label class="text-[10px] font-bold text-muted uppercase tracking-widest mb-2 block">Scaling Multiplier</label>
                            <div class="flex gap-2">
                                @foreach([0.5, 1, 2, 3] as $mult)
                                <button type="button" onclick="setMultiplier({{ $mult }})"
                                    class="scaling-btn flex-1 py-2 rounded border border-subtle bg-primary/20 text-muted font-bold text-xs hover:border-accent transition-all {{ $mult == 1 ? 'active' : '' }}"
                                    data-multiplier="{{ $mult }}">{{ $mult }}x</button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Yields Section -->
                    <div class="bg-primary/20 rounded-xl p-5 border border-subtle border-dashed">
                        <label class="text-[10px] font-bold text-muted uppercase tracking-widest mb-4 block">Yield Configuration</label>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <label class="text-[10px] text-muted font-bold uppercase tracking-widest">Portions <span class="text-red-500">*</span></label>
                                    <button type="button" onclick="resetScaling()" class="text-[10px] text-accent hover:underline font-bold uppercase tracking-widest">Reset</button>
                                </div>
                                <input type="number" name="yield_portions" id="yield_portions" min="1" step="1"
                                    value="{{ old('yield_portions', 10) }}" required
                                    class="form-control text-center font-bold"
                                    placeholder="10" oninput="updateYieldCalcs('portions')">
                            </div>
                            <div>
                                <label class="text-[10px] text-muted font-bold uppercase tracking-widest mb-1.5 block">Weight (g)</label>
                                <input type="number" name="yield_weight_grams" id="yield_weight_grams" min="0" step="any"
                                    value="{{ old('yield_weight_grams') }}"
                                    class="form-control text-center font-bold"
                                    placeholder="5000" oninput="updateYieldCalcs('weight')">
                            </div>
                        </div>
                        <div class="mt-4 p-3 bg-white/5 rounded-lg border border-subtle flex justify-between items-center">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-muted uppercase tracking-widest">Grams per Portion</span>
                                <span id="gramsPerPortionDisplay" class="text-lg font-bold text-primary">0g</span>
                            </div>
                            <i data-lucide="scale" class="text-accent opacity-50"></i>
                        </div>
                        <input type="hidden" name="yield_batches" value="1">
                    </div>

                    <!-- Sub-Recipe Toggle -->
                    <div id="subRecipeConfigSection" class="{{ old('is_sub_recipe') ? '' : 'hidden' }}">
                        <div class="bg-primary/20 rounded-xl p-5 border border-accent/20">
                            <h3 class="text-sm font-bold text-primary mb-1 uppercase tracking-widest">Sub-Recipe Config</h3>
                            <p class="text-[10px] text-muted mb-4 uppercase font-bold tracking-widest">Link this recipe to an ingredient.</p>

                            <input type="hidden" name="is_sub_recipe" id="is_sub_recipe" value="{{ old('is_sub_recipe', 0) }}">

                            <div id="subRecipeFields" class="space-y-4">
                                <div>
                                    <label class="form-label">Produced Ingredient</label>
                                    <select name="produces_ingredient_id" id="produces_ingredient_id" class="form-control">
                                        <option value="">-- Auto-create ingredient --</option>
                                        @foreach($ingredients as $ing)
                                            <option value="{{ $ing->id }}" {{ old('produces_ingredient_id') == $ing->id ? 'selected' : '' }}>
                                                {{ $ing->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="form-label">Output Qty</label>
                                        <input type="number" name="output_quantity" step="0.001" min="0" value="{{ old('output_quantity', 1) }}" class="form-control">
                                    </div>
                                    <div>
                                        <label class="form-label">Output Unit</label>
                                        <select name="output_unit" class="form-control">
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
            <div class="card space-y-4">
                <h2 class="flex items-center gap-2 text-primary">
                    <i data-lucide="file-text" class="text-accent"></i>
                    Recipe Overview
                </h2>
                <textarea name="method" rows="3" class="form-control min-h-[100px]"
                    placeholder="Brief description, chef's notes, or general overview of the dish...">{{ old('method') }}</textarea>
            </div>

            <!-- Sets Container -->
            <div id="stages-container" class="space-y-6">
                @if(old('stages') && count(old('stages')) > 0)
                    @foreach(old('stages') as $index => $stage)
                        @if($index == 999 || (isset($stage['name']) && $stage['name'] === 'Sub-Recipes')) @continue @endif
                        <div class="stage-block card p-0 overflow-hidden" data-stage-index="{{ $index }}">
                            <div class="bg-white/5 border-b border-subtle p-4 flex justify-between items-center">
                                <div class="flex items-center gap-3 flex-1">
                                    <i data-lucide="grip-vertical" class="text-muted cursor-move"></i>
                                    <input type="text" name="stages[{{ $index }}][name]" value="{{ $stage['name'] ?? '' }}"
                                        class="bg-transparent border-none text-lg font-bold text-primary focus:ring-0 placeholder-white/20 w-full"
                                        placeholder="Set Name (e.g. Sauce Prep)">
                                </div>
                                <button type="button" onclick="removeStage(this)" class="text-muted hover:text-red-500 transition-colors">
                                    <i data-lucide="trash-2"></i>
                                </button>
                            </div>

                            <div class="p-6 space-y-6">
                                <div class="overflow-x-auto">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="w-[50%]">Item</th>
                                                <th class="w-[20%] text-center">Qty</th>
                                                <th class="w-[20%]">Unit</th>
                                                @if(auth()->user()->isAdmin())
                                                    <th class="w-[10%] text-right">Cost</th>
                                                @endif
                                                <th class="w-[5%]"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(isset($stage['ingredients']) && is_array($stage['ingredients']) && count($stage['ingredients']) > 0)
                                                @foreach($stage['ingredients'] as $rIndex => $ingredient)
                                                    <tr class="hover:bg-white/5 transition-colors ingredient-row">
                                                        <td class="p-2">
                                                            <select class="ingredient-select w-full" name="stages[{{ $index }}][ingredients][{{ $rIndex }}][ingredient_id]" required>
                                                                <option value="">Select Item...</option>
                                                                @foreach($ingredients as $ing)
                                                                    <option value="{{ $ing->id }}"
                                                                        data-price="{{ $ing->latest_price ?? $ing->price }}"
                                                                        data-unit="{{ $ing->measurement_unit }}" {{ ($ingredient['ingredient_id'] ?? '') == $ing->id ? 'selected' : '' }}>
                                                                        {{ $ing->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="p-2">
                                                            <input type="number" step="any"
                                                                name="stages[{{ $index }}][ingredients][{{ $rIndex }}][quantity]"
                                                                value="{{ $ingredient['quantity'] ?? '' }}" required
                                                                data-base-qty="{{ $ingredient['quantity'] ?? '' }}"
                                                                class="quantity-input form-control text-center font-bold" placeholder="0">
                                                        </td>
                                                        <td class="p-2">
                                                            <input type="hidden" name="stages[{{ $index }}][ingredients][{{ $rIndex }}][unit]" value="{{ $ingredient['unit'] ?? '' }}" class="unit-value-input">
                                                            <input type="text" readonly value="{{ $ingredient['unit'] ?? '' }}" class="unit-display form-control bg-primary/20 cursor-not-allowed">
                                                        </td>
                                                        @if(auth()->user()->isAdmin())
                                                            <td class="p-2 text-right font-bold text-accent cost-display">0.00</td>
                                                        @endif
                                                        <td class="p-2 text-center">
                                                            <button type="button" onclick="removeRow(this)" class="text-muted hover:text-red-500 transition-colors">
                                                                <i data-lucide="x"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="flex border-t border-subtle bg-white/5">
                                        <button type="button" onclick="addIngredientRow(this)" class="btn-ghost flex-1 py-4 text-accent font-bold hover:bg-white/5 flex items-center justify-center gap-2">
                                            <i data-lucide="plus-circle"></i> Add Ingredient
                                        </button>
                                        <button type="button" onclick="openIngredientModal('')" class="btn-ghost py-4 px-6 text-muted border-l border-subtle hover:text-accent flex items-center gap-2">
                                            <i data-lucide="plus-square"></i> New
                                        </button>
                                    </div>
                                </div>


                                <div>
                                    <label class="form-label mb-2">Instructions</label>
                                    <textarea name="stages[{{ $index }}][method]" rows="3" class="form-control"
                                        placeholder="Detailed steps for this set...">{{ $stage['method'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <button type="button" id="addStageBtn" onclick="return addNewSet();" class="btn btn-secondary w-full py-4 border-2 border-dashed border-subtle">
                <i data-lucide="plus" class="text-accent"></i>
                Add Another Set
            </button>

            <!-- Sub-Recipes Used Section -->
            <div class="card space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="flex items-center gap-2 text-primary">
                        <i data-lucide="component" class="text-accent"></i>
                        Sub-Recipes Used
                    </h2>
                    <button type="button" onclick="openSubRecipeModal(); return false;" class="btn btn-secondary text-xs px-4 py-2">
                        <i data-lucide="plus"></i> Add Sub-Recipe
                    </button>
                </div>

                <div id="sub-recipes-container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div id="no-sub-recipes-msg" class="col-span-full py-8 text-center bg-white/5 border border-dashed border-subtle text-muted rounded-xl">
                        No sub-recipes added yet.
                    </div>
                </div>
            </div>

            @if(auth()->user()->isAdmin())
                <div class="card bg-navy-primary border-brass/30 p-8 shadow-2xl">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-[10px] font-bold text-muted uppercase tracking-widest mb-1">Total Estimated Cost</p>
                            <div class="text-4xl font-black text-accent tracking-tighter">
                                <span class="text-xl text-brass/60 mr-1">₹</span>
                                <span id="totalCostDisplay">0.00</span>
                            </div>
                        </div>
                        <i data-lucide="trending-up" class="w-12 h-12 text-brass/20"></i>
                    </div>
                </div>
            @endif
        </div>
    </form>

    <!-- Stage Template -->
    <template id="stageTemplate">
        <div class="stage-block card p-0 overflow-hidden mb-6">
            <div class="bg-white/5 border-b border-subtle p-4 flex justify-between items-center">
                <div class="flex items-center gap-3 flex-1">
                    <i data-lucide="grip-vertical" class="text-muted cursor-move"></i>
                    <input type="text" name="stages[STAGE_INDEX][name]" value="SET_NUMBER_PLACEHOLDER"
                        class="bg-transparent border-none text-lg font-bold text-primary focus:ring-0 placeholder-white/20 w-full"
                        placeholder="Set Name (e.g. Sauce Prep)">
                </div>
                <button type="button" onclick="removeStage(this)" class="text-muted hover:text-red-500 transition-colors">
                    <i data-lucide="trash-2"></i>
                </button>
            </div>

            <div class="p-6 space-y-6">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="w-[50%]">Item</th>
                                <th class="w-[20%] text-center">Qty</th>
                                <th class="w-[20%]">Unit</th>
                                @if(auth()->user()->isAdmin())
                                    <th class="w-[10%] text-right">Cost</th>
                                @endif
                                <th class="w-[5%]"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-subtle stage-ingredients-body">
                        </tbody>
                    </table>
                    <div class="flex border-t border-subtle bg-white/5">
                        <button type="button" onclick="addIngredientRow(this)" class="btn-ghost flex-1 py-4 text-accent font-bold hover:bg-white/5 flex items-center justify-center gap-2">
                            <i data-lucide="plus-circle"></i> Add Ingredient
                        </button>
                        <button type="button" onclick="openIngredientModal('')" class="btn-ghost py-4 px-6 text-muted border-l border-subtle hover:text-accent flex items-center gap-2">
                            <i data-lucide="plus-square"></i> New
                        </button>
                    </div>
                </div>

                <div>
                    <label class="form-label mb-2">Instructions</label>
                    <textarea name="stages[STAGE_INDEX][method]" rows="3" class="form-control" placeholder="Detailed steps for this stage..."></textarea>
                </div>
            </div>
        </div>
    </template>

    <!-- Ingredient Row Template -->
    <template id="ingredientRowTemplate">
        <tr class="hover:bg-white/5 transition-colors ingredient-row">
            <td class="p-2">
                <select class="ingredient-select w-full" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][ingredient_id]" required>
                    <option value="">Select Item...</option>
                </select>
            </td>
            <td class="p-2">
                <input type="number" step="any" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][quantity]" required
                    class="quantity-input form-control text-center font-bold" placeholder="0">
            </td>
            <td class="p-2">
                <input type="hidden" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][unit]" value="" class="unit-value-input">
                <input type="text" readonly value="" class="unit-display form-control bg-primary/20 cursor-not-allowed" placeholder="Select item first">
            </td>
            <input type="hidden" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][ingredient_group]" value="">
            @if(auth()->user()->isAdmin())
                <td class="p-2 text-right font-bold text-accent cost-display">0.00</td>
            @endif
            <td class="p-2 text-center">
                <button type="button" onclick="removeRow(this)" class="text-muted hover:text-red-500 transition-colors">
                    <i data-lucide="x"></i>
                </button>
            </td>
        </tr>
    </template>

    <!-- Quick Category Modal -->
    <div id="createCategoryModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 bg-black/70">
        <div class="card w-full max-w-md shadow-2xl p-0 overflow-hidden">
            <div class="p-6 border-b border-subtle flex justify-between items-center">
                <h3 class="flex items-center gap-2">
                    <i data-lucide="folder-plus" class="text-accent"></i>
                    New Category
                </h3>
                <button type="button" onclick="closeCategoryModal()" class="text-muted hover:text-accent transition-colors">
                    <i data-lucide="x"></i>
                </button>
            </div>
            
            <div class="p-8">
                <form id="quickCategoryForm" onsubmit="event.preventDefault(); submitQuickCategory();" class="space-y-6">
                    <div>
                        <label class="form-label">Category Name</label>
                        <input type="text" id="quick_category_name" name="name" required class="form-control" placeholder="e.g. Desserts">
                        <input type="hidden" name="type" value="recipe">
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="button" onclick="closeCategoryModal()" class="btn btn-secondary flex-1">Cancel</button>
                        <button type="submit" class="btn btn-primary flex-1">Create</button>
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
                        <label class="form-label mb-3 flex items-center gap-2">
                            <i data-lucide="hash" class="text-accent"></i>
                            Quantity
                        </label>
                        <input type="number" id="sub-recipe-qty" step="any" min="0.001" value="1" class="form-control text-center text-xl font-bold">
                    </div>
                    <div>
                        <label class="form-label mb-3 flex items-center gap-2">
                            <i data-lucide="ruler" class="text-accent"></i>
                            Unit
                        </label>
                        <input type="text" id="sub-recipe-unit-display" readonly placeholder="Auto" class="form-control bg-primary/20 text-accent text-center text-xl font-bold cursor-not-allowed">
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white/5 border-t border-subtle flex gap-4">
                <button type="button" onclick="closeSubRecipeModal()" class="btn btn-secondary flex-1">Cancel</button>
                <button type="button" onclick="confirmAddSubRecipe()" class="btn btn-primary flex-1" id="addSubRecipeBtn" {{ count($subRecipes) === 0 ? 'disabled' : '' }}>
                    Add to Recipe
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Create Ingredient Modal -->
    <div id="createIngredientModal" class="fixed inset-0 z-[70] hidden flex items-center justify-center p-4 bg-black/70">
        <div class="card w-full max-w-2xl p-0 overflow-hidden shadow-2xl">
            <div class="p-6 border-b border-subtle flex justify-between items-center bg-white/5">
                <div>
                    <h3 class="flex items-center gap-2">
                        <i data-lucide="plus-square" class="text-accent"></i>
                        Request New Ingredient
                    </h3>
                    @if(!auth()->user()->isAdmin())
                        <p class="text-[10px] text-muted mt-1 uppercase font-bold tracking-widest">Requires Admin Approval</p>
                    @endif
                </div>
                <button type="button" onclick="closeIngredientModal()" class="text-muted hover:text-accent transition-colors">
                    <i data-lucide="x"></i>
                </button>
            </div>
            
            <div class="p-8 max-h-[75vh] overflow-y-auto custom-scrollbar">
                <form id="quickIngredientForm" class="space-y-8">
                    <!-- Section 1: Basic Info -->
                    <div class="space-y-4">
                        <h4 class="text-[10px] font-bold text-muted uppercase tracking-widest flex items-center gap-2">
                            <i data-lucide="info" class="w-3.5 h-3.5"></i> Basic Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="col-span-full">
                                <label class="form-label">Ingredient Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="quick_name" required
                                    class="form-control"
                                    placeholder="e.g. Extra Virgin Olive Oil">
                            </div>
                            <div>
                                <label class="form-label">Category <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select name="category_id" id="quick_category_select" required class="form-control">
                                        <option value="">Select Category...</option>
                                        @foreach($ingredientCategories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" onclick="openCategoryModal('', (res) => { if(res){ const s = document.getElementById('quick_category_select'); const o = new Option(res.name, res.id, true, true); s.add(o); } })"
                                        class="absolute right-8 top-1/2 -translate-y-1/2 p-1.5 text-accent hover:bg-white/5 rounded transition-colors" title="Add New Category">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Usage Unit <span class="text-red-500">*</span></label>
                                <select name="measurement_unit" required class="form-control">
                                    <option value="">Select Unit...</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->value }}">{{ $unit->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Purchase Details -->
                    <div class="space-y-4 p-6 bg-white/5 rounded-2xl border border-subtle">
                        <h4 class="text-[10px] font-bold text-accent uppercase tracking-widest flex items-center gap-2">
                            <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i> Purchase Metrics
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div>
                                <label class="text-[10px] font-bold text-muted uppercase tracking-widest mb-1.5 block">Purchase Qty</label>
                                <input type="number" name="purchase_quantity" step="0.001" min="0" value="1"
                                    class="form-control text-center font-bold">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-muted uppercase tracking-widest mb-1.5 block">Purchase Unit</label>
                                <select name="purchase_unit" class="form-control font-bold">
                                    <option value="">Select...</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->value }}">{{ $unit->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-full md:col-span-1">
                                <label class="text-[10px] font-bold text-muted uppercase tracking-widest mb-1.5 block">Price (₹)</label>
                                <input type="number" name="purchase_price" step="0.01" min="0" placeholder="0.00"
                                    class="form-control font-bold">
                            </div>
                            <div class="col-span-full">
                                <label class="text-[10px] font-bold text-muted uppercase tracking-widest mb-1.5 block">Primary Vendor</label>
                                <input type="text" name="vendor" placeholder="e.g. Local Market" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Alerts & Storage -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="form-label flex items-center gap-2">
                                <i data-lucide="map-pin" class="text-accent opacity-50"></i> Storage
                            </label>
                            <select name="storage_location" required class="form-control">
                                <option value="Fridge">Fridge</option>
                                <option value="Freezer">Freezer</option>
                                <option value="Dry Store" selected>Dry Store</option>
                                <option value="Bar">Bar</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label flex items-center gap-2">
                                <i data-lucide="bell" class="text-accent opacity-50"></i> Alert At
                            </label>
                            <input type="number" name="alert_threshold" step="0.01" min="0" value="0" class="form-control text-center font-bold">
                        </div>
                    </div>

                    <!-- Section 4: Allergens -->
                    <div class="space-y-3">
                        <label class="text-[10px] font-bold text-muted uppercase tracking-widest flex items-center gap-2">
                            <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i> Allergen Safety
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach(App\Enums\Allergen::cases() as $allergen)
                                <label class="flex items-center gap-2.5 p-3 rounded-xl bg-white/5 border border-subtle transition-all cursor-pointer group hover:border-red-500/50">
                                    <input type="checkbox" name="allergen_tags[]" value="{{ $allergen->value }}" class="accent-brass w-4 h-4">
                                    <span class="text-[10px] font-bold text-muted group-hover:text-primary uppercase tracking-widest">{{ $allergen->label() }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" onclick="closeIngredientModal()" class="btn btn-secondary flex-1">Cancel</button>
                        <button type="button" onclick="submitQuickIngredient()" class="btn btn-primary flex-[2]">
                            <i data-lucide="send"></i> Submit Request
                        </button>
                    </div>
                </form>
                <!-- Success message shown after submit -->
                <div id="ingredientSubmitSuccess" class="hidden py-12 px-6 text-center">
                    <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="check-circle-2" class="w-10 h-10 text-green-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-primary mb-2 tracking-tight">Request Processed!</div>
                    <p class="text-muted text-sm mb-8 max-w-sm mx-auto" id="ingredientSubmitMsg"></p>
                    <button onclick="closeIngredientModal()" class="btn btn-primary w-full">Return to Recipe</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <style>
        .ts-dropdown { z-index: 99999 !important; background: var(--navy-primary) !important; border: 1px solid var(--color-border) !important; border-radius: 0.75rem !important; box-shadow: 0 20px 40px rgba(0,0,0,0.4) !important; }
        .ts-dropdown .option.active { background-color: var(--brass) !important; color: white !important; }
        .ts-dropdown .option { color: var(--ivory) !important; border-bottom: 1px solid rgba(242,237,230,0.05) !important; }
        .ts-control { background: transparent !important; border: none !important; color: var(--ivory) !important; padding: 0 !important; }
        .ts-wrapper.multi .ts-control > div { background: var(--brass) !important; color: white !important; border-radius: 4px !important; }
        body > .ts-dropdown { opacity: 1 !important; visibility: visible !important; display: block; }
    </style>

    <script>
        @php
            $realStageCount = collect(old('stages', []))->filter(fn($s, $k) => $k != 999 && ($s['name'] ?? '') !== 'Sub-Recipes')->count();
        @endphp
        let stageCount = {{ $realStageCount }};
        let ingredientOptionsHTML = '';

        window.addedSubRecipes = [];

        let lastGramsPerPortion = 0;

        function updateYieldCalcs(trigger) {
            const portionsInput = document.getElementById('yield_portions');
            const weightInput = document.getElementById('yield_weight_grams');
            const display = document.getElementById('gramsPerPortionDisplay');

            let portions = parseFloat(portionsInput.value) || 0;
            let weight = parseFloat(weightInput.value) || 0;

            // Reciprocal logic: If we have a multiplier, use it.
            // Otherwise, if both provided, calculate the multiplier.
            if (trigger === 'portions' && lastGramsPerPortion > 0) {
                weight = (portions * lastGramsPerPortion).toFixed(2);
                weightInput.value = weight;
            } else if (trigger === 'weight' && lastGramsPerPortion > 0) {
                portions = Math.round(weight / lastGramsPerPortion);
                portionsInput.value = portions;
            }

            // Update the anchor multiplier if both are positive
            if (portions > 0 && weight > 0) {
                lastGramsPerPortion = weight / portions;
                display.textContent = lastGramsPerPortion.toFixed(2) + 'g';
            }

            if (typeof updateScaling === 'function') updateScaling();
        }

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

        // Set initial toggle state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const currentType = document.querySelector('input[name="recipe_type_select"]:checked')?.value || 'main';
            updateRecipeType(currentType);
        });

        document.addEventListener('DOMContentLoaded', () => {
            // Initialize Tom Select for searchable dropdowns
            if (typeof TomSelect !== 'undefined') {
                // Initialize Category Select
                const catSelect = document.getElementById('category-select');
                if (catSelect) {
                    new TomSelect(catSelect, {
                        create: false,
                        placeholder: "Select Category...",
                        allowEmptyOption: true
                    });
                }

                // Initialize Sub-Recipe Selector in Modal
                const subSelectorEl = document.getElementById('sub-recipe-selector');
                if (subSelectorEl) {
                    window.subRecipeSelector = new TomSelect(subSelectorEl, {
                        create: false,
                        placeholder: "-- Search Sub-Recipe --",
                        allowEmptyOption: true,
                        maxOptions: null
                    });
                }
            }

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
                } else {
                    costDisplay.textContent = '0.00';
                }
                calculateTotal();
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
            window.addedSubRecipes.forEach(s => {
                total += parseFloat(s.cost || 0);
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
        
        async function confirmAddSubRecipe() {
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
                ingId: originalOpt?.dataset?.ingId || opt?.ingId || val,
                name: originalOpt?.dataset?.name || opt?.name || opt?.text || 'Unknown', 
                qty: qty, 
                unit: originalOpt?.dataset?.unit || opt?.unit || 'pcs', 
                price: 0 // Will be updated by FIFO fetch
            };

            // Fetch FIFO cost for the sub-recipe (which is an ingredient)
            try {
                const kitchenSlug = '{{ request()->route("kitchen_slug") }}';
                const url = `/k/${kitchenSlug}/ingredients/${subData.ingId}/fifo-cost?quantity=${qty}&unit=${subData.unit}`;
                const response = await fetch(url);
                const data = await response.json();
                subData.cost = data.cost !== undefined ? parseFloat(data.cost) : 0;
            } catch (e) {
                console.error("FIFO sub-recipe cost fetch failed", e);
                subData.cost = 0;
            }

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
                const cost = parseFloat(s.cost || 0).toFixed(2);
                const card = document.createElement('div');
                card.className = 'sub-recipe-card bg-white/5 border border-subtle rounded-xl p-4 flex justify-between items-center';
                card.innerHTML = `<div class="flex items-center gap-3">
                    <div class="p-2 bg-primary/40 rounded-lg text-accent">
                        <i data-lucide="component" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <input type="hidden" name="stages[999][ingredients][${i}][ingredient_id]" value="${s.ingId}">
                        <input type="hidden" name="stages[999][ingredients][${i}][quantity]" value="${s.qty}">
                        <input type="hidden" name="stages[999][ingredients][${i}][unit]" value="${s.unit}">
                        <input type="hidden" name="stages[999][ingredients][${i}][ingredient_group]" value="Sub-Recipe">
                        <h4 class="font-bold text-primary">${s.name}</h4>
                        <p class="text-[10px] text-muted uppercase font-bold tracking-widest">${s.qty} ${s.unit} • <span class="text-accent">₹${cost}</span></p>
                    </div>
                </div>
                <button type="button" onclick="window.addedSubRecipes.splice(${i},1);renderSubRecipeCards();calculateTotal();" class="text-muted hover:text-red-500 transition-all p-2">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
                <span class="cost-val hidden">${cost}</span>`;
                container.appendChild(card);
            });
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    </script>
@endpush
