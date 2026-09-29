<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('assets/images/logo/logo.jpeg') }}">
    
    <title>Dua Travels & Tours</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=DM+Sans:wght@400;500;600&display=swap"
        rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --gold: #0f535e;
            --gold-light: #187280;
            --gold-dim: #0f535e;
            --emerald: #0f535e;
            --emerald-light: #187280;
            --navy: #0f535e;
            --cream: #FAF6EE;
            --border: #d0e3e6;
            --muted: #4e7077;
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
            font-family: 'DM Sans', sans-serif;
            background: white;
        }

        /* ── Full-screen grid ── */
        .shell {
            height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr;
            position: relative;
            overflow: hidden;
        }

        /* Ambient blobs */
        .shell::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background:
                radial-gradient(ellipse 55% 55% at 15% 20%, rgba(201, 168, 76, .13) 0%, transparent 65%),
                radial-gradient(ellipse 45% 50% at 85% 75%, rgba(26, 107, 74, .09) 0%, transparent 60%);
        }

        /* Subtle diagonal lines */
        .shell::after {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background-image:
                repeating-linear-gradient(45deg, transparent, transparent 36px, rgba(201, 168, 76, .022) 36px, rgba(201, 168, 76, .022) 37px),
                repeating-linear-gradient(-45deg, transparent, transparent 36px, rgba(201, 168, 76, .022) 36px, rgba(201, 168, 76, .022) 37px);
        }

        /* ── Topbar ── */
        .topbar {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 36px;
            border-bottom: 1px solid var(--border);
            background: white;
            backdrop-filter: blur(14px);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .logo-mark {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            box-shadow: 0 3px 10px rgba(201, 168, 76, .32);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .logo-name {
            font-family: 'Playfair Display', serif;
            font-size: 15px;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.15;
        }

        .logo-sub {
            font-size: 9.5px;
            color: var(--muted);
            font-weight: 500;
            letter-spacing: .6px;
            text-transform: uppercase;
        }

        .top-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .date-chip {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--gold-dim);
            background: rgba(201, 168, 76, .10);
            border: 1px solid rgba(201, 168, 76, .22);
            padding: 5px 13px;
            border-radius: 50px;
        }

        .logout-btn {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--muted);
            background: #fff;
            border: 1px solid var(--border);
            padding: 5px 13px;
            border-radius: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color .18s, border-color .18s;
        }

        .logout-btn:hover {
            color: var(--navy);
            border-color: #bbb;
        }

        /* ── Main area — perfectly centered ── */
        .main {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 28px;
            padding: 24px 20px;
        }

        /* ── Header text block ── */
        .headline {
            text-align: center;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 1.3px;
            text-transform: uppercase;
            color: var(--gold-dim);
            background: rgba(201, 168, 76, .10);
            border: 1px solid rgba(201, 168, 76, .25);
            padding: 5px 14px;
            border-radius: 50px;
            margin-bottom: 14px;
            animation: up .5s ease both;
        }

        .h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(28px, 3.5vw, 42px);
            font-weight: 800;
            color: var(--navy);
            line-height: 1.15;
            margin-bottom: 10px;
            animation: up .5s .08s ease both;
        }

        .h1 em {
            font-style: italic;
            background: linear-gradient(120deg, var(--gold) 0%, var(--gold-dim) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .tagline {
            font-size: 13.5px;
            color: var(--muted);
            line-height: 1.55;
            animation: up .5s .16s ease both;
        }

        /* ── Cards row ── */
        .cards {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
            animation: up .55s .24s ease both;
        }

        .card {
            width: 270px;
            background: #fff;
            border-radius: 20px;
            border: 1.5px solid var(--border);
            box-shadow: 0 6px 28px rgba(15, 37, 68, .07);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            text-decoration: none;
            transition: transform .28s ease, box-shadow .28s ease, border-color .28s ease;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 52px rgba(15, 37, 68, .12);
        }

        .card.hajj:hover {
            border-color: rgba(15, 83, 94, .8);
        }

        .card.umrah:hover {
            border-color: rgba(216, 72, 22, .8);
        }

        /* Colour band */
        .band {
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        .card.hajj .band {
            background: linear-gradient(135deg, #0f535e, #187280);
        }

        .card.umrah .band {
            background: linear-gradient(135deg, #D84816, #e65c2b);
        }

        .band::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(-45deg, transparent, transparent 7px, rgba(255, 255, 255, .15) 7px, rgba(255, 255, 255, .15) 8px);
        }

        .band-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, .25));
            transition: transform .3s ease;
        }

        .card:hover .band-icon {
            transform: scale(1.12);
        }

        /* Card inner */
        .card-inner {
            padding: 18px 20px 20px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .tag {
            align-self: flex-start;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: .9px;
            text-transform: uppercase;
            padding: 3px 9px;
            border-radius: 50px;
            margin-bottom: 9px;
        }

        .card.hajj .tag {
            background: rgba(15, 83, 94, .12);
            color: #0f535e;
        }

        .card.umrah .tag {
            background: rgba(216, 72, 22, .12);
            color: #D84816;
        }

        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 21px;
            font-weight: 700;
            margin-bottom: 7px;
            text-align: center;
        }

        .card.hajj .card-title {
            color: #0f535e;
        }

        .card.umrah .card-title {
            color: #D84816;
        }

        .card-desc {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.55;
            margin-bottom: 16px;
            flex: 1;
        }

        /* Mini stats */
        .stats {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .stat {
            flex: 1;
            border-radius: 10px;
            padding: 8px 10px;
            text-align: center;
        }

        .card.hajj .stat {
            background: rgba(15, 83, 94, .06);
            border: 1px solid rgba(15, 83, 94, .2);
        }

        .card.umrah .stat {
            background: rgba(216, 72, 22, .06);
            border: 1px solid rgba(216, 72, 22, .2);
        }

        .sv {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 2px;
        }

        .card.hajj .sv {
            color: #0f535e;
        }

        .card.umrah .sv {
            color: #D84816;
        }

        .sl {
            font-size: 9.5px;
            color: var(--muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        /* CTA */
        .cta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 11px 16px;
            border-radius: 11px;
            font-size: 12.5px;
            font-weight: 700;
            color: #fff;
            letter-spacing: .15px;
            transition: filter .2s, transform .2s;
        }

        .card.hajj .cta {
            background: linear-gradient(135deg, #0f535e, #187280);
            box-shadow: 0 4px 14px rgba(15, 83, 94, .33);
        }

        .card.umrah .cta {
            background: linear-gradient(135deg, #D84816, #e65c2b);
            box-shadow: 0 4px 14px rgba(216, 72, 22, .28);
        }

        .cta:hover {
            filter: brightness(1.06);
            transform: scale(1.01);
            color: #fff;
        }

        .cta-arrow {
            width: 17px;
            height: 17px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .22);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }

        /* ── Animations ── */
        @keyframes up {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Responsive ── */
        @media (max-height: 700px) {
            .band {
                height: 70px;
            }

            .band-icon {
                font-size: 36px;
            }

            .card-inner {
                padding: 14px 16px 16px;
            }

            .main {
                gap: 18px;
            }

            .h1 {
                font-size: 26px;
            }
        }

        @media (max-width: 600px) {
            .shell {
                overflow: auto;
            }

            html,
            body {
                overflow: auto;
                height: auto;
            }

            .main {
                padding: 28px 16px 32px;
            }

            .card {
                width: 100%;
                max-width: 320px;
            }

            .topbar {
                padding: 12px 18px;
            }

            .date-chip {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="shell">

        {{-- Topbar --}}
        <header class="topbar">
            <div class="logo">
                <div class="logo-mark">🌙</div>
                <div>
                    <div class="logo-name">Dua Travels & Tours</div>
                    <div class="logo-sub">Management Portal</div>
                </div>
            </div>
            <div class="top-right">
                <span class="date-chip">📅 {{ now()->format('d M Y') }}</span>
                <form method="POST" action="{{ route('logout') }}" style="margin:0">
                    @csrf
                    <button class="logout-btn" type="submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M16 13v-2H7V8l-5 4l5 4v-3zm1-11H9a2 2 0 0 0-2 2v4h2V4h8v16H9v-4H7v4a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2" />
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </header>

        {{-- Main --}}
        <main class="main">

            {{-- Headline --}}
            <div class="headline">
                <div class="eyebrow">✦ Management Dashboard ✦</div>
                <h1 class="h1">Welcome to Dua Travels & Tours</h1>
                <p class="tagline">Select a package to view bookings, revenue & monthly reports.</p>
            </div>

            {{-- Cards --}}
            <div class="cards">

                {{-- Hajj --}}
                <a href="{{ route('dashboard', ['package' => 'hajj', 'year' => now()->year]) }}" class="card hajj">
                    <div class="band">
                        <div class="band-icon">
                            <svg width="56" height="56" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="kaabaBody" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#273238" />
                                        <stop offset="100%" stop-color="#151C20" />
                                    </linearGradient>
                                    <linearGradient id="goldKiswa" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" stop-color="#F59E0B" />
                                        <stop offset="50%" stop-color="#FDE047" />
                                        <stop offset="100%" stop-color="#D97706" />
                                    </linearGradient>
                                    <linearGradient id="kaabaRoof" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" stop-color="#455A64" />
                                        <stop offset="100%" stop-color="#263238" />
                                    </linearGradient>
                                </defs>
                                <ellipse cx="32" cy="56" rx="20" ry="4.5" fill="#000000" fill-opacity="0.3" />
                                <polygon points="16,18 32,10 48,18 32,26" fill="url(#kaabaRoof)" />
                                <polygon points="16,18 32,26 32,50 16,42" fill="#1C2529" />
                                <polygon points="32,26 48,18 48,42 32,50" fill="url(#kaabaBody)" />
                                <polygon points="16,23 32,31 32,34.5 16,26.5" fill="url(#goldKiswa)" />
                                <polygon points="32,31 48,23 48,26.5 32,34.5" fill="url(#goldKiswa)" />
                                <line x1="34" y1="32.2" x2="46" y2="26.2" stroke="#FEF08A" stroke-width="0.8" stroke-dasharray="1.5 1" />
                                <line x1="18" y1="25.2" x2="30" y2="31.2" stroke="#FEF08A" stroke-width="0.8" stroke-dasharray="1.5 1" />
                                <polygon points="36,36 41,33.5 41,45.5 36,48" fill="url(#goldKiswa)" stroke="#B45309" stroke-width="0.5" />
                                <line x1="38.5" y1="34.8" x2="38.5" y2="46.8" stroke="#78350F" stroke-width="0.5" />
                                <polyline points="16,18 32,26 48,18" fill="none" stroke="#78909C" stroke-width="0.8" />
                            </svg>
                        </div>
                    </div>
                    <div class="card-inner">
                        <div class="card-title">Hajj</div>
                        <p class="card-desc">Bookings, clients, revenue & monthly performance at a glance.</p>
                        <div class="stats">
                            <div class="stat">
                                <div class="sv">{{ number_format($hajjCount) }}</div>
                                <div class="sl">Bookings</div>
                            </div>
                            <div class="stat">
                                <div class="sv">
                                    {{ $hajjRevenue >= 1000 ? number_format($hajjRevenue / 1000, 0) . 'K' : number_format($hajjRevenue) }}
                                </div>
                                <div class="sl">Revenue</div>
                            </div>
                        </div>
                        <span class="cta">View Hajj Dashboard <span class="cta-arrow">→</span></span>
                    </div>
                </a>

                {{-- Umrah --}}
                <a href="{{ route('dashboard', ['package' => 'umrah', 'year' => now()->year]) }}" class="card umrah">
                    <div class="band">
                        <div class="band-icon">
                            <svg width="56" height="56" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="greenDome" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#10B981" />
                                        <stop offset="50%" stop-color="#059669" />
                                        <stop offset="100%" stop-color="#047857" />
                                    </linearGradient>
                                    <linearGradient id="goldSpire" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" stop-color="#FDE047" />
                                        <stop offset="100%" stop-color="#D97706" />
                                    </linearGradient>
                                    <linearGradient id="whiteWall" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" stop-color="#FFFFFF" />
                                        <stop offset="100%" stop-color="#E5E7EB" />
                                    </linearGradient>
                                </defs>
                                <ellipse cx="32" cy="56" rx="22" ry="4.5" fill="#000000" fill-opacity="0.25" />
                                <path d="M12 40H52V52H12V40Z" fill="url(#whiteWall)" stroke="#D1D5DB" stroke-width="0.5" />
                                <path d="M16 52V45C16 43.3431 17.3431 42 19 42C20.6569 42 22 43.3431 22 45V52" fill="#047857" fill-opacity="0.25" />
                                <path d="M26 52V45C26 43.3431 27.3431 42 29 42C30.6569 42 32 43.3431 32 45V52" fill="#047857" fill-opacity="0.25" />
                                <path d="M36 52V45C36 43.3431 37.3431 42 39 42C40.6569 42 42 43.3431 42 45V52" fill="#047857" fill-opacity="0.25" />
                                <path d="M20 40H44V34H20V40Z" fill="url(#whiteWall)" stroke="#9CA3AF" stroke-width="0.5" />
                                <line x1="24" y1="34" x2="24" y2="40" stroke="#D1D5DB" stroke-width="0.5" />
                                <line x1="28" y1="34" x2="28" y2="40" stroke="#D1D5DB" stroke-width="0.5" />
                                <line x1="32" y1="34" x2="32" y2="40" stroke="#D1D5DB" stroke-width="0.5" />
                                <line x1="36" y1="34" x2="36" y2="40" stroke="#D1D5DB" stroke-width="0.5" />
                                <line x1="40" y1="34" x2="40" y2="40" stroke="#D1D5DB" stroke-width="0.5" />
                                <path d="M20 34C20 22 32 16 32 16C32 16 44 22 44 34H20Z" fill="url(#greenDome)" />
                                <path d="M24 33C24 24 30 20 32 18.5C29 21 26 26 26 33H24Z" fill="#6EE7B7" opacity="0.6" />
                                <path d="M32 9V16" stroke="url(#goldSpire)" stroke-width="1.8" stroke-linecap="round" />
                                <circle cx="32" cy="12" r="1.5" fill="url(#goldSpire)" />
                                <path d="M33.2 8.5C32.8 7.5 31.8 7 30.8 7.3C29.8 7.6 29.3 8.6 29.6 9.6C29.9 10.6 30.9 11.1 31.9 10.8C31.2 10.8 30.5 10.2 30.4 9.4C30.2 8.6 30.7 7.9 31.5 7.7C32.2 7.5 33 7.9 33.2 8.5Z" fill="url(#goldSpire)" />
                                <path d="M46 52H50V24H46V52Z" fill="url(#whiteWall)" stroke="#D1D5DB" stroke-width="0.5" />
                                <path d="M45 24H51V21H45V24Z" fill="url(#goldSpire)" />
                                <path d="M47 21C47 17 48 15 48 15C48 15 49 17 49 21H47Z" fill="url(#greenDome)" />
                                <path d="M48 11V15" stroke="url(#goldSpire)" stroke-width="1" />
                            </svg>
                        </div>
                    </div>
                    <div class="card-inner">
                        <div class="card-title">Umrah</div>
                        <p class="card-desc">Bookings, clients, revenue & monthly performance at a glance.</p>
                        <div class="stats">
                            <div class="stat">
                                <div class="sv">{{ number_format($umrahCount) }}</div>
                                <div class="sl">Bookings</div>
                            </div>
                            <div class="stat">
                                <div class="sv">
                                    {{ $umrahRevenue >= 1000 ? number_format($umrahRevenue / 1000, 0) . 'K' : number_format($umrahRevenue) }}
                                </div>
                                <div class="sl">Revenue</div>
                            </div>
                        </div>
                        <span class="cta">View Umrah Dashboard <span class="cta-arrow">→</span></span>
                    </div>
                </a>

            </div>
        </main>

    </div>
</body>

</html>
