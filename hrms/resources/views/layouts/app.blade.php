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
                padding: 20px;
                max-width: 100vw;
            }
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
<body>
    <div class="app-wrapper">
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

            <!-- Tenant Selection Pill -->
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

            <!-- Navigation Links -->
            <div class="nav-section-title">MAIN MENU</div>
            <a href="{{ route('dashboard') }}" class="nav-link-custom {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
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

            <div class="nav-section-title">OTHER</div>
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

            <!-- User Pill from Screenshot 1 (William Dolaz) -->
            <div class="dropdown">
                <a href="#" class="user-pill dropdown-toggle" data-bs-toggle="dropdown">
                    <div class="d-flex align-items-center gap-2">
                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&h=100&fit=crop&crop=faces" alt="William Dolaz" class="user-avatar">
                        <div>
                            <div class="fw-bold" style="font-size: 0.82rem; line-height: 1.2;">William Dolaz</div>
                            <div class="text-muted" style="font-size: 0.72rem;">HR Manager</div>
                        </div>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: 0.85rem;">
                    <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-person me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('about') }}"><i class="bi bi-sliders me-2"></i> Account Settings</a></li>
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

        <!-- Main Content Area -->
        <main class="main-content">
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
    @stack('scripts')
</body>
</html>
