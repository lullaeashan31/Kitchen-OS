@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<div class="page-head">
    <div>
        <h1>{{ \Illuminate\Support\Str::before($user->name, ' ') }}</h1>
        <p class="sub">{{ now()->timezone('Asia/Kolkata')->format('l, j F Y') }}</p>
    </div>
    @can('employee.manage')
        <div class="actions">
            <a href="{{ route('employees.create') }}" class="btn">Add employee</a>
        </div>
    @endcan
</div>

@if ($needsSignatory)
    <div class="notice">
        <div>
            No one is set up to sign on the company's behalf yet, so documents signed now
            will carry the employee's signature only.
            <a href="{{ route('company-signatories.create') }}">Add a signatory</a>.
        </div>
    </div>
@endif

<div class="stat-grid">
    <div class="stat">
        <div class="n">{{ $active }}</div>
        <div class="k">Active staff</div>
    </div>
    @if ($onNotice)
        <div class="stat">
            <div class="n">{{ $onNotice }}</div>
            <div class="k">On notice</div>
        </div>
    @endif
    <div class="stat">
        <div class="n">{{ $signedThisWeek }}</div>
        <div class="k">Signed this week</div>
    </div>
    @if (! is_null($outletCount))
        <div class="stat">
            <div class="n">{{ $outletCount }}</div>
            <div class="k">{{ \Illuminate\Support\Str::plural('Outlet', $outletCount) }}</div>
        </div>
    @endif
</div>

<div class="card">
    <h2>Onboard someone</h2>
    <p class="muted">Add them to the employee list, then open their documents and sign each one
        together on the pad.</p>
    <div class="row mt">
        @can('employee.view')
            <a href="{{ route('employees.index') }}" class="btn secondary">Employees</a>
        @endcan
        @can('admin.settings.manage')
            <a href="{{ route('document-templates.index') }}" class="btn secondary">Document types</a>
            <a href="{{ route('company-signatories.index') }}" class="btn secondary">Signatories</a>
        @endcan
    </div>
</div>

@if ($exited)
    <p class="muted">{{ $exited }} former {{ \Illuminate\Support\Str::plural('employee', $exited) }}
        {{ $exited === 1 ? 'is' : 'are' }} archived and hidden from the active list.</p>
@endif

@endsection
