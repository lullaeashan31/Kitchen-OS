@extends('layouts.app')

@section('content')
    <div class="p-6">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Manage Kitchens</h1>
                <p class="text-gray-600">List and manage all kitchen branches</p>
            </div>
            <a href="{{ route('superadmin.kitchens.create') }}" class="btn btn-primary">
                + New Kitchen
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-700">Kitchen Name</th>
                        <th class="px-6 py-4 font-bold text-gray-700">Slug / URL</th>
                        <th class="px-6 py-4 font-bold text-gray-700">Status</th>
                        <th class="px-6 py-4 font-bold text-gray-700">Created At</th>
                        <th class="px-6 py-4 font-bold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($kitchens as $kitchen)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-gray-800 font-medium">{{ $kitchen->name }}</td>
                            <td class="px-6 py-4 text-gray-600">
                                <a href="{{ url('/k/' . $kitchen->slug) }}" class="hover:text-blue-600 hover:underline">
                                    <code>/k/{{ $kitchen->slug }}</code>
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                @if($kitchen->is_active)
                                    <span
                                        class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Active</span>
                                @else
                                    <span
                                        class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-sm">{{ $kitchen->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('superadmin.kitchens.edit', $kitchen) }}"
                                    class="text-blue-600 hover:underline">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No kitchens found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $kitchens->links() }}
        </div>
    </div>
@endsection