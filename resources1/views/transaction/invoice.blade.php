<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ str_pad($transaction->id, 4, '0', STR_PAD_LEFT) }}</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">

    <style>
        body {
            background: #f1f1f1;
        }

        .invoice-box {
            background: #fff;
            padding: 18px 28px;
            max-width: 900px;
            margin: 0 auto;
            font-size: 12px;
            color: #1a1a1a;
            font-family: Arial, Helvetica, sans-serif;
        }

        .company-name {
            font-weight: 700;
            font-size: 20px;
            letter-spacing: 0.3px;
        }

        .company-address-ar {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
            margin-top: 4px;
        }

        .thick-line {
            border-top: 3px solid #444;
            border-bottom: 1px solid #444;
            height: 4px;
            margin: 8px 0 10px;
        }

        .thin-line {
            border-top: 1px solid #333;
            margin: 6px 0 8px;
        }

        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .top-left p {
            font-size: 12px;
        }

        .top-right {
            text-align: right;
            line-height: 1.1;
        }

        .stamp-line {
            display: block;
            font-size: 24px;
            font-weight: 800;
            color: #b9bcc2;
            letter-spacing: 0.5px;
        }

        .intro-text {
            margin-bottom: 6px;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .invoice-table th,
        .invoice-table td {
            border: 1px solid #999;
            padding: 4px 5px;
            font-size: 11px;
            text-align: center;
            vertical-align: middle;
        }

        .invoice-table thead th {
            background: #e9e9e9;
            font-weight: 700;
        }

        .invoice-table tfoot td {
            border: 1px solid #999;
            background: #f5f5f5;
            font-size: 11px;
            padding: 4px 5px;
        }

        .charge-label {
            text-align: right;
        }

        .charge-value {
            text-align: center;
        }

        .summary-table {
            width: 60%;
            margin-left: auto;
            margin-top: 4px;
            margin-bottom: 6px;
            font-size: 11px;
        }

        .summary-table td {
            padding: 1px 6px;
        }

        .summary-note {
            font-size: 9.5px;
            color: #777;
            text-align: right;
            margin-top: 2px;
            margin-bottom: 0;
        }

        .section-heading {
            font-weight: 700;
            font-size: 13px;
            margin: 10px 0 6px;
        }

        .total-box-wrapper {
            display: flex;
            justify-content: flex-end;
            margin: 8px 0;
        }

        .total-box {
            width: 300px;
            border-top: 2px double #444;
            border-bottom: 2px double #444;
            padding: 4px 0;
        }

        .total-box td {
            padding: 2px 8px;
            font-size: 13px;
        }

        .underline {
            text-decoration: underline;
        }

        .terms-heading-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            font-size: 12px;
            margin-top: 10px;
        }

        .terms-lines p {
            font-size: 10.5px;
            color: #222;
            margin: 1px 0;
        }

        .bank-table {
            margin-top: 3px;
        }

        .bank-table td {
            padding: 1px 0;
            font-size: 12px;
        }

        .bank-label {
            font-weight: 700;
            padding-right: 30px;
            min-width: 130px;
        }

        .page-footer {
            font-size: 11px;
            color: #555;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #fff;
            }
        }
    </style>
</head>

