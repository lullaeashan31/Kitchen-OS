@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-3">
        <a href="{{ route('recipes.show', $recipe) }}"
            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
            <i data-lucide="arrow-left" class="w-6 h-6"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Recipe</h1>
            <p class="text-sm text-gray-500">Refining {{ $recipe->name }}</p>
        </div>
    </div>
@endsection

@section('content')
    <form action="{{ route('recipes.update', $recipe) }}" method="POST" id="recipeForm"
        class="flex flex-col lg:flex-row gap-6">
        @csrf
        @method('PUT')

        <!-- Left Column: Primary Details -->
        <div class="w-full lg:w-1/3 flex flex-col gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="info" class="w-5 h-5 text-blue-500"></i> Basic Info
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Recipe Name</label>
                        <input type="text" name="name"
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                            required value="{{ old('name', $recipe->name) }}">
                    </div>

                    <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Portions</label>
                            <input type="number" name="yield_portions"
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none font-bold text-gray-800"
                                required min="1" step="0.1"
                                value="{{ old('yield_portions', $recipe->yield_portions ?? $recipe->yields) }}">
                            <input type="hidden" name="yields" value="{{ $recipe->yields }}">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Prep Time (Mins)</label>
                            <input type="number" name="prep_time_minutes"
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none"
                                min="0" placeholder="e.g. 45"
                                value="{{ old('prep_time_minutes', $recipe->prep_time_minutes) }}">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Total Weight</label>
                            <input type="number" name="yield_weight"
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none"
                                step="0.01" min="0" placeholder="0.00"
                                value="{{ old('yield_weight', $recipe->yield_weight) }}">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Weight Unit</label>
                            <select name="yield_weight_unit"
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none bg-white">
                                <option value="g" {{ (old('yield_weight_unit', $recipe->yield_weight_unit) == 'g') ? 'selected' : '' }}>G (Grams)</option>
                                <option value="kg" {{ (old('yield_weight_unit', $recipe->yield_weight_unit) == 'kg') ? 'selected' : '' }}>KG (Kilograms)</option>
                                <option value="ml" {{ (old('yield_weight_unit', $recipe->yield_weight_unit) == 'ml') ? 'selected' : '' }}>ML (Milliliters)</option>
                                <option value="l" {{ (old('yield_weight_unit', $recipe->yield_weight_unit) == 'l') ? 'selected' : '' }}>L (Liters)</option>
                            </select>
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
        <div class="w-full lg:w-2/3">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="utensils" class="w-5 h-5 text-green-500"></i> Ingredients
                    </h2>
                    <button type="button"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
                        onclick="addIngredientRow()">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add Ingredient
                    </button>
                </div>

                <div class="overflow-visible">
                    <table class="w-full text-left" id="ingredientsTable">
                        <thead>
                            <tr class="text-xs font-bold text-gray-500 uppercase border-b border-gray-100">
                                <th class="pb-3 w-2/12 pl-2">Group</th>
                                <th class="pb-3 w-4/12">Ingredient</th>
                                <th class="pb-3 w-2/12">Quantity</th>
                                <th class="pb-3 w-2/12">Unit</th>
                                @if(auth()->user()->isAdmin())
                                    <th class="pb-3 w-1/12 text-right">Est. Cost</th>
                                @endif
                                <th class="pb-3 w-1/12"></th>
                            </tr>
                        </thead>
                        <tbody id="ingredientsBody" class="divide-y divide-gray-50">
                            @foreach($recipe->ingredients as $index => $ingredient)
                                <tr class="ingredient-row group">
                                    <td class="py-3 pr-2 align-top">
                                        <input type="text" name="ingredients[{{ $index }}][ingredient_group]"
                                            value="{{ $ingredient->pivot->ingredient_group }}" placeholder="Set..."
                                            class="w-full px-2 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none text-xs text-gray-600 bg-gray-50">
                                    </td>
                                    <td class="py-3 pr-2 align-top">
                                        <!-- Pre-populated Select -->
                                        <select name="ingredients[{{ $index }}][ingredient_id]" class="ingredient-select w-full"
                                            required>
                                            <option value="{{ $ingredient->id }}"
                                                data-price="{{ $ingredient->avg_cost > 0 ? $ingredient->avg_cost : $ingredient->price }}"
                                                selected>
                                                {{ $ingredient->name }}
                                            </option>
                                        </select>
                                    </td>
                                    <td class="py-3 px-2 align-top">
                                        <!-- Quantity -->
                                        <input type="number" name="ingredients[{{ $index }}][quantity]"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none quantity-input"
                                            step="0.001" min="0" required value="{{ $ingredient->pivot->quantity }}">
                                    </td>
                                    <td class="py-3 px-2 align-top">
                                        <!-- Unit -->
                                        <select name="ingredients[{{ $index }}][unit]"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none bg-white unit-select"
                                            required>
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->value }}" {{ $ingredient->pivot->unit === $unit->value ? 'selected' : '' }}>{{ $unit->label() }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    @if(auth()->user()->isAdmin())
                                        <td class="py-3 pl-2 align-top">
                                            <input type="text"
                                                class="w-full px-3 py-2 bg-transparent text-right font-mono text-gray-600 cost-input border-none focus:ring-0"
                                                readonly value="{{ $ingredient->pivot->cost ?? '0.00' }}">
                                        </td>
                                    @endif
                                    <td class="py-3 pl-2 align-top text-right">
                                        <button type="button" class="p-2 text-gray-400 hover:text-red-500 transition-colors"
                                            onclick="removeRow(this)">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(auth()->user()->isAdmin())
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg flex justify-between items-center border border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Total Estimated Cost</span>
                        <span class="text-xl font-bold text-gray-800">$<span id="totalCostDisplay">0.00</span></span>
                    </div>
                @endif
            </div>
        </div>
    </form>

    <!-- Hidden Ingredient Options for New Rows -->
    <div id="ingredientOptions" style="display: none;">
        @foreach(\App\Models\Ingredient::orderBy('name')->get() as $ing)
            <option value="{{ $ing->id }}" data-price="{{ $ing->avg_cost > 0 ? $ing->avg_cost : $ing->price }}"
                data-unit="{{ $ing->measurement_unit }}">
                {{ $ing->name }}
            </option>
        @endforeach
    </div>

    <!-- New Row Template -->
    <template id="ingredientRowTemplate">
        <tr class="ingredient-row group">
            <td class="py-3 pr-2 align-top">
                <input type="text" name="ingredients[INDEX][ingredient_group]" placeholder="Set..."
                    class="w-full px-2 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none text-xs text-gray-600 bg-gray-50">
            </td>
            <td class="py-3 pr-2 align-top">
                <select name="ingredients[INDEX][ingredient_id]" class="ingredient-select w-full" required
                    placeholder="Search ingredient...">
                    <option value="">Select ingredient...</option>
                </select>
            </td>
            <td class="py-3 px-2 align-top">
                <input type="number" name="ingredients[INDEX][quantity]"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none quantity-input"
                    step="0.001" min="0" required placeholder="0">
            </td>
            <td class="py-3 px-2 align-top">
                <select name="ingredients[INDEX][unit]"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none bg-white unit-select"
                    required>
                    @foreach($units as $unit)
                        <option value="{{ $unit->value }}">{{ $unit->label() }}</option>
                    @endforeach
                </select>
            </td>
            @if(auth()->user()->isAdmin())
                <td class="py-3 pl-2 align-top">
                    <input type="text"
                        class="w-full px-3 py-2 bg-transparent text-right font-mono text-gray-600 cost-input border-none focus:ring-0"
                        readonly value="0.00">
                </td>
            @endif
            <td class="py-3 pl-2 align-top text-right">
                <button type="button" class="p-2 text-gray-400 hover:text-red-500 transition-colors"
                    onclick="removeRow(this)">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        </tr>
    </template>
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
    </style>
    <script>
        let rowCount = {{ count($recipe->ingredients) }} + 1;

        const ingredientOptionsHTML = document.getElementById('ingredientOptions').innerHTML;

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.ingredient-row').forEach(row => {
                const select = row.querySelector('.ingredient-select');
                const currentValue = select.value;

                if (currentValue) {
                    select.innerHTML = ingredientOptionsHTML;
                    select.value = currentValue;
                }

                // Init TomSelect
                new TomSelect(select, {
                    create: true,
                    sortField: { field: "text", direction: "asc" },
                    placeholder: 'Search ingredient...',
                    plugins: ['dropdown_input'],
                    render: {
                        option_create: function (data, escape) {
                            return '<div class="create">Add <strong>' + escape(data.input) + '</strong>...</div>';
                        }
                    },
                    onChange: function (value) {
                        calculateRowCost(row);
                        updateIngredientAvailability();
                    },
                    onInitialize: function () {
                        setTimeout(() => updateIngredientAvailability(), 100);
                    }
                });

                // Init listeners
                const qtyInput = row.querySelector('.quantity-input');
                const unitSelect = row.querySelector('.unit-select');

                if (qtyInput) qtyInput.addEventListener('input', () => calculateRowCost(row));
                if (unitSelect) unitSelect.addEventListener('change', () => calculateRowCost(row));
            });

            calculateTotal();
            // Initial update of availability
            setTimeout(updateIngredientAvailability, 500); // Small delay to ensure all TomSelects are ready
        });

        function addIngredientRow() {
            const template = document.getElementById('ingredientRowTemplate');
            const tbody = document.getElementById('ingredientsBody');
            const clone = template.content.cloneNode(true);
            const tr = clone.querySelector('tr');

            // Replace INDEX
            const inputs = tr.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.name) input.name = input.name.replace('INDEX', rowCount);
            });

            // Populate Select
            const select = tr.querySelector('.ingredient-select');
            select.innerHTML += ingredientOptionsHTML;

            tbody.appendChild(tr);

            new TomSelect(select, {
                create: true,
                sortField: { field: "text", direction: "asc" },
                placeholder: 'Search ingredient...',
                plugins: ['dropdown_input'],
                render: {
                    option_create: function (data, escape) {
                        return '<div class="create">Add <strong>' + escape(data.input) + '</strong>...</div>';
                    }
                },
                onChange: function (value) {
                    calculateRowCost(tr);
                    updateIngredientAvailability();
                },
                onInitialize: function () {
                    setTimeout(() => updateIngredientAvailability(), 100);
                }
            });

            const qtyInput = tr.querySelector('.quantity-input');
            const unitSelect = tr.querySelector('.unit-select');
            qtyInput.addEventListener('input', () => calculateRowCost(tr));
            unitSelect.addEventListener('change', () => calculateRowCost(tr));

            rowCount++;
            lucide.createIcons();
        }

        function removeRow(btn) {
            btn.closest('tr').remove();
            calculateTotal();
            updateIngredientAvailability();
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
                    const opt = ts.options[optVal];

                    if (opt.disabled !== shouldDisable) {
                        ts.updateOption(optVal, { disabled: shouldDisable });
                    }
                });

                // CRITICAL: Clear cache to force re-render
                ts.clearCache();
                ts.refreshOptions(false);
            });
        }

        function calculateRowCost(row) {
            const select = row.querySelector('.ingredient-select');
            const qtyInput = row.querySelector('.quantity-input');
            const costInput = row.querySelector('.cost-input'); // Admin only check implied via existence

            if (!costInput) return;

            const selectedId = select.value;
            const quantity = parseFloat(qtyInput.value) || 0;

            if (!selectedId || quantity <= 0) {
                costInput.value = '0.00';
                calculateTotal();
                return;
            }

            // DOM query for TomSelect's underlying original option
            const option = select.querySelector(`option[value="${selectedId}"]`);
            if (option) {
                const price = parseFloat(option.dataset.price) || 0;
                const cost = price * quantity;
                costInput.value = cost.toFixed(2);
            }
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.cost-input').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            const display = document.getElementById('totalCostDisplay');
            if (display) display.innerText = total.toFixed(2);
        }
    </script>
@endpush