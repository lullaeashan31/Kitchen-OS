@extends('layouts.app')
@section('title', 'Employees')
@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap;">
    <h1 style="font-size:1.3rem;">Employees</h1>
    @can('employee.manage')<a href="{{ route('employees.create') }}" class="btn">Add employee</a>@endcan
</div>

<p class="muted">
    {{ $counts['active'] }} active
    @if ($counts['on_notice']) &middot; {{ $counts['on_notice'] }} on notice @endif
    @if ($counts['exited']) &middot; {{ $counts['exited'] }} exited @endif
</p>

<div class="card">
    <form method="GET" style="display:flex; gap:.6rem; flex-wrap:wrap; align-items:flex-end;">
        <div style="flex:2; min-width:180px;">
            <label for="q">Search</label>
            <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Name, code, phone, designation">
        </div>
        <div style="flex:1; min-width:130px;">
            <label for="outlet_id">Outlet</label>
            <select id="outlet_id" name="outlet_id">
                <option value="">All</option>
                @foreach ($outlets as $o)
                    <option value="{{ $o->id }}" @selected($filters['outlet_id'] == $o->id)>{{ $o->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1; min-width:130px;">
            <label for="job_role_id">Job role</label>
            <select id="job_role_id" name="job_role_id">
                <option value="">All</option>
                @foreach ($jobRoles as $r)
                    <option value="{{ $r->id }}" @selected($filters['job_role_id'] == $r->id)>{{ $r->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1; min-width:120px;">
            <label for="status">Status</label>
            <select id="status" name="status">
                @foreach (['active'=>'Active','on_notice'=>'On notice','exited'=>'Exited','all'=>'All'] as $v=>$l)
                    <option value="{{ $v }}" @selected($filters['status'] === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1; min-width:120px;">
            <label for="sort">Sort by</label>
            <select id="sort" name="sort">
                @foreach (['name'=>'Name','employee_code'=>'Code','date_of_joining'=>'Newest joiner'] as $v=>$l)
                    <option value="{{ $v }}" @selected($filters['sort'] === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn" style="height:2.4rem;">Filter</button>
        <a href="{{ route('employees.index') }}" class="btn secondary" style="height:2.4rem; line-height:1.4rem;">Reset</a>
    </form>
</div>

<div class="card">
    <table>
        <thead><tr><th></th><th>Code</th><th>Name</th><th>Role / outlet</th><th>Joined</th><th>Paperwork</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($employees as $employee)
            @php $done = $employee->signed_documents_count; @endphp
            <tr>
                <td data-label="Photo">
                    @if ($employee->photo_path)
                        <img src="{{ route('employees.photo', $employee) }}" alt=""
                             style="width:34px;height:34px;border-radius:50%;object-fit:cover;display:block;">
                    @else
                        <span style="width:34px;height:34px;border-radius:50%;background:#e9edf3;color:#7b8497;
                                     display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:600;">
                            {{ strtoupper(mb_substr($employee->name, 0, 1)) }}
                        </span>
                    @endif
                </td>
                <td data-label="Code">{{ $employee->employee_code }}</td>
                <td data-label="Name">
                    {{ $employee->name }}
                    @if ($employee->designation)<div class="muted">{{ $employee->designation }}</div>@endif
                </td>
                <td data-label="Role / outlet">
                    {{ $employee->jobRole?->name ?? '—' }}
                    <div class="muted">{{ $employee->outlet?->name ?? '—' }}</div>
                </td>
                <td data-label="Joined">{{ optional($employee->date_of_joining)->format('d M Y') ?? '—' }}</td>
                <td data-label="Paperwork">
                    @if ($expectedDocuments && $done >= $expectedDocuments)
                        <span style="color:#146c43;">Complete</span>
                    @elseif ($done === 0)
                        <span style="color:#a12622;">None signed</span>
                    @else
                        <span style="color:#8a6d3b;">{{ $done }} of {{ $expectedDocuments }}</span>
                    @endif
                </td>
                <td data-label="Status">{{ str_replace('_', ' ', $employee->status) }}</td>
                <td data-label="Actions">
                    <a href="{{ route('employees.edit', $employee) }}">Edit</a>
                    @can('document.manage') &middot;
                        <a href="{{ route('employees.documents.index', $employee) }}">Documents</a> &middot;
                        <a href="{{ route('employees.locker.show', $employee) }}">Locker</a>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="muted">No employees match these filters.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $employees->links() }}
@endsection
