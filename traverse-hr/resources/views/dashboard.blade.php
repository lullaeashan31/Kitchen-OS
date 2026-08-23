@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<h1 style="font-size:1.3rem;">Dashboard</h1>
<div class="card">
    <p>Outlets: <strong>{{ $outletCount }}</strong></p>
    <p>Employees: <strong>{{ $employeeCount }}</strong></p>
</div>
<p class="muted">This is the M1 foundation build (auth, permissions, outlets, job roles, employee master, audit log). Recruitment, documents, offers and payroll land in later milestones — see the repo's DECISIONS.md and the milestone plan.</p>
@endsection
