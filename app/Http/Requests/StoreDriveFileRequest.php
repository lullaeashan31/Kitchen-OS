<?php

namespace App\Http\Requests;

use App\Models\DriveFile;
use App\Enums\LinkedType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDriveFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'file' => 'nullable|file|max:10240', // 10MB Max
            'drive_url' => 'nullable|url',
            'linked_type' => ['required', Rule::enum(LinkedType::class)],
            'linked_id' => 'required|integer',
        ];
    }
}
