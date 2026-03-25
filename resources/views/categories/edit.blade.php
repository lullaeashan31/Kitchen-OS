@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-2">
        <a href="{{ route('categories.index') }}" class="text-muted"><i data-lucide="arrow-left"></i></a>
        <h1>Edit Category</h1>
    </div>
@endsection

@section('content')
    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <form action="{{ route('categories.update', $category) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Category Name</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $category->name) }}">
            <div class="form-group" style="margin-top: 1rem;">
                <label class="form-label">Category Type</label>
                <select name="type" class="form-control" required>
                    <option value="ingredient" {{ old('type', $category->type) == 'ingredient' ? 'selected' : '' }}>Ingredient / Purchase Category</option>
                    <option value="recipe" {{ old('type', $category->type) == 'recipe' ? 'selected' : '' }}>Recipe Collection Category</option>
                </select>
                <p class="text-xs text-muted" style="margin-top: 0.25rem;">Specify where you want to use this category.</p>
            </div>
            <button type="submit" class="btn btn-primary w-full" style="margin-top: 1.5rem; padding: 0.75rem;">Update Category</button>
        </form>
    </div>
@endsection