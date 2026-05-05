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
    protected $fifoService;

    public function __construct(ExcelImportService $excelService, PdfInventoryParser $pdfParser, \App\Services\FIFOInventoryService $fifoService)
    {
        $this->excelService = $excelService;
        $this->pdfParser = $pdfParser;
        $this->fifoService = $fifoService;
    }

    public function index(Request $request, string $kitchen_slug)
    {
        $query = Ingredient::query()->with([
            'category',
            'purchases' => fn($q) => $q->where('status', 'approved')->with('vendor'),
        ]);

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by storage location
        if ($request->filled('storage_location')) {
            $query->where('storage_location', $request->storage_location);
        }

        // Filter by allergen
        if ($request->filled('allergen')) {
            $query->whereJsonContains('allergen_tags', $request->allergen);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    // Low stock: current_stock <= alert_threshold AND current_stock > 0 AND alert_threshold > 0
                    // If include_out parameter is set, also include out of stock items (stock <= 0)
                    if ($request->has('include_out') && $request->include_out == '1') {
                        // Show both low stock AND out of stock items
                        $query->where(function ($q) {
                            $q->where(function ($subQ) {
                                // Low stock: stock > 0 but <= threshold
                                $subQ->whereColumn('current_stock', '<=', 'alert_threshold')
                                    ->where('current_stock', '>', 0)
                                    ->where('alert_threshold', '>', 0);
                            })->orWhere(function ($subQ) {
                                // Out of stock: stock <= 0
                                $subQ->where('current_stock', '<=', 0);
                            });
                        });
                    } else {
                        // Only low stock (excludes out of stock)
                        $query->whereColumn('current_stock', '<=', 'alert_threshold')
                            ->where('current_stock', '>', 0)
                            ->where('alert_threshold', '>', 0);
                    }
                    break;
                case 'out':
                    // Out of stock: current_stock <= 0
                    $query->where('current_stock', '<=', 0);
                    break;
                case 'ok':
                    // OK stock: current_stock > alert_threshold (when threshold > 0) OR alert_threshold is 0/null
                    $query->where(function ($q) {
                        $q->where(function ($subQ) {
                            $subQ->whereColumn('current_stock', '>', 'alert_threshold')
                                ->where('alert_threshold', '>', 0);
                        })->orWhere(function ($subQ) {
                            $subQ->where('alert_threshold', '<=', 0)
                                ->orWhereNull('alert_threshold');
                        });
                    });
                    break;
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');

        $allowedSorts = ['name', 'current_stock', 'price', 'category_name'];
        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'category_name') {
                $query->leftJoin('categories', 'ingredients.category_id', '=', 'categories.id')
                    ->select('ingredients.*')
                    ->orderBy('categories.name', $sortDir);
            } else {
                $query->orderBy($sortBy, $sortDir);
            }
        }

        $inventory = $query->paginate(50)->withQueryString();

        // Data for filters
        $categories = \App\Models\Category::orderBy('name')->get();
        $storageLocations = Ingredient::whereNotNull('storage_location')
            ->distinct()
            ->pluck('storage_location')
            ->sort()
            ->values();
        $allergens = \App\Enums\Allergen::cases();

        return view('admin.inventory.index', compact('inventory', 'categories', 'storageLocations', 'allergens'));
    }

    public function upload(string $kitchen_slug)
    {
        return view('admin.inventory.upload');
    }

    public function import(Request $request, string $kitchen_slug)
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

    public function importSalesReport(Request $request, string $kitchen_slug)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $result = $this->excelService->importSalesReport($request->file('file'), Auth::user());

        if (!empty($result['errors'])) {
            return redirect()->route('admin.inventory.index')
                ->with('warning', "Processed {$result['success']} sales records. Errors in " . count($result['errors']) . " rows.")
                ->with('import_errors', $result['errors']);
        }

        return redirect()->route('admin.inventory.index')->with('success', "Sales report processed successfully. {$result['success']} items adjusted.");
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

    public function adjust(Request $request, string $kitchen_slug, Ingredient $ingredient)
    {
        // restricted to: Audit correction, Damage, Opening balance fix
        $request->validate([
            'adjustment_quantity' => 'required|numeric', // Can be negative
            'reason' => ['required', 'string', \Illuminate\Validation\Rule::in(['Audit correction', 'Damage', 'Opening balance fix'])],
        ]);

        DB::transaction(function () use ($request, $ingredient) {
            $oldStock = $ingredient->current_stock;
            $quantityChange = $request->adjustment_quantity;
            $newStock = $oldStock + $quantityChange;

            if ($newStock < 0) {
                throw new \Exception("Insufficient stock. Adjustment would result in negative stock.");
            }

            // FIFO Handling
            if ($quantityChange < 0) {
                // Deduction: Use FIFO logic
                $this->fifoService->deductStock(
                    $ingredient, 
                    abs($quantityChange), 
                    'Adjustment', 
                    null // Manual adjustment doesn't have a source log id usually, or we can use the InventoryLog id later
                );
            } else if ($quantityChange > 0 && $request->reason === 'Opening balance fix') {
                // Adding stock via opening balance: Create a "Virtual Batch"
                \App\Models\PurchaseBatch::create([
                    'kitchen_id' => $ingredient->kitchen_id,
                    'ingredient_id' => $ingredient->id,
                    'quantity_initial' => $quantityChange,
                    'quantity_remaining' => $quantityChange,
                    'price_per_unit' => $ingredient->price ?? 0,
                    'created_at' => now(),
                ]);
            }

            $ingredient->current_stock = $newStock;
            $ingredient->save();

            InventoryLog::create([
                'ingredient_id' => $ingredient->id,
                'user_id' => Auth::id(),
                'quantity_change' => $quantityChange,
                'action' => 'adjustment',
                'stock_before' => $oldStock,
                'stock_after' => $newStock,
                'reason' => $request->reason,
            ]);

            // Add to central Audit Log
            \App\Models\AuditLog::log(
                'Manual Adjustment: ' . $request->reason,
                $ingredient,
                ['current_stock' => $oldStock],
                [
                    'current_stock' => $newStock, 
                    'adjustment' => $quantityChange,
                    'reason' => $request->reason
                ]
            );
        });

        return back()->with('success', 'Stock adjusted successfully.');
    }
    public function show(string $kitchen_slug, Ingredient $ingredient)
    {
        // Load logs with relationships
        $logs = $ingredient->logs()
            ->with(['user', 'productionLog.recipe', 'recipe']) // Eager load relationships
            ->latest()
            ->paginate(20);

        return view('admin.inventory.show', compact('ingredient', 'logs'));
    }

    public function destroy(string $kitchen_slug, Ingredient $ingredient)
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

    public function bulkDestroy(Request $request, string $kitchen_slug)
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
