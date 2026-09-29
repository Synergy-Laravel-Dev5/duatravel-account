<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Change Password | Maxims Group</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="shortcut icon" href="{{ asset('assets/images/logo/logo.png') }}">

    <!-- CSS -->
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

    <style>c:\xampp\htdocs\maxims\resources\views\welcome.blade.php
        /* RIGHT SIDE BACKGROUND */
        .account-page-bg {
            height: 100vh;
            background: url('/assets/images/images1.jfif') center center no-repeat;
            background-size: cover;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* OVERLAY */
        .account-page-bg::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
        }

        /* TEXT */
        .overlay-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #fff;
        }

        .overlay-content h2 {
            font-size: 34px;
            font-weight: 700;
        }

        .overlay-content p {
            font-size: 16px;
        }

        /* LEFT CARD */
        .card {
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .form-control {
            border-radius: 8px;
            height: 45px;
        }

        .btn-primary {
            height: 45px;
            border-radius: 8px;
            font-weight: 600;
        }

        /* Password toggle icon */
        .input-group .form-control {
            border-right: none;
        }

        .input-group .input-group-text {
            background: #fff;
            border-left: none;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            height: 45px;
        }

        .input-group .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }

        .input-group:focus-within .form-control,
        .input-group:focus-within .input-group-text {
            border-color: #0d6efd;
        }

        /* Password strength bar */
        .strength-bar {
            height: 5px;
            border-radius: 4px;
            transition: width 0.4s ease, background-color 0.4s ease;
            width: 0%;
        }

        /* MOBILE */
        @media (max-width: 768px) {
            .account-page-bg {
                height: 250px;
            }
        }
    </style>
</head>

<body class="bg-primary-subtle">

    <div class="account-page">
        <div class="container-fluid p-0">
            <div class="row align-items-center g-0">

                <!-- LEFT SIDE -->
                <div class="col-xl-4">
                    <div class="row">
                        <div class="col-md-8 mx-auto">
                            <div class="">
                                <div class="card-body">
                                    <div class="p-4">

                                        <!-- LOGO -->
                                        <div class="mb-4 text-center">
                                            @php
                                                $isChgHajj = session('dashboard_package', 'hajj') === 'hajj';
                                            @endphp
                                            <img src="{{ $isChgHajj ? asset('assets/images/logo/kgm.png') : asset('assets/images/logo/logo.png') }}"
                                                height="80" style="object-fit: contain; max-width: 100%;">
                                        </div>

                                        <!-- TITLE -->
                                        <div class="text-center mb-3">
                                            <h3>Change Password</h3>
                                            <p>Maxims Group <br> Manage Hajj & Umrah Bookings</p>
                                        </div>

                                        <!-- SUCCESS MESSAGE -->
                                        @if (session('success'))
                                            <div class="alert alert-success">
                                                {{ session('success') }}
                                            </div>
                                        @endif

                                        <!-- ERROR MESSAGES -->
                                        @if ($errors->any())
                                            <div class="alert alert-danger">
                                                <ul class="mb-0">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        <!-- FORM -->
                                        <form method="POST" action="{{ route('change.password.post') }}">
                                            @csrf

                                            {{-- Current Password --}}
                                            <div class="mb-3">
                                                <label class="form-label">Current Password</label>
                                                <div class="input-group">
                                                    <input type="password" id="current_password" name="current_password"
                                                        class="form-control @error('current_password') is-invalid @enderror"
                                                        placeholder="Enter current password" required>
                                                    <span class="input-group-text"
                                                        onclick="togglePassword('current_password', 'icon_current')">
                                                        <i id="icon_current" class="ri-eye-off-line"></i>
                                                    </span>
                                                    @error('current_password')
                                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- New Password --}}
                                            <div class="mb-3">
                                                <label class="form-label">New Password</label>
                                                <div class="input-group">
                                                    <input type="password" id="new_password" name="new_password"
                                                        class="form-control @error('new_password') is-invalid @enderror"
                                                        placeholder="Enter new password" required
                                                        oninput="checkStrength(this.value)">
                                                    <span class="input-group-text"
                                                        onclick="togglePassword('new_password', 'icon_new')">
                                                        <i id="icon_new" class="ri-eye-off-line"></i>
                                                    </span>
                                                    @error('new_password')
                                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                {{-- Password Strength Bar --}}
                                                <div class="mt-2">
                                                    <div class="bg-light rounded" style="height:5px;">
                                                        <div id="strength-bar" class="strength-bar"></div>
                                                    </div>
                                                    <small id="strength-text" class="text-muted"></small>
                                                </div>
                                            </div>

                                            {{-- Confirm Password --}}
                                            <div class="mb-4">
                                                <label class="form-label">Confirm New Password</label>
                                                <div class="input-group">
                                                    <input type="password" id="new_password_confirmation"
                                                        name="new_password_confirmation" class="form-control"
                                                        placeholder="Re-enter new password" required>
                                                    <span class="input-group-text"
                                                        onclick="togglePassword('new_password_confirmation', 'icon_confirm')">
                                                        <i id="icon_confirm" class="ri-eye-off-line"></i>
                                                    </span>
                                                </div>
                                                <small id="match-msg" class="text-muted"></small>
                                            </div>

                                            <button type="submit" class="btn btn-primary w-100">Update
                                                Password</button>

                                            <div class="text-center mt-3">
                                                <a href="{{ route('welcome') }}" class="text-muted small">
                                                    <i class="ri-arrow-left-line"></i> Back to Dashboard
                                                </a>
                                            </div>

                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT SIDE -->
                <div class="col-xl-8">
                    <div class="account-page-bg">
                        <div class="overlay-content">
                            <h2>Welcome to Maxims Group</h2>
                            <p>Manage Hajj &amp; Umrah Bookings</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>

    <script>
        // Toggle password visibility
        function togglePassword(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
            if (field.type === 'password') {
                field.type = 'text';
                icon.className = 'ri-eye-line';
            } else {
                field.type = 'password';
                icon.className = 'ri-eye-off-line';
            }
        }

        // Password strength checker
        function checkStrength(value) {
            const bar = document.getElementById('strength-bar');
            const text = document.getElementById('strength-text');
            let score = 0;

            if (value.length >= 8) score++;
            if (/[A-Z]/.test(value)) score++;
            if (/[0-9]/.test(value)) score++;
            if (/[^A-Za-z0-9]/.test(value)) score++;

            const levels = [{
                    label: '',
                    color: '',
                    width: '0%'
                },
                {
                    label: 'Weak',
                    color: '#dc3545',
                    width: '25%'
                },
                {
                    label: 'Fair',
                    color: '#fd7e14',
                    width: '50%'
                },
                {
                    label: 'Good',
                    color: '#ffc107',
                    width: '75%'
                },
                {
                    label: 'Strong',
                    color: '#198754',
                    width: '100%'
                },
            ];

            const level = value.length === 0 ? levels[0] : levels[score] || levels[1];
            bar.style.width = level.width;
            bar.style.backgroundColor = level.color;
            text.textContent = level.label;
            text.style.color = level.color;
        }

        document.getElementById('new_password_confirmation').addEventListener('input', function() {
            const newPass = document.getElementById('new_password').value;
            const msg = document.getElementById('match-msg');
            if (this.value === '') {
                msg.textContent = '';
            } else if (this.value === newPass) {
                msg.textContent = '✓ Passwords match';
                msg.style.color = '#198754';
            } else {
                msg.textContent = '✗ Passwords do not match';
                msg.style.color = '#dc3545';
            }
        });
    </script>

</body>

</html>
