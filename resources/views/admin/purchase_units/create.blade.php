@extends('layouts.app')

@section('title', 'Add New Purchase Unit')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0 text-gray-800">Add Purchase Unit</h1>
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
                    <h6 class="m-0 font-weight-bold text-primary">Unit Configuration</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.purchase-units.store', ['kitchen_slug' => $kitchen->slug]) }}" method="POST">
                        @csrf

                        <div class="form-group mb-4">
                            <label for="name" class="font-weight-bold">Unit Name (e.g., Bag, Crate, Box)</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                   placeholder="Enter unit name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">The name used when recording purchases.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="base_unit" class="font-weight-bold">Base Usage Unit</label>
                                    <select name="base_unit" id="base_unit" class="form-control @error('base_unit') is-invalid @enderror" required>
                                        <option value="">Select Base Unit</option>
                                        <option value="g" {{ old('base_unit') == 'g' ? 'selected' : '' }}>Gram (g)</option>
                                        <option value="ml" {{ old('base_unit') == 'ml' ? 'selected' : '' }}>Milliliter (ml)</option>
                                        <option value="pcs" {{ old('base_unit') == 'pcs' ? 'selected' : '' }}>Piece (pcs)</option>
                                        <option value="kg" {{ old('base_unit') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                        <option value="l" {{ old('base_unit') == 'l' ? 'selected' : '' }}>Liter (l)</option>
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
                                           placeholder="e.g., 25000" value="{{ old('conversion_factor') }}" required>
                                    @error('conversion_factor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div id="preview-calc" class="alert alert-info mt-2" style="display: none;">
                            <i class="fas fa-calculator mr-2"></i> Result: 
                            <strong>1 <span id="prev-name">Unit</span></strong> = 
                            <strong><span id="prev-factor">0</span> <span id="prev-unit">Base Unit</span></strong>
                        </div>

                        <hr>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="fas fa-save mr-2"></i>Create Purchase Unit
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

            if ($('#name').val() && $('#conversion_factor').val() && $('#base_unit').val()) {
                $('#prev-name').text(name);
                $('#prev-factor').text(factor);
                $('#prev-unit').text(unit);
                $('#preview-calc').fadeIn();
            } else {
                $('#preview-calc').fadeOut();
            }
        }

        $('#name, #conversion_factor, #base_unit').on('input change', updatePreview);
    });
</script>
@endpush
@endsection
