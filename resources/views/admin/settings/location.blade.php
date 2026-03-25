@extends('layouts.app')

@section('header')
    <h1 class="text-2xl font-bold text-gray-800">Location Settings</h1>
@endsection

@section('content')
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl flex items-center gap-3 mb-6">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                <p class="text-sm font-bold text-emerald-800">{{ session('success') }}</p>
            </div>
        @endif

        <div class="flex items-center gap-4 mb-6 text-amber-600 bg-amber-50 p-4 rounded-xl">
            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            <div>
                <h3 class="font-bold">Device Geofencing</h3>
                <p class="text-sm">Define your kitchen coordinates. Staff can only clock-in if they are within the specified radius.</p>
            </div>
        </div>

        <form action="{{ route('admin.settings.location.update') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Kitchen Latitude</label>
                    <input type="text" name="latitude" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none"
                        placeholder="e.g. 23.0225" value="{{ $kitchen->latitude }}">
                    <p class="text-[10px] text-gray-400 mt-1 uppercase font-bold tracking-widest">WGS84 Format</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Kitchen Longitude</label>
                    <input type="text" name="longitude" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none"
                        placeholder="e.g. 72.5714" value="{{ $kitchen->longitude }}">
                    <p class="text-[10px] text-gray-400 mt-1 uppercase font-bold tracking-widest">WGS84 Format</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Geofence Radius (Meters)</label>
                    <input type="number" name="geofence_radius" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none" 
                        value="{{ $kitchen->geofence_radius ?? 100 }}">
                    <p class="text-[10px] text-gray-400 mt-1 uppercase font-bold tracking-widest">Recommended: 100m - 500m</p>
                </div>
            </div>

            <div class="mt-8">
                <button type="submit" class="bg-slate-900 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-black transition-all flex items-center gap-3 shadow-xl shadow-slate-900/10">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Save Location Intelligence
                </button>
            </div>
        </form>
    </div>
@endsection