<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'name' => 'required|string|max:255|unique:ingredients,name',
            'category_id' => 'required|exists:categories,id',
            'storage_location' => 'required|string|in:Fridge,Freezer,Dry Store,Bar,Cellar',
            'measurement_unit' => 'required|string',
        ];
    }
}
