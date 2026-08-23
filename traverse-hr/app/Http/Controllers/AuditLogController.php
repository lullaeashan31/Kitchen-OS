<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Response;

/**
 * Read-only by design: no store/update/destroy actions exist for this
 * controller and none should ever be added — audit_logs is append-only.
 */
class AuditLogController extends Controller
{
    public function index()
    {
        return view('audit-log.index', [
            'logs' => AuditLog::with('user')->latest('created_at')->paginate(50),
        ]);
    }

    /** Full unfiltered export — answers "whose data was exposed, and when" (§9). */
    public function export()
    {
        $filename = 'audit-log-'.now()->format('Y-m-d-His').'.csv';

        return Response::streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'created_at', 'user_id', 'action', 'auditable_type', 'auditable_id', 'ip_address', 'reason', 'meta']);

            AuditLog::orderBy('id')->chunk(500, function ($chunk) use ($out) {
                foreach ($chunk as $log) {
                    fputcsv($out, [
                        $log->id, $log->created_at, $log->user_id, $log->action,
                        $log->auditable_type, $log->auditable_id, $log->ip_address,
                        $log->reason, json_encode($log->meta),
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
