@extends('layouts.app')
@section('title', 'Employees')
@section('content')
<div style="display:flex; justify-content:space-between; align-items:center;">
    <h1 style="font-size:1.3rem;">Employees</h1>
    @can('employee.manage')<a href="{{ route('employees.create') }}" class="btn">Add employee</a>@endcan
</div>
<div class="card">
    <table>
        <thead><tr><th>Code</th><th>Name</th><th>Outlet</th><th>Job role</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach ($employees as $employee)
            <tr>
                <td data-label="Code">{{ $employee->employee_code }}</td>
                <td data-label="Name">{{ $employee->name }}</td>
                <td data-label="Outlet">{{ $employee->outlet->name }}</td>
                <td data-label="Job role">{{ $employee->jobRole->name }}</td>
                <td data-label="Status">{{ $employee->status }}</td>
                <td data-label="Edit"><a href="{{ route('employees.edit', $employee) }}">Edit</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $employees->links() }}
@endsection