<body>

    @php
        $partyName = $transaction->client->name ?? ($transaction->company->name ?? '-');
    @endphp

    <div class="container py-4">

        {{-- Action buttons (hidden on print/PDF) --}}
        <div class="d-flex justify-content-end gap-2 mb-3 no-print">
            <a href="{{ route('transaction.index', $transaction->id) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <button onclick="downloadInvoicePDF()" class="btn btn-primary" id="downloadBtn">
                <i class="bi bi-download"></i> Download PDF
            </button>
        </div>

        {{-- INVOICE BOX (this whole div gets converted to PDF) --}}
        <div id="invoiceBox" class="invoice-box">

            {{-- Header --}}
            <div class="invoice-header text-center">
                @php
                    $isInvHajj = ($transaction->booking->package_type ?? session('dashboard_package', 'hajj')) === 'hajj';
                @endphp
                <img src="{{ $isInvHajj ? asset('assets/images/logo/kgm.png') : asset('assets/images/logo/logo.png') }}" height="70" style="object-fit:contain;">
                <p class="company-address-ar mb-0">شركــة إعـمـار الضیافــة الفندقیــة</p>
            </div>

            <div class="thick-line"></div>

            {{-- Date / To  |  Definite Confirmation stamp --}}
            <div class="top-row">
                <div class="top-left">
                    <p class="mb-1"><strong>Date:</strong>
                        {{ \Carbon\Carbon::parse($transaction->payment_date)->format('d/m/Y') }}</p>
                    <p class="mb-0"><strong>To:</strong>
                        {{ $transaction->client ? 'Mr. ' . $partyName : $partyName }}</p>
                </div>
                <div class="top-right">
                    <span class="stamp-line">Definite</span>
                    <span class="stamp-line">Confirmation</span>
                </div>
            </div>

            <div class="thin-line"></div>

            <p class="intro-text">Thank you for showing your interest in Maxims Group HAJJ &amp; UMRAH SERVICES (PVT) LTD </p>

            <p class="mb-1"><strong>Bill. No:</strong> {{ str_pad($transaction->id, 4, '0', STR_PAD_LEFT) }}</p>
            <p class="mb-1"><strong>Guest name:</strong>
                {{ $booking->client->name ?? ($booking->company->name ?? $partyName) }}</p>

            {{-- Hotel / Booking Table --}}
            @if ($hotels && $hotels->count() > 0)
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>RSV #</th>
                            <th>Hotel</th>
                            <th>Conf. #</th>
                            <th>QTY</th>
                            <th>Room Type</th>
                            <th>MP</th>
                            <th>Check In</th>
                            <th>NTS</th>
                            <th>Check Out</th>
                            <th>Room<br>Rate</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hotels as $hotel)
                            <tr>
                                <td>{{ str_pad($transaction->id, 4, '0', STR_PAD_LEFT) }}-{{ $loop->iteration }}</td>
                                <td>{{ $hotel->hotel_name ?? '-' }}</td>
                                <td>{{ $hotel->conf_number ?? '-' }}</td>
                                <td>{{ $hotel->qty ?? 1 }}</td>
                                <td>{{ $hotel->room_type ?? '-' }}</td>
                                <td>{{ $hotel->meal_plan ?? 'R.O' }}</td>
                                <td>{{ $hotel->check_in ? \Carbon\Carbon::parse($hotel->check_in)->format('d/m/Y') : '-' }}
                                </td>
                                <td>{{ $hotel->nights ?? '-' }}</td>
                                <td>{{ $hotel->check_out ? \Carbon\Carbon::parse($hotel->check_out)->format('d/m/Y') : '-' }}
                                </td>
                                <td>{{ number_format($hotel->room_rate ?? 0, 2) }}</td>
                                <td>{{ number_format(($hotel->room_rate ?? 0) * ($hotel->nights ?? 1) * ($hotel->qty ?? 1), 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="10" class="text-end charge-label"><strong>Net Accommodation Charge:</strong>
                            </td>
                            <td class="charge-value">
                                <strong>
                                    {{ number_format($hotels->sum(function ($h) {return ($h->room_rate ?? 0) * ($h->nights ?? 1) * ($h->qty ?? 1);}),2) }}
                                </strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            @endif

            @php
                $absBalance = abs($balance);
                $balanceSign = $balance < 0 ? '+' : '';
                $balanceLabel = $balance > 0 ? 'Balance Due' : ($balance < 0 ? 'Advance Balance' : 'Balance Cleared');
            @endphp

            @if ($booking)
                <table class="summary-table">
                    <tr>
                        <td>Package:</td>
                        <td class="text-end">{{ $booking->package_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Total Booking Amount:</td>
                        <td class="text-end">{{ number_format($totalBooking, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Total Received (as of
                            {{ \Carbon\Carbon::parse($transaction->payment_date)->format('d/m/Y') }}):</td>
                        <td class="text-end">{{ number_format($totalPaid, 2) }}</td>
                    </tr>
                    <tr class="fw-bold border-top">
                        <td>{{ $balanceLabel }}:</td>
                        <td class="text-end">{{ $balanceSign }}{{ number_format($absBalance, 2) }}</td>
                    </tr>
                </table>
                <p class="summary-note">* Balance reflects only payments received up to this transaction's date.</p>
            @endif

            {{-- This Transaction / Payment Record --}}
            <p class="section-heading">Payment Details</p>
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Payment Date</th>
                        <th>Payment Type</th>
                        <th>Reference #</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($transaction->payment_date)->format('d/m/Y') }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $transaction->payment_type)) }}</td>
                        <td>{{ $transaction->reference_number ?? '-' }}</td>
                        <td>{{ number_format($transaction->amount, 2) }}</td>
                        <td>
                            <span
                                class="badge
                            @if ($transaction->status === 'confirmed') bg-success
                            @elseif($transaction->status === 'pending') bg-warning text-dark
                            @else bg-danger @endif">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>

            @if ($transaction->notes)
                <p class="mt-1"><strong>Notes:</strong> {{ $transaction->notes }}</p>
            @endif

            {{-- Total box (boxed, like PDF) --}}
            <div class="total-box-wrapper">
                <table class="total-box">
                    <tr>
                        <td><strong>Total:</strong></td>
                        <td class="text-end">{{ number_format($transaction->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Net Value:</strong></td>
                        <td class="text-end"><strong>{{ number_format($transaction->amount, 2) }}</strong></td>
                    </tr>
                </table>
            </div>

            {{-- Guest name + Terms & Conditions heading row, like PDF --}}
            <div class="terms-heading-row">
                <span>{{ $booking->client->name ?? ($booking->company->name ?? $partyName) }}</span>
                <span class="underline fw-bold">Terms &amp; Conditions:</span>
            </div>

            <div class="terms-lines">
                <p>Above Rates are net &amp; non commission-able quoted in respective currency.</p>
                <p>Check in after 16:00 hours and check out at 12:00 hour</p>
                <p>Check in or check out amendment for individuals should be done 120 hours prior to guest check in.</p>
                <p>Check in or check out amendment for Group should be 21 days prior to guest check in.</p>
                <p>In case of no-show for groups full amount will be charged.</p>
                <p>In case of same arrival date cancellation or no show full amount of total stay will be charged.</p>
                <p>The reservation is Non-refundable, Non-changeable, Non-commissionable.</p>
                <p>Triple and Quad occupancy will be through extra bed, if standard room is not available.</p>
            </div>

            {{-- Bank Account --}}
            <p class="underline fw-bold mb-1 mt-2">Our Bank Account:</p>
            <table class="bank-table">
                <tr>
                    <td class="bank-label">Account name:</td>
                    <td>Maxims Group HAJJ &amp; UMRAH SERVICES</td>
                </tr>
                <tr>
                    <td class="bank-label">Bank name:</td>
                    <td>UNITED BANK LIMITED</td>
                </tr>
                <tr>
                    <td class="bank-label">Branch:</td>
                    <td>LIAQUAT BAZAR BRANCH QUETTA</td>
                </tr>
                <tr>
                    <td class="bank-label">Account #:</td>
                    <td>050701047181</td>
                </tr>
                <tr>
                    <td class="bank-label">IBAN #:</td>
                    <td>PK46UNIL0112050701047181</td>
                </tr>
            </table>

            <p class="text-end mt-2 mb-0">Thanks, and Best Regards</p>
            <p class="text-end page-footer mb-0">Page 1 of 1</p>

        </div>
    </div>

    {{-- html2pdf.js for client-side PDF generation (no server package needed) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadInvoicePDF() {
            const element = document.getElementById('invoiceBox');
            const btn = document.getElementById('downloadBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Generating...';
            btn.disabled = true;

            const opt = {
                margin: 0.2,
                filename: 'Invoice-{{ str_pad($transaction->id, 4, '0', STR_PAD_LEFT) }}.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'a4',
                    orientation: 'portrait'
                },
                pagebreak: {
                    mode: ['avoid-all']
                }
            };

            html2pdf().set(opt).from(element).save().then(function() {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(function() {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>
</body>

</html>
