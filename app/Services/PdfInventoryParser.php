<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class PdfInventoryParser
{
    /**
     * Parse PDF file and extract inventory data
     */
    public function parsePdfToArray(UploadedFile $file): array
    {
        try {
            // Use pdftotext command to extract text
            $tempPath = $file->getRealPath();
            $outputPath = storage_path('app/temp_pdf_text_' . time() . '.txt');

            // Extract text using pdftotext with layout preservation
            $command = "pdftotext -layout " . escapeshellarg($tempPath) . " " . escapeshellarg($outputPath) . " 2>&1";
            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($outputPath)) {
                $text = file_get_contents($outputPath);

                // Save a copy for debugging
                $debugPath = storage_path('app/last_pdf_extraction.txt');
                file_put_contents($debugPath, $text);

                unlink($outputPath); // Clean up temp file

                Log::info('PDF text extracted successfully', ['length' => strlen($text)]);

                return $this->parseTextToInventoryData($text);
            }

            Log::warning('PDF text extraction failed', [
                'return_code' => $returnCode,
                'output' => implode("\n", $output)
            ]);
            return [];

        } catch (\Exception $e) {
            Log::error('PDF parsing error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Parse extracted text to inventory data format
     */
    private function parseTextToInventoryData(string $text): array
    {
        $items = [];
        $lines = explode("\n", $text);

        Log::info('Parsing PDF text', ['total_lines' => count($lines)]);

        foreach ($lines as $lineNum => $line) {
            $originalLine = $line;
            $line = trim($line);

            // Skip empty lines, headers, and section titles
            if (
                empty($line) ||
                str_contains($line, 'Item Name') ||
                str_contains($line, 'Itemwise Data') ||
                str_contains($line, 'Category Wise Data') ||
                str_contains($line, 'Generated On') ||
                str_contains($line, 'about:blank') ||
                str_contains($line, 'Date') ||
                str_contains($line, 'Number')
            ) {
                continue;
            }

            // Look for data lines that start with a date pattern (YYYY-MM-DD)
            if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $line)) {
                $this->extractItemFromLine($originalLine, $items);
            }
        }

        Log::info('PDF parsing complete', ['items_found' => count($items)]);

        return $items;
    }

    /**
     * Extract item data from a line using position-based parsing
     * Format: "2023-05-12 11            Bacardi Black                       Rum                Liquor         1.00 0.00..."
     */
    private function extractItemFromLine(string $line, array &$items): void
    {
        // Use regex to extract the structured data
        // Pattern: Date ItemNum ItemName Category SuperCategory Qty...
        if (!preg_match('/^(\d{4}-\d{2}-\d{2})\s+(\d+)\s+(.+)/', $line, $matches)) {
            return;
        }

        $date = $matches[1];
        $itemNumber = $matches[2];
        $restOfLine = $matches[3];

        // Split the rest by multiple spaces (3 or more to be safe)
        $parts = preg_split('/\s{3,}/', $restOfLine);
        $parts = array_values(array_filter(array_map('trim', $parts), fn($p) => !empty($p)));

        if (count($parts) < 3) {
            Log::debug('Not enough parts after splitting', ['line' => substr($line, 0, 100)]);
            return;
        }

        // Now parts should be: [ItemName, Category, SuperCategory, Qty, Complimentary, ...]
        $itemName = $parts[0] ?? '';
        $category = $parts[1] ?? '';
        $superCategory = $parts[2] ?? '';

        // Extract numeric values from remaining parts
        $qty = 1.0;
        $price = 0.0;

        // Look for numeric values
        for ($i = 3; $i < count($parts); $i++) {
            // Split by spaces to get individual numbers
            $numbers = preg_split('/\s+/', $parts[$i]);
            foreach ($numbers as $num) {
                if (is_numeric($num)) {
                    $numVal = floatval($num);
                    // First small positive number is likely quantity
                    if ($numVal > 0 && $numVal <= 100 && $qty == 1.0) {
                        $qty = $numVal;
                    }
                    // Larger numbers (> 100) are likely prices/subtotals
                    elseif ($numVal > 100 && $price == 0.0) {
                        $price = $numVal;
                    }
                }
            }
        }

        // Validate and clean item name
        if (!empty($itemName) && strlen($itemName) > 2) {
            // Use super category if it looks valid, otherwise use category
            $finalCategory = 'Uncategorized';

            // SuperCategory is usually more descriptive (Liquor, Main Course, Starters, etc.)
            if (!empty($superCategory) && !is_numeric($superCategory) && !str_contains($superCategory, '0.00')) {
                $finalCategory = $superCategory;
            } elseif (!empty($category) && !is_numeric($category) && !str_contains($category, '0.00')) {
                $finalCategory = $category;
            }

            $items[] = [
                'item_name' => $itemName,
                'category' => $finalCategory,
                'measurement_unit' => 'unit',
                'purchase_unit' => 'unit',
                'price_per_unit' => $price,
                'vendor' => 'Imported from PDF',
                'minimum_stock_level' => 10,
                'current_stock' => $qty,
            ];

            Log::info('Successfully parsed item', [
                'item_name' => $itemName,
                'category' => $category,
                'super_category' => $superCategory,
                'final_category' => $finalCategory,
                'qty' => $qty,
                'price' => $price
            ]);
        }
    }
}
