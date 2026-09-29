@extends('layout.master')

@section('title', 'Company Ledger - ' . $company->name)

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container">

                <!-- TOP BAR -->
                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">
                        <i class="fas fa-building text-primary me-2"></i>
                        Company Ledger — {{ $company->name }}
                    </h4>
                    <div class="d-flex gap-2">
                        <button id="exportPDF" class="btn btn-danger btn-sm shadow-sm">
                            <i class="fas fa-file-pdf me-1"></i> Export PDF
                        </button>
                        <a href="{{ route('transaction.company-ledger.filter') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">

                            <!-- CARD HEADER -->
                            <div class="card-header bg-primary text-white py-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-file-invoice-dollar me-2"></i>
                                    Ledger Summary
                                </h5>
                            </div>

                            <div class="card-body p-4" id="ledgerContent">

                                @if (request('from_date') && request('to_date'))
                                    <div class="alert alert-info py-2 mb-4 shadow-sm rounded-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Transactions filtered between
                                        <strong>{{ \Carbon\Carbon::parse(request('from_date'))->format('d M, Y') }}</strong>
                                        and
                                        <strong>{{ \Carbon\Carbon::parse(request('to_date'))->format('d M, Y') }}</strong>.
                                        <br><small>Booking totals reflect all bookings for this company regardless of
                                            date.</small>
                                    </div>
                                @endif

                                {{-- Report Information --}}
                                <div class="border rounded-3 p-3 mb-4 bg-light">
                                    <h6 class="fw-bold text-secondary mb-3">
                                        <i class="fas fa-info-circle me-2"></i> Report Information
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Company:</strong> {{ $company->name }}</p>
                                            <p class="mb-1"><strong>Total Bookings:</strong> {{ $bookingsCount }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Total Pax:</strong> {{ $totalPax }}</p>
                                            <p class="mb-1"><strong>Generated On:</strong>
                                                {{ \Carbon\Carbon::now()->format('d M, Y h:i A') }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Summary Cards --}}
                                <div class="row g-3 mb-4">
                                    <div class="col-6 col-md-3">
                                        <div class="card border-0 shadow-sm h-100 text-center">
                                            <div class="card-body py-3">
                                                <div class="text-muted small mb-1">Total Bookings</div>
                                                <div class="fs-4 fw-bold">{{ $bookingsCount }}</div>
                                                <div class="text-muted small">{{ $totalPax }} pax</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="card border-0 shadow-sm h-100 text-center">
                                            <div class="card-body py-3">
                                                <div class="text-muted small mb-1">Total Booking Amount</div>
                                                <div class="fs-5 fw-bold text-primary">
                                                    PKR {{ number_format($totalBooking, 0) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="card border-0 shadow-sm h-100 text-center">
                                            <div class="card-body py-3">
                                                <div class="text-muted small mb-1">Total Received</div>
                                                <div class="fs-5 fw-bold text-success">
                                                    PKR {{ number_format($totalPaid, 0) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="card border-0 shadow-sm h-100 text-center">
                                            <div class="card-body py-3">
                                                <div class="text-muted small mb-1">
                                                    {{ $balance > 0 ? 'Balance Due' : ($balance < 0 ? 'Advance' : 'Balance') }}
                                                </div>
                                                <div
                                                    class="fs-5 fw-bold {{ $balance > 0 ? 'text-danger' : ($balance < 0 ? 'text-info' : 'text-success') }}">
                                                    PKR {{ number_format(abs($balance), 0) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Bookings Table --}}
                                <h6 class="fw-bold text-secondary mb-3">
                                    <i class="fas fa-briefcase me-2"></i> Bookings ({{ $bookingsCount }})
                                </h6>
                                <div class="table-responsive mb-4 shadow-sm rounded-3 border">
                                    <table id="bookingsTable" class="table table-hover align-middle mb-0">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>#</th>
                                                <th>Booking Date</th>
                                                <th>Package</th>
                                                <th>Pax</th>
                                                <th>Total Amount</th>
                                                <th>Received</th>
                                                <th>Balance</th>
                                                <th class="no-pdf text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($bookings as $b)
                                                <tr>
                                                    <td>#{{ $b->id }}</td>
                                                    <td>{{ $b->created_at ? $b->created_at->format('d M, Y') : '-' }}</td>
                                                    <td>{{ ucfirst($b->package_type ?? '-') }}
                                                        {{ $b->package_name ? '- ' . $b->package_name : '' }}</td>
                                                    <td>{{ $b->no_of_pax ?? '-' }}</td>
                                                    <td>PKR {{ number_format($b->total_amount, 2) }}</td>
                                                    <td class="text-success fw-semibold">
                                                        PKR {{ number_format($b->total_received, 2) }}
                                                    </td>
                                                    <td
                                                        class="fw-semibold {{ $b->balance > 0 ? 'text-danger' : 'text-success' }}">
                                                        PKR {{ number_format($b->balance, 2) }}
                                                    </td>
                                                    @can('booking_show')
                                                        <td class="text-center no-pdf">
                                                            <a href="{{ route('booking.show', $b->id) }}"
                                                                class="btn btn-sm btn-outline-primary">
                                                                <i class="mdi mdi-eye"></i>
                                                            </a>
                                                        </td>
                                                    @endcan
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted py-4">
                                                        No bookings found for this company.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        @if ($bookingsCount > 0)
                                            <tfoot class="table-light fw-bold">
                                                <tr>
                                                    <td colspan="4">Totals</td>
                                                    <td>PKR {{ number_format($totalBooking, 2) }}</td>
                                                    <td class="text-success">PKR {{ number_format($bookingReceived, 2) }}
                                                    </td>
                                                    <td>PKR {{ number_format($totalBooking - $bookingReceived, 2) }}</td>
                                                    <td class="no-pdf"></td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>

                                {{-- Transactions Table --}}
                                <h6 class="fw-bold text-secondary mb-3">
                                    <i class="fas fa-money-check-alt me-2"></i> Transactions ({{ $transactions->count() }})
                                </h6>
                                <div class="table-responsive mb-4 shadow-sm rounded-3 border">
                                    <table id="transactionsTable" class="table table-hover align-middle mb-0">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>#</th>
                                                <th>Date</th>
                                                <th>Booking</th>
                                                <th>Payment Type</th>
                                                <th>Reference No</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($transactions as $t)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($t->payment_date)->format('d M, Y') }}
                                                    </td>
                                                    <td>{{ $t->booking ? '#' . $t->booking->id : '-' }}</td>
                                                    <td>{{ ucfirst(str_replace('_', ' ', $t->payment_type)) }}</td>
                                                    <td>{{ $t->reference_number ?? '-' }}</td>
                                                    <td class="text-success fw-semibold">
                                                        PKR {{ number_format($t->amount, 2) }}
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge {{ ['confirmed' => 'bg-success', 'pending' => 'bg-warning text-dark', 'rejected' => 'bg-danger'][$t->status] ?? 'bg-secondary' }}">
                                                            {{ ucfirst($t->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-4">
                                                        No transactions found.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        @if ($transactions->count() > 0)
                                            <tfoot class="table-light fw-bold">
                                                <tr>
                                                    <td colspan="5">Confirmed Total</td>
                                                    <td class="text-success">PKR {{ number_format($transactionsPaid, 2) }}
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>

                                {{-- Final Summary --}}
                                <div class="row justify-content-end">
                                    <div class="col-md-6">
                                        <div class="border rounded-3 p-4 bg-light shadow-sm">
                                            <h6 class="fw-bold text-secondary mb-3">
                                                <i class="fas fa-calculator me-2"></i> Final Summary
                                            </h6>
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr>
                                                    <td>Total Booking Amount</td>
                                                    <td class="text-end fw-semibold">PKR
                                                        {{ number_format($totalBooking, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Total Received (Bookings)</td>
                                                    <td class="text-end">PKR {{ number_format($bookingReceived, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Total Received (Confirmed Transactions)</td>
                                                    <td class="text-end">PKR {{ number_format($transactionsPaid, 2) }}</td>
                                                </tr>
                                                <tr class="fw-bold border-top">
                                                    <td>Grand Total Received</td>
                                                    <td class="text-end">PKR {{ number_format($totalPaid, 2) }}</td>
                                                </tr>
                                                <tr class="fw-bold border-top">
                                                    <td>
                                                        {{ $balance > 0 ? 'Balance Due' : ($balance < 0 ? 'Advance Balance' : 'Balance') }}
                                                    </td>
                                                    <td
                                                        class="text-end {{ $balance > 0 ? 'text-danger' : ($balance < 0 ? 'text-info' : 'text-success') }}">
                                                        PKR {{ number_format(abs($balance), 2) }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- PDF Export Scripts --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>

    <script>
        document.getElementById('exportPDF').addEventListener('click', function() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF();

            const printedOnDateTime = "{{ \Carbon\Carbon::now()->format('d/M/Y h:i A') }}";
            const pageWidth = doc.internal.pageSize.width;

            doc.setFontSize(10);
            doc.setTextColor(128, 128, 128);
            const textWidth = doc.getTextWidth(`PRINTED ON: ${printedOnDateTime}`);
            doc.text(`PRINTED ON: ${printedOnDateTime}`, pageWidth - textWidth - 14, 10);

            doc.setTextColor(0, 0, 0);
            doc.setFontSize(16);
            doc.text("Company Ledger - {{ $company->name }}", 14, 20);

            doc.setFontSize(11);
            doc.text(`Total Bookings: {{ $bookingsCount }}   |   Total Pax: {{ $totalPax }}`, 14, 30);
            doc.text(`Total Amount: PKR {{ number_format($totalBooking, 2) }}`, 14, 36);
            doc.text(`Total Received: PKR {{ number_format($totalPaid, 2) }}`, 14, 42);
            doc.text(
                `{{ $balance > 0 ? 'Balance Due' : ($balance < 0 ? 'Advance Balance' : 'Balance') }}: PKR {{ number_format(abs($balance), 2) }}`,
                14, 48);

            doc.setFontSize(12);
            doc.text("Bookings", 14, 58);
            doc.autoTable({
                html: '#bookingsTable',
                startY: 62,
                theme: 'grid',
                headStyles: {
                    fillColor: [41, 128, 185]
                },
                styles: {
                    fontSize: 8
                },
                columnStyles: {
                    7: {
                        cellWidth: 0
                    }
                },
                didParseCell: function(data) {
                    if (data.column.index === 7) data.cell.text = [];
                }
            });

            let nextY = doc.lastAutoTable.finalY + 10;
            doc.setFontSize(12);
            doc.text("Transactions", 14, nextY);
            doc.autoTable({
                html: '#transactionsTable',
                startY: nextY + 4,
                theme: 'grid',
                headStyles: {
                    fillColor: [41, 128, 185]
                },
                styles: {
                    fontSize: 8
                }
            });

            doc.save(
                `Company_Ledger_{{ \Illuminate\Support\Str::slug($company->name) }}_{{ \Carbon\Carbon::now()->format('d_M_Y') }}.pdf`
                );
        });
    </script>

@endsection
