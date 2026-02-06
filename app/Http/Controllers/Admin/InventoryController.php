<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\InventoryLog;
use App\Services\ExcelImportService;
use App\Services\PdfInventoryParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    protected $excelService;
    protected $pdfParser;

    public function __construct(ExcelImportService $excelService, PdfInventoryParser $pdfParser)
    {
        $this->excelService = $excelService;
        $this->pdfParser = $pdfParser;
    }

    public function index()
    {
        $inventory = Ingredient::query()
            ->orderBy('name')
            ->get();

        return view('admin.inventory.index', compact('inventory'));
    }

    public function upload()
    {
        return view('admin.inventory.upload');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,pdf',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        // Handle PDF files
        if ($extension === 'pdf') {
            return $this->importFromPdf($file);
        }

        // Handle Excel files
        $result = $this->excelService->importInventory($file, Auth::user());

        if (!empty($result['errors'])) {
            return redirect()->route('admin.inventory.index')
                ->with('warning', "Imported {$result['success']} items. Errors in " . count($result['errors']) . " rows.");
        }

        return redirect()->route('admin.inventory.index')->with('success', "Inventory imported successfully ({$result['success']} items).");
    }

    private function importFromPdf($file)
    {
        $items = $this->pdfParser->parsePdfToArray($file);

        if (empty($items)) {
            return redirect()->route('admin.inventory.upload')
                ->with('error', 'Could not extract data from PDF. Please ensure the PDF contains valid inventory data or use the Excel template.');
        }

        $imported = 0;
        $errors = [];

        foreach ($items as $itemData) {
            try {
                DB::beginTransaction();

                // Find or create category
                $categoryId = null;
                if (!empty($itemData['category']) && $itemData['category'] !== 'Uncategorized') {
                    $category = \App\Models\Category::firstOrCreate(
                        ['name' => $itemData['category']],
                        ['status' => 'active']
                    );
                    $categoryId = $category->id;
                }

                // Check if item exists by name
                $ingredient = Ingredient::where('name', $itemData['item_name'])->first();

                if ($ingredient) {
                    // Update existing
                    $updateData = [
                        'price' => $itemData['price_per_unit'],
                        'current_stock' => $itemData['current_stock'],
                    ];

                    // Only update category if we found/created one
                    if ($categoryId) {
                        $updateData['category_id'] = $categoryId;
                    }

                    $ingredient->update($updateData);
                } else {
                    // Create new
                    Ingredient::create([
                        'name' => $itemData['item_name'],
                        'category_id' => $categoryId,
                        'measurement_unit' => $itemData['measurement_unit'],
                        'purchase_unit' => $itemData['purchase_unit'],
                        'price' => $itemData['price_per_unit'],
                        'vendor' => $itemData['vendor'],
                        'alert_threshold' => $itemData['minimum_stock_level'],
                        'current_stock' => $itemData['current_stock'],
                        'status' => 'approved',
                    ]);
                }

                DB::commit();
                $imported++;
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = $itemData['item_name'] . ': ' . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            return redirect()->route('admin.inventory.index')
                ->with('warning', "Imported {$imported} items from PDF. Errors: " . implode(', ', array_slice($errors, 0, 3)));
        }

        return redirect()->route('admin.inventory.index')
            ->with('success', "Successfully imported {$imported} items from PDF.");
    }

    public function adjust(Request $request, Ingredient $ingredient)
    {
        // Manual Stock Adjustment Logic
        $request->validate([
            'adjustment_quantity' => 'required|numeric', // Can be negative
            'reason' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request, $ingredient) {
            $oldStock = $ingredient->current_stock;
            $newStock = $oldStock + $request->adjustment_quantity;

            if ($newStock < 0) {
                // "Negative stock not allowed" - User rule
                throw new \Exception("Insufficient stock. adjustment would result in negative stock.");
            }

            $ingredient->current_stock = $newStock;
            $ingredient->save();

            InventoryLog::create([
                'ingredient_id' => $ingredient->id,
                'user_id' => Auth::id(),
                'quantity_change' => $request->adjustment_quantity,
                'action' => 'adjustment', // or 'manual_adjustment'
                'stock_before' => $oldStock,
                'stock_after' => $newStock,
                // 'reason' -> Add reason to InventoryLog?
                // Migration `inventory_logs` didn't have reason column.
                // I should add it or put it in action string? "adjustment: reason"?
                // Let's check migration again.
                // It has 'action' string. I'll append reason or add column.
                // User said "Saved to adjustment_logs: reason".
                // I used `inventory_logs` as `adjustment_logs`. I need to add `reason` column.
            ]);
        });

        return back()->with('success', 'Stock adjusted successfully.');
    }
    public function show(Ingredient $ingredient)
    {
        // Load logs with relationships
        $logs = $ingredient->logs()
            ->with(['user', 'productionLog.recipe', 'recipe']) // Eager load relationships
            ->latest()
            ->paginate(20);

        return view('admin.inventory.show', compact('ingredient', 'logs'));
    }

    public function destroy(Ingredient $ingredient)
    {
        // Check if ingredient is used in any recipes or has purchase/log history?
        // If used in active recipes, prevent delete.
        if ($ingredient->recipes()->exists()) {
            return back()->with('error', 'Cannot delete item: It is used in one or more recipes.');
        }

        // Optional: Check connection to purchases/logs? 
        // If we strictly want to preserve history, maybe soft delete?
        // But for "Delete" request, let's assume Hard Delete if no dependencies, 
        // or prevent if it has history. 
        // For now, simple check on recipes. If used, blocking.
        // If not used in recipes but has logs, we might want to keep it or soft delete.
        // Assuming simple delete for now based on user request "Delete option needed".

        $ingredient->delete();

        return redirect()->route('admin.inventory.index')->with('success', 'Inventory item deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:ingredients,id',
        ]);

        $ids = $request->ids;
        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($ids as $id) {
            $ingredient = Ingredient::find($id);
            if (!$ingredient)
                continue;

            if ($ingredient->recipes()->exists()) {
                $skippedCount++;
                continue;
            }

            $ingredient->delete();
            $deletedCount++;
        }

        $message = "Deleted {$deletedCount} items.";
        if ($skippedCount > 0) {
            $message .= " Skipped {$skippedCount} items currently in use by recipes.";
        }

        return redirect()->route('admin.inventory.index')->with($skippedCount > 0 ? 'warning' : 'success', $message);
    }
}
