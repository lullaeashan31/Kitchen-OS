@extends('layouts.app')

@section('header')
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">My Payslips</h1>
        <p class="text-gray-500 mt-1">Download and view your monthly salary disbursement records.</p>
    </div>
@endsection

@section('content')
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="p-6 font-semibold">Month / Year</th>
                    <th class="p-6 font-semibold">Base Salary</th>
                    <th class="p-6 font-semibold">Performance Bonus</th>
                    <th class="p-6 font-semibold">Net Payout</th>
                    <th class="p-6 font-semibold">Status</th>
                    <th class="p-6 font-semibold text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($payrollRecords as $record)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="p-6">
                            <div class="font-bold text-gray-900">{{ date('F', mktime(0, 0, 0, $record->month, 1)) }} {{ $record->year }}</div>
                        </td>
                        <td class="p-6 font-medium text-gray-700">₹{{ number_format($record->base_salary, 2) }}</td>
                        <td class="p-6">
                            <span class="text-green-600 font-bold">+ ₹{{ number_format($record->bonus, 2) }}</span>
                        </td>
                        <td class="p-6 font-black text-gray-900">₹{{ number_format($record->net_salary, 2) }}</td>
                        <td class="p-6">
                            @if($record->status === 'paid')
                                <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase flex items-center gap-1 w-fit">
                                    <i data-lucide="check" class="w-3 h-3"></i> Paid
                                </span>
                            @else
                                <span class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase flex items-center gap-1 w-fit">
                                    <i data-lucide="clock" class="w-3 h-3"></i> Processing
                                </span>
                            @endif
                        </td>
                        <td class="p-6 text-right">
                            <a href="{{ route('admin.payroll.download', $record->id) }}" class="inline-flex items-center gap-2 bg-gray-900 hover:bg-black text-white text-xs font-bold py-2 px-4 rounded-xl shadow-lg transition-all">
                                <i data-lucide="download" class="w-4 h-4"></i>
                                Download PDF
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($payrollRecords->isEmpty())
            <div class="p-20 text-center">
                <i data-lucide="banknote" class="w-16 h-16 text-gray-200 mx-auto mb-4"></i>
                <p class="text-gray-500 font-bold">No payslips found yet.</p>
                <p class="text-sm text-gray-400 mt-1">Payslips are generated at the end of each month.</p>
            </div>
        @endif
    </div>
@endsection
