@extends('layouts.app')
@section('title', 'Audit log')
@section('content')
<div style="display:flex; justify-content:space-between; align-items:center;">
    <h1 style="font-size:1.3rem;">Audit log</h1>
    <a href="{{ route('audit-log.export') }}" class="btn">Export CSV</a>
</div>
<p class="muted">Append-only. Nothing here can be edited or deleted through the UI.</p>
<div class="card">
    <table>
        <thead><tr><th>When</th><th>User</th><th>Action</th><th>Record</th><th>Reason</th><th>IP</th></tr></thead>
        <tbody>
        @foreach ($logs as $log)
            <tr>
                <td data-label="When">{{ $log->created_at }}</td>
                <td data-label="User">{{ $log->user?->name ?? 'system / token' }}</td>
                <td data-label="Action">{{ $log->action }}</td>
                <td data-label="Record">{{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : '—' }}</td>
                <td data-label="Reason">{{ $log->reason ?? '—' }}</td>
                <td data-label="IP">{{ $log->ip_address }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $logs->links() }}
@endsection
