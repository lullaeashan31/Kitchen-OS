<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\ProductionDay;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        } elseif ($user->isManager()) {
            return $this->managerDashboard();
        } else {
            return $this->staffDashboard();
        }
    }

    private function adminDashboard()
    {
        $stats = $this->getDashboardStats();
        $stats['total_staff'] = \App\Models\User::where('role', \App\Enums\UserRole::Staff)->count();
        $stats['today_clock_in'] = \App\Models\Attendance::whereDate('clock_in_time', today()->toDateString())->count();
        $stats['today_attendance'] = $stats['today_clock_in']; // Keep both for safety
        $stats['today_clock_out'] = \App\Models\Attendance::whereDate('clock_in_time', today()->toDateString())->whereNotNull('clock_out_time')->count();

        return view('dashboard.admin', compact('stats'));
    }

    private function managerDashboard()
    {
        $stats = $this->getDashboardStats();
        $stats['today_attendance'] = \App\Models\Attendance::whereDate('clock_in_time', today()->toDateString())->count();
        $stats['today_clock_in'] = $stats['today_attendance']; // Keep both for safety

        return view('dashboard.manager', compact('stats'));
    }

    private function getDashboardStats()
    {
        // Low stock alerts should include both:
        // 1. Low stock: current_stock <= alert_threshold AND current_stock > 0 AND alert_threshold > 0
        // 2. Out of stock: current_stock <= 0
        $lowStockItems = \App\Models\Ingredient::where(function ($q) {
            $q->where(function ($subQ) {
                // Low stock items
                $subQ->whereColumn('current_stock', '<=', 'alert_threshold')
                    ->where('current_stock', '>', 0)
                    ->where('alert_threshold', '>', 0);
            })->orWhere(function ($subQ) {
                // Out of stock items
                $subQ->where('current_stock', '<=', 0);
            });
        })
            ->orderBy('current_stock')
            ->limit(5)
            ->get();

        // SOP Metrics
        $totalChecklists = \App\Models\SopChecklist::active()->count();
        $completedChecklists = \App\Models\SopDailyRun::where('date', today()->toDateString())
            ->where('status', 'approved')
            ->count();

        $overdueSopCount = \App\Models\SopChecklist::active()
            ->where('deadline_time', '<', now()->toTimeString())
            ->whereDoesntHave('runs', function ($query) {
                $query->where('date', today()->toDateString())->where('status', 'approved');
            })
            ->count();

        // POS Sync Status
        $posSyncedToday = \App\Models\InventoryLog::whereDate('created_at', today()->toDateString())
            ->where('action', 'POS_SALE')
            ->exists();

        return [
            'active_staff' => \App\Models\Attendance::whereDate('clock_in_time', today()->toDateString())->whereNull('clock_out_time')->count(),
            'total_recipes' => Recipe::count(),
            'pending_ingredients' => \App\Models\Ingredient::where('status', 'pending')->count() ?? 0,
            'low_stock_count' => \App\Models\Ingredient::where(function ($q) {
                $q->where(function ($subQ) {
                    // Low stock items
                    $subQ->whereColumn('current_stock', '<=', 'alert_threshold')
                        ->where('current_stock', '>', 0)
                        ->where('alert_threshold', '>', 0);
                })->orWhere(function ($subQ) {
                    // Out of stock items
                    $subQ->where('current_stock', '<=', 0);
                });
            })->count(),
            'low_stock_items' => $lowStockItems,
            'total_checklists' => $totalChecklists,
            'completed_checklists' => $completedChecklists,
            'overdue_sop_count' => $overdueSopCount,
            'pending_purchases_count' => \App\Models\Purchase::where('status', 'pending')->count(),
            'pos_synced_today' => $posSyncedToday,
            'pending_onboarding_count' => \App\Models\User::where('role', 'staff')->where('onboarding_status', 'pending')->count(),
            'pending_leaves_count' => \App\Models\LeaveRequest::where('status', 'pending')->count(),
        ];
    }

    private function staffDashboard()
    {
        $user = auth()->user();

        // Today's Shift
        $todayShift = \App\Models\ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->where('date', today()->toDateString())
            ->first();

        // Pending Leaves
        $pendingLeavesCount = \App\Models\LeaveRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        // Monthly Attendance
        $monthDaysWorked = \App\Models\Attendance::where('user_id', $user->id)
            ->whereMonth('clock_in_time', date('n'))
            ->whereYear('clock_in_time', date('Y'))
            ->count();

        $myRecipesCount = Recipe::where('created_by', $user->id)->count();

        return view('dashboard.staff', compact(
            'todayShift',
            'pendingLeavesCount',
            'monthDaysWorked',
            'myRecipesCount'
        ));
    }
}
