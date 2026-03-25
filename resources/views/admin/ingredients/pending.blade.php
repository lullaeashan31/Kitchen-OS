@extends('layouts.app')

@section('header')
    <div class="flex flex-col gap-1">
        <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-3">
            <span class="p-2 bg-orange-100 text-orange-600 rounded-xl">
                <i data-lucide="shield-check" class="w-8 h-8"></i>
            </span>
            Ingredient Approval Queue
        </h1>
        <p class="text-sm text-slate-500 mt-2">New ingredients submitted by staff members that require your verification before usage in recipes.</p>
    </div>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto py-4">
        @if($pendingIngredients->count() > 0)
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100">
                                <th class="px-6 py-4 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Ingredient Details</th>
                                <th class="px-6 py-4 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Created By</th>
                                <th class="px-6 py-4 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Configuration Required</th>
                                <th class="px-6 py-4 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Status</th>
                                <th class="px-6 py-4 text-[10px] uppercase font-bold text-slate-400 tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($pendingIngredients as $ingredient)
                                <tr class="hover:bg-slate-50/30 transition-colors group">
                                    <!-- Name & Category -->
                                    <td class="px-6 py-6">
                                        <div class="flex flex-col">
                                            <span class="text-lg font-bold text-slate-800 tracking-tight">{{ $ingredient->name }}</span>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-bold rounded uppercase border border-blue-100">
                                                    {{ $ingredient->category->name ?? 'Uncategorized' }}
                                                </span>
                                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                                                    {{ $ingredient->measurement_unit }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Created By -->
                                    <td class="px-6 py-6 text-slate-600">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center text-xs font-bold text-slate-500">
                                                {{ substr($ingredient->creator->name ?? '?', 0, 1) }}
                                            </div>
                                            <div class="flex flex-col text-xs">
                                                <span class="font-bold text-slate-700">{{ $ingredient->creator->name ?? 'System' }}</span>
                                                <span class="text-slate-400">{{ $ingredient->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Configuration Form Injected in Table -->
                                    <td class="px-6 py-6 min-w-[300px]">
                                        <form action="{{ route('admin.ingredients.approve', ['kitchen_slug' => request()->route('kitchen_slug'), 'ingredient' => $ingredient->id]) }}" method="POST" id="approve-form-{{ $ingredient->id }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="grid grid-cols-2 gap-3 items-end">
                                                <div>
                                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-tighter mb-1 block">Purchase Price (₹)</label>
                                                    <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', $ingredient->purchase_price) }}" 
                                                        class="w-full px-2 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-blue-500 focus:bg-white outline-none transition-all" required>
                                                </div>
                                                <div>
                                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-tighter mb-1 block">Purchase Qty/Unit</label>
                                                    <div class="flex gap-1">
                                                        <input type="number" step="0.001" name="purchase_quantity" value="{{ old('purchase_quantity', $ingredient->purchase_quantity ?: 1) }}" 
                                                            class="w-1/2 px-2 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-blue-500 focus:bg-white outline-none" required>
                                                        <input type="text" name="purchase_unit" value="{{ old('purchase_unit', $ingredient->purchase_unit ?: 'kg') }}" 
                                                            class="w-1/2 px-2 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-blue-500 focus:bg-white outline-none" placeholder="Unit" required>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="alert_threshold" value="5">
                                            </div>
                                        </form>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-6">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-amber-100">
                                            <span class="relative flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                            </span>
                                            Locked
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="submit" form="approve-form-{{ $ingredient->id }}" 
                                                class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 shadow-md shadow-blue-200 transition-all flex items-center gap-1.5 active:scale-95">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                Approve
                                            </button>
                                            
                                            <button type="button" onclick="confirmReject('{{ $ingredient->id }}')" 
                                                class="p-2 bg-red-50 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-all active:scale-95 border border-red-100">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Global Hidden Reject Form -->
            <form id="global-reject-form" action="" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        @else
            <div class="text-center py-20 bg-white rounded-3xl border border-dotted border-slate-200 shadow-sm transition-all hover:border-blue-200 animate-fade-in group">
                <div class="w-24 h-24 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-500">
                    <i data-lucide="shield-check" class="w-12 h-12"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Queue is Clear!</h3>
                <p class="text-slate-500 max-w-sm mx-auto">No pending ingredient approvals. All staff members are currently using validated data.</p>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function confirmReject(id) {
            if (confirm('Are you sure you want to REJECT and delete this ingredient request? This action cannot be undone.')) {
                let form = document.getElementById('global-reject-form');
                form.action = "{{ url('k/' . request()->route('kitchen_slug') . '/admin/ingredients') }}/" + id + "/reject";
                form.submit();
            }
        }
    </script>
    @endpush
@endsection