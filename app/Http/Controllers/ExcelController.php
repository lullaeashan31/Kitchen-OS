<?php

namespace App\Http\Controllers;

use App\Services\ExcelImportService;
use App\Services\ExcelExportService;
use App\Http\Requests\ImportRecipesRequest;
use Illuminate\Http\Request;

class ExcelController extends Controller
{
    protected $importService;
    protected $exportService;

    public function __construct(ExcelImportService $importService, ExcelExportService $exportService)
    {
        $this->importService = $importService;
        $this->exportService = $exportService;
    }

    public function importForm()
    {
        return view('excel.import');
    }

    public function import(ImportRecipesRequest $request)
    {
        $result = $this->importService->importRecipes($request->file('file'), $request->user());

        if (!empty($result['errors'])) {
            // Store errors in session to download later or show immediate download link
            // Or better: Generate Error Excel immediately and force download?
            // "Error Excel export" -> User might want to see summary first.
            // Let's redirect back with summary and a link to download errors.

            // Store errors in cache or session
            $errorId = uniqid('import_errors_');
            cache()->put($errorId, $result['errors'], 3600); // 1 hour

            return redirect()->route('excel.import_form')
                ->with('warning', "Imported {$result['success']} recipes. encountered errors in " . count($result['errors']) . " recipes.")
                ->with('error_download_id', $errorId);
        }

        return redirect()->route('recipes.index')
            ->with('success', "Successfully imported {$result['success']} recipes.");
    }

    public function downloadErrors($errorId)
    {
        $errors = cache()->get($errorId);

        if (!$errors) {
            return redirect()->route('excel.import_form')->with('error', 'Error report expired or invalid.');
        }

        return $this->exportService->generateErrorExcel($errors);
    }

    public function exportRecipes()
    {
        return $this->exportService->exportRecipeList();
    }

    public function downloadTemplate()
    {
        return $this->exportService->downloadTemplate();
    }

    public function exportCostSummary()
    {
        return $this->exportService->exportCostSummary();
    }
}
