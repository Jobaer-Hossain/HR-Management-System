@extends('layouts.app')

@section('content')
<div class="attendance-punch-page mobile-attendance-shell">
    <!-- Header with Breadcrumbs & Profile Switcher -->
    <div class="attendance-page-header d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h2 class="fw-bold mb-0 text-slate-900" style="font-size: 1.65rem; letter-spacing: -0.5px;">Check In / Check Out</h2>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                    <i class="bi bi-person-check-fill me-1"></i> Facial Verification
                </span>
            </div>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">Real-time biometric attendance terminal and self-service punch</p>
        </div>

        <div class="mobile-page-actions d-flex align-items-center gap-2 flex-wrap">
            <!-- Profile Switcher Dropdown -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle bg-white px-3 py-2 fw-semibold" type="button" data-bs-toggle="dropdown" style="border-radius: 10px; font-size: 0.85rem;">
                    <i class="bi bi-person-circle me-1"></i> {{ $employee->name }}
                    @if($employee->hasReliableFaceBiometric())
                        <span class="badge bg-success-subtle text-success ms-1" style="font-size: 0.7rem;">🟢 Enrolled</span>
                    @else
                        <span class="badge bg-warning-subtle text-warning ms-1" style="font-size: 0.7rem;">⚠️ Face Pending</span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="font-size: 0.85rem; max-height: 280px; overflow-y: auto;">
                    <li><h6 class="dropdown-header">Verify Identity As:</h6></li>
                    @foreach($allStaff as $st)
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between gap-2 {{ $st->id === $employee->id ? 'active fw-bold' : '' }}" href="{{ route('attendance.mobile', ['emp_id' => $st->id]) }}">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $st->face_snapshot ?? $st->avatar }}" style="width: 22px; height: 22px; border-radius: 50%; object-fit: cover;">
                                    <span>{{ $st->name }} ({{ $st->employee_code ?? 'EMP-'.$st->id }})</span>
                                </div>
                                @if($st->hasReliableFaceBiometric())
                                    <span class="badge bg-success-subtle text-success py-0 px-1" style="font-size: 0.65rem;">Enrolled</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning py-0 px-1" style="font-size: 0.65rem;">Pending</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <a href="{{ route('attendance.face') }}" class="btn btn-outline-secondary fw-semibold px-3 py-2 bg-white" style="border-radius: 10px; font-size: 0.85rem;">
                <i class="bi bi-camera-video me-1"></i> Gate Scanner
            </a>
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary fw-semibold px-3 py-2 bg-white" style="border-radius: 10px; font-size: 0.85rem;">
                <i class="bi bi-clock-history me-1"></i> Attendance Log
            </a>
            <a href="{{ route('login') }}" class="btn btn-primary fw-bold px-3 py-2" style="border-radius: 10px; font-size: 0.85rem;">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login Option
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-info-circle-fill fs-5 text-info"></i>
            <div>{{ session('info') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Main Responsive Card Container (Numla Prototype 2 Clean Web & Mobile Layout) -->
    <div class="row g-4 justify-content-center">
        <!-- Punch Clock & Scanner Card -->
        <div class="col-12 col-xl-8 col-lg-9">
            <div class="card card-custom mobile-punch-card p-4 p-md-5 text-center position-relative overflow-hidden">
                <div class="mobile-terminal-strip d-flex align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-2 text-start">
                        <span class="mobile-terminal-dot"></span>
                        <div>
                            <div class="fw-bold text-dark mobile-terminal-title">Self-service biometric terminal</div>
                            <div class="text-muted mobile-terminal-subtitle">Three live frames are verified securely on the server</div>
                        </div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 flex-shrink-0">
                        <i class="bi bi-shield-check me-1"></i> Ready
                    </span>
                </div>
                <!-- User Greeting Header -->
                <div class="d-flex flex-column align-items-center mb-3">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="{{ $employee->face_snapshot ?? $employee->avatar ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=140' }}" class="user-punch-avatar" id="punchUserAvatar" alt="{{ $employee->name }}">
                        @if($employee->hasReliableFaceBiometric())
                            <span class="position-absolute bottom-0 end-0 bg-success text-white rounded-circle d-flex align-items-center justify-content-center border border-2 border-white" style="width: 26px; height: 26px; font-size: 0.8rem;" title="Enrolled in Biometric Database">
                                <i class="bi bi-shield-check"></i>
                            </span>
                        @endif
                    </div>
                    <h3 class="fw-bold mb-1 text-slate-900" id="punchGreetingText" style="font-size: 1.55rem; letter-spacing: -0.5px;">
                        Welcome Back, {{ $employee->name }}!
                    </h3>
                    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-center">
                        <span class="badge bg-light text-muted border font-monospace px-2 py-1" style="font-size: 0.76rem;" id="punchUserCode">
                            {{ $employee->employee_code ?? 'EMP-' . str_pad($employee->id, 3, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.76rem;">
                            {{ $employee->department->name ?? 'General' }}
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 0.76rem;">
                            {{ $employee->designation->title ?? 'Staff' }}
                        </span>
                    </div>

                    @if(!$employee->hasReliableFaceBiometric())
                        <div class="alert alert-warning py-2 px-3 mt-3 mb-0 text-start w-100" style="border-radius: 12px; font-size: 0.82rem; border-left: 4px solid #f59e0b; background: #fffbeb;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-shield-exclamation text-warning fs-5"></i>
                                <strong class="text-dark">Facial Biometrics Not Enrolled Yet</strong>
                            </div>
                            <p class="mb-2 text-secondary" style="font-size: 0.8rem; line-height: 1.4;">
                                Admin must take and register your face photo in the <strong>Employee Directory</strong> before you can clock attendance.
                            </p>
                            <a href="{{ route('employees.index') }}" class="btn btn-sm btn-warning text-dark fw-bold py-1 px-3 d-inline-flex align-items-center gap-1 shadow-sm" style="border-radius: 8px; font-size: 0.75rem;">
                                <i class="bi bi-camera-fill"></i> Go to Employee Directory (Admin Face Enrollment)
                            </a>
                        </div>
                    @else
                        <div class="alert alert-success py-1 px-3 mt-3 mb-0 text-center rounded-pill d-inline-flex align-items-center gap-2" style="font-size: 0.76rem; background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534;">
                            <i class="bi bi-shield-check-fill text-success"></i>
                            <span>Biometric Face Profile Enrolled & Active</span>
                        </div>
                    @endif
                </div>

                <!-- Live Big Digital Clock -->
                <div class="my-3">
                    <div class="responsive-digital-clock" id="liveDigitalClock">
                        {{ date('h:i') }} <span class="clock-ampm">{{ date('A') }}</span>
                    </div>
                    <div class="text-muted fw-semibold" id="liveDateString" style="font-size: 0.95rem;">
                        {{ date('l, d F Y') }}
                    </div>
                </div>

                <!-- Interactive Biometric Scan / Camera Area -->
                <div class="my-4 position-relative d-flex flex-column align-items-center">
                    <!-- Live Camera Feed Viewport (Reveals smoothly when scanning) -->
                    <div id="liveCameraContainer" class="live-camera-feed-box mb-4" style="display: none;">
                        <video id="responsiveWebcamVideo" autoplay playsinline muted></video>
                        <canvas id="responsiveCanvas" style="display: none;"></canvas>

                        <!-- HUD Scanner Frame Overlay -->
                        <div class="camera-hud-overlay">
                            <div class="hud-corner top-left"></div>
                            <div class="hud-corner top-right"></div>
                            <div class="hud-corner bottom-left"></div>
                            <div class="hud-corner bottom-right"></div>
                            <div class="hud-scanner-bar"></div>
                        </div>

                        <div class="position-absolute bottom-0 start-0 w-100 p-2 text-center" style="background: rgba(15,23,42,0.85); backdrop-filter: blur(4px);">
                            <small class="text-white fw-bold d-block" id="cameraStatusPrompt">Position your face inside the frame</small>
                            <small class="text-white-50" style="font-size: 0.72rem;">Verifying identity with database records</small>
                        </div>
                    </div>

                    <!-- Main Circular Biometric Button (Numla Prototype 2) -->
                    <div class="punch-button-wrapper" id="punchButtonWrapper">
                        <button type="button" class="punch-circular-btn {{ $todayAttendance && $todayAttendance->check_in && !$todayAttendance->check_out ? 'btn-checked-in' : '' }}" id="triggerPunchScanBtn">
                            <div class="pulse-ring ring-1"></div>
                            <div class="pulse-ring ring-2"></div>
                            <div class="punch-inner-disc">
                                <div class="punch-icon-glow">
                                    <i class="bi bi-fingerprint" id="punchBtnIcon"></i>
                                </div>
                                <span class="punch-btn-text" id="punchBtnText">
                                    {{ $todayAttendance && $todayAttendance->check_in && !$todayAttendance->check_out ? 'Check Out' : 'Check In' }}
                                </span>
                            </div>
                        </button>
                    </div>

                    <!-- Direct Check In / Out 1-Click Action Form -->
                    <div class="mt-3 d-flex justify-content-center gap-2">
                        @if($todayAttendance && $todayAttendance->check_in && !$todayAttendance->check_out)
                            <form action="{{ route('attendance.checkout') }}" method="POST">
                                @csrf
                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                <button type="submit" class="btn btn-danger btn-sm fw-bold px-3 py-1 shadow-sm" style="border-radius: 8px; font-size: 0.8rem;">
                                    <i class="bi bi-box-arrow-right me-1"></i> Direct Check Out (1-Click)
                                </button>
                            </form>
                        @elseif(!$todayAttendance || !$todayAttendance->check_in)
                            <form action="{{ route('attendance.checkin') }}" method="POST">
                                @csrf
                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                <button type="submit" class="btn btn-primary btn-sm fw-bold px-3 py-1 shadow-sm" style="border-radius: 8px; font-size: 0.8rem;">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Direct Check In (1-Click)
                                </button>
                            </form>
                        @else
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill small">
                                <i class="bi bi-check-all me-1"></i> Full Day Attendance Completed
                            </span>
                        @endif
                    </div>

                    <!-- Geofence Distance Indicator (Numla Prototype 2) -->
                    <div class="mt-3">
                        <span class="distance-geofence-pill">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                            <span id="geofenceDistanceText">You are 10 Km away from your office.</span>
                        </span>
                    </div>
                </div>

                <!-- Verified Identity Card Alert (Shows upon Face Recognition Match) -->
                <div class="verified-card-banner mb-4" id="verifiedResultBanner" style="display: none;">
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                        <span class="badge bg-success text-white px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                            <i class="bi bi-check-circle-fill me-1"></i> Identity Verified
                        </span>
                        <span class="badge bg-white text-success border border-success-subtle fw-bold" id="verifiedConfidencePill" style="font-size: 0.75rem;">
                            99.2% Match
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-3 text-start">
                        <img src="" id="verifiedEmpAvatar" class="avatar-sm" style="width: 52px; height: 52px; border-radius: 14px; object-fit: cover; border: 2px solid #16a34a;" alt="Avatar">
                        <div class="flex-grow-1">
                            <div class="fw-bold text-dark fs-6" id="verifiedEmpName">Cody Fisher</div>
                            <div class="text-muted small" id="verifiedEmpMeta">Lead Full-Stack Engineer &bull; Engineering</div>
                            <div class="text-success small fw-bold mt-1" id="verifiedPunchMessage">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Checked in successfully
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3 Stats Cards (Numla Prototype 2: Check In, Check Out, Hours Worked) -->
                <div class="row g-3 justify-content-center pt-2 mobile-stat-grid">
                    <!-- Check In Box -->
                    <div class="col-12 col-sm-4">
                        <div class="stat-punch-card">
                            <div class="stat-punch-icon icon-in">
                                <i class="bi bi-box-arrow-in-right"></i>
                            </div>
                            <div class="stat-punch-val" id="displayCheckInVal">
                                {{ $todayAttendance && $todayAttendance->check_in ? date('h:i A', strtotime($todayAttendance->check_in)) : '09:41 AM' }}
                            </div>
                            <div class="stat-punch-lbl">Check In Time</div>
                        </div>
                    </div>

                    <!-- Check Out Box -->
                    <div class="col-12 col-sm-4">
                        <div class="stat-punch-card">
                            <div class="stat-punch-icon icon-out">
                                <i class="bi bi-box-arrow-right"></i>
                            </div>
                            <div class="stat-punch-val" id="displayCheckOutVal">
                                {{ $todayAttendance && $todayAttendance->check_out ? date('h:i A', strtotime($todayAttendance->check_out)) : '--:--' }}
                            </div>
                            <div class="stat-punch-lbl">Check Out Time</div>
                        </div>
                    </div>

                    <!-- Hours Worked Box -->
                    <div class="col-12 col-sm-4">
                        <div class="stat-punch-card">
                            <div class="stat-punch-icon icon-hrs">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="stat-punch-val" id="displayHoursVal">
                                {{ $todayAttendance && $todayAttendance->worked_minutes ? round($todayAttendance->worked_minutes / 60, 1) . ' hrs' : '5.1 hrs' }}
                            </div>
                            <div class="stat-punch-lbl">Hours Worked Today</div>
                        </div>
                    </div>
                </div>

                <!-- Manual Mode Toggle / Quick Action -->
                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 text-muted small">
                    <div>
                        <i class="bi bi-shield-check text-success me-1"></i>
                        <span>Biometric Security Terminal Active</span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-link text-decoration-none text-muted p-0" id="toggleLiveCamBtn">
                            <i class="bi bi-camera me-1"></i> <span id="toggleCamText">Preview Camera</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Today's Recent Scans (Responsive Column) -->
        <div class="col-12 col-xl-4 col-lg-3">
            <div class="card card-custom p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0 text-slate-900" style="font-size: 1rem;">Recent Activity</h6>
                    <span class="badge bg-light text-muted border font-monospace">Today</span>
                </div>

                <div class="timeline-activity">
                    <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                        <div style="width: 34px; height: 34px; background: #eff6ff; color: #2563eb; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-fingerprint"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">Biometric Facial Verification</div>
                            <div class="text-muted" style="font-size: 0.74rem;">Live camera scanning terminal</div>
                            <div class="text-primary fw-semibold mt-1" style="font-size: 0.72rem;">Status: Ready</div>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                        <div style="width: 34px; height: 34px; background: #ecfdf5; color: #10b981; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">Geofence Location Validation</div>
                            <div class="text-muted" style="font-size: 0.74rem;">HQ Main Entrance Gate</div>
                            <div class="text-success fw-semibold mt-1" style="font-size: 0.72rem;">Zone: Verified</div>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3">
                        <div style="width: 34px; height: 34px; background: #fff7ed; color: #ea580c; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">Shift Schedule Compliance</div>
                            <div class="text-muted" style="font-size: 0.74rem;">Morning Shift (09:00 AM - 05:00 PM)</div>
                            <div class="text-muted fw-semibold mt-1" style="font-size: 0.72rem;">On Schedule</div>
                        </div>
                    </div>
                </div>

                <div class="mt-auto pt-3">
                    <a href="{{ route('attendance.manual.create') }}" class="btn btn-warning w-100 fw-bold py-2 mb-2 text-dark shadow-sm" style="border-radius: 10px; font-size: 0.84rem;">
                        <i class="bi bi-pencil-square me-1"></i> Facial not working? Manual Request
                    </a>
                    <a href="{{ route('leaves.apply') }}" class="btn btn-outline-primary w-100 fw-bold py-2" style="border-radius: 10px; font-size: 0.82rem;">
                        <i class="bi bi-send-plus me-1"></i> Apply for Time Off
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Mobile self-service terminal: share the same visual language as the gate scanner. */
    .mobile-attendance-shell {
        --attendance-ink: #0f172a;
        --attendance-muted: #64748b;
        --attendance-line: #e2e8f0;
        --attendance-blue: #2563eb;
    }
    .mobile-punch-card,
    .mobile-attendance-shell .card-custom {
        border: 1px solid var(--attendance-line);
        border-radius: 18px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    }
    .mobile-terminal-strip {
        text-align: left;
    }
    .mobile-terminal-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #22c55e;
        box-shadow: 0 0 0 5px #dcfce7;
        flex: 0 0 auto;
    }
    .mobile-terminal-title {
        font-size: .9rem;
        color: var(--attendance-ink);
    }
    .mobile-terminal-subtitle {
        font-size: .75rem;
        line-height: 1.35;
    }
    .mobile-page-actions .btn,
    .mobile-attendance-shell .dropdown > .btn {
        min-height: 40px;
        border-color: #cbd5e1;
    }
    .mobile-attendance-shell .text-slate-900,
    .mobile-attendance-shell h2,
    .mobile-attendance-shell h3,
    .mobile-attendance-shell h6 {
        color: var(--attendance-ink) !important;
    }

    .user-punch-avatar {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #f1f5f9;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }

    /* Digital Clock */
    .responsive-digital-clock {
        font-size: 3rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
        letter-spacing: -1.5px;
    }
    .responsive-digital-clock .clock-ampm {
        font-size: 1.4rem;
        font-weight: 600;
        color: #64748b;
        letter-spacing: normal;
    }
    @media (max-width: 576px) {
        .responsive-digital-clock {
            font-size: 2.3rem;
        }
        .responsive-digital-clock .clock-ampm {
            font-size: 1.1rem;
        }
        .user-punch-avatar {
            width: 72px;
            height: 72px;
        }
    }

    /* Circular Punch Button */
    .punch-button-wrapper {
        display: flex;
        justify-content: center;
        position: relative;
    }
    .punch-circular-btn {
        width: 172px;
        height: 172px;
        border-radius: 50%;
        border: none;
        background: transparent;
        position: relative;
        cursor: pointer;
        padding: 0;
        outline: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .punch-inner-disc {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
        color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 14px 30px -6px rgba(37, 99, 235, 0.55);
        z-index: 2;
        transition: transform 0.2s ease, background 0.3s ease;
    }
    .punch-circular-btn:active .punch-inner-disc {
        transform: scale(0.95);
    }
    .punch-circular-btn.btn-checked-in .punch-inner-disc {
        background: linear-gradient(135deg, #f43f5e 0%, #e11d48 50%, #be123c 100%);
        box-shadow: 0 14px 30px -6px rgba(225, 29, 72, 0.55);
    }
    .punch-icon-glow {
        font-size: 3.4rem;
        line-height: 1;
        margin-bottom: 2px;
    }
    .punch-btn-text {
        font-size: 0.92rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    /* Pulsating outer rings */
    .pulse-ring {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }
    .pulse-ring.ring-1 {
        width: 164px;
        height: 164px;
        background: rgba(37, 99, 235, 0.15);
        animation: pulseRingOne 2.2s infinite ease-out;
    }
    .pulse-ring.ring-2 {
        width: 184px;
        height: 184px;
        background: rgba(37, 99, 235, 0.08);
        animation: pulseRingTwo 2.2s infinite ease-out;
    }
    @keyframes pulseRingOne {
        0% { transform: scale(0.9); opacity: 0.8; }
        50% { transform: scale(1.06); opacity: 0.4; }
        100% { transform: scale(0.9); opacity: 0.8; }
    }
    @keyframes pulseRingTwo {
        0% { transform: scale(0.85); opacity: 0.5; }
        50% { transform: scale(1.14); opacity: 0.15; }
        100% { transform: scale(0.85); opacity: 0.5; }
    }

    /* Distance Pill */
    .distance-geofence-pill {
        display: inline-flex;
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 500;
        padding: 6px 16px;
        border-radius: 20px;
    }

    /* 3 Summary Stats Cards */
    .stat-punch-card {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 18px;
        padding: 16px 12px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        transition: transform 0.2s ease;
    }
    .stat-punch-card:hover {
        transform: translateY(-2px);
    }
    .stat-punch-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px auto;
        font-size: 1.25rem;
    }
    .icon-in { background: #eff6ff; color: #2563eb; }
    .icon-out { background: #f0fdf4; color: #16a34a; }
    .icon-hrs { background: #f8fafc; color: #64748b; }
    .stat-punch-val {
        font-weight: 800;
        font-size: 0.95rem;
        color: #0f172a;
        margin-bottom: 2px;
    }
    .stat-punch-lbl {
        font-size: 0.72rem;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    /* Live Camera Feed Viewport (Responsive) */
    .live-camera-feed-box {
        width: 100%;
        max-width: 340px;
        height: 260px;
        border-radius: 20px;
        overflow: hidden;
        position: relative;
        background: #0f172a;
        border: 3px solid #2563eb;
        box-shadow: 0 12px 30px rgba(37, 99, 235, 0.3);
        margin: 0 auto;
    }
    .live-camera-feed-box video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .camera-hud-overlay {
        position: absolute;
        top: 20px; left: 20px; right: 20px; bottom: 20px;
        pointer-events: none;
    }
    .hud-corner {
        position: absolute;
        width: 22px;
        height: 22px;
        border-color: #38bdf8;
        border-style: solid;
    }
    .hud-corner.top-left { top: 0; left: 0; border-width: 3px 0 0 3px; border-top-left-radius: 8px; }
    .hud-corner.top-right { top: 0; right: 0; border-width: 3px 3px 0 0; border-top-right-radius: 8px; }
    .hud-corner.bottom-left { bottom: 0; left: 0; border-width: 0 0 3px 3px; border-bottom-left-radius: 8px; }
    .hud-corner.bottom-right { bottom: 0; right: 0; border-width: 0 3px 3px 0; border-bottom-right-radius: 8px; }
    .hud-scanner-bar {
        position: absolute;
        left: 0; right: 0;
        height: 2px;
        background: #38bdf8;
        box-shadow: 0 0 10px #38bdf8;
        animation: hudScanAnim 2s infinite ease-in-out;
    }
    @keyframes hudScanAnim {
        0% { top: 5%; opacity: 0.2; }
        50% { top: 92%; opacity: 1; }
        100% { top: 5%; opacity: 0.2; }
    }

    /* Verified Result Card */
    .verified-card-banner {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 18px;
        padding: 16px 20px;
        width: 100%;
        max-width: 520px;
        margin: 0 auto;
        animation: fadeInBanner 0.35s ease;
    }
    @keyframes fadeInBanner {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 991.98px) {
        .attendance-page-header {
            align-items: flex-start !important;
        }
        .mobile-page-actions {
            width: 100%;
        }
        .mobile-page-actions .dropdown {
            flex: 1 1 100%;
        }
        .mobile-page-actions .dropdown > .btn {
            width: 100%;
            justify-content: space-between;
        }
        .mobile-page-actions > a {
            flex: 1 1 calc(33.333% - .5rem);
            white-space: nowrap;
        }
    }

    @media (max-width: 576px) {
        .mobile-attendance-shell {
            margin-inline: -0.35rem;
        }
        .attendance-page-header {
            margin-bottom: 1rem !important;
        }
        .attendance-page-header h2 {
            font-size: 1.3rem !important;
        }
        .attendance-page-header p {
            font-size: .78rem !important;
            margin-top: .2rem;
        }
        .attendance-page-header .badge {
            font-size: .67rem !important;
        }
        .mobile-page-actions {
            gap: .45rem !important;
        }
        .mobile-page-actions > a {
            flex: 1 1 calc(50% - .25rem);
            padding-inline: .65rem !important;
            font-size: .74rem !important;
        }
        .mobile-page-actions > a:last-child {
            flex-basis: 100%;
        }
        .mobile-punch-card {
            padding: 1rem !important;
            border-radius: 16px;
        }
        .mobile-terminal-strip {
            margin-bottom: 1rem !important;
            padding-bottom: .75rem !important;
        }
        .mobile-terminal-title {
            font-size: .8rem;
        }
        .mobile-terminal-subtitle {
            font-size: .68rem;
        }
        .mobile-terminal-strip .badge {
            font-size: .65rem;
        }
        .live-camera-feed-box {
            width: min(100%, 340px);
            height: auto;
            aspect-ratio: 4 / 3;
            border-radius: 16px;
        }
        .punch-circular-btn {
            width: 148px;
            height: 148px;
        }
        .punch-inner-disc {
            width: 128px;
            height: 128px;
        }
        .pulse-ring.ring-1 {
            width: 142px;
            height: 142px;
        }
        .pulse-ring.ring-2 {
            width: 162px;
            height: 162px;
        }
        .punch-icon-glow {
            font-size: 2.8rem;
        }
        .punch-btn-text {
            font-size: .82rem;
        }
        .distance-geofence-pill {
            max-width: 100%;
            padding: 6px 10px;
            font-size: .72rem;
        }
        .mobile-stat-grid {
            --bs-gutter-x: .5rem;
            --bs-gutter-y: .5rem;
        }
        .mobile-stat-grid > [class*="col-"] {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }
        .stat-punch-card {
            padding: 10px 5px;
            border-radius: 12px;
        }
        .stat-punch-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            font-size: .95rem;
            margin-bottom: 5px;
        }
        .stat-punch-val {
            font-size: .76rem;
        }
        .stat-punch-lbl {
            font-size: .58rem;
            letter-spacing: 0;
            line-height: 1.25;
        }
        .verified-card-banner {
            padding: 12px;
            border-radius: 14px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/face-api.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const punchBtn = document.getElementById('triggerPunchScanBtn');
    const punchText = document.getElementById('punchBtnText');
    const cameraBox = document.getElementById('liveCameraContainer');
    const video = document.getElementById('responsiveWebcamVideo');
    const canvas = document.getElementById('responsiveCanvas');
    const cameraPrompt = document.getElementById('cameraStatusPrompt');
    const verifiedBanner = document.getElementById('verifiedResultBanner');
    const toggleCamBtn = document.getElementById('toggleLiveCamBtn');
    const toggleCamText = document.getElementById('toggleCamText');

    let stream = null;
    let isScanning = false;
    let modelsLoaded = false;
    let activeEmployeeId = {{ $employee->id }};

    // Load Local Neural Network Models
    async function loadFaceModels() {
        try {
            await faceapi.nets.tinyFaceDetector.load('/models');
            modelsLoaded = true;
            console.log('Mobile face detector models loaded.');
        } catch (err) {
            console.warn('Mobile face models notice:', err);
        }
    }
    loadFaceModels();

    // Live Digital Clock (every second)
    setInterval(() => {
        const d = new Date();
        const hours = d.getHours();
        const minutes = String(d.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const formattedHours = hours % 12 || 12;

        const clockEl = document.getElementById('liveDigitalClock');
        if (clockEl) {
            clockEl.innerHTML = `${String(formattedHours).padStart(2, '0')}:${minutes} <span class="clock-ampm">${ampm}</span>`;
        }
    }, 1000);

    // Toggle Camera Manual Preview
    async function toggleCameraStream() {
        if (stream) {
            stream.getTracks().forEach(t => t.stop());
            stream = null;
            cameraBox.style.display = 'none';
            if (toggleCamText) toggleCamText.innerText = 'Preview Camera';
        } else {
            cameraBox.style.display = 'block';
            if (toggleCamText) toggleCamText.innerText = 'Hide Camera';
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { width: 480, height: 480, facingMode: 'user' }
                });
                video.srcObject = stream;
            } catch (err) {
                console.warn('Camera stream permission unavailable:', err);
                cameraPrompt.innerText = 'Camera access blocked in browser';
            }
        }
    }

    if (toggleCamBtn) {
        toggleCamBtn.addEventListener('click', toggleCameraStream);
    }

    const isEmployeeEnrolled = {{ $employee->hasReliableFaceBiometric() ? 'true' : 'false' }};

    // Trigger Punch with Face Recognition Verification
    async function executeFaceAttendancePunch() {
        if (!isEmployeeEnrolled) {
            alert("⚠️ Facial Biometrics Not Enrolled!\n\nYour facial biometrics have not been registered in the database yet.\nAdmin must enroll your face in the Employee Directory before you can punch attendance.");
            return;
        }
        if (isScanning) return;
        isScanning = true;

        // Reveal Camera Box
        cameraBox.style.display = 'block';
        cameraPrompt.innerText = 'Detecting face in frame...';
        punchText.innerText = 'Verifying...';

        const faceFrames = [];

        try {
            if (!stream) {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { width: 480, height: 480, facingMode: 'user' }
                });
                video.srcObject = stream;
                await new Promise(r => setTimeout(r, 600));
            }
        } catch (e) {
            console.warn('Camera access unavailable:', e);
            cameraPrompt.innerText = 'Camera access denied';
            alert("⚠️ Camera permission required for biometric facial verification.");
            isScanning = false;
            punchText.innerText = punchBtn.classList.contains('btn-checked-in') ? 'Check Out' : 'Check In';
            return;
        }

        // 1. Strict Authentic Face Detection Check
        if (video.readyState === 4 && modelsLoaded) {
            cameraPrompt.innerText = 'Analyzing facial features...';
            const check = await faceapi.detectSingleFace(
                video,
                new faceapi.TinyFaceDetectorOptions({ scoreThreshold: 0.40 })
            );

            if (!check) {
                cameraPrompt.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-x-circle-fill"></i> No Face Detected! Face camera</span>`;
                alert("⚠️ Biometric Scan Rejected: No human face detected in camera! Please make sure your face is visible in front of the camera.");
                isScanning = false;
                punchText.innerText = punchBtn.classList.contains('btn-checked-in') ? 'Check Out' : 'Check In';
                return;
            }
        }

        cameraPrompt.innerText = 'Verifying identity with database biometrics...';

        // Capture three camera frames for server-side recognition and live-capture validation.
        if (video.videoWidth > 0 && canvas) {
            try {
                canvas.width = 480;
                canvas.height = 480;
                const ctx = canvas.getContext('2d');
                for (let frame = 0; frame < 3; frame++) {
                    ctx.drawImage(video, 0, 0, 480, 480);
                    faceFrames.push(canvas.toDataURL('image/jpeg', 0.85));
                    await new Promise(resolve => setTimeout(resolve, 300));
                }
            } catch (ce) {
                console.warn('Snapshot frame capture note:', ce);
            }
        }

        if (faceFrames.length < 3) {
            cameraPrompt.innerText = 'Could not capture the required live frames. Please retry.';
            isScanning = false;
            punchText.innerText = punchBtn.classList.contains('btn-checked-in') ? 'Check Out' : 'Check In';
            return;
        }

        // Determine intended punch action based on current button state
        const targetPunchType = punchBtn.classList.contains('btn-checked-in') ? 'check_out' : 'check_in';

        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

            const response = await fetch("{{ route('attendance.facescan') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        employee_id: activeEmployeeId,
                        punch_type: targetPunchType,
                        face_frames: faceFrames
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Update Verified Card Banner
                    const avatarEl = document.getElementById('verifiedEmpAvatar');
                    if (avatarEl) {
                        avatarEl.src = faceFrames[0] || data.employee.avatar;
                    }
                    document.getElementById('verifiedEmpName').innerText = data.employee.name;
                    document.getElementById('verifiedEmpMeta').innerText = `${data.employee.designation} • ${data.employee.department}`;
                    document.getElementById('verifiedConfidencePill').innerText = `${data.confidence} Match`;

                    let punchMsg = '';
                    if (data.action === 'already_checked_in') {
                        punchMsg = `<i class="bi bi-info-circle-fill me-1 text-primary"></i> ${data.message}`;
                    } else if (data.action === 'already_checked_out') {
                        punchMsg = `<i class="bi bi-check-circle-fill me-1 text-secondary"></i> ${data.message}`;
                    } else if (data.action === 'checked_out') {
                        punchMsg = `<i class="bi bi-box-arrow-right me-1 text-danger"></i> Checked OUT at ${data.attendance.check_out}`;
                    } else {
                        punchMsg = `<i class="bi bi-box-arrow-in-right me-1 text-success"></i> Checked IN at ${data.attendance.check_in}`;
                    }
                    document.getElementById('verifiedPunchMessage').innerHTML = punchMsg;

                    verifiedBanner.style.display = 'block';

                    // Update Top User Header Info
                    document.getElementById('punchGreetingText').innerText = `Welcome Back, ${data.employee.name}!`;
                    const userAvatarEl = document.getElementById('punchUserAvatar');
                    if (userAvatarEl && faceFrames[0]) {
                        userAvatarEl.src = faceFrames[0];
                    }
                    document.getElementById('punchUserCode').innerText = data.employee.employee_code;

                    // Update Display Stats
                    if (data.attendance && data.attendance.check_in && data.attendance.check_in !== '--:--') {
                        document.getElementById('displayCheckInVal').innerText = data.attendance.check_in;
                    }
                    if (data.attendance && data.attendance.check_out && data.attendance.check_out !== '--:--') {
                        document.getElementById('displayCheckOutVal').innerText = data.attendance.check_out;
                    }
                    if (data.attendance && data.attendance.worked_hours) {
                        document.getElementById('displayHoursVal').innerText = data.attendance.worked_hours;
                    }

                    // Toggle Button State
                    if (data.action === 'checked_in' || data.action === 'already_checked_in') {
                        punchBtn.classList.add('btn-checked-in');
                        punchText.innerText = 'Check Out';
                    } else {
                        punchBtn.classList.remove('btn-checked-in');
                        punchText.innerText = 'Check In';
                    }

                    cameraPrompt.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> ${data.message || 'Verified: ' + data.employee.name}</span>`;

                    // Pleasant Audio Confirmation Chime
                    try {
                        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = audioCtx.createOscillator();
                        const gain = audioCtx.createGain();
                        osc.connect(gain);
                        gain.connect(audioCtx.destination);
                        osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
                        osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.1); // A5
                        gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.35);
                        osc.start();
                        osc.stop(audioCtx.currentTime + 0.35);
                    } catch(e) {}

                } else {
                    if (data.can_request_manual) {
                        if (confirm(data.message + '\n\nWould you like to submit a Manual Attendance Request for Branch Manager approval now?')) {
                            window.location.href = data.manual_request_url || "{{ route('attendance.manual.create') }}";
                            return;
                        }
                    } else {
                        alert(data.message || 'Verification Failed');
                    }
                    cameraPrompt.innerText = data.cutoff_exceeded ? 'Check-In Cutoff Exceeded' : 'Verification Failed';
                }
            } catch (err) {
                console.error(err);
                cameraPrompt.innerText = 'Network error during verification';
            } finally {
                setTimeout(() => {
                    cameraBox.style.display = 'none';
                    if (stream) {
                        stream.getTracks().forEach(t => t.stop());
                        stream = null;
                    }
                    if (toggleCamText) toggleCamText.innerText = 'Preview Camera';
                    isScanning = false;
                }, 1600);
            }
        }, 800);
    }

    if (punchBtn) {
        punchBtn.addEventListener('click', executeFaceAttendancePunch);
    }
});
</script>
@endpush
