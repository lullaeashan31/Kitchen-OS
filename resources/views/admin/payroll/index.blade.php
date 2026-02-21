@extends('layouts.app')

@section('header')
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Payroll Overview</h1>
            <p class="text-gray-500 mt-1">Showing {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }} — Manage monthly salary disbursements and performance bonuses.</p>
        </div>
        <div class="flex flex-wrap gap-2 items-center bg-white p-3 rounded-2xl shadow-sm border border-gray-100">
            {{-- View month/year: GET form so changing dropdown shows that month's data --}}
            <form action="{{ route('admin.payroll.index') }}" method="GET" class="flex gap-2 items-center" id="payrollViewForm">
                <select name="month" class="bg-transparent font-bold text-gray-700 outline-none px-2 rounded border border-gray-200" onchange="this.form.submit()">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ (int)$month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endforeach
                </select>
                <select name="year" class="bg-transparent font-bold text-gray-700 outline-none px-2 border-l border-gray-100 rounded border border-gray-200" onchange="this.form.submit()">
                    @foreach(range(date('Y')-1, date('Y')+1) as $y)
                        <option value="{{ $y }}" {{ (int)$year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </form>
            <form action="{{ route('admin.payroll.generate') }}" method="POST" class="flex gap-2 items-center">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-xl shadow-lg transition-all text-xs flex items-center gap-2" title="Recalculate base salary from Staff Monthly Salary. Run again after editing staff salary.">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    Generate Payroll
                </button>
            </form>
            <a href="{{ route('admin.payroll.export', ['month' => $month, 'year' => $year]) }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-xl shadow-lg transition-all text-xs flex items-center gap-2">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                Export Excel
            </a>
        </div>
    </div>
@endsection

@section('content')
    @if(session('warning'))
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-8 rounded-xl">
            <p class="text-sm text-amber-700">{{ session('warning') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="p-6 font-semibold">Staff Member</th>
                    <th class="p-6 font-semibold">Base Salary</th>
                    <th class="p-6 font-semibold">Bonus</th>
                    <th class="p-6 font-semibold">Net Salary</th>
                    <th class="p-6 font-semibold">Status</th>
                    <th class="p-6 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @php $totalDisbursement = 0; @endphp
                @foreach($payrollRecords as $record)
                    @php $totalDisbursement += $record->net_salary; @endphp
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="p-6">
                            <div class="font-bold text-gray-900">{{ $record->user->name }}</div>
                            <div class="text-xs text-gray-400">#{{ $record->user->staff_code }}</div>
                        </td>
                        <td class="p-6 font-medium text-gray-700">₹{{ number_format($record->base_salary, 2) }}</td>
                        <td class="p-6">
                            <span class="text-green-600 font-bold">+ ₹{{ number_format($record->bonus, 2) }}</span>
                        </td>
                        <td class="p-6 font-black text-gray-900 text-lg">₹{{ number_format($record->net_salary, 2) }}</td>
                        <td class="p-6">
                            @if($record->status === 'paid')
                                <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase flex items-center gap-1 w-fit">
                                    <i data-lucide="check" class="w-3 h-3"></i> Paid
                                </span>
                            @else
                                <span class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase flex items-center gap-1 w-fit">
                                    <i data-lucide="clock" class="w-3 h-3"></i> Pending
                                </span>
                            @endif
                        </td>
                        <td class="p-6 text-right">
                            <div class="flex justify-end gap-2">
                                @if($record->status === 'pending')
                                    <form action="{{ route('admin.payroll.pay', $record->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-gray-900 hover:bg-black text-white text-[10px] font-bold px-4 py-2 rounded-lg shadow-md transition-all">
                                            Mark Paid
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.payroll.download', $record->id) }}" class="p-2 border border-gray-100 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-all" title="Download Payslip">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            @if($payrollRecords->isNotEmpty())
            <tfoot class="bg-gray-50 border-t border-gray-100">
                <tr>
                    <td class="p-6 font-bold text-gray-500 uppercase text-xs">Total Monthly Disbursement</td>
                    <td colspan="2"></td>
                    <td class="p-6 font-black text-2xl text-blue-600">₹{{ number_format($totalDisbursement, 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
        @if($payrollRecords->isEmpty())
            <div class="p-20 text-center">
                <i data-lucide="banknote" class="w-16 h-16 text-gray-200 mx-auto mb-4"></i>
                <p class="text-gray-500 font-bold">No payroll records for this month yet.</p>
                <p class="text-sm text-gray-400 mt-1">Click "Generate Payroll" to calculate payouts.</p>
            </div>
        @endif
    </div>
@endsection
