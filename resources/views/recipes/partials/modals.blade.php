{{-- Quick Category Modal --}}
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

{{-- Hidden Ingredient Options (used by JS to populate selects) --}}
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

{{-- Sub-Recipe Modal --}}
<div id="addSubRecipeModal"
    class="fixed inset-0 bg-gray-900/70 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 animate-fade-in">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl transform transition-all scale-100 hover:scale-[1.01]">
        <div class="relative p-8 bg-gradient-to-br from-indigo-600 via-indigo-500 to-purple-600 overflow-hidden">
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

{{-- Quick Create Ingredient Modal --}}
<div id="createIngredientModal"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-[70] flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all scale-100">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-6 flex justify-between items-center relative overflow-hidden">
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
