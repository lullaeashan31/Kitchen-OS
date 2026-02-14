@extends('layouts.app')

@section('header')
    <h1 class="text-3xl font-bold text-gray-800">Kitchen Production</h1>
    <p class="text-sm text-gray-500">Log cooking sessions to automatically deduct inventory.</p>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <form action="{{ route('production.store') }}" method="POST" class="p-8">
                @csrf

                <div class="mb-8 text-center">
                    <div
                        class="w-20 h-20 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-orange-50">
                        <i data-lucide="chef-hat" class="w-10 h-10"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">What are you cooking?</h2>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Select Recipe</label>
                        <select name="recipe_id" id="recipe_id"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 outline-none bg-white font-medium"
                            required>
                            <option value="">-- Choose Approved Recipe --</option>
                            @foreach($recipes as $recipe)
                                <option value="{{ $recipe->id }}" data-yields="{{ $recipe->yields }}">
                                    {{ $recipe->name }} (Base: {{ $recipe->yields }} Portions)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <label class="block text-sm font-bold text-gray-700" id="qtyLabel">Quantity</label>
                            <div class="flex bg-white rounded-lg p-1 border border-gray-200">
                                <label
                                    class="cursor-pointer px-3 py-1 rounded-md text-sm font-medium transition-colors bg-orange-100 text-orange-700"
                                    id="lbl-batches">
                                    <input type="radio" name="unit_type" value="batches" class="hidden" checked
                                        onchange="toggleUnit('batches')">
                                    Batches
                                </label>
                                <label
                                    class="cursor-pointer px-3 py-1 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors"
                                    id="lbl-portions">
                                    <input type="radio" name="unit_type" value="portions" class="hidden"
                                        onchange="toggleUnit('portions')">
                                    Portions
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="button" onclick="adjustAmount(-1)"
                                class="w-12 h-12 rounded-lg bg-white border border-gray-200 text-gray-500 hover:text-orange-600 hover:border-orange-300 transition-colors flex items-center justify-center text-xl font-bold">-</button>
                            <input type="number" name="quantity" id="amount" value="1" step="0.1" min="0.1"
                                class="flex-1 text-center text-2xl font-bold bg-transparent border-none focus:ring-0 p-2"
                                required>
                            <button type="button" onclick="adjustAmount(1)"
                                class="w-12 h-12 rounded-lg bg-white border border-gray-200 text-gray-500 hover:text-orange-600 hover:border-orange-300 transition-colors flex items-center justify-center text-xl font-bold">+</button>
                        </div>
                        <p class="text-center text-xs text-gray-400 mt-2" id="helper-text">Produces 1 Batch (Base Yield)</p>
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white font-bold rounded-xl shadow-lg shadow-orange-500/30 transform hover:scale-[1.02] transition-all flex items-center justify-center gap-2 text-lg">
                        <i data-lucide="flame" class="w-6 h-6"></i> Cook & Deduct Stock
                    </button>
                </div>
            </form>
        </div>

        <!-- Recent Logs (Mini View) -->
        <div class="mt-8">
            <h3 class="font-bold text-gray-700 mb-4 px-2">Recent Production</h3>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                @php
                    $recentLogs = \App\Models\ProductionLog::with(['recipe', 'user'])->latest()->limit(3)->get();
                @endphp

                @forelse($recentLogs as $log)
                    <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                                {{ substr($log->recipe->name, 0, 2) }}
                            </div>
                            <div>
                                <div class="font-bold text-gray-800">{{ $log->recipe->name }}</div>
                                <div class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }} by
                                    {{ $log->user->name }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-orange-600">{{ number_format($log->portions, 0) }} Portions</div>
                            <div class="text-xs text-gray-400 font-mono">₹{{ number_format($log->total_cost, 2) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-400 py-4 text-sm">No recent cooking sessions.</div>
                @endforelse
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            let currentUnit = 'batches';
            let baseYield = 1;

            function toggleUnit(type) {
                currentUnit = type;
                // Update UI styles
                document.getElementById('lbl-batches').className = type === 'batches' ?
                    'cursor-pointer px-3 py-1 rounded-md text-sm font-medium transition-colors bg-orange-100 text-orange-700' :
                    'cursor-pointer px-3 py-1 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors';

                document.getElementById('lbl-portions').className = type === 'portions' ?
                    'cursor-pointer px-3 py-1 rounded-md text-sm font-medium transition-colors bg-orange-100 text-orange-700' :
                    'cursor-pointer px-3 py-1 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors';

                updateHelperText();
            }

            function adjustAmount(delta) {
                const input = document.getElementById('amount');
                let val = parseFloat(input.value) || 0;
                val += delta;
                if (val < 0.1) val = 0.1;
                input.value = parseFloat(val.toFixed(2));
                updateHelperText();
            }

            function updateHelperText() {
                const val = parseFloat(document.getElementById('amount').value) || 0;
                const text = document.getElementById('helper-text');

                if (currentUnit === 'batches') {
                    const totalPortions = (val * baseYield).toFixed(2);
                    text.innerText = `Produces ${val} Batch(es) = ${totalPortions} Portions`;
                } else {
                    const totalBatches = (val / baseYield).toFixed(2);
                    text.innerText = `Produces ${val} Portions = ${totalBatches} Batch(es)`;
                }
            }

            document.getElementById('recipe_id').addEventListener('change', function () {
                const selected = this.options[this.selectedIndex];
                const yieldVal = selected.getAttribute('data-yields');
                if (yieldVal) {
                    baseYield = parseFloat(yieldVal);
                    // Reset to 1 batch default
                    if (currentUnit === 'batches') {
                        document.getElementById('amount').value = 1;
                    } else {
                        document.getElementById('amount').value = baseYield;
                    }
                    updateHelperText();
                }
            });

            // Initialize if recipe is already selected (e.g. after validation error)
            window.addEventListener('load', () => {
                const recipeSelect = document.getElementById('recipe_id');
                if (recipeSelect.value) {
                    recipeSelect.dispatchEvent(new Event('change'));
                }
            });
        </script>
    @endpush
@endsection