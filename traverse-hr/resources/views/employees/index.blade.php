@extends('layouts.app')
@section('title', 'Employees')
@section('content')

<div class="page-head">
    <div>
        <h1>Employees</h1>
        <p class="sub">
            {{ $counts['active'] }} active
            @if ($counts['on_notice']) &middot; {{ $counts['on_notice'] }} on notice @endif
            @if ($counts['exited']) &middot; {{ $counts['exited'] }} exited @endif
        </p>
    </div>
    @can('employee.manage')
        <div class="actions">
            <a href="{{ route('employees.create') }}" class="btn">Add employee</a>
        </div>
    @endcan
</div>

<div class="card">
    <form method="GET" class="field-row">
        <div style="flex:2 1 200px;">
            <label for="q">Search</label>
            <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Name, code, phone, designation">
        </div>
        <div>
            <label for="outlet_id">Outlet</label>
            <select id="outlet_id" name="outlet_id">
                <option value="">All</option>
                @foreach ($outlets as $o)
                    <option value="{{ $o->id }}" @selected($filters['outlet_id'] == $o->id)>{{ $o->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="job_role_id">Job role</label>
            <select id="job_role_id" name="job_role_id">
                <option value="">All</option>
                @foreach ($jobRoles as $r)
                    <option value="{{ $r->id }}" @selected($filters['job_role_id'] == $r->id)>{{ $r->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field-narrow">
            <label for="status">Status</label>
            <select id="status" name="status">
                @foreach (['active'=>'Active','on_notice'=>'On notice','exited'=>'Exited','all'=>'All'] as $v=>$l)
                    <option value="{{ $v }}" @selected($filters['status'] === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="field-narrow">
            <label for="sort">Sort by</label>
            <select id="sort" name="sort">
                @foreach (['name'=>'Name','employee_code'=>'Code','date_of_joining'=>'Newest joiner'] as $v=>$l)
                    <option value="{{ $v }}" @selected($filters['sort'] === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="row" style="flex:0 0 auto;">
            <button type="submit" class="btn secondary">Filter</button>
            <a href="{{ route('employees.index') }}" class="btn plain">Reset</a>
        </div>
    </form>
</div>

<div class="card card-flush">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Role / outlet</th>
                    <th>Joined</th>
                    <th>Paperwork</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($employees as $employee)
                @php $done = $employee->signed_documents_count; @endphp
                <tr>
                    <td class="td-primary" data-label="Employee">
                        <span class="person">
                            @if ($employee->photo_path)
                                <img class="avatar" src="{{ route('employees.photo', $employee) }}" alt="">
                            @else
                                <span class="avatar" style="display:inline-flex;align-items:center;justify-content:center;
                                      font-size:.8rem;font-weight:600;color:var(--ink-soft);">
                                    {{ strtoupper(mb_substr($employee->name, 0, 1)) }}
                                </span>
                            @endif
                            <span>
                                <span class="name">{{ $employee->name }}</span>
                                <div class="muted">{{ $employee->employee_code }}@if ($employee->designation) &middot; {{ $employee->designation }}@endif</div>
                            </span>
                        </span>
                    </td>
                    <td data-label="Role / outlet">
                        {{ $employee->jobRole?->name ?? '—' }}
                        <div class="muted">{{ $employee->outlet?->name ?? '—' }}</div>
                    </td>
                    <td data-label="Joined">{{ optional($employee->date_of_joining)->format('d M Y') ?? '—' }}</td>
                    <td data-label="Paperwork">
                        @if ($expectedDocuments && $done >= $expectedDocuments)
                            <span class="pill ok">Complete</span>
                        @elseif ($done === 0)
                            <span class="pill bad">None signed</span>
                        @else
                            <span class="pill warn">{{ $done }} of {{ $expectedDocuments }}</span>
                        @endif
                    </td>
                    <td data-label="Status">
                        <span @class(['pill', 'ok' => $employee->status === 'active', 'warn' => $employee->status === 'on_notice'])>
                            {{ ucfirst(str_replace('_', ' ', $employee->status)) }}
                        </span>
                    </td>
                    <td data-label="Actions">
                        <span class="row-actions">
                            <a href="{{ route('employees.edit', $employee) }}">Edit</a>
                            @can('document.manage')
                                <a href="{{ route('employees.documents.index', $employee) }}">Documents</a>
                                <a href="{{ route('employees.locker.show', $employee) }}">Locker</a>
                            @endcan
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="empty">
                            <h3>No one here yet</h3>
                            <p>No employees match these filters.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $employees->links() }}
@endsection
