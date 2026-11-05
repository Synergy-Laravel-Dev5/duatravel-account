@extends('layout.master')

@section('title', 'Chart of Accounts (COA)')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">

<style>
    .coa-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        padding: 16px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .coa-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .coa-pill-btn {
        font-weight: 600;
        font-size: 13px;
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s;
    }
    .coa-pill-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .coa-pill-btn.active {
        background: #0f535e !important;
        color: #fff !important;
        border-color: #0f535e !important;
        box-shadow: 0 2px 6px rgba(15, 83, 94, 0.25);
    }
    .coa-table th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        background-color: #f8fafc;
        border-bottom: 2px solid #cbd5e1 !important;
    }
    .coa-table td {
        font-size: 13px;
        vertical-align: middle;
    }
    .code-badge {
        font-family: monospace;
        font-weight: 700;
        font-size: 12px;
        padding: 3px 8px;
        border-radius: 6px;
        background-color: #f1f5f9;
        color: #0f172a;
        border: 1px solid #cbd5e1;
    }
    @media print {
        body * {
            visibility: hidden;
        }
        #printableCOA, #printableCOA * {
            visibility: visible;
        }
        #printableCOA {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            box-shadow: none !important;
            border: none !important;
        }
        .no-print {
            display: none !important;
        }
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid py-3">

            {{-- ════ Top Bar ════ --}}
            <div class="card border-0 shadow-sm mb-4 no-print">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <h4 class="fs-18 fw-bold m-0 text-dark">
                                <i class="ri-node-tree text-primary me-2"></i> Chart of Accounts (COA)
                            </h4>
                            <span class="text-muted fs-12">
                                Standard General Ledger Account Heads & Directory (Listing without Balances).
                            </span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('account.create') }}" class="btn btn-sm btn-primary px-3">
                                <i class="ri-add-line me-1"></i> Add Account Head
                            </a>
                            <a href="{{ route('supplier.create') }}" class="btn btn-sm btn-outline-info px-3">
                                <i class="ri-user-add-line me-1"></i> Add Supplier
                            </a>
                            <button type="button" onclick="exportCoaCsv()" class="btn btn-sm btn-outline-success px-3">
                                <i class="ri-file-excel-line me-1"></i> Export Excel
                            </button>
                            <a href="{{ route('reports.chart-of-accounts.pdf', request()->query()) }}" target="_blank" class="btn btn-sm btn-outline-danger px-3">
                                <i class="ri-file-pdf-line me-1"></i> Export PDF
                            </a>
                            <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary px-3">
                                <i class="ri-printer-line me-1"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════ 6 Metric Head Cards (Accounts Count Only) ════ --}}
            <div class="row g-3 mb-4 no-print">
                {{-- 1. Assets --}}
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="coa-card border-start border-4 border-primary">
                        <span class="text-muted fs-11 fw-bold text-uppercase">1000 • Assets</span>
                        <h4 class="fs-18 fw-bold text-dark my-1">
                            {{ $headCounts['assets'] }} Heads
                        </h4>
                        <span class="text-primary fs-11 fw-semibold">
                            Cash, Banks & Fixed Assets
                        </span>
                    </div>
                </div>

                {{-- 2. Liabilities --}}
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="coa-card border-start border-4 border-warning">
                        <span class="text-muted fs-11 fw-bold text-uppercase">2000 • Liabilities</span>
                        <h4 class="fs-18 fw-bold text-dark my-1">
                            {{ $headCounts['liabilities'] }} Heads
                        </h4>
                        <span class="text-warning fs-11 fw-semibold">
                            Suppliers & Payables
                        </span>
                    </div>
                </div>

                {{-- 3. Equity --}}
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="coa-card border-start border-4 border-dark">
                        <span class="text-muted fs-11 fw-bold text-uppercase">3000 • Equity</span>
                        <h4 class="fs-18 fw-bold text-dark my-1">
                            {{ $headCounts['equity'] }} Heads
                        </h4>
                        <span class="text-muted fs-11 fw-semibold">
                            Capital & Retained Earnings
                        </span>
                    </div>
                </div>

                {{-- 4. Revenue --}}
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="coa-card border-start border-4 border-success">
                        <span class="text-muted fs-11 fw-bold text-uppercase">4000 • Revenue</span>
                        <h4 class="fs-18 fw-bold text-dark my-1">
                            {{ $headCounts['revenue'] }} Heads
                        </h4>
                        <span class="text-success fs-11 fw-semibold">
                            Packages & Flight Sales
                        </span>
                    </div>
                </div>

                {{-- 5. Cost of Sales --}}
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="coa-card border-start border-4 border-info">
                        <span class="text-muted fs-11 fw-bold text-uppercase">5000 • Cost of Sales</span>
                        <h4 class="fs-18 fw-bold text-dark my-1">
                            {{ $headCounts['cogs'] }} Heads
                        </h4>
                        <span class="text-info fs-11 fw-semibold">
                            Direct Services COGS
                        </span>
                    </div>
                </div>

                {{-- 6. Operating Expenses --}}
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="coa-card border-start border-4 border-danger">
                        <span class="text-muted fs-11 fw-bold text-uppercase">6000 • Expenses</span>
                        <h4 class="fs-18 fw-bold text-dark my-1">
                            {{ $headCounts['expenses'] }} Heads
                        </h4>
                        <span class="text-danger fs-11 fw-semibold">
                            Operating & Admin Overheads
                        </span>
                    </div>
                </div>
            </div>

            {{-- ════ Category Navigation Pills & Search ════ --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 no-print">
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="coa-pill-btn active" onclick="filterCategory('all', this)">
                        All Categories ({{ $totalAccountsCount }})
                    </button>
                    <button type="button" class="coa-pill-btn" onclick="filterCategory('assets', this)">
                        1000 Assets ({{ $headCounts['assets'] }})
                    </button>
                    <button type="button" class="coa-pill-btn" onclick="filterCategory('liabilities', this)">
                        2000 Liabilities ({{ $headCounts['liabilities'] }})
                    </button>
                    <button type="button" class="coa-pill-btn" onclick="filterCategory('equity', this)">
                        3000 Equity ({{ $headCounts['equity'] }})
                    </button>
                    <button type="button" class="coa-pill-btn" onclick="filterCategory('revenue', this)">
                        4000 Revenue ({{ $headCounts['revenue'] }})
                    </button>
                    <button type="button" class="coa-pill-btn" onclick="filterCategory('cogs', this)">
                        5000 COGS ({{ $headCounts['cogs'] }})
                    </button>
                    <button type="button" class="coa-pill-btn" onclick="filterCategory('expenses', this)">
                        6000 Expenses ({{ $headCounts['expenses'] }})
                    </button>
                </div>
                <div style="min-width: 250px;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="ri-search-line text-muted"></i></span>
                        <input type="text" id="coaSearchInput" class="form-control form-control-sm border-start-0" placeholder="Search account name, code, supplier..." onkeyup="filterCoaSearch()">
                    </div>
                </div>
            </div>

            {{-- ════ Printable Chart of Accounts ════ --}}
            <div class="card border shadow-sm" id="printableCOA" style="background:#fff; border-radius:12px;">
                <div class="card-body p-4 p-md-5">

                    {{-- Document Header --}}
                    <div class="text-center pb-4 mb-4 border-bottom">
                        <h4 class="text-uppercase fw-bold text-dark mb-1" style="letter-spacing:1px; font-family:'Playfair Display', serif;">
                            DUA TRAVELS & TOURS (PVT) LTD
                        </h4>
                        <h3 class="fw-bold mb-1" style="color:#0f535e; letter-spacing:1.5px;">
                            CHART OF ACCOUNTS
                        </h3>
                        <p class="text-muted fs-13 mb-0">
                            General Ledger Head Listing & Master Directory
                            <span class="badge bg-light text-dark border ms-2">Total Accounts: {{ $totalAccountsCount }}</span>
                        </p>
                    </div>

                    @php
                        $sections = [
                            'assets' => [
                                'title'       => '1000 • ASSETS',
                                'color'       => 'primary',
                                'description' => 'Current assets, bank accounts, cash registers, trade receivables, advances and fixed assets.',
                                'items'       => $chart['assets'],
                            ],
                            'liabilities' => [
                                'title'       => '2000 • LIABILITIES',
                                'color'       => 'warning',
                                'description' => 'Trade creditors, registered suppliers/vendors, staff salaries payable, and loans.',
                                'items'       => $chart['liabilities'],
                            ],
                            'equity' => [
                                'title'       => '3000 • EQUITY & CAPITAL',
                                'color'       => 'dark',
                                'description' => "Owner's invested capital, share capital, retained earnings, and reserves.",
                                'items'       => $chart['equity'],
                            ],
                            'revenue' => [
                                'title'       => '4000 • REVENUE & OPERATING INCOME',
                                'color'       => 'success',
                                'description' => 'Gross income from Umrah packages, flight ticketing, hotel booking and visa services.',
                                'items'       => $chart['revenue'],
                            ],
                            'cogs' => [
                                'title'       => '5000 • COST OF SALES (DIRECT OPERATING COSTS)',
                                'color'       => 'info',
                                'description' => 'Direct costs paid to suppliers for tickets, hotels, transport, and visas.',
                                'items'       => $chart['cogs'],
                            ],
                            'expenses' => [
                                'title'       => '6000 • OPERATING EXPENSES',
                                'color'       => 'danger',
                                'description' => 'Salaries, office rent, utilities, marketing, and general administrative overheads.',
                                'items'       => $chart['expenses'],
                            ],
                        ];
                    @endphp

                    @foreach($sections as $secKey => $sec)
                        <div class="coa-section mb-4" data-category="{{ $secKey }}">
                            <div class="d-flex align-items-center justify-content-between p-2 px-3 rounded mb-2 bg-light border-start border-4 border-{{ $sec['color'] }}">
                                <div>
                                    <h5 class="fw-bold text-dark m-0 fs-15">{{ $sec['title'] }}</h5>
                                    <span class="text-muted fs-11">{{ $sec['description'] }}</span>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-white text-dark border px-2 py-1 fs-12 fw-semibold">
                                        <i class="ri-list-check me-1 text-primary"></i> {{ count($sec['items']) }} Accounts
                                    </span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover coa-table align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width: 140px;" class="text-center">Code</th>
                                            <th>Account Name / Head</th>
                                            <th style="width: 220px;">Classification</th>
                                            <th style="width: 260px;">Sub-Type / Group</th>
                                            <th style="width: 100px;" class="text-center">Status</th>
                                            <th style="width: 80px;" class="text-center no-print">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($sec['items'] as $item)
                                            <tr class="coa-row">
                                                <td class="text-center">
                                                    <span class="code-badge">{{ $item['code'] }}</span>
                                                </td>
                                                <td class="fw-semibold text-dark">
                                                    {{ $item['name'] }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border fs-11">
                                                        {{ $item['classification'] }}
                                                    </span>
                                                </td>
                                                <td class="text-muted fs-12">
                                                    {{ $item['sub_type'] }}
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1 fs-11">
                                                        {{ $item['status'] }}
                                                    </span>
                                                </td>
                                                <td class="text-center no-print">
                                                    @if(!empty($item['edit_url']))
                                                        <a href="{{ $item['edit_url'] }}" class="btn btn-xs btn-outline-primary" title="Edit">
                                                            <i class="ri-edit-line"></i>
                                                        </a>
                                                    @else
                                                        <span class="text-muted fs-11">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-2 text-muted">No accounts under this classification.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function filterCategory(cat, btn) {
        document.querySelectorAll('.coa-pill-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const sections = document.querySelectorAll('.coa-section');
        sections.forEach(sec => {
            if (cat === 'all' || sec.getAttribute('data-category') === cat) {
                sec.style.display = '';
            } else {
                sec.style.display = 'none';
            }
        });
    }

    function filterCoaSearch() {
        const query = document.getElementById('coaSearchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.coa-row');
        rows.forEach(r => {
            const text = r.innerText.toLowerCase();
            r.style.display = text.includes(query) ? '' : 'none';
        });
    }

    function exportCoaCsv() {
        let csv = [];
        const tables = document.querySelectorAll('.coa-table');
        tables.forEach(t => {
            const rows = t.querySelectorAll('tr');
            rows.forEach(r => {
                let row = [], cols = r.querySelectorAll('td, th');
                // ignore the last column (action)
                let limit = cols.length > 5 ? cols.length - 1 : cols.length;
                for (let j = 0; j < limit; j++) {
                    let cellText = cols[j].innerText.replace(/,/g, '').replace(/\n/g, ' ').trim();
                    row.push('"' + cellText + '"');
                }
                csv.push(row.join(','));
            });
        });
        let csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
        let downloadLink = document.createElement('a');
        downloadLink.download = 'Chart_of_Accounts_' + (new Date().toISOString().slice(0, 10)) + '.csv';
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = 'none';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
</script>
@endsection
