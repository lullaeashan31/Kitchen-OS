@extends('layouts.app')

@section('header')
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Roles</h1>
            <p class="text-gray-500 mt-1">Create roles and assign permissions. Use these when adding staff.</p>
        </div>
        <a href="{{ route('admin.roles.create', ['kitchen_slug' => request()->route('kitchen_slug')]) }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-all text-sm uppercase tracking-widest">
            <i data-lucide="plus" class="w-5 h-5"></i> Add Role
        </a>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($roles as $role)
            <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 p-6 hover:shadow-xl hover:shadow-blue-500/5 transition-all group flex flex-col">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all duration-500">
                        <i data-lucide="briefcase" class="w-6 h-6"></i>
                    </div>
                    @if($role->users_count === 0)
                        <form action="{{ route('admin.roles.destroy', ['kitchen_slug' => request()->route('kitchen_slug'), 'role' => $role]) }}" method="POST" onsubmit="return confirm('Delete this role?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-slate-300 hover:text-red-500 transition-colors">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </form>
                    @endif
                </div>

                <div class="space-y-1 mb-6">
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ $role->name }}</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">{{ $role->slug }}</p>
                </div>

                @if($role->description)
                    <p class="text-sm text-slate-500 font-medium leading-relaxed mb-6 flex-1 line-clamp-2">
                        {{ $role->description }}
                    </p>
                @else
                    <p class="text-sm text-slate-300 italic font-medium leading-relaxed mb-6 flex-1">
                        No description provided.
                    </p>
                @endif

                <div class="pt-6 border-t border-slate-50 flex items-center justify-between mt-auto">
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col">
                            <span class="text-sm font-black text-slate-800">{{ $role->permissions_count }}</span>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Rules</span>
                        </div>
                        <div class="w-px h-6 bg-slate-100"></div>
                        <div class="flex flex-col">
                            <span class="text-sm font-black text-slate-800">{{ $role->users_count }}</span>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Users</span>
                        </div>
                    </div>

                    <a href="{{ route('admin.roles.edit', ['kitchen_slug' => request()->route('kitchen_slug'), 'role' => $role]) }}" 
                       class="px-5 py-2.5 bg-slate-900 hover:bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg shadow-slate-200 hover:shadow-blue-200">
                        Configure
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center">
                <div class="w-20 h-20 bg-slate-50 rounded-[40px] flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="ghost" class="w-10 h-10 text-slate-200"></i>
                </div>
                <h3 class="text-xl font-black text-slate-900">No Roles Yet</h3>
                <p class="text-slate-500 font-medium mt-2">Start by creating your first organizational role.</p>
                <a href="{{ route('admin.roles.create', ['kitchen_slug' => request()->route('kitchen_slug')]) }}" class="inline-flex items-center gap-2 mt-6 text-blue-600 font-black uppercase tracking-widest text-sm">
                    Create Role <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        @endforelse
    </div>
@endsection
