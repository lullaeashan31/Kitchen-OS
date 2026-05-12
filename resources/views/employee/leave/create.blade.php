@extends('layouts.app')

@section('header')
    <div class="mb-8">
        <a href="{{ route('employee.leave.index') }}"
            class="text-blue-600 hover:underline flex items-center gap-1 text-sm font-bold mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Requests
        </a>
        <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Request Leave</h1>
        <p class="text-gray-500 mt-1">Submit your time off request for manager approval.</p>
    </div>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto">
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-xl">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i data-lucide="alert-circle" class="h-5 w-5 text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 font-bold">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
            <form action="{{ route('employee.leave.store') }}" method="POST" class="p-8 md:p-10">
                @csrf
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Start Date</label>
                            <input type="date" name="start_date" value="{{ old('start_date') }}" required
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800">
                            @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">End Date</label>
                            <input type="date" name="end_date" value="{{ old('end_date') }}" required
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800">
                            @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Leave Type</label>
                        <select name="type" required
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800">
                            <option value="casual" {{ old('type') == 'casual' ? 'selected' : '' }}>Casual Leave</option>
                            <option value="sick" {{ old('type') == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                            <option value="annual" {{ old('type') == 'annual' ? 'selected' : '' }}>Annual Leave</option>
                        </select>
                        <p style="font-size:0.75rem;color:#7A7A72;margin-top:6px;">Casual and annual leave requires at least 2 weeks' notice. Sick leave can be submitted on the day.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Reason</label>
                        <textarea name="reason" required rows="4" placeholder="Brief explanation for your leave request..."
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800">{{ old('reason') }}</textarea>
                        @error('reason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                        <div class="flex gap-3">
                            <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                            <div class="text-xs text-blue-800 leading-relaxed font-medium">
                                <p class="font-bold mb-1">Company Policy Reminder:</p>
                                <ul class="list-disc ml-4 space-y-1">
                                    <li>Casual and Annual leaves must be requested at least 14 days in advance.</li>
                                    <li>Requests made with less than 14 days notice will be automatically blocked (except
                                        Sick Leave).</li>
                                    <li>All leaves are subject to manager approval based on kitchen staffing requirements.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit"
                            class="w-full py-4 bg-blue-600 text-white font-bold rounded-xl shadow-lg hover:bg-blue-700 transform hover:scale-[1.02] transition-all flex items-center justify-center gap-2">
                            Submit Request
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection