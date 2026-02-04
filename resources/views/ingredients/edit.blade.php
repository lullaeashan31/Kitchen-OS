@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-2">
        <a href="{{ route('ingredients.index') }}" class="text-muted"><i data-lucide="arrow-left"></i></a>
        <h1>Edit Ingredient</h1>
    </div>
@endsection

@section('content')
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <form action="{{ route('ingredients.update', $ingredient) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required
                        value="{{ old('name', $ingredient->name) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $ingredient->category_id) == $category->id ? 'selected' : '' }}>
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
                                <option value="{{ $unit->value }}" {{ old('measurement_unit', $ingredient->measurement_unit) == $unit->value ? 'selected' : '' }}>
                                    {{ $unit->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Purchase Unit (Optional)</label>
                        <input type="text" name="purchase_unit" class="form-control"
                            value="{{ old('purchase_unit', $ingredient->purchase_unit) }}" placeholder="e.g. Case, Bottle">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" required
                                value="{{ old('price', $ingredient->price) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Vendor (Optional)</label>
                        <input type="text" name="vendor" class="form-control"
                            value="{{ old('vendor', $ingredient->vendor) }}" placeholder="Primary Vendor">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Storage Location</label>
                        <select name="storage_location" class="form-control">
                            <option value="">Select Location</option>
                            @foreach(['Fridge', 'Freezer', 'Dry Store', 'Bar', 'Other'] as $loc)
                                <option value="{{ $loc }}" {{ old('storage_location', $ingredient->storage_location) == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Low Stock Alert</label>
                        <input type="number" name="alert_threshold" class="form-control" step="0.01" min="0"
                            value="{{ old('alert_threshold', $ingredient->alert_threshold) }}"
                            placeholder="Minimum Quantity">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="approved" {{ old('status', $ingredient->status) == 'approved' ? 'selected' : '' }}>
                            Approved</option>
                        <option value="pending" {{ old('status', $ingredient->status) == 'pending' ? 'selected' : '' }}>
                            Pending</option>
                        <option value="rejected" {{ old('status', $ingredient->status) == 'rejected' ? 'selected' : '' }}>
                            Rejected</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Allergens</label>
                    <div class="grid grid-cols-2 gap-2"
                        style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; border: 1px solid var(--border-color); padding: 1rem; border-radius: var(--radius-md);">
                        @foreach(App\Enums\Allergen::cases() as $allergen)
                            <label class="flex items-center gap-2 cursor-pointer"
                                style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="checkbox" name="allergen_tags[]" value="{{ $allergen->value }}" {{ in_array($allergen->value, old('allergen_tags', $ingredient->allergen_tags ?? [])) ? 'checked' : '' }}>
                                <span style="font-size: 0.875rem;">{{ $allergen->label() }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Attached Files</label>
                <div style="margin-bottom: 1rem;">
                    @if($ingredient->driveFiles->isEmpty())
                        <p class="text-muted text-sm">No files attached.</p>
                    @else
                        <div class="flex flex-col gap-2">
                            @foreach($ingredient->driveFiles as $file)
                                <div class="flex justify-between items-center p-2 border rounded bg-white">
                                    <a href="javascript:void(0)"
                                        onclick="openPreview('{{ $file->drive_url }}', '{{ $file->name }}')"
                                        class="flex items-center gap-2 text-sm text-blue-600 hover:underline">
                                        <i data-lucide="file"></i> {{ $file->name }}
                                    </a>
                                    <form action="{{ route('drive_files.destroy', $file) }}" method="POST"
                                        onsubmit="return confirm('Remove file?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="if(confirm('Remove?')) this.form.submit()"
                                            class="text-red-500 hover:text-red-700">
                                            <i data-lucide="x" style="width: 14px;"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div style="padding: 1rem; background: #f9fafb; border-radius: var(--radius-md);">
                    <label class="form-label text-sm">Attach New File</label>
                    <!-- We need a separate form or use JS to post? Or put this outside main form? -->
                    <!-- Linking Drive File requires StoreDriveFileRequest -->
                    <!-- Let's use a small separate section below the main form? -->
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-full" style="justify-content: center;">Update Ingredient</button>
        </form>

        <div style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
            <h3>Attach File</h3>
            <form action="{{ route('drive_files.store') }}" method="POST" style="margin-top: 1rem;">
                @csrf
                <input type="hidden" name="linked_type" value="ingredient">
                <input type="hidden" name="linked_id" value="{{ $ingredient->id }}">
                <div class="form-group">
                    <input type="text" name="name" class="form-control" placeholder="File Name" required>
                </div>
                <div class="form-group">
                    <input type="url" name="drive_url" class="form-control" placeholder="Google Drive Link" required>
                </div>
                <button type="submit" class="btn btn-secondary w-full">Attach File</button>
            </form>
        </div>
    </div>
@endsection