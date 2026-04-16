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

        {{-- Left Column: Primary Details --}}
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
                    {{-- Recipe Name --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Recipe Name</label>
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
                    {{-- Method / Instructions --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Method / Instructions</label>
                        <textarea name="method"
                                  class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none min-h-[250px]"
                                  required>{{ old('method', $recipe->method) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Sets & Sub‑Recipes --}}
        <div class="w-full lg:w-2/3 space-y-8 animate-fade-in-right">
            {{-- Sub‑Recipes Used Section --}}
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-black text-gray-900 flex items-center gap-3">
                        <span class="p-2 bg-indigo-600 rounded-xl shadow-lg">
                            <i data-lucide="component" class="w-6 h-6 text-white"></i>
                        </span>
                        Sub‑Recipes Used
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
                @foreach($stages->where('name', '!=', 'Sub‑Recipes') as $index => $stage)
                    <div class="stage-block border border-gray-200 rounded-xl p-6 bg-gray-50/50 relative group">
                        <input type="hidden" name="stages[{{ $index }}][id]" value="{{ data_get($stage, 'id') }}">
                        <button type="button" onclick="removeStage(this)" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100" title="Remove Set">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                        </button>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Set Name</label>
                                <input type="text" name="stages[{{ $index }}][name]" value="{{ data_get($stage, 'name') }}" required
                                       class="w-full px-4 py-2 rounded-lg border {{ $errors->has('stages.'.$index.'.name') ? 'border-red-500' : 'border-gray-200' }} focus:border-blue-500 outline-none">
                                @error('stages.' . $index . '.name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Method & Instructions</label>
                                <textarea name="stages[{{ $index }}][method]" rows="3"
                                          class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-blue-500 outline-none resize-y"
                                          placeholder="Describe the steps for this stage...">{{ data_get($stage, 'method') }}</textarea>
                            </div>
                        </div>
                        {{-- Ingredients Table --}}
                        <div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
                            <table class="w-full min-w-[600px]">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-2 md:px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Item</th>
                                        <th class="px-2 md:px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Quantity</th>
                                        <th class="px-2 md:px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Unit</th>
                                        @if(auth()->user()->isAdmin())
                                            <th class="px-2 md:px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Cost</th>
                                        @endif
                                        <th class="px-2 md:px-4 py-2 text-center"></th>
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
                                        <tr class="group hover:bg-blue-50/30">
                                            <td class="px-4 py-3">
                                                <select name="stages[{{ $index }}][ingredients][{{ $ingIndex }}][ingredient_id]" class="ingredient-select w-full" required>
                                                    <option value="{{ $rIngId }}" selected data-price="{{ $rPrice }}" data-unit="{{ $rMeasUnit }}">
                                                        {{ $rName }} @if($rMeasUnit) ({{ $rMeasUnit }}) @endif
                                                    </option>
                                                    {{-- other options injected via JS --}}
                                                </select>
                                            </td>
                                            <td class="px-2 md:px-4 py-2">
                                                <input type="number" step="any" name="stages[{{ $index }}][ingredients][{{ $ingIndex }}][quantity]"
                                                       value="{{ $rQty }}" required class="quantity-input w-full h-[40px] rounded-xl border-2 focus:border-blue-500 outline-none text-center font-bold" />
                                            </td>
                                            <td class="px-2 md:px-4 py-2">
                                                <input type="hidden" name="stages[{{ $index }}][ingredients][{{ $ingIndex }}][unit]" value="{{ $rUnit }}" />
                                                <input type="text" readonly value="{{ $rUnit }}" class="unit-display w-full h-[40px] rounded-xl border-2 bg-gray-50 font-bold text-sm text-gray-600 cursor-not-allowed" />
                                            </td>
                                            @if(auth()->user()->isAdmin())
                                                <td class="px-2 md:px-4 py-2 text-right font-medium text-gray-700 cost-display">{{ number_format((float) $rCost, 2) }}</td>
                                            @endif
                                            <td class="px-2 md:px-4 py-2 text-center">
                                                <button type="button" onclick="removeRow(this)" class="text-gray-400 hover:text-red-500 p-1 rounded-full">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50 border-t border-gray-200">
                                    <tr>
                                        <td colspan="6" class="px-4 py-3">
                                            <button type="button" onclick="addIngredientRow(this)" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                                <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Ingredient
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Sticky Summary Bar --}}
        <div class="fixed bottom-0 left-0 right-0 bg-white/90 backdrop-blur-xl border-t border-gray-200 z-[60] shadow-[0_-10px_40px_rgba(0,0,0,0.05)] p-4 md:p-6 animate-fade-in-up">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex flex-wrap items-center gap-8">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Total Recipe Cost</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-gray-900">₹<span id="totalCostDisplay">0.00</span></span>
                            <span class="text-sm font-bold text-gray-400">/ Total</span>
                        </div>
                    </div>
                    <div class="flex flex-col border-l border-gray-100 pl-8 md:pl-12">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Cost Per Portion</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-black text-blue-600">₹<span id="costPerPortionDisplay">0.00</span></span>
                            <span class="text-xs font-bold text-gray-400">/ Unit</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <button type="button" onclick="window.history.back()" class="flex-1 md:flex-none px-8 py-4 bg-white border-2 border-gray-100 text-gray-500 font-black rounded-2xl hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="flex-1 md:flex-none px-12 py-4 bg-gradient-to-br from-blue-600 to-indigo-700 text-white font-black rounded-2xl shadow-2xl hover:shadow-blue-600/50 hover:-translate-y-1 flex items-center justify-center gap-3">
                        <i data-lucide="check-circle" class="w-6 h-6"></i> Update Recipe
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Modals (Category, Sub‑Recipe, Ingredient) – same as create view --}}
@include('recipes.partials.modals')

@push('scripts')
<script>
    // Initialize data for sub‑recipes, similar to create view
    @php
        $subRecipeStage = $recipe->stages->where('name', 'Sub‑Recipes')->first();
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
    window.initialSubRecipes = @json($existingSubData);
    window.subRecipeStageId = {{ $subRecipeStage ? $subRecipeStage->id : 'null' }};
</script>
@endpush
@endsection