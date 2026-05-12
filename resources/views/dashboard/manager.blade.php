@extends('layouts.app')

@section('header')
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-black tracking-tight text-primary">Manager Dashboard</h1>
        <p class="text-[10px] font-bold text-muted mt-1 uppercase tracking-widest">Here's what's happening in your kitchen today.</p>
    </div>
@endsection

@section('content')
    {{-- Overview Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        {{-- Kitchen Team --}}
        <a href="{{ route('ingredients.index') }}" class="card p-0 overflow-hidden group">
            <div class="p-8 relative">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 bg-accent text-primary rounded-xl transition-all group-hover:scale-110">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-[10px] font-black text-muted uppercase tracking-widest">Kitchen Team</h3>
                </div>
                <div class="flex items-baseline gap-3">
                    <div class="text-4xl font-black text-primary">{{ $stats['active_staff'] }}</div>
                    <div class="flex h-2 w-2 mb-2">
                        <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-accent opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-accent"></span>
                    </div>
                </div>
                <p class="text-[9px] font-bold text-accent uppercase tracking-tight mt-1">Active on Shift</p>
            </div>
        </a>

        {{-- Today's Check-ins --}}
        <a href="{{ route('admin.attendance.index') }}" class="card p-0 overflow-hidden group">
            <div class="p-8 relative">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 bg-white/5 text-muted rounded-xl border border-subtle">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-[10px] font-black text-muted uppercase tracking-widest">Check-ins Today</h3>
                </div>
                <div class="text-4xl font-black text-primary">{{ $stats['today_attendance'] }}</div>
                <p class="text-[9px] font-bold text-muted uppercase tracking-tight mt-1">Check-ins Recorded</p>
            </div>
        </a>

        {{-- Recipe Book --}}
        <a href="{{ route('recipes.index') }}" class="card p-0 overflow-hidden group">
            <div class="p-8 relative">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 bg-white/5 text-muted rounded-xl border border-subtle">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-[10px] font-black text-muted uppercase tracking-widest">Recipes</h3>
                </div>
                <div class="text-4xl font-black text-primary">{{ $stats['total_recipes'] }}</div>
                <p class="text-[9px] font-bold text-muted uppercase tracking-tight mt-1">Approved recipes</p>
            </div>
        </a>

        {{-- Needs Review --}}
        <a href="{{ route('ingredients.index') }}" class="card p-0 overflow-hidden group border-accent/20">
            <div class="p-8 relative">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 bg-accent/10 text-accent rounded-xl border border-accent/20">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-[10px] font-black text-muted uppercase tracking-widest">Needs Approval</h3>
                </div>
                <div class="text-4xl font-black text-accent">{{ $stats['pending_ingredients'] }}</div>
                <p class="text-[9px] font-bold text-accent uppercase tracking-tight mt-1">Awaiting approval</p>
            </div>
        </a>
    </div>

    {{-- Command Centre --}}
    <div class="mb-10">
        <h2 class="text-[11px] font-black text-accent uppercase tracking-[0.2em] mb-6 flex items-center gap-3">
            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
            Today's Overview
        </h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
            {{-- SOP Status --}}
            <a href="{{ route('admin.sop.reviews.index') }}" class="card p-6 group hover:border-accent/40">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-white/5 text-accent rounded-lg border border-subtle">
                        <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                    </div>
                    <span class="text-[9px] font-black text-muted uppercase tracking-widest">SOP Status</span>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <div class="text-2xl font-black text-primary">
                            {{ $stats['completed_checklists'] }}/{{ $stats['total_checklists'] }}
                        </div>
                        <p class="text-[9px] font-bold text-muted uppercase">Done today</p>
                    </div>
                    @php $sopPercent = $stats['total_checklists'] > 0 ? ($stats['completed_checklists'] / $stats['total_checklists']) * 100 : 0; @endphp
                    <div class="w-10 h-10 relative flex items-center justify-center">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle cx="20" cy="20" r="16" stroke="rgba(242,237,230,0.05)" stroke-width="3" fill="transparent" />
                            <circle cx="20" cy="20" r="16" stroke="var(--brass)" stroke-width="3" fill="transparent" stroke-dasharray="100.5" stroke-dashoffset="{{ 100.5 * (1 - $sopPercent / 100) }}" class="transition-all duration-700" />
                        </svg>
                        <span class="absolute text-[8px] font-black text-accent">{{ round($sopPercent) }}%</span>
                    </div>
                </div>
            </a>

            {{-- Overdue --}}
            <a href="{{ route('admin.sop.reviews.index') }}" class="card p-6 border-red-500/20 bg-red-500/5 hover:border-red-500/40">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-red-500/10 text-red-500 rounded-lg">
                        <i data-lucide="clock-alert" class="w-4 h-4"></i>
                    </div>
                    <span class="text-[9px] font-black text-red-500/60 uppercase tracking-widest">Risk</span>
                </div>
                <div class="text-2xl font-black text-red-500">{{ $stats['overdue_sop_count'] }}</div>
                <p class="text-[9px] font-bold text-red-500/40 uppercase">Overdue</p>
            </a>

            {{-- Inventory --}}
            <a href="{{ route('ingredients.index') }}" class="card p-6 group hover:border-accent/40">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-white/5 text-muted rounded-lg border border-subtle">
                        <i data-lucide="package-search" class="w-4 h-4"></i>
                    </div>
                    <span class="text-[9px] font-black text-muted uppercase tracking-widest">Inventory</span>
                </div>
                <div class="text-2xl font-black {{ $stats['low_stock_count'] > 0 ? 'text-accent' : 'text-primary' }}">
                    {{ $stats['low_stock_count'] }}
                </div>
                <p class="text-[9px] font-bold text-muted uppercase">Stock Alerts</p>
            </a>

            {{-- Purchases --}}
            <a href="{{ route('purchases.index') }}" class="card p-6 group hover:border-accent/40">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-white/5 text-muted rounded-lg border border-subtle">
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                    </div>
                    <span class="text-[9px] font-black text-muted uppercase tracking-widest">Procurement</span>
                </div>
                <div class="text-2xl font-black text-primary">{{ $stats['pending_purchases_count'] }}</div>
                <p class="text-[9px] font-bold text-muted uppercase">Pending</p>
            </a>

            {{-- POS Sync --}}
            <a href="{{ route('pos.upload') }}" class="card p-6 group hover:border-accent/40">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 {{ $stats['pos_synced_today'] ? 'bg-accent/10 text-accent' : 'bg-red-500/10 text-red-500' }} rounded-lg">
                        <i data-lucide="{{ $stats['pos_synced_today'] ? 'refresh-cw' : 'alert-circle' }}" class="w-4 h-4"></i>
                    </div>
                    <span class="text-[9px] font-black text-muted uppercase tracking-widest">Sales Sync</span>
                </div>
                <div class="text-lg font-black {{ $stats['pos_synced_today'] ? 'text-accent' : 'text-red-500' }} uppercase">
                    {{ $stats['pos_synced_today'] ? 'Synced' : 'Required' }}
                </div>
                <p class="text-[9px] font-bold text-muted uppercase">{{ $stats['pos_synced_today'] ? 'Data Up-to-date' : 'Sync Required' }}</p>
            </a>
        </div>
    </div>

    {{-- Management Tools --}}
    <div class="card p-8">
        <h2 class="text-[11px] font-black text-accent uppercase tracking-widest mb-8 flex items-center gap-3">
            <i data-lucide="briefcase" class="w-4 h-4"></i>
            Quick Actions
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <a href="{{ route('admin.attendance.index') }}" class="flex flex-col items-center justify-center p-8 rounded-2xl border-2 border-dashed border-subtle hover:border-accent/40 hover:bg-white/5 transition-all group text-center">
                <div class="w-16 h-16 bg-primary text-accent rounded-full border border-subtle flex items-center justify-center mb-6 group-hover:bg-accent group-hover:text-primary transition-all">
                    <i data-lucide="users" class="w-8 h-8"></i>
                </div>
                <h4 class="font-black text-primary uppercase text-xs tracking-widest">Staff Attendance</h4>
                <p class="text-[9px] font-bold text-muted uppercase mt-2">Monitor shift logs (View Only)</p>
            </a>

            <a href="{{ route('recipes.create') }}" class="flex flex-col items-center justify-center p-8 rounded-2xl border-2 border-dashed border-subtle hover:border-accent/40 hover:bg-white/5 transition-all group text-center">
                <div class="w-16 h-16 bg-primary text-accent rounded-full border border-subtle flex items-center justify-center mb-6 group-hover:bg-accent group-hover:text-primary transition-all">
                    <i data-lucide="plus-circle" class="w-8 h-8"></i>
                </div>
                <h4 class="font-black text-primary uppercase text-xs tracking-widest">New Recipe</h4>
                <p class="text-[9px] font-bold text-muted uppercase mt-2">Write a new recipe</p>
            </a>

            <a href="{{ route('ingredients.index') }}" class="flex flex-col items-center justify-center p-8 rounded-2xl border-2 border-dashed border-subtle hover:border-accent/40 hover:bg-white/5 transition-all group text-center">
                <div class="w-16 h-16 bg-primary text-accent rounded-full border border-subtle flex items-center justify-center mb-6 group-hover:bg-accent group-hover:text-primary transition-all">
                    <i data-lucide="database" class="w-8 h-8"></i>
                </div>
                <h4 class="font-black text-primary uppercase text-xs tracking-widest">Ingredients</h4>
                <p class="text-[9px] font-bold text-muted uppercase mt-2">Browse all ingredients</p>
            </a>
        </div>
    </div>
@endsection