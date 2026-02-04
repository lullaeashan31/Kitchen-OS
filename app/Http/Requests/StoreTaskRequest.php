<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'production_day_id' => 'required|exists:production_days,id',
            'title' => 'required|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
        ];
    }
}
