<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bank Statement Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #333;
            margin: 0;
            padding: 8px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header h2 {
            margin: 0 0 3px 0;
            color: #1e3a8a;
            font-size: 15px;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 9px;
            color: #64748b;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 9px;
        }
        .meta-table td {
            padding: 2px 0;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
        }
        .text-end {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .fw-bold {
            font-weight: bold;
        }
        .text-success {
            color: #166534;
        }
        .text-danger {
            color: #991b1b;
        }
        .footer {
            margin-top: 15px;
            border-top: 1px solid #cbd5e1;
            padding-top: 5px;
            font-size: 8px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Bank Statement & Activity Report (Dual Currency: PKR & SAR)</h2>
        <p>Comprehensive Audit Log of Bank Deposits, Withdrawals & Transfers</p>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <strong>Generated On:</strong> {{ date('d M Y, h:i A') }}<br>
                <strong>Bank Account:</strong> {{ $selectedBank ? $selectedBank->name : 'All Bank Accounts (Consolidated)' }}
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>Date Range:</strong> {{ $fromDate ? date('d M Y', strtotime($fromDate)) : 'Start' }} to {{ $toDate ? date('d M Y', strtotime($toDate)) : 'Present' }}<br>
                <strong>PKR:</strong> Cr {{ number_format($totalDepositsPkr, 2) }} | Dr {{ number_format($totalWithdrawalsPkr, 2) }} | Net {{ number_format($netMovementPkr, 2) }}<br>
                <strong>SAR:</strong> Cr {{ number_format($totalDepositsSar, 2) }} | Dr {{ number_format($totalWithdrawalsSar, 2) }} | Net {{ number_format($netMovementSar, 2) }}
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;" class="text-center">#</th>
                <th style="width: 9%;">Date</th>
                <th style="width: 12%;">Voucher #</th>
                <th style="width: 13%;">Bank</th>
                <th style="width: 17%;">Party / Payee</th>
                <th style="width: 7%;">Mode</th>
                <th style="width: 9%;" class="text-end">Cr (PKR)</th>
                <th style="width: 9%;" class="text-end">Dr (PKR)</th>
                <th style="width: 7%;" class="text-end">Cr (SAR)</th>
                <th style="width: 7%;" class="text-end">Dr (SAR)</th>
                <th style="width: 7%;" class="text-end">Bal (PKR)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($t['date'])->format('d M Y') }}</td>
                    <td>{{ $t['voucher_no'] }}</td>
                    <td><strong>{{ $t['bank_name'] }}</strong></td>
                    <td>{{ $t['party_name'] }}</td>
                    <td>{{ $t['payment_mode'] }}</td>
                    <td class="text-end text-success fw-bold">
                        {{ $t['deposit_pkr'] > 0 ? number_format($t['deposit_pkr'], 2) : '-' }}
                    </td>
                    <td class="text-end text-danger fw-bold">
                        {{ $t['withdrawal_pkr'] > 0 ? number_format($t['withdrawal_pkr'], 2) : '-' }}
                    </td>
                    <td class="text-end text-success fw-bold">
                        {{ $t['deposit_sar'] > 0 ? number_format($t['deposit_sar'], 2) : '-' }}
                    </td>
                    <td class="text-end text-danger fw-bold">
                        {{ $t['withdrawal_sar'] > 0 ? number_format($t['withdrawal_sar'], 2) : '-' }}
                    </td>
                    <td class="text-end fw-bold">
                        {{ number_format($t['running_balance_pkr'], 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 15px;">No bank transactions recorded for this period.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($transactions) > 0)
            <tfoot>
                <tr style="background-color: #f8fafc;">
                    <td colspan="6" class="text-end fw-bold">TOTALS:</td>
                    <td class="text-end text-success fw-bold">{{ number_format($totalDepositsPkr, 2) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($totalWithdrawalsPkr, 2) }}</td>
                    <td class="text-end text-success fw-bold">{{ number_format($totalDepositsSar, 2) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($totalWithdrawalsSar, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($netMovementPkr, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        Dua Travels & Account Management System &bull; Confidential Financial Statement &bull; Page 1 of 1
    </div>

</body>
</html>
