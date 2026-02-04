<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIngredientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('ingredient') ? $this->route('ingredient')->id : null;

        return [
            'name' => 'required|string|max:255|unique:ingredients,name,' . $id,
            'allergen_tags' => 'nullable|array',
            'allergen_tags.*' => 'string',
            'category_id' => 'required|exists:categories,id',
            'measurement_unit' => 'required|string|max:50',
            'purchase_unit' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'vendor' => 'nullable|string|max:255',
            'alert_threshold' => 'nullable|numeric|min:0',
            'storage_location' => 'nullable|string|in:Fridge,Freezer,Dry Store,Bar,Other',
            'status' => 'nullable|string|in:pending,approved,rejected',
        ];
    }
}
