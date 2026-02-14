<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Daily SOP Report - {{ $date }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .run-card {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }

        .run-title {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #eee;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }

        th {
            background: #fcfcfc;
            font-weight: bold;
        }

        .status-badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .status-completed {
            background: #dcfce7;
            color: #166534;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-resubmitted {
            background: #fef9c3;
            color: #854d0e;
        }

        .photo-thumbnail {
            width: 100px;
            height: 100px;
            object-cover;
            border-radius: 4px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Daily SOP Compliance Report</h1>
        <p>Date: {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</p>
    </div>

    @foreach($runs as $run)
        <div class="run-card">
            <div class="run-title">
                {{ $run->checklist->name }}
                <span style="float: right; font-size: 12px; margin-top: 5px;">
                    Staff: {{ $run->user->name }} | Completed:
                    {{ $run->completed_at ? $run->completed_at->format('H:i') : 'N/A' }}
                </span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 40%;">Task Item</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 15%;">Time</th>
                        <th style="width: 30%;">Photo Proof</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($run->completions as $completion)
                        <tr>
                            <td>
                                <strong>{{ $completion->item->name }}</strong>
                                @if($completion->rejection_reason)
                                    <div style="color: #dc2626; font-size: 10px; margin-top: 4px;">
                                        Reason: {{ $completion->rejection_reason }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge status-{{ $completion->status }}">
                                    {{ $completion->status }}
                                </span>
                            </td>
                            <td>{{ $completion->completed_at ? $completion->completed_at->format('H:i') : '-' }}</td>
                            <td>
                                @if($completion->photo_path)
                                    {{-- Use storage_path for PDF generation if local, or a temporary URL if S3 --}}
                                    @php
                                        $url = Storage::disk(config('filesystems.default'))->url($completion->photo_path);
                                    @endphp
                                    <img src="{{ $url }}" class="photo-thumbnail">
                                @else
                                    <span style="color: #ccc;">No photo</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="footer">
        Generated on {{ now()->format('Y-m-d H:i:s') }} | Kitchen Management System
    </div>
</body>

</html>