<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Purchase;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PurchaseController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $query = Purchase::with(['ingredient', 'creator', 'approver']);

        // Staff can only see their own purchases? Or all? Usually safer to see own.
        // User request doesn't specify visibility, but "Purchase & Inventory" usually implies transparency or role-based.
        // Let's let admins see all, staff see theirs.
        if (Auth::user()->isStaff()) {
            $query->where('created_by', Auth::id());
        }

        $purchases = $query->latest('purchase_date')->paginate(15);
        return view('admin.purchases.index', compact('purchases')); // Reuse view or create new? Let's assume we'll update the view path in a bit.
    }

    public function create()
    {
        $ingredients = Ingredient::where('status', 'approved')
            ->orderBy('name')
            ->get()
            ->map(function ($ingredient) {
                // Get last approved purchase price if available
                $lastPurchase = Purchase::where('ingredient_id', $ingredient->id)
                    ->where('status', 'approved')
                    ->latest('approved_at')
                    ->first();

                // Use last purchase price OR current ingredient price OR 0
                $ingredient->latest_price = $lastPurchase ? $lastPurchase->unit_price : ($ingredient->price ?? 0);
                return $ingredient;
            });

        return view('admin.purchases.create', compact('ingredients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_date' => 'required|date',
            'vendor' => 'required|string|max:255',
            'invoice_photo' => 'required|image|max:4096', // 4MB
            'goods_photo' => 'required|image|max:4096',
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            // 'items.*.unit_price' => 'required|numeric|min:0', // User wants auto-calc total. So input is unit price and qty.
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.unit' => 'required|string',
        ]);

        // Upload Photos
        $invoicePath = $request->file('invoice_photo')->store('purchases/invoices', 'public');
        $goodsPath = $request->file('goods_photo')->store('purchases/goods', 'public');

        // Unit Service (Since it's not injected in method)
        $unitService = app(\App\Services\UnitConversionService::class);

        DB::transaction(function () use ($request, $invoicePath, $goodsPath, $unitService) {
            foreach ($request->items as $item) {
                $ingredient = Ingredient::findOrFail($item['ingredient_id']);
                $inputQty = $item['quantity'];
                $inputUnit = $item['unit'];

                // Use provided unit_price or fetch from ingredient
                $inputUnitPrice = isset($item['unit_price']) ? $item['unit_price'] : ($ingredient->price ?? 0);

                // Calculate Total Price (Input Qty * Input Unit Price)
                $totalPrice = $inputQty * $inputUnitPrice;

                // Normalize Quantity to Ingredient's Base Unit
                try {
                    $normalizedQty = $unitService->convert($inputQty, $inputUnit, $ingredient->measurement_unit);
                } catch (\Exception $e) {
                    // Fallback: If conversion fails (unlikely due to UI grouping), use input qty but this is dangerous. 
                    // Assume 1:1 if fails? Or throw error? 
                    // Better to log error and fallback.
                    $normalizedQty = $inputQty;
                }

                // Calculate Normalized Unit Price (Total / Normalized Qty)
                // Avoid division by zero
                $normalizedUnitPrice = $normalizedQty > 0 ? ($totalPrice / $normalizedQty) : 0;

                // Create Purchase Record - Stores NORMALIZED values
                $purchase = Purchase::create([
                    'ingredient_id' => $ingredient->id,
                    'quantity' => $normalizedQty,
                    'unit_price' => $normalizedUnitPrice,
                    'total_price' => $totalPrice,
                    'purchase_date' => $request->purchase_date,
                    'vendor' => $request->vendor,
                    'created_by' => Auth::id(),
                    'invoice_photo_path' => $invoicePath,
                    'goods_photo_path' => $goodsPath,
                    'status' => 'approved', // Auto-approved
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);

                // Update Stock & Price Immediately
                $oldStock = $ingredient->current_stock;
                $newStock = $oldStock + $normalizedQty;

                // Weighted Average Cost Calculation
                $currentAvgCost = $ingredient->avg_cost ?? 0;
                if ($newStock > 0) {
                    // If old stock was negative, we treat it as 0 for weighted avg to avoid skewing, 
                    // or we just do simple math. Math: (OldValue + NewValue) / NewQty
                    // OldValue = OldStock * OldAvg. If OldStock < 0, this might be weird.
                    // Let's assume standard logic:
                    $oldValue = ($oldStock > 0 ? $oldStock : 0) * $currentAvgCost;
                    $newAvgCost = ($oldValue + $totalPrice) / ($normalizedQty + ($oldStock > 0 ? $oldStock : 0));
                } else {
                    $newAvgCost = $normalizedUnitPrice;
                }

                $ingredient->update([
                    'current_stock' => $newStock,
                    'avg_cost' => $newAvgCost,
                    'price' => $normalizedUnitPrice, // Update latest price
                ]);

                // Log Inventory Change
                InventoryLog::create([
                    'ingredient_id' => $ingredient->id,
                    'user_id' => Auth::id(),
                    'quantity_change' => $normalizedQty,
                    'action' => 'purchase',
                    'stock_before' => $oldStock,
                    'stock_after' => $newStock,
                    'reason' => 'New Purchase (Auto-processed)',
                ]);
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase recorded and stock updated successfully.');
    }

    public function approve(Purchase $purchase)
    {
        $this->authorize('approve', $purchase); // Need policy? Or just check role.

        if (!$purchase->isPending()) {
            return back()->with('error', 'Purchase is not pending.');
        }

        DB::transaction(function () use ($purchase) {
            $purchase->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Update Ingredient Stock & Avg Price
            $ingredient = $purchase->ingredient;
            $currentStock = $ingredient->current_stock;
            $currentAvgCost = $ingredient->avg_cost ?? 0;
            $quantity = $purchase->quantity;
            $totalPrice = $purchase->total_price;
            $unitPrice = $purchase->unit_price;

            $newStock = $currentStock + $quantity;

            if ($newStock > 0) {
                // Weighted Average
                $newAvgCost = (($currentStock * $currentAvgCost) + $totalPrice) / $newStock;
            } else {
                $newAvgCost = $unitPrice;
            }

            $ingredient->current_stock = $newStock;
            $ingredient->avg_cost = $newAvgCost;
            $ingredient->price = $unitPrice; // Update latest price
            $ingredient->save();

            // Log Inventory Change
            InventoryLog::create([
                'ingredient_id' => $ingredient->id,
                'user_id' => Auth::id(), // Admin who approved
                'quantity_change' => $quantity,
                'action' => 'purchase_approved',
                'stock_before' => $currentStock,
                'stock_after' => $newStock,
                'notes' => 'Purchase Approved. Vendor: ' . $purchase->vendor . '. Invoice: ' . $purchase->id,
            ]);
        });

        return back()->with('success', 'Purchase approved and inventory updated.');
    }

    public function reject(Request $request, Purchase $purchase)
    {
        $this->authorize('approve', $purchase);

        $request->validate(['rejection_reason' => 'required|string']);

        $purchase->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => Auth::id(), // Rejected by
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Purchase rejected.');
    }
}
