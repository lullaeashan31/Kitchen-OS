<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function uploadForm(string $kitchen_slug)
    {
        return view('pos.upload');
    }

    public function parse(Request $request, string $kitchen_slug)
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

        $permanentRecipes = Recipe::where('status', 'permanent')
            ->get(['id', 'name']);

        foreach ($rows as $index => $row) {
            // Flexible Column Matching
            $name = $row['item_name']
                ?? $row['item']
                ?? $row['product']
                ?? $row['description']
                ?? $row['name']
                ?? null;
            $qty = $row['quantity'] ?? $row['qty'] ?? $row['count'] ?? $row['sold'] ?? 0;

            if (!$name) {
                continue; // Skip empty rows
            }

            $recipe = $this->findRecipeMatch($name, $permanentRecipes);

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

    public function process(string $kitchen_slug)
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
        // Parse CSV and normalize headers (e.g. "Item Name" => "item_name").
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));
        $header = array_map(function ($column) {
            $column = preg_replace('/^\xEF\xBB\xBF/', '', (string) $column); // Strip UTF-8 BOM if present.
            $column = strtolower(trim($column));
            $column = preg_replace('/[\s\-]+/', '_', $column);
            return $column;
        }, array_shift($data));

        $result = [];
        foreach ($data as $row) {
            if (count($row) === count($header)) {
                $result[] = array_combine($header, $row);
            }
        }
        return $result;
    }

    private function normalizeRecipeName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = str_replace(['/', '-', '_'], ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        return $name;
    }

    private function findRecipeMatch(string $name, $recipes): ?Recipe
    {
        $normalizedInput = $this->normalizeRecipeName($name);

        // 1) Exact normalized match first.
        foreach ($recipes as $recipe) {
            if ($this->normalizeRecipeName($recipe->name) === $normalizedInput) {
                return $recipe;
            }
        }

        // 2) Fuzzy fallback to handle minor POS spelling variations.
        $bestMatch = null;
        $bestScore = 0;
        foreach ($recipes as $recipe) {
            similar_text($normalizedInput, $this->normalizeRecipeName($recipe->name), $score);
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $recipe;
            }
        }

        return $bestScore >= 80 ? $bestMatch : null;
    }
}
