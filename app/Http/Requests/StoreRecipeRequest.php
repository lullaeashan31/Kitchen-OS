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
            'method' => 'required|string',
            'yields' => 'required|integer|min:1',
            'yield_portions' => 'nullable|numeric|min:0',
            'yield_weight' => 'nullable|numeric|min:0',
            'yield_weight_unit' => 'nullable|string',
            'prep_time_minutes' => 'nullable|integer|min:0',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.name' => 'nullable|string',
            'ingredients.*.ingredient_id' => 'required',
            'ingredients.*.quantity' => 'required|numeric|min:0',
            'ingredients.*.unit' => ['required', Rule::enum(Unit::class)],
            'ingredients.*.ingredient_group' => 'nullable|string',
            'ingredients.*.cost' => 'nullable|numeric|min:0',
            'produces_ingredient_id' => 'nullable|exists:ingredients,id',
            'output_quantity' => 'nullable|numeric|min:0',
            'output_unit' => ['nullable', Rule::enum(Unit::class)],
        ];
    }
}
