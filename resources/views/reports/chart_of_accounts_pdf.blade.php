<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chart of Accounts - {{ $asOfDate }}</title>
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
        .section-header {
            background-color: #f1f5f9;
            border-left: 4px solid #0f535e;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 10px;
            margin-top: 12px;
            margin-bottom: 4px;
        }
        table.coa-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.coa-table th {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        table.coa-table td {
            border: 1px solid #e2e8f0;
            padding: 3.5px 6px;
            font-size: 8.5px;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .code-box {
            font-family: monospace;
            font-weight: bold;
            font-size: 8.5px;
        }
        .subtotal-row {
            background-color: #fafbfc;
            border-top: 1px solid #334155 !important;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="company-name">DUA TRAVELS & TOURS (PVT) LTD</div>
        <div class="report-title">CHART OF ACCOUNTS</div>
        <div class="report-meta">
            As at <strong>{{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}</strong> &nbsp;|&nbsp;
            Exchange Rate: <strong>1 SAR = PKR {{ number_format($activeRoe, 2) }}</strong> &nbsp;|&nbsp;
            Total General Ledger Accounts: <strong>{{ $totalAccountsCount }}</strong>
        </div>
    </div>

    @php
        $sections = [
            'assets' => [
                'title'     => '1000 • ASSETS',
                'items'     => $chart['assets'],
                'total_pkr' => $totals['assets_pkr'],
                'total_sar' => $totals['assets_sar'],
            ],
            'liabilities' => [
                'title'     => '2000 • LIABILITIES',
                'items'     => $chart['liabilities'],
                'total_pkr' => $totals['liabilities_pkr'],
                'total_sar' => $totals['liabilities_sar'],
            ],
            'equity' => [
                'title'     => '3000 • EQUITY & CAPITAL',
                'items'     => $chart['equity'],
                'total_pkr' => $totals['equity_pkr'],
                'total_sar' => $totals['equity_sar'],
            ],
            'revenue' => [
                'title'     => '4000 • REVENUE & OPERATING INCOME',
                'items'     => $chart['revenue'],
                'total_pkr' => $totals['revenue_pkr'],
                'total_sar' => $totals['revenue_sar'],
            ],
            'cogs' => [
                'title'     => '5000 • COST OF SALES (DIRECT COSTS)',
                'items'     => $chart['cogs'],
                'total_pkr' => $totals['cogs_pkr'],
                'total_sar' => $totals['cogs_sar'],
            ],
            'expenses' => [
                'title'     => '6000 • OPERATING EXPENSES',
                'items'     => $chart['expenses'],
                'total_pkr' => $totals['expenses_pkr'],
                'total_sar' => $totals['expenses_sar'],
            ],
        ];
    @endphp

    @foreach($sections as $secKey => $sec)
        <div class="section-header">
            {{ $sec['title'] }}
            <span style="float: right; font-weight: normal; color: #475569;">
                Subtotal: PKR {{ number_format($sec['total_pkr'], 2) }} (SAR {{ number_format($sec['total_sar'], 2) }})
            </span>
        </div>

        <table class="coa-table">
            <thead>
                <tr>
                    <th style="width: 14%;" class="text-center">Code</th>
                    <th style="width: 44%;">Account Title</th>
                    <th style="width: 22%;">Classification</th>
                    <th style="width: 10%;" class="text-end">Balance (PKR)</th>
                    <th style="width: 10%;" class="text-end">Balance (SAR)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sec['items'] as $item)
                    <tr>
                        <td class="text-center code-box">{{ $item['code'] }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['sub_type'] }}</td>
                        <td class="text-end fw-bold">{{ number_format($item['balance_pkr'], 2) }}</td>
                        <td class="text-end">{{ number_format($item['balance_sar'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td colspan="3" class="text-end fw-bold">Subtotal {{ $sec['title'] }}:</td>
                    <td class="text-end fw-bold">{{ number_format($sec['total_pkr'], 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($sec['total_sar'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

</body>
</html>
