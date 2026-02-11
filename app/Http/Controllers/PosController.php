<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\DB;
use App\Services\ExcelImportService; // Reuse or create new? Let's keep logic here for now or distinct service.

class PosController extends Controller
{
    public function uploadForm()
    {
        return view('pos.upload');
    }

    public function parse(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');

        // Simple CSV/Excel Parse
        // Assuming we use a library like fast-excel or just box/spout, or phpoffice/phpspreadsheet.
        // ExcelController uses ExcelImportService, let's see if we can reuse or just use basic logic.
        // For now, I'll use a simple array parser helper or assume a Service exists.
        // Let's write a simple parser here using fgetcsv if csv, or use the existing service if adaptable.

        // Let's assume we can map rows to key-value.
        $rows = $this->fileToArray($file);

        // Analyze Rows
        $mappedItems = [];
        $unmappedItems = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            // Flexible Column Matching
            $name = $row['item_name'] ?? $row['product'] ?? $row['description'] ?? $row['name'] ?? null;
            $qty = $row['quantity'] ?? $row['qty'] ?? $row['count'] ?? $row['sold'] ?? 0;

            if (!$name) {
                continue; // Skip empty rows
            }

            // Find Recipe
            // Try exact match first
            $recipe = Recipe::where('name', 'LIKE', $name)->where('status', 'permanent')->first();

            // If no match, maybe try soundex or just fail? User wants "Map POS items".
            // Implementation: "Show unmapped items warning".

            if ($recipe) {
                $mappedItems[] = [
                    'name' => $name,
                    'recipe_id' => $recipe->id,
                    'recipe_name' => $recipe->name,
                    'quantity' => floatval($qty),
                    'status' => 'mapped'
                ];
            } else {
                $unmappedItems[] = [
                    'name' => $name,
                    'quantity' => floatval($qty),
                    'status' => 'unmapped'
                ];
            }
        }

        // Store in session for confirmation
        session()->put('pos_import_data', ['mapped' => $mappedItems, 'unmapped' => $unmappedItems]);

        return view('pos.preview', compact('mappedItems', 'unmappedItems'));
    }

    public function process()
    {
        $data = session()->get('pos_import_data');
        if (!$data) {
            return redirect()->route('pos.upload')->with('error', 'Session expired. Please upload again.');
        }

        $mapped = $data['mapped'];
        $processedCount = 0;

        DB::transaction(function () use ($mapped, &$processedCount) {
            foreach ($mapped as $item) {
                $recipe = Recipe::with('ingredients')->find($item['recipe_id']);
                $qtySold = $item['quantity'];

                if (!$recipe)
                    continue;

                // Deduct Inventory based on Recipe
                foreach ($recipe->ingredients as $ingredient) {
                    $qtyPerPortion = $ingredient->pivot->quantity / ($recipe->yields ?: 1);
                    $totalDeduct = $qtyPerPortion * $qtySold;

                    $ingredient->decrement('current_stock', $totalDeduct);

                    // Log it
                    InventoryLog::create([
                        'ingredient_id' => $ingredient->id,
                        'user_id' => auth()->id(),
                        'quantity_change' => -$totalDeduct,
                        'action' => 'POS_SALE',
                        'notes' => "POS Sales: {$qtySold} x {$recipe->name}",
                        'stock_before' => $ingredient->current_stock + $totalDeduct,
                        'stock_after' => $ingredient->current_stock,
                    ]);
                }
                $processedCount++;
            }
        });

        session()->forget('pos_import_data');

        return redirect()->route('pos.upload')->with('success', "Processed {$processedCount} sales items. Inventory updated.");
    }

    private function fileToArray($file)
    {
        // quick helper for task
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));
        $header = array_map('strtolower', array_map('trim', array_shift($data)));

        $result = [];
        foreach ($data as $row) {
            if (count($row) === count($header)) {
                $result[] = array_combine($header, $row);
            }
        }
        return $result;
    }
}
