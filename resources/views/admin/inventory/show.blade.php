@extends('layouts.app')

@section('header')
    <div style="display: flex; align-items: center; gap: 1rem;">
        <a href="{{ route('admin.inventory.index') }}" style="display: flex; align-items: center; color: #64748b; text-decoration: none;">
            <i data-lucide="arrow-left" style="width: 1.25rem; height: 1.25rem;"></i>
        </a>
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 600;">{{ $ingredient->name }} - History</h1>
            <p style="color: var(--text-muted);">Current Stock: {{ number_format($ingredient->current_stock, 3) }} {{ $ingredient->measurement_unit }}</p>
        </div>
    </div>
@endsection

@section('content')
    <div style="background: white; border-radius: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <tr>
                    <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Date/Time</th>
                    <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Action</th>
                    <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Change</th>
                    <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Stock After</th>
                    <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">User</th>
                    <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Context / Reason</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 0.75rem 1rem; color: #64748b;">
                            {{ $log->created_at->format('M d, Y H:i') }}
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            @if($log->action === 'purchase')
                                <span style="color: #16a34a; background: #dcfce7; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500;">Purchase</span>
                            @elseif($log->action === 'RECIPE_USE')
                                <span style="color: #ea580c; background: #ffedd5; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500;">Production</span>
                            @else
                                <span style="color: #4b5563; background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500;">{{ ucfirst($log->action) }}</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; {{ $log->quantity_change > 0 ? 'color: #16a34a;' : 'color: #ef4444;' }}">
                            {{ $log->quantity_change > 0 ? '+' : '' }}{{ number_format($log->quantity_change, 3) }}
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right;">
                            {{ number_format($log->stock_after, 3) }}
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            {{ $log->user->name ?? 'System' }}
                        </td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.875rem; color: #4b5563;">
                            @if($log->action === 'RECIPE_USE' && $log->productionLog && $log->productionLog->recipe)
                                Used in: <strong>{{ $log->productionLog->recipe->name }}</strong>
                            @elseif($log->recipe) {{-- Direct link fallback --}}
                                Used in: <strong>{{ $log->recipe->name }}</strong>
                            @elseif($log->reason)
                                {{ $log->reason }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 2rem; text-align: center; color: #94a3b8;">
                            No history available for this item.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div style="padding: 1rem;">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
