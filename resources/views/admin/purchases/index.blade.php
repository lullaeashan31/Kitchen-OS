@extends('layouts.app')

@section('header')
    <h1 style="font-size: 1.5rem; font-weight: 600;">Purchase History</h1>
    <p style="color: var(--text-muted);">Track all inventory purchases.</p>
@endsection

@section('actions')
    <a href="{{ route('purchases.create') }}" class="btn btn-primary"
        style="padding: 0.5rem 1rem; background: var(--primary-color); color: white; border-radius: 0.375rem; text-decoration: none;">
        <i data-lucide="plus" style="width: 1rem; height: 1rem; display: inline-block;"></i> New Purchase
    </a>
@endsection

@section('content')
    <div style="background: white; border-radius: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <th
                        style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                        Date</th>
                    <th
                        style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                        Item</th>
                    <th
                        style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                        Vendor</th>
                    <th
                        style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                        Quantity</th>
                    <th
                        style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                        Total Price</th>
                    <th
                        style="padding: 0.75rem 1rem; text-align: center; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                        Invoice</th>
                    <th
                        style="padding: 0.75rem 1rem; text-align: center; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                        Goods</th>
                    <th
                        style="padding: 0.75rem 1rem; text-align: center; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                        Status</th>
                    <th
                        style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                        Entered By</th>
                    @if(auth()->user()->isAdmin())
                        <th
                            style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                            Actions</th>
                    @endif
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 0.75rem 1rem;">{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                        <td style="padding: 0.75rem 1rem;">
                            {{ $purchase->ingredient->name ?? 'Unknown' }}
                            <div style="font-size: 0.75rem; color: #94a3b8;">{{ $purchase->ingredient->category ?? '' }}</div>
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            {{ $purchase->vendor->name ?? $purchase->vendor ?? 'N/A' }}
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right;">
                            {{ number_format($purchase->quantity, 3) }} {{ $purchase->ingredient->measurement_unit ?? '' }}
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right;">₹{{ number_format($purchase->total_price ?? $purchase->price, 2) }}</td>
                        <td style="padding: 0.75rem 1rem; text-align: center;">
                            @if($purchase->invoice_url)
                                <a href="{{ $purchase->invoice_url }}" target="_blank" title="View Invoice" style="color: #3b82f6;">
                                    <i data-lucide="file-text" style="width: 1.25rem; height: 1.25rem; margin: 0 auto;"></i>
                                </a>
                            @else
                                <span style="color: #cbd5e1;">-</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: center;">
                            @if($purchase->goods_url)
                                <a href="{{ $purchase->goods_url }}" target="_blank" title="View Goods" style="color: #3b82f6;">
                                    <i data-lucide="package" style="width: 1.25rem; height: 1.25rem; margin: 0 auto;"></i>
                                </a>
                            @else
                                <span style="color: #cbd5e1;">-</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: center;">
                            @if($purchase->status == 'approved')
                                <span style="display: inline-block; padding: 0.25rem 0.75rem; font-size: 0.75rem; font-weight: 600; color: #027a48; background-color: #ecfdf5; border-radius: 9999px;">Approved</span>
                            @elseif($purchase->status == 'rejected')
                                <span style="display: inline-block; padding: 0.25rem 0.75rem; font-size: 0.75rem; font-weight: 600; color: #b91c1c; background-color: #fef2f2; border-radius: 9999px;">Rejected</span>
                            @else
                                <span style="display: inline-block; padding: 0.25rem 0.75rem; font-size: 0.75rem; font-weight: 600; color: #b45309; background-color: #fffbeb; border-radius: 9999px;">Pending</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem;">{{ $purchase->creator->name ?? 'System' }}</td>
                        @if(auth()->user()->isAdmin())
                            <td style="padding: 0.75rem 1rem; text-align: right;">
                                @if($purchase->status == 'pending')
                                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                        <form action="{{ route('purchases.approve', $purchase) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" style="background: none; border: none; cursor: pointer; color: #059669; padding: 0.25rem;" title="Approve">
                                                <i data-lucide="check-circle" style="width: 1.25rem; height: 1.25rem;"></i>
                                            </button>
                                        </form>
                                        <button type="button" onclick="rejectPurchase({{ $purchase->id }})" style="background: none; border: none; cursor: pointer; color: #dc2626; padding: 0.25rem;" title="Reject">
                                            <i data-lucide="x-circle" style="width: 1.25rem; height: 1.25rem;"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="padding: 2rem; text-align: center; color: #94a3b8;">
                            No purchases found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="padding: 1rem;">
            {{ $purchases->links() }}
        </div>
    </div>
    
    <!-- Reject Confirmation Modal logic (Simplified JS) -->
    <form id="rejectForm" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="rejection_reason" id="rejection_reason">
    </form>

    <script>
        function rejectPurchase(id) {
            const reason = prompt("Please enter a reason for rejection:");
            if (reason) {
                const form = document.getElementById('rejectForm');
                form.action = `/purchases/${id}/reject`; // Corrected path based on routes/web.php
                document.getElementById('rejection_reason').value = reason;
                form.submit();
            }
        }
    </script>
@endsection