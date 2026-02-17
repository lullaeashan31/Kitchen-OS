@extends('layouts.app')

@section('header')
    <div class="mb-8">
        <a href="{{ route('admin.performance.index') }}"
            class="text-blue-600 hover:underline flex items-center gap-1 text-sm font-bold mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to List
        </a>
        <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Performance Review: {{ $user->name }}</h1>
        <p class="text-gray-500 mt-1">Submit ratings for {{ date('F Y') }}.</p>
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('admin.performance.store') }}" method="POST">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <input type="hidden" name="month" value="{{ date('n') }}">
            <input type="hidden" name="year" value="{{ date('Y') }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Rating Section -->
                <div class="space-y-6">
                    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                            <i data-lucide="star" class="w-5 h-5 text-amber-500"></i>
                            Key Metrics (1-5 Scale)
                        </h3>

                        @php
                            $metrics = [
                                'sop_compliance' => 'SOP Compliance',
                                'hygiene' => 'Kitchen Hygiene',
                                'punctuality' => 'Punctuality',
                                'teamwork' => 'Teamwork',
                                'technical_skill' => 'Technical Skills'
                            ];
                        @endphp

                        <div class="space-y-6">
                            @foreach($metrics as $key => $label)
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <label class="text-sm font-bold text-gray-700">{{ $label }}</label>
                                        <span class="text-blue-600 font-bold" id="val_{{ $key }}">3</span>
                                    </div>
                                    <input type="range" name="{{ $key }}" min="1" max="5" step="1" value="3"
                                        class="w-full h-2 bg-gray-100 rounded-lg appearance-none cursor-pointer accent-blue-600"
                                        oninput="document.getElementById('val_{{ $key }}').innerText = this.value; calculateTotal();">
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-100">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 font-bold">Total Score:</span>
                                <span class="text-3xl font-black text-gray-900" id="total_score">15</span>
                            </div>
                            <div class="text-[10px] text-gray-400 mt-1 uppercase tracking-widest">Max possible: 25</div>
                        </div>
                    </div>
                </div>

                <!-- Summary & Submission -->
                <div class="space-y-6">
                    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                            <i data-lucide="message-square" class="w-5 h-5 text-blue-500"></i>
                            Comments & Observations
                        </h3>
                        <textarea name="comments" rows="6"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-sm text-gray-800"
                            placeholder="Provide feedback to the employee..."></textarea>
                    </div>

                    <div class="bg-blue-600 p-8 rounded-3xl shadow-xl text-white">
                        <div class="mb-4">
                            <div class="text-xs uppercase tracking-widest opacity-70 mb-1 font-bold">Estimated Bonus</div>
                            <div class="text-4xl font-black">₹<span id="bonus_preview">0</span></div>
                        </div>
                        <p class="text-xs opacity-80 leading-relaxed mb-6">
                            Bonus is calculated as <code>(Score / 25) × Eligible Max Variable Amount</code>.
                            The final amount will be included in next month's payroll.
                        </p>
                        <button type="submit"
                            class="w-full py-4 bg-white text-blue-600 font-bold rounded-xl shadow-lg hover:bg-gray-50 transition-all transform hover:scale-[1.02]">
                            Submit Review
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            const maxVariable = {{ $user->max_variable_amount ?? 0 }};

            function calculateTotal() {
                const metrics = ['sop_compliance', 'hygiene', 'punctuality', 'teamwork', 'technical_skill'];
                let total = 0;
                metrics.forEach(m => {
                    total += parseInt(document.getElementsByName(m)[0].value);
                });
                document.getElementById('total_score').innerText = total;

                const bonus = (total / 25) * maxVariable;
                document.getElementById('bonus_preview').innerText = Math.round(bonus).toLocaleString();
            }

            // Initial calc
            window.addEventListener('DOMContentLoaded', calculateTotal);
        </script>
    @endpush
@endsection