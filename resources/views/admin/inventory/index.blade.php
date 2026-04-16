@extends('layouts.app')

@section('header')
    <h1 style="font-size: 1.5rem; font-weight: 600;">Master Inventory</h1>
    <p style="color: var(--text-muted);">Manage stock levels, prices, and low stock alerts.</p>
@endsection

@section('actions')
    <div class="flex gap-2">
        <a href="{{ route('excel.purchase_template') }}" class="btn btn-secondary" style="background: #10b981; border: none;">
            <i data-lucide="download" class="w-4 h-4 mr-1"></i> Purchase Sample
        </a>
        <a href="javascript:void(0)" id="salesUploadBtn" onclick="openSalesUploadModal()" class="btn btn-secondary" style="background: #8b5cf6; border: none; display: inline-flex; align-items: center; justify-content: center; padding: 0.5rem 1rem; border-radius: 0.375rem; color: white; text-decoration: none;">
            <i data-lucide="file-up" class="w-4 h-4 mr-1"></i> Upload Sales Report
        </a>
        <a href="{{ route('admin.inventory.upload') }}" class="btn btn-secondary">
            <i data-lucide="upload" class="w-4 h-4 mr-1"></i> Upload Master
        </a>
        <button onclick="submitBulkDelete()" class="btn btn-danger" style="display: none;" id="bulkDeleteBtn">
            <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Delete Selected
        </button>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
            <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Add Purchase
        </a>
    </div>
@endsection

