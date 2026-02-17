@extends('layouts.app')

@section('header')
    <h2 class="text-xl font-bold text-gray-800">{{ isset($shift) ? 'Edit Shift' : 'Create New Shift' }}</h2>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto">
        <form action="{{ isset($shift) ? route('admin.shifts.update', $shift) : route('admin.shifts.store') }}"
            method="POST" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            @csrf
            @if(isset($shift)) @method('PUT') @endif

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Shift Name</label>
                    <div class="relative">
                        <select name="name" id="shift_name_select"
                            class="w-full rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500 appearance-none pr-10"
                            onchange="handleShiftNameChange()">
                            <option value="">Select or type new shift name...</option>
                            @if(isset($existingShifts) && $existingShifts->count() > 0)
                                @foreach($existingShifts as $existingShift)
                                    <option value="{{ $existingShift->name }}" {{ old('name') == $existingShift->name ? 'selected' : '' }}>
                                        {{ $existingShift->name }}
                                    </option>
                                @endforeach
                            @endif
                            <option value="__custom__">+ Create New Shift Name</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                    <input type="text" name="name_custom" id="shift_name_input" value="{{ old('name') }}"
                        class="w-full mt-2 rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500 hidden"
                        placeholder="e.g. Morning Shift">
                    <input type="hidden" name="name" id="shift_name_hidden" value="{{ old('name') }}">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Start Time</label>
                        <input type="time" name="start_time" value="{{ old('start_time', $shift->start_time ?? '') }}"
                            class="w-full rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">End Time</label>
                        <input type="time" name="end_time" value="{{ old('end_time', $shift->end_time ?? '') }}"
                            class="w-full rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Required Staff Count</label>
                    <input type="number" name="required_staff" value="{{ old('required_staff', $shift->required_staff ?? 1) }}"
                        class="w-full rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500" required min="1">
                    <p class="text-xs text-gray-400 mt-1">Number of staff members needed for this shift.</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $shift->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="is_active" class="text-sm font-semibold text-gray-700">Active Shift</label>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex gap-3">
                <button type="submit"
                    class="flex-1 bg-blue-600 text-white font-bold py-2.5 rounded-lg hover:bg-blue-700 transition-colors">
                    {{ isset($shift) ? 'Update Shift' : 'Create Shift' }}
                </button>
                <a href="{{ route('admin.shifts.index') }}"
                    class="px-6 py-2.5 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function handleShiftNameChange() {
            const select = document.getElementById('shift_name_select');
            const input = document.getElementById('shift_name_input');
            const hidden = document.getElementById('shift_name_hidden');
            const selectedValue = select.value;

            if (selectedValue === '__custom__') {
                // Show input field for custom name
                input.classList.remove('hidden');
                input.required = true;
                select.required = false;
                hidden.value = '';
                input.focus();
            } else if (selectedValue) {
                // Hide input, use selected value
                input.classList.add('hidden');
                input.required = false;
                select.required = true;
                hidden.value = selectedValue;
            } else {
                // Nothing selected
                input.classList.add('hidden');
                input.required = false;
                select.required = true;
                hidden.value = '';
            }
        }

        // Update hidden field when custom input changes
        document.getElementById('shift_name_input')?.addEventListener('input', function() {
            document.getElementById('shift_name_hidden').value = this.value;
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            handleShiftNameChange();
        });
    </script>
    @endpush
@endsection