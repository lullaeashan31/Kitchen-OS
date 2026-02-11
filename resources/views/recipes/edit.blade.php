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

                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Yields (Fill at least
                            one)</label>
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Row 1 -->
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Portions</label>
                                <input type="number" name="yield_portions"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none font-bold text-gray-800"
                                    min="1" step="0.1" value="{{ old('yield_portions', $recipe->yield_portions) }}"
                                    placeholder="e.g. 10">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Batches</label>
                                <input type="number" name="yield_batches"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none"
                                    min="1" step="1" value="{{ old('yield_batches', $recipe->yield_batches) }}"
                                    placeholder="e.g. 1">
                            </div>

                            <!-- Row 2 -->
                            <div class="col-span-2 grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Weight</label>
                                    <div class="flex gap-1">
                                        <input type="number" name="yield_weight"
                                            class="w-2/3 px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none"
                                            step="0.01" min="0" placeholder="0.00"
                                            value="{{ old('yield_weight', $recipe->yield_weight) }}">
                                        <select name="yield_weight_unit"
                                            class="w-1/3 px-2 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none bg-white text-xs">
                                            <option value="g" {{ (old('yield_weight_unit', $recipe->yield_weight_unit) == 'g') ? 'selected' : '' }}>g</option>
                                            <option value="kg" {{ (old('yield_weight_unit', $recipe->yield_weight_unit) == 'kg') ? 'selected' : '' }}>kg</option>
                                            <option value="oz" {{ (old('yield_weight_unit', $recipe->yield_weight_unit) == 'oz') ? 'selected' : '' }}>oz</option>
                                            <option value="lb" {{ (old('yield_weight_unit', $recipe->yield_weight_unit) == 'lb') ? 'selected' : '' }}>lb</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Volume</label>
                                    <div class="flex gap-1">
                                        <input type="number" name="yield_volume"
                                            class="w-2/3 px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none"
                                            step="0.01" min="0" placeholder="0.00"
                                            value="{{ old('yield_volume', $recipe->yield_volume) }}">
                                        <select name="yield_volume_unit"
                                            class="w-1/3 px-2 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none bg-white text-xs">
                                            <option value="ml" {{ (old('yield_volume_unit', $recipe->yield_volume_unit) == 'ml') ? 'selected' : '' }}>ml</option>
                                            <option value="l" {{ (old('yield_volume_unit', $recipe->yield_volume_unit) == 'l') ? 'selected' : '' }}>l</option>
                                            <option value="fl_oz" {{ (old('yield_volume_unit', $recipe->yield_volume_unit) == 'fl_oz') ? 'selected' : '' }}>fl oz</option>
                                            <option value="cup" {{ (old('yield_volume_unit', $recipe->yield_volume_unit) == 'cup') ? 'selected' : '' }}>cup</option>
                                        </select>
                                    </div>
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
        <div class="w-full lg:w-2/3">
            <!-- Recipe Stages Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="layers" class="w-5 h-5 text-blue-500"></i>
                        Recipe Stages
                    </h2>
                    <button type="button" onclick="addStage()"
                        class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 font-medium transition-colors flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Add Stage
                    </button>
                </div>

                <div id="stages-container" class="space-y-8">
                    @foreach($recipe->stages as $index => $stage)
                        <div class="stage-block border border-gray-200 rounded-xl p-6 bg-gray-50/50 relative group transition-all hover:border-blue-200 hover:shadow-sm"
                            data-stage-index="{{ $index }}">

                            <input type="hidden" name="stages[{{ $index }}][id]" value="{{ $stage->id }}">

                            <button type="button" onclick="removeStage(this)"
                                class="absolute top-4 right-4 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity"
                                title="Remove Stage">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div class="col-span-1">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Stage Name</label>
                                    <input type="text" name="stages[{{ $index }}][name]" value="{{ $stage->name }}" required
                                        class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition-colors"
                                        placeholder="e.g., Marination, Sauce, Assembly">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Method & Instructions</label>
                                    <textarea name="stages[{{ $index }}][method]" rows="3"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition-colors resize-y"
                                        placeholder="Describe the steps for this stage...">{{ $stage->method }}</textarea>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-gray-50 border-b border-gray-200">
                                            <tr>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[35%]">
                                                    Ingredient</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[15%]">
                                                    Quantity</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[15%]">
                                                    Unit</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[15%]">
                                                    Group (Opt)</th>
                                                <th
                                                    class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-[15%]">
                                                    Cost</th>
                                                <th
                                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-[5%]">
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 stage-ingredients-body">
                                            @foreach($stage->ingredients as $rIngredient)
                                                <tr class="group hover:bg-blue-50/30 transition-colors ingredient-row">
                                                    <td class="px-4 py-3">
                                                        <select class="ingredient-select w-full"
                                                            name="stages[{{ $index }}][ingredients][{{ $loop->index }}][ingredient_id]"
                                                            required>
                                                            <option value="{{ $rIngredient->ingredient_id }}" selected
                                                                data-price="{{ $rIngredient->ingredient->latest_price ?? $rIngredient->ingredient->price }}"
                                                                data-unit="{{ $rIngredient->ingredient->measurement_unit }}">
                                                                {{ $rIngredient->ingredient->name }}
                                                                ({{ $rIngredient->ingredient->measurement_unit }})
                                                            </option>
                                                            <!-- Other options injected via JS or fallback -->
                                                        </select>
                                                        <input type="hidden"
                                                            name="stages[{{ $index }}][ingredients][{{ $loop->index }}][name]"
                                                            class="ingredient-name-hidden"
                                                            value="{{ $rIngredient->ingredient->name }}">
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <input type="number" step="any"
                                                            name="stages[{{ $index }}][ingredients][{{ $loop->index }}][quantity]"
                                                            value="{{ $rIngredient->quantity }}" required
                                                            class="quantity-input w-full px-2 py-1.5 rounded border border-gray-200 focus:border-blue-500 outline-none">
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <select name="stages[{{ $index }}][ingredients][{{ $loop->index }}][unit]"
                                                            required
                                                            class="unit-select w-full px-2 py-1.5 rounded border border-gray-200 focus:border-blue-500 outline-none bg-white">
                                                            @foreach(\App\Enums\Unit::cases() as $unit)
                                                                <option value="{{ $unit->value }}" {{ $rIngredient->unit == $unit->value ? 'selected' : '' }}>
                                                                    {{ $unit->label() }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <input type="text"
                                                            name="stages[{{ $index }}][ingredients][{{ $loop->index }}][ingredient_group]"
                                                            value="{{ $rIngredient->ingredient_group }}"
                                                            class="w-full px-2 py-1.5 rounded border border-gray-200 focus:border-blue-500 outline-none"
                                                            placeholder="e.g. For Sauce">
                                                    </td>
                                                    <td class="px-4 py-3 text-right font-medium text-gray-700 cost-display">
                                                        {{ number_format($rIngredient->cost, 2) }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
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
                title="Remove Stage">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
            </button>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="col-span-1">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Stage Name</label>
                    <input type="text" name="stages[STAGE_INDEX][name]" value="Main" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition-colors"
                        placeholder="e.g., Marination, Sauce, Assembly">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Method & Instructions</label>
                    <textarea name="stages[STAGE_INDEX][method]" rows="3"
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition-colors resize-y"
                        placeholder="Describe the steps for this stage..."></textarea>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[35%]">
                                    Ingredient</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[15%]">
                                    Quantity</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[15%]">
                                    Unit</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-[15%]">
                                    Group (Opt)</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-[15%]">
                                    Cost</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-[5%]">
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 stage-ingredients-body">
                            <!-- Ingredient Rows -->
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
    </template>

    <template id="ingredientRowTemplate">
        <tr class="group hover:bg-blue-50/30 transition-colors ingredient-row">
            <td class="px-4 py-3">
                <select class="ingredient-select w-full" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][ingredient_id]"
                    required>
                    <option value="">Select Ingredient...</option>
                </select>
                <input type="hidden" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][name]"
                    class="ingredient-name-hidden">
            </td>
            <td class="px-4 py-3">
                <input type="number" step="any" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][quantity]" required
                    class="quantity-input w-full px-2 py-1.5 rounded border border-gray-200 focus:border-blue-500 outline-none">
            </td>
            <td class="px-4 py-3">
                <select name="stages[STAGE_INDEX][ingredients][ROW_INDEX][unit]" required
                    class="unit-select w-full px-2 py-1.5 rounded border border-gray-200 focus:border-blue-500 outline-none bg-white">
                    @foreach(\App\Enums\Unit::cases() as $unit)
                        <option value="{{ $unit->value }}">{{ $unit->label() }}</option>
                    @endforeach
                </select>
            </td>
            <td class="px-4 py-3">
                <input type="text" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][ingredient_group]"
                    class="w-full px-2 py-1.5 rounded border border-gray-200 focus:border-blue-500 outline-none"
                    placeholder="e.g. For Sauce">
            </td>
            <td class="px-4 py-3 text-right font-medium text-gray-700 cost-display">
                0.00
            </td>
            <td class="px-4 py-3 text-center">
                <button type="button" onclick="removeRow(this)"
                    class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50">
                    <i data-lucide="x" class="w-4 h-4"></i>
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
        let stageCount = {{ $recipe->stages->count() }};
        let activeSelect = null; // Track which select triggered the modal
        const ingredientOptionsHTML = document.getElementById('ingredientOptions').innerHTML;

        document.addEventListener('DOMContentLoaded', () => {
            // Initialize TomSelect on existing rows
            document.querySelectorAll('.ingredient-row').forEach(row => {
                const select = row.querySelector('.ingredient-select');
                if (select) {
                    const currentValue = select.value;
                    // Reset options to include all (hidden list) + selected
                    // Note: In Edit mode, the server-rendered option is just the selected one.
                    // We inject the full list then re-select.

                    // However, to avoid value loss, we save the selected value/data first.
                    const selectedOpt = select.querySelector('option[selected]');

                    // We simply append the other options or replace HTML if needed.
                    // For simplicity, let's just make sure the full list is available.

                    // Better approach:
                    // 1. Keep the selected option.
                    // 2. Append the rest from ingredientOptionsHTML (which excludes the selected one ideally, or duplication is handled by browser/TomSelect).
                    // Actually, simple string injection works because duplicate values are usually handled or we can filter.
                    // But to be clean:
                    select.innerHTML = ingredientOptionsHTML;
                    select.value = currentValue; // Restore selection

                    initTomSelect(select, row);

                    // Init listeners for existing inputs
                    const qtyInput = row.querySelector('.quantity-input');
                    const unitSelect = row.querySelector('.unit-select');
                    if (qtyInput) qtyInput.addEventListener('input', () => calculateRowCost(row));
                    if (unitSelect) unitSelect.addEventListener('change', () => calculateRowCost(row));
                }
            });

            calculateTotal();
            setTimeout(updateIngredientAvailability, 500);
        });

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

            const inputs = tr.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.name) {
                    input.name = input.name.replace('STAGE_INDEX', stageIndex).replace('ROW_INDEX', rowIndex);
                }
            });

            // Populate Select
            const select = tr.querySelector('.ingredient-select');
            select.innerHTML += ingredientOptionsHTML;

            tbody.appendChild(tr);

            initTomSelect(select, tr);

            // Listeners
            const qtyInput = tr.querySelector('.quantity-input');
            const unitSelect = tr.querySelector('.unit-select');
            qtyInput.addEventListener('input', () => calculateRowCost(tr));
            unitSelect.addEventListener('change', () => calculateRowCost(tr));

            lucide.createIcons();
        }

        function initTomSelect(select, row) {
            new TomSelect(select, {
                create: true,
                sortField: { field: "text", direction: "asc" },
                placeholder: 'Search ingredient...',
                plugins: ['dropdown_input'],
                render: {
                    option_create: function (data, escape) {
                        return '<div class="create">Add <strong>' + escape(data.input) + '</strong>...</div>';
                    },
                    no_results: function (data, escape) {
                        return '<div class="no-results">No results found for "' + escape(data.input) + '"</div>';
                    }
                },
                create: function (input) {
                    activeSelect = select;
                    openIngredientModal(input);
                    return false;
                },
                onChange: function (value) {
                    calculateRowCost(row);
                    updateIngredientAvailability();
                },
                onInitialize: function () {
                    // small check
                }
            });
        }

        function removeRow(btn) {
            btn.closest('tr').remove();
            calculateTotal();
            updateIngredientAvailability();
        }

        const UNIT_FACTORS = {
            'g': 1,
            'kg': 1000,
            'ml': 1,
            'l': 1000,
            'tbsp': 15,
            'tsp': 5,
            'cup': 240,
            'pcs': 1
        };

        function calculateRowCost(row) {
            const select = row.querySelector('.ingredient-select');
            const qtyInput = row.querySelector('.quantity-input');
            const unitSelect = row.querySelector('.unit-select');
            const costDisplay = row.querySelector('.cost-display');

            if (!costDisplay) return; // Not admin

            // Remove any existing formula tooltip or info
            const existingInfo = row.querySelector('.cost-formula');
            if(existingInfo) existingInfo.remove();

            if (!select.value || !qtyInput.value || !unitSelect.value) {
                costDisplay.textContent = '0.00';
                calculateTotal();
                return;
            }

            const selectedOption =  Array.from(select.options).find(opt => opt.value === select.value);
            
            if(!selectedOption) {
                costDisplay.textContent = '0.00';
                calculateTotal();
                return;
            }

            const price = parseFloat(selectedOption.dataset.price || 0);
            const inventoryUnit = selectedOption.dataset.unit; 
            const quantity = parseFloat(qtyInput.value);
            const recipeUnit = unitSelect.value;

            let cost = 0;

            const inventoryFactor = UNIT_FACTORS[inventoryUnit] || 1;
            const recipeFactor = UNIT_FACTORS[recipeUnit] || 1;
            
            if (inventoryUnit === 'pcs' || recipeUnit === 'pcs') {
                if (inventoryUnit === recipeUnit) {
                    cost = price * quantity;
                } else {
                     cost = 0;
                }
            } else {
                 const pricePerBase = price / inventoryFactor;
                 const qtyInBase = quantity * recipeFactor;
                 cost = pricePerBase * qtyInBase;
            }

            costDisplay.textContent = cost.toFixed(2);
             // Add Formula Tooltip/Text
            if (cost > 0) {
                 const info = document.createElement('div');
                 info.className = 'cost-formula text-xs text-gray-400 mt-1';
                 info.style.fontSize = '0.7rem';
                 info.textContent = `${quantity} ${recipeUnit} @ ${price}/${inventoryUnit}`;
                 costDisplay.appendChild(info);
            }
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.cost-display').forEach(el => {
                total += parseFloat(el.textContent || 0);
            });
            // No total display in this UI design currently or it's remove?
            // If there is one, update it.
            const totalEl = document.getElementById('totalCostDisplay'); // if exists
            if (totalEl) totalEl.textContent = total.toFixed(2);
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
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

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
                        document.querySelectorAll('.ingredient-select').forEach(select => {
                            if (select.tomselect) {
                                select.tomselect.addOption({
                                    value: result.ingredient.id,
                                    text: result.ingredient.name + ' (' + result.ingredient.unit + ')',
                                    price: result.ingredient.price,
                                    unit: result.ingredient.unit
                                });
                            }
                        });
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