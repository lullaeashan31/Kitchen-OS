@extends('layouts.app')

@section('header')
    <h1 class="text-2xl font-bold text-gray-800">Location Settings</h1>
@endsection

@section('content')
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
        <div class="flex items-center gap-4 mb-6 text-amber-600 bg-amber-50 p-4 rounded-xl">
            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            <div>
                <h3 class="font-bold">Configuration Needed</h3>
                <p class="text-sm">Please define the kitchen coordinates to enable Geofencing features.</p>
            </div>
        </div>

        <form>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Kitchen Latitude</label>
                    <input type="text" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200"
                        placeholder="e.g. 23.0225" value="23.0225">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Kitchen Longitude</label>
                    <input type="text" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200"
                        placeholder="e.g. 72.5714" value="72.5714">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Geofence Radius (Meters)</label>
                    <input type="number" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200" value="100">
                </div>
            </div>

            <div class="mt-8">
                <button type="button" class="btn btn-primary bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                    Save Location Settings
                </button>
            </div>
        </form>
    </div>
@endsection