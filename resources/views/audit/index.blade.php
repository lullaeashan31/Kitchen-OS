@extends('layouts.app')

@section('header')
    <h1>Audit Logs</h1>
@endsection

@section('content')
    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Model</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('M d H:i') }}</td>
                            <td>{{ $log->user->name ?? 'System' }}</td>
                            <td><span class="badge badge-gray" style="text-transform: uppercase;">{{ $log->action }}</span></td>
                            <td>{{ class_basename($log->model) }} #{{ $log->model_id }}</td>
                            <td style="font-family: monospace; font-size: 0.75rem;">
                                @if($log->action === 'updated')
                                    <div style="max-height: 100px; overflow-y: auto;">
                                        @foreach($log->new_data as $key => $val)
                                            @if(isset($log->old_data[$key]) && $log->old_data[$key] !== $val)
                                                <div><strong>{{ $key }}:</strong>
                                                    {{ is_array($log->old_data[$key]) ? json_encode($log->old_data[$key]) : $log->old_data[$key] }}
                                                    &rarr; {{ is_array($val) ? json_encode($val) : $val }}</div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    View Details (JSON)
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1rem;">
            {{ $logs->links() }}
        </div>
    </div>
@endsection