@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-4">
        <a href="{{ route('ingredients.index') }}" class="btn btn-secondary p-3">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-primary">Edit Ingredient</h1>
            <p class="text-[10px] font-bold text-muted mt-1 uppercase tracking-widest">Master <span class="text-accent">Inventory Data</span></p>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto">
        <form action="{{ route('ingredients.update', $ingredient) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Left Column: General & Usage --}}
                <div class="space-y-8">
                    <div class="card p-0 overflow-hidden">
                        <div class="p-8 border-b border-subtle bg-white/5 flex items-center justify-between">
                            <h2 class="flex items-center gap-3">
                                <i data-lucide="info" class="text-accent"></i>
                                General Information
                            </h2>
                            <span class="badge {{ $ingredient->status === 'approved' ? 'badge-success' : 'badge-warning' }}">
                                {{ ucfirst($ingredient->status) }}
                            </span>
                        </div>
                        
                        <div class="p-8 space-y-6">
                            <div class="form-group">
                                <label class="form-label text-[10px] uppercase">Ingredient Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" class="form-control" required value="{{ old('name', $ingredient->name) }}" placeholder="e.g. Extra Virgin Olive Oil">
                            </div>

                            <div class="form-group">
                                <label class="form-label text-[10px] uppercase">Category <span class="text-red-500">*</span></label>
                                <select name="category_id" id="category-select" class="form-control" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $ingredient->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label text-[10px] uppercase">Usage Unit (Recipe Unit) <span class="text-red-500">*</span></label>
                                <select name="measurement_unit" id="usage_unit_select" class="form-control" required>
                                    <option value="">Select Unit</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->value }}" {{ old('measurement_unit', $ingredient->measurement_unit) == $unit->value ? 'selected' : '' }}>{{ $unit->label() }}</option>
                                    @endforeach
                                </select>
                                <p class="text-[10px] font-bold text-muted mt-2 uppercase tracking-tight opacity-60 italic">Measurement used in recipe specifications (e.g. grams, ml)</p>
                            </div>
                        </div>
                    </div>

                    <div class="card p-0 overflow-hidden">
                        <div class="p-8 border-b border-subtle bg-white/5">
                            <h2 class="flex items-center gap-3">
                                <i data-lucide="package" class="text-accent"></i>
                                Storage & Operations
                            </h2>
                        </div>
                        
                        <div class="p-8 space-y-6">
                            <div class="grid grid-cols-2 gap-6">
                                <div class="form-group">
                                    <label class="form-label text-[10px] uppercase">Storage Location</label>
                                    <select name="storage_location" class="form-control">
                                        <option value="">Select Location</option>
                                        @foreach(['Fridge', 'Freezer', 'Dry Store', 'Bar', 'Other'] as $loc)
                                            <option value="{{ $loc }}" {{ old('storage_location', $ingredient->storage_location) == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-[10px] uppercase">Low Stock Threshold</label>
                                    <input type="number" name="alert_threshold" class="form-control font-bold" step="0.01" min="0" value="{{ old('alert_threshold', $ingredient->alert_threshold) }}" placeholder="0.00">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-[10px] uppercase">Primary Vendor (Optional)</label>
                                <input type="text" name="vendor" class="form-control" value="{{ old('vendor', $ingredient->vendor) }}" placeholder="Primary Supplier Name">
                            </div>

                            <div class="form-group pt-4 border-t border-subtle">
                                <label class="form-label text-[10px] uppercase">Operational Status</label>
                                <select name="status" class="form-control">
                                    <option value="approved" {{ old('status', $ingredient->status) == 'approved' ? 'selected' : '' }}>Approved / Active</option>
                                    <option value="pending" {{ old('status', $ingredient->status) == 'pending' ? 'selected' : '' }}>Pending Review</option>
                                    <option value="rejected" {{ old('status', $ingredient->status) == 'rejected' ? 'selected' : '' }}>Rejected / Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Purchase & Safety --}}
                <div class="space-y-8">
                    <div class="card p-0 overflow-hidden border-accent/20 bg-accent/5">
                        <div class="p-8 border-b border-subtle flex items-center justify-between">
                            <h2 class="flex items-center gap-3">
                                <i data-lucide="shopping-cart" class="text-accent"></i>
                                Purchase Logic
                            </h2>
                            @if($ingredient->price_per_base_unit > 0)
                                <div class="text-[10px] font-black bg-primary text-accent px-3 py-1.5 rounded-full border border-accent/20">
                                    LATEST: ₹{{ number_format($ingredient->price_per_base_unit, 4) }} / {{ $ingredient->base_unit }}
                                </div>
                            @endif
                        </div>
                        
                        <div class="p-8 space-y-6">
                            <div class="grid grid-cols-2 gap-6">
                                <div class="form-group">
                                    <label class="form-label text-[10px] uppercase text-accent">Purchase Qty</label>
                                    <input type="number" name="purchase_quantity" class="form-control font-black border-accent/20" step="0.001" min="0" value="{{ old('purchase_quantity', $ingredient->purchase_quantity) }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-[10px] uppercase text-accent">Purchase Unit</label>
                                    <select name="purchase_unit" id="purchase_unit_select" class="form-control border-accent/20" required>
                                        <option value="">Select Unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->value }}" {{ old('purchase_unit', $ingredient->purchase_unit) == $unit->value ? 'selected' : '' }}>{{ $unit->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card p-0 overflow-hidden">
                        <div class="p-8 border-b border-subtle bg-white/5">
                            <h2 class="flex items-center gap-3">
                                <i data-lucide="shield-alert" class="text-red-500"></i>
                                Allergen Safety
                            </h2>
                        </div>
                        <div class="p-8 grid grid-cols-2 gap-x-6 gap-y-3">
                            @php $currentAllergens = old('allergen_tags', $ingredient->allergen_tags ?? []) @endphp
                            @foreach(App\Enums\Allergen::cases() as $allergen)
                                <label class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/5 cursor-pointer transition-all border border-transparent hover:border-subtle group">
                                    <input type="checkbox" name="allergen_tags[]" value="{{ $allergen->value }}" 
                                        class="w-5 h-5 rounded border-subtle bg-primary text-accent focus:ring-accent transition-all cursor-pointer"
                                        {{ in_array($allergen->value, $currentAllergens) ? 'checked' : '' }}>
                                    <span class="text-[10px] font-bold text-muted uppercase tracking-widest group-hover:text-primary">{{ $allergen->label() }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="card p-0 overflow-hidden">
                        <div class="p-8 border-b border-subtle bg-white/5 flex items-center justify-between">
                            <h2 class="flex items-center gap-3">
                                <i data-lucide="paperclip" class="text-accent"></i>
                                Digital Artifacts
                            </h2>
                        </div>
                        
                        <div class="p-8 space-y-4">
                            @forelse($ingredient->driveFiles as $file)
                                <div class="flex justify-between items-center p-4 rounded-xl border border-subtle bg-white/5 group">
                                    <a href="javascript:void(0)" onclick="openPreview('{{ $file->drive_url }}', '{{ $file->name }}')" class="flex items-center gap-3 text-[10px] font-black text-primary hover:text-accent uppercase tracking-widest transition-all">
                                        <i data-lucide="file-text" class="w-4 h-4 text-accent"></i> {{ $file->name }}
                                    </a>
                                    <form action="{{ route('drive_files.destroy', $file) }}" method="POST" onsubmit="return confirm('Permanently remove this file?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-muted hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <div class="py-8 text-center bg-white/5 rounded-xl border border-dashed border-subtle">
                                    <p class="text-[10px] font-bold text-muted uppercase tracking-widest opacity-40">No artifacts attached</p>
                                </div>
                            @endforelse

                            @can('create', App\Models\DriveFile::class)
                                <div class="mt-6 pt-6 border-t border-subtle">
                                    <button type="button" onclick="document.getElementById('attach-form').classList.toggle('hidden')" class="btn btn-secondary w-full py-2 text-[10px] uppercase">
                                        + Attach New File
                                    </button>
                                    <div id="attach-form" class="hidden mt-4 space-y-3 p-4 bg-primary/20 rounded-xl border border-subtle border-dashed">
                                        <form action="{{ route('drive_files.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="linked_type" value="ingredient">
                                            <input type="hidden" name="linked_id" value="{{ $ingredient->id }}">
                                            <div class="space-y-3">
                                                <input type="text" name="name" class="form-control text-[10px] py-2" placeholder="File Title" required>
                                                <input type="url" name="drive_url" class="form-control text-[10px] py-2" placeholder="Link (Drive/URL)" required>
                                                <button type="submit" class="btn btn-primary w-full py-2 text-[10px] uppercase">Attach</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex items-center justify-end gap-4 border-t border-subtle pt-8">
                <a href="{{ route('ingredients.index') }}" class="btn btn-secondary px-8 py-3 text-[10px] uppercase">Cancel changes</a>
                <button type="submit" class="btn btn-primary px-12 py-3 text-[10px] uppercase tracking-widest">
                    <i data-lucide="save"></i> Sync Ingredient
                </button>
            </div>
        </form>
    </div>

    @push('styles')
    <style>
        .ts-dropdown {
            z-index: 1000 !important;
            background: var(--navy-primary) !important;
            border: 1px solid var(--navy-mid) !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4) !important;
            border-radius: 12px !important;
            margin-top: 4px !important;
            color: var(--ivory) !important;
        }
        .ts-dropdown .active {
            background: var(--navy-mid) !important;
            color: var(--brass) !important;
        }
        .ts-dropdown .option {
            padding: 10px 16px !important;
            font-size: 13px !important;
        }
        .ts-control {
            background: var(--navy-primary) !important;
            border: 1px solid var(--navy-mid) !important;
            border-radius: 12px !important;
            min-height: 48px !important;
            color: var(--ivory) !important;
            padding: 8px 16px !important;
        }
        .ts-control input {
            color: var(--ivory) !important;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        (function() {
            function initializeTomSelect() {
                try {
                    const initTS = (id, options = {}) => {
                        const el = document.getElementById(id);
                        if (!el) return;

                        const config = {
                            closeAfterSelect: true,
                            onItemAdd: function() { this.close(); this.blur(); },
                            onChange: function() { this.close(); this.blur(); },
                            ...options
                        };
                        return new TomSelect(el, config);
                    };

                    initTS('category-select', { 
                        create: function(input, callback) {
                            if (!confirm('Add "' + input + '" as a new Category?')) return callback(false);
                            fetch('{{ route('categories.storeQuick') }}', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                body: JSON.stringify({ name: input, type: 'ingredient' })
                            })
                            .then(r => r.json())
                            .then(res => {
                                if (res.success) callback({ value: res.id, text: res.name });
                                else { alert(res.message || 'Failed to create category'); callback(false); }
                            })
                            .catch(() => callback(false));
                        },
                        placeholder: 'Select or type to create...',
                        sortField: { field: "text", direction: "asc" }
                    });

                    ['usage_unit_select', 'purchase_unit_select'].forEach(id => {
                        initTS(id, { create: false, sortField: { field: "text", direction: "asc" } });
                    });
                } catch (e) { console.error('TomSelect Error:', e); }
            }
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initializeTomSelect);
            else initializeTomSelect();
        })();
    </script>
    @endpush
@endsection