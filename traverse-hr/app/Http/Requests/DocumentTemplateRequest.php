<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('document.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'kind' => ['required', Rule::in(['acknowledge_only', 'sign_with_fields', 'upload_required'])],
            'category' => ['nullable', 'string', 'max:255'],
            'conditional' => ['boolean'],
            'active' => ['boolean'],
        ];
    }
}
