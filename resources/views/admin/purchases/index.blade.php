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
                <tr>
                    <th
                        style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                        Date</th>
                    <th
                        style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                        Item</th>
                    <th
                        style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                        Quantity</th>
                    <th
                        style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                        Price</th>
                    <th
                        style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">
                        Entered By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 0.75rem 1rem;">{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                        <td style="padding: 0.75rem 1rem;">
                            {{ $purchase->ingredient->name ?? 'Unknown' }}
                            <div style="font-size: 0.75rem; color: #94a3b8;">{{ $purchase->ingredient->category ?? '' }}</div>
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right;">
                            {{ number_format($purchase->quantity, 3) }} {{ $purchase->ingredient->measurement_unit ?? '' }}
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right;">₹{{ number_format($purchase->price, 2) }}</td>
                        <td style="padding: 0.75rem 1rem;">{{ $purchase->creator->name ?? 'System' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding: 2rem; text-align: center; color: #94a3b8;">
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
@endsection