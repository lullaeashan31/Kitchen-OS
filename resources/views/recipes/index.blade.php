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
                                    <div class="font-mono font-bold text-gray-700">{{ number_format($recipe->total_cost, 2) }}</div>
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
    
    <script>
        lucide.createIcons();
    </script>
@endsection