<?php

namespace App\Services;

use App\Exports\ErrorsExport;
use App\Exports\RecipesExport;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExportService
{
    public function exportRecipeList()
    {
        return Excel::download(new RecipesExport, 'recipes.xlsx');
    }

    public function generateErrorExcel(array $errors)
    {
        return Excel::download(new ErrorsExport($errors), 'import_errors.xlsx');
    }

    /**
     * Generate template for import.
     * Basically same as recipe export but empty or with example.
     */
    public function downloadTemplate()
    {
        return Excel::download(new RecipesExport, 'recipe_import_template.xlsx');
    }

    public function downloadInventoryTemplate()
    {
        return Excel::download(new \App\Exports\InventoryTemplateExport, 'inventory_import_template.xlsx');
    }

    public function exportCostSummary()
    {
        return Excel::download(new \App\Exports\RecipeCostExport, 'recipe_cost_summary.xlsx');
    }
}
