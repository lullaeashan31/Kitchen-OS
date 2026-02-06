@extends('layouts.app')

@section('header')
    <h1 style="font-size: 1.5rem; font-weight: 600;">Upload Master Inventory</h1>
    <p style="color: var(--text-muted);">Bulk update or create inventory items via Excel.</p>
@endsection

@section('content')
    <div
        style="background: white; padding: 2rem; border-radius: 0.5rem; max-width: 600px; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);">

        <div
            style="margin-bottom: 2rem; padding: 1.5rem; background: #f8fafc; border-radius: 0.75rem; border: 1px solid #e2e8f0; box-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="font-weight: 700; font-size: 1rem; color: #1e293b;">Format Guide</h3>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="{{ route('excel.inventory_template') }}" class="btn btn-secondary btn-sm"
                        style="font-size: 0.75rem; display: flex; align-items: center; gap: 0.25rem; background: white; border: 1px solid #cbd5e1; color: #334155; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-weight: 600;">
                        <i data-lucide="download" style="width: 14px; height: 14px;"></i> Download Excel Template
                    </a>
                </div>
            </div>

            <p style="font-size: 0.875rem; color: #64748b; margin-bottom: 1rem;">
                Please ensure your file follows the structure shown below. You can download the pre-formatted Excel
                template to get started quickly.
            </p>

            <div
                style="background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; overflow: hidden; margin-bottom: 1rem;">
                <img src="/home/prajapati/.gemini/antigravity/brain/a6108b41-83f7-4d29-bf97-0e3b526f731c/inventory_upload_sample_format_1770274225288.png"
                    alt="Sample Format" style="width: 100%; height: auto; display: block;">
            </div>

            <h4 style="font-weight: 600; font-size: 0.875rem; margin-bottom: 0.5rem; color: #334155;">Expected Columns:
            </h4>
            <ul
                style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; list-style: none; padding: 0; font-size: 0.8rem; color: #475569;">
                <li style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="width: 6px; height: 6px; background: #3b82f6; border-radius: 50%;"></span>
                    <strong>item_name</strong> (Required)
                </li>
                <li style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="width: 6px; height: 6px; background: #e2e8f0; border-radius: 50%;"></span>
                    <strong>measurement_unit</strong>
                </li>
                <li style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="width: 6px; height: 6px; background: #e2e8f0; border-radius: 50%;"></span>
                    <strong>category</strong>
                </li>
                <li style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="width: 6px; height: 6px; background: #e2e8f0; border-radius: 50%;"></span>
                    <strong>price_per_unit</strong>
                </li>
                <li style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="width: 6px; height: 6px; background: #e2e8f0; border-radius: 50%;"></span>
                    <strong>vendor</strong>
                </li>
                <li style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="width: 6px; height: 6px; background: #e2e8f0; border-radius: 50%;"></span>
                    <strong>inventory_id</strong>
                </li>
            </ul>
        </div>

        <form action="{{ route('admin.inventory.import') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div style="margin-bottom: 1.5rem;">
                <label
                    style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Select
                    Excel or PDF File</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv,.pdf" required
                    style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem;">
                    Accepted formats: Excel (.xlsx, .xls, .csv) or PDF (.pdf)
                </p>
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