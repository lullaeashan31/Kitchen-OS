@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-4">
        <a href="{{ route('production.index') }}" class="btn btn-secondary p-3">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-primary">Production Entry</h1>
            <p class="text-xs md:text-sm text-muted font-medium mt-1 uppercase tracking-widest">Logging <span class="text-accent">Cooking Sessions</span></p>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto space-y-8">
        {{-- Alerts --}}
        @if(session('success'))
            <div class="card p-4 border-l-4 border-l-green-500 bg-green-500/5">
                <div class="flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
                    <p class="text-xs font-bold text-green-500 uppercase tracking-widest">{{ session('success') }}</p>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="card p-4 border-l-4 border-l-red-500 bg-red-500/5">
                <div class="flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
                    <p class="text-xs font-bold text-red-500 uppercase tracking-widest">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- Main Entry Form --}}
        <div class="card p-0 overflow-hidden">
            <div class="p-8 border-b border-subtle bg-white/5 flex items-center justify-between">
                <h2 class="flex items-center gap-3">
                    <i data-lucide="chef-hat" class="text-accent"></i>
                    Cooking Session
                </h2>
                <div class="flex bg-primary/20 rounded-lg p-1 border border-subtle">
                    <label class="cursor-pointer px-4 py-2 rounded-md text-[10px] font-bold uppercase tracking-widest transition-all bg-accent text-primary" id="lbl-batches">
                        <input type="radio" name="unit_type_toggle" value="batches" class="hidden" checked onchange="toggleUnit('batches')">
                        Batches
                    </label>
                    <label class="cursor-pointer px-4 py-2 rounded-md text-[10px] font-bold uppercase tracking-widest transition-all text-muted hover:text-primary" id="lbl-portions">
                        <input type="radio" name="unit_type_toggle" value="portions" class="hidden" onchange="toggleUnit('portions')">
                        Portions
                    </label>
                </div>
            </div>
            
            <form action="{{ route('production.store') }}" method="POST" class="p-8 space-y-8">
                @csrf
                <input type="hidden" name="unit_type" id="unit_type_input" value="batches">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Recipe Selection --}}
                    <div class="space-y-4">
                        <label class="form-label text-[10px] uppercase">Select Recipe <span class="text-red-500">*</span></label>
                        <select name="recipe_id" id="recipe_id" class="form-control" required>
                            <option value="">Select a recipe...</option>
                            @foreach($recipes as $recipe)
                                @php $yieldValue = $recipe->yield_portions ?? $recipe->yields ?? 1; @endphp
                                <option value="{{ $recipe->id }}" data-yields="{{ $yieldValue }}">
                                    {{ $recipe->name }} (Base: {{ $yieldValue }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] font-bold text-muted uppercase tracking-widest opacity-60">Only approved recipes can be used for production</p>
                    </div>

                    {{-- Quantity Control --}}
                    <div class="space-y-4">
                        <label class="form-label text-[10px] uppercase" id="qtyLabel">Production Quantity</label>
                        <div class="flex items-center gap-4 bg-primary/20 p-2 rounded-xl border border-subtle">
                            <button type="button" onclick="adjustAmount(-1)" class="w-12 h-12 rounded-lg bg-white/5 border border-subtle text-muted hover:text-accent hover:border-accent transition-all flex items-center justify-center text-xl font-bold">
                                <i data-lucide="minus" class="w-4 h-4"></i>
                            </button>
                            <input type="number" name="quantity" id="amount" value="1" step="0.1" min="0.1" class="flex-1 text-center text-2xl font-black bg-transparent border-none focus:ring-0 text-primary" required>
                            <button type="button" onclick="adjustAmount(1)" class="w-12 h-12 rounded-lg bg-white/5 border border-subtle text-muted hover:text-accent hover:border-accent transition-all flex items-center justify-center text-xl font-bold">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <p class="text-center text-[10px] font-bold text-accent uppercase tracking-widest" id="helper-text">Produces 1 Batch (Base Yield)</p>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary w-full py-5 text-lg">
                        <i data-lucide="flame"></i> Cook & Deduct Inventory
                    </button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Bulk Upload Section --}}
            <div class="card flex flex-col">
                <div class="mb-6 flex justify-between items-start">
                    <div>
                        <h3 class="flex items-center gap-2 mb-1">
                            <i data-lucide="upload" class="w-5 h-5 text-accent"></i>
                            Bulk Upload
                        </h3>
                        <p class="text-[10px] font-bold text-muted uppercase tracking-widest">Excel Import</p>
                    </div>
                    <a href="{{ route('production.template') }}" class="text-[10px] font-bold text-accent hover:text-accent-hover uppercase tracking-widest flex items-center gap-1">
                        <i data-lucide="download" class="w-3 h-3"></i> Template
                    </a>
                </div>
                
                <form action="{{ route('production.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4 mt-auto">
                    @csrf
                    <div class="relative">
                        <input type="file" name="file" id="excel_file" accept=".xlsx,.xls" required class="form-control text-[10px] py-3 opacity-0 absolute inset-0 z-10 cursor-pointer">
                        <div class="form-control py-3 text-[10px] uppercase font-bold text-muted flex items-center gap-3">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                            <span id="file-name-display">Choose Excel File...</span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-secondary w-full py-3 text-[10px] uppercase tracking-widest">
                        Process Upload
                    </button>
                    @error('file') <p class="text-red-500 text-[10px] font-bold uppercase">{{ $message }}</p> @enderror
                </form>

                @if(session('upload_errors'))
                    <div class="mt-4 p-4 bg-red-500/5 border border-red-500/20 rounded-xl">
                        <p class="text-[10px] font-bold text-red-500 uppercase tracking-widest mb-2">Errors Found:</p>
                        <ul class="text-[10px] text-muted space-y-1 max-h-32 overflow-y-auto">
                            @foreach(session('upload_errors') as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Recent Activity Mini-View --}}
            <div class="card">
                <div class="mb-6">
                    <h3 class="flex items-center gap-2 mb-1">
                        <i data-lucide="activity" class="w-5 h-5 text-accent"></i>
                        Recent Activity
                    </h3>
                    <p class="text-[10px] font-bold text-muted uppercase tracking-widest">Last 3 Cooking Sessions</p>
                </div>

                <div class="space-y-4">
                    @php
                        $recentLogs = \App\Models\ProductionLog::with(['recipe', 'user'])->latest()->limit(3)->get();
                    @endphp

                    @forelse($recentLogs as $log)
                        <div class="flex items-center justify-between p-4 rounded-xl bg-white/5 border border-subtle">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-lg bg-primary text-accent flex items-center justify-center font-black text-xs uppercase border border-subtle">
                                    {{ substr($log->recipe?->name ?? 'NA', 0, 2) }}
                                </div>
                                <div>
                                    <div class="font-bold text-primary">{{ $log->recipe?->name ?? 'Deleted Recipe' }}</div>
                                    <div class="text-[9px] font-bold text-muted uppercase tracking-widest">
                                        {{ $log->created_at->diffForHumans() }} by {{ $log->user?->name ?? 'Unknown' }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-accent">{{ number_format($log->portions, 0) }} Portions</div>
                                <div class="text-[9px] font-bold text-muted tracking-widest">₹{{ number_format($log->total_cost, 2) }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center bg-white/5 rounded-xl border border-dashed border-subtle">
                            <p class="text-[10px] font-bold text-muted uppercase tracking-widest">No recent sessions</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let currentUnit = 'batches';
            let baseYield = 1;

            function toggleUnit(type) {
                currentUnit = type;
                document.getElementById('unit_type_input').value = type;
                
                // Update UI styles
                const batchesLbl = document.getElementById('lbl-batches');
                const portionsLbl = document.getElementById('lbl-portions');
                
                if(type === 'batches') {
                    batchesLbl.className = 'cursor-pointer px-4 py-2 rounded-md text-[10px] font-bold uppercase tracking-widest transition-all bg-accent text-primary';
                    portionsLbl.className = 'cursor-pointer px-4 py-2 rounded-md text-[10px] font-bold uppercase tracking-widest transition-all text-muted hover:text-primary';
                } else {
                    portionsLbl.className = 'cursor-pointer px-4 py-2 rounded-md text-[10px] font-bold uppercase tracking-widest transition-all bg-accent text-primary';
                    batchesLbl.className = 'cursor-pointer px-4 py-2 rounded-md text-[10px] font-bold uppercase tracking-widest transition-all text-muted hover:text-primary';
                }

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
                    if (currentUnit === 'batches') {
                        document.getElementById('amount').value = 1;
                    } else {
                        document.getElementById('amount').value = baseYield;
                    }
                    updateHelperText();
                }
            });

            document.getElementById('excel_file').addEventListener('change', function() {
                const fileName = this.files[0]?.name || 'Choose Excel File...';
                document.getElementById('file-name-display').innerText = fileName;
            });

            window.addEventListener('load', () => {
                const recipeSelect = document.getElementById('recipe_id');
                if (recipeSelect.value) {
                    recipeSelect.dispatchEvent(new Event('change'));
                }
            });
        </script>
    @endpush
@endsection