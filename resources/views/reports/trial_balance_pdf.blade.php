<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trial Balance - {{ $asOfDate }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #1e293b;
            margin: 15px;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f535e;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .report-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f535e;
            letter-spacing: 1px;
            margin: 3px 0;
        }
        .report-meta {
            font-size: 9px;
            color: #64748b;
        }
        table.tb-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.tb-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        table.tb-table td {
            border: 1px solid #e2e8f0;
            padding: 4px 6px;
            font-size: 9px;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .code-box {
            font-family: monospace;
            font-weight: bold;
            font-size: 9px;
        }
        .double-underline {
            border-top: 1px solid #0f172a !important;
            border-bottom: 3px double #0f172a !important;
            font-weight: bold;
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 4px;
            font-size: 8px;
            border-radius: 3px;
            border: 0.5px solid #94a3b8;
        }
        .footer-signatures {
            margin-top: 40px;
            width: 100%;
        }
        .footer-signatures td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding-top: 20px;
        }
        .sig-line {
            border-top: 1px solid #334155;
            margin: 0 15px;
            padding-top: 3px;
            font-size: 8.5px;
            color: #475569;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="company-name">DUA TRAVELS & TOURS (PVT) LTD</div>
        <div class="report-title">TRIAL BALANCE WORKSHEET</div>
        <div class="report-meta">
            As at <strong>{{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}</strong> &nbsp;|&nbsp;
            Operations: <strong>{{ ucfirst($packageType) }}</strong> &nbsp;|&nbsp;
            Exchange Rate: <strong>1 SAR = PKR {{ number_format($activeRoe, 2) }}</strong> &nbsp;|&nbsp;
            Status: <strong>{{ $isBalanced ? 'PERFECTLY BALANCED (Equilibrium Verified)' : 'DISCREPANCY DETECTED' }}</strong>
        </div>
    </div>

    <table class="tb-table">
        <thead>
            <tr>
                <th style="width: 12%;" class="text-center">Code</th>
                <th style="width: 36%;">Account Title</th>
                <th style="width: 12%;" class="text-center">Category</th>
                <th style="width: 10%;" class="text-end">Debit (PKR)</th>
                <th style="width: 10%;" class="text-end">Credit (PKR)</th>
                <th style="width: 10%;" class="text-end">Debit (SAR)</th>
                <th style="width: 10%;" class="text-end">Credit (SAR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accounts as $acc)
                <tr>
                    <td class="text-center code-box">{{ $acc['code'] }}</td>
                    <td>{{ $acc['name'] }}</td>
                    <td class="text-center">
                        <span class="badge">{{ $acc['category'] }}</span>
                    </td>
                    <td class="text-end">{{ $acc['debit'] > 0 ? number_format($acc['debit'], 2) : '-' }}</td>
                    <td class="text-end">{{ $acc['credit'] > 0 ? number_format($acc['credit'], 2) : '-' }}</td>
                    <td class="text-end">{{ $acc['debit_sar'] > 0 ? number_format($acc['debit_sar'], 2) : '-' }}</td>
                    <td class="text-end">{{ $acc['credit_sar'] > 0 ? number_format($acc['credit_sar'], 2) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="double-underline">
                <td colspan="3" class="text-end fw-bold" style="padding-right: 10px;">TOTAL GENERAL LEDGER EQUILIBRIUM:</td>
                <td class="text-end fw-bold">{{ number_format($totalDebit, 2) }}</td>
                <td class="text-end fw-bold">{{ number_format($totalCredit, 2) }}</td>
                <td class="text-end fw-bold">{{ number_format($totalDebit / $activeRoe, 2) }}</td>
                <td class="text-end fw-bold">{{ number_format($totalCredit / $activeRoe, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="footer-signatures">
        <tr>
            <td>
                <div class="sig-line">Prepared By (Accountant)</div>
            </td>
            <td>
                <div class="sig-line">Verified By (Internal Audit)</div>
            </td>
            <td>
                <div class="sig-line">Authorized Signatory</div>
            </td>
        </tr>
    </table>

</body>
</html>
