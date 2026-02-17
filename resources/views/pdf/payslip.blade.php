<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
        }

        .section {
            margin-top: 30px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 8px 0;
        }

        .label {
            color: #666;
            font-size: 13px;
        }

        .value {
            text-align: right;
            font-weight: bold;
        }

        .total-row {
            border-top: 2px solid #3b82f6;
            padding-top: 10px;
            margin-top: 20px;
        }

        .total-label {
            font-size: 18px;
            font-weight: bold;
        }

        .total-value {
            font-size: 22px;
            font-weight: 900;
            text-align: right;
            color: #3b82f6;
        }

        .footer {
            margin-top: 50px;
            font-size: 10px;
            text-align: center;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">Kitchen OS - Payout Slip</div>
        <div class="subtitle">Monthly Payroll: {{ date('F Y', mktime(0, 0, 0, $record->month, 1, $record->year)) }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">Employee Details</div>
        <table>
            <tr>
                <td class="label">Name:</td>
                <td class="value">{{ $record->user->name }}</td>
            </tr>
            <tr>
                <td class="label">Staff ID:</td>
                <td class="value">#{{ $record->user->staff_code }}</td>
            </tr>
            <tr>
                <td class="label">Designation:</td>
                <td class="value">{{ $record->user->role->label() }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Earnings Breakdown</div>
        <table>
            <tr>
                <td class="label">Base Salary (Attendance Based):</td>
                <td class="value">₹{{ number_format($record->base_salary, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Performance Bonus:</td>
                <td class="value">+ ₹{{ number_format($record->bonus, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Deductions</div>
        <table>
            <tr>
                <td class="label">Other Deductions:</td>
                <td class="value">- ₹{{ number_format($record->deductions, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="total-row">
        <table>
            <tr>
                <td class="total-label">Net Payable Amount:</td>
                <td class="total-value">₹{{ number_format($record->net_salary, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        This is a computer-generated document and does not require a physical signature.
        <br>Kitchen Management System | {{ date('d-M-Y H:i') }}
    </div>
</body>

</html>