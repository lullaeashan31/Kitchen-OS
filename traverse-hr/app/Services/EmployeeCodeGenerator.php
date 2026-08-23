<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Outlet;
use Illuminate\Support\Facades\DB;

/**
 * employee_code = <outlet code prefix> + zero-padded sequence, e.g. ALN-0007.
 * Sequence is per-outlet. Uses a locking query so two concurrent joiners at
 * the same outlet never collide.
 */
class EmployeeCodeGenerator
{
    public static function next(Outlet $outlet): string
    {
        return DB::transaction(function () use ($outlet) {
            $count = Employee::withoutGlobalScopes()
                ->where('outlet_id', $outlet->id)
                ->lockForUpdate()
                ->count();

            $sequence = str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);

            return "{$outlet->code}-{$sequence}";
        });
    }
}
