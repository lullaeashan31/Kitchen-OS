@extends('layouts.app')

@section('header')
    <h1 style="font-size: 1.5rem; font-weight: 600;">Upload Master Inventory</h1>
    <p style="color: var(--text-muted);">Bulk update or create inventory items via Excel.</p>
@endsection

@section('content')
    <div
        style="background: white; padding: 2rem; border-radius: 0.5rem; max-width: 600px; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);">

        <div
            style="margin-bottom: 2rem; padding: 1rem; background: #f8fafc; border-radius: 0.375rem; border: 1px solid #e2e8f0;">
            <h3 style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.5rem;">Expected Excel Columns</h3>
            <ul style="list-style: disc; padding-left: 1.5rem; font-size: 0.85rem; color: #475569;">
                <li><strong>item_name</strong> (Required)</li>
                <li><strong>category</strong></li>
                <li><strong>measurement_unit</strong> (e.g., kg, liter, pcs)</li>
                <li><strong>purchase_unit</strong> (e.g., bag, box)</li>
                <li><strong>price_per_unit</strong> (Numeric)</li>
                <li><strong>vendor</strong></li>
                <li><strong>minimum_stock_level</strong> (Alert threshold)</li>
                <li><strong>inventory_id</strong> (Optional - Use to update specific existing items)</li>
            </ul>
        </div>

        <form action="{{ route('admin.inventory.import') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div style="margin-bottom: 1.5rem;">
                <label
                    style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Select
                    Excel File</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                    style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary"
                    style="padding: 0.75rem 1.5rem; background: var(--primary-color); color: white; border: none; border-radius: 0.375rem; cursor: pointer; font-weight: 500;">
                    Upload & Process
                </button>
                <a href="{{ route('admin.inventory.index') }}"
                    style="padding: 0.75rem 1.5rem; color: #64748b; text-decoration: none; display: flex; align-items: center;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection