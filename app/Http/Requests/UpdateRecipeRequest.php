<?php

namespace App\Http\Requests;

use App\Enums\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'method' => 'nullable|string',
            // Yield System - At least one is required
            'yield_portions' => 'required_without_all:yield_weight,yield_volume,yield_batches|nullable|integer|min:1',
            'yield_weight' => 'required_without_all:yield_portions,yield_volume,yield_batches|nullable|numeric|min:0',
            'yield_weight_unit' => 'nullable|required_with:yield_weight|string|in:g,kg,oz,lb',
            'yield_volume' => 'required_without_all:yield_portions,yield_weight,yield_batches|nullable|numeric|min:0',
            'yield_volume_unit' => 'nullable|required_with:yield_volume|string|in:ml,l,fl_oz,cup',
            'yield_batches' => 'required_without_all:yield_portions,yield_weight,yield_volume|nullable|integer|min:1',
            'prep_time_minutes' => 'nullable|integer|min:0',

            'stages' => 'required|array|min:1',
            'stages.*.id' => 'nullable|exists:recipe_stages,id',
            'stages.*.name' => 'required|string|max:255',
            'stages.*.method' => 'nullable|string',
            'stages.*.ingredients' => 'nullable|array',
            'stages.*.ingredients.*.name' => 'nullable|string',
            'stages.*.ingredients.*.ingredient_id' => 'required',
            'stages.*.ingredients.*.quantity' => 'required|numeric|min:0',
            'stages.*.ingredients.*.unit' => ['required', Rule::enum(Unit::class)],
            'stages.*.ingredients.*.ingredient_group' => 'nullable|string',
            'stages.*.ingredients.*.cost' => 'nullable|numeric|min:0',
        ];
    }
}
