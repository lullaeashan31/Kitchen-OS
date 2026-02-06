@extends('layouts.app')

@section('header')
    <h1 class="text-2xl font-bold text-gray-800">Log New Purchase</h1>
    <p class="text-sm text-gray-500">Record incoming stock and update inventory.</p>
@endsection

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.purchases.store') }}" method="POST" id="purchaseForm">
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
                                <th class="pb-3 w-2/12">Total Price</th>
                                <th class="pb-3 w-2/12 text-right">Unit Price</th>
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
            <option value="{{ $item->id }}" data-unit="{{ $item->measurement_unit }}">
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
                <div class="relative">
                    <input type="number" name="items[INDEX][quantity]"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none quantity-input"
                        step="0.001" min="0" required placeholder="0">
                    <span class="absolute right-3 top-2 text-xs text-gray-400 unit-label"></span>
                </div>
            </td>
            <td class="py-3 px-2 align-top">
                <input type="number" name="items[INDEX][total_price]"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none price-input"
                    step="0.01" min="0" required placeholder="0.00">
            </td>
            <td class="py-3 pl-2 align-top text-right">
                <input type="text"
                    class="w-full px-3 py-2 bg-transparent text-right font-mono text-gray-600 unit-price-display border-none focus:ring-0"
                    readonly value="0.00">
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
        /* Hide disabled options in Tom Select to fulfill "already selected hid ho jaye" */
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
                    updateUnitLabel(tr);
                    updateItemAvailability();
                },
                onInitialize: function () {
                    setTimeout(() => updateItemAvailability(), 100);
                }
            });

            // Listeners
            const qtyInput = tr.querySelector('.quantity-input');
            const priceInput = tr.querySelector('.price-input');

            qtyInput.addEventListener('input', () => calculateRowStats(tr));
            priceInput.addEventListener('input', () => calculateRowStats(tr));

            rowCount++;
            lucide.createIcons();
        }

        function removeRow(btn) {
            btn.closest('tr').remove();
            calculateGrandTotal();
            updateItemAvailability();
        }

        function calculateRowStats(row) {
            const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const total = parseFloat(row.querySelector('.price-input').value) || 0;
            const unitDisplay = row.querySelector('.unit-price-display');

            if (qty > 0) {
                unitDisplay.value = (total / qty).toFixed(2);
            } else {
                unitDisplay.value = '0.00';
            }
            calculateGrandTotal();
        }

        function updateUnitLabel(row) {
            const select = row.querySelector('.ingredient-select');
            const unitLabel = row.querySelector('.unit-label');
            if (!select.value) {
                unitLabel.innerText = '';
                return;
            }
            // Find option in original select (since TomSelect mirrors it)
            // Actually, TomSelect wraps options, but we can query the hidden select
            const option = select.querySelector(`option[value="${select.value}"]`);
            if (option) {
                unitLabel.innerText = option.dataset.unit || '';
            }
        }

        function calculateGrandTotal() {
            let total = 0;
            document.querySelectorAll('.price-input').forEach(input => {
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