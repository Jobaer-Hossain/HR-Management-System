@extends('layouts.app')

@section('content')
<div class="auth-page-wrapper w-100 py-5 d-flex align-items-center justify-content-center">
    <div class="auth-card-container">
        <!-- Minimalist Black & White Login Card -->
        <div class="card auth-card shadow-sm border bg-white">
            <div class="card-body p-4 p-sm-5">
                
                <!-- Brand Header -->
                <div class="text-center mb-4">
                    <div class="brand-badge mb-3">
                        <i class="bi bi-fingerprint"></i>
                    </div>
                    <h3 class="fw-bolder text-black mb-1 brand-title">Sign In</h3>
                    <p class="text-muted small mb-0">EasyTeam HRMS &bull; Biometric Workforce System</p>
                </div>

                <!-- 1-Click Role Quick-Select (Monochrome Segmented Grid) -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label text-black fw-bold small text-uppercase mb-0" style="font-size: 0.72rem; letter-spacing: 0.05em;">Quick Role Demo</label>
                        <span class="badge bg-black text-white" style="font-size: 0.65rem; font-weight: 600; padding: 3px 7px;">1-Click Fill</span>
                    </div>

                    <div class="role-grid">
                        <button type="button" class="role-pill active" onclick="selectRole('admin@hrms.com', 'password123', this)">
                            <i class="bi bi-shield-check"></i>
                            <span>HR Admin</span>
                        </button>
                        <button type="button" class="role-pill" onclick="selectRole('manager@hrms.com', 'password123', this)">
                            <i class="bi bi-briefcase"></i>
                            <span>Manager</span>
                        </button>
                        <button type="button" class="role-pill" onclick="selectRole('finance@hrms.com', 'password123', this)">
                            <i class="bi bi-cash-stack"></i>
                            <span>Finance</span>
                        </button>
                        <button type="button" class="role-pill" onclick="selectRole('cody.fisher@easyteam.com', 'password123', this)">
                            <i class="bi bi-person"></i>
                            <span>Staff</span>
                        </button>
                        <button type="button" class="role-pill" onclick="selectRole('superadmin@hrms.com', 'password123', this)">
                            <i class="bi bi-star"></i>
                            <span>Super</span>
                        </button>
                    </div>
                </div>

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label text-black fw-semibold small mb-1">Email address</label>
                        <div class="input-group input-group-custom">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input id="email" type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email', 'admin@hrms.com') }}" required autocomplete="email" autofocus placeholder="name@easyteam.com">
                        </div>
                        @error('email')
                            <span class="invalid-feedback d-block mt-1 small" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label text-black fw-semibold small mb-0">Password</label>
                            <span class="text-muted small" style="font-size: 0.72rem;">
                                Default: <code class="text-black bg-light px-1 py-0.5 rounded border">password123</code>
                            </span>
                        </div>
                        <div class="input-group input-group-custom">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input id="password" type="password" class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror" name="password" value="password123" required autocomplete="current-password" placeholder="••••••••">
                            <button type="button" class="btn btn-outline-secondary border-start-0 bg-white text-muted" id="togglePasswordBtn" title="Toggle password visibility">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="invalid-feedback d-block mt-1 small" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check">
                            <input class="form-check-input custom-checkbox" type="checkbox" name="remember" id="remember" checked>
                            <label class="form-check-label small text-muted user-select-none" for="remember">
                                Remember me
                            </label>
                        </div>
                        <a class="text-black text-decoration-none small fw-semibold" href="#" onclick="alert('Demo notice: All accounts use password123'); return false;">
                            Forgot password?
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-submit-black w-100 fw-bold d-flex align-items-center justify-content-center gap-2">
                        <span>Sign In</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <!-- Expandable 19 Employee Credentials List -->
                <div class="mt-4 pt-3 border-top">
                    <button class="btn btn-outline-dark btn-sm w-100 d-flex align-items-center justify-content-between py-2 px-3 fw-semibold bg-white text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#employeeCredentialsList" aria-expanded="false" style="border-radius: 8px; font-size: 0.78rem; border-color: #e4e4e7;">
                        <span><i class="bi bi-people me-2"></i> View all 19 employee logins</span>
                        <i class="bi bi-chevron-down text-muted" id="accordionChevron"></i>
                    </button>

                    <div class="collapse mt-2" id="employeeCredentialsList">
                        <div class="card card-body bg-white border p-0 overflow-hidden" style="border-radius: 8px; max-height: 220px; overflow-y: auto; border-color: #e4e4e7;">
                            <table class="table table-sm align-middle mb-0" style="font-size: 0.74rem;">
                                <thead class="bg-light text-secondary border-bottom">
                                    <tr>
                                        <th class="ps-3 py-2">Name</th>
                                        <th class="py-2">Email</th>
                                        <th class="py-2">Role</th>
                                        <th class="text-end pe-3 py-2">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $demoStaff = [
                                            ['name' => 'William Dolaz', 'email' => 'admin@hrms.com', 'role' => 'HR Admin'],
                                            ['name' => 'Robert Fox', 'email' => 'manager@hrms.com', 'role' => 'Manager'],
                                            ['name' => 'Jacob Jones', 'email' => 'finance@hrms.com', 'role' => 'Finance'],
                                            ['name' => 'Cody Fisher', 'email' => 'cody.fisher@easyteam.com', 'role' => 'Staff'],
                                            ['name' => 'Jenny Wilson', 'email' => 'jenny.wilson@easyteam.com', 'role' => 'Finance'],
                                            ['name' => 'Darrell Steward', 'email' => 'darrell.steward@easyteam.com', 'role' => 'Staff'],
                                            ['name' => 'Brooklyn Simmons', 'email' => 'brooklyn.simmons@easyteam.com', 'role' => 'HR Admin'],
                                            ['name' => 'Dianne Russell', 'email' => 'dianne.russell@easyteam.com', 'role' => 'Finance'],
                                            ['name' => 'Leslie Alexander', 'email' => 'leslie.alexander@easyteam.com', 'role' => 'Staff'],
                                            ['name' => 'Floyd Miles', 'email' => 'floyd.miles@easyteam.com', 'role' => 'Manager'],
                                            ['name' => 'Cameron Williamson', 'email' => 'cameron.williamson@easyteam.com', 'role' => 'Staff'],
                                            ['name' => 'Devon Lane', 'email' => 'devon.lane@easyteam.com', 'role' => 'Finance'],
                                            ['name' => 'Esther Howard', 'email' => 'esther.howard@easyteam.com', 'role' => 'Manager'],
                                            ['name' => 'Ralph Edwards', 'email' => 'ralph.edwards@easyteam.com', 'role' => 'Staff'],
                                            ['name' => 'Kristin Watson', 'email' => 'kristin.watson@easyteam.com', 'role' => 'Finance'],
                                            ['name' => 'Annette Black', 'email' => 'annette.black@easyteam.com', 'role' => 'Finance'],
                                            ['name' => 'Jane Cooper', 'email' => 'jane.cooper@easyteam.com', 'role' => 'Staff'],
                                            ['name' => 'Guy Hawkins', 'email' => 'guy.hawkins@easyteam.com', 'role' => 'Manager'],
                                            ['name' => 'Bessie Cooper', 'email' => 'bessie.cooper@easyteam.com', 'role' => 'Staff'],
                                            ['name' => 'Kathryn Murphy', 'email' => 'kathryn.murphy@easyteam.com', 'role' => 'Manager'],
                                        ];
                                    @endphp
                                    @foreach($demoStaff as $s)
                                        <tr class="border-bottom">
                                            <td class="ps-3 py-1 fw-semibold text-black">{{ $s['name'] }}</td>
                                            <td class="py-1 font-monospace text-muted">{{ $s['email'] }}</td>
                                            <td class="py-1"><span class="badge bg-light text-black border">{{ $s['role'] }}</span></td>
                                            <td class="text-end pe-3 py-1">
                                                <button type="button" class="btn btn-xs btn-dark py-0 px-2" style="font-size: 0.68rem; border-radius: 4px;" onclick="selectRole('{{ $s['email'] }}', 'password123')">
                                                    Fill
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <small class="text-muted" style="font-size: 0.72rem;">&copy; {{ date('Y') }} EasyTeam Global Ltd. All rights reserved.</small>
                </div>

            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Monochrome Black & White Minimalist Design */
    body {
        background-color: #fafafa !important;
    }

    .auth-page-wrapper {
        min-height: 85vh;
    }

    .auth-card-container {
        width: 100%;
        max-width: 480px;
        margin: 0 auto;
    }

    .auth-card {
        border-radius: 16px !important;
        border: 1px solid #e4e4e7 !important;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;
    }

    .brand-badge {
        width: 48px;
        height: 48px;
        background: #000000;
        color: #ffffff;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .brand-title {
        letter-spacing: -0.6px;
    }

    /* Role Pill Selector Grid */
    .role-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 6px;
    }

    @media (max-width: 480px) {
        .role-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .role-pill {
        background: #ffffff;
        border: 1px solid #e4e4e7;
        color: #27272a;
        border-radius: 8px;
        padding: 8px 4px;
        font-size: 0.76rem;
        font-weight: 600;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        cursor: pointer;
        transition: all 0.15s ease;
        user-select: none;
    }

    .role-pill i {
        font-size: 1rem;
        color: #71717a;
        transition: color 0.15s ease;
    }

    .role-pill:hover {
        background: #f4f4f5;
        border-color: #000000;
        color: #000000;
    }

    .role-pill:hover i {
        color: #000000;
    }

    .role-pill.active {
        background: #000000;
        color: #ffffff;
        border-color: #000000;
    }

    .role-pill.active i {
        color: #ffffff;
    }

    /* Input Groups */
    .input-group-custom .input-group-text {
        border-color: #e4e4e7;
        font-size: 0.95rem;
    }

    .input-group-custom .form-control {
        border-color: #e4e4e7;
        font-size: 0.88rem;
        height: 44px;
        color: #09090b;
    }

    .input-group-custom .form-control:focus {
        border-color: #000000;
        box-shadow: 0 0 0 1px #000000;
    }

    .input-group-custom .btn {
        border-color: #e4e4e7;
    }

    .input-group-custom .btn:hover {
        background: #f4f4f5;
        color: #000000;
    }

    /* Black Checkbox */
    .custom-checkbox:checked {
        background-color: #000000;
        border-color: #000000;
    }

    .custom-checkbox:focus {
        border-color: #000000;
        box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.15);
    }

    /* Submit Button (Solid Black) */
    .btn-submit-black {
        background: #000000;
        color: #ffffff;
        border: 1px solid #000000;
        border-radius: 10px;
        height: 46px;
        font-size: 0.92rem;
        transition: all 0.15s ease;
    }

    .btn-submit-black:hover {
        background: #27272a;
        border-color: #27272a;
        color: #ffffff;
        transform: translateY(-1px);
    }

    .btn-submit-black:active {
        background: #3f3f46;
        transform: translateY(0);
    }
</style>
@endpush

@push('scripts')
<script>
function selectRole(email, password, element) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;

    if (element) {
        document.querySelectorAll('.role-pill').forEach(btn => btn.classList.remove('active'));
        element.classList.add('active');
    }

    // Subtle feedback flash on the email field
    const emailInput = document.getElementById('email');
    emailInput.style.borderColor = '#000000';
    setTimeout(() => {
        emailInput.style.borderColor = '#e4e4e7';
    }, 400);
}

document.getElementById('togglePasswordBtn').addEventListener('click', function() {
    const pwd = document.getElementById('password');
    const icon = this.querySelector('i');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        pwd.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
});
</script>
@endpush
@endsection
