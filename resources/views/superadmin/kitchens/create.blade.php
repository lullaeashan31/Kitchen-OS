@extends('layouts.app')

@section('content')
    <div class="p-6">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Create New Kitchen</h1>
            <p class="text-gray-600">Setup a new branch for the system</p>
        </div>

        <div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <form action="{{ route('superadmin.kitchens.store') }}" method="POST">
                @csrf

                <div class="form-group mb-6">
                    <label class="form-label font-bold text-gray-700 mb-2 block">Kitchen Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Jaipur Kitchen" required
                        autofocus>
                    <p class="text-xs text-gray-500 mt-1">The display name of the kitchen branch.</p>
                    @error('name')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group mb-8">
                    <label class="form-label font-bold text-gray-700 mb-2 block">URL Slug <span
                            class="text-red-500">*</span></label>
                    <div class="flex items-center">
                        <span
                            class="bg-gray-100 px-3 py-2 border border-r-0 border-gray-300 rounded-l-lg text-gray-500 text-sm">/k/</span>
                        <input type="text" name="slug" class="form-control rounded-l-none" placeholder="e.g. jaipur-kitchen"
                            required value="{{ old('slug') }}">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">This will be used in the branch's unique URL. Use letters,
                        numbers, and hyphens only.</p>
                    @error('slug')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <hr class="my-8 border-gray-100">

                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-800">Branch Administrator</h3>
                    <p class="text-sm text-gray-600">This user will have full access to manage this branch.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="form-group">
                        <label class="form-label font-bold text-gray-700 mb-2 block">Admin Name <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="admin_name" class="form-control" placeholder="e.g. Rahul Sharma" required
                            value="{{ old('admin_name') }}">
                        @error('admin_name')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label font-bold text-gray-700 mb-2 block">Phone Number (Login ID) <span
                                class="text-red-500">*</span></label>
                        <input type="tel" name="admin_phone" class="form-control" placeholder="10 Digits" required
                            value="{{ old('admin_phone') }}" minlength="10" maxlength="10" pattern="[0-9]{10}">
                        @error('admin_phone')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="form-group">
                        <label class="form-label font-bold text-gray-700 mb-2 block">Admin Code (6 Digits) <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="admin_code" class="form-control" placeholder="e.g. 123456" required
                            value="{{ old('admin_code') }}" minlength="6" maxlength="6" pattern="[0-9]{6}">
                        @error('admin_code')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label font-bold text-gray-700 mb-2 block">Password <span
                                class="text-red-500">*</span></label>
                        <input type="password" name="admin_password" class="form-control" placeholder="Minimum 8 characters"
                            required>
                        @error('admin_password')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="btn btn-primary px-8">Create Kitchen</button>
                    <a href="{{ route('superadmin.kitchens.index') }}"
                        class="btn btn-secondary bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection