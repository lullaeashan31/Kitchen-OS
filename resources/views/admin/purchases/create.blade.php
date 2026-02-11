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
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Date</label>
                        <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Vendor Name</label>
                        <input type="text" name="vendor" placeholder="e.g. Fresh Farms Ltd." required
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Invoice Photo</label>
                        <input type="file" name="invoice_photo" accept="image/*" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Goods Photo</label>
                        <input type="file" name="goods_photo" accept="image/*" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none transition-all">
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
                                    <th class="pb-3 w-5/12">Inventory Item</th>
                                    <th class="pb-3 w-2/12">Quantity</th>
                                    <th class="pb-3 w-2/12">Unit Price</th>
                                    <th class="pb-3 w-2/12 text-right">Total Price</th>
                                    <th class="pb-3 w-1/12"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody" class="divide-y divide-gray-50">
                                <!-- Rows added via JS -->
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
                        <input type="number" name="items[INDEX][quantity]"
                            class="w-2/3 px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none quantity-input"
                            step="0.001" min="0" required placeholder="0">
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
            let rowCount = 0;
            const ingredientOptionsHTML = document.getElementById('ingredientOptions').innerHTML;

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
                addItemRow();
            });
        </script>
    @endpush