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
            'drive_url' => [
                'required',
                'url',
                function ($attribute, $value, $fail) {
                    if (!DriveFile::validateDriveUrl($value)) {
                        $fail('The ' . $attribute . ' must be a valid Google Drive URL.');
                    }
                    if (!DriveFile::extractFileId($value)) {
                        $fail('Could not extract file ID from the provided Google Drive URL.');
                    }
                },
            ],
            'linked_type' => ['required', Rule::enum(LinkedType::class)],
            'linked_id' => 'required|integer',
        ];
    }
}
