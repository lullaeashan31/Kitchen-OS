<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Purchase;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ZipArchive;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PurchaseController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request, string $kitchen_slug)
    {
        $view = $request->query('view', 'bill'); // Default to bill-wise

        $query = Purchase::with(['ingredient', 'creator', 'approver', 'vendor']);

        // Search Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('ingredient', function($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('vendor', function($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('creator', function($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('purchase_date', $request->date);
        }

        if (Auth::user()->isStaff()) {
            $query->where('created_by', Auth::id());
        }

        if ($view === 'bill') {
            // Group by common attributes of a single upload session
            // We use vendor_id, purchase_date, created_by, and invoice_photo_path as the grouping key
            $purchases = $query->latest('purchase_date')->get()
                ->groupBy(function ($item) {
                    return $item->vendor_id . '-' . $item->purchase_date->format('Y-m-d') . '-' . $item->invoice_photo_path;
                })
                ->map(function ($group) {
                    $first = $group->first();
                    return (object) [
                        'id' => $first->id, // Representing the "Bill ID" (using first item's ID)
                        'purchase_date' => $first->purchase_date,
                        'vendor' => $first->vendor,
                        'vendor_name' => $first->vendor_name,
                        'invoice_photo_path' => $first->invoice_photo_path,
                        'goods_photo_path' => $first->goods_photo_path,
                        'invoice_url' => $first->invoice_url,
                        'goods_urls' => $first->goods_urls,
                        'status' => $first->status,
                        'creator' => $first->creator,
                        'total_bill_amount' => $group->sum('total_price'),
                        'items_count' => $group->count(),
                        'items' => $group,
                        // For approval logic, we check if ANY item is pending
                        'is_pending' => $group->contains('status', 'pending'),
                    ];
                });

            // Manual pagination for grouped collection
            $perPage = 15;
            $page = $request->input('page', 1);
            $paginatedPurchases = new \Illuminate\Pagination\LengthAwarePaginator(
                $purchases->forPage($page, $perPage),
                $purchases->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return view('admin.purchases.index', [
                'purchases' => $paginatedPurchases,
                'view' => 'bill'
            ]);
        }

        $purchases = $query->latest('purchase_date')->paginate(15);
        return view('admin.purchases.index', [
            'purchases' => $purchases,
            'view' => 'item'
        ]);
    }

    public function create(string $kitchen_slug)
    {
        // Show all ingredients regardless of status for purchase form
        // Users can purchase any ingredient, even if it's pending approval
        $ingredients = Ingredient::orderBy('name')
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

        $vendors = \App\Models\Vendor::orderBy('name')->get();

        return view('admin.purchases.create', compact('ingredients', 'vendors'));
    }

    public function store(Request $request, string $kitchen_slug)
    {
        $request->validate([
            'purchase_date' => 'required|date',
            'vendor_id' => 'required|exists:vendors,id',
            'invoice_photo' => 'required|image|max:4096', // 4MB
            'goods_photo' => 'required|array|min:1',
            'goods_photo.*' => 'required|image|max:4096',
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            // 'items.*.unit_price' => 'required|numeric|min:0', // User wants auto-calc total. So input is unit price and qty.
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.unit' => 'required|string',
        ]);

        $uploadedFiles = [];

        try {
            $disk = config('filesystems.default');

            // Upload Photos
            $invoicePath = $request->file('invoice_photo')->store('purchases/invoices', $disk);
            if (!$invoicePath)
                throw new \Exception('Failed to upload invoice photo.');
            $uploadedFiles[] = $invoicePath;

            $goodsPaths = [];
            if ($request->hasFile('goods_photo')) {
                foreach ($request->file('goods_photo') as $file) {
                    $path = $file->store('purchases/goods', $disk);
                    if (!$path)
                        throw new \Exception('Failed to upload a goods photo.');
                    $goodsPaths[] = $path;
                    $uploadedFiles[] = $path;
                }
            }

            // Unit Service
            $unitService = app(\App\Services\UnitConversionService::class);

            DB::transaction(function () use ($request, $invoicePath, $goodsPaths, $unitService) {
                foreach ($request->items as $item) {
                    $ingredient = Ingredient::findOrFail($item['ingredient_id']);
                    $inputQty = $item['quantity'];
                    $inputUnit = $item['unit'];

                    // Use provided unit_price or fetch from ingredient
                    $inputUnitPrice = isset($item['unit_price']) ? $item['unit_price'] : ($ingredient->price ?? 0);

                    // Calculate Total Price (Input Qty * Input Unit Price)
                    $totalPrice = $inputQty * $inputUnitPrice;

                    // Normalize Quantity to Ingredient's Base Unit
                    // Skip conversion if ingredient has no unit or units match
                    if (!$ingredient->measurement_unit || $inputUnit === $ingredient->measurement_unit) {
                        $normalizedQty = $inputQty;
                    } else {
                        try {
                            $normalizedQty = $unitService->convert($inputQty, $inputUnit, $ingredient->measurement_unit);
                        } catch (\Exception $e) {
                            // Log error and fallback (or throw to rollback)
                            // For data integrity, it is safer to fail than to store wrong units.
                            throw new \Exception("Unit conversion failed for {$ingredient->name}: " . $e->getMessage());
                        }
                    }

                    // Calculate Normalized Unit Price (Total / Normalized Qty)
                    $normalizedUnitPrice = $normalizedQty > 0 ? ($totalPrice / $normalizedQty) : 0;

                    // Create Purchase Record - Stores NORMALIZED values
                    Purchase::create([
                        'ingredient_id' => $ingredient->id,
                        'quantity' => $normalizedQty,
                        'unit_price' => $normalizedUnitPrice,
                        'total_price' => $totalPrice, // Total price remains same regardless of unit
                        'purchase_date' => $request->purchase_date,
                        'vendor_id' => $request->vendor_id,
                        'vendor' => \App\Models\Vendor::find($request->vendor_id)->name, // Keep for legacy/display compatibility if views use it
                        'created_by' => Auth::id(),
                        'invoice_photo_path' => $invoicePath,
                        'goods_photo_path' => $goodsPaths,
                        'status' => 'pending',
                    ]);
                }
            });

            return redirect()->route('purchases.index')->with('success', 'Purchase recorded successfully.');

        } catch (\Exception $e) {
            $disk = config('filesystems.default');
            foreach ($uploadedFiles as $path) {
                Storage::disk($disk)->delete($path);
            }

            return back()->withErrors(['error' => 'Purchase failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function approve(string $kitchen_slug, Purchase $purchase)
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

    public function bulkApprove(Request $request, string $kitchen_slug)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected for approval.');
        }

        $purchases = Purchase::whereIn('id', $ids)->where('status', 'pending')->get();

        foreach ($purchases as $purchase) {
            $this->approve($kitchen_slug, $purchase);
        }

        return back()->with('success', count($purchases) . ' items in bill approved.');
    }


    public function reject(Request $request, string $kitchen_slug, Purchase $purchase)
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

    /**
     * Download single invoice (image/PDF)
     */
    public function downloadInvoice(string $kitchen_slug, Purchase $purchase)
    {
        $this->authorize('view', $purchase);

        if (!$purchase->invoice_photo_path) {
            return back()->with('error', 'Invoice not found.');
        }

        $disk = config('filesystems.default');

        if (!Storage::disk($disk)->exists($purchase->invoice_photo_path)) {
            return back()->with('error', 'Invoice file not found.');
        }

        $file = Storage::disk($disk)->get($purchase->invoice_photo_path);
        $mimeType = Storage::disk($disk)->mimeType($purchase->invoice_photo_path);
        $extension = pathinfo($purchase->invoice_photo_path, PATHINFO_EXTENSION) ?: 'jpg';
        $filename = 'invoice_' . $purchase->id . '_' . $purchase->purchase_date->format('Y-m-d') . '.' . $extension;

        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Download all invoices from last 90 days as ZIP
     */
    public function downloadInvoices90Days(string $kitchen_slug)
    {
        $this->authorize('viewAny', Purchase::class);

        $startDate = now()->subDays(90);

        $query = Purchase::where('purchase_date', '>=', $startDate)
            ->whereNotNull('invoice_photo_path');

        if (Auth::user()->isStaff()) {
            $query->where('created_by', Auth::id());
        }

        $purchases = $query->get();

        if ($purchases->isEmpty()) {
            return back()->with('error', 'No invoices found in the last 90 days.');
        }

        $disk = config('filesystems.default');
        $zipPath = storage_path('app/temp/invoices_90days_' . now()->format('Y-m-d_His') . '.zip');

        // Create temp directory if not exists
        $tempDir = dirname($zipPath);
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            return back()->with('error', 'Failed to create ZIP file.');
        }

        $addedCount = 0;
        foreach ($purchases as $purchase) {
            if (Storage::disk($disk)->exists($purchase->invoice_photo_path)) {
                try {
                    $fileContent = Storage::disk($disk)->get($purchase->invoice_photo_path);
                    $extension = pathinfo($purchase->invoice_photo_path, PATHINFO_EXTENSION) ?: 'jpg';
                    $ingredientName = $purchase->ingredient->name ?? 'Unknown';
                    // Sanitize filename - remove special characters
                    $ingredientName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $ingredientName);
                    $zipFilename = 'Invoice_' . $purchase->id . '_' . $purchase->purchase_date->format('Y-m-d') . '_' . $ingredientName . '.' . $extension;
                    $zip->addFromString($zipFilename, $fileContent);
                    $addedCount++;
                } catch (\Exception $e) {
                    Log::warning('Failed to add invoice to ZIP: ' . $e->getMessage(), ['purchase_id' => $purchase->id]);
                    continue;
                }
            }
        }

        $zip->close();

        if ($addedCount === 0) {
            @unlink($zipPath);
            return back()->with('error', 'No invoice files found to download.');
        }

        return response()->download($zipPath, 'invoices_last_90_days_' . now()->format('Y-m-d') . '.zip')
            ->deleteFileAfterSend(true);
    }
}
