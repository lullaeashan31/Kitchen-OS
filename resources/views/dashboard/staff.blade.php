@extends('layouts.app')

@section('header')
    <h1>Staff Dashboard</h1>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto mt-8">

        <!-- Screen 1: Time Clock Shortcut -->
        <a href="{{ route('attendance.tablet') }}" class="group block">
            <div
                class="card h-full flex flex-col items-center justify-center p-12 hover:bg-blue-50 transition-colors border-2 border-transparent hover:border-blue-500 cursor-pointer text-center">
                <div class="bg-blue-100 p-6 rounded-full mb-6 group-hover:scale-110 transition-transform">
                    <i data-lucide="clock" class="w-16 h-16 text-blue-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Time Clock</h2>
                <p class="text-gray-500">Clock In / Out via Tablet Mode</p>
            </div>
        </a>

        <!-- Screen 2: Recipe Management -->
        <a href="{{ route('recipes.create') }}" class="group block">
            <div
                class="card h-full flex flex-col items-center justify-center p-12 hover:bg-green-50 transition-colors border-2 border-transparent hover:border-green-500 cursor-pointer text-center">
                <div class="bg-green-100 p-6 rounded-full mb-6 group-hover:scale-110 transition-transform">
                    <i data-lucide="chef-hat" class="w-16 h-16 text-green-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Create Recipe</h2>
                <p class="text-gray-500">Add new recipe or view my recipes</p>
            </div>
        </a>

    </div>

    <div class="text-center mt-12 text-gray-400">
        <p>My Contributions: {{ $myRecipesCount }} Recipes Created</p>
    </div>
@endsection