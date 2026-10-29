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

                    $topbarHajjSeasons = \App\Models\Season::hajj()->orderByDesc('year')->get();
                    $topbarUmrahSeasons = \App\Models\Season::umrah()->orderByDesc('year')->get();
                    $topbarCurrSeason = \App\Models\Season::current();
                    $topbarCurrPkg = session('dashboard_package', 'hajj');
                    $topbarCurrYr = (int) session('dashboard_year', date('Y'));
                    $topbarSeasonName = session(
                        'selected_season_name',
                        $topbarCurrSeason ? $topbarCurrSeason->name : strtoupper($topbarCurrPkg) . ' ' . $topbarCurrYr,
                    );
                @endphp

                <!-- Active Season Switcher Dropdown -->
                <li class="dropdown notification-list topbar-dropdown me-2">
                    <a class="nav-link dropdown-toggle py-1 px-3 d-flex align-items-center rounded-3 border"
                        data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false"
                        aria-expanded="false"
                        style="background: rgba(255, 255, 255, 0.16); border-color: rgba(255, 255, 255, 0.35) !important;">
                        <span class="fs-16 me-2">
                            {{ $topbarCurrPkg === 'hajj' ? '🕋' : '🕌' }}
                        </span>
                        <div class="d-none d-sm-inline-block text-start lh-sm me-2">
                            <span class="d-block fw-bold text-white fs-12"
                                style="text-shadow: 0 1px 2px rgba(0,0,0,0.3);">{{ $topbarSeasonName }}</span>
                            <span class="d-block"
                                style="font-size: 10px; color: #e2e8f0;">{{ strtoupper($topbarCurrPkg) }} •
                                {{ $topbarCurrYr }}</span>
                        </div>
                        <i class="mdi mdi-chevron-down text-white fs-14"></i>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end shadow-lg" style="min-width: 270px;">
                        <div
                            class="dropdown-header d-flex justify-content-between align-items-center py-2 px-3 bg-light">
                            <span class="fw-bold text-dark fs-12">SYSTEM SEASON</span>
                            <a href="{{ route('season.index') }}" class="text-primary fs-11 text-decoration-none">
                                <i class="mdi mdi-cog-outline"></i> Manage
                            </a>
                        </div>

                        <!-- Hajj Section -->
                        <div class="dropdown-header text-uppercase fs-11 fw-bold text-muted py-1 px-3">
                            🕋 HAJJ SEASONS
                        </div>
                        @forelse($topbarHajjSeasons as $hs)
                            @php
                                $isThisActive =
                                    session('selected_season_id') == $hs->id ||
                                    (!session('selected_season_id') &&
                                        $topbarCurrPkg === 'hajj' &&
                                        $topbarCurrYr == $hs->year);
                            @endphp
                            <a href="{{ route('season.switch', $hs->id) }}"
                                class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $isThisActive ? 'active bg-primary-subtle text-primary fw-bold' : '' }}">
                                <div>
                                    <div class="fs-13">{{ $hs->name }}</div>
                                    <small class="text-muted fs-11">{{ $hs->hijri_year ?? $hs->year }}</small>
                                </div>
                                @if ($isThisActive)
                                    <span class="badge bg-primary fs-10">Active</span>
                                @elseif($hs->is_active)
                                    <span class="badge bg-light text-muted border fs-10">Default</span>
                                @endif
                            </a>
                        @empty
                            <span class="dropdown-item text-muted fs-12">No Hajj seasons</span>
                        @endforelse

                        <div class="dropdown-divider my-1"></div>

                        <!-- Umrah Section -->
                        <div class="dropdown-header text-uppercase fs-11 fw-bold text-muted py-1 px-3">
                            🕌 UMRAH SEASONS
                        </div>
                        @forelse($topbarUmrahSeasons as $us)
                            @php
                                $isThisActive =
                                    session('selected_season_id') == $us->id ||
                                    (!session('selected_season_id') &&
                                        $topbarCurrPkg === 'umrah' &&
                                        $topbarCurrYr == $us->year);
                            @endphp
                            <a href="{{ route('season.switch', $us->id) }}"
                                class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $isThisActive ? 'active bg-warning-subtle text-dark fw-bold' : '' }}">
                                <div>
                                    <div class="fs-13">{{ $us->name }}</div>
                                    <small class="text-muted fs-11">{{ $us->hijri_year ?? $us->year }}</small>
                                </div>
                                @if ($isThisActive)
                                    <span class="badge bg-warning text-dark fs-10">Active</span>
                                @elseif($us->is_active)
                                    <span class="badge bg-light text-muted border fs-10">Default</span>
                                @endif
                            </a>
                        @empty
                            <span class="dropdown-item text-muted fs-12">No Umrah seasons</span>
                        @endforelse

                        <div class="dropdown-divider my-1"></div>

                        <a href="{{ route('season.create') }}"
                            class="dropdown-item text-center text-primary fw-semibold fs-12 py-2">
                            <i class="mdi mdi-plus-circle-outline me-1"></i> Add New Season
                        </a>
                    </div>
                </li>

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
        $isHajjPackage = $activePackage === 'hajj';
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
                <li class="{{ request()->routeIs('season.*') ? 'menuitem-active' : '' }}">
                    <a href="#sidebarSeasons" data-bs-toggle="collapse"
                        class="{{ request()->routeIs('season.*') ? 'active' : '' }}">
                        <i data-feather="calendar"></i>
                        <span>Seasons</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('season.*') ? 'show' : '' }}" id="sidebarSeasons">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link {{ request()->routeIs('season.index') && request('status') != 'active' && request('status') != 'inactive' ? 'active' : '' }}"
                                    href="{{ route('season.index') }}">
                                    <i data-feather="list"></i>
                                    <span>All Seasons</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link {{ request('status') === 'active' ? 'active' : '' }}"
                                    href="{{ route('season.index', ['status' => 'active']) }}">
                                    <i data-feather="check-circle" class="text-success"></i>
                                    <span>Active Seasons</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link {{ request('status') === 'inactive' ? 'active' : '' }}"
                                    href="{{ route('season.index', ['status' => 'inactive']) }}">
                                    <i data-feather="x-circle" class="text-danger"></i>
                                    <span>Inactive Seasons</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link {{ request()->routeIs('season.create') ? 'active' : '' }}"
                                    href="{{ route('season.create') }}">
                                    <i data-feather="plus-circle"></i>
                                    <span>Add Season</span>
                                </a>
                            </li>
                        </ul>
                    </div>
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
                        <span>Account Management</span>
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
                                <a class="tp-link" href="{{ route('expense.index') }}">
                                    <i data-feather="file-text"></i>
                                    <span>Expense Heads</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('bank.index') }}">
                                    <i data-feather="briefcase"></i>
                                    <span>Banks</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('supplier.index') }}">
                                    <i data-feather="truck"></i>
                                    <span>Suppliers</span>
                                </a>
                            </li>
                            
                            <li>
                                <a class="tp-link" href="{{ route('account.index') }}">
                                    <i data-feather="dollar-sign"></i>
                                    <span>Cash Accounts</span>
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
                            @can('hotel_view')
                                <li>
                                    <a class="tp-link" href="{{ route('hotel.index') }}">
                                        <i data-feather="home"></i>
                                        <span>Hotels</span>
                                    </a>
                                </li>
                            @endcan
                            <li>
                                <a class="tp-link" href="{{ route('room-type.index') }}">
                                    <i data-feather="grid"></i>
                                    <span>Room Types</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('meal-plans.index') }}">
                                    <i data-feather="coffee"></i>
                                    <span>Meal Plans</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('quotation.index') }}">
                                    <i data-feather="file-text"></i>
                                    <span>Quotations</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li>
                    <a href="#sidebarPackageManagement" data-bs-toggle="collapse">
                        <i data-feather="layers"></i>
                        <span>{{ $isHajjPackage ? 'Package Management' : 'Umrah Management' }}</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarPackageManagement">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link" href="{{ route('package.index') }}">
                                    <i data-feather="package"></i>
                                    <span>{{ $isHajjPackage ? 'Packages' : 'Umrah Packages' }}</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('season.index') }}">
                                    <i data-feather="calendar"></i>
                                    <span>Hajj & Umrah Seasons</span>
                                </a>
                            </li>
                            @if ($isHajjPackage)
                                <li>
                                    <a class="tp-link" href="{{ route('room-inventory.index') }}">
                                        <i data-feather="box"></i>
                                        <span>Room Inventory</span>
                                    </a>
                                </li>
                                @if ($isHajjPackage)
                                    <li>
                                        <a class="tp-link" href="{{ route('report.rooming-list') }}">
                                            <i data-feather="clipboard"></i>
                                            <span>Room Allocation Manifest</span>
                                        </a>
                                    </li>
                                @endif

                                <li>
                                    <a class="tp-link" href="{{ route('training-session.index') }}">
                                        <i data-feather="book-open"></i>
                                        <span>Training Sessions</span>
                                    </a>
                                </li>
                            @endif
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

                @if ($isHajjPackage)
                    <li class="{{ request()->routeIs('haji-group.*') ? 'menuitem-active' : '' }}">
                        <a href="{{ route('haji-group.index') }}"
                            class="{{ request()->routeIs('haji-group.*') ? 'active' : '' }}">
                            <i data-feather="users"></i>
                            <span>Haji Pilgrim Groups</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('arrival-group.*') ? 'menuitem-active' : '' }}">
                        <a href="{{ route('arrival-group.index') }}"
                            class="{{ request()->routeIs('arrival-group.*') ? 'active' : '' }}">
                            <i data-feather="arrow-down-left"></i>
                            <span>Arrival Groups</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('departure-group.*') ? 'menuitem-active' : '' }}">
                        <a href="{{ route('departure-group.index') }}"
                            class="{{ request()->routeIs('departure-group.*') ? 'active' : '' }}">
                            <i data-feather="arrow-up-right"></i>
                            <span>Departure Groups</span>
                        </a>
                    </li>
                @endif
                <li>
                    <a href="#sidebarFinanceManagement" data-bs-toggle="collapse">
                        <i data-feather="dollar-sign"></i>
                        <span>Finance</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarFinanceManagement">
                        <ul class="nav-second-level">
                            <li>
                                <a class="tp-link" href="{{ route('expense.transaction.index') }}">
                                    <i data-feather="credit-card"></i>
                                    <span>Transactions</span>
                                </a>
                            </li>


                            <li>
                                <a class="tp-link"
                                    href="{{ route('report.profit-loss', ['package_type' => $isHajjPackage ? 'hajj' : 'umrah']) }}">
                                    <i data-feather="dollar-sign"></i>
                                    <span>Profit & Loss Report</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('bank-transfer.index') }}">
                                    <i data-feather="repeat"></i>
                                    <span>Bank Transfers</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('bank-transfer.report.filter') }}">
                                    <i data-feather="credit-card"></i>
                                    <span>Bank Transaction Report</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('reports.cash') }}">
                                    <i data-feather="dollar-sign"></i>
                                    <span>Cash Report (Cash Book)</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link"
                                    href="{{ route('reports.checkin', ['package_type' => $isHajjPackage ? 'hajj' : 'umrah']) }}">
                                    <i data-feather="calendar"></i>
                                    <span>Check-In Report</span>
                                </a>
                            </li>
                            @if ($isHajjPackage)
                                <li>
                                    <a class="tp-link" href="{{ route('report.rooming-list') }}">
                                        <i data-feather="clipboard"></i>
                                        <span>Room Allocation Manifest</span>
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a class="tp-link" href="{{ route('reports.daily') }}">
                                    <i data-feather="clock"></i>
                                    <span>Daily Sales & Transactions</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('reports.transactions') }}">
                                    <i data-feather="file-text"></i>
                                    <span>All Transactions Report</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('transaction.ledger.filter') }}">
                                    <i data-feather="book"></i>
                                    <span>Ledger Reports</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('expense.transaction.report.filter') }}">
                                    <i data-feather="pie-chart"></i>
                                    <span>All Reports</span>
                                </a>
                            </li>

                            <li>
                                <a class="tp-link" href="{{ route('supplier.ledger.filter') }}">
                                    <i data-feather="book-open"></i>
                                    <span>Supplier Ledger</span>
                                </a>
                            </li>
                            <li>
                                <a class="tp-link" href="{{ route('transaction.company-ledger.filter') }}">
                                    <i data-feather="book-open"></i>
                                    <span>Company Ledger</span>
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
                            <li>
                                <a class="tp-link" href="{{ route('payroll.index') }}">
                                    <i data-feather="dollar-sign"></i>
                                    <span>Payroll / Salary</span>
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
            </ul>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
