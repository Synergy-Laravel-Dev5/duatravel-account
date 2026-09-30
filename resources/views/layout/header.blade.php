<div class="topbar-custom">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">
                <li>
                    <button class="button-toggle-menu nav-link">
                        <i data-feather="menu" class="noti-icon"></i>
                    </button>
                </li>
                <li class="d-none d-lg-block">
                    <h5 class="mb-0">
                        {{ Auth::user()->name }}
                    </h5>
                </li>
            </ul>

            <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">

                <li class="d-none d-lg-block">
                    <div class="position-relative topbar-search">
                        <input type="text" class="form-control bg-light bg-opacity-75 border-light ps-4"
                            placeholder="Search...">
                        <i
                            class="mdi mdi-magnify fs-16 position-absolute text-muted top-50 translate-middle-y ms-2"></i>
                    </div>
                </li>


                @php
                    if (Auth::guard('company')->check()) {
                        $user = Auth::guard('company')->user();
                        $displayName = $user->company->company_name ?? 'Company User';
                        $isCompany = true;
                    } else {
                        $user = Auth::guard('web')->user();
                        $displayName = $user->name ?? 'Admin User';
                        $isCompany = false;
                    }
                @endphp

                <li class="dropdown notification-list topbar-dropdown">
                    <a class="nav-link dropdown-toggle nav-user me-0" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="pro-user-name ms-1">
                            {{ $displayName }}
                            <i class="mdi mdi-chevron-down"></i>
                        </span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end profile-dropdown">

                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">
                                Welcome {{ $displayName }}
                            </h6>
                        </div>

                        <div class="dropdown-divider"></div>

                        @unless ($isCompany)
                            <a href="{{ route('change.password') }}" class="dropdown-item notify-item">
                                <i class="mdi mdi-lock-reset fs-16 align-middle"></i>
                                <span>Change Password</span>
                            </a>
                        @endunless

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item notify-item border-0 bg-transparent">
                                <i class="mdi mdi-location-exit fs-16 align-middle"></i>
                                <span>Logout</span>
                            </button>
                        </form>

                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
