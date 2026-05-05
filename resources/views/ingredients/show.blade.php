@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-4">
        <a href="{{ route('ingredients.index') }}" class="btn btn-secondary btn-sm">
            <i data-lucide="arrow-left"></i> Back
        </a>
        <h1>Ingredient: {{ $ingredient->name }}</h1>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card">
            <h3 class="mb-4">Basic Information</h3>
            <div class="space-y-3">
                <div class="flex justify-between border-b pb-2">
                    <span class="text-muted">Category</span>
                    <span class="font-medium">{{ $ingredient->category?->name ?? 'Uncategorized' }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-muted">Measurement Unit</span>
                    <span class="font-medium">{{ $ingredient->measurement_unit }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-muted">Storage Location</span>
                    <span class="font-medium">{{ $ingredient->storage_location ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-muted">Status</span>
                    <span>
                        @if($ingredient->status === 'approved')
                            <span class="badge badge-success">Approved</span>
                        @elseif($ingredient->status === 'pending')
                            <span class="badge badge-warning">Pending Approval</span>
                        @else
                            <span class="badge badge-danger">Rejected</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="mb-4">Allergens</h3>
            <div class="flex flex-wrap gap-2">
                @forelse($ingredient->allergen_tags ?? [] as $tag)
                    <span class="badge badge-gray">
                        {{ App\Enums\Allergen::tryFrom($tag)?->label() ?? $tag }}
                    </span>
                @empty
                    <span class="text-muted italic">No allergens listed.</span>
                @endforelse
            </div>
        </div>
    </div>
@endsection
