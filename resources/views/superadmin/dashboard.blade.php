@extends('layouts.app')

@section('header')
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-black tracking-tight text-primary">Super Admin Console</h1>
        <p class="text-[10px] font-bold text-muted mt-1 uppercase tracking-widest">Kitchen OS <span class="text-accent">Central Governance</span></p>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        {{-- Manage Kitchens Card --}}
        <div class="card p-0 overflow-hidden group">
            <div class="p-8">
                <div class="w-16 h-16 bg-accent text-primary rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <i data-lucide="layout" class="w-8 h-8"></i>
                </div>
                <h3 class="text-xl font-black text-primary mb-2 uppercase tracking-tight">Enterprise Infrastructure</h3>
                <p class="text-[10px] font-bold text-muted uppercase tracking-widest mb-6 leading-relaxed">
                    Orchestrate, initialize, and monitor multi-unit kitchen nodes across the enterprise network.
                </p>
                <a href="{{ route('superadmin.kitchens.index') }}"
                    class="btn btn-secondary w-full py-4 text-[10px] uppercase tracking-[0.2em]">
                    Access Kitchen Cluster
                </a>
            </div>
        </div>

        {{-- System Logs Card --}}
        <div class="card p-0 overflow-hidden group">
            <div class="p-8">
                <div class="w-16 h-16 bg-white/5 text-accent border border-subtle rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <i data-lucide="activity" class="w-8 h-8"></i>
                </div>
                <h3 class="text-xl font-black text-primary mb-2 uppercase tracking-tight">Central Intelligence</h3>
                <p class="text-[10px] font-bold text-muted uppercase tracking-widest mb-6 leading-relaxed">
                    Real-time telemetry and audit logs encompassing all active kitchen branch operations.
                </p>
                <a href="#" class="btn btn-secondary w-full py-4 text-[10px] uppercase tracking-[0.2em] opacity-50 cursor-not-allowed">
                    Telemetry Offline
                </a>
            </div>
        </div>
    </div>
@endsection