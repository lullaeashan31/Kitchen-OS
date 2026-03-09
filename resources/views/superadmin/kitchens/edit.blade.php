@extends('layouts.app')

@section('content')
    <div class="p-6">
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Edit Kitchen: {{ $kitchen->name }}</h1>
                <p class="text-gray-600">Update branch details and administrator access</p>
            </div>
            <a href="{{ route('superadmin.kitchens.index') }}" class="text-blue-600 hover:underline">Back to List</a>
        </div>

        <div class="max-w-4xl bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <form action="{{ route('superadmin.kitchens.update', $kitchen) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="form-group">
                        <label class="form-label font-bold text-gray-700 mb-2 block">Kitchen Name <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $kitchen->name) }}"
                            required>
                        @error('name')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label font-bold text-gray-700 mb-2 block">URL Slug <span
                                class="text-red-500">*</span></label>
                        <div class="flex items-center">
                            <span
                                class="bg-gray-100 px-3 py-2 border border-r-0 border-gray-300 rounded-l-lg text-gray-500 text-sm">/k/</span>
                            <input type="text" name="slug" class="form-control rounded-l-none"
                                value="{{ old('slug', $kitchen->slug) }}" required>
                        </div>
                        @error('slug')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label class="form-label font-bold text-gray-700 mb-2 block">Branch Status <span
                            class="text-red-500">*</span></label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active', $kitchen->is_active) ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !old('is_active', $kitchen->is_active) ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <hr class="my-8 border-gray-100">

                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-800">Branch Administrator</h3>
                    <p class="text-sm text-gray-600">Modify the primary administrator for this branch.</p>
                </div>

                @if($admin)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="form-group">
                            <label class="form-label font-bold text-gray-700 mb-2 block">Admin Name <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="admin_name" class="form-control"
                                value="{{ old('admin_name', $admin->name) }}" required>
                            @error('admin_name')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label font-bold text-gray-700 mb-2 block">Phone Number (Login ID) <span
                                    class="text-red-500">*</span></label>
                            <input type="tel" name="admin_phone" class="form-control"
                                value="{{ old('admin_phone', $admin->phone) }}" required minlength="10" maxlength="10"
                                pattern="[0-9]{10}">
                            @error('admin_phone')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="form-group">
                            <label class="form-label font-bold text-gray-700 mb-2 block">Admin Code (6 Digits) <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="admin_code" class="form-control"
                                value="{{ old('admin_code', $admin->staff_code) }}" required minlength="6" maxlength="6"
                                pattern="[0-9]{6}">
                            @error('admin_code')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label font-bold text-gray-700 mb-2 block">New Password (Leave blank to keep
                                current)</label>
                            <input type="password" name="admin_password" class="form-control"
                                placeholder="Minimum 8 characters">
                            @error('admin_password')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                @else
                    <div class="p-4 bg-yellow-50 text-yellow-700 rounded-lg mb-8">
                        No administrator found for this kitchen. Please create one in the Staff section.
                    </div>
                @endif

                <div class="flex gap-4">
                    <button type="submit" class="btn btn-primary px-8">Save Changes</button>
                    <a href="{{ route('superadmin.kitchens.index') }}"
                        class="btn btn-secondary bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection