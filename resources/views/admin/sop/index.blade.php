@extends('layouts.app')

@section('header')
    <h2 class="text-xl font-semibold">SOP Checklist Management</h2>
    <p class="text-sm text-gray-500">Create and manage Standard Operating Procedures for your kitchen staff.</p>
@endsection

@section('actions')
    <a href="{{ route('admin.sop.create') }}"
        class="btn bg-primary text-white flex items-center gap-2 px-4 py-2 rounded-lg">
        <i data-lucide="plus-circle" class="w-4 h-4"></i> Create Checklist
    </a>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($checklists as $checklist)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                <div class="p-5">
                    <div class="flex justify-between items-start mb-4">
                        <span
                            class="px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $checklist->status === 'active' ? 'bg-green-100 text-green-800' : ($checklist->status === 'paused' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($checklist->status) }}
                        </span>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.sop.edit', $checklist) }}" class="text-gray-400 hover:text-primary">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('admin.sop.pause', $checklist) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-400 hover:text-yellow-500">
                                    <i data-lucide="{{ $checklist->status === 'paused' ? 'play-circle' : 'pause-circle' }}"
                                        class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $checklist->name }}</h3>
                    <p class="text-sm text-gray-500 mb-4">{{ Str::limit($checklist->description, 100) }}</p>

                    <div class="grid grid-cols-2 gap-y-2 gap-x-4 text-xs text-gray-500 mb-5">
                        <div class="flex items-center gap-1">
                            <i data-lucide="list-todo" class="w-3.5 h-3.5"></i>
                            {{ $checklist->items_count }} Items
                        </div>
                        @if($checklist->shift)
                            <div class="flex items-center gap-1">
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                {{ $checklist->shift->name }}
                            </div>
                        @endif
                        <div class="flex items-center gap-1">
                            <i data-lucide="user-square" class="w-3.5 h-3.5"></i>
                            {{ ucfirst($checklist->role) }}
                        </div>
                        <div class="flex items-center gap-1">
                            <i data-lucide="calendar-check" class="w-3.5 h-3.5"></i>
                            {{ \Carbon\Carbon::parse($checklist->deadline_time)->format('H:i') }}
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('admin.sop.edit', $checklist) }}"
                            class="flex-1 text-center py-2 text-sm font-semibold border border-gray-200 rounded-lg hover:bg-gray-50">
                            Manage Items
                        </a>
                        @if($checklist->status !== 'archived')
                            <form action="{{ route('admin.sop.archive', $checklist) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-2 text-sm font-semibold border border-gray-200 rounded-lg text-red-500 hover:bg-red-50"
                                    onclick="return confirm('Archive this checklist?')">
                                    <i data-lucide="archive" class="w-4 h-4"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-white rounded-xl border-2 border-dashed border-gray-200">
                <i data-lucide="clipboard-list" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900">No checklists found</h3>
                <p class="text-gray-500 mb-6">Start by creating your first Standard Operating Procedure.</p>
                <a href="{{ route('admin.sop.create') }}"
                    class="inline-flex items-center gap-2 text-primary font-semibold hover:underline">
                    <i data-lucide="plus" class="w-4 h-4"></i> Create New Checklist
                </a>
            </div>
        @endforelse
    </div>
@endsection