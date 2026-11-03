<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HRMS - Enterprise Human Resource Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #0f172a; color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .hero-section { min-height: 85vh; display: flex; align-items: center; }
        .feature-card { background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; transition: transform 0.2s; }
        .feature-card:hover { transform: translateY(-4px); background: rgba(30, 41, 59, 1); }
        .glow-text { text-shadow: 0 0 20px rgba(13, 110, 253, 0.5); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-transparent border-bottom border-secondary border-opacity-25 py-3">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4 text-primary d-flex align-items-center gap-2" href="{{ url('/') }}">
                <i class="bi bi-people-fill"></i> HRMS Portal
            </a>
            <div class="d-flex gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary px-4">
                        <i class="bi bi-speedometer2 me-1"></i> Open Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-light px-4">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-primary px-4">
                        <i class="bi bi-person-plus me-1"></i> Register
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container py-5 text-center">
            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-3 py-2 rounded-pill mb-3">
                <i class="bi bi-shield-check me-1"></i> Laravel 12.70 Enterprise HRMS
            </span>
            <h1 class="display-3 fw-bold mb-3 glow-text">Streamline Your Global Workforce</h1>
            <p class="lead text-secondary mb-5 mx-auto" style="max-width: 680px;">
                End-to-end personnel records, dynamic multi-tier leave approval pipelines, automated payroll generation, and verifiable compensation slips.
            </p>

            <div class="d-flex justify-content-center gap-3 mb-5">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg px-5 shadow">
                        <i class="bi bi-speedometer2 me-2"></i> Launch Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-5 shadow">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Admin Sign In
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-lg px-4">
                        Quick Demo Mode
                    </a>
                @endauth
            </div>

            <!-- Preloaded Credentials Helper -->
            <div class="card mx-auto p-3 text-start mb-5 bg-dark border-secondary border-opacity-50" style="max-width: 480px;">
                <div class="d-flex align-items-center gap-2 mb-2 text-warning fw-semibold small">
                    <i class="bi bi-key-fill"></i> Pre-Seeded Admin Credentials
                </div>
                <div class="small text-secondary">
                    <div><strong>Email:</strong> <code class="text-info">admin@hrms.com</code></div>
                    <div><strong>Password:</strong> <code class="text-info">password123</code></div>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="row g-4 text-start">
                <div class="col-md-4">
                    <div class="feature-card p-4 h-100">
                        <div class="fs-1 text-primary mb-3"><i class="bi bi-person-vcard"></i></div>
                        <h4 class="fw-bold mb-2">Employee Directory</h4>
                        <p class="text-secondary small mb-0">Centralized database for personnel records, departments, designations, contacts, and contract statuses.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card p-4 h-100">
                        <div class="fs-1 text-warning mb-3"><i class="bi bi-calendar2-range"></i></div>
                        <h4 class="fw-bold mb-2">Leave Management</h4>
                        <p class="text-secondary small mb-0">Online application workflow with real-time status updates and one-click administrative approvals/rejections.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card p-4 h-100">
                        <div class="fs-1 text-success mb-3"><i class="bi bi-wallet2"></i></div>
                        <h4 class="fw-bold mb-2">Payroll & Slips</h4>
                        <p class="text-secondary small mb-0">Automated compensation calculation, tax withholdings, allowances, and printable corporate payslips.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="border-top border-secondary border-opacity-25 py-4 text-center text-secondary small">
        <div class="container">
            HR Management System · Powered by Laravel 12 & MySQL · Built by Jobaer Hossain
        </div>
    </footer>
</body>
</html>
