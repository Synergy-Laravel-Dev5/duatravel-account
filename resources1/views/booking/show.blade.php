<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking — {{ $booking->client->name ?? 'N/A' }} | Dua Travels & Tours</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Outfit:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.2.96/css/materialdesignicons.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
@php
    $pkgType = $booking->package_type ?? '';
    if ($pkgType === 'hajj') {
        $themeAccent = '#C9A84C';
        $themeAccentLt = '#F0D080';
        $themeAccentDk = '#7A5C18';
        $themeAccentSubtle = '#FBF5E6';
        $themeHeroBg = 'linear-gradient(135deg, #0F2544 0%, #30220a 100%)';
    } elseif ($pkgType === 'umrah') {
        $themeAccent = '#1A6B4A';
        $themeAccentLt = '#228B5E';
        $themeAccentDk = '#0F402C';
        $themeAccentSubtle = '#E8F5EE';
        $themeHeroBg = 'linear-gradient(135deg, #0F2544 0%, #0a251a 100%)';
    } else {
        $themeAccent = '#0F2544';
        $themeAccentLt = '#1B3A6B';
        $themeAccentDk = '#081426';
        $themeAccentSubtle = '#F0F4F8';
        $themeHeroBg = 'linear-gradient(135deg, #0F2544 0%, #162a4a 100%)';
    }
