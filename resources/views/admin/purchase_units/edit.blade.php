@extends('layouts.app')

@section('title', 'Edit Purchase Unit')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0 text-gray-800">Edit Purchase Unit</h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('admin.purchase-units.index', ['kitchen_slug' => $kitchen->slug]) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Update Unit: {{ $unit->name }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.purchase-units.update', ['kitchen_slug' => $kitchen->slug, 'purchase_unit' => $unit->id]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-4">
                            <label for="name" class="font-weight-bold">Unit Name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $unit->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="base_unit" class="font-weight-bold">Base Usage Unit</label>
                                    <select name="base_unit" id="base_unit" class="form-control @error('base_unit') is-invalid @enderror" required>
                                        <option value="g" {{ old('base_unit', $unit->base_unit) == 'g' ? 'selected' : '' }}>Gram (g)</option>
                                        <option value="ml" {{ old('base_unit', $unit->base_unit) == 'ml' ? 'selected' : '' }}>Milliliter (ml)</option>
                                        <option value="pcs" {{ old('base_unit', $unit->base_unit) == 'pcs' ? 'selected' : '' }}>Piece (pcs)</option>
                                        <option value="kg" {{ old('base_unit', $unit->base_unit) == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                        <option value="l" {{ old('base_unit', $unit->base_unit) == 'l' ? 'selected' : '' }}>Liter (l)</option>
                                    </select>
                                    @error('base_unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="conversion_factor" class="font-weight-bold">Conversion Factor</label>
                                    <input type="number" step="0.0001" name="conversion_factor" id="conversion_factor" 
                                           class="form-control @error('conversion_factor') is-invalid @enderror" 
                                           value="{{ old('conversion_factor', $unit->conversion_factor) }}" required>
                                    @error('conversion_factor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div id="preview-calc" class="alert alert-info mt-2">
                            <i class="fas fa-calculator mr-2"></i> Result: 
                            <strong>1 <span id="prev-name">{{ $unit->name }}</span></strong> = 
                            <strong><span id="prev-factor">{{ $unit->conversion_factor }}</span> <span id="prev-unit">{{ $unit->base_unit }}</span></strong>
                        </div>

                        <hr>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="fas fa-save mr-2"></i>Update Purchase Unit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        function updatePreview() {
            const name = $('#name').val() || 'Unit';
            const factor = $('#conversion_factor').val() || '0';
            const unit = $('#base_unit').val() || 'Base Unit';

            $('#prev-name').text(name);
            $('#prev-factor').text(factor);
            $('#prev-unit').text(unit);
        }

        $('#name, #conversion_factor, #base_unit').on('input change', updatePreview);
    });
</script>
@endpush
@endsection
