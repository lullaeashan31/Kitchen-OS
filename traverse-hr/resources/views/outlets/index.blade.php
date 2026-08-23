@extends('layouts.app')
@section('title', 'Outlets')
@section('content')
<div style="display:flex; justify-content:space-between; align-items:center;">
    <h1 style="font-size:1.3rem;">Outlets</h1>
    <a href="{{ route('outlets.create') }}" class="btn">Add outlet</a>
</div>
<div class="card">
    <table>
        <thead><tr><th>Name</th><th>Code</th><th>Divisor</th><th>Active</th><th></th></tr></thead>
        <tbody>
        @foreach ($outlets as $outlet)
            <tr>
                <td data-label="Name">{{ $outlet->name }}</td>
                <td data-label="Code">{{ $outlet->code }}</td>
                <td data-label="Divisor">{{ $outlet->payroll_divisor_setting }}</td>
                <td data-label="Active">{{ $outlet->active ? 'Yes' : 'No' }}</td>
                <td data-label="Edit"><a href="{{ route('outlets.edit', $outlet) }}">Edit</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $outlets->links() }}
@endsection
