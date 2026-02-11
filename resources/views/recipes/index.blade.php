@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Recipe Book</h1>
            <p class="text-sm text-gray-500">Manage kitchen recipes and approvals</p>
        </div>
        <a href="{{ route('recipes.create') }}" class="btn btn-primary bg-blue-600 hover:bg-blue-700 text-white shadow-lg flex items-center gap-2 px-6 py-3 rounded-xl">
            <i data-lucide="plus-circle" class="w-5 h-5"></i> Create Recipe
        </a>
    </div>
@endsection

@section('content')
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        
        <!-- Filters -->
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <form method="GET" action="{{ route('recipes.index') }}" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Search</label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="search" class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none" 
                               placeholder="Search by name..." value="{{ request('search') }}">
                    </div>
                </div>
                
                <div class="w-48">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category</label>
                    <select name="category_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none bg-white">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-48">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 outline-none bg-white">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Pending (Draft)</option>
                        <option value="permanent" {{ request('status') == 'permanent' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 font-medium text-sm flex items-center gap-2">
                        <i data-lucide="filter" class="w-4 h-4"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'category_id', 'status']))
                        <a href="{{ route('recipes.index') }}" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 font-medium text-sm flex items-center gap-2">
                            <i data-lucide="x" class="w-4 h-4"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="p-5 font-semibold">Recipe Details</th>
                        <th class="p-5 font-semibold">Category</th>
                        <th class="p-5 font-semibold">Status</th>
                        @if(auth()->user()->isAdmin())
                            <th class="p-5 font-semibold">Costing</th>
                        @endif
                        <th class="p-5 font-semibold">Created By</th>
                        <th class="p-5 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($recipes as $recipe)
                        <tr class="group hover:bg-blue-50/20 transition-colors">
                            <td class="p-5">
                                <div class="font-bold text-gray-800 text-lg">{{ $recipe->name }}</div>
                                <div class="text-xs text-gray-400 font-mono">v{{ $recipe->version }} • {{ $recipe->yields }} Portions</div>
                            </td>
                            <td class="p-5">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                    {{ $recipe->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="p-5">
                                @if($recipe->status->value === 'permanent')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i> Approved
                                    </span>
                                @elseif($recipe->status->value === 'draft')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                        <i data-lucide="clock" class="w-3 h-3"></i> Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                        <i data-lucide="x-circle" class="w-3 h-3"></i> Rejected
                                    </span>
                                @endif
                            </td>
                            @if(auth()->user()->isAdmin())
                                <td class="p-5">
                                    <div class="font-mono font-bold text-gray-700">₹{{ number_format($recipe->total_cost, 2) }}</div>
                                </td>
                            @endif
                            <td class="p-5">
                                <div class="text-sm font-medium text-gray-700">{{ $recipe->creator->name }}</div>
                                <div class="text-xs text-gray-400">{{ $recipe->updated_at->diffForHumans() }}</div>
                            </td>
                            <td class="p-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('recipes.show', $recipe) }}" 
                                       class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="View">
                                        <i data-lucide="eye" class="w-5 h-5"></i>
                                    </a>
                                    
                                    @if($recipe->isDraft() && auth()->user()->isAdmin())
                                        <!-- Admin Actions for Drafts -->
                                        <form action="{{ route('recipes.approve', $recipe) }}" method="POST" class="inline" onsubmit="return confirm('Approve this recipe?');">
                                            @csrf
                                            <button type="submit" class="p-2 text-green-500 hover:text-green-700 hover:bg-green-50 rounded-lg transition-colors" title="Approve">
                                                <i data-lucide="check" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('recipes.reject', $recipe) }}" method="POST" class="inline" onsubmit="return confirm('Reject this recipe?');">
                                            @csrf
                                            <button type="submit" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" title="Reject">
                                                <i data-lucide="x" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(auth()->user()->can('update', $recipe) && $recipe->status->value !== 'rejected')
                                        <a href="{{ route('recipes.edit', $recipe) }}" 
                                           class="p-2 text-blue-500 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                            <i data-lucide="edit-3" class="w-5 h-5"></i>
                                        </a>
                                        
                                        <button onclick="openScaleModal({{ $recipe->id }}, '{{ $recipe->name }}', {{ $recipe->yield_portions ?? $recipe->yields }})" 
                                            class="p-2 text-purple-500 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition-colors" title="Scale Recipe">
                                            <i data-lucide="copy" class="w-5 h-5"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-16 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i data-lucide="book-open" class="w-12 h-12 mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium">No recipes found matching your filters.</p>
                                    <a href="{{ route('recipes.create') }}" class="mt-4 text-blue-600 hover:underline">Create your first recipe</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-6 border-t border-gray-100">
            {{ $recipes->withQueryString()->links() }}
        </div>
    </div>
    
    <!-- Scale Recipe Modal -->
    <div id="scaleRecipeModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeScaleModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="scaleRecipeForm" method="POST" action="">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="calculator" class="w-6 h-6 text-purple-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Scale Recipe</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">
                                        Create a new version of <strong id="scaleRecipeName"></strong> with a different yield.
                                    </p>
                                    <div class="mb-4">
                                        <label for="new_yield" class="block text-sm font-medium text-gray-700">New Target Yield (Portions)</label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <input type="number" name="new_yield" id="new_yield" step="0.01" min="0.01" required
                                                class="focus:ring-purple-500 focus:border-purple-500 flex-1 block w-full rounded-md sm:text-sm border-gray-300 p-2 border">
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Current Base: <span id="currentYield"></span> Portions</p>
                                    </div>
                                    <input type="hidden" name="yield_type" value="portions">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Create Scaled Version
                        </button>
                        <button type="button" onclick="closeScaleModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
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
    </script>
@endsection