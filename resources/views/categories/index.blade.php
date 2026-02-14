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
    <!-- Search Bar -->
    <div style="background: white; border-radius: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); padding: 1rem; margin-bottom: 1rem;">
        <div style="position: relative;">
            <i data-lucide="search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); width: 1.25rem; height: 1.25rem; color: #94a3b8;"></i>
            <input type="text" id="categorySearch" placeholder="Search categories..." 
                   style="width: 100%; padding: 0.75rem 1rem 0.75rem 3rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.95rem; outline: none; transition: border-color 0.2s;"
                   oninput="filterCategories()"
                   onfocus="this.style.borderColor='#3b82f6';"
                   onblur="this.style.borderColor='#e2e8f0';">
        </div>
    </div>

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
                <tbody id="categoryTableBody">
                    @forelse($categories as $category)
                        <tr class="category-row" data-category-name="{{ strtolower($category->name) }}" style="border-bottom: 1px solid #e2e8f0;">
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
                            <td style="padding: 0.75rem 1rem; text-align: right;">
                                <div style="display: flex; justify-content: flex-end; gap: 0.5rem; align-items: center;">
                                    <a href="{{ route('categories.edit', $category) }}"
                                        style="font-size: 0.85rem; color: #3b82f6; text-decoration: none; display: inline-flex; align-items: center;">
                                        <i data-lucide="edit" style="width: 1rem; height: 1rem; margin-right: 0.25rem;"></i> Edit
                                    </a>
                                    <form id="delete-form-{{ $category->id }}" action="{{ route('categories.destroy', $category) }}"
                                        method="POST" style="display: inline;" onsubmit="return confirm('Delete this category? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            style="font-size: 0.85rem; color: #ef4444; background: none; border: none; cursor: pointer; display: inline-flex; align-items: center; padding: 0;">
                                            <i data-lucide="trash-2" style="width: 1rem; height: 1rem; margin-right: 0.25rem;"></i>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="noCategoriesRow">
                            <td colspan="4" style="padding: 2rem; text-align: center; color: #94a3b8;">
                                No categories found. Create one to get started.
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noResultsRow" style="display: none;">
                        <td colspan="4" style="padding: 2rem; text-align: center; color: #94a3b8;">
                            No categories match your search.
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>

    <script>
        lucide.createIcons();

        function filterCategories() {
            const searchInput = document.getElementById('categorySearch');
            const searchTerm = searchInput.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.category-row');
            const noResultsRow = document.getElementById('noResultsRow');
            const noCategoriesRow = document.getElementById('noCategoriesRow');
            
            let visibleCount = 0;

            rows.forEach(row => {
                const categoryName = row.getAttribute('data-category-name');
                
                if (searchTerm === '' || categoryName.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide "no results" message
            if (searchTerm !== '' && visibleCount === 0) {
                noResultsRow.style.display = '';
                if (noCategoriesRow) noCategoriesRow.style.display = 'none';
            } else {
                noResultsRow.style.display = 'none';
                if (noCategoriesRow && rows.length === 0) {
                    noCategoriesRow.style.display = '';
                }
            }

            // Update select all checkbox state
            updateSelectAllState();
        }

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.item-checkbox:not([style*="display: none"])');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(cb => cb.checked = !allChecked);
            toggleBulkBtn();
            selectAll.checked = !allChecked;
        }

        function updateSelectAllState() {
            const selectAll = document.getElementById('selectAll');
            const visibleCheckboxes = document.querySelectorAll('.item-checkbox:not([style*="display: none"])');
            const checkedCheckboxes = document.querySelectorAll('.item-checkbox:checked:not([style*="display: none"])');
            
            if (visibleCheckboxes.length === 0) {
                selectAll.indeterminate = false;
                selectAll.checked = false;
            } else {
                selectAll.checked = checkedCheckboxes.length === visibleCheckboxes.length;
                selectAll.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < visibleCheckboxes.length;
            }
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


        // Initialize select all state
        document.addEventListener('DOMContentLoaded', function() {
            updateSelectAllState();
        });
    </script>
@endsection