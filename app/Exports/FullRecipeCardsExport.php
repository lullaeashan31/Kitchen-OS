<?php

namespace App\Exports;

use App\Models\Recipe;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Collection;

class FullRecipeCardsExport implements WithMultipleSheets
{
    protected $recipes;

    public function __construct(Collection $recipes)
    {
        $this->recipes = $recipes;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        foreach ($this->recipes as $recipe) {
            $sheets[] = new FullRecipeCardSheet($recipe);
        }

        return $sheets;
    }

    public static function export(Collection $recipes)
    {
        $export = new self($recipes);
        return \Maatwebsite\Excel\Facades\Excel::download($export, 'recipe-cards-' . date('Y-m-d') . '.xlsx');
    }
}
