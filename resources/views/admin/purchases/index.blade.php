@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-3">
        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
            <i data-lucide="shopping-cart" class="w-6 h-6"></i>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Purchase History</h1>
            <p class="text-sm text-slate-500 font-medium">Track and verify all inventory orders and invoices.</p>
        </div>
    </div>
@endsection

@section('actions')
    <div class="flex gap-3 items-center flex-wrap">
        <a href="{{ route('purchases.download.90days') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors rounded-lg text-sm font-semibold border border-emerald-100 shadow-sm">
            <i data-lucide="download" class="w-4 h-4"></i> 
            Download Invoices (90 Days)
        </a>
        <a href="{{ route('purchases.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 transition-all rounded-lg text-sm font-semibold shadow-md active:scale-95">
            <i data-lucide="plus" class="w-4 h-4 text-white/80"></i> 
            New Purchase
        </a>
    </div>
@endsection

@section('content')
<style>
    .view-toggle-active {
        @apply bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200;
    }
    .purchase-row:hover { background-color: #f8fafc; }
    .status-badge { @apply px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border; }
    .bill-card { @apply bg-white border border-slate-200 rounded-xl overflow-hidden transition-all duration-300 hover:shadow-lg; }
    .bill-table-header th { @apply px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100; }
    .bill-table-row td { @apply px-4 py-3 text-sm text-slate-600 border-b border-slate-50; }
</style>

<div class="space-y-6">
    <!-- Search and View Toggle -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex bg-slate-100/80 p-1 rounded-xl w-full sm:w-auto ring-1 ring-slate-200">
            <a href="{{ request()->fullUrlWithQuery(['view' => 'item']) }}" 
               class="flex-1 sm:px-6 py-2 rounded-lg text-center text-sm font-bold transition-all {{ $view === 'item' ? 'view-toggle-active' : 'text-slate-500 hover:text-slate-700' }}">
                Item-wise
            </a>
            <a href="{{ request()->fullUrlWithQuery(['view' => 'bill']) }}" 
               class="flex-1 sm:px-6 py-2 rounded-lg text-center text-sm font-bold transition-all {{ $view === 'bill' ? 'view-toggle-active' : 'text-slate-500 hover:text-slate-700' }}">
                Bill-wise
            </a>
        </div>
        
        <form action="{{ url()->current() }}" method="GET" class="relative w-full sm:w-64 group">
            <input type="hidden" name="view" value="{{ $view }}">
            <input type="text" name="search" placeholder="Search orders..." value="{{ request('search') }}"
                   class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
            <i data-lucide="search" class="absolute left-3 top-2.5 w-4 h-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
        </form>
    </div>

    @if($view === 'item')
        <!-- Item-wise Table View -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-slate-50/50 bill-table-header">
                        <tr>
                            <th>Date</th>
                            <th>Ingredient</th>
                            <th>Vendor</th>
                            <th class="text-right">Quantity</th>
                            <th class="text-right">Total Price</th>
                            <th class="text-center">Invoice</th>
                            <th class="text-center">Status</th>
                            @if(auth()->user()->isAdmin())
                                <th class="text-right">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($purchases as $purchase)
                            <tr class="purchase-row transition-colors">
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-slate-500">
                                    {{ $purchase->purchase_date->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-slate-900">{{ $purchase->ingredient->name ?? 'Unknown' }}</div>
                                    <div class="text-[10px] text-indigo-500 font-bold uppercase tracking-wide">{{ $purchase->ingredient->category->name ?? '' }}</div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-sm font-medium text-slate-700">{{ $purchase->vendor_name ?? $purchase->vendor->name ?? 'Direct Purchase' }}</div>
                                </td>
                                <td class="px-4 py-4 text-right whitespace-nowrap">
                                    <span class="text-sm font-bold text-slate-900">{{ number_format($purchase->quantity, 2) }}</span>
                                    <span class="text-xs text-slate-400 font-medium ml-1">{{ $purchase->unit?->value ?? $purchase->ingredient->measurement_unit ?? '' }}</span>
                                </td>
                                <td class="px-4 py-4 text-right whitespace-nowrap">
                                    <span class="text-sm font-extrabold text-slate-900">₹{{ number_format($purchase->total_price, 2) }}</span>
                                    <div class="text-[10px] text-slate-400">₹{{ number_format($purchase->unit_price, 2) }}/{{ $purchase->unit?->value ?? 'unit' }}</div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        @if($purchase->invoice_url)
                                            <a href="{{ route('purchases.view.invoice', $purchase) }}" target="_blank" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors border border-transparent hover:border-indigo-100" title="View">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>
                                            <a href="{{ route('purchases.download.invoice', $purchase) }}" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors border border-transparent hover:border-emerald-100" title="Download">
                                                <i data-lucide="download" class="w-4 h-4"></i>
                                            </a>
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if($purchase->status == 'approved')
                                        <span class="status-badge bg-emerald-50 text-emerald-700 border-emerald-100">Approved</span>
                                    @elseif($purchase->status == 'rejected')
                                        <span class="status-badge bg-rose-50 text-rose-700 border-rose-100">Rejected</span>
                                    @else
                                        <span class="status-badge bg-amber-50 text-amber-700 border-amber-100 animate-pulse">Pending</span>
                                    @endif
                                </td>
                                @if(auth()->user()->isAdmin())
                                    <td class="px-4 py-4 text-right">
                                        @if($purchase->status == 'pending')
                                            <div class="flex justify-end gap-1">
                                                <form action="{{ route('purchases.approve', $purchase) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Approve">
                                                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                                                    </button>
                                                </form>
                                                <button type="button" onclick="rejectPurchase('{{ $purchase->id }}')" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Reject">
                                                    <i data-lucide="x-circle" class="w-5 h-5"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center gap-2">
                                        <i data-lucide="inbox" class="w-8 h-8 opacity-20"></i>
                                        <span class="font-medium text-sm">No items found matching your filter.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($purchases->hasPages())
                <div class="px-4 py-3 bg-slate-50/50 border-t border-slate-100">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- Bill-wise Grouped Cards View -->
        <div class="grid grid-cols-1 gap-6">
            @forelse($purchases as $bill)
                <div class="bill-card bg-white" x-data="{ expanded: false }">
                    <div class="p-5 flex flex-col sm:flex-row justify-between sm:items-center gap-4 border-b border-slate-50">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 cursor-pointer" @click="expanded = !expanded">
                                <i data-lucide="receipt" class="w-6 h-6"></i>
                            </div>
                            <div class="cursor-pointer" @click="expanded = !expanded">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-slate-900 uppercase text-[10px] tracking-widest">
                                        Bill #{{ $bill->id }}
                                        <span class="ml-2 text-slate-300 font-normal">|</span>
                                        <span class="ml-2 text-indigo-400">Inv: {{ Str::limit(pathinfo($bill->invoice_photo_path, PATHINFO_FILENAME), 12, '..') ?: 'N/A' }}</span>
                                    </h3>
                                    @if($bill->is_pending)
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse" title="Requires Attention"></span>
                                    @endif
                                </div>
                                <div class="flex items-center flex-wrap gap-x-4 gap-y-1 mt-1">
                                    <span class="text-[10px] text-slate-500 font-bold flex items-center gap-1 uppercase">
                                        <i data-lucide="calendar" class="w-3 h-3 text-indigo-400"></i>
                                        {{ $bill->purchase_date->format('M d, Y') }}
                                    </span>
                                    <span class="text-[10px] text-slate-500 font-bold flex items-center gap-1 uppercase">
                                        <i data-lucide="building-2" class="w-3 h-3 text-indigo-400"></i>
                                        {{ $bill->vendor_name }}
                                    </span>
                                    <span class="text-[10px] text-slate-500 font-bold flex items-center gap-1 uppercase">
                                        <i data-lucide="user" class="w-3 h-3 text-indigo-400"></i>
                                        {{ $bill->creator->name ?? 'System' }}
                                    </span>
                                    @if(!$bill->is_pending)
                                        <span class="text-[9px] bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full font-black uppercase tracking-tighter flex items-center gap-1">
                                            <i data-lucide="shield-check" class="w-2.5 h-2.5"></i>
                                            Settled
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-3 sm:text-right">
                            <div class="bg-indigo-50/50 px-3 py-2 rounded-xl text-right">
                                <div class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest">Grand Total</div>
                                <div class="text-lg font-black text-indigo-700 leading-tight">₹{{ number_format($bill->total_bill_amount, 2) }}</div>
                            </div>
                            
                            @if(auth()->user()->isAdmin() && $bill->is_pending)
                                <form action="{{ route('purchases.bulk_approve') }}" method="POST">
                                    @csrf
                                    @foreach($bill->items->where('status', 'pending') as $p)
                                        <input type="hidden" name="ids[]" value="{{ $p->id }}">
                                    @endforeach
                                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white hover:bg-indigo-700 transition-all rounded-xl text-sm font-bold shadow-lg shadow-indigo-100">
                                        <i data-lucide="check-check" class="w-4 h-4"></i>
                                        Approve All
                                    </button>
                                </form>
                            @endif

                            <div class="flex gap-1">
                                @if($bill->invoice_url)
                                    <a href="{{ route('purchases.view.invoice', $bill->id) }}" target="_blank" class="p-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition-all" title="View Full Invoice">
                                        <i data-lucide="external-link" class="w-5 h-5"></i>
                                    </a>
                                @endif
                                <button type="button" @click="expanded = !expanded" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-slate-50 transition-all rounded-xl" title="Expand Items">
                                    <i data-lucide="chevron-down" class="w-5 h-5 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50/30 border-t border-slate-50 overflow-hidden transition-all duration-300" x-show="expanded" x-collapse>
                        <table class="w-full text-xs overflow-x-auto">
                            <thead class="bg-slate-50/60 text-slate-400 font-bold uppercase tracking-widest text-[9px]">
                                <tr>
                                    <th class="px-5 py-2 text-left">Item Name</th>
                                    <th class="px-5 py-2 text-right">Qty</th>
                                    <th class="px-5 py-2 text-right">Unit Price</th>
                                    <th class="px-5 py-2 text-right">Total</th>
                                    <th class="px-5 py-2 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100/50">
                                @foreach($bill->items as $item)
                                    <tr class="hover:bg-indigo-50/20 transition-colors">
                                        <td class="px-5 py-3 font-extrabold text-slate-700">{{ $item->ingredient->name ?? 'Unknown' }}</td>
                                        <td class="px-5 py-3 text-right text-slate-500 font-medium whitespace-nowrap">
                                            {{ number_format($item->quantity, 1) }}
                                            <span class="text-[9px] text-slate-400 opacity-70">{{ $item->unit?->value ?? $item->ingredient->measurement_unit }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-right text-slate-500 font-medium">
                                            ₹{{ number_format($item->unit_price, 2) }}
                                            <div class="text-[9px] text-slate-400 opacity-60">per {{ $item->unit?->value }}</div>
                                        </td>
                                        <td class="px-5 py-3 text-right font-black text-slate-800">₹{{ number_format($item->total_price, 2) }}</td>
                                        <td class="px-5 py-3 text-center">
                                            @if($item->status == 'approved')
                                                <span class="text-[9px] font-black text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100 uppercase">OK</span>
                                            @elseif($item->status == 'pending')
                                                <span class="text-[9px] font-black text-amber-500 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-100 uppercase">WAIT</span>
                                            @else
                                                <span class="text-[9px] font-black text-rose-500 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-100 uppercase">X</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="px-5 py-3 bg-white/50 flex justify-between items-center border-t border-slate-50">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Recorded by: {{ $bill->creator->name ?? 'System' }}</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $bill->items_count }} Items Total</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white p-12 text-center rounded-2xl border border-dashed border-slate-300">
                    <div class="flex flex-col items-center gap-4 text-slate-400">
                        <i data-lucide="receipt" class="w-12 h-12 opacity-10"></i>
                        <p class="font-bold text-lg">No bills found</p>
                        <p class="text-sm">Try entering a purchase or checking your filters.</p>
                    </div>
                </div>
            @endforelse
        </div>
        
        @if($purchases->hasPages())
            <div class="mt-8">
                {{ $purchases->links() }}
            </div>
        @endif
    @endif
</div>

<!-- Modal-less Reject Logic -->
<form id="rejectForm" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="rejection_reason" id="rejection_reason">
</form>

<script>
    function rejectPurchase(id) {
        const reason = prompt("Enter rejection reason:");
        if (reason && reason.trim() !== '') {
            const form = document.getElementById('rejectForm');
            // Assuming the current context is /k/{kitchen_slug}/purchases
            // We use the relative path to trigger the route with kitchen_slug defaults
            form.action = `./purchases/${id}/reject`.replace('//', '/');
            document.getElementById('rejection_reason').value = reason;
            form.submit();
        }
    }
</script>
@endsection