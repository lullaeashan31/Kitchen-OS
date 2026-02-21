@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.roles.index') }}"
            class="p-2 bg-white rounded-lg border border-gray-200 text-gray-500 hover:text-blue-600 hover:border-blue-300 transition-all shadow-sm">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Add Role</h1>
            <p class="text-sm text-gray-500">Create a role and assign permissions.</p>
        </div>
    </div>
@endsection

@section('content')
    <form action="{{ route('admin.roles.store') }}" method="POST" class="max-w-2xl">
        @csrf
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 mb-6">
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Role Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        placeholder="e.g. Manager, Cook"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none @error('name') border-red-500 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Slug (unique)</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" required
                        placeholder="e.g. manager, cook"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none font-mono @error('slug') border-red-500 @enderror">
                    @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Description (optional)</label>
                    <input type="text" name="description" value="{{ old('description') }}"
                        placeholder="e.g. Kitchen manager, head cook"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">Permissions</label>
                    <p class="text-xs text-gray-500 mb-3">Select what this role can do. Staff with this role will get these permissions.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto p-3 bg-gray-50 rounded-xl border border-gray-100">
                        @foreach($permissions as $p)
                            <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-white transition-colors cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $p->id }}" {{ in_array($p->id, old('permissions', [])) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-800">{{ $p->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('permissions.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.roles.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl font-bold text-gray-700 hover:bg-gray-50 transition-all">Cancel</a>
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg transition-all">Create Role</button>
        </div>
    </form>
@endsection
