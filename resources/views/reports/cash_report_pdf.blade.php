<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Book Statement Report</title>
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
            border-bottom: 2px solid #16a34a;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header h2 {
            margin: 0 0 3px 0;
            color: #14532d;
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
        <h2>Cash Book & Daily Cash Ledger Report (Dual Currency: PKR & SAR)</h2>
        <p>Comprehensive Audit Log of Cash Inflows, Disbursements & Cash in Hand</p>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <strong>Generated On:</strong> {{ date('d M Y, h:i A') }}<br>
                <strong>Report Mode:</strong> Cash Book (Dual Currency Ledger)
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>Date Range:</strong> {{ $dateFrom ? date('d M Y', strtotime($dateFrom)) : 'Start' }} to {{ $dateTo ? date('d M Y', strtotime($dateTo)) : 'Present' }}<br>
                <strong>PKR:</strong> In {{ number_format($totalCashInPkr, 2) }} | Out {{ number_format($totalCashOutPkr, 2) }} | Net {{ number_format($netCashBalancePkr, 2) }}<br>
                <strong>SAR:</strong> In {{ number_format($totalCashInSar, 2) }} | Out {{ number_format($totalCashOutSar, 2) }} | Net {{ number_format($netCashBalanceSar, 2) }}
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;" class="text-center">#</th>
                <th style="width: 9%;">Date</th>
                <th style="width: 12%;">Voucher #</th>
                <th style="width: 6%;">Flow</th>
                <th style="width: 14%;">Category</th>
                <th style="width: 16%;">Party / Payee</th>
                <th style="width: 8%;" class="text-end">In (PKR)</th>
                <th style="width: 8%;" class="text-end">Out (PKR)</th>
                <th style="width: 8%;" class="text-end">In (SAR)</th>
                <th style="width: 8%;" class="text-end">Out (SAR)</th>
                <th style="width: 8%;" class="text-end">Bal (PKR)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($t['date'])->format('d M Y') }}</td>
                    <td>{{ $t['voucher_no'] }}</td>
                    <td>
                        <strong class="{{ $t['flow'] === 'inflow' ? 'text-success' : 'text-danger' }}">
                            {{ $t['flow'] === 'inflow' ? 'IN' : 'OUT' }}
                        </strong>
                    </td>
                    <td>{{ $t['category_label'] }}</td>
                    <td>{{ $t['party_name'] }}</td>
                    <td class="text-end text-success fw-bold">
                        {{ $t['cash_in_pkr'] > 0 ? number_format($t['cash_in_pkr'], 2) : '-' }}
                    </td>
                    <td class="text-end text-danger fw-bold">
                        {{ $t['cash_out_pkr'] > 0 ? number_format($t['cash_out_pkr'], 2) : '-' }}
                    </td>
                    <td class="text-end text-success fw-bold">
                        {{ $t['cash_in_sar'] > 0 ? number_format($t['cash_in_sar'], 2) : '-' }}
                    </td>
                    <td class="text-end text-danger fw-bold">
                        {{ $t['cash_out_sar'] > 0 ? number_format($t['cash_out_sar'], 2) : '-' }}
                    </td>
                    <td class="text-end fw-bold">
                        {{ number_format($t['running_balance_pkr'], 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 15px;">No cash transactions recorded for this period.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($transactions) > 0)
            <tfoot>
                <tr style="background-color: #f8fafc;">
                    <td colspan="6" class="text-end fw-bold">TOTALS:</td>
                    <td class="text-end text-success fw-bold">{{ number_format($totalCashInPkr, 2) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($totalCashOutPkr, 2) }}</td>
                    <td class="text-end text-success fw-bold">{{ number_format($totalCashInSar, 2) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($totalCashOutSar, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($netCashBalancePkr, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        Dua Travels & Account Management System &bull; Confidential Financial Statement &bull; Page 1 of 1
    </div>

</body>
</html>
