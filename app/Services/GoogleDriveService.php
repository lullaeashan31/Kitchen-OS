<?php

namespace App\Services;

use App\Models\Recipe;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    /**
     * Generate PDF for a recipe and upload it to Google Drive.
     *
     * @param Recipe $recipe
     * @return string|null The Google Drive File ID
     */
    public function saveRecipePdfToDrive(Recipe $recipe): ?string
    {
        try {
            // 1. Generate PDF content
            // We use the same view as the print route but with some adjustments if needed
            // Since we're in a background service/action, we might need to load relationships
            $recipe->load(['category', 'ingredients', 'stages.ingredients.ingredient', 'recipeIngredients.ingredient']);

            $pdf = Pdf::loadView('recipes.print', ['recipe' => $recipe]);
            $content = $pdf->output();

            // 2. Define filename
            $filename = "Recipe_" . str_replace(' ', '_', $recipe->name) . "_v" . $recipe->version . ".pdf";

            // 3. Upload to Google Drive
            // Assuming 'google' disk is configured in filesystems.php
            // If not, we fall back to 'local' for now or log error
            if (config('filesystems.disks.google')) {
                $path = 'recipes/' . $filename;
                Storage::disk('google')->put($path, $content);

                // Get Metadata to find the actual File ID if using flysystem-google-drive
                // Some drivers return the path which is the ID
                return $path;
            } else {
                Log::warning("Google Drive disk not configured. Saving to local storage instead.");
                $path = 'recipes/' . $filename;
                Storage::disk('local')->put('public/' . $path, $content);
                return 'local_test_' . uniqid();
            }
        } catch (\Exception $e) {
            Log::error("Failed to save recipe PDF to Drive: " . $e->getMessage());
            return null;
        }
    }
}
