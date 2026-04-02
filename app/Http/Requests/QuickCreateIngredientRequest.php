<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuickCreateIngredientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ingredients')->where(function ($query) {
                    return $query->where('kitchen_id', app('current_kitchen')->id);
                }),
            ],
            'category_id' => 'required|exists:categories,id',
            'storage_location' => 'required|string',
            'measurement_unit' => 'required|string',
            'alert_threshold' => 'nullable|numeric|min:0',
            'vendor' => 'nullable|string|max:255',
            'purchase_quantity' => 'nullable|numeric|min:0.001',
            'purchase_unit' => 'nullable|string|max:255',
            'purchase_price' => 'nullable|numeric|min:0',
            'allergen_tags' => 'nullable|array',
            'allergen_tags.*' => 'string',
        ];
    }
}