@endphp
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --accent: {{ $themeAccent }};
            --accent-lt: {{ $themeAccentLt }};
            --accent-dk: {{ $themeAccentDk }};
            --accent-subtle: {{ $themeAccentSubtle }};
            --hero-bg: {{ $themeHeroBg }};

            --gold: #c9a84c;
            --gold-lt: #e8c96d;
            --gold-dk: #7a5c18;
            --green: #1a6b4a;
            --red: #c83a3a;
            --text: #1c1c1e;
            --muted: #6b7280;
            --border: #ede8dc;
            --bg: #fdfaf4;
            --card: #ffffff;
            --head-bg: #fbf9f4;
            --strip: #faf8f5;
        }

        .doc-previews {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 5px;
        }

        .doc-thumb-container {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 4px;
            background: var(--card);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
            width: 75px;
            text-align: center;
            cursor: pointer;
        }

        .doc-thumb-container:hover {
            transform: scale(1.05);
            border-color: var(--accent);
        }

        .doc-thumb {
            width: 65px;
            height: 45px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #f0f0f0;
        }

        .doc-label {
            font-size: 9px;
            font-weight: 600;
            color: var(--muted);
            margin-top: 3px;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
            width: 100%;
        }

        .pdf-badge {
            width: 65px;
            height: 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #ffebee;
            color: #c62828;
            font-size: 9px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 4px;
            border: 1px solid #ffcdd2;
            cursor: pointer;
        }

        .pdf-badge i {
            font-size: 18px;
            margin-bottom: 2px;
        }

        .gallery-img-trigger {
            cursor: pointer;
            transition: opacity .15s;
        }

        .gallery-img-trigger:hover {
            opacity: 0.85;
        }

        .gallery-pdf-trigger {
            cursor: pointer;
            display: block;
            width: 100%;
            height: 250px;
            background: #ffebee;
            color: #c62828;
            border-radius: 4px;
            border: 1px solid #ffcdd2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            gap: 6px;
        }

        .gallery-pdf-trigger i {
            font-size: 30px;
        }

        .doc-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(10, 8, 3, 0.82);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .doc-modal-overlay.show {
            display: flex;
        }

        .doc-modal-box {
            background: var(--card);
            border-radius: 14px;
            max-width: 90vw;
            max-height: 90vh;
            width: 720px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .doc-modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            background: var(--head-bg);
        }

        .doc-modal-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--gold-dk);
            text-transform: uppercase;
            letter-spacing: .8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .doc-modal-close {
            background: none;
            border: none;
            font-size: 22px;
            color: var(--muted);
            cursor: pointer;
            line-height: 1;
            padding: 4px 8px;
            border-radius: 6px;
        }

        .doc-modal-close:hover {
            background: rgba(0, 0, 0, 0.06);
            color: var(--text);
        }

        .doc-modal-body {
            padding: 18px;
            overflow: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f1e8;
            flex: 1;
            min-height: 200px;
        }

        .doc-modal-body img {
            max-width: 100%;
            max-height: 70vh;
            object-fit: contain;
            border-radius: 6px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .doc-modal-body embed {
            width: 100%;
            height: 70vh;
            border-radius: 6px;
        }

        .doc-modal-foot {
            padding: 12px 18px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .doc-modal-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 20px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            border: none;
            text-decoration: none;
            letter-spacing: .2px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dk) 100%);
            color: #fff;
        }

        .doc-modal-btn:hover {
            filter: brightness(1.1);
        }

        .pdf-preview-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(10, 8, 3, 0.85);
            z-index: 10001;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .pdf-preview-overlay.show {
            display: flex;
        }

        .pdf-preview-box {
            background: var(--card);
            border-radius: 14px;
            width: 900px;
            max-width: 95vw;
            height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .pdf-preview-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            background: var(--head-bg);
        }

        .pdf-preview-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--gold-dk);
            text-transform: uppercase;
            letter-spacing: .8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pdf-preview-body {
            flex: 1;
            background: #4a4a4a;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .pdf-preview-body iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .pdf-preview-loading {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 13px;
        }

        .pdf-preview-foot {
            padding: 12px 18px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            .costing-section.costing-hidden {
                display: none !important;
            }

            @page {
                margin: 8mm 10mm;
            }
        }

        body {
            background-color: var(--bg);
            background-image:
                
                radial-gradient(circle at 25px 25px, rgba(201, 168, 76, 0.10) 2px, transparent 2px),
                radial-gradient(circle at 75px 75px, rgba(201, 168, 76, 0.10) 2px, transparent 2px),
                radial-gradient(circle at 75px 25px, rgba(201, 168, 76, 0.06) 1.5px, transparent 1.5px),
                radial-gradient(circle at 25px 75px, rgba(201, 168, 76, 0.06) 1.5px, transparent 1.5px),
                
                repeating-linear-gradient(45deg, rgba(201, 168, 76, 0.04) 0, rgba(201, 168, 76, 0.04) 1px, transparent 1px, transparent 50px),
                repeating-linear-gradient(-45deg, rgba(201, 168, 76, 0.04) 0, rgba(201, 168, 76, 0.04) 1px, transparent 1px, transparent 50px);
            background-size: 100px 100px, 100px 100px, 100px 100px, 100px 100px, 100px 100px, 100px 100px;
            color: var(--text);
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            min-height: 100vh;
        }

        .hero {
            position: relative;
            min-height: 230px;
            overflow: hidden;
            display: flex;
            align-items: flex-end;
            background: var(--hero-bg);
        }

        .hero-tex {
            position: absolute;
            inset: 0;
            background-image:
                repeating-linear-gradient(45deg, rgba(201, 168, 76, 0.04) 0, rgba(201, 168, 76, 0.04) 1px, transparent 1px, transparent 28px),
                repeating-linear-gradient(-45deg, rgba(201, 168, 76, 0.04) 0, rgba(201, 168, 76, 0.04) 1px, transparent 1px, transparent 28px);
        }

        .hero-glow {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 80% at 10% 70%, rgba(201, 168, 76, 0.14) 0%, transparent 60%),
                radial-gradient(ellipse 35% 45% at 85% 25%, rgba(201, 168, 76, 0.07) 0%, transparent 55%);
        }

        .hero-kaaba {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.18;
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            padding: 20px 32px 22px;
            width: 100%;
        }

        .hero-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            flex-wrap: wrap;
            gap: 10px;
        }

       
        .hero-logo-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-img-box {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(201, 168, 76, 0.25);
            border-radius: 10px;
            padding: 6px 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .logo-img-box img {
            height: 48px;
            width: auto;
            max-width: 140px;
            object-fit: contain;
            display: block;
            filter: brightness(1.15) drop-shadow(0 2px 8px rgba(201, 168, 76, 0.4));
        }

        .logo-text-wrap {
            display: flex;
            flex-direction: column;
        }

        .logo-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--accent-lt);
            line-height: 1.1;
            letter-spacing: .3px;
        }

        .logo-sub {
            font-size: 9px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: rgba(201, 168, 76, 0.48);
            margin-top: 2px;
        }

        .hero-btns {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 20px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            border: none;
            white-space: nowrap;
            letter-spacing: .2px;
            transition: all .18s;
        }

        .btn-pdf {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dk) 100%);
            color: #fff;
            box-shadow: 0 4px 16px rgba(201, 168, 76, 0.38);
        }

        .btn-pdf:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
        }

        .btn-toggle {
            background: rgba(255, 255, 255, 0.07);
            color: rgba(255, 255, 255, 0.70);
            border: 1px solid rgba(255, 255, 255, 0.14);
        }

        .btn-toggle:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .hero-meta {
            display: flex;
            align-items: flex-end;
            gap: 14px;
        }

        .hero-avatar {
            width: 56px;
            height: 56px;
            border-radius: 13px;
            background: linear-gradient(135deg, var(--gold), #6a4408);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 6px 20px rgba(201, 168, 76, 0.30);
        }

        .hero-info h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 27px;
            font-weight: 700;
            color: #fff;
            line-height: 1.15;
            margin-bottom: 3px;
        }

        .hero-company {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.38);
            margin-bottom: 9px;
        }

        .badge-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .bk-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 11px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        .stats-strip {
            background: var(--strip);
            border-bottom: 1.5px solid var(--border);
            display: flex;
            overflow-x: auto;
        }

        .stat-item {
            padding: 14px 22px;
            border-right: 1px solid var(--border);
            flex: 1;
            min-width: 110px;
            text-align: center;
        }

        .stat-item:last-child {
            border-right: none;
        }

        .stat-label {
            font-size: 9px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 3px;
        }

        .stat-value {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px;
            font-weight: 700;
        }

        .stat-sub {
            font-size: 9px;
            color: var(--muted);
            margin-top: 1px;
        }

        .progress-wrap {
            padding: 11px 32px;
            background: var(--strip);
            border-bottom: 1.5px solid var(--border);
        }

        .progress-labels {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .progress-track {
            height: 6px;
            background: #e0d4b0;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 10px;
            background: linear-gradient(90deg, var(--gold), var(--green));
        }

        .main-body {
            padding: 24px 32px 60px;
        }

        .section-label {
            font-size: 9.5px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1.8px;
            margin: 0 0 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, var(--border), transparent);
        }

        .info-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 16px;
            box-shadow: 0 2px 16px rgba(100, 70, 10, 0.07), 0 1px 4px rgba(100, 70, 10, 0.04);
        }

        .info-card:last-child {
            margin-bottom: 0;
        }

        .row-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .row-2col .info-card {
            margin-bottom: 0;
        }

        .card-head {
            padding: 11px 18px;
            background: var(--head-bg);
            border-bottom: 1px solid var(--border);
            font-size: 10.5px;
            font-weight: 700;
            color: var(--gold-dk);
            text-transform: uppercase;
            letter-spacing: 1.3px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-head .mdi {
            color: var(--gold);
            font-size: 15px;
        }

        .count-pill {
            margin-left: auto;
            background: rgba(201, 168, 76, 0.13);
            color: var(--gold-dk);
            border: 1px solid rgba(201, 168, 76, 0.30);
            border-radius: 12px;
            padding: 1px 10px;
            font-size: 10px;
            font-weight: 600;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table tr {
            border-bottom: 1px solid #f0e5c8;
        }

        .info-table tr:last-child {
            border-bottom: none;
        }

        .info-table tr:hover {
            background: #fdf7e8;
        }

        .info-table td {
            padding: 10px 18px;
            font-size: 13px;
            vertical-align: middle;
        }

        .info-table td:first-child {
            width: 38%;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .5px;
            border-right: 1px solid #f0e5c8;
            background: rgba(250, 243, 220, 0.5);
        }

        .info-table td:last-child {
            color: var(--text);
            font-weight: 400;
            padding-left: 20px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }

        .data-table thead tr {
            border-bottom: 2px solid var(--border);
        }

        .data-table thead th {
            padding: 10px 14px;
            font-size: 10px;
            font-weight: 700;
            color: var(--gold-dk);
            text-transform: uppercase;
            letter-spacing: .6px;
            background: var(--head-bg);
            white-space: nowrap;
            text-align: left;
        }

        .data-table thead th:first-child {
            text-align: center;
        }

        .data-table tbody tr {
            border-bottom: 1px solid #f0e5c8;
        }

        .data-table tbody tr:last-child {
            border-bottom: none;
        }

        .data-table tbody tr:hover td {
            background: #fdf7e8;
        }

        .data-table tbody td {
            padding: 10px 14px;
            vertical-align: middle;
            color: var(--text);
        }

        .data-table tbody td:first-child {
            text-align: center;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 10.5px;
            font-weight: 600;
            white-space: nowrap;
        }

        .flight-route {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            background: linear-gradient(90deg, #fdf6e3, #faf0cc, #fdf6e3);
            border-bottom: 1px solid var(--border);
        }

        .flight-city .city-code {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: 1px;
        }

        .flight-city .city-date {
            font-size: 10.5px;
            color: var(--muted);
            margin-top: 2px;
        }

        .flight-arrow {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .flight-line {
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
        }

        .flight-city.right {
            text-align: right;
        }

        .cost-card {
            background: linear-gradient(135deg, #fefaf0 0%, #f8f0d8 100%);
            border-color: rgba(201, 168, 76, 0.32) !important;
        }

        .cost-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cost-table tr {
            border-bottom: 1px solid #ece0b8;
        }

        .cost-table tr:last-child {
            border-bottom: none;
        }

        .cost-table td {
            padding: 11px 18px;
            font-size: 13px;
            color: var(--text);
        }

        .cost-table td:first-child {
            font-weight: 500;
            width: 50%;
        }

        .cost-table td:nth-child(2) {
            text-align: right;
            color: var(--muted);
            font-size: 11px;
        }

        .cost-table td:last-child {
            text-align: right;
            font-weight: 600;
            width: 25%;
        }

        .cost-total td {
            background: rgba(201, 168, 76, 0.10) !important;
            border-top: 1px solid rgba(201, 168, 76, 0.30) !important;
            border-bottom: 1px solid rgba(201, 168, 76, 0.30) !important;
            font-weight: 700 !important;
        }

        .costing-section.costing-hidden .cost-table {
            display: none;
        }

        .cost-hidden-msg {
            display: none;
            padding: 32px 18px;
            text-align: center;
            color: var(--muted);
            font-size: 13px;
        }

        .costing-section.costing-hidden .cost-hidden-msg {
            display: block;
        }

        .row-tv {
            display: grid;
            gap: 16px;
            margin-bottom: 16px;
        }

        .row-tv.both {
            grid-template-columns: 1fr 1fr;
        }

        .row-tv .info-card {
            margin-bottom: 0;
        }

        @media (max-width: 860px) {

            .row-2col,
            .row-tv.both {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 580px) {

            .hero-content,
            .progress-wrap,
            .main-body {
                padding-left: 14px;
                padding-right: 14px;
            }

            .stat-item {
                padding: 12px 10px;
            }

            .doc-modal-box {
                width: 95vw;
            }

            .pdf-preview-box {
                width: 100vw;
                height: 100vh;
                border-radius: 0;
            }
        }

        #pdf-loading {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(10, 8, 3, 0.75);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 14px;
        }

        #pdf-loading.show {
            display: flex;
        }

        .pdf-spinner {
            width: 44px;
            height: 44px;
            border: 3px solid rgba(201, 168, 76, 0.2);
            border-top: 3px solid var(--gold);
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .pdf-loading-text {
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            color: rgba(201, 168, 76, 0.8);
            letter-spacing: .5px;
        }
    </style>
</head>

<body>

    <div id="pdf-loading">
        <div class="pdf-spinner"></div>
        <div class="pdf-loading-text">Preparing PDF…</div>
    </div>

    <div class="doc-modal-overlay no-print" id="docModalOverlay" onclick="closeDocModalOnOverlay(event)">
        <div class="doc-modal-box">
            <div class="doc-modal-head">
                <div class="doc-modal-title"><i class="mdi mdi-file-image" id="docModalIcon"></i> <span
                        id="docModalTitle">Document</span></div>
                <button class="doc-modal-close" onclick="closeDocModal()">&times;</button>
            </div>
            <div class="doc-modal-body" id="docModalBody">
            </div>
            <div class="doc-modal-foot">
                <a href="#" id="docModalDownload" class="doc-modal-btn" download>
                    <i class="mdi mdi-download"></i> Download
                </a>
            </div>
        </div>
    </div>

    <div class="pdf-preview-overlay no-print" id="pdfPreviewOverlay">
        <div class="pdf-preview-box">
            <div class="pdf-preview-head">
                <div class="pdf-preview-title"><i class="mdi mdi-file-pdf-box"></i> PDF Preview</div>
                <button class="doc-modal-close" onclick="closePdfPreview()">&times;</button>
            </div>
            <div class="pdf-preview-body" id="pdfPreviewBody">
                <div class="pdf-preview-loading" id="pdfPreviewLoading">
                    <div class="pdf-spinner"></div>
                    <div>Generating PDF preview…</div>
                </div>
            </div>
            <div class="pdf-preview-foot">
                <button class="doc-modal-btn" style="background:rgba(0,0,0,0.3)"
                    onclick="closePdfPreview()">Close</button>
                <button class="doc-modal-btn" id="pdfPreviewDownloadBtn" onclick="downloadPDFFromPreview()">
                    <i class="mdi mdi-download"></i> Download PDF
                </button>
            </div>
        </div>
    </div>

    <div class="hero" id="page-top">
        <div class="hero-tex"></div>
        <div class="hero-glow"></div>

        <div class="hero-kaaba">
            <svg viewBox="0 0 320 450" xmlns="http://www.w3.org/2000/svg" fill="none" width="300">
                <ellipse cx="160" cy="432" rx="140" ry="14" fill="rgba(201,168,76,0.2)" />
                <rect x="14" y="168" width="28" height="264" fill="rgba(201,168,76,0.52)" rx="3" />
                <rect x="4" y="148" width="48" height="28" fill="rgba(201,168,76,0.42)" rx="3" />
                <ellipse cx="28" cy="143" rx="20" ry="13" fill="rgba(201,168,76,0.48)" />
                <line x1="28" y1="130" x2="28" y2="92" stroke="rgba(201,168,76,0.72)"
                    stroke-width="2.5" />
                <polygon points="28,76 35,92 21,92" fill="rgba(201,168,76,0.88)" />
                <circle cx="28" cy="70" r="5" fill="rgba(201,168,76,0.6)" />
                <rect x="19" y="188" width="13" height="20" fill="rgba(201,168,76,0.22)" rx="6" />
                <rect x="19" y="224" width="13" height="20" fill="rgba(201,168,76,0.22)" rx="6" />
                <rect x="19" y="260" width="13" height="20" fill="rgba(201,168,76,0.22)" rx="6" />
                <rect x="278" y="168" width="28" height="264" fill="rgba(201,168,76,0.52)" rx="3" />
                <rect x="268" y="148" width="48" height="28" fill="rgba(201,168,76,0.42)" rx="3" />
                <ellipse cx="292" cy="143" rx="20" ry="13"
                    fill="rgba(201,168,76,0.48)" />
                <line x1="292" y1="130" x2="292" y2="92" stroke="rgba(201,168,76,0.72)"
                    stroke-width="2.5" />
                <polygon points="292,76 299,92 285,92" fill="rgba(201,168,76,0.88)" />
                <circle cx="292" cy="70" r="5" fill="rgba(201,168,76,0.6)" />
                <rect x="288" y="188" width="13" height="20" fill="rgba(201,168,76,0.22)" rx="6" />
                <rect x="288" y="224" width="13" height="20" fill="rgba(201,168,76,0.22)" rx="6" />
                <rect x="288" y="260" width="13" height="20" fill="rgba(201,168,76,0.22)" rx="6" />
                <rect x="88" y="200" width="144" height="222" fill="rgba(201,168,76,0.09)"
                    stroke="rgba(201,168,76,0.82)" stroke-width="2.5" rx="3" />
                <rect x="88" y="200" width="15" height="222" fill="rgba(201,168,76,0.14)" rx="2" />
                <rect x="217" y="200" width="15" height="222" fill="rgba(201,168,76,0.14)" rx="2" />
                <rect x="88" y="244" width="144" height="38" fill="rgba(201,168,76,0.26)"
                    stroke="rgba(201,168,76,0.55)" stroke-width="1.5" />
                <line x1="102" y1="252" x2="218" y2="252" stroke="rgba(201,168,76,0.62)"
                    stroke-width="1" />
                <line x1="102" y1="258" x2="218" y2="258" stroke="rgba(201,168,76,0.42)"
                    stroke-width="0.8" />
                <line x1="102" y1="264" x2="218" y2="264" stroke="rgba(201,168,76,0.35)"
                    stroke-width="0.8" />
                <line x1="102" y1="270" x2="218" y2="270" stroke="rgba(201,168,76,0.42)"
                    stroke-width="0.8" />
                <rect x="132" y="322" width="56" height="98" fill="rgba(201,168,76,0.16)"
                    stroke="rgba(201,168,76,0.62)" stroke-width="2" rx="3" />
                <rect x="139" y="329" width="20" height="40" fill="rgba(201,168,76,0.12)" rx="2" />
                <rect x="161" y="329" width="20" height="40" fill="rgba(201,168,76,0.12)" rx="2" />
                <circle cx="160" cy="378" r="3.5" fill="rgba(201,168,76,0.5)" />
                <path d="M128,188 Q160,155 192,188" stroke="rgba(201,168,76,0.72)" stroke-width="2" fill="none" />
                <line x1="160" y1="155" x2="160" y2="118" stroke="rgba(201,168,76,0.75)"
                    stroke-width="2.5" />
                <path d="M153,111 Q160,98 167,111 Q160,104 153,111Z" fill="rgba(201,168,76,0.88)" />
                <circle cx="60" cy="55" r="1.8" fill="rgba(201,168,76,0.48)" />
                <circle cx="255" cy="32" r="1.8" fill="rgba(201,168,76,0.48)" />
                <circle cx="118" cy="24" r="2.2" fill="rgba(201,168,76,0.38)" />
                <circle cx="210" cy="60" r="1.5" fill="rgba(201,168,76,0.32)" />
            </svg>
        </div>

        <div class="hero-content">
            <div class="hero-topbar">

                @php
                    $isBookingHajj = ($booking->package_type ?? session('dashboard_package', 'hajj')) === 'hajj';
                @endphp
                <div class="hero-logo-wrap">
                    <div class="logo-img-box">
                        <img src="{{ $isBookingHajj ? asset('assets/images/logo/kgm.png') : asset('assets/images/logo/logo.png') }}"
                            alt="{{ $isBookingHajj ? 'KGM' : 'Dua Travels & Tours' }}"
                            onerror="this.src='{{ asset('assets/images/logo-light.png') }}'">
                    </div>
                    <div class="logo-text-wrap">
                        <div class="logo-name">{{ $isBookingHajj ? 'KGM' : 'Dua Travels & Tours' }}</div>
                        <div class="logo-sub">Booking Detail</div>
                    </div>
                </div>

                <div class="hero-btns no-print">
                    <button class="btn btn-pdf" onclick="openPdfPreview()">
                        <i class="mdi mdi-file-eye-outline"></i> Preview / Download PDF
                    </button>
                    <button class="btn btn-toggle" id="toggleCostBtn" onclick="toggleCosting()">
                        <i class="mdi mdi-eye-off-outline" id="toggleCostIcon"></i>
                        <span id="toggleCostText">Hide Costing</span>
                    </button>
                </div>
            </div>

            <div class="hero-meta">
                @php
                    $displayName = $booking->company->name ?? $booking->company->company_name ?? $booking->client->name ?? 'N/A';
                    $pkgDisplay  = $booking->package ? ($booking->package->name . ($booking->package->code ? ' (' . $booking->package->code . ')' : '')) : $booking->package_name;
                @endphp
                <div class="hero-avatar">
                    {{ strtoupper(substr($displayName, 0, 1)) }}
                </div>
                <div class="hero-info">
                    <h1>{{ $displayName }}</h1>
                    @if ($booking->company)
                        <div class="hero-company"><i class="mdi mdi-domain me-1"></i>Company Booking</div>
                    @elseif ($booking->client->company_name ?? false)
                        <div class="hero-company">{{ $booking->client->company_name }}</div>
                    @endif
                    <div class="badge-row">
                        @php
                            $stC = [
                                'pending' => ['rgba(224,155,60,0.18)', 'rgba(224,155,60,0.4)', '#8a5c10'],
                                'confirmed' => ['rgba(46,204,138,0.18)', 'rgba(46,204,138,0.4)', '#12845e'],
                                'cancelled' => ['rgba(224,92,92,0.18)', 'rgba(224,92,92,0.4)', '#a02828'],
                            ][$booking->status] ?? [
                                'rgba(255,255,255,0.1)',
                                'rgba(255,255,255,0.2)',
                                'rgba(255,255,255,0.62)',
                            ];
                        @endphp
                        @if ($pkgDisplay)
                            <span class="bk-badge"
                                style="background:rgba(201,168,76,0.18);border:1px solid rgba(201,168,76,0.4);color:#e8c96d">
                                <i class="mdi mdi-kaaba"></i>
                                {{ $pkgDisplay }}@if ($booking->package_year) — {{ $booking->package_year }}@endif
                            </span>
                        @endif
                        <span class="bk-badge"
                            style="background:{{ $stC[0] }};border:1px solid {{ $stC[1] }};color:{{ $stC[2] }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                        <span class="bk-badge"
                            style="background:rgba(201,168,76,0.14);border:1px solid rgba(201,168,76,0.32);color:#c9a84c">
                            <i class="mdi mdi-account-group"></i> {{ $booking->no_of_pax }} Pax
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $percent = $booking->total_amount > 0 ? min(100, ($booking->total_received / $booking->total_amount) * 100) : 0;
    @endphp

    <div class="stats-strip">
        <div class="stat-item">
            <div class="stat-label">Total Amount</div>
            <div class="stat-value" style="color:var(--gold)">{{ number_format($booking->total_amount, 0) }}</div>
            <div class="stat-sub">PKR</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Received</div>
            <div class="stat-value" style="color:var(--green)">{{ number_format($booking->total_received, 0) }}</div>
            <div class="stat-sub">PKR</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Balance</div>
            <div class="stat-value" style="color:{{ $booking->balance > 0 ? 'var(--red)' : 'var(--green)' }}">
                {{ number_format($booking->balance, 0) }}</div>
            <div class="stat-sub">PKR</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Pkg Cost</div>
            <div class="stat-value" style="color:var(--gold)">{{ number_format($booking->package_cost, 0) }}</div>
            <div class="stat-sub">per person</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Pax</div>
            <div class="stat-value" style="color:var(--text)">{{ $booking->no_of_pax }}</div>
            <div class="stat-sub">persons</div>
        </div>
    </div>

    <div class="progress-wrap">
        <div class="progress-labels">
            <span>Payment Progress</span>
            <span style="color:var(--gold-dk);font-weight:600">{{ number_format($percent, 0) }}% Paid</span>
        </div>
        <div class="progress-track">
            <div class="progress-fill" style="width:{{ $percent }}%"></div>
        </div>
    </div>

    <div class="main-body" id="pdf-content">

        <div class="section-label">Traveller Information</div>

        <div class="row-2col">
            <div class="info-card">
                <div class="card-head"><i class="mdi mdi-card-account-details"></i> Booking Details</div>
                <table class="info-table">
                    <tr>
                        <td>Passport #</td>
                        <td>{{ $booking->passport_number ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>CNIC</td>
                        <td>{{ $booking->cnic ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Care Of</td>
                        <td>{{ $booking->care_of ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Phone</td>
                        <td>{{ $booking->phone ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Emergency</td>
                        <td>{{ $booking->emergency_phone ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Voucher #</td>
                        <td><strong style="color:var(--gold-dk)">{{ $booking->voucher_number ?? '—' }}</strong></td>
                    </tr>
                    <tr>
                        <td>Card #</td>
                        <td>{{ $booking->card_number ?? '—' }}</td>
                    </tr>
                </table>
            </div>

            <div class="info-card">
                <div class="card-head"><i class="mdi mdi-airplane"></i> Flight Details</div>
                @if ($booking->departure_date || $booking->arrival_date)
                    <div class="flight-route">
                        <div class="flight-city">
                            <div class="city-code">{{ $booking->departure_flight ?? 'DEP' }}</div>
                            <div class="city-date">
                                {{ $booking->departure_date ? \Carbon\Carbon::parse($booking->departure_date)->format('d M Y') : '—' }}
                                @if ($booking->departure_time)
                                    · {{ $booking->departure_time }}
                                @endif
                            </div>
                        </div>
                        <div class="flight-arrow">
                            <i class="mdi mdi-airplane-takeoff" style="color:var(--gold);font-size:18px"></i>
                            <div class="flight-line"></div>
                            <div style="font-size:10px;color:var(--muted)">{{ $booking->airline ?? '' }}</div>
                        </div>
                        <div class="flight-city right">
                            <div class="city-code">{{ $booking->arrival_flight ?? 'ARR' }}</div>
                            <div class="city-date">
                                {{ $booking->arrival_date ? \Carbon\Carbon::parse($booking->arrival_date)->format('d M Y') : '—' }}
                                @if ($booking->arrival_time)
                                    · {{ $booking->arrival_time }}
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                <table class="info-table">
                    <tr>
                        <td>Dep. Airline</td>
                        <td>{{ $booking->departure_airline ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Dep. Flight #</td>
                        <td>{{ $booking->departure_flight ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Dep. PNR</td>
                        <td><strong style="color:var(--gold-dk)">{{ $booking->departure_pnr ?? '—' }}</strong></td>
                    </tr>
                    <tr>
                        <td>Departure</td>
                        <td>{{ $booking->departure_date ? \Carbon\Carbon::parse($booking->departure_date)->format('d M Y') : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td>Arr. Airline</td>
                        <td>{{ $booking->arrival_airline ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Arr. Flight #</td>
                        <td>{{ $booking->arrival_flight ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Arr. PNR</td>
                        <td><strong style="color:var(--gold-dk)">{{ $booking->arrival_pnr ?? '—' }}</strong></td>
                    </tr>
                    <tr>
                        <td>Return</td>
                        <td>{{ $booking->arrival_date ? \Carbon\Carbon::parse($booking->arrival_date)->format('d M Y') : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td>Flight Ticket</td>
                        <td>
                            @if ($booking->flight_attachment)
                                @php
                                    $fUrl = asset('storage/' . $booking->flight_attachment);
                                    $fIsPdf = Str::endsWith(strtolower($booking->flight_attachment), '.pdf');
                                @endphp
                                <div class="doc-thumb-container"
                                    onclick="openDocModal('{{ $fUrl }}', '{{ $fIsPdf ? 'pdf' : 'image' }}', 'Flight Ticket')">
                                    @if ($fIsPdf)
                                        <div class="pdf-badge"><i class="mdi mdi-file-pdf-box"></i>PDF</div>
                                    @else
                                        <img src="{{ $fUrl }}" class="doc-thumb" alt="Flight Ticket">
                                    @endif
                                    <span class="doc-label">Flight Ticket</span>
                                </div>
                            @else
                                <span style="color:var(--muted)">Not Uploaded</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if (
            $booking->nominee_name ||
                $booking->cnic_front ||
                $booking->cnic_back ||
                $booking->passport_photo ||
                $booking->photo ||
                $booking->medical_certificate)
            <div class="row-2col">
                <div class="info-card">
                    <div class="card-head"><i class="mdi mdi-file-document-multiple"></i> Main Booking Documents</div>
                    <table class="info-table">
                        <tr>
                            <td>CNIC Front</td>
                            <td>
                                @if ($booking->cnic_front)
                                    <div class="doc-thumb-container" style="width: 100px;"
                                        onclick="openDocModal('{{ asset('storage/' . $booking->cnic_front) }}', '{{ Str::endsWith(strtolower($booking->cnic_front), '.pdf') ? 'pdf' : 'image' }}', 'CNIC Front')">
                                        @if (Str::endsWith(strtolower($booking->cnic_front), '.pdf'))
                                            <div class="pdf-badge" style="width: 90px; height: 60px;"><i
                                                    class="mdi mdi-file-pdf-box"></i>PDF File</div>
                                        @else
                                            <img src="{{ asset('storage/' . $booking->cnic_front) }}"
                                                class="doc-thumb" style="width: 90px; height: 60px;"
                                                alt="CNIC Front">
                                        @endif
                                    </div>
                                @else
                                    <span style="color:var(--muted)">Not Uploaded</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>CNIC Back</td>
                            <td>
                                @if ($booking->cnic_back)
                                    <div class="doc-thumb-container" style="width: 100px;"
                                        onclick="openDocModal('{{ asset('storage/' . $booking->cnic_back) }}', '{{ Str::endsWith(strtolower($booking->cnic_back), '.pdf') ? 'pdf' : 'image' }}', 'CNIC Back')">
                                        @if (Str::endsWith(strtolower($booking->cnic_back), '.pdf'))
                                            <div class="pdf-badge" style="width: 90px; height: 60px;"><i
                                                    class="mdi mdi-file-pdf-box"></i>PDF File</div>
                                        @else
                                            <img src="{{ asset('storage/' . $booking->cnic_back) }}"
                                                class="doc-thumb" style="width: 90px; height: 60px;" alt="CNIC Back">
                                        @endif
                                    </div>
                                @else
                                    <span style="color:var(--muted)">Not Uploaded</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Passport First Page</td>
                            <td>
                                @if ($booking->passport_photo)
                                    <div class="doc-thumb-container" style="width: 100px;"
                                        onclick="openDocModal('{{ asset('storage/' . $booking->passport_photo) }}', '{{ Str::endsWith(strtolower($booking->passport_photo), '.pdf') ? 'pdf' : 'image' }}', 'Passport First Page')">
                                        @if (Str::endsWith(strtolower($booking->passport_photo), '.pdf'))
                                            <div class="pdf-badge" style="width: 90px; height: 60px;"><i
                                                    class="mdi mdi-file-pdf-box"></i>PDF File</div>
                                        @else
                                            <img src="{{ asset('storage/' . $booking->passport_photo) }}"
                                                class="doc-thumb" style="width: 90px; height: 60px;" alt="Passport">
                                        @endif
                                    </div>
                                @else
                                    <span style="color:var(--muted)">Not Uploaded</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Picture of Haji</td>
                            <td>
                                @if ($booking->photo)
                                    <div class="doc-thumb-container" style="width: 100px;"
                                        onclick="openDocModal('{{ asset('storage/' . $booking->photo) }}', '{{ Str::endsWith(strtolower($booking->photo), '.pdf') ? 'pdf' : 'image' }}', 'Picture of Haji')">
                                        @if (Str::endsWith(strtolower($booking->photo), '.pdf'))
                                            <div class="pdf-badge" style="width: 90px; height: 60px;"><i
                                                    class="mdi mdi-file-pdf-box"></i>PDF File</div>
                                        @else
                                            <img src="{{ asset('storage/' . $booking->photo) }}" class="doc-thumb"
                                                style="width: 90px; height: 60px;" alt="Photo">
                                        @endif
                                    </div>
                                @else
                                    <span style="color:var(--muted)">Not Uploaded</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Medical Certificate</td>
                            <td>
                                @if ($booking->medical_certificate)
                                    <div class="doc-thumb-container" style="width: 100px;"
                                        onclick="openDocModal('{{ asset('storage/' . $booking->medical_certificate) }}', '{{ Str::endsWith(strtolower($booking->medical_certificate), '.pdf') ? 'pdf' : 'image' }}', 'Medical Certificate')">
                                        @if (Str::endsWith(strtolower($booking->medical_certificate), '.pdf'))
                                            <div class="pdf-badge" style="width: 90px; height: 60px;"><i
                                                    class="mdi mdi-file-pdf-box"></i>PDF File</div>
                                        @else
                                            <img src="{{ asset('storage/' . $booking->medical_certificate) }}"
                                                class="doc-thumb" style="width: 90px; height: 60px;" alt="Medical">
                                        @endif
                                    </div>
                                @else
                                    <span style="color:var(--muted)">Not Uploaded</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="info-card">
                    <div class="card-head"><i class="mdi mdi-account-alert"></i> Main Booking Nominee</div>
                    <table class="info-table">
                        <tr>
                            <td>Nominee Name</td>
                            <td>{{ $booking->nominee_name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Relation with Haji</td>
                            <td>{{ $booking->nominee_relation ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Nominee CNIC</td>
                            <td>{{ $booking->nominee_cnic ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Nominee Mobile</td>
                            <td>{{ $booking->nominee_mobile ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        @endif

        @if ($booking->persons->count())
            <div class="info-card">
                <div class="card-head">
                    <i class="mdi mdi-account-group"></i> Travelling Persons
                    <span class="count-pill">{{ $booking->persons->count() }}</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Passenger Details</th>
                            <th>Passport & Personal Info</th>
                            <th>Contact & Location</th>
                            <th>Documents</th>
                            <th>Nominee</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($booking->persons as $person)
                            <tr>
                                <td><span class="pill"
                                        style="background:rgba(201,168,76,0.12);color:var(--gold-dk);border:1px solid rgba(201,168,76,0.28)">{{ $loop->iteration }}</span>
                                </td>
                                <td>
                                    <div style="font-size:12px; line-height:1.4;">
                                        <strong style="color:var(--text); font-size:13px;">{{ $person->full_name }}</strong>
                                        @if($person->given_name || $person->surname)
                                            <div style="color:var(--muted); font-size:11px;">
                                                Given: {{ $person->given_name ?? '—' }} | Surname: {{ $person->surname ?? '—' }}
                                            </div>
                                        @endif
                                        @if($person->father_name)
                                            <div style="color:var(--muted); font-size:11px;">
                                                S/O, D/O: <strong>{{ $person->father_name }}</strong>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:12px; line-height:1.4;">
                                        <div><strong>Pass #:</strong> {{ $person->passport_number ?? '—' }}</div>
                                        <div>
                                            <strong>DOB:</strong> {{ $person->dob ? \Carbon\Carbon::parse($person->dob)->format('d M Y') : '—' }}
                                            @if($person->gender)
                                                · <span class="badge bg-secondary" style="font-size:10px;">{{ ucfirst($person->gender) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:12px; line-height:1.4;">
                                        <div><strong>CNIC:</strong> {{ $person->cnic ?? '—' }}</div>
                                        <div><strong>Phone:</strong> {{ $person->phone ?? '—' }}</div>
                                        <div>
                                            <strong>City:</strong> {{ $person->city ?? '—' }}
                                            @if($person->blood_group)
                                                · <span class="badge bg-danger" style="font-size:10px;">{{ $person->blood_group }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="doc-previews">
                                        @if ($person->cnic_front)
                                            <div class="doc-thumb-container"
                                                onclick="openDocModal('{{ asset('storage/' . $person->cnic_front) }}', '{{ Str::endsWith(strtolower($person->cnic_front), '.pdf') ? 'pdf' : 'image' }}', 'CNIC Front — {{ $person->full_name }}')">
                                                @if (Str::endsWith(strtolower($person->cnic_front), '.pdf'))
                                                    <div class="pdf-badge"><i class="mdi mdi-file-pdf-box"></i>PDF
                                                    </div>
                                                @else
                                                    <img src="{{ asset('storage/' . $person->cnic_front) }}"
                                                        class="doc-thumb" alt="CNIC Front">
                                                @endif
                                                <span class="doc-label">CNIC Front</span>
                                            </div>
                                        @endif

                                        @if ($person->cnic_back)
                                            <div class="doc-thumb-container"
                                                onclick="openDocModal('{{ asset('storage/' . $person->cnic_back) }}', '{{ Str::endsWith(strtolower($person->cnic_back), '.pdf') ? 'pdf' : 'image' }}', 'CNIC Back — {{ $person->full_name }}')">
                                                @if (Str::endsWith(strtolower($person->cnic_back), '.pdf'))
                                                    <div class="pdf-badge"><i class="mdi mdi-file-pdf-box"></i>PDF
                                                    </div>
                                                @else
                                                    <img src="{{ asset('storage/' . $person->cnic_back) }}"
                                                        class="doc-thumb" alt="CNIC Back">
                                                @endif
                                                <span class="doc-label">CNIC Back</span>
                                            </div>
                                        @endif

                                        @if ($person->passport_photo)
                                            <div class="doc-thumb-container"
                                                onclick="openDocModal('{{ asset('storage/' . $person->passport_photo) }}', '{{ Str::endsWith(strtolower($person->passport_photo), '.pdf') ? 'pdf' : 'image' }}', 'Passport — {{ $person->full_name }}')">
                                                @if (Str::endsWith(strtolower($person->passport_photo), '.pdf'))
                                                    <div class="pdf-badge"><i class="mdi mdi-file-pdf-box"></i>PDF
                                                    </div>
                                                @else
                                                    <img src="{{ asset('storage/' . $person->passport_photo) }}"
                                                        class="doc-thumb" alt="Passport">
                                                @endif
                                                <span class="doc-label">Passport</span>
                                            </div>
                                        @endif

                                        @if ($person->photo)
                                            <div class="doc-thumb-container"
                                                onclick="openDocModal('{{ asset('storage/' . $person->photo) }}', '{{ Str::endsWith(strtolower($person->photo), '.pdf') ? 'pdf' : 'image' }}', 'Haji Photo — {{ $person->full_name }}')">
                                                @if (Str::endsWith(strtolower($person->photo), '.pdf'))
                                                    <div class="pdf-badge"><i class="mdi mdi-file-pdf-box"></i>PDF
                                                    </div>
                                                @else
                                                    <img src="{{ asset('storage/' . $person->photo) }}"
                                                        class="doc-thumb" alt="Haji Photo">
                                                @endif
                                                <span class="doc-label">Haji Photo</span>
                                            </div>
                                        @endif

                                        @if ($person->medical_certificate)
                                            <div class="doc-thumb-container"
                                                onclick="openDocModal('{{ asset('storage/' . $person->medical_certificate) }}', '{{ Str::endsWith(strtolower($person->medical_certificate), '.pdf') ? 'pdf' : 'image' }}', 'Medical Cert — {{ $person->full_name }}')">
                                                @if (Str::endsWith(strtolower($person->medical_certificate), '.pdf'))
                                                    <div class="pdf-badge"><i class="mdi mdi-file-pdf-box"></i>PDF
                                                    </div>
                                                @else
                                                    <img src="{{ asset('storage/' . $person->medical_certificate) }}"
                                                        class="doc-thumb" alt="Medical">
                                                @endif
                                                <span class="doc-label">Medical Cert</span>
                                            </div>
                                        @endif

                                        @if (
                                            !$person->cnic_front &&
                                                !$person->cnic_back &&
                                                !$person->passport_photo &&
                                                !$person->photo &&
                                                !$person->medical_certificate)
                                            <span style="color:var(--muted)">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if ($person->nominee_name)
                                        <div style="font-size:11px; line-height: 1.3;">
                                            <div><strong>Name:</strong> {{ $person->nominee_name }}</div>
                                            <div><strong>Rel:</strong> {{ $person->nominee_relation ?? '—' }}</div>
                                            <div><strong>CNIC:</strong> {{ $person->nominee_cnic ?? '—' }}</div>
                                            <div><strong>Mob:</strong> {{ $person->nominee_mobile ?? '—' }}</div>
                                        </div>
                                    @else
                                        <span style="color:var(--muted)">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($booking->persons->count())
            @php
                $hasAnyDocs = false;
                foreach ($booking->persons as $person) {
                    if (
                        $person->cnic_front ||
                        $person->cnic_back ||
                        $person->passport_photo ||
                        $person->photo ||
                        $person->medical_certificate
                    ) {
                        $hasAnyDocs = true;
                        break;
                    }
                }
            @endphp
            @if ($hasAnyDocs)
                <div class="section-label">Passenger Uploaded Documents Gallery</div>
                @foreach ($booking->persons as $person)
                    @if (
                        $person->cnic_front ||
                            $person->cnic_back ||
                            $person->passport_photo ||
                            $person->photo ||
                            $person->medical_certificate)
                        <div class="info-card">
                            <div class="card-head">
                                <i class="mdi mdi-file-image"></i> Documents for Passenger #{{ $loop->iteration }}:
                                {{ $person->full_name }}
                            </div>
                            <div
                                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; padding: 15px;">
                                @if ($person->cnic_front)
                                    <div
                                        style="border: 1px solid var(--border); border-radius: 8px; padding: 10px; background: #fff; text-align: center;">
                                        <div
                                            style="font-weight: 600; font-size: 12px; margin-bottom: 8px; color: var(--accent-dk);">
                                            CNIC Front</div>
                                        @if (Str::endsWith(strtolower($person->cnic_front), '.pdf'))
                                            <div class="gallery-pdf-trigger"
                                                onclick="openDocModal('{{ asset('storage/' . $person->cnic_front) }}', 'pdf', 'CNIC Front — {{ $person->full_name }}')">
                                                <i class="mdi mdi-file-pdf-box"></i> Click to view PDF
                                            </div>
                                        @else
                                            <img src="{{ asset('storage/' . $person->cnic_front) }}"
                                                class="gallery-img-trigger"
                                                style="max-width: 100%; max-height: 250px; object-fit: contain; border-radius: 4px;"
                                                alt="CNIC Front"
                                                onclick="openDocModal('{{ asset('storage/' . $person->cnic_front) }}', 'image', 'CNIC Front — {{ $person->full_name }}')">
                                        @endif
                                    </div>
                                @endif

                                @if ($person->cnic_back)
                                    <div
                                        style="border: 1px solid var(--border); border-radius: 8px; padding: 10px; background: #fff; text-align: center;">
                                        <div
                                            style="font-weight: 600; font-size: 12px; margin-bottom: 8px; color: var(--accent-dk);">
                                            CNIC Back</div>
                                        @if (Str::endsWith(strtolower($person->cnic_back), '.pdf'))
                                            <div class="gallery-pdf-trigger"
                                                onclick="openDocModal('{{ asset('storage/' . $person->cnic_back) }}', 'pdf', 'CNIC Back — {{ $person->full_name }}')">
                                                <i class="mdi mdi-file-pdf-box"></i> Click to view PDF
                                            </div>
                                        @else
                                            <img src="{{ asset('storage/' . $person->cnic_back) }}"
                                                class="gallery-img-trigger"
                                                style="max-width: 100%; max-height: 250px; object-fit: contain; border-radius: 4px;"
                                                alt="CNIC Back"
                                                onclick="openDocModal('{{ asset('storage/' . $person->cnic_back) }}', 'image', 'CNIC Back — {{ $person->full_name }}')">
                                        @endif
                                    </div>
                                @endif

                                @if ($person->passport_photo)
                                    <div
                                        style="border: 1px solid var(--border); border-radius: 8px; padding: 10px; background: #fff; text-align: center;">
                                        <div
                                            style="font-weight: 600; font-size: 12px; margin-bottom: 8px; color: var(--accent-dk);">
                                            Passport First Page</div>
                                        @if (Str::endsWith(strtolower($person->passport_photo), '.pdf'))
                                            <div class="gallery-pdf-trigger"
                                                onclick="openDocModal('{{ asset('storage/' . $person->passport_photo) }}', 'pdf', 'Passport — {{ $person->full_name }}')">
                                                <i class="mdi mdi-file-pdf-box"></i> Click to view PDF
                                            </div>
                                        @else
                                            <img src="{{ asset('storage/' . $person->passport_photo) }}"
                                                class="gallery-img-trigger"
                                                style="max-width: 100%; max-height: 250px; object-fit: contain; border-radius: 4px;"
                                                alt="Passport"
                                                onclick="openDocModal('{{ asset('storage/' . $person->passport_photo) }}', 'image', 'Passport — {{ $person->full_name }}')">
                                        @endif
                                    </div>
                                @endif

                                @if ($person->photo)
                                    <div
                                        style="border: 1px solid var(--border); border-radius: 8px; padding: 10px; background: #fff; text-align: center;">
                                        <div
                                            style="font-weight: 600; font-size: 12px; margin-bottom: 8px; color: var(--accent-dk);">
                                            Picture of Haji</div>
                                        @if (Str::endsWith(strtolower($person->photo), '.pdf'))
                                            <div class="gallery-pdf-trigger"
                                                onclick="openDocModal('{{ asset('storage/' . $person->photo) }}', 'pdf', 'Haji Photo — {{ $person->full_name }}')">
                                                <i class="mdi mdi-file-pdf-box"></i> Click to view PDF
                                            </div>
                                        @else
                                            <img src="{{ asset('storage/' . $person->photo) }}"
                                                class="gallery-img-trigger"
                                                style="max-width: 100%; max-height: 250px; object-fit: contain; border-radius: 4px;"
                                                alt="Haji Photo"
                                                onclick="openDocModal('{{ asset('storage/' . $person->photo) }}', 'image', 'Haji Photo — {{ $person->full_name }}')">
                                        @endif
                                    </div>
                                @endif

                                @if ($person->medical_certificate)
                                    <div
                                        style="border: 1px solid var(--border); border-radius: 8px; padding: 10px; background: #fff; text-align: center;">
                                        <div
                                            style="font-weight: 600; font-size: 12px; margin-bottom: 8px; color: var(--accent-dk);">
                                            Medical Fitness Certificate</div>
                                        @if (Str::endsWith(strtolower($person->medical_certificate), '.pdf'))
                                            <div class="gallery-pdf-trigger"
                                                onclick="openDocModal('{{ asset('storage/' . $person->medical_certificate) }}', 'pdf', 'Medical Certificate — {{ $person->full_name }}')">
                                                <i class="mdi mdi-file-pdf-box"></i> Click to view PDF
                                            </div>
                                        @else
                                            <img src="{{ asset('storage/' . $person->medical_certificate) }}"
                                                class="gallery-img-trigger"
                                                style="max-width: 100%; max-height: 250px; object-fit: contain; border-radius: 4px;"
                                                alt="Medical Certificate"
                                                onclick="openDocModal('{{ asset('storage/' . $person->medical_certificate) }}', 'image', 'Medical Certificate — {{ $person->full_name }}')">
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
        @endif

        <div class="section-label">Accommodation & Services</div>

        @if ($booking->hotels->count())
            <div class="info-card">
                <div class="card-head">
                    <i class="mdi mdi-hotel"></i> Accommodation
                    <span class="count-pill">{{ $booking->hotels->count() }}</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Location</th>
                            <th>Hotel Name</th>
                            <th style="text-align:center">Nights</th>
                            <th>Room Type</th>
                            <th style="text-align:center">Rooms</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Voucher</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($booking->hotels as $hotel)
                            @php
                                $lc = [
                                    'makkah' => ['rgba(201,168,76,0.12)', 'rgba(201,168,76,0.32)', '#7a5c18'],
                                    'madinah' => ['rgba(46,204,138,0.12)', 'rgba(46,204,138,0.32)', '#12845e'],
                                    'other' => ['rgba(0,0,0,0.03)', 'rgba(0,0,0,0.08)', 'var(--muted)'],
                                ][$hotel->location] ?? ['rgba(0,0,0,0.03)', 'rgba(0,0,0,0.08)', 'var(--muted)'];
                            @endphp
                            <tr>
                                <td><span class="pill"
                                        style="background:{{ $lc[0] }};border:1px solid {{ $lc[1] }};color:{{ $lc[2] }}">{{ ucfirst($hotel->location) }}</span>
                                </td>
                                <td><strong style="color:var(--text)">{{ $hotel->hotel_name }}</strong></td>
                                <td style="text-align:center">{{ $hotel->no_of_nights }}</td>
                                <td>{{ ucfirst($hotel->room_type) }}</td>
                                <td style="text-align:center">{{ $hotel->no_of_rooms }}</td>
                                <td>{{ $hotel->check_in ? \Carbon\Carbon::parse($hotel->check_in)->format('d M Y') : '—' }}
                                </td>
                                <td>{{ $hotel->check_out ? \Carbon\Carbon::parse($hotel->check_out)->format('d M Y') : '—' }}
                                </td>
                                <td>
                                    @if ($hotel->hotel_voucher)
                                        @php $hvIsPdf = Str::endsWith(strtolower($hotel->hotel_voucher), '.pdf'); @endphp
                                        <div class="doc-thumb-container"
                                            onclick="openDocModal('{{ asset('storage/' . $hotel->hotel_voucher) }}', '{{ $hvIsPdf ? 'pdf' : 'image' }}', 'Hotel Voucher — {{ $hotel->hotel_name }}')">
                                            @if ($hvIsPdf)
                                                <div class="pdf-badge"><i class="mdi mdi-file-pdf-box"></i>PDF</div>
                                            @else
                                                <img src="{{ asset('storage/' . $hotel->hotel_voucher) }}"
                                                    class="doc-thumb" alt="Hotel Voucher">
                                            @endif
                                            <span class="doc-label">Voucher</span>
                                        </div>
                                    @else
                                        <span style="color:var(--muted)">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($booking->transports->count() || $booking->visas->count())
            <div class="row-tv {{ $booking->transports->count() && $booking->visas->count() ? 'both' : '' }}">

                @if ($booking->transports->count())
                    <div class="info-card">
                        <div class="card-head"><i class="mdi mdi-bus"></i> Transportation <span
                                class="count-pill">{{ $booking->transports->count() }}</span></div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Route</th>
                                    <th>Type</th>
                                    <th>Notes</th>
                                    <th>Ticket</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($booking->transports as $t)
                                    <tr>
                                        <td>{{ $t->route }}</td>
                                        <td><span class="pill"
                                                style="background:rgba(120,90,20,0.07);color:var(--muted);border:1px solid var(--border)">{{ ucfirst(str_replace('_', ' ', $t->transport_type)) }}</span>
                                        </td>
                                        <td>{{ $t->notes ?? '—' }}</td>
                                        <td>
                                            @if ($t->transport_ticket)
                                                @php $ttIsPdf = Str::endsWith(strtolower($t->transport_ticket), '.pdf'); @endphp
                                                <div class="doc-thumb-container"
                                                    onclick="openDocModal('{{ asset('storage/' . $t->transport_ticket) }}', '{{ $ttIsPdf ? 'pdf' : 'image' }}', 'Transport Ticket — {{ $t->route }}')">
                                                    @if ($ttIsPdf)
                                                        <div class="pdf-badge"><i class="mdi mdi-file-pdf-box"></i>PDF
                                                        </div>
                                                    @else
                                                        <img src="{{ asset('storage/' . $t->transport_ticket) }}"
                                                            class="doc-thumb" alt="Transport Ticket">
                                                    @endif
                                                    <span class="doc-label">Ticket</span>
                                                </div>
                                            @else
                                                <span style="color:var(--muted)">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if ($booking->visas->count())
                    <div class="info-card">
                        <div class="card-head"><i class="mdi mdi-passport"></i> Visa Details <span
                                class="count-pill">{{ $booking->visas->count() }}</span></div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Passport #</th>
                                    <th>DOB</th>
                                    <th>Send To</th>
                                    <th>Status</th>
                                    <th>Attachment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($booking->visas as $visa)
                                    @php
                                        $vc = [
                                            'pending' => ['rgba(224,155,60,0.12)', 'rgba(224,155,60,0.32)', '#8a5c10'],
                                            'submitted' => [
                                                'rgba(201,168,76,0.12)',
                                                'rgba(201,168,76,0.32)',
                                                '#7a5c18',
                                            ],
                                            'approved' => ['rgba(46,204,138,0.12)', 'rgba(46,204,138,0.32)', '#12845e'],
                                            'rejected' => ['rgba(224,92,92,0.12)', 'rgba(224,92,92,0.32)', '#a02828'],
                                        ][$visa->status] ?? ['rgba(0,0,0,0.03)', 'rgba(0,0,0,0.08)', 'var(--muted)'];
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><strong style="color:var(--text)">{{ $visa->given_name ?? '—' }}</strong>
                                        </td>
                                        <td>{{ $visa->passport_number ?? '—' }}</td>
                                        <td>{{ $visa->date_of_birth ? \Carbon\Carbon::parse($visa->date_of_birth)->format('d M Y') : '—' }}
                                        </td>
                                        <td>{{ ucfirst($visa->send_to ?? '—') }}</td>
                                        <td><span class="pill"
                                                style="background:{{ $vc[0] }};border:1px solid {{ $vc[1] }};color:{{ $vc[2] }}">{{ ucfirst($visa->status) }}</span>
                                        </td>
                                        <td>
                                            @if ($visa->visa_attachment)
                                                @php $vaIsPdf = Str::endsWith(strtolower($visa->visa_attachment), '.pdf'); @endphp
                                                <div class="doc-thumb-container"
                                                    onclick="openDocModal('{{ asset('storage/' . $visa->visa_attachment) }}', '{{ $vaIsPdf ? 'pdf' : 'image' }}', 'Visa — {{ $visa->given_name }}')">
                                                    @if ($vaIsPdf)
                                                        <div class="pdf-badge"><i class="mdi mdi-file-pdf-box"></i>PDF
                                                        </div>
                                                    @else
                                                        <img src="{{ asset('storage/' . $visa->visa_attachment) }}"
                                                            class="doc-thumb" alt="Visa">
                                                    @endif
                                                    <span class="doc-label">Visa</span>
                                                </div>
                                            @else
                                                <span style="color:var(--muted)">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>
        @endif

        <div class="section-label">Financial Summary</div>

        <div class="info-card cost-card costing-section" id="costingSection">
            <div class="card-head" style="background:rgba(201,168,76,0.10);border-color:rgba(201,168,76,0.28)">
                <i class="mdi mdi-cash-multiple"></i> Costing Summary
            </div>
            <table class="cost-table">
                <tr>
                    <td>Package Cost</td>
                    <td>PKR {{ number_format($booking->package_cost, 0) }} × {{ $booking->no_of_pax }} pax</td>
                    <td>PKR {{ number_format($booking->package_cost * $booking->no_of_pax, 0) }}</td>
                </tr>
                <tr>
                    <td>Visa Charges</td>
                    <td></td>
                    <td>PKR {{ number_format($booking->visa_charges, 0) }}</td>
                </tr>
                <tr>
                    <td>Flight Charges</td>
                    <td></td>
                    <td>PKR {{ number_format($booking->flight_charges, 0) }}</td>
                </tr>
                <tr>
                    <td>Other Charges</td>
                    <td></td>
                    <td>PKR {{ number_format($booking->other_charges, 0) }}</td>
                </tr>
                <tr class="cost-total">
                    <td style="color:var(--gold-dk)"><strong>Total Amount</strong></td>
                    <td></td>
                    <td style="color:var(--gold-dk)"><strong>PKR
                            {{ number_format($booking->total_amount, 0) }}</strong></td>
                </tr>
                <tr>
                    <td style="color:var(--green);font-weight:600">Total Received</td>
                    <td></td>
                    <td style="color:var(--green);font-weight:600">PKR
                        {{ number_format($booking->total_received, 0) }}</td>
                </tr>
                <tr class="cost-total">
                    <td style="color:{{ $booking->balance > 0 ? 'var(--red)' : 'var(--green)' }};font-weight:700">
                        Balance Due</td>
                    <td></td>
                    <td
                        style="color:{{ $booking->balance > 0 ? 'var(--red)' : 'var(--green)' }};font-size:15px;font-weight:700">
                        PKR {{ number_format($booking->balance, 0) }}
                    </td>
                </tr>
            </table>
            <div class="cost-hidden-msg">
                <i class="mdi mdi-eye-off-outline"
                    style="font-size:32px;color:var(--border);display:block;margin-bottom:8px"></i>
                Costing is hidden — will not appear in PDF
            </div>
        </div>

    </div>

    <script>
       
        function openDocModal(url, type, title) {
            var overlay = document.getElementById('docModalOverlay');
            var body = document.getElementById('docModalBody');
            var titleEl = document.getElementById('docModalTitle');
            var iconEl = document.getElementById('docModalIcon');
            var downloadBtn = document.getElementById('docModalDownload');

            titleEl.textContent = title || 'Document';
            downloadBtn.href = url;

            var fname = (title || 'document').replace(/[^a-z0-9\-_. ]/gi, '').replace(/\s+/g, '-');
            var ext = url.split('.').pop().split('?')[0];
            downloadBtn.setAttribute('download', fname + '.' + ext);

            if (type === 'pdf') {
                iconEl.className = 'mdi mdi-file-pdf-box';
                body.innerHTML = '<embed src="' + url +
                    '" type="application/pdf" style="width:100%;height:70vh;border-radius:6px;">';
            } else {
                iconEl.className = 'mdi mdi-file-image';
                body.innerHTML = '<img src="' + url + '" alt="' + (title || 'document') + '">';
            }

            overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeDocModal() {
            var overlay = document.getElementById('docModalOverlay');
            overlay.classList.remove('show');
            document.getElementById('docModalBody').innerHTML = '';
            document.body.style.overflow = '';
        }

        function closeDocModalOnOverlay(e) {
            if (e.target.id === 'docModalOverlay') {
                closeDocModal();
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDocModal();
                closePdfPreview();
            }
        });

        var costingVisible = true;

        function toggleCosting() {
            var s = document.getElementById('costingSection');
            var i = document.getElementById('toggleCostIcon');
            var t = document.getElementById('toggleCostText');
            if (costingVisible) {
                s.classList.add('costing-hidden');
                i.className = 'mdi mdi-eye-outline';
                t.textContent = 'Show Costing';
            } else {
                s.classList.remove('costing-hidden');
                i.className = 'mdi mdi-eye-off-outline';
                t.textContent = 'Hide Costing';
            }
            costingVisible = !costingVisible;
        }

        var lastPdfBlobUrl = null;
        var lastPdfFilename = 'booking.pdf';

        function buildPdfOptions() {
            var clientName = '{{ addslashes($booking->client->name ?? 'booking') }}';
            var filename = 'Gulf-Umrah-' + clientName.replace(/\s+/g, '-') + '.pdf';
            lastPdfFilename = filename;

            return {
                margin: [6, 6, 6, 6],
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 0.97
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#fdfaf4',
                    logging: false
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                },
                pagebreak: {
                    mode: ['avoid-all', 'css', 'legacy']
                }
            };
        }

        function openPdfPreview() {
            var overlay = document.getElementById('pdfPreviewOverlay');
            var body = document.getElementById('pdfPreviewBody');
            var loading = document.getElementById('pdfPreviewLoading');

            overlay.classList.add('show');
            document.body.style.overflow = 'hidden';

            body.innerHTML = '';
            body.appendChild(loading);
            loading.style.display = 'flex';

            var btns = document.querySelectorAll('.no-print');
            btns.forEach(function(b) {
                b.style.display = 'none';
            });

            var element = document.getElementById('page-top').parentElement;
            var opt = buildPdfOptions();

            html2pdf().set(opt).from(element).toPdf().output('bloburl').then(function(blobUrl) {
                btns.forEach(function(b) {
                    b.style.display = '';
                });
                lastPdfBlobUrl = blobUrl;

                var iframe = document.createElement('iframe');
                iframe.src = blobUrl;
                body.innerHTML = '';
                body.appendChild(iframe);
            }).catch(function(err) {
                btns.forEach(function(b) {
                    b.style.display = '';
                });
                body.innerHTML =
                    '<div class="pdf-preview-loading" style="position:static;"><i class="mdi mdi-alert-circle-outline" style="font-size:32px"></i><div>PDF preview generate nahi ho saka. Please try again.</div></div>';
                console.error(err);
            });
        }

        function closePdfPreview() {
            var overlay = document.getElementById('pdfPreviewOverlay');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }

        function downloadPDFFromPreview() {
            if (lastPdfBlobUrl) {
                var a = document.createElement('a');
                a.href = lastPdfBlobUrl;
                a.download = lastPdfFilename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            } else {
                
                downloadPDF();
            }
        }

        function downloadPDF() {
            var btns = document.querySelectorAll('.no-print');
            btns.forEach(function(b) {
                b.style.display = 'none';
            });

            var element = document.getElementById('page-top').parentElement;
            var opt = buildPdfOptions();

            html2pdf().set(opt).from(element).save().then(function() {
                btns.forEach(function(b) {
                    b.style.display = '';
                });
            }).catch(function() {
                btns.forEach(function(b) {
                    b.style.display = '';
                });
            });
        }
    </script>

</body>

</html>
