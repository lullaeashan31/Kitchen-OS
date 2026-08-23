<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OutletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('admin.settings.manage');
    }

    public function rules(): array
    {
        $outletId = $this->route('outlet')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', Rule::unique('outlets', 'code')->ignore($outletId)],
            'address' => ['nullable', 'string'],
            'timezone' => ['required', 'string', 'max:64'],
            'payroll_divisor_setting' => ['required', Rule::in(['calendar', 'fixed_26', 'fixed_30'])],
            'active' => ['boolean'],
        ];
    }
}
