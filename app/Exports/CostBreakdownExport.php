<?php

namespace App\Exports;

use App\Models\Recipe;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;

class CostBreakdownExport implements WithMultipleSheets
{
    protected $recipes;

    public function __construct(Collection $recipes)
    {
        $this->recipes = $recipes->load(['category', 'recipeIngredients.ingredient']);
    }

    public function sheets(): array
    {
        return [
            new CostBreakdownSummarySheet($this->recipes),
            new CostBreakdownDetailSheet($this->recipes),
        ];
    }

    public static function export(Collection $recipes)
    {
        $export = new self($recipes);
        return \Maatwebsite\Excel\Facades\Excel::download($export, 'cost-breakdown-' . date('Y-m-d') . '.xlsx');
    }
}
