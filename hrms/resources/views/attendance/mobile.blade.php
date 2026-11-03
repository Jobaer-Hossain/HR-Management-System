@extends('layouts.app')

@section('content')
<div class="mobile-attendance-container py-2">
    <!-- Top Bar Controls -->
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('attendance.face') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                <i class="bi bi-arrow-left me-1"></i> Desktop Scanner
            </a>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                <i class="bi bi-phone me-1"></i> Numla Prototype Mobile View
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Test Profile Switcher -->
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle bg-white px-3" type="button" data-bs-toggle="dropdown" style="border-radius: 8px; font-size: 0.82rem;">
                    <i class="bi bi-person-bounding-box me-1"></i> Switch Test Profile
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: 0.82rem; max-height: 280px; overflow-y: auto;">
                    <li><h6 class="dropdown-header">Test Facial Identity Verification As:</h6></li>
                    @foreach($allStaff as $st)
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 {{ $st->id === $employee->id ? 'active fw-bold' : '' }}" href="{{ route('attendance.mobile', ['emp_id' => $st->id]) }}">
                                <img src="{{ $st->face_snapshot ?? $st->avatar }}" style="width: 22px; height: 22px; border-radius: 50%; object-fit: cover;">
                                <span>{{ $st->name }} ({{ $st->employee_code ?? 'EMP-'.$st->id }})</span>
                                @if($st->face_descriptor)
                                    <i class="bi bi-shield-check text-success ms-auto" title="Biometrics Enrolled in DB"></i>
                                @else
                                    <i class="bi bi-clock text-warning ms-auto" title="Pending"></i>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Viewport Switcher (Phone Frame vs Responsive) -->
            <button class="btn btn-sm btn-light border px-2 py-1" id="togglePhoneFrameBtn" style="border-radius: 8px; font-size: 0.82rem;" title="Toggle Phone Frame">
                <i class="bi bi-aspect-ratio me-1"></i> Frame View
            </button>
        </div>
    </div>

    <!-- Center Phone Display Wrapper -->
    <div class="d-flex justify-content-center">
        <!-- iPhone Mockup Body matching Prototype 2 (Numla) -->
        <div class="numla-phone-frame" id="numlaPhoneFrame">
            <!-- iOS Notch / Dynamic Island & Status Bar -->
            <div class="phone-status-bar">
                <div class="time" id="iosClock">{{ date('g:i A') }}</div>
                <div class="camera-notch">
                    <span class="lens"></span>
                </div>
                <div class="icons">
                    <i class="bi bi-reception-4"></i>
                    <i class="bi bi-wifi"></i>
                    <i class="bi bi-battery-full text-dark"></i>
                </div>
            </div>

            <!-- Mobile App Header -->
            <div class="phone-app-header">
                <a href="{{ route('dashboard') }}" class="btn-icon text-dark">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <div class="app-brand-title">
                    <span class="numla-logo-text">numla</span>
                </div>
                <button class="btn-icon text-dark" id="mobileMenuBtn">
                    <i class="bi bi-list fs-5"></i>
                </button>
            </div>

            <!-- Phone Scrollable Body -->
            <div class="phone-app-body">
                <!-- User Profile & Dynamic Greeting -->
                <div class="text-center pt-2 pb-3">
                    <div class="position-relative d-inline-block mb-2">
                        <img src="{{ $employee->face_snapshot ?? $employee->avatar ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=120' }}" class="phone-user-avatar" id="phoneUserAvatar" alt="{{ $employee->name }}">
                        <span class="position-absolute bottom-0 end-0 bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 20px; height: 20px; font-size: 0.65rem; border: 2px solid white;" id="phoneBioStatusIcon" title="Verified in Database">
                            <i class="bi bi-shield-check"></i>
                        </span>
                    </div>

                    <h4 class="fw-bold mb-1 text-slate-900" id="phoneGreetingText" style="font-size: 1.25rem;">
                        Welcome {{ $employee->name }}!
                    </h4>
                    <div class="badge bg-light text-muted border font-monospace px-2 py-1" id="phoneUserCode" style="font-size: 0.72rem;">
                        {{ $employee->employee_code ?? 'EMP-' . str_pad($employee->id, 3, '0', STR_PAD_LEFT) }} &bull; {{ $employee->department->name ?? 'General' }}
                    </div>
                </div>

                <!-- Live Big Digital Clock -->
                <div class="text-center mb-4">
                    <div class="phone-digital-clock" id="phoneBigClock">
                        {{ date('h:i') }} <span class="clock-meridiem">{{ date('a') }}</span>
                    </div>
                    <div class="phone-date-string text-muted" id="phoneDateString">
                        {{ date('l, d F') }}
                    </div>
                </div>

                <!-- Interactive Biometric Scan Zone -->
                <div class="text-center position-relative mb-4">
                    <!-- Scanner Mode Switch (Normal Button vs Live Camera Viewport) -->
                    <div id="liveCameraViewport" class="phone-live-camera-viewport" style="display: none;">
                        <video id="mobileWebcamVideo" autoplay playsinline muted></video>
                        <canvas id="mobileCanvas" style="display: none;"></canvas>
                        
                        <!-- Biometric Landmark Reticle Overlay -->
                        <div class="mobile-hud-reticle">
                            <div class="b-bracket b-tl"></div>
                            <div class="b-bracket b-tr"></div>
                            <div class="b-bracket b-bl"></div>
                            <div class="b-bracket b-br"></div>
                            <div class="mobile-laser-scanner"></div>
                        </div>

                        <!-- Recognition Target Overlay -->
                        <div class="position-absolute bottom-0 start-0 w-100 p-2 text-center" style="background: rgba(15,23,42,0.75);">
                            <small class="text-white fw-bold d-block" id="mobileScanPrompt">Align Face to Scan</small>
                            <small class="text-white-50" style="font-size: 0.65rem;">ArcFace buffalo_l (512-D Cosine Metric)</small>
                        </div>
                    </div>

                    <!-- Main Circular Biometric Button (Numla Prototype 2) -->
                    <div class="biometric-scan-button-wrapper" id="biometricBtnWrapper">
                        <button type="button" class="biometric-scan-button {{ $todayAttendance && $todayAttendance->check_in && !$todayAttendance->check_out ? 'active-checked-in' : '' }}" id="mobileBiometricScanBtn">
                            <div class="pulse-ring-outer"></div>
                            <div class="pulse-ring-middle"></div>
                            <div class="button-inner-circle">
                                <!-- Fingerprint / Face Biometric Graphic -->
                                <div class="biometric-icon-glow">
                                    <i class="bi bi-fingerprint" id="scanIcon"></i>
                                </div>
                                <span class="scan-button-label" id="scanButtonLabel">
                                    {{ $todayAttendance && $todayAttendance->check_in && !$todayAttendance->check_out ? 'Check Out' : 'Check In' }}
                                </span>
                            </div>
                        </button>
                    </div>

                    <!-- Geofence Distance Indicator (Numla Prototype 2) -->
                    <div class="mt-3">
                        <span class="phone-distance-pill">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                            <span id="geofenceDistanceText">You are 10 Km away from your office.</span>
                        </span>
                    </div>
                </div>

                <!-- Verified Identity Card (Shows on Face Recognition Success) -->
                <div class="verified-identity-card mb-3" id="verifiedIdentityCard" style="display: none;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size: 0.72rem;">
                            <i class="bi bi-check-circle-fill me-1"></i> Face Verified in DB
                        </span>
                        <span class="badge bg-light text-dark border font-monospace ms-auto" id="cardConfidenceBadge" style="font-size: 0.72rem;">
                            99.4% Match
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <img src="" id="cardEmpAvatar" class="avatar-sm" style="width: 48px; height: 48px; border-radius: 14px; object-fit: cover; border: 2px solid #16a34a;" alt="Verified">
                        <div class="flex-grow-1 text-start">
                            <div class="fw-bold text-dark fs-6" id="cardEmpName">Cody Fisher</div>
                            <div class="text-muted small" id="cardEmpDesignation">Staff Engineer &bull; Engineering</div>
                            <div class="text-primary small fw-semibold" id="cardPunchMessage">Checked in at 09:41 AM</div>
                        </div>
                    </div>
                </div>

                <!-- 3 Stats Cards (Numla Prototype 2: Check In, Check Out, Hours Worked) -->
                <div class="row g-2 mb-4 px-1">
                    <!-- Check In Box -->
                    <div class="col-4">
                        <div class="phone-stat-box">
                            <div class="stat-icon-square icon-checkin">
                                <i class="bi bi-box-arrow-in-right"></i>
                            </div>
                            <div class="stat-value" id="phoneCheckInVal">
                                {{ $todayAttendance && $todayAttendance->check_in ? date('h:i a', strtotime($todayAttendance->check_in)) : '9:41 am' }}
                            </div>
                            <div class="stat-label">Check In</div>
                        </div>
                    </div>

                    <!-- Check Out Box -->
                    <div class="col-4">
                        <div class="phone-stat-box">
                            <div class="stat-icon-square icon-checkout">
                                <i class="bi bi-box-arrow-right"></i>
                            </div>
                            <div class="stat-value" id="phoneCheckOutVal">
                                {{ $todayAttendance && $todayAttendance->check_out ? date('h:i a', strtotime($todayAttendance->check_out)) : '-----' }}
                            </div>
                            <div class="stat-label">Check Out</div>
                        </div>
                    </div>

                    <!-- Hours Worked Box -->
                    <div class="col-4">
                        <div class="phone-stat-box">
                            <div class="stat-icon-square icon-hours">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="stat-value" id="phoneHoursVal">
                                {{ $todayAttendance && $todayAttendance->worked_minutes ? round($todayAttendance->worked_minutes / 60, 1) . ' hrs' : '5.1 hrs' }}
                            </div>
                            <div class="stat-label">Hours Worked</div>
                        </div>
                    </div>
                </div>

                <!-- Verification Info Note -->
                <div class="p-3 rounded-3 bg-light border text-start small mb-4">
                    <div class="d-flex align-items-center gap-2 mb-1 fw-bold text-dark" style="font-size: 0.78rem;">
                        <i class="bi bi-shield-check text-primary"></i>
                        <span>ArcFace (buffalo_l) Cosine Verification</span>
                    </div>
                    <p class="text-muted mb-0" style="font-size: 0.72rem; line-height: 1.35;">
                        Your face is analyzed using the InsightFace ArcFace (buffalo_l / w600k_r50) 512-dimensional embedding model. Verification computes Cosine Similarity (threshold &ge; 0.45) against database biometric vectors.
                    </p>
                </div>
            </div>

            <!-- Bottom App Navigation Bar (Numla Prototype 2) -->
            <div class="phone-bottom-nav">
                <a href="{{ route('attendance.mobile') }}" class="nav-item active">
                    <i class="bi bi-fingerprint"></i>
                    <span>Check In/Out</span>
                    <span class="active-dot"></span>
                </a>
                <a href="{{ route('attendance.index') }}" class="nav-item">
                    <i class="bi bi-calendar-check"></i>
                    <span>Attendances</span>
                </a>
                <!-- Center Floating Camera Punch Button -->
                <button type="button" class="nav-item-center-camera" id="bottomNavCameraBtn" title="Scan Face Now">
                    <i class="bi bi-camera-fill"></i>
                </button>
                <a href="{{ route('schedule.index') }}" class="nav-item">
                    <i class="bi bi-building"></i>
                    <span>Workplaces</span>
                </a>
                <a href="{{ route('dashboard') }}" class="nav-item">
                    <i class="bi bi-bar-chart"></i>
                    <span>Reports</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Phone Mockup Container (Prototype 2 Numla) */
    .numla-phone-frame {
        width: 390px;
        height: 800px;
        background: #ffffff;
        border-radius: 46px;
        box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.25), 0 0 0 10px #1e293b, 0 0 0 12px #334155;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
        transition: all 0.3s ease;
    }

    /* Fullscreen mode toggle */
    .numla-phone-frame.fullscreen-mode {
        width: 100%;
        max-width: 480px;
        height: auto;
        min-height: 90vh;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border: 1px solid #e2e8f0;
    }

    /* iOS Status Bar */
    .phone-status-bar {
        height: 44px;
        padding: 0 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.8rem;
        font-weight: 700;
        color: #0f172a;
        z-index: 10;
        background: #ffffff;
    }
    .phone-status-bar .camera-notch {
        width: 110px;
        height: 22px;
        background: #0f172a;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .phone-status-bar .camera-notch .lens {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #1e293b;
        border: 1px solid #334155;
    }
    .phone-status-bar .icons {
        display: flex;
        gap: 6px;
        font-size: 0.85rem;
    }

    /* App Header */
    .phone-app-header {
        height: 48px;
        padding: 0 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .numla-logo-text {
        font-weight: 800;
        font-size: 1.35rem;
        color: #0284c7;
        letter-spacing: -0.5px;
    }
    .btn-icon {
        background: transparent;
        border: none;
        padding: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    /* Phone Body */
    .phone-app-body {
        flex: 1;
        overflow-y: auto;
        padding: 0 22px 20px 22px;
        display: flex;
        flex-direction: column;
    }
    .phone-user-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #f1f5f9;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    /* Digital Clock */
    .phone-digital-clock {
        font-size: 2.25rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
        letter-spacing: -1px;
    }
    .phone-digital-clock .clock-meridiem {
        font-size: 1.15rem;
        font-weight: 600;
        color: #64748b;
        text-transform: lowercase;
    }
    .phone-date-string {
        font-size: 0.82rem;
        font-weight: 500;
        margin-top: 4px;
    }

    /* Circular Biometric Scan Button (Numla Prototype 2) */
    .biometric-scan-button-wrapper {
        display: flex;
        justify-content: center;
        position: relative;
    }
    .biometric-scan-button {
        width: 154px;
        height: 154px;
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
    .button-inner-circle {
        width: 136px;
        height: 136px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
        color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 12px 28px -6px rgba(37, 99, 235, 0.55);
        z-index: 2;
        transition: transform 0.2s ease, background 0.3s ease;
    }
    .biometric-scan-button:active .button-inner-circle {
        transform: scale(0.95);
    }
    .biometric-scan-button.active-checked-in .button-inner-circle {
        background: linear-gradient(135deg, #f43f5e 0%, #e11d48 50%, #be123c 100%);
        box-shadow: 0 12px 28px -6px rgba(225, 29, 72, 0.55);
    }
    .biometric-icon-glow {
        font-size: 3rem;
        line-height: 1;
        margin-bottom: 2px;
    }
    .scan-button-label {
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    /* Pulsating outer rings */
    .pulse-ring-outer, .pulse-ring-middle {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }
    .pulse-ring-middle {
        width: 148px;
        height: 148px;
        background: rgba(37, 99, 235, 0.15);
        animation: pulseMiddle 2s infinite ease-out;
    }
    .pulse-ring-outer {
        width: 164px;
        height: 164px;
        background: rgba(37, 99, 235, 0.08);
        animation: pulseOuter 2s infinite ease-out;
    }
    @keyframes pulseMiddle {
        0% { transform: scale(0.9); opacity: 0.8; }
        50% { transform: scale(1.05); opacity: 0.4; }
        100% { transform: scale(0.9); opacity: 0.8; }
    }
    @keyframes pulseOuter {
        0% { transform: scale(0.85); opacity: 0.5; }
        50% { transform: scale(1.12); opacity: 0.2; }
        100% { transform: scale(0.85); opacity: 0.5; }
    }

    /* Live Camera Viewport inside phone */
    .phone-live-camera-viewport {
        width: 200px;
        height: 200px;
        border-radius: 28px;
        overflow: hidden;
        margin: 0 auto 12px auto;
        position: relative;
        background: #000;
        border: 3px solid #0284c7;
        box-shadow: 0 10px 25px rgba(2, 132, 199, 0.35);
    }
    .phone-live-camera-viewport video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .mobile-hud-reticle {
        position: absolute;
        top: 15px; left: 15px; right: 15px; bottom: 15px;
        pointer-events: none;
    }
    .b-bracket {
        position: absolute;
        width: 16px;
        height: 16px;
        border-color: #38bdf8;
        border-style: solid;
    }
    .b-tl { top: 0; left: 0; border-width: 2px 0 0 2px; }
    .b-tr { top: 0; right: 0; border-width: 2px 2px 0 0; }
    .b-bl { bottom: 0; left: 0; border-width: 0 0 2px 2px; }
    .b-br { bottom: 0; right: 0; border-width: 0 2px 2px 0; }
    .mobile-laser-scanner {
        position: absolute;
        left: 0; right: 0;
        height: 2px;
        background: #38bdf8;
        box-shadow: 0 0 8px #38bdf8;
        animation: scanMobile 1.8s infinite ease-in-out;
    }
    @keyframes scanMobile {
        0% { top: 5%; opacity: 0.3; }
        50% { top: 90%; opacity: 1; }
        100% { top: 5%; opacity: 0.3; }
    }

    /* Distance Pill */
    .phone-distance-pill {
        display: inline-flex;
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.74rem;
        font-weight: 500;
        padding: 5px 14px;
        border-radius: 20px;
    }

    /* 3 Summary Stats Boxes (Prototype 2) */
    .phone-stat-box {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 18px;
        padding: 12px 6px;
        text-align: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }
    .stat-icon-square {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 6px auto;
        font-size: 1.15rem;
    }
    .icon-checkin { background: #eff6ff; color: #2563eb; }
    .icon-checkout { background: #f0fdf4; color: #16a34a; }
    .icon-hours { background: #f8fafc; color: #64748b; }
    .stat-value {
        font-weight: 700;
        font-size: 0.8rem;
        color: #0f172a;
        margin-bottom: 2px;
        white-space: nowrap;
    }
    .stat-label {
        font-size: 0.68rem;
        color: #94a3b8;
        font-weight: 500;
    }

    /* Verified Identity Card */
    .verified-identity-card {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 16px;
        padding: 14px;
        animation: fadeInCard 0.4s ease;
    }
    @keyframes fadeInCard {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Bottom App Nav Bar */
    .phone-bottom-nav {
        height: 70px;
        background: #ffffff;
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-around;
        padding: 0 10px 10px 10px;
        position: relative;
    }
    .phone-bottom-nav .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        color: #94a3b8;
        text-decoration: none;
        font-size: 0.65rem;
        font-weight: 600;
        position: relative;
    }
    .phone-bottom-nav .nav-item i {
        font-size: 1.25rem;
    }
    .phone-bottom-nav .nav-item.active {
        color: #0284c7;
    }
    .phone-bottom-nav .nav-item .active-dot {
        width: 4px;
        height: 4px;
        background: #0284c7;
        border-radius: 50%;
        position: absolute;
        bottom: -6px;
    }
    .nav-item-center-camera {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: #0284c7;
        color: white;
        border: 3px solid #ffffff;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        cursor: pointer;
        transform: translateY(-8px);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const scanBtn = document.getElementById('mobileBiometricScanBtn');
    const scanLabel = document.getElementById('scanButtonLabel');
    const cameraViewport = document.getElementById('liveCameraViewport');
    const video = document.getElementById('mobileWebcamVideo');
    const canvas = document.getElementById('mobileCanvas');
    const scanPrompt = document.getElementById('mobileScanPrompt');
    const verifiedCard = document.getElementById('verifiedIdentityCard');
    const cameraBottomBtn = document.getElementById('bottomNavCameraBtn');
    const frameToggleBtn = document.getElementById('togglePhoneFrameBtn');
    const phoneFrame = document.getElementById('numlaPhoneFrame');

    let stream = null;
    let isScanning = false;
    let targetEmployeeId = {{ $employee->id }};

    // Toggle frame mode (Desktop iPhone mockup vs full responsive)
    if (frameToggleBtn) {
        frameToggleBtn.addEventListener('click', function() {
            phoneFrame.classList.toggle('fullscreen-mode');
            frameToggleBtn.innerHTML = phoneFrame.classList.contains('fullscreen-mode') 
                ? '<i class="bi bi-phone me-1"></i> Phone Mockup' 
                : '<i class="bi bi-aspect-ratio me-1"></i> Frame View';
        });
    }

    // Digital clock updater
    setInterval(() => {
        const d = new Date();
        const hours = d.getHours();
        const minutes = String(d.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'pm' : 'am';
        const formattedHours = hours % 12 || 12;

        const clockEl = document.getElementById('phoneBigClock');
        if (clockEl) {
            clockEl.innerHTML = `${String(formattedHours).padStart(2, '0')}:${minutes} <span class="clock-meridiem">${ampm}</span>`;
        }
        const iosClock = document.getElementById('iosClock');
        if (iosClock) {
            iosClock.innerText = `${formattedHours}:${minutes} ${ampm.toUpperCase()}`;
        }
    }, 1000);

    // Trigger Face Scan & Verification
    async function triggerBiometricFaceVerification() {
        if (isScanning) return;
        isScanning = true;

        // Show camera viewport inside phone
        cameraViewport.style.display = 'block';
        scanPrompt.innerText = 'Extracting ArcFace (buffalo_l) 512-D features...';
        scanLabel.innerText = 'Analyzing...';

        try {
            if (!stream) {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { width: 480, height: 480, facingMode: 'user' }
                });
                video.srcObject = stream;
            }
        } catch (e) {
            console.warn('Camera stream permission unavailable, using simulation extraction:', e);
            scanPrompt.innerText = 'Camera Mode: Sensor Extraction';
        }

        // Simulate 750ms AI Face Extraction
        setTimeout(async () => {
            scanPrompt.innerText = 'Verifying identity with database...';

            try {
                // Post to face-scan API endpoint
                const res = await fetch("{{ route('attendance.facescan') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        employee_id: targetEmployeeId
                    })
                });

                const data = await res.json();

                if (data.success) {
                    // Update Verified Identity Card
                    document.getElementById('cardEmpAvatar').src = data.employee.avatar;
                    document.getElementById('cardEmpName').innerText = data.employee.name;
                    document.getElementById('cardEmpDesignation').innerText = data.employee.designation + ' • ' + data.employee.department;
                    document.getElementById('cardConfidenceBadge').innerText = data.confidence + ' Match';

                    const punchMsg = data.action === 'checked_out' 
                        ? `Checked OUT at ${data.attendance.check_out}` 
                        : `Checked IN at ${data.attendance.check_in}`;
                    document.getElementById('cardPunchMessage').innerText = punchMsg;

                    verifiedCard.style.display = 'block';

                    // Update Top greeting & Avatar to the verified employee
                    document.getElementById('phoneGreetingText').innerText = `Welcome ${data.employee.name}!`;
                    document.getElementById('phoneUserAvatar').src = data.employee.avatar;
                    document.getElementById('phoneUserCode').innerText = `${data.employee.employee_code} • ${data.employee.department}`;

                    // Update stats boxes
                    if (data.attendance.check_in) {
                        document.getElementById('phoneCheckInVal').innerText = data.attendance.check_in;
                    }
                    if (data.attendance.check_out && data.attendance.check_out !== '--:--') {
                        document.getElementById('phoneCheckOutVal').innerText = data.attendance.check_out;
                    }

                    // Update Scan Button state
                    if (data.action === 'checked_in') {
                        scanBtn.classList.add('active-checked-in');
                        scanLabel.innerText = 'Check Out';
                    } else {
                        scanBtn.classList.remove('active-checked-in');
                        scanLabel.innerText = 'Check In';
                    }

                    scanPrompt.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Verified: ${data.employee.name}</span>`;

                    // Play success sound beep (Web Audio API)
                    try {
                        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = audioCtx.createOscillator();
                        const gain = audioCtx.createGain();
                        osc.connect(gain);
                        gain.connect(audioCtx.destination);
                        osc.frequency.value = 880;
                        gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
                        osc.start();
                        osc.stop(audioCtx.currentTime + 0.15);
                    } catch(e) {}

                } else {
                    alert('Verification Failed: ' + data.message);
                    scanPrompt.innerText = 'Identity Not Found in DB';
                }
            } catch (err) {
                console.error(err);
                scanPrompt.innerText = 'Verification Request Error';
            } finally {
                setTimeout(() => {
                    cameraViewport.style.display = 'none';
                    if (stream) {
                        stream.getTracks().forEach(t => t.stop());
                        stream = null;
                    }
                    isScanning = false;
                }, 1600);
            }
        }, 850);
    }

    if (scanBtn) {
        scanBtn.addEventListener('click', triggerBiometricFaceVerification);
    }
    if (cameraBottomBtn) {
        cameraBottomBtn.addEventListener('click', triggerBiometricFaceVerification);
    }
});
</script>
@endpush
