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
                    <input type="text" name="name" value="{{ old('name', $shift->name ?? '') }}"
                        class="w-full rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="e.g. Morning Shift" required>
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
@endsection