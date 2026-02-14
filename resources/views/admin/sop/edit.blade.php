@extends('layouts.app')

@section('header')
    <h2 class="text-xl font-semibold">Edit SOP Checklist</h2>
    <p class="text-sm text-gray-500">Update tasks and assignments for this procedure.</p>
@endsection

@section('content')
    <form action="{{ route('admin.sop.update', $sop) }}" method="POST" class="max-w-4xl mx-auto">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Checklist Info -->
            <div class="md:col-span-2 space-y-6">
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <h3 class="text-lg font-bold mb-4">General Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Checklist Name</label>
                            <input type="text" name="name" value="{{ $sop->name }}" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                            <textarea name="description" rows="3"
                                class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none">{{ $sop->description }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">Checklist Items</h3>
                        <button type="button" onclick="addItem()"
                            class="text-primary text-sm font-bold flex items-center gap-1 hover:underline">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Item
                        </button>
                    </div>

                    <div id="items-container" class="space-y-3">
                        @foreach($sop->items as $index => $item)
                            <div class="item-row flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-100">
                                <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                <div class="cursor-move text-gray-400">
                                    <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                                </div>
                                <input type="text" name="items[{{ $index }}][name]" value="{{ $item->name }}" required
                                    class="flex-1 bg-white px-3 py-1.5 rounded border border-gray-200 outline-none">
                                <label
                                    class="flex items-center gap-2 text-xs font-semibold text-gray-600 bg-white px-3 py-1.5 rounded border border-gray-200 cursor-pointer">
                                    <input type="checkbox" name="items[{{ $index }}][is_photo_required]" value="1" {{ $item->is_photo_required ? 'checked' : '' }} class="rounded border-gray-300">
                                    Photo Required
                                </label>
                                <button type="button" onclick="this.closest('.item-row').remove()"
                                    class="p-1.5 text-red-400 hover:text-red-500">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Meta -->
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <h3 class="text-lg font-bold mb-4">Assignment</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assign Shift</label>
                            <select name="shift_id" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-primary">
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}" {{ $sop->shift_id == $shift->id ? 'selected' : '' }}>
                                        {{ $shift->name }}
                                        ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assign Role</label>
                            <select name="role" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-primary">
                                <option value="staff" {{ $sop->role === 'staff' ? 'selected' : '' }}>Kitchen Staff</option>
                                <option value="manager" {{ $sop->role === 'manager' ? 'selected' : '' }}>Manager</option>
                                <option value="admin" {{ $sop->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deadline Time</label>
                            <input type="time" name="deadline_time" value="{{ $sop->deadline_time }}" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <h3 class="text-lg font-bold mb-4">Status</h3>
                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Current Status:</span>
                            <span
                                class="font-bold {{ $sop->status === 'active' ? 'text-green-600' : 'text-gray-600' }}">{{ ucfirst($sop->status) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Items:</span>
                            <span class="font-bold">{{ $sop->items->count() }}</span>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3 bg-primary text-white font-bold rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-600 transition-colors">
                    Update Checklist
                </button>
                <a href="{{ route('admin.sop.index') }}"
                    class="block w-full text-center py-3 text-gray-500 font-semibold hover:text-gray-700">
                    Back to List
                </a>
            </div>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        let itemCount = {{ $sop->items->count() }};

        function addItem() {
            const container = document.getElementById('items-container');
            const html = `
                    <div class="item-row flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <div class="cursor-move text-gray-400">
                            <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="items[${itemCount}][name]" required class="flex-1 bg-white px-3 py-1.5 rounded border border-gray-200 outline-none" placeholder="What needs to be done?">
                        <label class="flex items-center gap-2 text-xs font-semibold text-gray-600 bg-white px-3 py-1.5 rounded border border-gray-200 cursor-pointer">
                            <input type="checkbox" name="items[${itemCount}][is_photo_required]" value="1" class="rounded border-gray-300">
                            Photo Required
                        </label>
                        <button type="button" onclick="this.closest('.item-row').remove()" class="p-1.5 text-red-400 hover:text-red-500">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                `;
            container.insertAdjacentHTML('beforeend', html);
            itemCount++;
            lucide.createIcons();
        }

        new Sortable(document.getElementById('items-container'), {
            animation: 150,
            handle: '.cursor-move',
            ghostClass: 'bg-blue-50'
        });
    </script>
@endsection