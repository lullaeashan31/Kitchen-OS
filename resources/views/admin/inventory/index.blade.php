@extends('layouts.app')

@section('header')
    <h1 style="font-size: 1.5rem; font-weight: 600;">Master Inventory</h1>
    <p style="color: var(--text-muted);">Manage stock levels, prices, and low stock alerts.</p>
@endsection

@section('actions')
    <a href="{{ route('admin.inventory.upload') }}" class="btn btn-secondary" style="margin-right: 0.5rem; padding: 0.5rem 1rem; background: var(--secondary-color); color: white; border-radius: 0.375rem; text-decoration: none;">
        <i data-lucide="upload" style="width: 1rem; height: 1rem; display: inline-block;"></i> Upload Excel
    </a>
    <button onclick="submitBulkDelete()" class="btn btn-danger" style="margin-right: 0.5rem; padding: 0.5rem 1rem; background: #ef4444; color: white; border: none; border-radius: 0.375rem; cursor: pointer; display: none;" id="bulkDeleteBtn">
        <i data-lucide="trash-2" style="width: 1rem; height: 1rem; display: inline-block;"></i> Delete Selected
    </button>
    <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; background: var(--primary-color); color: white; border-radius: 0.375rem; text-decoration: none;">
        <i data-lucide="plus" style="width: 1rem; height: 1rem; display: inline-block;"></i> Add Purchase
    </a>
@endsection

@section('content')
    <div style="background: white; border-radius: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); overflow: hidden;">
        <form id="bulkDeleteForm" action="{{ route('admin.inventory.bulk_destroy') }}" method="POST">
            @csrf
            @method('DELETE')
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 0.75rem 1rem; width: 40px;">
                            <input type="checkbox" id="selectAll" onclick="toggleSelectAll()">
                        </th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Item Name</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Category</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Total Purchased</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Total Used</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Current Stock</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Unit</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Price / Unit</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Status</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventory as $item)
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 0.75rem 1rem;">
                                <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="item-checkbox" onclick="toggleBulkBtn()">
                            </td>
                            <td style="padding: 0.75rem 1rem; font-weight: 500;">
                                {{ $item->name }}
                                <div style="font-size: 0.75rem; color: #94a3b8;">{{ $item->vendor ?? 'No Vendor' }}</div>
                            </td>
                            <td style="padding: 0.75rem 1rem;">{{ $item->category->name ?? '-' }}</td>
                            <td style="padding: 0.75rem 1rem; text-align: right; color: #16a34a; font-weight: 500;">
                                {{ number_format($item->total_purchased, 3) }}
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right; color: #ef4444; font-weight: 500;">
                                {{ number_format($item->total_used, 3) }}
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; {{ $item->current_stock <= $item->alert_threshold ? 'color: #ef4444;' : '' }}">
                                {{ number_format($item->current_stock, 3) }}
                            </td>
                            <td style="padding: 0.75rem 1rem;">{{ $item->measurement_unit }}</td>
                            <td style="padding: 0.75rem 1rem; text-align: right;">{{ number_format($item->price, 2) }}</td>
                            <td style="padding: 0.75rem 1rem;">
                                @if($item->current_stock <= $item->alert_threshold)
                                    <span style="display: inline-flex; align-items: center; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background: #fef2f2; color: #dc2626;">
                                        Low Stock
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background: #f0fdf4; color: #16a34a;">
                                        OK
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right;">
                                <a href="{{ route('admin.inventory.show', $item) }}" 
                                   style="font-size: 0.85rem; color: #64748b; margin-right: 0.75rem; text-decoration: none;">
                                    History
                                </a>
                                <a href="{{ route('ingredients.edit', $item) }}" 
                                   style="font-size: 0.85rem; color: #3b82f6; margin-right: 0.75rem; text-decoration: none;">
                                    Edit
                                </a>
                                <button type="button" onclick="openAdjustModal('{{ $item->id }}', '{{ addslashes($item->name) }}', '{{ $item->current_stock }}')" 
                                    style="font-size: 0.85rem; color: #3b82f6; background: none; border: none; cursor: pointer; text-decoration: underline;">
                                    Adjust
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="padding: 2rem; text-align: center; color: #94a3b8;">
                                No inventory items found. Please upload Excel or add items.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </form>
    </div>

    <!-- Adjust Modal -->
    <div id="adjustModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 50; align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: 0.5rem; width: 400px; max-width: 90%;">
            <h3 style="margin-top: 0; font-size: 1.25rem; font-weight: 600;">Adjust Stock: <span id="modalItemName"></span></h3>
            <p style="color: #64748b; font-size: 0.9rem;">Current Stock: <span id="modalCurrentStock"></span></p>
            
            <form id="adjustForm" method="POST" action="">
                @csrf
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">Adjustment Quantity (+ or -)</label>
                    <input type="number" step="0.001" name="adjustment_quantity" required 
                        style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    <small style="color: #64748b;">Enter negative value to reduce stock.</small>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">Reason</label>
                    <input type="text" name="reason" required placeholder="e.g. Spillage, Audit Correction"
                        style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                    <button type="button" onclick="closeAdjustModal()" 
                        style="padding: 0.5rem 1rem; border: 1px solid #d1d5db; background: white; color: #374151; border-radius: 0.375rem; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit" 
                        style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 0.375rem; cursor: pointer;">
                        Save Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAdjustModal(id, name, stock) {
            document.getElementById('modalItemName').textContent = name;
            document.getElementById('modalCurrentStock').textContent = stock;
            document.getElementById('adjustForm').action = "/admin/inventory/" + id + "/adjust";
            document.getElementById('adjustModal').style.display = 'flex';
        }

        function closeAdjustModal() {
            document.getElementById('adjustModal').style.display = 'none';
        }

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
            if (confirm('Are you sure you want to delete the selected items? Items used in recipes will be skipped.')) {
                document.getElementById('bulkDeleteForm').submit();
            }
        }
    </script>
@endsection
