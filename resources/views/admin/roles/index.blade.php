@extends('layouts.app')

@section('header')
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Roles</h1>
            <p class="text-gray-500 mt-1">Create roles and assign permissions. Use these when adding staff.</p>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-all text-sm">
            <i data-lucide="plus" class="w-5 h-5"></i> Add Role
        </a>
    </div>
@endsection

@section('content')
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i> {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="p-6 font-semibold">Role</th>
                        <th class="p-6 font-semibold">Slug</th>
                        <th class="p-6 font-semibold">Permissions</th>
                        <th class="p-6 font-semibold">Staff</th>
                        <th class="p-6 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($roles as $role)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="p-6">
                                <div class="font-bold text-gray-900">{{ $role->name }}</div>
                                @if($role->description)
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $role->description }}</div>
                                @endif
                            </td>
                            <td class="p-6 text-gray-600 font-mono text-sm">{{ $role->slug }}</td>
                            <td class="p-6"><span class="text-sm text-gray-700">{{ $role->permissions_count }} permissions</span></td>
                            <td class="p-6"><span class="text-sm text-gray-700">{{ $role->users_count }} staff</span></td>
                            <td class="p-6 text-right">
                                <a href="{{ route('admin.roles.edit', $role) }}" class="inline-flex items-center gap-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg text-sm">Edit</a>
                                @if($role->users_count === 0)
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('Delete this role?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 font-medium rounded-lg text-sm">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-gray-500">
                                No roles yet. <a href="{{ route('admin.roles.create') }}" class="text-blue-600 font-bold">Add first role</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
