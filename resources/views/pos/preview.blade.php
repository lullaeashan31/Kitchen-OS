@extends('layouts.app')

@section('header')
    <h1 class="text-2xl font-bold text-gray-800">Review Sales Import</h1>
    <p class="text-sm text-gray-500">Confirm items to deduct from inventory.</p>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Mapped Items -->
        <div class="space-y-4">
            <h3 class="font-bold text-green-700 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5"></i> Mapped Items ({{ count($mappedItems) }})
            </h3>
            <div class="bg-white rounded-xl shadow-sm border border-green-100 overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-green-50 text-green-800 font-bold">
                        <tr>
                            <th class="p-3">POS Item</th>
                            <th class="p-3">Recipe Match</th>
                            <th class="p-3 text-right">Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($mappedItems as $item)
                            <tr>
                                <td class="p-3">{{ $item['name'] }}</td>
                                <td class="p-3 text-gray-600">{{ $item['recipe_name'] }}</td>
                                <td class="p-3 text-right font-mono">{{ $item['quantity'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Unmapped Items -->
        <div class="space-y-4">
            <h3 class="font-bold text-red-700 flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i> Unmapped Items ({{ count($unmappedItems) }})
            </h3>
            <div class="bg-white rounded-xl shadow-sm border border-red-100 overflow-hidden">
                <div class="p-4 bg-red-50 text-red-700 text-sm mb-0">
                    These items will be <strong>ignored</strong> during inventory deduction. Please create recipes with
                    matching names to track them.
                </div>
                <table class="w-full text-left text-sm">
                    <thead class="bg-red-50 text-red-800 font-bold">
                        <tr>
                            <th class="p-3">POS Item</th>
                            <th class="p-3 text-right">Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($unmappedItems as $item)
                            <tr>
                                <td class="p-3">{{ $item['name'] }}</td>
                                <td class="p-3 text-right font-mono">{{ $item['quantity'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-8 flex justify-between items-center bg-gray-50 p-6 rounded-xl border border-gray-200">
        <div class="text-sm text-gray-600">
            Proceeding will deduct stock for <strong>{{ count($mappedItems) }}</strong> items.
        </div>
        <div class="flex gap-4">
            <a href="{{ route('pos.upload') }}" class="btn btn-secondary">Cancel</a>
            <form action="{{ route('pos.process') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary bg-green-600 hover:bg-green-700 border-none">
                    <i data-lucide="play"></i> Confirm & Deduct Inventory
                </button>
            </form>
        </div>
    </div>
@endsection