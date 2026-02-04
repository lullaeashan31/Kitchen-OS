<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AuditLogController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        // Only admin
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $logs = AuditLog::with('user')->latest()->paginate(20);

        return view('audit.index', compact('logs'));
    }
}
