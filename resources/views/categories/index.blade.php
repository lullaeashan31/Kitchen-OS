@extends('layouts.app')

@section('header')
    <h1 style="font-size: 1.5rem; font-weight: 600;">Manage Categories</h1>
    <p style="color: var(--text-muted);">Organize your ingredients and recipes.</p>
@endsection

@section('actions')
    <button onclick="submitBulkDelete()" class="btn btn-danger"
        style="margin-right: 0.5rem; padding: 0.5rem 1rem; background: #ef4444; color: white; border: none; border-radius: 0.375rem; cursor: pointer; display: none;"
        id="bulkDeleteBtn">
        <i data-lucide="trash-2" style="width: 1rem; height: 1rem; display: inline-block;"></i> Delete Selected
    </button>
    <a href="{{ route('categories.create') }}" class="btn btn-primary"
        style="padding: 0.5rem 1rem; background: var(--primary-color); color: white; border-radius: 0.375rem; text-decoration: none;">
        <i data-lucide="plus" style="width: 1rem; height: 1rem; display: inline-block;"></i> Add Category
    </a>
@endsection

@section('content')
    <div style="background: white; border-radius: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); overflow: hidden;">
        <form id="bulkDeleteForm" action="{{ route('categories.bulk_destroy') }}" method="POST">
            @csrf
            @method('DELETE')
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 0.75rem 1rem; width: 40px;">
                            <input type="checkbox" id="selectAll" onclick="toggleSelectAll()">
                        </th>
                        <th
                            style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                            Name</th>
                        <th
                            style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                            Usage</th>
                        <th
                            style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                            Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 0.75rem 1rem;">
                                <input type="checkbox" name="ids[]" value="{{ $category->id }}" class="item-checkbox"
                                    onclick="toggleBulkBtn()">
                            </td>
                            <td style="padding: 0.75rem 1rem; font-weight: 500;">
                                {{ $category->name }}
                            </td>
                            <td style="padding: 0.75rem 1rem; color: #64748b; font-size: 0.85rem;">
                                {{ $category->recipes()->count() }} Recipes
                            </td>
                            <td
                                style="padding: 0.75rem 1rem; text-align: right; display: flex; justify-content: flex-end; gap: 0.5rem;">
                                <a href="{{ route('categories.edit', $category) }}"
                                    style="font-size: 0.85rem; color: #3b82f6; text-decoration: none; display: inline-flex; align-items: center;">
                                    <i data-lucide="edit" style="width: 1rem; height: 1rem; margin-right: 0.25rem;"></i> Edit
                                </a>
                                <button type="button" onclick="confirmDelete('{{ $category->id }}')"
                                    style="font-size: 0.85rem; color: #ef4444; background: none; border: none; cursor: pointer; display: inline-flex; align-items: center;">
                                    <i data-lucide="trash-2" style="width: 1rem; height: 1rem; margin-right: 0.25rem;"></i>
                                    Delete
                                </button>

                                <form id="delete-form-{{ $category->id }}" action="{{ route('categories.destroy', $category) }}"
                                    method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding: 2rem; text-align: center; color: #94a3b8;">
                                No categories found. Create one to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </form>
    </div>

    <script>
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            toggleBulkBtn();
        }

        function toggleBulkBtn() {
            const checkboxes = document.querySelectorAll('.item-checkbox:checked');
            const btn = document.getElementById('bulkDeleteBtn');
            btn.style.display = checkboxes.length > 0 ? 'inline-block' : 'none';
        }

        function submitBulkDelete() {
            if (confirm('Are you sure you want to delete the selected categories? Categories used in recipes will be skipped.')) {
                document.getElementById('bulkDeleteForm').submit();
            }
        }

        function confirmDelete(id) {
            if (confirm('Delete this category? This cannot be undone.')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }
    </script>
@endsection