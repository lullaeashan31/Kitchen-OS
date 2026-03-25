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
                                    <span style="font-size:0.7rem; background:#fff7ed; color:#ea580c; border:1px solid #fed7aa; padding:1px 6px; border-radius:9999px; font-weight:700; display:inline-block; margin-top:2px;">Pending Approval</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                                    @foreach($ingredient->allergen_tags ?? [] as $tag)
                                        <span class="badge badge-gray" style="font-size: 0.7rem;">
                                            {{ App\Enums\Allergen::tryFrom($tag)?->label() ?? $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if($ingredient->status === 'approved')
                                    <span style="font-size:0.7rem; background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; padding:1px 8px; border-radius:9999px; font-weight:700;">✔ Approved</span>
                                @elseif($ingredient->status === 'pending')
                                    <span style="font-size:0.7rem; background:#fff7ed; color:#ea580c; border:1px solid #fed7aa; padding:1px 8px; border-radius:9999px; font-weight:700;">⏳ Pending</span>
                                @else
                                    <span style="font-size:0.7rem; background:#fef2f2; color:#dc2626; border:1px solid #fecaca; padding:1px 8px; border-radius:9999px; font-weight:700;">✕ Rejected</span>
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