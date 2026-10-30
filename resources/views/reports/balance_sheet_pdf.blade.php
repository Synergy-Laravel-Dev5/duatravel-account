<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Balance Sheet - {{ $asOfDate }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 15px;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f535e;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f535e;
            letter-spacing: 1px;
            margin: 3px 0;
        }
        .report-date {
            font-size: 10px;
            color: #64748b;
        }
        table.bs-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #334155;
        }
        table.bs-table th {
            background-color: #f1f5f9;
            border-bottom: 1.5px solid #334155;
            padding: 6px 8px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        table.bs-table td {
            vertical-align: top;
            padding: 8px;
            width: 50%;
        }
        .section-header {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 4px;
            font-size: 11px;
            color: #0f172a;
        }
        .item-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
        }
        .item-label {
            display: table-cell;
            text-align: left;
            padding-left: 10px;
            color: #334155;
        }
        .item-val {
            display: table-cell;
            text-align: right;
            font-weight: 500;
        }
        .subtotal-row {
            display: table;
            width: 100%;
            border-top: 1px solid #94a3b8;
            margin-top: 4px;
            padding-top: 3px;
            font-weight: bold;
        }
        .total-box {
            border-top: 1.5px solid #0f172a;
            border-bottom: 3px double #0f535e;
            padding: 6px 0;
            margin-top: 15px;
            display: table;
            width: 100%;
            font-weight: bold;
            font-size: 12px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="company-name">DUA TRAVELS & TOURS (PVT) LTD</div>
        <div class="report-title">BALANCE SHEET</div>
        <div class="report-date">
            As at {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }} | All figures in PKR
        </div>
    </div>

    <table class="bs-table">
        <thead>
            <tr>
                <th style="border-right: 1.5px solid #334155;">Assets (PKR)</th>
                <th>Liabilities & Equity (PKR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                {{-- Left: Assets --}}
                <td style="border-right: 1.5px solid #334155;">

                    {{-- Current Assets --}}
                    <div class="section-header">Current assets:</div>
                    <div class="item-row">
                        <span class="item-label">Cash and bank balances</span>
                        <span class="item-val">{{ number_format($totalCashAndBankPkr, 2) }}</span>
                    </div>
                    <div class="item-row">
                        <span class="item-label">Accounts receivable (Clients)</span>
                        <span class="item-val">{{ number_format($totalAccountsReceivablePkr, 2) }}</span>
                    </div>
                    <div class="item-row">
                        <span class="item-label">Prepaid expenses & supplier advances</span>
                        <span class="item-val">{{ number_format($totalPrepaidsPkr, 2) }}</span>
                    </div>
                    <div class="subtotal-row">
                        <span class="item-label" style="padding-left:0;">Total current assets</span>
                        <span class="item-val">{{ number_format($totalCurrentAssets, 2) }}</span>
                    </div>

                    {{-- Non-Current Assets --}}
                    <div class="section-header" style="margin-top: 15px;">Non-current assets:</div>
                    <div class="item-row">
                        <span class="item-label">Equipment & furniture</span>
                        <span class="item-val">{{ number_format($fixedAssetsBase, 2) }}</span>
                    </div>
                    <div class="item-row">
                        <span class="item-label">Less: Acc. depreciation</span>
                        <span class="item-val">({{ number_format($accumulatedDepreciation, 2) }})</span>
                    </div>
                    <div class="subtotal-row">
                        <span class="item-label" style="padding-left:0;">Net fixed assets</span>
                        <span class="item-val">{{ number_format($netFixedAssets, 2) }}</span>
                    </div>

                    <div style="height: 30px;"></div>

                    {{-- Total Assets --}}
                    <div class="total-box">
                        <span class="item-label" style="padding-left:0; color:#0f535e;">TOTAL ASSETS</span>
                        <span class="item-val" style="color:#0f535e;">{{ number_format($totalAssets, 2) }}</span>
                    </div>

                </td>

                {{-- Right: Liabilities & Equity --}}
                <td>

                    {{-- Liabilities --}}
                    <div class="section-header">Liabilities:</div>
                    <div class="item-row">
                        <span class="item-label">Notes payable / Borrowings</span>
                        <span class="item-val">{{ number_format($totalNotesPayablePkr, 2) }}</span>
                    </div>
                    <div class="item-row">
                        <span class="item-label">Accounts payable (Suppliers)</span>
                        <span class="item-val">{{ number_format($totalAccountsPayablePkr, 2) }}</span>
                    </div>
                    <div class="item-row">
                        <span class="item-label">Salaries & staff payable</span>
                        <span class="item-val">{{ number_format($totalSalariesPayablePkr, 2) }}</span>
                    </div>
                    <div class="item-row">
                        <span class="item-label">Unearned service revenue</span>
                        <span class="item-val">{{ number_format($unearnedServiceRevenue, 2) }}</span>
                    </div>
                    <div class="subtotal-row">
                        <span class="item-label" style="padding-left:0;">Total liabilities</span>
                        <span class="item-val">{{ number_format($totalLiabilities, 2) }}</span>
                    </div>

                    {{-- Stockholders' Equity --}}
                    <div class="section-header" style="margin-top: 15px;">Stockholders' equity:</div>
                    <div class="item-row">
                        <span class="item-label">Capital stock</span>
                        <span class="item-val">{{ number_format($capitalStock, 2) }}</span>
                    </div>
                    <div class="item-row">
                        <span class="item-label">Retained earnings</span>
                        <span class="item-val">{{ number_format($retainedEarnings, 2) }}</span>
                    </div>
                    @if(abs($balancingAdjustment) > 0.01)
                    <div class="item-row">
                        <span class="item-label">Balancing adjustment</span>
                        <span class="item-val">{{ number_format($balancingAdjustment, 2) }}</span>
                    </div>
                    @endif
                    <div class="subtotal-row">
                        <span class="item-label" style="padding-left:0;">Total stockholders' equity</span>
                        <span class="item-val">{{ number_format($totalEquity, 2) }}</span>
                    </div>

                    <div style="height: 30px;"></div>

                    {{-- Total Liabilities & Equity --}}
                    <div class="total-box">
                        <span class="item-label" style="padding-left:0; color:#0f535e;">TOTAL LIABILITIES & EQUITY</span>
                        <span class="item-val" style="color:#0f535e;">{{ number_format($totalLiabilitiesAndEquity, 2) }}</span>
                    </div>

                </td>
            </tr>
        </tbody>
    </table>

</body>
</html>
