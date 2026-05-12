@extends('layouts.app')

@section('header')
    <h1>Ingredients</h1>
@endsection

@section('actions')
    <a href="{{ route('ingredients.create') }}" class="btn btn-primary">
        <i data-lucide="plus"></i> New Ingredient
    </a>
@endsection

@section('content')
    <div class="card">
        <div style="margin-bottom: 1.5rem;">
            <form method="GET" action="{{ route('ingredients.index') }}" class="flex gap-4">
                <div style="flex: 1;">
                    <input type="text" name="search" class="form-control" placeholder="Search ingredients..."
                        value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn btn-secondary">Search</button>
            </form>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Allergens</th>
                        <th>Status</th>
                        <th>Used In</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ingredients as $ingredient)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $ingredient->name }}</div>
                                @if($ingredient->status === 'pending')
                                    <span class="badge-warning">Awaiting Approval</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                                    @foreach($ingredient->allergen_tags ?? [] as $tag)
                                        <span class="badge-gray">
                                            {{ App\Enums\Allergen::tryFrom($tag)?->label() ?? $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if($ingredient->status === 'approved')
                                    <span class="badge-success">Active</span>
                                @elseif($ingredient->status === 'pending')
                                    <span class="badge-warning">Awaiting Approval</span>
                                @else
                                    <span class="badge-danger">Not Approved</span>
                                @endif
                            </td>
                            <td>{{ $ingredient->recipes_count ?? 0 }} Recipes</td>
                            <td>
                                <div class="flex gap-2">
                                    @unless(auth()->user()->isStaff())
                                        <a href="{{ route('ingredients.edit', $ingredient) }}" class="btn btn-sm btn-secondary">
                                            <i data-lucide="edit-2" style="width: 14px;"></i>
                                        </a>
                                        @can('delete', $ingredient)
                                            <form action="{{ route('ingredients.destroy', $ingredient) }}" method="POST"
                                                onsubmit="return confirm('Delete ingredient? References in recipes might break.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm" style="color: var(--danger-color);">
                                                    <i data-lucide="trash-2" style="width: 14px;"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    @else
                                        <span class="text-xs text-muted">Read Only</span>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                No ingredients found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $ingredients->links() }}
        </div>
    </div>
@endsection