@extends('layout.master')

@section('title', 'Transaction Detail')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap');

        :root {
            --gold: #C9A84C;
            --gold-light: #F0D080;
            --gold-subtle: #FBF5E6;
            --emerald: #1A6B4A;
            --navy: #0F2544;
            --navy-light: #1B3A6B;
            --cream: #FDFAF4;
            --text-dark: #1C1C1E;
            --text-muted: #6B7280;
            --border: #EDE8DC;
            --card-shadow: 0 2px 16px rgba(15, 37, 68, .08);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream) !important;
        }

        .page-title-font {
            font-family: 'Playfair Display', serif;
            color: var(--navy);
            font-size: 20px;
            font-weight: 700;
        }

        .detail-card {
            background: #fff;
            border-radius: 18px;
            border: 1px solid var(--border);
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .detail-card-header {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
            padding: 22px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .detail-card-header h5 {
            font-family: 'Playfair Display', serif;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .detail-card-header .txn-id {
            font-size: 12px;
            color: rgba(255, 255, 255, .6);
            font-weight: 500;
            margin-top: 3px;
        }

        .amount-pill {
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 30px;
            padding: 6px 18px;
            color: #fff;
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 700;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }

        .detail-cell {
            padding: 18px 28px;
            border-bottom: 1px solid #F4F0E8;
            border-right: 1px solid #F4F0E8;
        }

        .detail-cell:nth-child(even) {
            border-right: none;
        }

        .detail-cell:nth-last-child(-n+2) {
            border-bottom: none;
        }

        .detail-cell .d-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .detail-cell .d-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .proof-area {
            padding: 20px 28px;
            border-top: 1px solid #F4F0E8;
            background: #FAFAF8;
        }

        .proof-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .timestamp-bar {
            padding: 12px 28px;
            background: #F9F6F0;
            border-top: 1px solid #F0EBE0;
            font-size: 11.5px;
            color: var(--text-muted);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .status-pill::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-pill.confirmed {
            background: #EDFBF4;
            color: #1A6B4A;
        }

        .status-pill.confirmed::before {
            background: #1A6B4A;
        }

        .status-pill.pending {
            background: #FBF5E6;
            color: #9A6F1A;
        }

        .status-pill.pending::before {
            background: #C9A84C;
        }

        .status-pill.rejected {
            background: #FEF0F0;
            color: #D94040;
        }

        .status-pill.rejected::before {
            background: #D94040;
        }

        .pay-type-pill {
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .pay-type-pill.cash {
            background: #EDFBF4;
            color: #1A6B4A;
        }

        .pay-type-pill.bank_transfer {
            background: #EEF2FF;
            color: #3D5CCC;
        }

        .pay-type-pill.cheque {
            background: #FBF5E6;
            color: #9A6F1A;
        }

        .pay-type-pill.online {
            background: #E8F7FB;
            color: #0D7A8A;
        }

        .btn-back {
            background: #F5F0E8;
            border: 1px solid var(--border);
            color: var(--navy);
            font-size: 13px;
            font-weight: 600;
            border-radius: 10px;
            padding: 7px 16px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background .2s;
        }

        .btn-back:hover {
            background: var(--border);
            color: var(--navy);
        }

        .btn-edit-txn {
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            border: none;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            border-radius: 10px;
            padding: 7px 16px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: opacity .2s;
        }

        .btn-edit-txn:hover {
            opacity: .88;
            color: #fff;
        }

        @media (max-width: 576px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .detail-cell {
                border-right: none;
            }

            .detail-cell:last-child {
                border-bottom: none;
            }

            .amount-pill {
                font-size: 15px;
                padding: 5px 14px;
            }
        }
    </style>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                {{-- Header --}}
                <div class="py-3 d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h4 class="page-title-font mb-1">Transaction Detail</h4>
                        <p style="font-size:13px;color:var(--text-muted);margin:0;">
                            Full breakdown of payment record
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('transaction.edit', $transaction->id) }}" class="btn-edit-txn">
                            <i class="mdi mdi-pencil"></i> Edit
                        </a>
                        <a href="{{ route('transaction.index') }}" class="btn-back">
                            <i class="mdi mdi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                {{-- Main Card --}}
                <div class="detail-card">

                    {{-- Card Header with amount --}}
                    <div class="detail-card-header">
                        <div>
                            <h5>Payment Record</h5>
                            <div class="txn-id">#TXN-{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</div>
                        </div>
                        <div class="amount-pill">
                            PKR {{ number_format($transaction->amount, 0) }}
                        </div>
                    </div>

                    {{-- Detail Grid --}}
                    <div class="detail-grid">

                        {{-- Client --}}
                        <div class="detail-cell">
                            <div class="d-label">Client</div>
                            <div class="d-value">
                                <i class="mdi mdi-account-outline me-1" style="color:var(--gold);"></i>
                                {{ $transaction->client->name ?? '—' }}
                            </div>
                        </div>

                        {{-- Booking --}}
                        <div class="detail-cell">
                            <div class="d-label">Booking</div>
                            <div class="d-value">
                                @if ($transaction->booking)
                                    <a href="{{ route('booking.show', $transaction->booking->id) }}"
                                        style="color:var(--navy);text-decoration:none;font-weight:600;">
                                        <i class="mdi mdi-calendar-check-outline me-1" style="color:var(--gold);"></i>
                                        #{{ $transaction->booking->id }} —
                                        {{ ucfirst($transaction->booking->package_type) }}
                                    </a>
                                @else
                                    <span style="color:var(--text-muted);">—</span>
                                @endif
                            </div>
                        </div>

                        {{-- Payment Type --}}
                        <div class="detail-cell">
                            <div class="d-label">Payment Type</div>
                            <div class="d-value">
                                <span class="pay-type-pill {{ $transaction->payment_type }}">
                                    {{ ucfirst(str_replace('_', ' ', $transaction->payment_type)) }}
                                </span>
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="detail-cell">
                            <div class="d-label">Status</div>
                            <div class="d-value">
                                <span class="status-pill {{ $transaction->status }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </div>
                        </div>

                        {{-- Payment Date --}}
                        <div class="detail-cell">
                            <div class="d-label">Payment Date</div>
                            <div class="d-value">
                                <i class="mdi mdi-calendar-outline me-1" style="color:var(--gold);"></i>
                                {{ \Carbon\Carbon::parse($transaction->payment_date)->format('d M, Y') }}
                            </div>
                        </div>

                        {{-- Reference No --}}
                        <div class="detail-cell">
                            <div class="d-label">Reference / Cheque No</div>
                            <div class="d-value">
                                @if ($transaction->reference_number)
                                    <span
                                        style="font-family:monospace;background:#F5F0E8;padding:2px 8px;border-radius:6px;font-size:13px;">
                                        {{ $transaction->reference_number }}
                                    </span>
                                @else
                                    <span style="color:var(--text-muted);">—</span>
                                @endif
                            </div>
                        </div>

                        {{-- Notes - full width --}}
                        <div class="detail-cell" style="grid-column: span 2; border-right: none;">
                            <div class="d-label">Notes</div>
                            <div class="d-value" style="font-weight:400;color:var(--text-muted);">
                                {{ $transaction->notes ?? 'No notes added.' }}
                            </div>
                        </div>

                    </div>

                    {{-- Proof of Payment --}}
                    <div class="proof-area">
                        <div class="proof-label">Proof of Payment</div>
                        @if ($transaction->proof_of_payment)
                            @php
                                $ext = strtolower(pathinfo($transaction->proof_of_payment, PATHINFO_EXTENSION));
                            @endphp
                            @if (in_array($ext, ['jpg', 'jpeg', 'png']))
                                <img src="{{ asset('assets/images/transaction_proofs/' . $transaction->proof_of_payment) }}"
                                    alt="Proof"
                                    style="max-height:220px;border-radius:10px;border:1px solid var(--border);">
                            @else
                                <a href="{{ asset('assets/images/transaction_proofs/' . $transaction->proof_of_payment) }}"
                                    target="_blank"
                                    style="display:inline-flex;align-items:center;gap:6px;background:var(--navy);color:#fff;padding:8px 16px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;">
                                    <i class="mdi mdi-file-pdf"></i> View PDF Proof
                                </a>
                            @endif
                        @else
                            <span style="font-size:13px;color:var(--text-muted);">No proof uploaded.</span>
                        @endif
                    </div>

                    {{-- Timestamps --}}
                    <div class="timestamp-bar d-flex gap-3 flex-wrap">
                        <span><i class="mdi mdi-clock-outline me-1"></i> Created:
                            {{ $transaction->created_at->format('d M Y, h:i A') }}</span>
                        <span><i class="mdi mdi-update me-1"></i> Updated:
                            {{ $transaction->updated_at->format('d M Y, h:i A') }}</span>
                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection
