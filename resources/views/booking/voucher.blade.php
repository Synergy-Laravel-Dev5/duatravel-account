<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher - {{ 'GHU/HB-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">

    <style>
        body {
            background: #f1f1f1;
        }

        .voucher-box {
            background: #fff;
            padding: 28px 36px;
            max-width: 900px;
            margin: 0 auto;
            font-size: 13px;
            color: #1a1a1a;
            font-family: Arial, Helvetica, sans-serif;
        }

        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding-bottom: 14px;
            margin-bottom: 14px;
        }

        .header-row img {
            height: 70px;
        }

        .header-right {
            text-align: right;
        }

        .company-name {
            font-weight: 700;
            font-size: 17px;
            margin-bottom: 2px;
            color: #1a1a1a;
        }

        .company-address-ar {
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .company-meta {
            font-size: 11.5px;
            color: #555;
        }

        .section-bar {
            background: #f0f0f0;
            font-weight: 700;
            font-size: 14px;
            padding: 7px 12px;
            margin: 14px 0 10px;
            border-radius: 2px;
        }

        .field-row {
            font-size: 13px;
            margin: 4px 0;
        }

        .client-bar {
            background: #f0f0f0;
            font-weight: 700;
            font-size: 13.5px;
            padding: 7px 12px;
            margin: 10px 0 4px;
            border-radius: 2px;
        }

        .guest-row {
            font-size: 13px;
            font-weight: 600;
            margin: 4px 0 10px;
        }

        .voucher-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .voucher-table th,
        .voucher-table td {
            border: 1px solid #ddd;
            padding: 7px 8px;
            font-size: 12px;
            text-align: left;
        }

        .voucher-table thead th {
            background: #e7dcb8;
            font-weight: 700;
            white-space: nowrap;
        }

        .voucher-table td.num,
        .voucher-table th.num {
            text-align: center;
        }

        .cost-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .cost-table td {
            padding: 7px 12px;
            border-bottom: 1px solid #eee;
        }

        .cost-table td:first-child {
            font-weight: 500;
        }

        .cost-table td:last-child {
            text-align: right;
            font-weight: 600;
        }

        .cost-table tr.total-line td {
            border-top: 2px double #444;
            border-bottom: 2px double #444;
            font-size: 14px;
            font-weight: 700;
            background: #f7f7f7;
        }

        .cost-table tr.balance-line td {
            font-size: 14.5px;
            font-weight: 700;
        }

        .words-bar {
            background: #f0f0f0;
            font-size: 12.5px;
            padding: 8px 12px;
            margin-bottom: 10px;
            border-radius: 2px;
        }

        .note-bar {
            background: #e7dcb8;
            font-weight: 700;
            font-size: 12.5px;
            padding: 6px 12px;
            margin-bottom: 10px;
            border-radius: 2px;
        }

        .bank-cols {
            display: flex;
            gap: 30px;
            margin-bottom: 14px;
        }

        .bank-col {
            font-size: 12.5px;
            line-height: 1.5;
        }

        .footer-note {
            font-size: 11px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 8px;
            margin-top: 10px;
        }

        .footer-company {
            font-weight: 700;
            color: #444;
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

    <div class="container py-4">

        <div class="d-flex justify-content-end gap-2 mb-3 no-print">
            <a href="{{ route('booking.show', $booking->id) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <button onclick="downloadVoucherPDF()" class="btn btn-primary" id="downloadBtn">
                <i class="bi bi-download"></i> Download PDF
            </button>
        </div>

        <div id="voucherBox" class="voucher-box">

            @php
                $bookingPartyLabel = $booking->booking_for === 'company' ? 'Company' : 'Client';
                $bookingPartyName =
                    $booking->booking_for === 'company'
                        ? $booking->company->name ?? '-'
                        : $booking->client->name ?? '-';
            @endphp

            {{-- Header --}}
            <div class="header-row">
                @php
                    $isVoucherHajj = ($booking->package_type ?? session('dashboard_package', 'hajj')) === 'hajj';
                @endphp
                <img src="{{ $isVoucherHajj ? asset('assets/images/logo/kgm.png') : asset('assets/images/logo/logo.png') }}" alt="Logo" style="height:60px; object-fit:contain;">
                <div class="header-right">
                    <div class="company-name">Maxims Group &amp; UMRAH SERVICES (PVT) LTD</div>
                    <div class="company-address-ar">شركــة إعـمـار الضیافــة الفندقیــة</div>
                    <div class="company-meta">
                        Telephone: +92-XXX-XXXXXXX &nbsp;|&nbsp; Email: info@gulfhajjumrah.com
                    </div>
                </div>
            </div>

            <div class="section-bar">Hotel Voucher</div>

            <p class="field-row"><strong>Voucher #:</strong>
                GHU/HB-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</p>
            <p class="field-row"><strong>Date:</strong> {{ now()->format('d-m-Y') }}</p>

            <div class="client-bar">{{ $bookingPartyLabel }} : {{ $bookingPartyName }}</div>
            <p class="guest-row">Guest Name :
                {{ $booking->persons->first()->full_name ?? $bookingPartyName }}</p>

            {{-- Accommodation --}}
            <div class="section-bar" style="margin-top:14px;">Accommodation Details</div>

            <table class="voucher-table">
                <thead>
                    <tr>
                        <th>Hotel Name</th>
                        <th>Location</th>
                        <th>Room Type</th>
                        <th class="num">Rooms</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th class="num">Nights</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($booking->hotels as $hotel)
                        <tr>
                            <td>{{ $hotel->hotel_name ?? '-' }}</td>
                            <td>{{ $hotel->location ? ucfirst($hotel->location) : '-' }}</td>
                            <td>{{ $hotel->room_type ? ucfirst($hotel->room_type) : '-' }}</td>
                            <td class="num">{{ $hotel->no_of_rooms ?? '-' }}</td>
                            <td>{{ $hotel->check_in ? \Carbon\Carbon::parse($hotel->check_in)->format('d-m-Y') : '-' }}
                            </td>
                            <td>{{ $hotel->check_out ? \Carbon\Carbon::parse($hotel->check_out)->format('d-m-Y') : '-' }}
                            </td>
                            <td class="num">{{ $hotel->no_of_nights ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No hotels added for this booking.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="section-bar" style="margin-top:14px;">Costing Summary</div>

            <table class="cost-table">
                <tr>
                    <td>Package Cost ({{ number_format($booking->package_cost, 0) }} x {{ $booking->no_of_pax }} pax)
                    </td>
                    <td>PKR {{ number_format($booking->package_cost * $booking->no_of_pax, 0) }}</td>
                </tr>
                <tr>
                    <td>Visa Charges</td>
                    <td>PKR {{ number_format($booking->visa_charges, 0) }}</td>
                </tr>
                <tr>
                    <td>Flight Charges</td>
                    <td>PKR {{ number_format($booking->flight_charges, 0) }}</td>
                </tr>
                <tr>
                    <td>Other Charges</td>
                    <td>PKR {{ number_format($booking->other_charges, 0) }}</td>
                </tr>
                @if($booking->additional_services_amount > 0)
                <tr>
                    <td>Additional Services<br><small style="color: #666; font-size: 11px;">{!! strip_tags($booking->additional_services_detail) !!}</small></td>
                    <td>PKR {{ number_format($booking->additional_services_amount, 0) }}</td>
                </tr>
                @endif
                @if($booking->discount > 0)
                <tr>
                    <td>Discount</td>
                    <td style="color: red;">- PKR {{ number_format($booking->discount, 0) }}</td>
                </tr>
                @endif
                <tr class="total-line">
                    <td>Total Amount</td>
                    <td>PKR {{ number_format($booking->total_amount, 0) }}</td>
                </tr>
                <tr>
                    <td>Total Received</td>
                    <td>PKR {{ number_format($booking->total_received, 0) }}</td>
                </tr>
                @php
                    $bal = $booking->balance;
                    $balLabel = $bal > 0 ? 'Balance Due' : ($bal < 0 ? 'Advance Balance' : 'Balance Cleared');
                    $balSign = $bal < 0 ? '+' : '';
                @endphp
                <tr class="balance-line">
                    <td>{{ $balLabel }}</td>
                    <td>PKR {{ $balSign }}{{ number_format(abs($bal), 0) }}</td>
                </tr>
            </table>

            @php
                if (!function_exists('numberToWordsVoucher')) {
                    function numberToWordsVoucher($num)
                    {
                        $num = (int) $num;
                        if ($num === 0) {
                            return 'Zero';
                        }

                        $ones = [
                            '',
                            'One',
                            'Two',
                            'Three',
                            'Four',
                            'Five',
                            'Six',
                            'Seven',
                            'Eight',
                            'Nine',
                            'Ten',
                            'Eleven',
                            'Twelve',
                            'Thirteen',
                            'Fourteen',
                            'Fifteen',
                            'Sixteen',
                            'Seventeen',
                            'Eighteen',
                            'Nineteen',
                        ];
                        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

                        $words = function ($n) use (&$words, $ones, $tens) {
                            if ($n < 20) {
                                return $ones[$n];
                            }
                            if ($n < 100) {
                                return trim($tens[intdiv($n, 10)] . ' ' . $ones[$n % 10]);
                            }
                            if ($n < 1000) {
                                return trim(
                                    $ones[intdiv($n, 100)] . ' Hundred ' . ($n % 100 ? 'and ' . $words($n % 100) : ''),
                                );
                            }
                            if ($n < 100000) {
                                return trim(
                                    $words(intdiv($n, 1000)) . ' Thousand ' . ($n % 1000 ? $words($n % 1000) : ''),
                                );
                            }
                            if ($n < 10000000) {
                                return trim(
                                    $words(intdiv($n, 100000)) . ' Lakh ' . ($n % 100000 ? $words($n % 100000) : ''),
                                );
                            }
                            return trim(
                                $words(intdiv($n, 10000000)) . ' Crore ' . ($n % 10000000 ? $words($n % 10000000) : ''),
                            );
                        };

                        return trim($words($num));
                    }
                }
            @endphp

            <div class="words-bar">
                <strong>Total Amount in Words:</strong> {{ numberToWordsVoucher($booking->total_amount) }} Rupees Only
            </div>

            <div class="note-bar">** For Booking Transactions **</div>

            {{-- Bank Details --}}
            <div class="bank-cols">
                <div class="bank-col">
                    <strong>UNITED BANK LIMITED</strong><br>
                    MAXIMS GROUP Hajj &amp; UMRAH SERVICES<br>
                    Account #: 050701047181<br>
                    IBAN: PK46UNIL0112050701047181<br>
                    LIAQUAT BAZAR BRANCH QUETTA.
                </div>
            </div>

            <div class="footer-note">
                This is a system generated voucher. Please collect official receipt after payment.<br>
                <span class="footer-company">Maxims Group HAJJ &amp; UMRAH SERVICES (PVT) LTD</span><br>
                Printed by {{ auth()->user()->name ?? 'Admin' }} on {{ now()->format('d-m-Y H:i:s') }}
            </div>

        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadVoucherPDF() {
            const element = document.getElementById('voucherBox');
            const btn = document.getElementById('downloadBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Generating...';
            btn.disabled = true;

            const opt = {
                margin: 0.2,
                filename: 'Voucher-{{ 'GHU-HB-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}.pdf',
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
