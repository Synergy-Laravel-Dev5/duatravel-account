<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bank Transfer Movement Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            color: #1e3a8a;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 11px;
            color: #64748b;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .meta-table td {
            padding: 4px 0;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: bold;
            font-size: 10px;
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
        .from-bank {
            color: #991b1b;
            font-weight: bold;
        }
        .to-bank {
            color: #166534;
            font-weight: bold;
        }
        .footer {
            margin-top: 25px;
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
            font-size: 9px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Bank Transfer Movement Report</h2>
        <p>Internal Inter-Bank Balance Transfer Audit Log</p>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <strong>Generated On:</strong> {{ date('d M Y, h:i A') }}<br>
                <strong>Filtered Bank:</strong> {{ $selectedBank ? $selectedBank->name : ($selectedFromBank ? 'From: ' . $selectedFromBank->name : ($selectedToBank ? 'To: ' . $selectedToBank->name : 'All Banks')) }}
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>Date Range:</strong> {{ $fromDate ? date('d M Y', strtotime($fromDate)) : 'Start' }} to {{ $toDate ? date('d M Y', strtotime($toDate)) : 'Present' }}<br>
                <strong>Total Transfers:</strong> {{ number_format($totalCount) }} &nbsp;|&nbsp; <strong>Total Amount:</strong> PKR {{ number_format($totalAmount, 2) }}
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 15%;">Date</th>
                <th style="width: 25%;">Source (From Bank)</th>
                <th style="width: 25%;">Destination (To Bank)</th>
                <th style="width: 15%;" class="text-end">Amount (PKR)</th>
                <th style="width: 15%;">Reference / Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transfers as $t)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($t->transfer_date)->format('d M Y') }}</td>
                    <td>
                        <span class="from-bank">{{ $t->fromBank->name ?? 'Deleted Bank' }}</span>
                        @if(!empty($t->fromBank->account_number))
                            <br><small style="color:#64748b;">A/C: {{ $t->fromBank->account_number }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="to-bank">{{ $t->toBank->name ?? 'Deleted Bank' }}</span>
                        @if(!empty($t->toBank->account_number))
                            <br><small style="color:#64748b;">A/C: {{ $t->toBank->account_number }}</small>
                        @endif
                    </td>
                    <td class="text-end fw-bold">
                        {{ number_format($t->amount, 2) }}
                    </td>
                    <td>{{ $t->description ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">No bank transfers recorded for this period.</td>
                </tr>
            @endforelse
        </tbody>
        @if($transfers->count() > 0)
            <tfoot>
                <tr style="background-color: #f8fafc;">
                    <td colspan="4" class="text-end fw-bold">Total Transferred:</td>
                    <td class="text-end fw-bold" style="color: #166534; font-size: 12px;">PKR {{ number_format($totalAmount, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        Dua Travels & Account Management System &bull; Confidential Financial Document &bull; Page 1 of 1
    </div>

</body>
</html>
