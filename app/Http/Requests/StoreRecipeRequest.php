<?php

namespace App\Http\Requests;

use App\Enums\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy handles authorization
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            // method is now optional/legacy, stages contain the method
            'method' => 'nullable|string',

            // Yield System - At least one is required
            'yield_portions' => 'required_without:yield_batches|nullable|integer|min:1',
            'yield_batches' => 'required_without:yield_portions|nullable|integer|min:1',
            'yield_weight' => 'nullable|numeric|min:0',
            'yield_weight_unit' => 'nullable|required_with:yield_weight|string|in:g,kg,oz,lb',
            'yield_volume' => 'nullable|numeric|min:0',
            'yield_volume_unit' => 'nullable|required_with:yield_volume|string|in:ml,l,fl_oz,cup',
            'prep_time_minutes' => 'nullable|integer|min:0',

            'stages' => 'required|array|min:1',
            'stages.*.name' => 'required|string|max:255',
            'stages.*.method' => 'nullable|string',
            'stages.*.ingredients' => 'nullable|array',
            'stages.*.ingredients.*.name' => 'nullable|string', // For dynamic creation
            'stages.*.ingredients.*.ingredient_id' => 'required',
            'stages.*.ingredients.*.quantity' => 'required|numeric|min:0',
            'stages.*.ingredients.*.unit' => ['required', Rule::enum(Unit::class)],
            'stages.*.ingredients.*.ingredient_group' => 'nullable|string',
            'stages.*.ingredients.*.cost' => 'nullable|numeric|min:0',

            'produces_ingredient_id' => 'nullable|exists:ingredients,id',
            'output_quantity' => 'nullable|numeric|min:0',
            'output_unit' => ['nullable', Rule::enum(Unit::class)],
        ];
    }
}
