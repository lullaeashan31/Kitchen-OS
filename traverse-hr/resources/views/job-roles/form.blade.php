@extends('layouts.app')
@section('title', $jobRole->exists ? 'Edit job role' : 'Add job role')
@section('content')
<h1>{{ $jobRole->exists ? 'Edit job role' : 'Add job role' }}</h1>
<div class="card" style="max-width:520px;">
    <form method="POST" action="{{ $jobRole->exists ? route('job-roles.update', $jobRole) : route('job-roles.store') }}">
        @csrf
        @if ($jobRole->exists) @method('PUT') @endif
        <label for="name">Name</label>
        <input id="name" name="name" value="{{ old('name', $jobRole->name) }}" required>
        <label for="outlet_id">Outlet (blank = all outlets)</label>
        <select id="outlet_id" name="outlet_id">
            <option value="">All outlets</option>
            @foreach ($outlets as $outlet)
                <option value="{{ $outlet->id }}" @selected(old('outlet_id', $jobRole->outlet_id) == $outlet->id)>{{ $outlet->name }}</option>
            @endforeach
        </select>
        <label for="department_id">Department</label>
        <select id="department_id" name="department_id">
            <option value="">—</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $jobRole->department_id) == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        <label for="default_designation">Default designation</label>
        <input id="default_designation" name="default_designation" value="{{ old('default_designation', $jobRole->default_designation) }}">
        <label for="default_salary_band_min">Salary band min</label>
        <input id="default_salary_band_min" name="default_salary_band_min" type="number" value="{{ old('default_salary_band_min', $jobRole->default_salary_band_min) }}">
        <label for="default_salary_band_max">Salary band max</label>
        <input id="default_salary_band_max" name="default_salary_band_max" type="number" value="{{ old('default_salary_band_max', $jobRole->default_salary_band_max) }}">
        <label for="probation_months">Probation (months)</label>
        <input id="probation_months" name="probation_months" type="number" value="{{ old('probation_months', $jobRole->probation_months ?? 3) }}" required>
        <label for="notice_period_days">Notice period (days)</label>
        <input id="notice_period_days" name="notice_period_days" type="number" value="{{ old('notice_period_days', $jobRole->notice_period_days ?? 30) }}" required>
        <label for="sort_order">Sort order</label>
        <input id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $jobRole->sort_order ?? 0) }}">
        <label style="display:flex; align-items:center; gap:.4rem; flex-direction:row;">
            <input type="checkbox" name="active" value="1" style="width:auto;" @checked(old('active', $jobRole->active ?? true))> <span>Active</span>
        </label>
        <button type="submit" class="btn" style="margin-top:1rem;">Save</button>
    </form>
</div>
@endsection
