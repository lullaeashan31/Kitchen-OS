<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Outlet;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('dashboard', [
            'outletCount' => Outlet::query()->count(),
            'employeeCount' => Employee::query()->count(),
        ]);
    }
}
