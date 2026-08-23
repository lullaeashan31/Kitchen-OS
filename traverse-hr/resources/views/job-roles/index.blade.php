@extends('layouts.app')
@section('title', 'Job roles')
@section('content')
<div style="display:flex; justify-content:space-between; align-items:center;">
    <h1 style="font-size:1.3rem;">Job roles</h1>
    <a href="{{ route('job-roles.create') }}" class="btn">Add job role</a>
</div>
<div class="card">
    <table>
        <thead><tr><th>Name</th><th>Outlet</th><th>Department</th><th>Probation</th><th>Active</th><th></th></tr></thead>
        <tbody>
        @foreach ($jobRoles as $role)
            <tr>
                <td data-label="Name">{{ $role->name }}</td>
                <td data-label="Outlet">{{ $role->outlet?->name ?? 'All outlets' }}</td>
                <td data-label="Department">{{ $role->department?->name ?? '—' }}</td>
                <td data-label="Probation">{{ $role->probation_months }} mo</td>
                <td data-label="Active">{{ $role->active ? 'Yes' : 'No' }}</td>
                <td data-label="Edit">
                    <a href="{{ route('job-roles.edit', $role) }}">Edit</a>
                    @if ($role->active)
                        <form method="POST" action="{{ route('job-roles.destroy', $role) }}" style="display:inline" onsubmit="return confirm('Deactivate this role?');">
                            @csrf @method('DELETE')
                            <button type="submit" style="background:none;border:none;color:#a12622;cursor:pointer;padding:0;">Deactivate</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $jobRoles->links() }}
@endsection
