@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-3">
        <div class="p-2 border border-accent rounded text-accent">
            <i data-lucide="shopping-cart" class="w-6 h-6"></i>
        </div>
        <div>
            <h1>Purchase History</h1>
            <p class="text-muted text-sm">Track and verify all inventory orders and invoices.</p>
        </div>
    </div>
@endsection

@section('actions')
    <div class="flex gap-3 items-center flex-wrap">
        <a href="{{ route('purchases.download.90days') }}" 
           class="btn btn-secondary">
            <i data-lucide="download"></i> 
            Download Invoices
        </a>
        <a href="{{ route('purchases.create') }}" 
           class="btn btn-primary">
            <i data-lucide="plus"></i> 
            New Purchase
        </a>
    </div>
@endsection

@section('content')
<style>
    .view-toggle-active {
        background: var(--accent);
        color: var(--bg-primary);
    }
    .purchase-row:hover { background-color: rgba(242, 237, 230, 0.02); }
</style>

<div class="space-y-6">
    <!-- Search and View Toggle -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex bg-card p-1 rounded border border-subtle w-full sm:w-auto" style="background: var(--bg-card); border: 1px solid var(--border-subtle);">
            <a href="{{ request()->fullUrlWithQuery(['view' => 'item']) }}" 
               class="flex-1 sm:px-6 py-2 rounded text-center text-sm font-bold transition-all {{ $view === 'item' ? 'view-toggle-active' : 'text-muted hover:text-primary' }}">
                Item-wise
            </a>
            <a href="{{ request()->fullUrlWithQuery(['view' => 'bill']) }}" 
               class="flex-1 sm:px-6 py-2 rounded text-center text-sm font-bold transition-all {{ $view === 'bill' ? 'view-toggle-active' : 'text-muted hover:text-primary' }}">
                Bill-wise
            </a>
        </div>
        
        <form action="{{ url()->current() }}" method="GET" class="relative w-full sm:w-64 group">
            <input type="hidden" name="view" value="{{ $view }}">
            <input type="text" name="search" placeholder="Search orders..." value="{{ request('search') }}"
                   class="form-control" style="padding-left: 2.5rem !important;">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted group-focus-within:text-accent transition-colors"></i>
        </form>
    </div>

    @if($view === 'item')
        <!-- Item-wise Table View -->
        <div class="card p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
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
                    <tbody>
                        @forelse($purchases as $purchase)
                            <tr class="purchase-row transition-colors">
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-muted">
                                    {{ $purchase->purchase_date->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-primary">{{ $purchase->ingredient->name ?? 'Unknown' }}</div>
                                    <div class="text-[10px] text-accent font-bold uppercase tracking-wide">{{ $purchase->ingredient->category->name ?? '' }}</div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-sm font-medium text-primary">{{ $purchase->vendor_name ?? $purchase->vendor->name ?? 'Direct Purchase' }}</div>
                                </td>
                                <td class="px-4 py-4 text-right whitespace-nowrap">
                                    <span class="text-sm font-bold text-primary">{{ number_format($purchase->quantity, 2) }}</span>
                                    <span class="text-xs text-muted font-medium ml-1">{{ $purchase->unit?->value ?? $purchase->ingredient->measurement_unit ?? '' }}</span>
                                </td>
                                <td class="px-4 py-4 text-right whitespace-nowrap">
                                    <span class="text-sm font-extrabold text-primary">₹{{ number_format($purchase->total_price, 2) }}</span>
                                    <div class="text-[10px] text-muted">₹{{ number_format($purchase->unit_price, 2) }}/{{ $purchase->unit?->value ?? 'unit' }}</div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        @if($purchase->invoice_url)
                                            <a href="{{ route('purchases.view.invoice', $purchase) }}" target="_blank" class="p-1.5 hover:text-accent transition-colors" title="View Invoice">
                                                <i data-lucide="receipt" class="w-4 h-4"></i>
                                            </a>
                                        @endif
                                        @if(!empty($purchase->goods_photo_path))
                                            @foreach($purchase->goods_photo_path as $idx => $path)
                                                <a href="{{ route('purchases.view.goods', ['purchase' => $purchase->id, 'index' => $idx]) }}" target="_blank" class="p-1.5 hover:text-accent transition-colors" title="View Goods">
                                                    <i data-lucide="package" class="w-4 h-4"></i>
                                                </a>
                                            @endforeach
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if($purchase->status == 'approved')
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($purchase->status == 'rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                                @if(auth()->user()->isAdmin())
                                    <td class="px-4 py-4 text-right">
                                        @if($purchase->status == 'pending')
                                            <div class="flex justify-end gap-1">
                                                <form action="{{ route('purchases.approve', $purchase) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="p-1.5 text-accent hover:opacity-80 transition-colors" title="Approve">
                                                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                                                    </button>
                                                </form>
                                                <button type="button" onclick="rejectPurchase('{{ $purchase->id }}')" class="p-1.5 text-muted hover:text-accent transition-colors" title="Reject">
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
                <div class="card p-0 overflow-hidden" x-data="{ expanded: false }">
                    <div class="p-5 flex flex-col sm:flex-row justify-between sm:items-center gap-4 border-b border-subtle">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 border border-accent flex items-center justify-center text-accent cursor-pointer" @click="expanded = !expanded">
                                <i data-lucide="receipt" class="w-6 h-6"></i>
                            </div>
                            <div class="cursor-pointer" @click="expanded = !expanded">
                                <div class="flex items-center gap-2">
                                    <h3 style="margin: 0; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-primary);">
                                        Bill #{{ $bill->id }}
                                        <span class="mx-2 text-muted">|</span>
                                        <span class="text-accent">Inv: {{ Str::limit(pathinfo($bill->invoice_photo_path, PATHINFO_FILENAME), 12, '..') ?: 'N/A' }}</span>
                                    </h3>
                                    @if($bill->is_pending)
                                        <span class="w-2 h-2 rounded-full bg-accent animate-pulse" title="Requires Attention"></span>
                                    @endif
                                </div>
                                <div class="flex items-center flex-wrap gap-x-4 gap-y-1 mt-1">
                                    <span class="text-[10px] text-muted font-bold flex items-center gap-1 uppercase">
                                        <i data-lucide="calendar" class="w-3 h-3"></i>
                                        {{ $bill->purchase_date->format('M d, Y') }}
                                    </span>
                                    <span class="text-[10px] text-muted font-bold flex items-center gap-1 uppercase">
                                        <i data-lucide="building-2" class="w-3 h-3"></i>
                                        {{ $bill->vendor_name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-3 sm:text-right">
                            <div class="bg-primary/50 px-3 py-2 rounded border border-subtle text-right" style="background: rgba(6, 16, 30, 0.5);">
                                <div class="text-[10px] font-bold text-muted uppercase tracking-widest">Grand Total</div>
                                <div class="text-lg font-bold text-accent leading-tight">₹{{ number_format($bill->total_bill_amount, 2) }}</div>
                            </div>
                            
                            @if(auth()->user()->isAdmin() && $bill->is_pending)
                                <form action="{{ route('purchases.bulk_approve') }}" method="POST">
                                    @csrf
                                    @foreach($bill->items->where('status', 'pending') as $p)
                                        <input type="hidden" name="ids[]" value="{{ $p->id }}">
                                    @endforeach
                                    <button type="submit" class="btn btn-primary">
                                        <i data-lucide="check-check"></i>
                                        Approve All
                                    </button>
                                </form>
                            @endif

                            <div class="flex gap-1">
                                @if($bill->invoice_url)
                                    <a href="{{ route('purchases.view.invoice', $bill->id) }}" target="_blank" 
                                       class="p-2 border border-subtle hover:text-accent transition-all flex items-center gap-1" 
                                       title="View Invoice">
                                        <i data-lucide="receipt" class="w-5 h-5"></i>
                                    </a>
                                @endif
                                
                                <button type="button" @click="expanded = !expanded" class="p-2 text-muted hover:text-accent transition-all">
                                    <i data-lucide="chevron-down" class="w-5 h-5 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-primary/30 border-t border-subtle overflow-hidden transition-all duration-300" x-show="expanded" x-collapse>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bill->items as $item)
                                    <tr class="purchase-row transition-colors">
                                        <td class="px-5 py-3 font-bold text-primary">{{ $item->ingredient->name ?? 'Unknown' }}</td>
                                        <td class="px-5 py-3 text-right text-muted font-medium whitespace-nowrap">
                                            {{ number_format($item->quantity, 1) }}
                                            <span class="text-[9px]">{{ $item->unit?->value ?? $item->ingredient->measurement_unit }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-right text-muted font-medium">
                                            ₹{{ number_format($item->unit_price, 2) }}
                                        </td>
                                        <td class="px-5 py-3 text-right font-bold text-accent">₹{{ number_format($item->total_price, 2) }}</td>
                                        <td class="px-5 py-3 text-center">
                                            @if($item->status == 'approved')
                                                <span class="badge badge-success">OK</span>
                                            @elseif($item->status == 'pending')
                                                <span class="badge badge-warning">WAIT</span>
                                            @else
                                                <span class="badge badge-danger">X</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="px-5 py-3 bg-card flex justify-between items-center border-t border-subtle">
                            <span class="text-[10px] font-bold text-muted uppercase tracking-widest">Recorded by: {{ $bill->creator->name ?? 'System' }}</span>
                            <span class="text-[10px] font-bold text-muted uppercase tracking-widest">{{ $bill->items_count }} Items Total</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card p-12 text-center" style="border-style: dashed;">
                    <div class="flex flex-col items-center gap-4 text-muted">
                        <i data-lucide="receipt" class="w-12 h-12 opacity-20"></i>
                        <p class="font-bold text-lg">No bills found</p>
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