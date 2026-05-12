@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-4">
        <a href="{{ route('recipes.index') }}" class="btn btn-secondary p-3">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl md:text-3xl font-black tracking-tight text-primary">{{ $recipe->name }}</h1>
                <span class="badge {{ $recipe->status->value === 'permanent' ? 'badge-success' : 'badge-warning' }}">
                    {{ $recipe->status->label() }}
                </span>
                <span class="text-[10px] font-bold text-muted uppercase tracking-widest bg-white/5 px-2 py-1 rounded">v{{ $recipe->version }}</span>
            </div>
            <p class="text-[10px] font-bold text-muted mt-1 uppercase tracking-widest">Recipe <span class="text-accent">Specification</span></p>
        </div>
    </div>
@endsection

@section('actions')
    <div class="flex gap-2">
        @if($recipe->isDraft() && auth()->user()->canApproveRecipes())
            <form action="{{ route('recipes.approve', $recipe) }}" method="POST" onsubmit="return confirm('Approve this recipe permanent version?')">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check-circle"></i> Approve
                </button>
            </form>
        @endif

        @can('update', $recipe)
            <a href="{{ route('recipes.edit', $recipe) }}" class="btn btn-secondary">
                <i data-lucide="edit-2"></i> Edit
            </a>
        @endcan

        <a href="{{ route('recipes.print', $recipe) }}" target="_blank" class="btn btn-secondary">
            <i data-lucide="printer"></i> Print
        </a>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Main Recipe Content --}}
        <div class="lg:col-span-2 space-y-8">
            {{-- Quick Stats Card --}}
            <div class="card p-0 overflow-hidden">
                <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-subtle">
                    <div class="p-6 text-center">
                        <div class="text-[10px] font-bold text-muted uppercase tracking-widest mb-1">Category</div>
                        <div class="font-bold text-primary">{{ $recipe->category->name ?? 'None' }}</div>
                    </div>
                    <div class="p-6 text-center">
                        <div class="text-[10px] font-bold text-muted uppercase tracking-widest mb-1">Base Yield</div>
                        <div class="font-black text-accent text-lg">{{ $recipe->yields }} <span class="text-[10px]">Portions</span></div>
                    </div>
                    @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                        <div class="p-6 text-center">
                            <div class="text-[10px] font-bold text-muted uppercase tracking-widest mb-1">Cost / Portion</div>
                            <div class="font-black text-primary text-lg">₹{{ number_format($costPerPortion, 2) }}</div>
                        </div>
                        <div class="p-6 text-center">
                            <div class="text-[10px] font-bold text-muted uppercase tracking-widest mb-1">Total Batch Cost</div>
                            <div class="font-bold text-muted tracking-tight">₹{{ number_format((float) $recipe->total_cost, 2) }}</div>
                        </div>
                    @endif
                </div>
            </div>

            @if($recipe->isSubRecipe())
                <div class="card bg-accent/5 border-accent/20">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-accent rounded-xl text-primary">
                            <i data-lucide="package" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-accent mb-1 uppercase tracking-widest text-[11px] font-black">Production Output</h3>
                            <p class="text-primary opacity-80 text-sm">
                                This recipe produces <span class="font-black underline">{{ number_format($recipe->output_quantity, 3) }} {{ $recipe->output_unit }}</span> 
                                of <span class="font-bold">{{ $recipe->producesIngredient->name }}</span> per {{ $recipe->yields }} portion(s).
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Stages & Ingredients --}}
            @foreach($recipe->stages as $stage)
                <div class="card p-0 overflow-hidden">
                    <div class="p-8 border-b border-subtle bg-white/5 flex items-center justify-between">
                        <h2 class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-accent text-primary flex items-center justify-center font-black text-sm">{{ $loop->iteration }}</span>
                            {{ $stage->name }}
                        </h2>
                    </div>

                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="px-6 py-4 w-5/12">Ingredient</th>
                                    <th class="px-6 py-4 text-center w-3/12">Quantity</th>
                                    <th class="px-6 py-4 w-2/12">Unit</th>
                                    @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                        <th class="px-6 py-4 text-right w-2/12">Cost</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-subtle">
                                @foreach($stage->ingredients as $ri)
                                    <tr class="hover:bg-white/5 transition-colors ingredient-row">
                                        <td class="px-6 py-4">
                                            @if($ri->ingredient->producedByRecipes->isNotEmpty())
                                                @php $subRecipe = $ri->ingredient->producedByRecipes->first(); @endphp
                                                <div class="flex items-center justify-between group/sub">
                                                    <a href="{{ route('recipes.show', $subRecipe) }}" class="flex items-center gap-2 group-hover:text-accent transition-all">
                                                        <i data-lucide="link" class="w-3 h-3 text-accent"></i>
                                                        <span class="font-bold text-primary group-hover:text-accent">{{ $ri->ingredient->name }}</span>
                                                        @if($subRecipe->status->value === 'draft')
                                                            <span class="badge bg-white/10 text-[9px]">Draft</span>
                                                        @endif
                                                    </a>
                                                    <div class="flex items-center gap-2 opacity-0 group-hover/sub:opacity-100 transition-all">
                                                        <a href="{{ route('recipes.print', $subRecipe) }}" target="_blank" class="text-muted hover:text-accent">
                                                            <i data-lucide="printer" class="w-4 h-4"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="font-bold text-primary">{{ $ri->ingredient->name }}</div>
                                            @endif
                                            
                                            @if($ri->ingredient->allergen_tags && count($ri->ingredient->allergen_tags) > 0)
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    @foreach($ri->ingredient->allergen_tags as $tag)
                                                        <span class="text-[8px] font-black uppercase tracking-widest text-red-400 bg-red-400/10 px-1.5 py-0.5 rounded-full border border-red-400/20">
                                                            {{ $tag }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="qty-display font-black text-primary" data-base="{{ $ri->quantity }}">
                                                {{ number_format($ri->quantity, 3) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-[10px] font-bold text-muted uppercase tracking-widest">{{ $ri->unit }}</td>
                                        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                            <td class="px-6 py-4 text-right">
                                                <span class="cost-display font-bold text-accent" data-base="{{ $ri->cost }}">
                                                    ₹{{ number_format($ri->cost, 2) }}
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($stage->method)
                        <div class="p-8 bg-white/5 border-t border-subtle">
                            <h4 class="text-[10px] font-bold text-accent uppercase tracking-widest mb-3 flex items-center gap-2">
                                <i data-lucide="info" class="w-3 h-3"></i> Instructions
                            </h4>
                            <div class="text-sm leading-relaxed text-primary opacity-80 whitespace-pre-wrap font-serif">{{ $stage->method }}</div>
                        </div>
                    @endif
                </div>
            @endforeach

            @if($recipe->method)
                <div class="card">
                    <h3 class="mb-4 text-accent uppercase tracking-widest text-[11px] font-black">Method Notes</h3>
                    <div class="text-lg leading-relaxed text-primary italic font-serif whitespace-pre-wrap opacity-90">"{{ $recipe->method }}"</div>
                </div>
            @endif

            @if(count($recipe->allergens) > 0)
                <div class="card border-l-4 border-l-red-500 bg-red-500/5">
                    <div class="flex items-center gap-3 mb-4">
                        <i data-lucide="alert-triangle" class="text-red-500"></i>
                        <h4 class="text-[11px] font-black text-red-500 uppercase tracking-widest">Allergens</h4>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($recipe->allergens as $allergen)
                            <span class="px-3 py-1 bg-red-500/10 border border-red-500/30 text-red-500 rounded-full text-[10px] font-black uppercase tracking-widest">
                                {{ App\Enums\Allergen::tryFrom($allergen)?->label() ?? ucfirst($allergen) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar Tools --}}
        <div class="space-y-8">
            {{-- Smart Scale --}}
            <div class="card border-accent/40 bg-accent/5">
                <div class="mb-6">
                    <h3 class="flex items-center gap-2 mb-1">
                        <i data-lucide="calculator" class="w-5 h-5 text-accent"></i>
                        Smart Scale
                    </h3>
                    <p class="text-[10px] font-bold text-muted uppercase tracking-widest">Dynamic Portion Recalculation</p>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="form-label text-[10px] uppercase">Scaling Preset</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach([0.5, 1, 2, 3] as $m)
                                <button onclick="setMultiplier({{ $m }})" class="scaling-btn btn btn-secondary text-[10px] py-2" data-multiplier="{{ $m }}">
                                    {{ $m }}x
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="form-label text-[10px] uppercase">Target Portions</label>
                        <div class="relative">
                            <input type="number" id="desiredPortions" class="form-control font-black text-xl py-4" 
                                value="{{ $recipe->yield_portions ?? $recipe->yields }}" min="0.1" step="0.1" oninput="updateScaling()">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-bold text-muted uppercase tracking-widest bg-primary/20 px-2 py-1 rounded">
                                / {{ $recipe->yield_portions ?? $recipe->yields }} Base
                            </div>
                        </div>
                    </div>

                    <div id="scalingResult" class="p-6 bg-primary rounded-xl border border-subtle">
                        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                            <div class="text-[10px] font-bold text-muted uppercase tracking-widest mb-1">Estimated Cost</div>
                            <div class="text-3xl font-black text-accent mb-4">
                                ₹<span id="scaledCostDisplay">{{ number_format((float) $recipe->total_cost, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-2 text-[10px] font-bold text-muted uppercase tracking-widest">
                            <i data-lucide="clock" class="w-3 h-3"></i> Prep: <span id="scaledTime">{{ $recipe->prep_time_minutes }}</span> mins
                        </div>
                    </div>

                    <form action="{{ route('recipes.scale', $recipe) }}" method="POST" onsubmit="return confirm('Create a NEW scaled version?')">
                        @csrf
                        <input type="hidden" name="yield_type" value="portions">
                        <input type="hidden" name="new_yield" id="formNewYield" value="{{ $recipe->yield_portions ?? $recipe->yields }}">
                        <button type="submit" class="btn btn-primary w-full py-4 text-[10px] uppercase tracking-widest">
                            <i data-lucide="copy"></i> Save New Version
                        </button>
                    </form>
                </div>
            </div>

            {{-- Verification Files --}}
            <div class="card">
                <div class="mb-6 flex justify-between items-center">
                    <div>
                        <h3 class="flex items-center gap-2 mb-1">
                            <i data-lucide="paperclip" class="w-5 h-5 text-accent"></i>
                            Attached Files
                        </h3>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($recipe->driveFiles as $file)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white/5 border border-subtle group">
                            <a href="{{ $file->preview_url ?? $file->drive_url }}" target="_blank" 
                                class="flex items-center gap-3 text-[10px] font-bold text-primary hover:text-accent uppercase tracking-widest transition-all">
                                <i data-lucide="file-text" class="w-4 h-4 text-accent"></i>
                                {{ $file->name }}
                            </a>
                            @can('delete', $file)
                                <form action="{{ route('drive_files.destroy', $file) }}" method="POST" onsubmit="return confirm('Remove file?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-muted hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    @empty
                        <div class="py-8 text-center bg-white/5 rounded-xl border border-dashed border-subtle">
                            <p class="text-[10px] font-bold text-muted uppercase tracking-widest opacity-40">No attachments</p>
                        </div>
                    @endforelse
                </div>

                @can('create', App\Models\DriveFile::class)
                    <form action="{{ route('drive_files.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-2 p-4 bg-primary/20 rounded-xl border border-subtle border-dashed">
                        @csrf
                        <input type="hidden" name="linked_type" value="recipe">
                        <input type="hidden" name="linked_id" value="{{ $recipe->id }}">
                        <input type="text" name="name" class="form-control text-[10px] py-2" placeholder="Artifact Name" required>
                        <input type="file" name="file" class="form-control text-[10px] py-2" required>
                        <button type="submit" class="btn btn-secondary w-full py-2 text-[10px] uppercase">Upload Artifact</button>
                    </form>
                @endcan
            </div>

            {{-- Lineage / History --}}
            <div class="card">
                <div class="mb-6">
                    <h3 class="flex items-center gap-2 mb-1">
                        <i data-lucide="history" class="w-5 h-5 text-accent"></i>
                        Version History
                    </h3>
                </div>

                <div class="space-y-4 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                    @foreach($recipe->versions as $v)
                        <div class="relative pl-6 pb-6 border-l border-subtle last:pb-0">
                            <div class="absolute left-[-5px] top-0 w-2.5 h-2.5 rounded-full bg-accent"></div>
                            <div class="flex justify-between items-start mb-1">
                                <span class="text-[10px] font-black text-primary uppercase">v{{ $v->version }}</span>
                                <span class="text-[9px] font-bold text-muted uppercase">{{ $v->created_at->format('M d, Y') }}</span>
                            </div>
                            <p class="text-[9px] font-bold text-muted uppercase tracking-widest">Editor: {{ $v->editor->name ?? 'System' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const basePortions = {{ $recipe->yield_portions ?? $recipe->yields ?? 1 }};
        const baseCost = {{ $recipe->total_cost }};
        const basePrepTime = {{ $recipe->prep_time_minutes ?? 0 }};

        document.addEventListener('DOMContentLoaded', function() {
            updateScaling();
        });

        function setMultiplier(m) {
            const target = basePortions * m;
            document.getElementById('desiredPortions').value = target;
            updateScaling();
        }

        function updateScaling() {
            const targetPortions = parseFloat(document.getElementById('desiredPortions').value) || basePortions;
            const ratio = targetPortions / basePortions;

            document.querySelectorAll('.scaling-btn').forEach(btn => {
                const btnMultiplier = parseFloat(btn.getAttribute('data-multiplier'));
                if (Math.abs(btnMultiplier - ratio) < 0.01) {
                    btn.className = 'scaling-btn btn btn-primary text-[10px] py-2';
                } else {
                    btn.className = 'scaling-btn btn btn-secondary text-[10px] py-2';
                }
            });

            document.getElementById('formNewYield').value = targetPortions;
            const newCost = baseCost * ratio;
            const costEl = document.getElementById('scaledCostDisplay');
            if (costEl) costEl.innerText = newCost.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

            document.querySelectorAll('.qty-display').forEach(el => {
                const base = parseFloat(el.dataset.base);
                el.innerText = (base * ratio).toLocaleString(undefined, {minimumFractionDigits: 3, maximumFractionDigits: 3});
            });

            document.querySelectorAll('.cost-display').forEach(el => {
                const base = parseFloat(el.dataset.base);
                el.innerText = '₹' + (base * ratio).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            });
        }
    </script>
@endpush