@extends('layouts.app')

@section('header')
    <div class="mb-8">
        <a href="{{ route('admin.staff.index') }}" class="text-blue-600 hover:underline flex items-center gap-2 mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to Staff Management
        </a>
        <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Manage Permissions</h1>
        <p class="text-gray-500 mt-1">Assign module-wise access for <strong>{{ $user->name }}</strong>
            ({{ $user->role->label() }})</p>
    </div>
@endsection

@section('content')
    <div class="max-w-4xl">
        <form action="{{ route('admin.staff.permissions.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-lg font-bold text-gray-900">Module Access</h2>
                    <p class="text-sm text-gray-500">Toggle the modules this user is allowed to access.</p>
                </div>

                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($permissions as $permission)
                        <label
                            class="relative flex items-center p-4 rounded-2xl border-2 cursor-pointer transition-all hover:border-blue-200 group {{ in_array($permission->id, $userPermissions) ? 'border-blue-500 bg-blue-50/50' : 'border-gray-100 bg-white' }}">
                            <div class="flex-1">
                                <div class="font-bold text-gray-900">{{ $permission->name }}</div>
                                <div class="text-xs text-gray-500">{{ str_replace('module_', '', $permission->slug) }} access
                                </div>
                            </div>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="sr-only peer"
                                    {{ in_array($permission->id, $userPermissions) ? 'checked' : '' }}>
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none ring-offset-2 ring-blue-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="p-8 bg-gray-50 border-t border-gray-100 flex justify-end gap-4">
                    <a href="{{ route('admin.staff.index') }}"
                        class="px-6 py-3 rounded-xl font-bold text-gray-600 hover:bg-gray-200 transition-all">Cancel</a>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transform hover:scale-105 transition-all">
                        Update Permissions
                    </button>
                </div>
            </div>
        </form>

        @if($user->isAdmin())
            <div class="mt-8 p-6 bg-amber-50 border-l-4 border-amber-500 rounded-xl">
                <div class="flex items-center gap-3 text-amber-800 font-bold mb-2">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    Administrator Note
                </div>
                <p class="text-sm text-amber-700">
                    As an Administrator, this user will have access to all modules by default, regardless of the permissions
                    selected here. However, it's still recommended to keep their relevant module permissions synced.
                </p>
            </div>
        @endif
    </div>
@endsection