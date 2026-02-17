<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayrollRecord;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        $payrollRecords = PayrollRecord::with('user')
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        return view('admin.payroll.index', compact('payrollRecords', 'month', 'year'));
    }

    public function generate(Request $request, PayrollService $payrollService)
    {
        $request->validate([
            'month' => 'required|integer',
            'year' => 'required|integer',
        ]);

        $results = $payrollService->generatePayroll($request->month, $request->year);

        if (count($results['errors']) > 0) {
            return redirect()->back()->with('warning', "Generated {$results['total_processed']} records. " . count($results['errors']) . " errors occurred.");
        }

        return redirect()->back()->with('success', "Payroll generated successfully for {$results['total_processed']} staff members.");
    }

    public function markAsPaid(string $id)
    {
        $record = PayrollRecord::findOrFail($id);
        $record->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Payroll marked as paid.');
    }

    public function export(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        $filename = "payroll_export_{$month}_{$year}.xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PayrollExport($month, $year),
            $filename
        );
    }

    public function myPayslips()
    {
        $payrollRecords = PayrollRecord::where('user_id', auth()->id())
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('employee.payroll.index', compact('payrollRecords'));
    }

    public function downloadPayslip(string $id)
    {
        $record = PayrollRecord::with('user')->findOrFail($id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.payslip', compact('record'));

        $filename = "payslip_{$record->user->staff_code}_{$record->month}_{$record->year}.pdf";
        return $pdf->download($filename);
    }
}
