<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('admin.settings.manage');
    }

    public function rules(): array
    {
        return [
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'default_designation' => ['nullable', 'string', 'max:255'],
            'default_salary_band_min' => ['nullable', 'integer', 'min:0'],
            'default_salary_band_max' => ['nullable', 'integer', 'gte:default_salary_band_min'],
            'probation_months' => ['required', 'integer', 'min:0', 'max:24'],
            'notice_period_days' => ['required', 'integer', 'min:0', 'max:180'],
            'sort_order' => ['nullable', 'integer'],
            'active' => ['boolean'],
        ];
    }
}
