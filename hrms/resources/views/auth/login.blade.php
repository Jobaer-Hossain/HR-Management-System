@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-9">
            <div class="card shadow-sm border-0 overflow-hidden" style="border-radius: 18px;">
                <div class="row g-0">
                    <!-- Left Brand / Info Side -->
                    <div class="col-md-5 d-none d-md-flex flex-column justify-content-between p-4 p-lg-5 text-white" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-diagram-3-fill fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0">EasyTeam HRMS</h5>
                                    <small class="text-white-50">Enterprise Workforce Suite</small>
                                </div>
                            </div>

                            <h4 class="fw-bold mb-3">Enterprise AI Facial Biometrics & HR Portal</h4>
                            <p class="text-white-50 small mb-4" style="line-height: 1.6;">
                                Complete multi-tenant HR management with real-time biometric attendance, role-based workflows, shift scheduling, and self-service employee portal.
                            </p>

                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex align-items-center gap-2 small text-light">
                                    <i class="bi bi-camera-video-fill text-success fs-5"></i>
                                    <span>Face Enrollment in Admin Directory</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 small text-light">
                                    <i class="bi bi-shield-check text-info fs-5"></i>
                                    <span>Zero-Trust Biometric Gate Verification</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 small text-light">
                                    <i class="bi bi-people-fill text-warning fs-5"></i>
                                    <span>19 Staff Accounts with Password <code class="text-warning">password123</code></span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-top border-secondary border-opacity-25 text-white-50 small">
                            &copy; {{ date('Y') }} EasyTeam Global Ltd. Multi-Tenant Enterprise.
                        </div>
                    </div>

                    <!-- Right Login Form Side -->
                    <div class="col-12 col-md-7 p-4 p-lg-5 bg-white">
                        <div class="text-center text-md-start mb-4">
                            <h3 class="fw-bold text-dark mb-1">Sign In to EasyTeam</h3>
                            <p class="text-muted small">Choose a role demo below or enter your employee email & password</p>
                        </div>

                        <!-- 1-Click Quick Role Selector -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small mb-2">⚡ 1-Click Role Login Demo</label>
                            <div class="row g-2">
                                <div class="col-6 col-sm-3">
                                    <button type="button" class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center gap-1 quick-login-btn" onclick="quickFill('admin@hrms.com', 'password123')" style="border-radius: 12px; font-size: 0.76rem;">
                                        <i class="bi bi-person-fill-gear fs-5"></i>
                                        <span class="fw-bold">HR Admin</span>
                                    </button>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <button type="button" class="btn btn-outline-info w-100 py-2 d-flex flex-column align-items-center justify-content-center gap-1 quick-login-btn" onclick="quickFill('manager@hrms.com', 'password123')" style="border-radius: 12px; font-size: 0.76rem;">
                                        <i class="bi bi-briefcase-fill fs-5"></i>
                                        <span class="fw-bold">Manager</span>
                                    </button>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <button type="button" class="btn btn-outline-success w-100 py-2 d-flex flex-column align-items-center justify-content-center gap-1 quick-login-btn" onclick="quickFill('finance@hrms.com', 'password123')" style="border-radius: 12px; font-size: 0.76rem;">
                                        <i class="bi bi-cash-coin fs-5"></i>
                                        <span class="fw-bold">Finance</span>
                                    </button>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <button type="button" class="btn btn-outline-warning w-100 py-2 d-flex flex-column align-items-center justify-content-center gap-1 quick-login-btn" onclick="quickFill('cody.fisher@easyteam.com', 'password123')" style="border-radius: 12px; font-size: 0.76rem;">
                                        <i class="bi bi-person-badge-fill fs-5"></i>
                                        <span class="fw-bold">Staff Portal</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Login Form -->
                        <form method="POST" action="{{ route('login') }}" id="loginForm">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold text-dark small">{{ __('Email Address') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 10px 0 0 10px;">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input id="email" type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email', 'admin@hrms.com') }}" required autocomplete="email" autofocus placeholder="name@easyteam.com" style="border-radius: 0 10px 10px 0;">
                                </div>
                                @error('email')
                                    <span class="invalid-feedback d-block mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="password" class="form-label fw-semibold text-dark small mb-0">{{ __('Password') }}</label>
                                    <span class="badge bg-light text-muted border" style="font-size: 0.72rem;">Default: password123</span>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 10px 0 0 10px;">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input id="password" type="password" class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror" name="password" value="password123" required autocomplete="current-password" placeholder="Enter password">
                                    <button type="button" class="btn btn-outline-secondary border-start-0 bg-light" id="togglePasswordBtn" style="border-radius: 0 10px 10px 0;">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <span class="invalid-feedback d-block mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                                    <label class="form-check-label small text-muted" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                                @if (Route::has('password.request'))
                                    <a class="text-primary text-decoration-none small fw-semibold" href="{{ route('password.request') }}">
                                        Forgot Password?
                                    </a>
                                @endif
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2" style="border-radius: 10px; font-size: 0.95rem;">
                                <span>Sign In</span>
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </form>

                        <!-- Expandable All Employee Accounts Directory -->
                        <div class="mt-4 pt-3 border-top">
                            <button class="btn btn-sm btn-light w-100 text-start d-flex align-items-center justify-content-between py-2 px-3 fw-semibold text-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#employeeCredentialsList" aria-expanded="false" style="border-radius: 10px; font-size: 0.8rem;">
                                <span><i class="bi bi-people me-2 text-primary"></i> View All 19 Employee Logins & Passwords</span>
                                <i class="bi bi-chevron-down"></i>
                            </button>

                            <div class="collapse mt-3" id="employeeCredentialsList">
                                <div class="card card-body bg-light border-0 p-2" style="border-radius: 12px; max-height: 280px; overflow-y: auto;">
                                    <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 0.75rem;">
                                        <thead>
                                            <tr class="text-muted border-bottom">
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Password</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $demoStaff = [
                                                    ['name' => 'William Dolaz', 'email' => 'admin@hrms.com', 'role' => 'HR Manager', 'badge' => 'primary'],
                                                    ['name' => 'Robert Fox', 'email' => 'manager@hrms.com', 'role' => 'Dept Manager', 'badge' => 'info'],
                                                    ['name' => 'Jacob Jones', 'email' => 'finance@hrms.com', 'role' => 'Finance', 'badge' => 'success'],
                                                    ['name' => 'Cody Fisher', 'email' => 'cody.fisher@easyteam.com', 'role' => 'Eng Lead', 'badge' => 'warning'],
                                                    ['name' => 'Jenny Wilson', 'email' => 'jenny.wilson@easyteam.com', 'role' => 'Finance', 'badge' => 'secondary'],
                                                    ['name' => 'Darrell Steward', 'email' => 'darrell.steward@easyteam.com', 'role' => 'Staff', 'badge' => 'secondary'],
                                                    ['name' => 'Brooklyn Simmons', 'email' => 'brooklyn.simmons@easyteam.com', 'role' => 'HR Manager', 'badge' => 'primary'],
                                                    ['name' => 'Dianne Russell', 'email' => 'dianne.russell@easyteam.com', 'role' => 'Finance', 'badge' => 'secondary'],
                                                    ['name' => 'Leslie Alexander', 'email' => 'leslie.alexander@easyteam.com', 'role' => 'Staff', 'badge' => 'secondary'],
                                                    ['name' => 'Floyd Miles', 'email' => 'floyd.miles@easyteam.com', 'role' => 'Dept Manager', 'badge' => 'info'],
                                                    ['name' => 'Cameron Williamson', 'email' => 'cameron.williamson@easyteam.com', 'role' => 'Staff', 'badge' => 'secondary'],
                                                    ['name' => 'Devon Lane', 'email' => 'devon.lane@easyteam.com', 'role' => 'Finance', 'badge' => 'secondary'],
                                                    ['name' => 'Esther Howard', 'email' => 'esther.howard@easyteam.com', 'role' => 'Dept Manager', 'badge' => 'info'],
                                                    ['name' => 'Ralph Edwards', 'email' => 'ralph.edwards@easyteam.com', 'role' => 'Staff', 'badge' => 'secondary'],
                                                    ['name' => 'Kristin Watson', 'email' => 'kristin.watson@easyteam.com', 'role' => 'Finance', 'badge' => 'secondary'],
                                                    ['name' => 'Annette Black', 'email' => 'annette.black@easyteam.com', 'role' => 'Finance', 'badge' => 'secondary'],
                                                    ['name' => 'Jane Cooper', 'email' => 'jane.cooper@easyteam.com', 'role' => 'Staff', 'badge' => 'secondary'],
                                                    ['name' => 'Guy Hawkins', 'email' => 'guy.hawkins@easyteam.com', 'role' => 'Dept Manager', 'badge' => 'info'],
                                                    ['name' => 'Bessie Cooper', 'email' => 'bessie.cooper@easyteam.com', 'role' => 'Staff', 'badge' => 'secondary'],
                                                    ['name' => 'Kathryn Murphy', 'email' => 'kathryn.murphy@easyteam.com', 'role' => 'Dept Manager', 'badge' => 'info'],
                                                ];
                                            @endphp
                                            @foreach($demoStaff as $s)
                                                <tr class="border-bottom border-light">
                                                    <td class="fw-semibold text-dark">{{ $s['name'] }}</td>
                                                    <td class="font-monospace text-muted">{{ $s['email'] }}</td>
                                                    <td><span class="badge bg-{{ $s['badge'] }}-subtle text-{{ $s['badge'] }} border">{{ $s['role'] }}</span></td>
                                                    <td><code class="text-muted">password123</code></td>
                                                    <td class="text-end">
                                                        <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.7rem; border-radius: 6px;" onclick="quickFill('{{ $s['email'] }}', 'password123')">
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

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function quickFill(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
    
    // Highlight inputs briefly
    const emailInput = document.getElementById('email');
    emailInput.classList.add('border-primary');
    setTimeout(() => emailInput.classList.remove('border-primary'), 800);
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