<div class="app-sidebar-menu">
    @php
        $activePackage = session('dashboard_package', 'hajj');
        if (isset($package) && is_string($package) && in_array($package, ['hajj', 'umrah'])) {
            $activePackage = $package;
        }
        $isHajjPackage = ($activePackage === 'hajj');
    @endphp

    <div class="logo-box sidebar-logo-box text-center">
        <a class="logo logo-dark" href="{{ route('dashboard') }}">
            <span class="logo-lg">
                @if ($isHajjPackage)
                    <img src="{{ asset('assets/images/logo/kgm.png') }}" alt="KGM Logo"
                        class="sidebar-logo-full kgm-logo-img">
                @else
                    <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Dua Travels Logo"
                        class="sidebar-logo-full dua-logo-img">
                @endif
            </span>
        </a>
    </div>

    <div class="sidebar-menu-scroll" data-simplebar>
        <div id="sidebar-menu">
            <ul id="side-menu">
                <li class="menu-title">Menu</li>
                <li>
                    <a href="{{ route('dashboard') }}">
                        <i data-feather="home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="#sidebarUserManagement" data-bs-toggle="collapse">
                        <i data-feather="users"></i>
                        <span>User Management</span>
                        <span class="menu-arrow"></span>
                    </a>

                    <div class="collapse" id="sidebarUserManagement">
                        <ul class="nav-second-level">

                            @can('role_view')
                                <li>
                                    <a class="tp-link" href="{{ route('role.index') }}">
                                        <i data-feather="shield"></i>
                                        <span>Role</span>
                                    </a>
                                </li>
                            @endcan

                            @can('user_view')
                                <li>
                                    <a class="tp-link" href="{{ route('user.index') }}">
                                        <i data-feather="user"></i>
                                        <span>User</span>
                                    </a>
                                </li>
                            @endcan
                            @can('user_activity_view')
                                <li>
                                    <a class="tp-link" href="{{ route('user_activity.index') }}">
                                        <i data-feather="activity"></i>
                                        <span>User Activity</span>
                                    </a>
                                </li>
                            @endcan

                        </ul>
                    </div>
                </li>

                <li>
                    <a href="#sidebarLeadManagement" data-bs-toggle="collapse">
                        <i data-feather="user-plus"></i>
                        <span>Lead Management</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarLeadManagement">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link" href="{{ route('lead.index') }}">
                                    <i data-feather="user-check"></i>
                                    <span>All Leads</span>
                                </a>
                            </li>
                            @can('client_view')
                                <li>
                                    <a href="{{ route('client.index') }}">
                                        <i data-feather="users"></i>
                                        <span>Clients</span>
                                    </a>
                                </li>
                            @endcan
                            <li>
                                <a class="tp-link" href="{{ route('quotation.index') }}">
                                    <i data-feather="file-text"></i>
                                    <span>Quotations</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('room-inventory.index') }}">
                                    <i data-feather="clipboard"></i>
                                    <span>Rooming List & Stock</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li>
                    <a href="#sidebarBankingManagement" data-bs-toggle="collapse">
                        <i data-feather="dollar-sign"></i>
                        <span>Accounts & Banking</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarBankingManagement">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link" href="{{ route('account.index') }}">
                                    <i data-feather="credit-card"></i>
                                    <span>Accounts</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('bank.index') }}">
                                    <i data-feather="briefcase"></i>
                                    <span>Banks</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('bank-transfer.index') }}">
                                    <i data-feather="repeat"></i>
                                    <span>Bank Transfers</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li>
                    <a href="#sidebarHRManagement" data-bs-toggle="collapse">
                        <i data-feather="users"></i>
                        <span>HR Management</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarHRManagement">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link" href="{{ route('department.index') }}">
                                    <i data-feather="grid"></i>
                                    <span>Departments</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('designation.index') }}">
                                    <i data-feather="award"></i>
                                    <span>Designations</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('staff.index') }}">
                                    <i data-feather="user"></i>
                                    <span>Staff</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li>
                    <a href="{{ route('company.index') }}">
                        <i data-feather="briefcase"></i>
                        <span>Companies</span>
                    </a>
                </li>
                <li>
                    <a href="#sidebarPackageManagement" data-bs-toggle="collapse">
                        <i data-feather="layers"></i>
                        <span>Package Management</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarPackageManagement">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link" href="{{ route('package.index') }}">
                                    <i data-feather="package"></i>
                                    <span>Packages</span>
                                </a>
                            </li>
                            @can('hotel_view')
                                <li>
                                    <a class="tp-link" href="{{ route('hotel.index') }}">
                                        <i data-feather="home"></i>
                                        <span>Hotels</span>
                                    </a>
                                </li>
                            @endcan
                            <li>
                                <a class="tp-link" href="{{ route('room-inventory.index') }}">
                                    <i data-feather="grid"></i>
                                    <span>Room Inventory</span>
                                </a>
                            </li>
                            @can('airline_view')
                                <li>
                                    <a class="tp-link" href="{{ route('airline.index') }}">
                                        <i data-feather="globe"></i>
                                        <span>Airlines</span>
                                    </a>
                                </li>
                            @endcan
                            @can('flight_view')
                                <li>
                                    <a class="tp-link" href="{{ route('flight.index') }}">
                                        <i data-feather="navigation"></i>
                                        <span>Flights</span>
                                    </a>
                                </li>
                            @endcan
                            @can('train_view')
                                <li>
                                    <a class="tp-link" href="{{ route('train.index') }}">
                                        <i data-feather="cpu"></i>
                                        <span>Trains</span>
                                    </a>
                                </li>
                            @endcan
                            @can('vehicle_view')
                                <li>
                                    <a class="tp-link" href="{{ route('vehicle.index') }}">
                                        <i data-feather="truck"></i>
                                        <span>Vehicles</span>
                                    </a>
                                </li>
                            @endcan
                            @can('route_view')
                                <li>
                                    <a class="tp-link" href="{{ route('route.index') }}">
                                        <i data-feather="map-pin"></i>
                                        <span>Routes</span>
                                    </a>
                                </li>
                            @endcan
                            @can('travel_route_view')
                                <li>
                                    <a class="tp-link" href="{{ route('travel-route.index') }}">
                                        <i data-feather="map"></i>
                                        <span>Travel Routes</span>
                                    </a>
                                </li>
                            @endcan
                            <li>
                                <a class="tp-link" href="{{ route('training-session.index') }}">
                                    <i data-feather="book-open"></i>
                                    <span>Training Sessions</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                @can('booking_view')
                    <li>
                        <a href="{{ route('booking.index') }}">
                            <i data-feather="calendar"></i>
                            <span>Booking</span>
                        </a>
                    </li>
                @endcan
                @can('expense_view')
                    <li>
                        <a href="{{ route('expense.index') }}">
                            <i data-feather="dollar-sign"></i>
                            <span>Add Expense</span>
                        </a>
                    </li>
                @endcan
                @can('expense_transaction_view')
                    <li>
                        <a href="{{ route('expense.transaction.index') }}">
                            <i data-feather="credit-card"></i>
                            <span>Expense Transactions</span>
                        </a>
                    </li>
                @endcan

                @can('client_Transactions_view')
                    <li>
                        <a href="{{ route('transaction.index') }}">
                            <i data-feather="list"></i>
                            <span>Transactions</span>
                        </a>
                    </li>
                @endcan
                @can('Ledger_Filter_view')
                    <li>
                        <a href="{{ route('transaction.ledger.filter') }}">
                            <i data-feather="filter"></i>
                            <span>Ledger Filter</span>
                        </a>
                    </li>
                @endcan
                <li>
                    <a href="{{ route('expense.transaction.report.filter') }}">
                        <i data-feather="filter"></i>
                        <span>Expense Filter</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('transaction.company-ledger.filter') }}">
                        <i data-feather="filter"></i>
                        <span>Company Filter</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
