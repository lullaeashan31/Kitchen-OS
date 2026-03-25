@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-2">
        <a href="{{ route('categories.index') }}" class="text-muted"><i data-lucide="arrow-left"></i></a>
        <h1>Create Category</h1>
    </div>
@endsection

@section('content')
    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Category Name</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name') }}"
                    placeholder="e.g. Appetizers">
            </div>

            <div class="form-group" style="margin-top: 1rem;">
                <label class="form-label">Category Type</label>
                <select name="type" class="form-control" required>
                    <option value="ingredient" {{ old('type') == 'ingredient' ? 'selected' : '' }}>Ingredient / Purchase Category</option>
                    <option value="recipe" {{ old('type') == 'recipe' ? 'selected' : '' }}>Recipe Collection Category</option>
                </select>
                <p class="text-xs text-muted" style="margin-top: 0.25rem;">Specify where you want to use this category.</p>
            </div>

            <button type="submit" class="btn btn-primary w-full" style="margin-top: 1.5rem; padding: 0.75rem;">Create Category</button>
        </form>
    </div>
@endsection