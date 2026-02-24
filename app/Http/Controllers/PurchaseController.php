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
    public function index()
    {
        $query = Purchase::with(['ingredient', 'creator', 'approver', 'vendor']);

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

    public function store(Request $request)
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

    /**
     * Download single invoice (image/PDF)
     */
    public function downloadInvoice(Purchase $purchase)
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
    public function downloadInvoices90Days()
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
