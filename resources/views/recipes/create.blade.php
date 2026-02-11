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
                        <label class="block text-sm font-bold text-gray-700 mb-2">Recipe Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none font-medium placeholder-gray-400"
                            placeholder="e.g. Truffle Mushroom Risotto">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="category_id" required
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none appearance-none font-medium text-gray-700 cursor-pointer">
                                <option value="" disabled selected>Select a category...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Yields Section -->
                    <div class="bg-gray-50/80 rounded-xl p-5 border border-gray-100 border-dashed">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Yield Configuration</label>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5 font-medium">Portions</label>
                                <input type="number" name="yield_portions" min="1" step="0.1" value="{{ old('yield_portions') }}"
                                    class="w-full px-3 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none transition-all text-center font-bold text-gray-800"
                                    placeholder="10">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5 font-medium">Batches</label>
                                <input type="number" name="yield_batches" min="1" step="1" value="{{ old('yield_batches') }}"
                                    class="w-full px-3 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none transition-all text-center font-bold text-gray-800"
                                    placeholder="1">
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5 font-medium">Net Weight</label>
                                <div class="flex rounded-lg shadow-sm">
                                    <input type="number" name="yield_weight" step="0.01" min="0" value="{{ old('yield_weight') }}"
                                        class="w-full px-3 py-2.5 rounded-l-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none transition-all border-r-0"
                                        placeholder="0.00">
                                    <select name="yield_weight_unit" class="px-3 py-2.5 bg-white border border-gray-200 rounded-r-lg text-sm font-medium text-gray-600 focus:border-blue-500 outline-none">
                                        <option value="g">g</option>
                                        <option value="kg">kg</option>
                                        <option value="lb">lb</option>
                                        <option value="oz">oz</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5 font-medium">Net Volume</label>
                                <div class="flex rounded-lg shadow-sm">
                                    <input type="number" name="yield_volume" step="0.01" min="0" value="{{ old('yield_volume') }}"
                                        class="w-full px-3 py-2.5 rounded-l-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none transition-all border-r-0"
                                        placeholder="0.00">
                                    <select name="yield_volume_unit" class="px-3 py-2.5 bg-white border border-gray-200 rounded-r-lg text-sm font-medium text-gray-600 focus:border-blue-500 outline-none">
                                        <option value="ml">ml</option>
                                        <option value="l">l</option>
                                        <option value="cup">cup</option>
                                    </select>
                                </div>
                            </div>
                        </div>
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
                            <div class="relative inline-block w-10 h-6 transition-colors duration-200 ease-in-out border-2 border-transparent rounded-full cursor-pointer bg-gray-200" id="subRecipeToggleBg">
                                <span class="translate-x-0 inline-block w-5 h-5 transition duration-200 ease-in-out transform bg-white rounded-full shadow pointer-events-none" id="subRecipeToggleDot"></span>
                            </div>
                        </div>

                        <div id="subRecipeFields" class="hidden space-y-4 pt-4 border-t border-indigo-100 mt-2">
                            <div>
                                <label class="block text-xs font-bold text-indigo-800 uppercase mb-1.5">Produces Ingredient</label>
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
                                    <label class="block text-xs font-bold text-indigo-800 uppercase mb-1.5">Output Qty</label>
                                    <input type="number" name="output_quantity" step="0.001" min="0" value="{{ old('output_quantity', 1) }}"
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

        <!-- RIGHT CONTENT: Stages & Methods -->
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

            <!-- Stages Container -->
            <div id="stages-container" class="space-y-6">
                <!-- Stages injected via JS -->
            </div>

            <!-- Add Stage Button -->
            <button type="button" onclick="addStage()"
                class="w-full py-4 border-2 border-dashed border-gray-300 rounded-2xl text-gray-500 font-bold hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50/50 transition-all flex items-center justify-center gap-2 group">
                <div class="p-1 bg-gray-200 rounded-full text-white group-hover:bg-blue-500 transition-colors">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                </div>
                Add Another Stage
            </button>

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
                    <input type="text" name="stages[STAGE_INDEX][name]" value="Stage 1" 
                        class="bg-transparent border-none text-lg font-bold text-gray-800 focus:ring-0 placeholder-gray-400 w-full"
                        placeholder="Stage Name (e.g. Sauce Prep)">
                </div>
                <button type="button" onclick="removeStage(this)" class="text-gray-400 hover:text-red-500 p-2 rounded-lg hover:bg-red-50 transition-colors">
                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                </button>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Ingredients Table -->
                <div class="rounded-xl border border-gray-100">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-500 font-semibold uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3 w-[40%]">Ingredient</th>
                                <th class="px-4 py-3 w-[15%]">Qty</th>
                                <th class="px-4 py-3 w-[15%]">Unit</th>
                                <th class="px-4 py-3 w-[20%]">Group/Note</th>
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
                <select class="ingredient-select w-full" 
                        name="stages[STAGE_INDEX][ingredients][ROW_INDEX][ingredient_id]" 
                        required>
                    <option value="">Search...</option>
                </select>
            </td>
            <td class="px-4 py-2">
                <input type="number" step="any" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][quantity]" required
                    class="quantity-input w-full px-2 py-1.5 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none text-center font-medium">
            </td>
            <td class="px-4 py-2">
                <select name="stages[STAGE_INDEX][ingredients][ROW_INDEX][unit]" required
                    class="unit-select w-full px-2 py-1.5 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none bg-white text-xs">
                    @foreach(\App\Enums\Unit::cases() as $unit)
                        <option value="{{ $unit->value }}">{{ $unit->label() }}</option>
                    @endforeach
                </select>
            </td>
            <td class="px-4 py-2">
                <input type="text" name="stages[STAGE_INDEX][ingredients][ROW_INDEX][ingredient_group]"
                    class="w-full px-2 py-1.5 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none text-xs"
                    placeholder="e.g. Sauce">
            </td>
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
            <option value="{{ $ing->id }}" 
                    data-price="{{ $ing->latest_price ?? $ing->price }}" 
                    data-unit="{{ $ing->measurement_unit }}">
                {{ $ing->name }} ({{ $ing->measurement_unit }})
            </option>
        @endforeach
    </div>

    <!-- Quick Create Ingredient Modal (Same as before but styled) -->
    <div id="createIngredientModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
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
                        <input type="text" name="name" id="quick_name" required class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none">
                    </div>
                    <!-- Reuse categories/units passed to view -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                             <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category</label>
                            <select name="category_id" required class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none bg-white">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                             <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Unit</label>
                             <select name="measurement_unit" required class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none bg-white">
                                @foreach($units as $unit)
                                    <option value="{{ $unit->value }}">{{ $unit->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                     <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Storage</label>
                        <select name="storage_location" required class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none bg-white">
                            <option value="Fridge">Fridge</option>
                            <option value="Freezer">Freezer</option>
                            <option value="Dry Store">Dry Store</option>
                            <option value="Bar">Bar</option>
                        </select>
                    </div>

                    <button type="button" onclick="submitQuickIngredient()" class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl shadow-lg mt-4 hover:bg-blue-700">Create Ingredient</button>
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
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
    </style>
    <script>
        // --- Core Application Logic ---
        let stageCount = 0;
        let activeSelect = null;
        const ingredientOptionsHTML = document.getElementById('ingredientOptions').innerHTML;
        const UNIT_FACTORS = { 'g': 1, 'kg': 1000, 'ml': 1, 'l': 1000, 'tbsp': 15, 'tsp': 5, 'cup': 240, 'pcs': 1, 'oz': 28.35, 'lb': 453.6 };

        document.addEventListener('DOMContentLoaded', () => {
            addStage(); // Initial stage
            calculateTotal();
            
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
                if(forceState === null) {
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
            if(stageCount > 1) {
                stageBlock.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        function removeStage(btn) {
            const container = document.getElementById('stages-container');
            if (container.children.length <= 1) {
                alert('You need at least one stage!');
                return;
            }
            if(confirm('Remove this stage?')) {
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

            // Populate select options
            const select = tr.querySelector('.ingredient-select');
            select.innerHTML += ingredientOptionsHTML;

            tbody.appendChild(tr);

            // Init TomSelect
            new TomSelect(select, {
                create: true,
                sortField: { field: "text", direction: "asc" },
                placeholder: 'Type to search...',
                plugins: ['dropdown_input'],
                render: {
                    option_create: (data, escape) => `<div class="create text-blue-600 p-2">Create <strong>${escape(data.input)}</strong>...</div>`
                },
                create: function(input) {
                    activeSelect = select;
                    openIngredientModal(input);
                    return false;
                },
                onChange: () => calculateRowCost(tr)
            });

            // Listeners for cost recalc
            tr.querySelector('.quantity-input').addEventListener('input', () => calculateRowCost(tr));
            tr.querySelector('.unit-select').addEventListener('change', () => calculateRowCost(tr));

            lucide.createIcons();
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

            if (!costDisplay) return; 

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
            if(invUnit === useUnit) {
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
            if(display) display.textContent = total.toFixed(2);
        }

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
                 if(res.success) {
                    const newOpt = { value: res.ingredient.id, text: res.ingredient.name, price: res.ingredient.price, unit: res.ingredient.unit };
                    
                    // Add to all selects
                    document.querySelectorAll('.ingredient-select').forEach(s => {
                         if(s.tomselect) s.tomselect.addOption(newOpt);
                    });
                    
                    // Select in active
                    if(activeSelect) activeSelect.tomselect.setValue(res.ingredient.id);
                    
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