@extends('layouts.app')

@section('header')
    <div>
        <h1>Recipe Book</h1>
        <p class="text-muted text-sm">Manage kitchen recipes and approvals</p>
    </div>
@endsection

@section('actions')
    <div class="flex flex-wrap items-center gap-2 md:gap-3">
        <button onclick="openExportModal()" class="btn btn-secondary">
            <i data-lucide="download"></i> <span class="hidden sm:inline">Export</span>
        </button>
        @if(auth()->user()->isAdmin())
        <form action="{{ route('recipes.backup_all') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="btn btn-secondary" title="Backup to Drive">
                <i data-lucide="cloud-upload"></i> <span class="hidden sm:inline">Backup Now</span>
            </button>
        </form>
        @endif
        <a href="{{ route('recipes.create') }}" class="btn btn-primary">
            <i data-lucide="plus-circle"></i> <span class="hidden sm:inline">Create Recipe</span>
        </a>
    </div>
@endsection

@section('content')
    <div class="card p-0 overflow-hidden">
        
        <!-- Filters -->
        <div class="p-4 md:p-6 border-b border-subtle bg-white/5">
            <form method="GET" action="{{ route('recipes.index') }}" class="flex flex-col sm:flex-row flex-wrap gap-4 sm:items-end">
                <div class="flex-1 min-w-full sm:min-w-[200px]">
                    <label class="form-label">Search</label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted"></i>
                        <input type="text" name="search" class="form-control pl-10" 
                               placeholder="Search recipes..." value="{{ request('search') }}">
                    </div>
                </div>
                
                <div class="w-full sm:w-48">
                    <label class="form-label">Category</label>
                    <select name="category_id" id="category-filter" class="form-control">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        new TomSelect('#category-filter', {
                            create: false,
                            sortField: {
                                field: "text",
                                direction: "asc"
                            },
                            placeholder: "All Categories",
                            plugins: ['remove_button'],
                            allowEmptyOption: true,
                        });
                    });
                </script>
                @endpush

                <div class="w-full sm:w-48">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Pending (Draft)</option>
                        <option value="permanent" {{ request('status') == 'permanent' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary h-[38px] px-4">
                        <i data-lucide="filter"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'category_id', 'status']))
                        <a href="{{ route('recipes.index') }}" class="btn btn-secondary h-[38px] px-4">
                            <i data-lucide="x"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Recipe Details</th>
                        <th class="hidden sm:table-cell">Category</th>
                        <th>Status</th>
                        @if(auth()->user()->isAdmin())
                            <th class="hidden md:table-cell">Costing</th>
                        @endif
                        <th class="hidden lg:table-cell">Created By</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recipes as $recipe)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="p-3 md:p-4 lg:p-5">
                                <div class="font-bold text-primary text-base md:text-lg">{{ $recipe->name }}</div>
                                <div class="text-xs text-muted font-mono uppercase tracking-widest mt-0.5">v{{ $recipe->version }} • {{ $recipe->yields }} Portions</div>
                            </td>
                            <td class="p-3 md:p-4 lg:p-5 hidden sm:table-cell">
                                <span class="badge badge-secondary">
                                    {{ $recipe->category?->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="p-3 md:p-4 lg:p-5">
                                @if($recipe->status->value === 'permanent')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($recipe->status->value === 'draft')
                                    <span class="badge badge-warning">Pending</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                            @if(auth()->user()->isAdmin())
                                <td class="p-3 md:p-4 lg:p-5 hidden md:table-cell">
                                    <div class="font-mono font-bold text-accent text-sm md:text-base">₹{{ number_format($recipe->total_cost, 2) }}</div>
                                </td>
                            @endif
                            <td class="p-3 md:p-4 lg:p-5 hidden lg:table-cell">
                                <div class="text-sm font-medium text-primary">{{ $recipe->creator?->name ?? 'Unknown' }}</div>
                                <div class="text-[10px] uppercase font-bold text-muted">{{ $recipe->updated_at->diffForHumans() }}</div>
                            </td>
                            <td class="p-3 md:p-4 lg:p-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('recipes.show', $recipe) }}" 
                                       class="p-2 text-muted hover:text-accent transition-colors" title="View">
                                        <i data-lucide="eye"></i>
                                    </a>
                                    
                                    @if($recipe->isDraft() && auth()->user()->isAdmin())
                                        <form action="{{ route('recipes.approve', $recipe) }}" method="POST" class="inline" onsubmit="return confirm('Approve this recipe?');">
                                            @csrf
                                            <button type="submit" class="p-2 text-accent hover:text-white transition-colors" title="Approve">
                                                <i data-lucide="check"></i>
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('recipes.reject', $recipe) }}" method="POST" class="inline" onsubmit="return confirm('Reject this recipe?');">
                                            @csrf
                                            <button type="submit" class="p-2 text-red-500 hover:text-red-400 transition-colors" title="Reject">
                                                <i data-lucide="x"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(auth()->user()->can('update', $recipe) && $recipe->status->value !== 'rejected')
                                        <a href="{{ route('recipes.edit', $recipe) }}" 
                                           class="p-2 text-muted hover:text-accent transition-colors" title="Edit">
                                            <i data-lucide="edit-3"></i>
                                        </a>
                                        
                                        <button onclick="openScaleModal({{ $recipe->id }}, '{{ $recipe->name }}', {{ $recipe->yield_portions ?? $recipe->yields }})" 
                                            class="p-2 text-muted hover:text-accent transition-colors" title="Scale Recipe">
                                            <i data-lucide="copy"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-16 text-center text-muted">
                                <div class="flex flex-col items-center justify-center">
                                    <i data-lucide="book-open" class="w-12 h-12 mb-4 opacity-20"></i>
                                    <p class="text-lg">No recipes found.</p>
                                    <a href="{{ route('recipes.create') }}" class="mt-4 text-accent hover:underline">Create your first recipe</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-6 border-t border-subtle">
            {{ $recipes->withQueryString()->links() }}
        </div>
    </div>
    
    <!-- Export Modal -->
    <div id="exportModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/70">
        <div class="card w-full max-w-2xl shadow-2xl overflow-hidden p-0">
            <div class="p-6 border-b border-subtle flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i data-lucide="download" class="text-accent"></i>
                    <h3 class="text-xl">Export Recipes</h3>
                </div>
                <button onclick="closeExportModal()" class="text-muted hover:text-accent transition-colors">
                    <i data-lucide="x"></i>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <p class="text-sm text-muted">Choose an export format to download recipe data</p>
                
                <div class="grid grid-cols-1 gap-4">
                    <!-- Full Recipe Cards -->
                    <a href="{{ route('recipes.export', ['type' => 'full-cards']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
                       class="flex items-center gap-4 p-4 border border-subtle rounded-xl hover:border-accent hover:bg-white/5 transition-all group">
                        <div class="p-3 bg-primary rounded-lg group-hover:bg-accent/20 transition-colors">
                            <i data-lucide="file-text" class="text-accent"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-primary mb-1">Full Recipe Cards</h4>
                            <p class="text-xs text-muted">Separate sheets with ingredients, method, and allergens</p>
                        </div>
                        <i data-lucide="chevron-right" class="text-muted group-hover:text-accent"></i>
                    </a>
                    
                    <!-- Procurement List -->
                    <a href="{{ route('recipes.export', ['type' => 'procurement']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
                       class="flex items-center gap-4 p-4 border border-subtle rounded-xl hover:border-accent hover:bg-white/5 transition-all group">
                        <div class="p-3 bg-primary rounded-lg group-hover:bg-accent/20 transition-colors">
                            <i data-lucide="shopping-cart" class="text-accent"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-primary mb-1">Procurement List</h4>
                            <p class="text-xs text-muted">Consolidated list of ingredients for ordering</p>
                        </div>
                        <i data-lucide="chevron-right" class="text-muted group-hover:text-accent"></i>
                    </a>
                    
                    <!-- Cost Breakdown -->
                    <a href="{{ route('recipes.export', ['type' => 'cost-breakdown']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
                       class="flex items-center gap-4 p-4 border border-subtle rounded-xl hover:border-accent hover:bg-white/5 transition-all group">
                        <div class="p-3 bg-primary rounded-lg group-hover:bg-accent/20 transition-colors">
                            <i data-lucide="dollar-sign" class="text-accent"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-primary mb-1">Cost Breakdown</h4>
                            <p class="text-xs text-muted">Summary sheet with margin calculations</p>
                        </div>
                        <i data-lucide="chevron-right" class="text-muted group-hover:text-accent"></i>
                    </a>
                </div>
            </div>
            
            <div class="p-4 bg-white/5 border-t border-subtle flex justify-end">
                <button onclick="closeExportModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
    
    <!-- Scale Recipe Modal -->
    <div id="scaleRecipeModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/70">
        <div class="card w-full max-w-lg shadow-2xl p-0 overflow-hidden">
            <form id="scaleRecipeForm" method="POST" action="">
                @csrf
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <i data-lucide="calculator" class="text-accent"></i>
                        <h3>Scale Recipe</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <p class="text-sm text-muted">
                            Create a new version of <strong id="scaleRecipeName" class="text-primary"></strong> with a different yield.
                        </p>
                        <div>
                            <label class="form-label">New Target Yield (Portions)</label>
                            <input type="number" name="new_yield" id="new_yield" step="0.01" min="0.01" required class="form-control">
                            <p class="mt-1 text-[10px] text-muted font-bold uppercase">Current Base: <span id="currentYield"></span> Portions</p>
                        </div>
                        <input type="hidden" name="yield_type" value="portions">
                    </div>
                </div>
                
                <div class="p-4 bg-white/5 border-t border-subtle flex justify-end gap-3">
                    <button type="button" onclick="closeScaleModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Scaled Version</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function openScaleModal(id, name, currentYield) {
            document.getElementById('scaleRecipeName').innerText = name;
            document.getElementById('currentYield').innerText = currentYield;
            document.getElementById('new_yield').value = currentYield; // Default to current
            
            // Set Action URL
            const form = document.getElementById('scaleRecipeForm');
            // Assuming route is /recipes/{id}/scale
            form.action = `/recipes/${id}/scale`; 
            
            document.getElementById('scaleRecipeModal').classList.remove('hidden');
        }

        function closeScaleModal() {
            document.getElementById('scaleRecipeModal').classList.add('hidden');
        }

        function openExportModal() {
            document.getElementById('exportModal').classList.remove('hidden');
        }

        function closeExportModal() {
            document.getElementById('exportModal').classList.add('hidden');
        }
    </script>
@endsection