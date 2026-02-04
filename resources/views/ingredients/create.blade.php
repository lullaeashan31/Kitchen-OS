@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-2">
        <a href="{{ route('ingredients.index') }}" class="text-muted"><i data-lucide="arrow-left"></i></a>
        <h1>Create Ingredient</h1>
    </div>
@endsection

@section('content')
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <form action="{{ route('ingredients.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name') }}"
                        placeholder="e.g. Olive Oil">
                </div>

                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Measurement Unit</label>
                        <select name="measurement_unit" class="form-control" required>
                            <option value="">Select Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->value }}">{{ $unit->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Purchase Unit (Optional)</label>
                        <input type="text" name="purchase_unit" class="form-control" placeholder="e.g. Case, Bottle">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" required
                                placeholder="0.00">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Vendor (Optional)</label>
                        <input type="text" name="vendor" class="form-control" placeholder="Primary Vendor">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Storage Location</label>
                        <select name="storage_location" class="form-control">
                            <option value="">Select Location</option>
                            <option value="Fridge">Fridge</option>
                            <option value="Freezer">Freezer</option>
                            <option value="Dry Store">Dry Store</option>
                            <option value="Bar">Bar</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Low Stock Alert</label>
                        <input type="number" name="alert_threshold" class="form-control" step="0.01" min="0"
                            placeholder="Minimum Quantity">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Allergens</label>
                    <div class="grid grid-cols-2 gap-2"
                        style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; border: 1px solid var(--border-color); padding: 1rem; border-radius: var(--radius-md);">
                        @foreach(App\Enums\Allergen::cases() as $allergen)
                            <label class="flex items-center gap-2 cursor-pointer"
                                style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="checkbox" name="allergen_tags[]" value="{{ $allergen->value }}" {{ in_array($allergen->value, old('allergen_tags', [])) ? 'checked' : '' }}>
                                <span style="font-size: 0.875rem;">{{ $allergen->label() }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-full" style="justify-content: center;">Save Ingredient</button>
        </form>
    </div>
@endsection