<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Dua Travels & Tours - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="shortcut icon" href="{{ asset('assets/images/logo/logo.jpeg') }}">

    <!-- CSS -->
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

    <style>
        :root {
            --brand-teal: #134B54;
            --brand-teal-dark: #0A2E34;
            --brand-orange: #F05A24;
            --brand-orange-hover: #D84816;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            width: 100vw;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;

            /* Background Image setup for full screen */
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.45) 0%, rgba(0, 0, 0, 0.8) 100%),
                url('/assets/images/logo/3.jpg');
            background-position: center center;
            background-repeat: no-repeat;
            background-size: 100% 100%;
            /* Complete Full Screen display without cropping */
            background-attachment: fixed;

            padding: 20px;
        }

        /* CENTERED VIP GLASS CARD */
        .login-card {
            width: 100%;
            max-width: 460px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 40px 35px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.2) inset;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        /* TOP ACCENT LINE */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--brand-teal), var(--brand-orange));
        }

        /* LOGO STYLING */
        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-logo img {
            max-height: 110px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
        }

        /* TITLE & SUBTITLE */
        .login-title {
            text-align: center;
            margin-bottom: 25px;
        }

        .login-title h3 {
            font-size: 24px;
            font-weight: 700;
            color: var(--brand-teal-dark);
            margin-bottom: 4px;
        }

        .login-title p {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }

        /* FORM ELEMENTS */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            font-size: 13px;
            color: var(--brand-teal-dark);
            margin-bottom: 8px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            border-radius: 10px;
            height: 50px;
            border: 1.5px solid #E1E8ED;
            padding: 10px 16px;
            font-size: 15px;
            transition: all 0.25s ease;
            background-color: #FAFAFA;
        }

        .form-control:focus {
            background-color: #FFF;
            border-color: var(--brand-teal);
            box-shadow: 0 0 0 4px rgba(19, 75, 84, 0.12);
            outline: none;
        }

        /* PASSWORD CONTAINER */
        .password-wrapper {
            position: relative;
        }

        .toggle-password-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            padding: 6px;
            color: #888;
            cursor: pointer;
            z-index: 10;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .toggle-password-btn:hover {
            color: var(--brand-orange);
        }

        /* VIP BRAND BUTTON */
        .login-btn {
            height: 50px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 16px;
            letter-spacing: 0.5px;
            border: none;
            background: linear-gradient(135deg, var(--brand-orange) 0%, #D84816 100%);
            color: #FFF;
            box-shadow: 0 6px 18px rgba(240, 90, 36, 0.35);
            transition: all 0.3s ease;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(240, 90, 36, 0.45);
            background: linear-gradient(135deg, #F26735 0%, var(--brand-orange) 100%);
            color: #FFF;
        }

        .login-btn:active {
            transform: translateY(0);
        }

        /* FOOTER BRANDING */
        .card-footer-text {
            text-align: center;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #EAEAEA;
            font-size: 12px;
            color: #0f535e;
            font-weight: 500;
        }

        .card-footer-text span {
            color: var(--brand-orange);
            font-weight: 600;
        }

        /* ALERT STYLES */
        .alert {
            border-radius: 10px;
            font-size: 13px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }

            .login-logo img {
                max-height: 90px;
            }
        }

        /* FORGOT PASSWORD LINK STYLING */
        .forgot-password-link {
            font-size: 13px;
            font-weight: 600;
            color: var(--brand-teal);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .forgot-password-link:hover {
            color: var(--brand-orange);
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="login-card">

        <!-- LOGO -->
        <div class="login-logo">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Dua Travels & Tours Logo">
        </div>

        <!-- TITLE -->
        <div class="login-title">
            <h3>Welcome Back</h3>
            <p>Sign in to continue</p>
        </div>

        <!-- ERROR MESSAGES -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- SUCCESS MESSAGE -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- FORM -->
        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <!-- EMAIL -->
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email"
                    class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required
                    placeholder="e.g. admin@duatravels.com" autofocus>

                @error('email')
                    <span class="invalid-feedback d-block mt-1">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <!-- PASSWORD -->
            <div class="form-group">
                <label class="form-label" for="password">Password</label>

                <div class="password-wrapper">
                    <input type="password" id="password" name="password"
                        class="form-control @error('password') is-invalid @enderror" required
                        placeholder="Enter your password" style="padding-right: 48px;">

                    <button type="button" id="togglePassword" class="toggle-password-btn" aria-label="Show password">
                        <!-- Eye Icon -->
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>

                <!-- INPUT KE BELOW / RIGHT ALIGNED LINK -->
                <div class="text-end mt-2">
                    <a href="#" class="forgot-password-link">Forgot Password?</a>
                </div>

                @error('password')
                    <span class="invalid-feedback d-block mt-1">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <!-- LOGIN BUTTON -->
            <button type="submit" class="btn login-btn">
                LOG IN
            </button>

        </form>

        <!-- FOOTER TAGLINE -->
        <div class="card-footer-text">
            Dua Travels & Tours
        </div>

    </div>

    <!-- JS SCRIPTS -->
    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5 c5 0 8.73 3.11 10 7 a10.78 10.78 0 0 1-2.17 3.68" />
                    <path d="M6.61 6.61A10.78 10.78 0 0 0 2 12 c1.27 3.89 5 7 10 7 a10.43 10.43 0 0 0 5.39-1.48" />
                    <line x1="2" y1="2" x2="22" y2="22" />
                `;
                this.setAttribute('aria-label', 'Hide password');
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
                    <circle cx="12" cy="12" r="3" />
                `;
                this.setAttribute('aria-label', 'Show password');
            }
        });
    </script>

</body>

</html>
