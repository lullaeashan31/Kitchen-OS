@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.roles.index') }}"
            class="p-2 bg-white rounded-lg border border-gray-200 text-gray-500 hover:text-blue-600 hover:border-blue-300 transition-all shadow-sm">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Role: {{ $role->name }}</h1>
            <p class="text-sm text-gray-500">Update permissions for this role.</p>
        </div>
    </div>
@endsection

@section('content')
    <form action="{{ route('admin.roles.update', ['kitchen_slug' => request()->route('kitchen_slug'), 'role' => $role]) }}" method="POST" class="max-w-4xl">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-6 flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4"></i> Role Details
                    </h3>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Role Name</label>
                            <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none @error('name') border-red-500 @enderror font-bold">
                            @error('name') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 font-mono">Slug (identifier)</label>
                            <input type="text" name="slug" value="{{ old('slug', $role->slug) }}" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 focus:border-blue-500 outline-none font-mono text-sm text-gray-500 @error('slug') border-red-500 @enderror">
                            @error('slug') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 font-black uppercase tracking-tight">Purpose</label>
                            <textarea name="description" rows="3"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 transition-all outline-none text-sm">{{ old('description', $role->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                        <h3 class="text-lg font-black text-slate-800 tracking-tight flex items-center gap-2">
                            <i data-lucide="key" class="w-5 h-5 text-indigo-500"></i> Configure Permissions
                        </h3>
                        <div class="flex gap-2">
                            <button type="button" onclick="selectAll()" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-widest rounded-lg border border-indigo-100 hover:bg-indigo-100 transition-colors">Select All</button>
                            <button type="button" onclick="deselectAll()" class="px-3 py-1.5 bg-gray-50 text-gray-600 text-[10px] font-black uppercase tracking-widest rounded-lg border border-gray-100 hover:bg-gray-100 transition-colors">Clear All</button>
                        </div>
                    </div>

                    <div class="space-y-8 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
                        @php 
                            $rolePermissionIds = $role->permissions->pluck('id')->toArray();
                            $grouped = $permissions->groupBy(function($p) {
                                if (str_starts_with($p->slug, 'module_')) return 'Module Access';
                                if (str_starts_with($p->slug, 'manage_')) return 'Management';
                                if (str_starts_with($p->slug, 'view_')) return 'Visibility';
                                return 'Core Permissions';
                            });
                        @endphp
                        
                        @foreach($grouped as $groupName => $groupPermissions)
                            <div class="space-y-3">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400 bg-slate-50 px-3 py-1 rounded inline-block">{{ $groupName }}</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($groupPermissions as $p)
                                        <label class="group relative flex items-center gap-3 p-4 rounded-2xl border-2 {{ in_array($p->id, old('permissions', $rolePermissionIds)) ? 'border-blue-100 bg-blue-50/50' : 'border-gray-50 bg-gray-50/30' }} hover:bg-white hover:border-blue-200 transition-all cursor-pointer">
                                            <div class="relative flex items-center shrink-0">
                                                <input type="checkbox" name="permissions[]" value="{{ $p->id }}"
                                                    {{ in_array($p->id, old('permissions', $rolePermissionIds)) ? 'checked' : '' }}
                                                    class="peer checkbox-item w-6 h-6 rounded-lg border-2 border-gray-300 text-blue-600 focus:ring-0 focus:ring-offset-0 transition-all checked:border-blue-600">
                                                <i data-lucide="check" class="absolute inset-0 m-auto w-4 h-4 text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity"></i>
                                            </div>
                                            <div class="flex-1">
                                                <span class="block text-sm font-black text-slate-700 uppercase tracking-tight group-hover:text-blue-700 transition-colors">{{ $p->name }}</span>
                                                <span class="block text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $p->slug }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('permissions') <p class="text-red-500 text-xs mt-3 font-bold">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <button type="submit" class="flex-1 px-8 py-4 bg-slate-900 hover:bg-slate-800 text-white font-black uppercase tracking-widest rounded-2xl shadow-xl shadow-slate-200 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-5 h-5"></i>
                        Update Registry Access
                    </button>
                    <a href="{{ route('admin.roles.index') }}" class="px-8 py-4 bg-white border border-gray-200 text-gray-500 hover:text-gray-800 rounded-2xl font-black uppercase tracking-widest text-xs transition-colors flex items-center justify-center">Cancel</a>
                </div>
            </div>
        </div>
    </form>

    <script>
        function selectAll() {
            document.querySelectorAll('.checkbox-item').forEach(cb => cb.checked = true);
            updateVisuals();
        }

        function deselectAll() {
            document.querySelectorAll('.checkbox-item').forEach(cb => cb.checked = false);
            updateVisuals();
        }

        function updateVisuals() {
            document.querySelectorAll('.checkbox-item').forEach(cb => {
                const label = cb.closest('label');
                if (cb.checked) {
                    label.classList.add('border-blue-100', 'bg-blue-50/50');
                    label.classList.remove('border-gray-50', 'bg-gray-50/30');
                } else {
                    label.classList.remove('border-blue-100', 'bg-blue-50/50');
                    label.classList.add('border-gray-50', 'bg-gray-50/30');
                }
            });
        }

        document.querySelectorAll('.checkbox-item').forEach(cb => {
            cb.addEventListener('change', updateVisuals);
        });

        // Initialize icons after any dynamic changes
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }
    </style>
@endsection
