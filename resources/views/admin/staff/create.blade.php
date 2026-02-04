@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.staff.index') }}"
            class="p-2 bg-white rounded-lg border border-gray-200 text-gray-500 hover:text-blue-600 hover:border-blue-300 transition-all shadow-sm">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Add New Staff</h1>
            <p class="text-sm text-gray-500">Register a new team member.</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="w-full pb-10">
        <form action="{{ route('admin.staff.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden mb-6">
                <!-- Profile & Photo Header -->
                <div class="bg-white p-8 border-b border-gray-100 flex flex-col md:flex-row items-center gap-8">
                    <div class="shrink-0 relative group">
                        <div
                            class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-2xl bg-gray-100 ring-4 ring-blue-50">
                            <div id="photo_placeholder"
                                class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400">
                                <i data-lucide="user" class="w-12 h-12"></i>
                            </div>
                            <img id="photo_preview" class="hidden w-full h-full object-cover">
                        </div>
                        <label for="profile_photo"
                            class="absolute bottom-1 right-1 bg-blue-600 text-white p-2.5 rounded-full cursor-pointer hover:bg-blue-700 shadow-lg transition-all transform hover:scale-110 border-2 border-white">
                            <i data-lucide="camera" class="w-5 h-5"></i>
                        </label>
                        <input type="file" name="profile_photo" id="profile_photo" class="hidden" accept="image/*"
                            onchange="previewImage(this)">
                    </div>
                    <div class="text-center md:text-left">
                        <h2 class="text-xl font-bold text-gray-800">New Employee Profile</h2>
                        <p class="text-sm text-gray-500 max-w-md">Fill in the details below to create an account. A profile
                            picture is recommended for identification.</p>
                    </div>
                </div>

                <div class="p-8">
                    <!-- Standard Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                        <div class="space-y-6">
                            <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-100">
                                <i data-lucide="user" class="w-5 h-5 text-blue-500"></i>
                                <h3 class="font-bold text-gray-700">Basic Information</h3>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Full Name</label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800 placeholder-gray-400">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Email Address</label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800 placeholder-gray-400">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Staff Code (Login ID)</label>
                                <input type="text" name="staff_code" value="{{ old('staff_code') }}" required maxlength="6"
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none font-mono tracking-widest text-center text-lg">
                                <p class="text-xs text-blue-500 mt-2 flex items-center gap-1 font-medium">
                                    <i data-lucide="info" class="w-3 h-3"></i> Required for Tablet Login
                                </p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-100">
                                <i data-lucide="lock" class="w-5 h-5 text-orange-500"></i>
                                <h3 class="font-bold text-gray-700">Security & Password</h3>
                            </div>

                            <div class="bg-orange-50/50 p-6 rounded-xl border border-orange-100">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                                <input type="password" name="password" required
                                    class="w-full px-4 py-3 rounded-xl bg-white border border-gray-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all outline-none mb-4">

                                <label class="block text-sm font-bold text-gray-700 mb-2">Confirm Password</label>
                                <input type="password" name="password_confirmation" required
                                    class="w-full px-4 py-3 rounded-xl bg-white border border-gray-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Section -->
                    <div class="border-t border-gray-100 pt-8">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-5 h-5 text-purple-600"></i>
                            Access Permissions
                        </h3>

                        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                            @if(isset($permissions) && $permissions->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($permissions as $permission)
                                        <label
                                            class="flex items-center space-x-3 p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-400 hover:shadow-md transition-all cursor-pointer group">
                                            <div class="relative flex items-center">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                    class="peer w-5 h-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300 transition-colors">
                                            </div>
                                            <span
                                                class="text-sm font-semibold text-gray-700 peer-checked:text-blue-700 transition-colors">{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-gray-500 text-sm italic">No custom permissions defined yet.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="bg-gray-50 px-8 py-6 border-t border-gray-100 flex justify-end items-center gap-4">
                    <a href="{{ route('admin.staff.index') }}"
                        class="px-6 py-3 rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-100 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-8 py-3 rounded-xl bg-blue-600 text-white font-bold shadow-lg hover:bg-blue-700 hover:shadow-blue-500/30 transition-all flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i> Create Staff Member
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        lucide.createIcons();
        function previewImage(input) {
            const preview = document.getElementById('photo_preview');
            const placeholder = document.getElementById('photo_placeholder');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    if (placeholder) placeholder.classList.add('hidden');
                }

                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection