@extends('layouts.app')

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ auth()->user()->isSuperAdmin() ? route('superadmin.dashboard') : (app()->has('current_kitchen') ? route('dashboard') : '#') }}"
                class="p-2 bg-white rounded-lg border border-gray-200 text-gray-500 hover:text-blue-600 hover:border-blue-300 transition-all shadow-sm">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">My Profile</h1>
                <p class="text-sm text-gray-500">View your profile and onboarding information.</p>
            </div>
        </div>
        <a href="{{ route('profile.edit') }}"
            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-blue-600 text-white font-bold shadow-sm hover:bg-blue-700 transition-all shrink-0">
            <i data-lucide="pencil" class="w-5 h-5"></i>
            <span>Edit Profile</span>
        </a>
    </div>
@endsection

@section('content')
    <div class="w-full pb-10">
        @include('profile.partials.profile-content', ['user' => $user])
    </div>
@endsection