@section('content')

    @if(session('import_errors'))
        <div class="card mb-6" style="border-left: 4px solid #f59e0b; background: #fffbeb;">
            <div class="flex items-center gap-2 mb-2 p-4 pb-0">
                <i data-lucide="alert-circle" class="text-amber-600 w-5 h-5"></i>
                <h4 class="font-bold text-amber-800">Import Errors Found:</h4>
            </div>
            <div class="px-4 pb-4 overflow-auto max-h-60">
                <table class="w-full text-xs text-amber-900 border-collapse">
                    <thead>
                        <tr>
                            <th class="text-left border-b border-amber-200 py-1">Row</th>
                            <th class="text-left border-b border-amber-200 py-1">Item</th>
                            <th class="text-left border-b border-amber-200 py-1">Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(session('import_errors') as $error)
                            <tr>
                                <td class="py-1 pr-4">{{ $error['row'] }}</td>
                                <td class="py-1 pr-4 font-bold">{{ $error['item'] }}</td>
                                <td class="py-1">{{ $error['error'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="card mb-6" style="margin-bottom: 1.5rem; padding: 1.25rem;">
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <!-- Search -->
            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold text-gray-500 uppercase">Search Item</label>
                <input type="text" name="search" class="form-control" placeholder="Name..." value="{{ request('search') }}">
            </div>

            <!-- Category -->
            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold text-gray-500 uppercase">Category</label>
                <select name="category_id" id="category-filter" class="form-control">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Storage Location -->
            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold text-gray-500 uppercase">Location</label>
                <select name="storage_location" id="location-filter" class="form-control">
                    <option value="">All Locations</option>
                    @foreach($storageLocations as $location)
                        <option value="{{ $location }}" {{ request('storage_location') == $location ? 'selected' : '' }}>
                            {{ $location }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Allergen -->
            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold text-gray-500 uppercase">Allergen</label>
                <select name="allergen" id="allergen-filter" class="form-control">
                    <option value="">All Allergens</option>
                    @foreach($allergens as $allergen)
                        <option value="{{ $allergen->value }}" {{ request('allergen') == $allergen->value ? 'selected' : '' }}>
                            {{ $allergen->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Stock Status -->
            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold text-gray-500 uppercase">Stock Status</label>
                <select name="stock_status" class="form-control">
                    <option value="">All Status</option>
                    <option value="ok" {{ request('stock_status') == 'ok' ? 'selected' : '' }}>OK</option>
                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>

            <!-- Sort & Actions -->
            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold text-gray-500 uppercase">Sort By</label>
                <div class="flex gap-2">
                    <select name="sort_by" class="form-control flex-1">
                        <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                        <option value="current_stock" {{ request('sort_by') == 'current_stock' ? 'selected' : '' }}>Stock</option>
                        <option value="price" {{ request('sort_by') == 'price' ? 'selected' : '' }}>Price</option>
                        <option value="category_name" {{ request('sort_by') == 'category_name' ? 'selected' : '' }}>Category</option>
                    </select>
                    <button type="submit" class="btn btn-primary p-2">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                    </button>
                    @if(request()->anyFilled(['search', 'category_id', 'storage_location', 'allergen', 'stock_status', 'sort_by']))
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary p-2" title="Clear Filters">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

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
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Vendor / Price / Purchased</th>
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
                            <td style="padding: 0.75rem 1rem; font-weight: 500;">{{ $item->name }}</td>
                            <td style="padding: 0.75rem 1rem;">{{ $item->category->name ?? '-' }}</td>
                            <td style="padding: 0.75rem 1rem; font-size: 0.8rem;">
                                @php $vendorSummary = $item->vendor_purchase_summary; @endphp
                                @if($vendorSummary->isEmpty())
                                    <span style="color: #94a3b8;">—</span>
                                @else
                                    @foreach($vendorSummary as $v)
                                        <div style="margin-bottom: 0.4rem; padding-bottom: 0.25rem; border-bottom: 1px dashed #f1f5f9; last-child: border-bottom: none;">
                                            <div style="font-weight: 700; color: #1e293b; font-size: 0.85rem;">{{ $v['name'] }}</div>
                                            <div class="flex items-center gap-1 mt-0.5">
                                                <span style="color: #475569; font-weight: 500;">
                                                    {{ number_format($v['purchase_quantity'], $v['purchase_quantity'] == (int)$v['purchase_quantity'] ? 0 : 1) }} {{ $v['purchase_unit'] }}
                                                </span>
                                                <span style="color: #94a3b8; font-size: 0.75rem;">(₹{{ number_format($v['unit_price'], 2) }}/{{ $v['purchase_unit'] }})</span>
                                                <span style="color: #cbd5e1; font-size: 0.75rem;">→</span>
                                                <span style="color: #16a34a; font-weight: 600; font-size: 0.75rem;">
                                                    {{ number_format($v['normalized_quantity'], 0) }} {{ $v['base_unit'] }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right; color: #16a34a; font-weight: 500;">
                                {{ number_format($item->total_purchased, 3) }}
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right; color: #ef4444; font-weight: 500;">
                                {{ number_format($item->total_used, 3) }}
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; {{ $item->current_stock_display <= $item->alert_threshold ? 'color: #ef4444;' : '' }}">
                                {{ number_format($item->current_stock_display, 3) }}
                            </td>
                            <td style="padding: 0.75rem 1rem;">{{ $item->measurement_unit }}</td>
                            <td style="padding: 0.75rem 1rem; text-align: right;">{{ number_format($item->price, 2) }}</td>
                            <td style="padding: 0.75rem 1rem;">
                                @if($item->current_stock_display <= $item->alert_threshold)
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
                                <button type="button" onclick="openAdjustModal('{{ $item->id }}', '{{ addslashes($item->name) }}', '{{ $item->current_stock_display }}')" 
                                    style="font-size: 0.85rem; color: #3b82f6; background: none; border: none; cursor: pointer; text-decoration: underline;">
                                    Adjust
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="padding: 2rem; text-align: center; color: #94a3b8;">
                                No inventory items found. Please upload Excel or add items.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </form>
    </div>

@push('modals')
    <!-- Adjust Modal -->
    <div id="adjustModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.6); z-index: 99999; overflow: auto;">
        <div style="min-height: 100%; display: flex; align-items: center; justify-content: center; padding: 2rem;">
            <div style="background: white; padding: 2rem; border-radius: 0.75rem; width: 450px; max-width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); margin: auto;">
                <h3 style="margin-top: 0; margin-bottom: 0.5rem; font-size: 1.25rem; font-weight: 600; color: #1e293b;">Adjust Stock: <span id="modalItemName"></span></h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1.5rem;">Current Stock: <strong id="modalCurrentStock" style="color: #3b82f6;"></strong></p>
                
                <form id="adjustForm" method="POST" action="">
                    @csrf
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Adjustment Quantity (+ or -)</label>
                        <input type="number" step="0.001" name="adjustment_quantity" required 
                            placeholder="e.g., 10 or -5"
                            style="width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 0.5rem; font-size: 1rem; transition: border-color 0.2s;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Enter positive to add, negative to reduce stock</small>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Reason</label>
                        <select name="reason" required
                            style="width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 0.5rem; font-size: 1rem; transition: border-color 0.2s;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                            <option value="" disabled selected>Select a reason...</option>
                            <option value="Audit correction">Audit correction</option>
                            <option value="Damage">Damage</option>
                            <option value="Opening balance fix">Opening balance fix</option>
                        </select>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                        <button type="button" onclick="closeAdjustModal()" 
                            style="padding: 0.75rem 1.5rem; border: 2px solid #e2e8f0; background: white; color: #64748b; border-radius: 0.5rem; cursor: pointer; font-weight: 600; transition: all 0.2s;"
                            onmouseover="this.style.backgroundColor='#f8fafc'; this.style.borderColor='#cbd5e1'" 
                            onmouseout="this.style.backgroundColor='white'; this.style.borderColor='#e2e8f0'">
                            Cancel
                        </button>
                        <button type="submit" 
                            style="padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; transition: background-color 0.2s;"
                            onmouseover="this.style.backgroundColor='#2563eb'" 
                            onmouseout="this.style.backgroundColor='#3b82f6'">
                            Save Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sales Upload Sales Modal -->
    <div id="salesUploadModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.6); z-index: 99999; overflow: auto;">
        <div style="min-height: 100%; display: flex; align-items: center; justify-content: center; padding: 2rem;">
            <div style="background: white; padding: 2rem; border-radius: 0.75rem; width: 450px; max-width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); margin: auto;">
                <div class="flex justify-between items-center mb-4">
                    <h3 style="margin: 0; font-size: 1.25rem; font-weight: 600; color: #1e293b;">Upload Sales Report</h3>
                    <button onclick="closeSalesUploadModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1.5rem;">
                    Upload an Excel/CSV file with <code>item_name</code> and <code>quantity_sold</code> to automatically adjust stock.
                    <br><br>
                    <a href="{{ route('excel.sales_template') }}" class="text-indigo-600 hover:underline inline-flex items-center gap-1 font-bold">
                        <i data-lucide="download" class="w-3 h-3"></i> Download Template
                    </a>
                </p>
                
                <form action="{{ route('admin.inventory.import_sales') }}" method="POST" enctype="multipart/form-data" onsubmit="this.querySelector('button[type=submit]').disabled = true; this.querySelector('button[type=submit]').innerHTML = 'Processing...';">
                    @csrf
                    <div style="margin-bottom: 1.5rem;">
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Select File (Excel or CSV)</label>
                        <div class="relative">
                            <input type="file" name="file" required 
                                class="w-full px-3 py-2 border-2 border-dashed border-gray-300 rounded-lg hover:border-indigo-400 focus:outline-none transition-colors"
                                accept=".xlsx,.xls,.csv">
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                        <button type="button" onclick="closeSalesUploadModal()" 
                            class="px-4 py-2 border-2 border-gray-200 text-gray-600 rounded-lg font-bold hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 shadow-md transition-all">
                            Adjust Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

    <div style="padding: 1rem; border-top: 1px solid #e2e8f0;">
        {{ $inventory->links() }}
    </div>

    @push('scripts')
    <script>
        function openAdjustModal(id, name, stock) {
            const modal = document.getElementById('adjustModal');
            if (modal) {
                document.getElementById('modalItemName').textContent = name;
                document.getElementById('modalCurrentStock').textContent = stock;
                document.getElementById('adjustForm').action = "/admin/inventory/" + id + "/adjust";
                modal.style.display = 'block';
            }
        }

        function closeAdjustModal() {
            document.getElementById('adjustModal').style.display = 'none';
        }

        function openSalesUploadModal() {
            const modal = document.getElementById('salesUploadModal');
            if (modal) {
                modal.style.display = 'block';
            } else {
                alert('Error: Modal not found. Please refresh page.');
            }
        }

        function closeSalesUploadModal() {
            document.getElementById('salesUploadModal').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.querySelector('form[action="{{ route('admin.inventory.index') }}"]');
            if (window.lucide) window.lucide.createIcons();

            const tomSelectElements = ['#category-filter', '#location-filter', '#allergen-filter'];
            tomSelectElements.forEach(selector => {
                const el = document.querySelector(selector);
                if (el && window.TomSelect) {
                    new TomSelect(selector, {
                        create: false,
                        allowEmptyOption: true,
                        plugins: ['remove_button'],
                        onChange: function() { if (filterForm) filterForm.submit(); }
                    });
                }
            });
            
            document.querySelectorAll('select[name="stock_status"], select[name="sort_by"]').forEach(el => {
                el.addEventListener('change', () => { if (filterForm) filterForm.submit(); });
            });
        });

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            toggleBulkBtn();
        }

        function toggleBulkBtn() {
            const checkboxes = document.querySelectorAll('.item-checkbox:checked');
            const btn = document.getElementById('bulkDeleteBtn');
            if (btn) btn.style.display = checkboxes.length > 0 ? 'inline-block' : 'none';
        }

        function submitBulkDelete() {
            if (confirm('Are you sure you want to delete the selected items? Items used in recipes will be skipped.')) {
                const form = document.getElementById('bulkDeleteForm');
                if (form) form.submit();
            }
        }
    </script>
    @endpush
@endsection
