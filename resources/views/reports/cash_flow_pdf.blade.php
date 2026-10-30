<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cash Flow Statement - {{ $dateTo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 20px;
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
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f535e;
            margin: 3px 0;
        }
        .report-date {
            font-size: 10px;
            color: #64748b;
        }
        table.cf-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #334155;
        }
        table.cf-table th, table.cf-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
        }
        .section-header {
            font-weight: bold;
            background-color: #f1f5f9;
            font-size: 12px;
        }
        .sub-header {
            font-style: italic;
            font-weight: bold;
            color: #475569;
            padding-left: 20px !important;
        }
        .item-text {
            padding-left: 30px !important;
            color: #334155;
        }
        .text-right {
            text-align: right;
            font-weight: 500;
        }
        .subtotal-row {
            font-weight: bold;
            background-color: #f8fafc;
            border-top: 1.5px solid #64748b !important;
        }
        .grand-total {
            font-weight: bold;
            font-size: 13px;
            background-color: #e6f3f5;
            border-top: 2px solid #0f535e !important;
            border-bottom: 3px double #0f535e !important;
            color: #0f535e;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="company-name">DUA TRAVELS & TOURS (PVT) LTD</div>
        <div class="report-title">Cash Flow Statement</div>
        <div class="report-date">
            Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }} | All Figures in PKR
        </div>
    </div>

    <table class="cf-table">
        <tbody>
            {{-- Operations --}}
            <tr class="section-header">
                <td>Cash Flow From Operations</td>
                <td style="width: 140px;"></td>
            </tr>
            <tr>
                <td class="item-text">Net Earnings / Net Profit</td>
                <td class="text-right">{{ $netEarnings >= 0 ? number_format($netEarnings, 2) : '(' . number_format(abs($netEarnings), 2) . ')' }}</td>
            </tr>
            <tr>
                <td class="sub-header">Additions to Cash</td>
                <td></td>
            </tr>
            <tr>
                <td class="item-text">Depreciations</td>
                <td class="text-right">{{ number_format($depreciation, 2) }}</td>
            </tr>
            @if($decreaseInReceivables > 0)
            <tr>
                <td class="item-text">Decrease in Accounts Receivable</td>
                <td class="text-right">{{ number_format($decreaseInReceivables, 2) }}</td>
            </tr>
            @endif
            @if($increaseInPayables > 0)
            <tr>
                <td class="item-text">Increase in Accounts Payable</td>
                <td class="text-right">{{ number_format($increaseInPayables, 2) }}</td>
            </tr>
            @endif
            @if(($increaseInTaxes ?? 0) > 0)
            <tr>
                <td class="item-text">Increase in Taxes / Accrued Payables</td>
                <td class="text-right">{{ number_format($increaseInTaxes, 2) }}</td>
            </tr>
            @endif
            @if(($decreaseInAdvances ?? 0) > 0)
            <tr>
                <td class="item-text">Decrease in Supplier Advances / Prepaids</td>
                <td class="text-right">{{ number_format($decreaseInAdvances, 2) }}</td>
            </tr>
            @endif
            @if($depreciation == 0 && $decreaseInReceivables == 0 && $increaseInPayables == 0 && ($increaseInTaxes ?? 0) == 0 && ($decreaseInAdvances ?? 0) == 0)
            <tr>
                <td class="item-text">Other Non-cash / Working Capital Additions</td>
                <td class="text-right">0.00</td>
            </tr>
            @endif
            <tr>
                <td class="sub-header">Subtractions From Cash</td>
                <td></td>
            </tr>
            @if($increaseInReceivables > 0)
            <tr>
                <td class="item-text">Increase in Accounts Receivable (Uncollected Revenue)</td>
                <td class="text-right" style="color:#b91c1c;">({{ number_format($increaseInReceivables, 2) }})</td>
            </tr>
            @endif
            @if($decreaseInPayables > 0)
            <tr>
                <td class="item-text">Decrease in Accounts Payable (Supplier Dues Paid Down)</td>
                <td class="text-right" style="color:#b91c1c;">({{ number_format($decreaseInPayables, 2) }})</td>
            </tr>
            @endif
            @if(($increaseInAdvances ?? 0) > 0)
            <tr>
                <td class="item-text">Increase in Supplier Advances / Prepaids</td>
                <td class="text-right" style="color:#b91c1c;">({{ number_format($increaseInAdvances, 2) }})</td>
            </tr>
            @endif
            @if($increaseInReceivables == 0 && $decreaseInPayables == 0 && ($increaseInAdvances ?? 0) == 0)
            <tr>
                <td class="item-text">Other Working Capital Subtractions</td>
                <td class="text-right">0.00</td>
            </tr>
            @endif
            <tr class="subtotal-row">
                <td style="text-decoration: underline;">Net Cash From Operations</td>
                <td class="text-right">{{ $netCashFromOperations >= 0 ? number_format($netCashFromOperations, 2) : '(' . number_format(abs($netCashFromOperations), 2) . ')' }}</td>
            </tr>

            {{-- Investing --}}
            <tr class="section-header">
                <td>Cash Flow From Investing</td>
                <td></td>
            </tr>
            <tr>
                <td class="item-text">Office Equipment & Assets Purchased</td>
                <td class="text-right">{{ $equipmentPurchased > 0 ? '(' . number_format($equipmentPurchased, 2) . ')' : '0.00' }}</td>
            </tr>
            <tr class="subtotal-row">
                <td style="text-decoration: underline;">Net Cash From Investing</td>
                <td class="text-right">{{ $netCashFromInvesting >= 0 ? number_format($netCashFromInvesting, 2) : '(' . number_format(abs($netCashFromInvesting), 2) . ')' }}</td>
            </tr>

            {{-- Financing --}}
            <tr class="section-header">
                <td>Cash Flow From Financing</td>
                <td></td>
            </tr>
            <tr>
                <td class="item-text">Owner Capital Injected / Equity Contributions</td>
                <td class="text-right">{{ number_format($capitalInjected, 2) }}</td>
            </tr>
            <tr>
                <td class="item-text">Notes Payable / Short-term Borrowings Received</td>
                <td class="text-right">{{ number_format($notesPayableBorrowings, 2) }}</td>
            </tr>
            @if($drawingsOrLoanRepayments > 0)
            <tr>
                <td class="item-text">Loan Repayments / Owner Drawings</td>
                <td class="text-right" style="color:#b91c1c;">({{ number_format($drawingsOrLoanRepayments, 2) }})</td>
            </tr>
            @endif
            <tr class="subtotal-row">
                <td style="text-decoration: underline;">Net Cash From Financing</td>
                <td class="text-right">{{ $netCashFromFinancing >= 0 ? number_format($netCashFromFinancing, 2) : '(' . number_format(abs($netCashFromFinancing), 2) . ')' }}</td>
            </tr>

            {{-- Net Cash Flow --}}
            <tr class="grand-total">
                <td>Cash Flow for Period Ended {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</td>
                <td class="text-right">PKR {{ number_format($netCashFlowPeriod, 2) }}</td>
            </tr>

            {{-- Reconciliation --}}
            <tr>
                <td class="item-text">Cash & bank balance at beginning of period</td>
                <td class="text-right">{{ number_format($cashAtBeginning, 2) }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td class="item-text">Cash & bank balance at end of period</td>
                <td class="text-right">{{ number_format($cashAtEnd, 2) }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
