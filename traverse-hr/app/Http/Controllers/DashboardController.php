<?php

namespace App\Http\Controllers;

use App\Models\CompanySignatory;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Outlet;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // Employee counts respect the outlet scope, so an Outlet Manager
        // sees their own outlet's numbers rather than the whole group's.
        $employees = Employee::query()->get(['id', 'status']);

        $signedThisWeek = EmployeeDocument::whereNull('superseded_at')
            ->whereNotNull('signed_at')
            ->where('signed_at', '>=', now()->subDays(7))
            ->count();

        return view('dashboard', [
            'user' => $user,
            'outletCount' => $user->can('admin.settings.manage') ? Outlet::query()->count() : null,
            'active' => $employees->where('status', 'active')->count(),
            'onNotice' => $employees->where('status', 'on_notice')->count(),
            'exited' => $employees->where('status', 'exited')->count(),
            'signedThisWeek' => $signedThisWeek,
            // Surfaced as a prompt rather than an error: documents can be
            // signed without a countersignatory, but they shouldn't be.
            'needsSignatory' => $user->can('admin.settings.manage')
                && CompanySignatory::active()->exists() === false,
        ]);
    }
}
