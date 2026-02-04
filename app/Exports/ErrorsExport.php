<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ErrorsExport implements FromArray, WithHeadings
{
    protected $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    public function array(): array
    {
        return array_map(function ($err) {
            return [
                $err['recipe'],
                $err['error'],
                json_encode($err['row_data'] ?? []),
            ];
        }, $this->errors);
    }

    public function headings(): array
    {
        return [
            'Recipe Name',
            'Error Message',
            'Original Data',
        ];
    }
}
