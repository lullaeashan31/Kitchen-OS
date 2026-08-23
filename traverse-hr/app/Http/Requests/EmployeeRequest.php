<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('employee.manage');
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'dob' => ['nullable', 'date', 'before:today'],
            'date_of_joining' => ['required', 'date'],
            'job_role_id' => ['required', 'exists:job_roles,id'],
            'designation' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'outlet_id' => ['required', 'exists:outlets,id'],
            'reporting_manager_id' => ['nullable', 'exists:employees,id'],
            'employment_type' => ['required', Rule::in(['full_time', 'part_time', 'trainee', 'contract'])],
            'probation_end_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'on_notice', 'exited'])],
            'aadhaar_last_four' => ['nullable', 'digits:4'],
            // Statutory identifiers are optional at creation, editable only by employee.manage holders.
            'pan' => ['nullable', 'string', 'max:20'],
            'uan' => ['nullable', 'string', 'max:20'],
            'esic_number' => ['nullable', 'string', 'max:20'],
            'bank_account' => ['nullable', 'string', 'max:34'],
            'ifsc' => ['nullable', 'string', 'max:11'],
        ];
    }
}
