<?php

namespace App\Exports;

use App\Models\PayrollRecord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PayrollExport implements FromCollection, WithHeadings, WithMapping
{
    protected $month;
    protected $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        return PayrollRecord::with(['user', 'user.employeeProfile'])
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Staff Name',
            'Staff Code',
            'Account Holder Name',
            'Bank Name',
            'Account Number',
            'IFSC Code',
            'Monthly Salary',
            'Bonus',
            'Deductions',
            'Net Payable',
            'Status'
        ];
    }

    public function map($record): array
    {
        $profile = $record->user->employeeProfile;

        return [
            $record->user->name,
            $record->user->staff_code,
            $record->user->name, // Assuming same as user name for now
            $profile->bank_name ?? 'N/A',
            $profile->account_number ?? 'N/A',
            $profile->ifsc_code ?? 'N/A',
            $record->base_salary,
            $record->bonus,
            $record->deductions,
            $record->net_salary,
            ucfirst($record->status)
        ];
    }
}
