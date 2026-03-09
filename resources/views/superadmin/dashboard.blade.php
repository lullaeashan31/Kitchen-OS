@extends('layouts.app')

@section('content')
    <div class="p-6">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Super Admin Dashboard</h1>
            <p class="text-gray-600">Central Kitchen Management System</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Manage Kitchens Card -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-4">
                    <i data-lucide="layout" class="text-orange-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Manage Kitchens</h3>
                <p class="text-gray-600 mb-4 text-sm">Create, edit, and manage multiple kitchen branches.</p>
                <a href="{{ route('superadmin.kitchens.index') }}"
                    class="inline-flex items-center text-orange-600 font-semibold hover:gap-2 transition-all">
                    View Kitchens <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>

            <!-- System Logs Card -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                    <i data-lucide="activity" class="text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Central Logs</h3>
                <p class="text-gray-600 mb-4 text-sm">Monitor system activity across all kitchen branches.</p>
                <a href="#" class="inline-flex items-center text-blue-600 font-semibold hover:gap-2 transition-all">
                    View Logs <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
        </div>
    </div>
@endsection