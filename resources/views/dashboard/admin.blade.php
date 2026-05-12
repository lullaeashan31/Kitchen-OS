@extends('layouts.app')

@section('header')
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-black tracking-tight text-primary">Admin Dashboard</h1>
        <p class="text-[10px] font-bold text-muted mt-1 uppercase tracking-widest">Good morning — here's what needs your attention today.</p>
    </div>
@endsection

@section('content')
    {{-- Overview Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        {{-- Total Staff --}}
        <a href="{{ route('admin.staff.index') }}" class="card p-0 overflow-hidden group">
            <div class="p-8 relative">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 bg-accent text-primary rounded-xl transition-all group-hover:scale-110">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-[10px] font-black text-muted uppercase tracking-widest">Staff</h3>
                </div>
                <div class="text-4xl font-black text-primary">{{ $stats['total_staff'] }}</div>
                <p class="text-[9px] font-bold text-muted uppercase tracking-tight mt-1">Total Registered Staff</p>
            </div>
        </a>

        {{-- Active Now --}}
        <a href="{{ route('admin.attendance.index') }}" class="card p-0 overflow-hidden group border-accent/20">
            <div class="p-8 relative">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 bg-white/5 text-accent rounded-xl border border-subtle">
                        <i data-lucide="activity" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-[10px] font-black text-muted uppercase tracking-widest">On Shift</h3>
                </div>
                <div class="flex items-baseline gap-3">
                    <div class="text-4xl font-black text-primary">{{ $stats['active_staff'] }}</div>
                    <div class="flex h-2 w-2 mb-2">
                        <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-accent opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-accent"></span>
                    </div>
                </div>
                <p class="text-[9px] font-bold text-accent uppercase tracking-tight mt-1">Currently On-Shift</p>
            </div>
        </a>

        {{-- Today's Attendance --}}
        <a href="{{ route('admin.attendance.index') }}" class="card p-0 overflow-hidden group">
            <div class="p-8 relative">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 bg-white/5 text-muted rounded-xl border border-subtle">
                        <i data-lucide="calendar-check" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-[10px] font-black text-muted uppercase tracking-widest">Attendance</h3>
                </div>
                <div class="text-4xl font-black text-primary">{{ $stats['today_clock_in'] }}</div>
                <p class="text-[9px] font-bold text-muted uppercase tracking-tight mt-1">
                    <span class="text-primary">{{ $stats['today_clock_out'] }}</span> Shifts Completed Today
                </p>
            </div>
        </a>

        {{-- Recipes --}}
        <a href="{{ route('recipes.index') }}" class="card p-0 overflow-hidden group">
            <div class="p-8 relative">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 bg-white/5 text-muted rounded-xl border border-subtle">
                        <i data-lucide="chef-hat" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-[10px] font-black text-muted uppercase tracking-widest">Recipes</h3>
                </div>
                <div class="text-4xl font-black text-primary">{{ $stats['total_recipes'] }}</div>
                @if($stats['pending_ingredients'] > 0)
                    <p class="text-[9px] font-black text-accent uppercase tracking-tight mt-1 bg-accent/5 px-2 py-0.5 rounded-full inline-block">
                        {{ $stats['pending_ingredients'] }} Ingredients Pending
                    </p>
                @else
                    <p class="text-[9px] font-bold text-muted uppercase tracking-tight mt-1">All ingredients approved</p>
                @endif
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
                    <span class="text-[9px] font-black text-muted uppercase tracking-widest">Checklists</span>
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
                    <span class="text-[9px] font-black text-red-500/60 uppercase tracking-widest">Overdue</span>
                </div>
                <div class="text-2xl font-black text-red-500">{{ $stats['overdue_sop_count'] }}</div>
                <p class="text-[9px] font-bold text-red-500/40 uppercase">Checklists overdue</p>
            </a>

            {{-- Inventory --}}
            <a href="{{ route('admin.inventory.index', ['stock_status' => 'low', 'include_out' => '1']) }}" class="card p-6 group hover:border-accent/40">
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
                <p class="text-[9px] font-bold text-muted uppercase">Awaiting Approval</p>
            </a>

            {{-- POS Sync --}}
            <a href="{{ route('pos.upload') }}" class="card p-6 group hover:border-accent/40">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 {{ $stats['pos_synced_today'] ? 'bg-accent/10 text-accent' : 'bg-red-500/10 text-red-500' }} rounded-lg">
                        <i data-lucide="{{ $stats['pos_synced_today'] ? 'refresh-cw' : 'alert-circle' }}" class="w-4 h-4"></i>
                    </div>
                    <span class="text-[9px] font-black text-muted uppercase tracking-widest">Sales Data</span>
                </div>
                <div class="text-lg font-black {{ $stats['pos_synced_today'] ? 'text-accent' : 'text-red-500' }} uppercase tracking-tighter">
                    {{ $stats['pos_synced_today'] ? 'Synced' : 'Required' }}
                </div>
                <p class="text-[9px] font-bold text-muted uppercase">{{ $stats['pos_synced_today'] ? 'Up to date' : 'Upload today\'s sales' }}</p>
            </a>
        </div>
    </div>

    @if($stats['low_stock_count'] > 0)
        <div class="card bg-red-500/5 border-red-500/20 p-8 mb-10">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-[11px] font-black text-red-500 uppercase tracking-widest flex items-center gap-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    Critical Stock Shortage ({{ $stats['low_stock_count'] }})
                </h2>
                <a href="{{ route('admin.inventory.index') }}" class="text-[10px] font-black text-red-500 hover:underline uppercase tracking-widest">Full Inventory</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($stats['low_stock_items'] as $item)
                    <div class="bg-primary/20 p-4 rounded-xl border border-red-500/20 flex justify-between items-center transition-all hover:bg-primary/30">
                        <div>
                            <div class="font-bold text-primary text-sm">{{ $item->name }}</div>
                            <div class="text-[9px] font-bold text-muted uppercase mt-1">Min. Required: {{ $item->alert_threshold }} {{ $item->measurement_unit }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-black text-red-500 text-xl">{{ $item->current_stock_display }}</div>
                            <div class="text-[9px] font-bold text-red-500/60 uppercase">{{ $item->measurement_unit }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-10">
        {{-- Quick Actions Panel --}}
        <div class="card p-8">
            <h2 class="text-[11px] font-black text-accent uppercase tracking-widest mb-8 flex items-center gap-3">
                <i data-lucide="zap" class="w-4 h-4"></i>
                Quick Actions
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="{{ route('admin.attendance.index') }}" class="flex items-center gap-5 p-5 rounded-xl border border-subtle hover:border-accent/40 bg-white/5 transition-all group">
                    <div class="bg-primary p-4 rounded-xl text-accent border border-subtle group-hover:bg-accent group-hover:text-primary transition-all">
                        <i data-lucide="clipboard-list" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-primary uppercase text-xs tracking-widest">Attendance</h4>
                        <p class="text-[9px] font-bold text-muted uppercase mt-1">View and manage logs</p>
                    </div>
                </a>

                <a href="{{ route('recipes.create') }}" class="flex items-center gap-5 p-5 rounded-xl border border-subtle hover:border-accent/40 bg-white/5 transition-all group">
                    <div class="bg-primary p-4 rounded-xl text-accent border border-subtle group-hover:bg-accent group-hover:text-primary transition-all">
                        <i data-lucide="plus" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-primary uppercase text-xs tracking-widest">New Recipe</h4>
                        <p class="text-[9px] font-bold text-muted uppercase mt-1">Write a new recipe</p>
                    </div>
                </a>

                <a href="{{ route('excel.import_form') }}" class="flex items-center gap-5 p-5 rounded-xl border border-subtle hover:border-accent/40 bg-white/5 transition-all group">
                    <div class="bg-primary p-4 rounded-xl text-accent border border-subtle group-hover:bg-accent group-hover:text-primary transition-all">
                        <i data-lucide="file-spreadsheet" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-primary uppercase text-xs tracking-widest">Import Data</h4>
                        <p class="text-[9px] font-bold text-muted uppercase mt-1">Upload from Excel</p>
                    </div>
                </a>

                <a href="{{ route('admin.staff.index') }}" class="flex items-center gap-5 p-5 rounded-xl border border-subtle hover:border-accent/40 bg-white/5 transition-all group">
                    <div class="bg-primary p-4 rounded-xl text-accent border border-subtle group-hover:bg-accent group-hover:text-primary transition-all">
                        <i data-lucide="user-cog" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-primary uppercase text-xs tracking-widest">Staff</h4>
                        <p class="text-[9px] font-bold text-muted uppercase mt-1">Manage your team</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- Today's Summary --}}
        <div class="card bg-primary p-8 border-subtle">
            <h2 class="text-[11px] font-black text-accent uppercase tracking-widest mb-8 flex items-center gap-3">
                <i data-lucide="calendar" class="w-4 h-4"></i>
                Today's Date
            </h2>

            <div class="text-3xl font-black text-primary tracking-tight mb-8">{{ now()->format('l, d F Y') }}</div>

            <div class="space-y-4">
                <h3 class="text-[10px] font-black text-muted uppercase tracking-widest">Pending Actions</h3>
                @if($stats['overdue_sop_count'] > 0)
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                        <p class="text-xs font-bold text-primary">{{ $stats['overdue_sop_count'] }} checklist(s) overdue</p>
                    </div>
                @endif
                @if($stats['low_stock_count'] > 0)
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-accent"></div>
                        <p class="text-xs font-bold text-primary">{{ $stats['low_stock_count'] }} low stock alert(s)</p>
                    </div>
                @endif
                @if($stats['pending_purchases_count'] > 0)
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-accent"></div>
                        <p class="text-xs font-bold text-primary">{{ $stats['pending_purchases_count'] }} purchase(s) awaiting approval</p>
                    </div>
                @endif
                @if(!$stats['pos_synced_today'])
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                        <p class="text-xs font-bold text-primary">Sales data not uploaded today</p>
                    </div>
                @endif
                @if($stats['overdue_sop_count'] == 0 && $stats['low_stock_count'] == 0 && $stats['pending_purchases_count'] == 0 && $stats['pos_synced_today'])
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-accent shadow-[0_0_12px_rgba(181,151,90,0.6)]"></div>
                        <p class="text-xs font-bold text-primary">All clear — nothing pending</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection