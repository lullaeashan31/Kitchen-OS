@extends('layouts.app')

@section('header')
    <h1 class="text-3xl font-bold text-gray-800">Pending Ingredients</h1>
    <p class="text-sm text-gray-500">Review and finalize new ingredients added by staff.</p>
@endsection

@section('content')
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        @if($pendingIngredients->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-orange-50 text-orange-800 text-xs uppercase tracking-wider border-b border-orange-100">
                            <th class="p-5 font-semibold">Ingredient Name</th>
                            <th class="p-5 font-semibold">Suggested Price</th>
                            <th class="p-5 font-semibold">Purchase Unit</th>
                            <th class="p-5 font-semibold">Threshold</th>
                            <th class="p-5 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($pendingIngredients as $ingredient)
                            <tr class="group hover:bg-orange-50/20 transition-colors">
                                <form action="{{ route('admin.ingredients.approve', $ingredient) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <td class="p-5">
                                        <div class="font-bold text-gray-800 text-lg">{{ $ingredient->name }}</div>
                                        <div class="text-xs text-orange-500 font-medium">New Arrival</div>
                                    </td>
                                    <td class="p-5">
                                        <input type="number" step="0.01" name="price" value="{{ old('price', $ingredient->price) }}"
                                            class="w-24 px-2 py-1 border rounded focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="0.00" required>
                                    </td>
                                    <td class="p-5">
                                        <input type="text" name="purchase_unit" value="{{ old('purchase_unit', 'kg') }}"
                                            class="w-20 px-2 py-1 border rounded focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="kg" required>
                                    </td>
                                    <td class="p-5">
                                        <input type="number" step="0.001" name="alert_threshold"
                                            value="{{ old('alert_threshold', '1.000') }}"
                                            class="w-20 px-2 py-1 border rounded focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="1.0">
                                    </td>
                                    <td class="p-5 text-right">
                                        <button type="submit"
                                            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 shadow-md transition-all flex items-center gap-2 ml-auto">
                                            <i data-lucide="check-circle" class="w-4 h-4"></i> Approve
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-16 text-center">
                <div class="w-20 h-20 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="check-circle" class="w-10 h-10"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">All Clear!</h3>
                <p class="text-gray-500">There are no pending ingredients to review at this time.</p>
            </div>
        @endif
    </div>
@endsection