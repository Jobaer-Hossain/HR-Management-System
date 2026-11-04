<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'EasyTeam HRMS') }} - Multi-tenant SaaS HR Management</title>

    <!-- Google Fonts & Bootstrap 5 & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary-blue: #2563eb;
            --primary-blue-hover: #1d4ed8;
            --sidebar-bg: #ffffff;
            --main-bg: #f8fafc;
            --card-border: #f1f5f9;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--main-bg);
            color: var(--text-dark);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* SaaS Sidebar Layout */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            padding: 24px 16px;
        }

        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px 40px;
            max-width: calc(100vw - 260px);
        }

        .guest-shell .sidebar { display: none; }
        .guest-shell .main-content { margin-left: 0; max-width: 100vw; padding: 0; }
        .guest-shell .role-context-strip { display: none !important; }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 20px 16px;
                max-width: 100vw;
            }
        }

        @media (max-width: 576px) {
            .main-content { padding: 14px 12px; }
            .main-content > .d-flex { align-items: stretch !important; }
            .main-content .btn { max-width: 100%; }
            .top-navbar, .page-heading { margin-bottom: 18px !important; }
            .table-responsive { border-radius: 12px; }
            .card, .rounded-4 { border-radius: 14px !important; }
            .sidebar { width: min(86vw, 300px); padding: 18px 14px; }
        }

        /* Sidebar Branding */
        .brand-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 8px 24px 8px;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 1.25rem;
            color: #0f172a;
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        .brand-icon {
            width: 36px;
            height: 36px;
            background: #2563eb;
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        /* Navigation Sections */
        .nav-section-title {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            margin: 16px 8px 8px 8px;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 0.92rem;
            font-weight: 600;
            color: #64748b;
            text-decoration: none;
            transition: all 0.15s ease;
            margin-bottom: 4px;
        }
        .nav-link-custom:hover {
            color: #1e293b;
            background: #f1f5f9;
        }
        .nav-link-custom.active {
            color: #2563eb;
            background: #eff6ff;
            font-weight: 700;
        }
        .nav-link-custom i {
            font-size: 1.15rem;
        }

        /* Upgrade Banner */
        .upgrade-card {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border-radius: 16px;
            padding: 18px;
            color: white;
            margin-top: auto;
            margin-bottom: 16px;
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.35);
        }
        .upgrade-card h6 {
            font-size: 0.88rem;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .upgrade-card p {
            font-size: 0.75rem;
            opacity: 0.9;
            line-height: 1.35;
            margin-bottom: 12px;
        }
        .upgrade-btn {
            background: #ffffff;
            color: #2563eb;
            border: none;
            padding: 6px 14px;
            font-size: 0.78rem;
            font-weight: 700;
            border-radius: 8px;
            width: 100%;
            transition: opacity 0.2s;
        }
        .upgrade-btn:hover {
            opacity: 0.95;
            color: #1d4ed8;
        }

        /* User Profile Pill */
        .user-pill {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            text-decoration: none;
            color: inherit;
        }
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Top Header */
        .top-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }
        .search-bar-wrap {
            position: relative;
            width: 320px;
        }
        .search-bar-wrap input {
            padding-left: 38px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            font-size: 0.88rem;
        }
        .search-bar-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        /* Cards & Components */
        .card-custom {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            transition: all 0.2s ease;
        }
        .card-stat {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }

        /* Shared workspace actions: consistent across admin and employee portals. */
        .workspace-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 16px;
            margin-bottom: 24px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .03);
        }
        .workspace-actions-label {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #0f172a;
            font-size: .82rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .workspace-actions-label i {
            color: #2563eb;
            font-size: 1rem;
        }
        .workspace-action-list {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }
        .workspace-action {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 34px;
            padding: 7px 11px;
            border: 1px solid #dbe4ef;
            border-radius: 9px;
            background: #fff;
            color: #334155;
            font-size: .76rem;
            font-weight: 700;
            text-decoration: none;
            transition: border-color .15s ease, background .15s ease, color .15s ease;
        }
        .workspace-action:hover {
            color: #1d4ed8;
            background: #eff6ff;
            border-color: #bfdbfe;
        }
        .workspace-action.primary {
            color: #fff;
            background: #2563eb;
            border-color: #2563eb;
        }
        .workspace-action.primary:hover {
            color: #fff;
            background: #1d4ed8;
            border-color: #1d4ed8;
        }
        .workspace-action i { font-size: .9rem; }

        @media (max-width: 767.98px) {
            .workspace-actions {
                align-items: stretch;
                flex-direction: column;
                gap: 10px;
                padding: 12px;
                margin-bottom: 18px;
            }
            .workspace-action-list {
                justify-content: stretch;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .workspace-action {
                justify-content: center;
                padding-inline: 8px;
                white-space: nowrap;
            }
        }

        /* Avatar group & Badges */
        .avatar-sm {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }
        .badge-pending {
            background: #fffbeb;
            color: #d97706;
            font-weight: 600;
            border: 1px solid #fef3c7;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
        }
        .badge-approved {
            background: #f0fdf4;
            color: #16a34a;
            font-weight: 600;
            border: 1px solid #dcfce7;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
        }
        .badge-rejected {
            background: #fef2f2;
            color: #dc2626;
            font-weight: 600;
            border: 1px solid #fee2e2;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
        }

        /* Action Buttons */
        .btn-circle-action {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            font-size: 0.82rem;
            border: none;
            transition: transform 0.15s ease;
        }
        .btn-circle-action:hover {
            transform: scale(1.1);
        }
        .btn-circle-reject {
            background: #fee2e2;
            color: #dc2626;
        }
        .btn-circle-approve {
            background: #dcfce7;
            color: #16a34a;
        }
    </style>
    @stack('styles')
</head>
<body class="{{ auth()->check() ? 'authenticated-shell' : 'guest-shell' }}">
    <div class="app-wrapper {{ request()->routeIs('login') || request()->routeIs('register') ? 'justify-content-center align-items-center' : '' }}">
        @if(!request()->routeIs('login') && !request()->routeIs('register'))
        <!-- Sidebar matching Screenshot 1 (EasyTeam) -->
        <aside class="sidebar" id="sidebar">
            <div class="brand-box">
                <a href="{{ route('dashboard') }}" class="brand-logo">
                    <div class="brand-icon">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </div>
                    <span>EasyTeam</span>
                </a>
                <button class="btn btn-sm btn-link text-muted p-0" id="sidebarToggle">
                    <i class="bi bi-layout-sidebar-inset"></i>
                </button>
            </div>

            <!-- Tenant & Role Selection -->
            @php
                $currentRole = auth()->check() ? auth()->user()->role : 'hr_manager';
                $roleMeta = [
                    'super_admin' => ['title' => 'Super Admin', 'icon' => 'bi-shield-lock-fill', 'color' => '#111827', 'name' => 'Super Admin', 'sub' => 'Platform Owner', 'avatar' => ''],
                    'hr_manager' => ['title' => 'HR Admin / Exec', 'icon' => 'bi-shield-check', 'color' => '#2563eb', 'name' => 'William Dolaz', 'sub' => 'HR Manager', 'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&h=100&fit=crop&crop=faces'],
                    'department_manager' => ['title' => 'Department Manager', 'icon' => 'bi-diagram-3-fill', 'color' => '#0891b2', 'name' => 'Robert Fox', 'sub' => 'Engineering Lead', 'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=faces'],
                    'finance' => ['title' => 'Finance & Payroll', 'icon' => 'bi-cash-coin', 'color' => '#059669', 'name' => 'Jacob Jones', 'sub' => 'Payroll Officer', 'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop&crop=faces'],
                    'employee' => ['title' => 'Staff / Self-Service', 'icon' => 'bi-person-circle', 'color' => '#7c3aed', 'name' => 'Cody Fisher', 'sub' => 'Staff Engineer', 'avatar' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&h=100&fit=crop&crop=faces'],
                ];
                $activeRoleData = $roleMeta[$currentRole] ?? $roleMeta['hr_manager'];
            @endphp

            @if($currentRole === 'super_admin')
            <!-- Super Admin role switcher -->
            <div class="dropdown mb-2">
                <button class="btn btn-sm w-100 text-start d-flex align-items-center justify-content-between border shadow-sm" type="button" data-bs-toggle="dropdown" style="border-radius: 10px; font-size: 0.8rem; font-weight: 700; background: #ffffff; border-color: #e2e8f0 !important;">
                    <span class="d-flex align-items-center gap-2 text-truncate">
                        <i class="bi {{ $activeRoleData['icon'] }}" style="color: {{ $activeRoleData['color'] }}; font-size: 1rem;"></i>
                        <span>{{ $activeRoleData['title'] }}</span>
                    </span>
                    <i class="bi bi-chevron-down text-muted" style="font-size: 0.72rem;"></i>
                </button>
                <ul class="dropdown-menu shadow-lg w-100 border-0 p-2" style="font-size: 0.82rem; border-radius: 12px;">
                    <li><h6 class="dropdown-header px-2 text-uppercase fw-bold text-muted" style="font-size: 0.68rem; letter-spacing: 0.05em;">Switch Active Role View</h6></li>
                    <li>
                        <form action="{{ route('role.switch') }}" method="POST">
                            @csrf
                            <input type="hidden" name="role" value="hr_manager">
                            <button type="submit" class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2 {{ $currentRole === 'hr_manager' ? 'active fw-bold' : '' }}">
                                <i class="bi bi-shield-check text-primary"></i>
                                <div>
                                    <div class="fw-bold">HR Administrator</div>
                                    <div class="small opacity-75" style="font-size: 0.7rem;">Executive oversight & settings</div>
                                </div>
                            </button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('role.switch') }}" method="POST">
                            @csrf
                            <input type="hidden" name="role" value="department_manager">
                            <button type="submit" class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2 {{ $currentRole === 'department_manager' ? 'active fw-bold' : '' }}">
                                <i class="bi bi-diagram-3-fill text-info"></i>
                                <div>
                                    <div class="fw-bold">Department Manager</div>
                                    <div class="small opacity-75" style="font-size: 0.7rem;">Team attendance & leave approvals</div>
                                </div>
                            </button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('role.switch') }}" method="POST">
                            @csrf
                            <input type="hidden" name="role" value="finance">
                            <button type="submit" class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2 {{ $currentRole === 'finance' ? 'active fw-bold' : '' }}">
                                <i class="bi bi-cash-coin text-success"></i>
                                <div>
                                    <div class="fw-bold">Finance & Payroll</div>
                                    <div class="small opacity-75" style="font-size: 0.7rem;">Tax, PF & BEFTN bank batch</div>
                                </div>
                            </button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('role.switch') }}" method="POST">
                            @csrf
                            <input type="hidden" name="role" value="employee">
                            <button type="submit" class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2 {{ $currentRole === 'employee' ? 'active fw-bold' : '' }}">
                                <i class="bi bi-person-circle text-purple" style="color: #7c3aed;"></i>
                                <div>
                                    <div class="fw-bold">Staff / Self-Service</div>
                                    <div class="small opacity-75" style="font-size: 0.7rem;">Face check-in, shifts & payslips</div>
                                </div>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

            <!-- Super Admin tenant selector -->
            <div class="dropdown mb-3">
                <button class="btn btn-light btn-sm w-100 text-start d-flex align-items-center justify-content-between border" type="button" data-bs-toggle="dropdown" style="border-radius: 10px; font-size: 0.8rem; font-weight: 600;">
                    <span class="d-flex align-items-center gap-2 text-truncate">
                        <i class="bi bi-building text-primary"></i>
                        {{ session('tenant_name', 'EasyTeam Global Ltd.') }}
                    </span>
                    <i class="bi bi-chevron-down text-muted" style="font-size: 0.75rem;"></i>
                </button>
                <ul class="dropdown-menu shadow-sm w-100 border-0" style="font-size: 0.82rem;">
                    <li><h6 class="dropdown-header">Switch Organization (Multi-Tenant)</h6></li>
                    <li>
                        <form action="{{ route('tenant.switch') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tenant_id" value="1">
                            <button type="submit" class="dropdown-item d-flex align-items-center gap-2">
                                <i class="bi bi-building-check text-success"></i> EasyTeam Global Ltd. (HQ)
                            </button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('tenant.switch') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tenant_id" value="2">
                            <button type="submit" class="dropdown-item d-flex align-items-center gap-2">
                                <i class="bi bi-buildings text-primary"></i> Dhaka Enterprise Corp
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

            <!-- Super Admin quick-login shortcut -->
            <div class="mb-3">
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm w-100 d-flex align-items-center justify-content-between py-2 px-3 fw-bold shadow-sm {{ request()->routeIs('login') ? 'ring-2 ring-primary' : '' }}" style="border-radius: 10px; font-size: 0.82rem; background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-box-arrow-in-right fs-5 text-white"></i>
                        <span class="text-white">Login Option</span>
                    </div>
                    <span class="badge bg-white text-primary" style="font-size: 0.68rem; font-weight: 700;">19 Staff</span>
                </a>
            </div>
            @endif

            <!-- Role-Specific Navigation Links -->
            @if($currentRole === 'department_manager')
                <div class="nav-section-title">MANAGER PORTAL</div>
                <a href="{{ route('dashboard.manager') }}" class="nav-link-custom {{ request()->routeIs('dashboard.manager') ? 'active' : '' }}">
                    <i class="bi bi-diagram-3-fill"></i>
                    <span>Team Dashboard</span>
                </a>
                <a href="{{ route('leaves.calendar') }}" class="nav-link-custom {{ request()->routeIs('leaves.calendar') ? 'active' : '' }}">
                    <i class="bi bi-calendar3-range"></i>
                    <span>Team Calendar</span>
                </a>
                <a href="{{ route('leaves.index') }}" class="nav-link-custom {{ request()->routeIs('leaves.index') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i>
                    <span>Leave Approvals</span>
                </a>
                <a href="{{ route('schedule.index') }}" class="nav-link-custom {{ request()->routeIs('schedule*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-week"></i>
                    <span>Shift Planner</span>
                </a>
                <a href="{{ route('attendance.face') }}" class="nav-link-custom {{ request()->routeIs('attendance.face') ? 'active' : '' }}">
                    <i class="bi bi-camera-video"></i>
                    <span>Facial Scanner</span>
                </a>
                <a href="{{ route('attendance.mobile') }}" class="nav-link-custom {{ request()->routeIs('attendance.mobile') ? 'active' : '' }}">
                    <i class="bi bi-person-check"></i>
                    <span>Self Check In/Out</span>
                </a>
                <div class="nav-section-title">SELF-SERVICE</div>
                <a href="{{ route('employee.dashboard') }}" class="nav-link-custom {{ request()->routeIs('employee.dashboard*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i>
                    <span>My Staff Profile</span>
                </a>

            @elseif($currentRole === 'finance')
                <div class="nav-section-title">FINANCE & AUDIT</div>
                <a href="{{ route('dashboard.finance') }}" class="nav-link-custom {{ request()->routeIs('dashboard.finance') ? 'active' : '' }}">
                    <i class="bi bi-cash-coin"></i>
                    <span>Finance Dashboard</span>
                </a>
                <a href="{{ route('payrolls.index') }}" class="nav-link-custom {{ request()->routeIs('payrolls.index') ? 'active' : '' }}">
                    <i class="bi bi-credit-card"></i>
                    <span>Disbursement Runs</span>
                </a>
                <a href="{{ route('payrolls.generate') }}" class="nav-link-custom {{ request()->routeIs('payrolls.generate') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle"></i>
                    <span>Process Payroll</span>
                </a>
                <a href="{{ route('employees.index') }}" class="nav-link-custom {{ request()->routeIs('employees*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Salary Master List</span>
                </a>
                <div class="nav-section-title">SELF-SERVICE</div>
                <a href="{{ route('employee.dashboard') }}" class="nav-link-custom {{ request()->routeIs('employee.dashboard*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i>
                    <span>My Staff Profile</span>
                </a>

            @elseif($currentRole === 'employee')
                <div class="nav-section-title">MY WORKSPACE</div>
                <a href="{{ route('employee.dashboard') }}" class="nav-link-custom {{ request()->routeIs('employee.dashboard*') || request()->routeIs('portal*') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i>
                    <span>Employee Dashboard</span>
                </a>
                <a href="{{ route('attendance.mobile') }}" class="nav-link-custom {{ request()->routeIs('attendance.mobile') ? 'active' : '' }}">
                    <i class="bi bi-person-check"></i>
                    <span>Self Check In/Out</span>
                </a>
                <a href="{{ route('leaves.apply') }}" class="nav-link-custom {{ request()->routeIs('leaves.apply') ? 'active' : '' }}">
                    <i class="bi bi-send-plus"></i>
                    <span>Apply for Leave</span>
                </a>
                <a href="{{ route('employee.dashboard') }}#shifts" class="nav-link-custom">
                    <i class="bi bi-calendar-week"></i>
                    <span>My Work Shifts</span>
                </a>
                <a href="{{ route('employee.dashboard') }}#payslips" class="nav-link-custom">
                    <i class="bi bi-credit-card"></i>
                    <span>My Payslips</span>
                </a>

            @else
                <!-- Full HR Admin Navigation -->
                <div class="nav-section-title">MAIN MENU</div>
                <a href="{{ route('dashboard.hr') }}" class="nav-link-custom {{ request()->routeIs('dashboard*') && !request()->routeIs('dashboard.manager') && !request()->routeIs('dashboard.finance') && !request()->routeIs('dashboard.employee') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('employees.index') }}" class="nav-link-custom {{ request()->routeIs('employees*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Employees</span>
                </a>
                <a href="{{ route('leaves.index') }}" class="nav-link-custom {{ request()->routeIs('leaves.index') ? 'active' : '' }}">
                    <i class="bi bi-calendar-event"></i>
                    <span>Leave Management</span>
                </a>
                <a href="{{ route('leaves.calendar') }}" class="nav-link-custom {{ request()->routeIs('leaves.calendar') ? 'active' : '' }}">
                    <i class="bi bi-calendar3-range"></i>
                    <span>Leave Calendar</span>
                </a>
                <a href="{{ route('attendance.index') }}" class="nav-link-custom {{ request()->routeIs('attendance.index') ? 'active' : '' }}">
                    <i class="bi bi-fingerprint"></i>
                    <span>Attendance</span>
                </a>
                <a href="{{ route('attendance.face') }}" class="nav-link-custom {{ request()->routeIs('attendance.face') ? 'active' : '' }}">
                    <i class="bi bi-camera-video"></i>
                    <span>Facial Scanner</span>
                </a>
                <a href="{{ route('attendance.mobile') }}" class="nav-link-custom {{ request()->routeIs('attendance.mobile') ? 'active' : '' }}">
                    <i class="bi bi-person-check"></i>
                    <span>Self Check In/Out</span>
                </a>
                <a href="{{ route('schedule.index') }}" class="nav-link-custom {{ request()->routeIs('schedule*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-week"></i>
                    <span>Schedule</span>
                </a>
                <a href="{{ route('payrolls.index') }}" class="nav-link-custom {{ request()->routeIs('payrolls*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card"></i>
                    <span>Payroll</span>
                </a>

                <div class="nav-section-title">SELF-SERVICE</div>
                <a href="{{ route('employee.dashboard') }}" class="nav-link-custom {{ request()->routeIs('employee.dashboard*') || request()->routeIs('portal*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i>
                    <span>Employee Portal</span>
                </a>
            @endif

            <div class="nav-section-title">OTHER</div>
            @if(auth()->user()?->hasPermission('permissions.manage'))
                <a href="{{ route('permissions.index') }}" class="nav-link-custom {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                    <i class="bi bi-person-lock"></i>
                    <span>Role Permissions</span>
                </a>
            @endif
            <a href="#" class="nav-link-custom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-bell"></i>
                    <span>Notifications</span>
                </div>
                <span class="badge rounded-pill bg-danger" style="font-size: 0.68rem; padding: 3px 6px;">99</span>
            </a>
            <a href="{{ route('services') }}" class="nav-link-custom">
                <i class="bi bi-question-circle"></i>
                <span>Help & Support</span>
            </a>
            <a href="{{ route('about') }}" class="nav-link-custom">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </a>

            <!-- Upgrade Banner from Screenshot 1 -->
            <div class="upgrade-card mt-auto">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-people-fill"></i>
                    <h6 class="mb-0">Save 20% on your annual!</h6>
                </div>
                <p>Upgrade to a full year for more value, savings, and uninterrupted access.</p>
                <button class="upgrade-btn">Upgrade Now</button>
            </div>

            <!-- Dynamic User Pill Based on Active Role -->
            <div class="dropdown">
                <a href="#" class="user-pill dropdown-toggle" data-bs-toggle="dropdown">
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ $activeRoleData['avatar'] }}" alt="{{ $activeRoleData['name'] }}" class="user-avatar">
                        <div>
                            <div class="fw-bold" style="font-size: 0.82rem; line-height: 1.2;">{{ $activeRoleData['name'] }}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">{{ $activeRoleData['sub'] }}</div>
                        </div>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: 0.85rem;">
                    <li><h6 class="dropdown-header">Logged In As {{ $activeRoleData['name'] }}</h6></li>
                    @if($currentRole === 'super_admin')
                        <li><a class="dropdown-item fw-bold text-primary" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-2"></i> Login Option (All Accounts)</a></li>
                    @endif
                    <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-person me-2"></i> Role Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('about') }}"><i class="bi bi-sliders me-2"></i> System Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Log Out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </aside>
        @endif

        <!-- Main Content Area -->
        <main class="{{ request()->routeIs('login') || request()->routeIs('register') ? 'w-100 min-vh-100 d-flex align-items-center justify-content-center p-3' : 'main-content' }}" style="{{ request()->routeIs('login') || request()->routeIs('register') ? 'margin-left: 0; max-width: 100vw; background: #fafafa;' : '' }}">
            @if(!request()->routeIs('login') && !request()->routeIs('register'))
            <!-- Top Role Context Strip (SRS Section 6: Role Based Access & Testing Bar) -->
            <div class="role-context-strip d-flex align-items-center justify-content-between p-2 px-3 mb-4 rounded-3 bg-white border shadow-sm flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-light btn-sm d-lg-none border py-1 px-2" id="mobileNavToggleBtn" type="button" title="Toggle Navigation Menu">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <span class="badge" style="background-color: {{ $activeRoleData['color'] }}; color: white; font-size: 0.72rem; padding: 5px 9px;">
                        <i class="bi {{ $activeRoleData['icon'] }} me-1"></i> CURRENT ROLE
                    </span>
                    <span class="fw-bold text-dark small">{{ $activeRoleData['title'] }}</span>
                    <span class="text-muted small d-none d-md-inline">&bull; {{ $activeRoleData['name'] }}</span>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <!-- 1-Click Role Switcher Pills (administrators only) -->
                    @if($currentRole === 'super_admin')
                    <div class="d-flex align-items-center gap-1 flex-wrap role-switcher">
                        <span class="text-muted small fw-semibold me-1 d-none d-lg-inline" style="font-size: 0.75rem;">Switch View:</span>
                        
                        <form action="{{ route('role.switch') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="role" value="hr_manager">
                            <button type="submit" class="btn btn-sm py-1 px-2 {{ $currentRole === 'hr_manager' ? 'btn-primary' : 'btn-light border' }}" style="border-radius: 6px; font-size: 0.75rem; font-weight: 600;">
                                <i class="bi bi-shield-check me-1"></i> HR Admin
                            </button>
                        </form>

                        <form action="{{ route('role.switch') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="role" value="department_manager">
                            <button type="submit" class="btn btn-sm py-1 px-2 {{ $currentRole === 'department_manager' ? 'btn-info text-white' : 'btn-light border' }}" style="border-radius: 6px; font-size: 0.75rem; font-weight: 600;">
                                <i class="bi bi-diagram-3-fill me-1"></i> Dept Manager
                            </button>
                        </form>

                        <form action="{{ route('role.switch') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="role" value="finance">
                            <button type="submit" class="btn btn-sm py-1 px-2 {{ $currentRole === 'finance' ? 'btn-success text-white' : 'btn-light border' }}" style="border-radius: 6px; font-size: 0.75rem; font-weight: 600;">
                                <i class="bi bi-cash-coin me-1"></i> Finance
                            </button>
                        </form>

                        <form action="{{ route('role.switch') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="role" value="employee">
                            <button type="submit" class="btn btn-sm py-1 px-2 {{ $currentRole === 'employee' ? 'btn-dark text-white' : 'btn-light border' }}" style="border-radius: 6px; font-size: 0.75rem; font-weight: 600;">
                                <i class="bi bi-person-circle me-1"></i> Staff Portal
                            </button>
                        </form>
                    </div>
                    @endif

                    @if($currentRole === 'super_admin')
                    <!-- Super Admin-only login shortcut -->
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm px-3 py-1 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" style="border-radius: 8px; font-size: 0.78rem; background: linear-gradient(135deg, #2563eb, #1e40af); border: none;" title="Go to Login Page with All Accounts">
                        <i class="bi bi-person-lock fs-6"></i>
                        <span>Login Option</span>
                    </a>
                    @endif

                    @auth
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm px-3 py-1 fw-bold d-inline-flex align-items-center gap-1" style="border-radius: 8px; font-size: 0.78rem;" title="Sign out">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                    @endauth
                </div>
            </div>
            @endif
            <!-- Toast / Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mobileToggle = document.getElementById('mobileNavToggleBtn');
        const sidebarToggle = document.getElementById('sidebarToggle');

        function toggleSidebar(e) {
            if (e) e.stopPropagation();
            if (sidebar) sidebar.classList.toggle('show');
        }

        if (mobileToggle) mobileToggle.addEventListener('click', toggleSidebar);
        if (sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);

        document.addEventListener('click', function(e) {
            if (sidebar && sidebar.classList.contains('show')) {
                if (!sidebar.contains(e.target) && (!mobileToggle || !mobileToggle.contains(e.target))) {
                    sidebar.classList.remove('show');
                }
            }
        });
    });
    </script>
    @stack('scripts')
</body>
</html>
