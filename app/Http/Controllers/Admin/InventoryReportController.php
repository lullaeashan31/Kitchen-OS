<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\PurchaseBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryReportController extends Controller
{
    /**
     * Show the FIFO depletion report.
     * This helps in identifying which batches are nearly empty and need reordering.
     */
    public function depletion(string $kitchen_slug)
    {
        // Get ingredients with their remaining batches
        $ingredients = Ingredient::with(['category', 'batches' => function($q) {
            $q->where('quantity_remaining', '>', 0)->orderBy('created_at', 'asc');
        }])
        ->whereHas('batches', function($q) {
            $q->where('quantity_remaining', '>', 0);
        })
        ->get();

        $depletionData = $ingredients->map(function($ingredient) {
            $totalInitial = $ingredient->batches->sum('quantity_initial');
            $totalRemaining = $ingredient->batches->sum('quantity_remaining');
            $depletionRate = $totalInitial > 0 ? (1 - ($totalRemaining / $totalInitial)) * 100 : 0;
            
            // Oldest batch info
            $oldestBatch = $ingredient->batches->first();
            
            return [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'category' => $ingredient->category?->name ?? 'Uncategorized',
                'total_remaining' => $totalRemaining,
                'unit' => $ingredient->measurement_unit,
                'depletion_percentage' => round($depletionRate, 2),
                'batch_count' => $ingredient->batches->count(),
                'oldest_batch_date' => $oldestBatch?->created_at->format('Y-m-d'),
                'oldest_batch_remaining' => $oldestBatch?->quantity_remaining,
            ];
        })->sortByDesc('depletion_percentage');

        return view('admin.reports.depletion', compact('depletionData'));
    }
}
