@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-2">
        <a href="{{ route('ingredients.index') }}" class="text-muted"><i data-lucide="arrow-left"></i></a>
        <h1>Create Ingredient</h1>
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('ingredients.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column: General & Usage -->
                <div class="space-y-6">
                    <div class="card bg-white shadow-sm border border-slate-200">
                        <div class="flex items-center gap-2 mb-6 pb-2 border-b border-slate-100">
                            <i data-lucide="info" class="text-primary w-5 h-5"></i>
                            <h3 class="text-lg font-semibold text-slate-800">General Information</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="form-group">
                                <label class="form-label flex items-center gap-2">
                                    Ingredient Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" class="form-control" required value="{{ old('name') }}"
                                    placeholder="e.g. Extra Virgin Olive Oil">
                            </div>

                            <div class="form-group">
                                <label class="form-label flex items-center gap-2">
                                    Category <span class="text-danger">*</span>
                                </label>
                                <select name="category_id" id="category-select" class="form-control" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label flex items-center gap-2">
                                    Usage Unit (Recipe Unit) <span class="text-danger">*</span>
                                </label>
                                <select name="measurement_unit" id="usage_unit_select" class="form-control" required>
                                    <option value="">Select Unit</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->value }}" {{ old('measurement_unit') == $unit->value ? 'selected' : '' }}>{{ $unit->label() }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-slate-500 mt-1">This is how you measure it in recipes (e.g. grams, ml).</p>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-white shadow-sm border border-slate-200">
                        <div class="flex items-center gap-2 mb-6 pb-2 border-b border-slate-100">
                            <i data-lucide="alert-triangle" class="text-warning w-5 h-5"></i>
                            <h3 class="text-lg font-semibold text-slate-800">Storage & Alerts</h3>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label text-slate-600">Storage Location</label>
                                <select name="storage_location" class="form-control">
                                    <option value="">Select Location</option>
                                    <option value="Fridge" {{ old('storage_location') == 'Fridge' ? 'selected' : '' }}>Fridge</option>
                                    <option value="Freezer" {{ old('storage_location') == 'Freezer' ? 'selected' : '' }}>Freezer</option>
                                    <option value="Dry Store" {{ old('storage_location') == 'Dry Store' ? 'selected' : '' }}>Dry Store</option>
                                    <option value="Bar" {{ old('storage_location') == 'Bar' ? 'selected' : '' }}>Bar</option>
                                    <option value="Other" {{ old('storage_location') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-600">Alert Threshold</label>
                                <input type="number" name="alert_threshold" class="form-control" step="0.01" min="0" value="{{ old('alert_threshold', 0) }}"
                                    placeholder="Qty for low stock alert">
                            </div>
                        </div>
                        <div class="form-group mt-2">
                            <label class="form-label text-slate-600">Primary Vendor (Optional)</label>
                            <input type="text" name="vendor" class="form-control" value="{{ old('vendor') }}" placeholder="Primary Supplier Name">
                        </div>
                    </div>
                </div>

                <!-- Right Column: Purchase & Allergens -->
                <div class="space-y-6">
                    <!-- Purchase Configuration Card -->
                    <div class="card shadow-sm border border-indigo-100" style="background: linear-gradient(to bottom right, #f8faff, #ffffff);">
                        <div class="flex items-center gap-2 mb-6 pb-2 border-b border-indigo-50">
                            <i data-lucide="shopping-cart" class="text-indigo-600 w-5 h-5"></i>
                            <h3 class="text-lg font-semibold text-indigo-900">Purchase Configuration</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="form-label text-indigo-700">Purchase Qty</label>
                                    <input type="number" name="purchase_quantity" class="form-control border-indigo-200" step="0.001" min="0" value="{{ old('purchase_quantity', 1) }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-indigo-700">Purchase Unit</label>
                                    <select name="purchase_unit" id="purchase_unit_select" class="form-control border-indigo-200" required>
                                        <option value="">Select Unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->value }}" {{ old('purchase_unit') == $unit->value ? 'selected' : '' }}>{{ $unit->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label text-indigo-700 font-bold">Total Purchase Price (Excl. Tax)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-slate-400">$</span>
                                    </div>
                                    <input type="number" name="purchase_price" class="form-control pl-8 border-indigo-300 bg-indigo-50/30" step="0.01" min="0" value="{{ old('purchase_price') }}" required placeholder="0.00">
                                </div>
                                <p class="text-xs text-indigo-500 mt-2 italic flex items-center gap-1">
                                    <i data-lucide="calculator" class="w-3 h-3"></i> Setup standard cost per packaging unit.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Allergens Section -->
                    <div class="card bg-white shadow-sm border border-slate-200">
                        <div class="flex items-center gap-2 mb-6 pb-2 border-b border-slate-100">
                            <i data-lucide="shield-alert" class="text-red-500 w-5 h-5"></i>
                            <h3 class="text-lg font-semibold text-slate-800">Allergen Safety</h3>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                            @foreach(App\Enums\Allergen::cases() as $allergen)
                                <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 cursor-pointer transition-colors group">
                                    <div class="relative flex items-center">
                                        <input type="checkbox" name="allergen_tags[]" value="{{ $allergen->value }}" 
                                            class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary transition-all cursor-pointer"
                                            {{ in_array($allergen->value, old('allergen_tags', [])) ? 'checked' : '' }}>
                                    </div>
                                    <span class="text-sm text-slate-600 group-hover:text-slate-900 transition-colors">{{ $allergen->label() }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end gap-4 border-t border-slate-200 pt-6">
                <a href="{{ route('ingredients.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary px-12 py-3 shadow-lg shadow-blue-500/20">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    @if(auth()->user()->isAdmin())
                        Save & Approve Ingredient
                    @else
                        Submit for Approval
                    @endif
                </button>
            </div>

        </form>
    </div>

    @push('styles')
    <style>
        /* Force Tom Select dropdown behavior and visibility */
        .ts-dropdown {
            z-index: 1000 !important;
            background: white !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            border-radius: 0.5rem !important;
            margin-top: 4px !important;
        }
        .ts-dropdown.hidden {
            display: none !important;
        }
        .ts-control {
            border-radius: 0.5rem !important;
            min-height: 42px !important;
            display: flex !important;
            align-items: center !important;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        (function() {
            function initializeTomSelect() {
                try {
                    // Helper to initialize TomSelect with standard best-practices for closing
                    const initTS = (id, options = {}) => {
                        const el = document.getElementById(id);
                        if (!el) return;

                        const config = {
                            closeAfterSelect: true,
                            onItemAdd: function() {
                                this.close();
                                this.blur();
                            },
                            onChange: function() {
                                this.close();
                                this.blur();
                            },
                            render: {
                                option_create: function(data, escape) {
                                    return '<div class="create">Add <strong>' + escape(data.input) + '</strong>...</div>';
                                }
                            },
                            ...options
                        };
                        return new TomSelect(el, config);
                    };

                    // Category Select with Create function
                    initTS('category-select', {  // Used hyphen
                        create: function(input, callback) {
                            if (!confirm('Add "' + input + '" as a new Ingredient Category?')) {
                                return callback(false);
                            }
                            
                            fetch('{{ route('categories.storeQuick') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ name: input, type: 'ingredient' })
                            })
                            .then(r => r.json())
                            .then(res => {
                                if (res.success) {
                                    callback({ value: res.id, text: res.name });
                                } else {
                                    alert(res.message || 'Failed to create category');
                                    callback(false);
                                }
                            })
                            .catch(err => {
                                console.error('Error creating category:', err);
                                callback(false);
                            });
                        },
                        placeholder: 'Select or type to create...',
                        sortField: { field: "text", direction: "asc" }
                    });

                    // Other selects
                    ['usage_unit_select', 'purchase_unit_select'].forEach(id => {
                        initTS(id, {
                            create: false,
                            sortField: { field: "text", direction: "asc" }
                        });
                    });
                } catch (e) {
                    console.error('TomSelect Initialization Error:', e);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeTomSelect);
            } else {
                initializeTomSelect();
            }
        })();
    </script>
    @endpush
@endsection