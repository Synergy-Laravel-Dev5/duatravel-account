<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chart of Accounts</title>
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
            padding: 5px 8px;
            font-weight: bold;
            font-size: 10.5px;
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
            padding: 5px 6px;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        table.coa-table td {
            border: 1px solid #e2e8f0;
            padding: 4px 6px;
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
    </style>
</head>
<body>

    <div class="header">
        <div class="company-name">DUA TRAVELS & TOURS (PVT) LTD</div>
        <div class="report-title">CHART OF ACCOUNTS</div>
        <div class="report-meta">
            General Ledger Master Head Listing & Directory &nbsp;|&nbsp;
            Total Accounts: <strong>{{ $totalAccountsCount }}</strong>
        </div>
    </div>

    @php
        $sections = [
            'assets' => [
                'title'     => '1000 • ASSETS',
                'items'     => $chart['assets'],
            ],
            'liabilities' => [
                'title'     => '2000 • LIABILITIES',
                'items'     => $chart['liabilities'],
            ],
            'equity' => [
                'title'     => '3000 • EQUITY & CAPITAL',
                'items'     => $chart['equity'],
            ],
            'revenue' => [
                'title'     => '4000 • REVENUE & OPERATING INCOME',
                'items'     => $chart['revenue'],
            ],
            'cogs' => [
                'title'     => '5000 • COST OF SALES (DIRECT COSTS)',
                'items'     => $chart['cogs'],
            ],
            'expenses' => [
                'title'     => '6000 • OPERATING EXPENSES',
                'items'     => $chart['expenses'],
            ],
        ];
    @endphp

    @foreach($sections as $secKey => $sec)
        <div class="section-header">
            {{ $sec['title'] }}
            <span style="float: right; font-weight: normal; color: #475569; font-size: 9px;">
                {{ count($sec['items']) }} Accounts
            </span>
        </div>

        <table class="coa-table">
            <thead>
                <tr>
                    <th style="width: 16%;" class="text-center">Code</th>
                    <th style="width: 44%;">Account Name / Head</th>
                    <th style="width: 20%;">Classification</th>
                    <th style="width: 20%;">Sub-Type / Group</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sec['items'] as $item)
                    <tr>
                        <td class="text-center code-box">{{ $item['code'] }}</td>
                        <td class="fw-bold">{{ $item['name'] }}</td>
                        <td>{{ $item['classification'] }}</td>
                        <td>{{ $item['sub_type'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center" style="color:#64748b;">No accounts listed under this classification.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

</body>
</html>
