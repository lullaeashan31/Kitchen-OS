<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Purchase;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with(['ingredient', 'creator'])->latest('purchase_date')->paginate(15);
        return view('admin.purchases.index', compact('purchases'));
    }

    public function create()
    {
        $ingredients = Ingredient::orderBy('name')->get();
        return view('admin.purchases.create', compact('ingredients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_date' => 'required|date',
            'vendor' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.total_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->items as $item) {
                $ingredient = Ingredient::findOrFail($item['ingredient_id']);
                $quantity = $item['quantity'];
                $totalPrice = $item['total_price'];

                // Calculate Unit Price
                $unitPrice = $quantity > 0 ? ($totalPrice / $quantity) : 0;

                // 1. Create Purchase Record
                Purchase::create([
                    'ingredient_id' => $ingredient->id,
                    'quantity' => $quantity,
                    'total_price' => $totalPrice,
                    'unit_price' => $unitPrice,
                    'purchase_date' => $request->purchase_date,
                    'vendor' => $request->vendor,
                    'created_by' => Auth::id(),
                ]);

                // 2. Update Ingredient Stock & Avg Price (Weighted Average)
                $currentStock = $ingredient->current_stock;
                $currentAvgCost = $ingredient->avg_cost ?? 0;

                $newStock = $currentStock + $quantity;

                if ($newStock > 0) {
                    // Weighted Average Formula: ((OldStock * OldPrice) + (NewPrice for NewQty)) / TotalNewStock
                    // Note: total_price IS the cost of the new quantity
                    $newAvgCost = (($currentStock * $currentAvgCost) + $totalPrice) / $newStock;
                } else {
                    $newAvgCost = $unitPrice;
                }

                $ingredient->current_stock = $newStock;
                $ingredient->avg_cost = $newAvgCost;
                $ingredient->price = $unitPrice; // Update price for legacy compatibility
                $ingredient->save();

                // 3. Log Inventory Change
                InventoryLog::create([
                    'ingredient_id' => $ingredient->id,
                    'user_id' => Auth::id(),
                    'quantity_change' => $quantity,
                    'action' => 'purchase',
                    'stock_before' => $currentStock,
                    'stock_after' => $newStock,
                    'notes' => 'Vendor: ' . ($request->vendor ?? 'N/A'),
                ]);
            }
        });

        return redirect()->route('admin.purchases.index')->with('success', 'Purchases recorded successfully.');
    }
}
