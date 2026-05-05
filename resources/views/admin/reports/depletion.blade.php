@extends('layouts.app')

@section('title', 'FIFO Depletion Report')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1>Inventory Depletion Analytics</h1>
            <p class="text-muted text-sm">Track batch-level consumption and plan procurement based on FIFO depletion rates.</p>
        </div>
        <button onclick="window.print()" class="btn btn-secondary">
            <i data-lucide="printer"></i> Print Report
        </button>
    </div>

    <div class="card p-0 overflow-hidden">
        <div class="p-6 border-b border-subtle flex justify-between items-center">
            <h2 class="text-lg uppercase tracking-widest text-primary">Batch Depletion Status</h2>
            <span class="badge badge-success">Live Analytics</span>
        </div>
            
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ingredient</th>
                        <th>Category</th>
                        <th>Remaining Stock</th>
                        <th>Depletion Level</th>
                        <th>Oldest Batch</th>
                        <th>Batches</th>
                    </tr>
                </thead>
                <tbody>
                        @foreach($depletionData as $item)
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-primary">{{ $item['name'] }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-muted">
                            {{ $item['category'] }}
                        </td>
                        <td class="px-6 py-4 font-mono text-sm text-muted">
                            {{ number_format($item['total_remaining'], 2) }} {{ $item['unit'] }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="w-full bg-primary/20 rounded-full h-1.5 mb-1 max-w-[150px]">
                                @php
                                    $color = 'var(--accent)';
                                    if($item['depletion_percentage'] > 90) $color = '#EF4444'; // Keep red for critical
                                @endphp
                                <div class="h-1.5 rounded-full" style="width: {{ $item['depletion_percentage'] }}%; background: {{ $color }};"></div>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-widest {{ $item['depletion_percentage'] > 90 ? 'text-red-500' : 'text-muted' }}">
                                {{ $item['depletion_percentage'] }}% consumed
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="text-primary font-medium">{{ $item['oldest_batch_date'] }}</div>
                            <div class="text-[10px] text-muted font-bold uppercase">{{ number_format($item['oldest_batch_remaining'], 2) }} {{ $item['unit'] }} left</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge badge-secondary">
                                {{ $item['batch_count'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>


@endsection
