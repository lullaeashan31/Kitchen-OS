@extends('layouts.app')

@section('header')
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Staff Performance Reviews</h1>
        <p class="text-gray-500 mt-1">Submit monthly ratings to calculate performance bonuses.</p>
    </div>
@endsection

@section('content')
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="p-6 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
            <h2 class="font-bold text-gray-700">Active Staff List</h2>
            <div class="text-sm text-gray-500">
                Reviewing for: <span class="font-bold text-blue-600">{{ date('F Y') }}</span>
            </div>
        </div>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="p-6 font-semibold">Staff Member</th>
                    <th class="p-6 font-semibold">Last Review</th>
                    <th class="p-6 font-semibold">Bonus Status</th>
                    <th class="p-6 font-semibold text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($staff as $member)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="p-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold">
                                    {{ substr($member->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900">{{ $member->name }}</div>
                                    <div class="text-xs text-gray-400">#{{ $member->staff_code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-6">
                            @php $lastReview = $member->performanceReviews->first(); @endphp
                            @if($lastReview)
                                <div class="text-sm font-medium text-gray-700">{{ $lastReview->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-400">Score: {{ $lastReview->total_score }}/25</div>
                            @else
                                <span class="text-xs text-gray-400 italic">No history</span>
                            @endif
                        </td>
                        <td class="p-6">
                            @if($member->variable_enabled)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-50 text-green-700 border border-green-100">
                                    Eligible (Max: ₹{{ number_format($member->max_variable_amount) }})
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-50 text-gray-400 border border-gray-100">
                                    Fixed Salary Only
                                </span>
                            @endif
                        </td>
                        <td class="p-6 text-right">
                            <a href="{{ route('admin.performance.create', ['user_id' => $member->id]) }}" 
                               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow transition-all text-xs">
                                <i data-lucide="award" class="w-4 h-4"></i>
                                Submit Review
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
