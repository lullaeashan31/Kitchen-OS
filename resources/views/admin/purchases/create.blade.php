@extends('layouts.app')

@section('header')
    <h1 class="text-2xl font-bold text-gray-800">Log New Purchase</h1>
    <p class="text-sm text-gray-500">Record incoming stock and update inventory.</p>
@endsection

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm" enctype="multipart/form-data">
                @csrf

                <!-- Header Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" required
                            class="w-full px-4 py-2 rounded-lg border {{ $errors->has('purchase_date') ? 'border-red-500' : 'border-gray-200' }} focus:border-blue-500 outline-none transition-all">
                        @error('purchase_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Vendor Name <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <div class="w-full relative {{ $errors->has('vendor_id') ? 'border border-red-500 rounded-lg' : '' }}">
                                <select name="vendor_id" id="vendorSelect" required placeholder="Select or search vendor..."
                                    class="w-full rounded-lg border border-gray-200">
                                    <option value="">Select Vendor...</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" onclick="openVendorModal()"
                                class="px-3 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors"
                                title="Add New Vendor">
                                <i data-lucide="plus" class="w-5 h-5"></i>
                            </button>
                        </div>
                        @error('vendor_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Invoice Photo <span class="text-red-500">*</span></label>
                        <input type="file" name="invoice_photo" accept="image/*" required
                            class="w-full px-4 py-2 rounded-lg border {{ $errors->has('invoice_photo') ? 'border-red-500' : 'border-gray-200' }} focus:border-blue-500 outline-none transition-all">
                        @error('invoice_photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Goods Photo <span class="text-red-500">*</span></label>
                        <input type="file" name="goods_photo" accept="image/*" required
                            class="w-full px-4 py-2 rounded-lg border {{ $errors->has('goods_photo') ? 'border-red-500' : 'border-gray-200' }} focus:border-blue-500 outline-none transition-all">
                        @error('goods_photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Items Table -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <i data-lucide="shopping-cart" class="w-5 h-5 text-blue-500"></i> Items
                        </h2>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="addItemRow()">
                            <i data-lucide="plus"></i> Add Item
                        </button>
                    </div>

                    <div class="overflow-visible">
                        <table class="w-full text-left" id="itemsTable">
                            <thead>
                                <tr class="text-xs font-bold text-gray-500 uppercase border-b border-gray-100">
                                    <th class="pb-3 w-5/12">Inventory Item <span class="text-red-500">*</span></th>
                                    <th class="pb-3 w-2/12">Quantity <span class="text-red-500">*</span></th>
                                    <th class="pb-3 w-2/12">Unit Price</th>
                                    <th class="pb-3 w-2/12 text-right">Total Price</th>
                                    <th class="pb-3 w-1/12"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody" class="divide-y divide-gray-50">
                                @if(old('items'))
                                    @foreach(old('items') as $index => $item)
                                        <tr class="item-row group">
                                            <td class="py-3 pr-2 align-top">
                                                <div class="{{ $errors->has('items.'.$index.'.ingredient_id') ? 'border border-red-500 rounded-lg' : '' }}">
                                                <select name="items[{{ $index }}][ingredient_id]" class="ingredient-select w-full" required
                                                    placeholder="Search item...">
                                                    <option value="">Select item...</option>
                                                    @foreach($ingredients as $ing)
                                                        <option value="{{ $ing->id }}" 
                                                            data-unit="{{ $ing->measurement_unit }}"
                                                            data-price="{{ $ing->latest_price ?? $ing->price ?? 0 }}"
                                                            {{ ($item['ingredient_id'] ?? '') == $ing->id ? 'selected' : '' }}>
                                                            {{ $ing->name }} (Cur: {{ $ing->current_stock }} {{ $ing->measurement_unit }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                </div>
                                                @error('items.'.$index.'.ingredient_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </td>
                                            <td class="py-3 px-2 align-top">
                                                <div class="flex gap-2">
                                                    <div class="w-2/3">
                                                    <input type="number" name="items[{{ $index }}][quantity]"
                                                        class="w-full px-3 py-2 rounded-lg border {{ $errors->has('items.'.$index.'.quantity') ? 'border-red-500' : 'border-gray-200' }} focus:border-blue-500 outline-none quantity-input"
                                                        step="0.001" min="0" required placeholder="0" value="{{ $item['quantity'] ?? '' }}">
                                                        @error('items.'.$index.'.quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                                    </div>
                                                    <select name="items[{{ $index }}][unit]"
                                                        class="w-1/3 px-2 py-2 rounded-lg border {{ $errors->has('items.'.$index.'.unit') ? 'border-red-500' : 'border-gray-200' }} focus:border-blue-500 outline-none unit-select bg-white text-sm"
                                                        required>
                                                        <!-- Options populated via JS on init, but we can set default logic here if possible. 
                                                             However, since unit options depend on ingredient, it's safer to let JS handle dynamic population based on selected ingredient.
                                                             But for 'old' value, we might need to pre-populate. 
                                                             Better: Let JS 'initRow' handle populating options based on selected ingredient, 
                                                             then select the correct unit. -->
                                                    </select>
                                                    <!-- Hidden input to store old unit for JS to pick up -->
                                                    <input type="hidden" class="old-unit" value="{{ $item['unit'] ?? '' }}">
                                                </div>
                                            </td>

                                            <td class="py-3 px-2 align-top">
                                                <input type="number" name="items[{{ $index }}][unit_price]"
                                                    class="w-full px-3 py-2 rounded-lg border {{ $errors->has('items.'.$index.'.unit_price') ? 'border-red-500' : 'border-gray-200' }} focus:border-blue-500 outline-none unit-price-input"
                                                    step="0.01" min="0" placeholder="0.00" value="{{ $item['unit_price'] ?? '' }}">
                                                <span class="text-xs text-gray-400 block mt-1 text-right">Per <span class="unit-text">Unit</span></span>
                                                @error('items.'.$index.'.unit_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </td>

                                            <td class="py-3 px-2 align-top text-right">
                                                <input type="number" name="items[{{ $index }}][total_price]"
                                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-right font-bold text-gray-700 total-price-input outline-none"
                                                    step="0.01" min="0" placeholder="0.00" value="{{ $item['total_price'] ?? '' }}">
                                            </td>

                                            <td class="py-3 pl-2 align-top text-right">
                                                <button type="button" class="p-2 text-gray-400 hover:text-red-500 transition-colors"
                                                    onclick="removeRow(this)">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                <!-- Rows added via JS appear here -->
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 p-4 bg-gray-50 rounded-lg flex justify-end items-center border border-gray-100 gap-4">
                        <span class="text-sm font-medium text-gray-600">Total Purchase Value:</span>
                        <span class="text-xl font-bold text-gray-800">₹<span id="grandTotalDisplay">0.00</span></span>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="submit"
                        class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-5 h-5"></i> Save Purchases
                    </button>
                </div>
            </form>
        </div>

        <!-- Hidden Options for Tom Select -->
        <div id="ingredientOptions" style="display: none;">
            @foreach($ingredients as $item)
                <option value="{{ $item->id }}" data-unit="{{ $item->measurement_unit }}"
                    data-price="{{ $item->latest_price ?? $item->price ?? 0 }}">
                    {{ $item->name }} (Cur: {{ $item->current_stock }} {{ $item->measurement_unit }})
                </option>
            @endforeach
        </div>

        <!-- Row Template -->
        <template id="itemRowTemplate">
            <tr class="item-row group">
                <td class="py-3 pr-2 align-top">
                    <select name="items[INDEX][ingredient_id]" class="ingredient-select w-full" required
                        placeholder="Search item...">
                        <option value="">Select item...</option>
                    </select>
                </td>
                <td class="py-3 px-2 align-top">
                    <div class="flex gap-2">
                        <div class="w-2/3">
                        <input type="number" name="items[INDEX][quantity]"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none quantity-input"
                            step="0.001" min="0" required placeholder="0">
                        </div>
                        <select name="items[INDEX][unit]"
                            class="w-1/3 px-2 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none unit-select bg-white text-sm"
                            required>
                            <!-- Populated by JS -->
                        </select>
                    </div>
                </td>

                <td class="py-3 px-2 align-top">
                    <input type="number" name="items[INDEX][unit_price]"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none unit-price-input"
                        step="0.01" min="0" placeholder="0.00">
                    <span class="text-xs text-gray-400 block mt-1 text-right">Per <span class="unit-text">Unit</span></span>
                </td>

                <td class="py-3 px-2 align-top text-right">
                    <input type="number" name="items[INDEX][total_price]"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-right font-bold text-gray-700 total-price-input outline-none"
                        step="0.01" min="0" placeholder="0.00">
                </td>

                <td class="py-3 pl-2 align-top text-right">
                    <button type="button" class="p-2 text-gray-400 hover:text-red-500 transition-colors"
                        onclick="removeRow(this)">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </td>
            </tr>
        </template>
        <!-- Vendor Modal -->
        <div id="vendorModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeVendorModal()"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Add New Vendor</h3>
                                <div class="mt-2">
                                    <form id="vendorForm">
                                        @csrf
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700">Name</label>
                                            <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2">
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700">Contact Person (Optional)</label>
                                            <input type="text" name="contact_person" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2">
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700">Phone (Optional)</label>
                                            <input type="text" name="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2">
                                        </div>
                                        <div id="vendorError" class="text-red-500 text-sm hidden mb-2"></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="button" onclick="submitVendor()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Save Vendor
                        </button>
                        <button type="button" onclick="closeVendorModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
@endsection

    @push('scripts')
        <style>
            /* Hide disabled options in Tom Select */
            .ts-dropdown .option[data-selectable].disabled,
            .ts-dropdown .option.disabled {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                opacity: 0 !important;
            }
        </style>
        <script>
            let rowCount = {{ count(old('items', [])) }};
            const ingredientOptionsHTML = document.getElementById('ingredientOptions').innerHTML;
            let vendorSelect;

            // Unit Factors (Base to Unit)
            const UNIT_FACTORS = {
                'g': 1, 'kg': 1000, 'oz': 28.35, 'lb': 453.6,
                'ml': 1, 'l': 1000, 'cup': 240, 'tbsp': 15, 'tsp': 5,
                'pcs': 1
            };

            // Unit Groups
            const UNIT_GROUPS = {
                'weight': ['g', 'kg', 'oz', 'lb'],
                'volume': ['ml', 'l', 'cup', 'tbsp', 'tsp'],
                'piece': ['pcs']
            };
            const UNIT_LABELS = {
                'g': 'Gram (g)', 'kg': 'Kg', 'oz': 'Oz', 'lb': 'Lb',
                'ml': 'ml', 'l': 'Liter', 'cup': 'Cup', 'tbsp': 'Tbsp', 'tsp': 'Tsp',
                'pcs': 'Pcs'
            };

            function getUnitGroup(unit) {
                if (UNIT_GROUPS.weight.includes(unit)) return UNIT_GROUPS.weight;
                if (UNIT_GROUPS.volume.includes(unit)) return UNIT_GROUPS.volume;
                return UNIT_GROUPS.piece;
            }

            function addItemRow() {
                const template = document.getElementById('itemRowTemplate');
                const tbody = document.getElementById('itemsBody');
                const clone = template.content.cloneNode(true);
                const tr = clone.querySelector('tr');

                // Replace INDEX
                const inputs = tr.querySelectorAll('input, select');
                inputs.forEach(input => {
                    if (input.name) input.name = input.name.replace('INDEX', rowCount);
                });

                // Populate Select
                const select = tr.querySelector('.ingredient-select');
                select.innerHTML += ingredientOptionsHTML;

                tbody.appendChild(tr);

                // Init Tom Select
                new TomSelect(select, {
                    create: false,
                    sortField: { field: "text", direction: "asc" },
                    placeholder: 'Search item...',
                    plugins: ['dropdown_input'],
                    onChange: function (value) {
                        updateRowDetails(tr, value);
                        updateItemAvailability();
                        calculateTotalFromPrice(tr);
                    },
                    onInitialize: function () {
                        setTimeout(() => updateItemAvailability(), 100);
                    }
                });

                // Listeners
                const qtyInput = tr.querySelector('.quantity-input');
                const unitPriceInput = tr.querySelector('.unit-price-input');
                const totalPriceInput = tr.querySelector('.total-price-input');
                const unitSelect = tr.querySelector('.unit-select');

                qtyInput.addEventListener('input', () => calculateTotalFromPrice(tr));
                unitPriceInput.addEventListener('input', () => calculateTotalFromPrice(tr));
                totalPriceInput.addEventListener('input', () => calculatePriceFromTotal(tr)); // Reverse Calc

                // When unit changes, update label AND recalculate price suggestion
                unitSelect.addEventListener('change', () => {
                    updateUnitBasedPrice(tr);
                    calculateTotalFromPrice(tr);
                });

                rowCount++;
                lucide.createIcons();
            }

            function removeRow(btn) {
                btn.closest('tr').remove();
                calculateGrandTotal();
                updateItemAvailability();
            }

            function updateRowDetails(row, value) {
                const unitSelect = row.querySelector('.unit-select');
                const unitPriceInput = row.querySelector('.unit-price-input');

                if (!value) {
                    unitSelect.innerHTML = '';
                    unitPriceInput.value = '';
                    row.querySelector('.unit-text').textContent = 'Unit';
                    return;
                }

                // Get details
                const option = document.querySelector(`#ingredientOptions option[value="${value}"]`);
                if (option) {
                    const baseUnit = option.dataset.unit;
                    const group = getUnitGroup(baseUnit);

                    // Populate Unit Select
                    unitSelect.innerHTML = '';
                    group.forEach(u => {
                        const opt = document.createElement('option');
                        opt.value = u;
                        opt.text = UNIT_LABELS[u] || u;
                        if (u === baseUnit) opt.selected = true;
                        unitSelect.appendChild(opt);
                    });

                    // Update Price Suggestion
                    updateUnitBasedPrice(row);
                }
            }

            function updateUnitBasedPrice(row) {
                const select = row.querySelector('.ingredient-select');
                const unitSelect = row.querySelector('.unit-select');
                const unitPriceInput = row.querySelector('.unit-price-input');
                const unitText = row.querySelector('.unit-text');

                if (!select.value) return;

                const option = document.querySelector(`#ingredientOptions option[value="${select.value}"]`);
                const selectedUnit = unitSelect.value;
                const basePrice = parseFloat(option.dataset.price) || 0;

                // Update Label
                unitText.textContent = unitSelect.options[unitSelect.selectedIndex]?.text || 'Unit';

                // Calculate Scaled Price (Base Price * Factor)
                const factor = UNIT_FACTORS[selectedUnit] || 1;
                const scaledPrice = basePrice * factor;

                // Update Input (Always update to suggested price to fix "Zero" issue)
                unitPriceInput.value = scaledPrice > 0 ? scaledPrice.toFixed(2) : '';
            }

            function calculateTotalFromPrice(row) {
                const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const unitPrice = parseFloat(row.querySelector('.unit-price-input').value) || 0;
                const totalInput = row.querySelector('.total-price-input');

                const total = qty * unitPrice;
                // Only update if one of the inputs changed (to avoid loop/rounding fights, though explicit event handler separation handles this)
                totalInput.value = total > 0 ? total.toFixed(2) : '';

                calculateGrandTotal();
            }

            function calculatePriceFromTotal(row) {
                const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const total = parseFloat(row.querySelector('.total-price-input').value) || 0;
                const unitPriceInput = row.querySelector('.unit-price-input');

                if (qty > 0) {
                    const unitPrice = total / qty;
                    unitPriceInput.value = unitPrice.toFixed(2);
                }

                calculateGrandTotal();
            }

            function calculateGrandTotal() {
                let total = 0;
                document.querySelectorAll('.total-price-input').forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                document.getElementById('grandTotalDisplay').innerText = total.toFixed(2);
            }

            function updateItemAvailability() {
                const allSelects = document.querySelectorAll('.ingredient-select');
                const selectedValues = Array.from(allSelects).map(s => s.value).filter(v => v);

                allSelects.forEach(select => {
                    if (!select.tomselect) return;
                    const ts = select.tomselect;
                    const myValue = select.value;

                    Object.keys(ts.options).forEach(optVal => {
                        if (optVal === myValue) return;

                        const shouldDisable = selectedValues.includes(optVal);
                        const opt = ts.options[optVal];

                        if (opt.disabled !== shouldDisable) {
                            ts.updateOption(optVal, { disabled: shouldDisable });
                        }
                    });
                    ts.clearCache();
                    ts.refreshOptions(false);
                });
            }

            document.addEventListener('DOMContentLoaded', () => {
                // Initialize existing rows (from validation errors)
                const existingRows = document.querySelectorAll('#itemsBody .item-row');
                if (existingRows.length > 0) {
                    existingRows.forEach(row => {
                         // We need to re-init TomSelect and listeners
                         const select = row.querySelector('.ingredient-select');
                         
                         // Init Tom Select
                         new TomSelect(select, {
                            create: false,
                            sortField: { field: "text", direction: "asc" },
                            placeholder: 'Search item...',
                            plugins: ['dropdown_input'],
                            onChange: function (value) {
                                // For existing rows, value is already set, but this handles changes
                                updateRowDetails(row, value);
                                updateItemAvailability();
                                calculateTotalFromPrice(row);
                            },
                         });

                         // Initialize listeners
                        const qtyInput = row.querySelector('.quantity-input');
                        const unitPriceInput = row.querySelector('.unit-price-input');
                        const totalPriceInput = row.querySelector('.total-price-input');
                        const unitSelect = row.querySelector('.unit-select');

                        qtyInput.addEventListener('input', () => calculateTotalFromPrice(row));
                        unitPriceInput.addEventListener('input', () => calculateTotalFromPrice(row));
                        totalPriceInput.addEventListener('input', () => calculatePriceFromTotal(row)); // Reverse Calc

                        unitSelect.addEventListener('change', () => {
                            updateUnitBasedPrice(row);
                            calculateTotalFromPrice(row);
                        });

                        // Populate unit options if value exists
                        if(select.value) {
                             updateRowDetails(row, select.value);
                             // Restore selected unit
                             const oldUnit = row.querySelector('.old-unit').value;
                             if(oldUnit) unitSelect.value = oldUnit;
                        }
                    });
                    lucide.createIcons();
                    calculateGrandTotal();
                    setTimeout(updateItemAvailability, 100);
                } else {
                    // No existing rows, add one empty one
                    addItemRow();
                }
                
                // Init Vendor TomSelect
                vendorSelect = new TomSelect('#vendorSelect', {
                    create: false,
                    sortField: { field: "text", direction: "asc" },
                    placeholder: 'Select or search vendor...',
                    plugins: ['dropdown_input'],
                });
            });

            function openVendorModal() {
                document.getElementById('vendorModal').classList.remove('hidden');
            }

            function closeVendorModal() {
                document.getElementById('vendorModal').classList.add('hidden');
                document.getElementById('vendorForm').reset();
                document.getElementById('vendorError').classList.add('hidden');
            }

            function submitVendor() {
                const form = document.getElementById('vendorForm');
                const formData = new FormData(form);

                fetch("{{ route('vendors.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Add to TomSelect
                        vendorSelect.addOption({value: data.vendor.id, text: data.vendor.name});
                        vendorSelect.addItem(data.vendor.id);
                        closeVendorModal();
                        // alert(data.message);
                    } else {
                        throw new Error(data.message || 'Validation failed');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const errorDiv = document.getElementById('vendorError');
                    errorDiv.textContent = error.message;
                    if (error.message.includes('The name has already been taken')) {
                         errorDiv.textContent = 'Vendor name already exists.';
                    }
                    errorDiv.classList.remove('hidden');
                });
            }
        </script>
    @endpush