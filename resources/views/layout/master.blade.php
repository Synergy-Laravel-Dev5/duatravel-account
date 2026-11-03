<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

@php
    $currentPkg = session('dashboard_package', 'hajj');
    $isHajjActive = ($currentPkg === 'hajj');
@endphp
<head>

    <meta charset="utf-8" />
    <title>@yield('title', 'Dashboard') - {{ $isHajjActive ? 'KGM' : 'Dua Travels' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A fully featured admin theme which can be used to build , CMS, etc." />
    <meta name="author" content="Zoyothemes" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <link rel="shortcut icon" href="{{ $isHajjActive ? asset('assets/images/logo/kgm.png') : asset('assets/images/logo/logo.jpeg') }}">

    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />

    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/libs/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}"
        rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}" rel="stylesheet"
        type="text/css" />

    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />

    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link href="{{ asset('assets/css/custom-theme.css') }}?v={{ filemtime(public_path('assets/css/custom-theme.css')) }}" rel="stylesheet" type="text/css" />
    <style>
        /* Globally remove HTML5 number input spinner up/down arrows */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
        }
        input[type="number"] {
            -moz-appearance: textfield !important;
            appearance: textfield !important;
        }
    </style>
</head>

<body data-menu-color="light" data-sidebar="default">
    <div id="app-layout">


        @include('layout.header')

        @yield('content')

        @include('layout.footer')

        <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
        <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
        <script src="{{ asset('assets/libs/waypoints/lib/jquery.waypoints.min.js') }}"></script>
        <script src="{{ asset('assets/libs/jquery.counterup/jquery.counterup.min.js') }}"></script>
        <script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

        <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

        <script src="{{ asset('assets/apexcharts.com/samples/assets/stock-prices.js') }}"></script>

        <script src="{{ asset('assets/js/pages/crm-dashboard.init.js') }}"></script>

        <script src="{{ asset('assets/js/app.js') }}"></script>

        <script src="{{ asset('assets/js/pages/datatable.init.js') }}"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                if (typeof feather !== "undefined") {
                    feather.replace();
                }

                if (typeof jQuery !== "undefined" && jQuery.fn.select2) {
                    function initAppSelect2() {
                        $('select.select2').each(function() {
                            if (!$(this).hasClass("select2-hidden-accessible")) {
                                $(this).select2({
                                    theme: 'bootstrap-5',
                                    width: '100%',
                                    placeholder: $(this).attr('placeholder') || 'Search & Select...'
                                });
                            }
                        });
                    }

                    initAppSelect2();

                    $(document).on('shown.bs.tab shown.bs.modal shown.bs.collapse', function() {
                        initAppSelect2();
                    });
                }

                $(document).on('input', 'input.person-flight-ticket, input[name*="ticket"], input[id*="ticket"]', function() {
                    if (this.type === 'text' || this.type === 'number') {
                        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13);
                    }
                });

                $('#sidebar-menu a[href]').each(function() {
                    var href = $(this).attr('href');
                    if (href && href !== '#' && !href.startsWith('javascript') && !$(this).attr('data-bs-toggle')) {
                        $(this).attr('target', '_blank');
                    }
                });

                document.addEventListener('wheel', function(e) {
                    if (document.activeElement && document.activeElement.type === 'number') {
                        document.activeElement.blur();
                    }
                }, { passive: true });
            });
        </script>
        @yield('scripts')
        @stack('scripts')
</body>
</html>
