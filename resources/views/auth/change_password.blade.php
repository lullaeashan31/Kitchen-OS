@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-md">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Change Password Required
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    For security, you must change your password on first login.
                </p>
            </div>
            <form class="mt-8 space-y-6" action="{{ route('password.update') }}" method="POST">
                @csrf

                <div class="rounded-md shadow-sm -space-y-px">
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                        <input id="password" name="password" type="password" required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="New Password">
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm
                            Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Confirm Password">
                    </div>
                </div>

                @if($errors->any())
                    <div class="text-red-500 text-sm">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection