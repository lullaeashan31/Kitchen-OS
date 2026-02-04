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
        $lowStockItems = \App\Models\Ingredient::whereColumn('current_stock', '<=', 'alert_threshold')
            ->orderBy('current_stock')
            ->limit(5)
            ->get();

        $stats = [
            'total_staff' => \App\Models\User::where('role', \App\Enums\UserRole::Staff)->count(),
            'today_clock_in' => \App\Models\Attendance::whereDate('clock_in_time', today())->count(),
            'today_clock_out' => \App\Models\Attendance::whereDate('clock_in_time', today())->whereNotNull('clock_out_time')->count(),
            'active_staff' => \App\Models\Attendance::whereDate('clock_in_time', today())->whereNull('clock_out_time')->count(),
            'total_recipes' => Recipe::count(),
            'pending_ingredients' => \App\Models\Ingredient::where('status', 'pending')->count() ?? 0,
            'low_stock_count' => \App\Models\Ingredient::whereColumn('current_stock', '<=', 'alert_threshold')->count(),
            'low_stock_items' => $lowStockItems,
        ];

        return view('dashboard.admin', compact('stats'));
    }

    private function managerDashboard()
    {
        $lowStockItems = \App\Models\Ingredient::whereColumn('current_stock', '<=', 'alert_threshold')
            ->orderBy('current_stock')
            ->limit(5)
            ->get();

        $stats = [
            'today_attendance' => \App\Models\Attendance::whereDate('clock_in_time', today())->count(),
            'active_staff' => \App\Models\Attendance::whereDate('clock_in_time', today())->whereNull('clock_out_time')->count(),
            'total_recipes' => Recipe::count(),
            'pending_ingredients' => \App\Models\Ingredient::where('status', 'pending')->count() ?? 0,
            'low_stock_count' => \App\Models\Ingredient::whereColumn('current_stock', '<=', 'alert_threshold')->count(),
            'low_stock_items' => $lowStockItems,
        ];

        return view('dashboard.manager', compact('stats'));
    }

    private function staffDashboard()
    {
        $myRecipesCount = Recipe::where('created_by', auth()->id())->count();
        return view('dashboard.staff', compact('myRecipesCount'));
    }
}
