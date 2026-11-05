@extends('layouts.app')

@section('content')
<div class="face-attendance-page">
    <!-- Header with System Status & Switcher -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h2 class="fw-bold mb-0 text-slate-900" style="font-size: 1.65rem; letter-spacing: -0.5px;">Facial Attendance System</h2>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                    <i class="bi bi-broadcast me-1"></i> Facial Recognition Terminal Active
                </span>
            </div>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">Real-time biometric facial recognition attendance terminal & verification</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('login') }}" class="btn btn-outline-primary fw-bold px-3 py-2 bg-white" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login Option
            </a>
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary fw-semibold px-3 py-2" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-fingerprint me-1"></i> Fingerprint Biometric
            </a>
            <a href="{{ route('attendance.face') }}" class="btn btn-primary fw-bold px-3 py-2" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-camera-video me-1"></i> Facial Scanner
            </a>
        </div>
    </div>

    @php
        $enrolledStaffCount = $employees->filter(fn ($employee) => $employee->hasReliableFaceBiometric())->count();
    @endphp
    @if($enrolledStaffCount === 0)
        <div class="alert alert-warning d-flex align-items-center justify-content-between p-3 mb-4 rounded-4 shadow-sm" style="border-left: 5px solid #f59e0b;">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-shield-exclamation text-warning fs-3"></i>
                <div>
                    <strong class="text-dark d-block">No Employee Faces Enrolled Yet (Biometrics Empty)</strong>
                    <span class="text-secondary small">Faces must be captured by Admin from the Employee Directory before employees can clock attendance.</span>
                </div>
            </div>
            <a href="{{ route('employees.index') }}" class="btn btn-warning fw-bold text-dark btn-sm px-3" style="border-radius: 8px;">
                <i class="bi bi-camera-fill me-1"></i> Enroll Faces Now
            </a>
        </div>
    @endif

    <!-- Main Scanner Terminal Row -->
    <div class="row g-4 justify-content-center mb-4">
        <div class="col-12 col-lg-8">
            <div class="card card-custom p-4 text-center">
                <!-- Terminal Top Bar -->
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="status-indicator-dot pulsing-green"></div>
                        <span class="fw-bold text-dark" style="font-size: 0.9rem;">Terminal Gate #01 (Main Entrance HQ)</span>
                    </div>
                    <div class="text-muted fw-semibold" id="terminalClock" style="font-size: 0.88rem;">
                        {{ date('h:i:s A') }}
                    </div>
                </div>

                <!-- Video Viewport / Biometric Scanner View -->
                <div class="scanner-viewport-wrapper mx-auto position-relative">
                    <!-- Live HTML5 Video Element -->
                    <video id="webcamVideo" autoplay playsinline muted class="scanner-video"></video>
                    <canvas id="captureCanvas" style="display: none;"></canvas>
                    <div id="cameraFlashOverlay" style="position: absolute; inset: 0; background: white; opacity: 0; pointer-events: none; transition: opacity 0.2s ease; z-index: 5;"></div>
                    
                    <!-- Simulated Scanner Poster Canvas (Shown if camera is inactive/loading) -->
                    <div id="scannerPlaceholder" class="scanner-placeholder">
                        <i class="bi bi-person-bounding-box scanner-placeholder-icon"></i>
                        <p class="mb-2 fw-bold text-white">Camera Ready</p>
                        <small class="text-white-50">Click "Start Camera" or "Scan Face" below</small>
                    </div>

                    <!-- Biometric HUD Reticle Overlay -->
                    <div class="biometric-hud" id="biometricHud">
                        <div class="reticle-box">
                            <div class="corner-bracket top-left"></div>
                            <div class="corner-bracket top-right"></div>
                            <div class="corner-bracket bottom-left"></div>
                            <div class="corner-bracket bottom-right"></div>
                            
                            <!-- Scanning Laser Bar -->
                            <div class="scanner-laser" id="scannerLaser"></div>

                            <!-- Facial Mesh Crosshairs -->
                            <div class="hud-crosshair"></div>
                            <div class="hud-facial-points">
                                <span class="mesh-point point-1"></span>
                                <span class="mesh-point point-2"></span>
                                <span class="mesh-point point-3"></span>
                                <span class="mesh-point point-4"></span>
                                <span class="mesh-point point-5"></span>
                            </div>
                        </div>

                        <!-- Live Detection Tag -->
                        <div class="detection-status-pill" id="detectionStatusPill">
                            <i class="bi bi-shield-check me-1"></i>
                            <span id="detectionStatusText">Ready for Face Scan</span>
                        </div>
                    </div>
                </div>

                <!-- Control Buttons & Test Profile Selector -->
                <div class="scanner-controls mt-4">
                    <div class="row g-2 align-items-center justify-content-center">
                        <div class="col-12 col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0" style="border-radius: 10px 0 0 10px; font-size: 0.82rem;">
                                    <i class="bi bi-person-check text-primary"></i>
                                </span>
                                <select id="employeeSelect" class="form-select border-start-0" style="border-radius: 0 10px 10px 0; font-size: 0.85rem;">
                                    <option value="auto" selected>✨ Auto-Detect Identity (Camera AI)</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" data-name="{{ $emp->name }}" data-avatar="{{ $emp->avatar }}" data-dept="{{ $emp->department->name ?? 'General' }}" data-desig="{{ $emp->designation->title ?? 'Staff' }}" data-enrolled="{{ $emp->hasReliableFaceBiometric() ? '1' : '0' }}">
                                            {{ $emp->name }} ({{ $emp->department->name ?? 'General' }}) — {{ $emp->hasReliableFaceBiometric() ? '🟢 Enrolled' : '⚠️ Not Enrolled' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-5">
                            <label class="form-label small fw-bold text-dark mb-1">Select Punch Mode:</label>
                            <div class="btn-group w-100" role="group" id="gatePunchModeGroup">
                                <input type="radio" class="btn-check" name="gatePunchMode" id="modeAuto" value="auto" checked autocomplete="off">
                                <label class="btn btn-outline-secondary btn-sm fw-semibold" for="modeAuto">Auto</label>

                                <input type="radio" class="btn-check" name="gatePunchMode" id="modeCheckIn" value="check_in" autocomplete="off">
                                <label class="btn btn-outline-success btn-sm fw-semibold" for="modeCheckIn"><i class="bi bi-box-arrow-in-right"></i> Check In</label>

                                <input type="radio" class="btn-check" name="gatePunchMode" id="modeCheckOut" value="check_out" autocomplete="off">
                                <label class="btn btn-outline-danger btn-sm fw-semibold" for="modeCheckOut"><i class="bi bi-box-arrow-right"></i> Check Out</label>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold text-dark mb-1 d-none d-md-block">&nbsp;</label>
                            <button type="button" id="startCamBtn" class="btn btn-outline-secondary w-100 fw-semibold" style="border-radius: 10px; font-size: 0.85rem; height: 38px;">
                                <i class="bi bi-camera me-1"></i> <span id="camBtnText">Start Camera</span>
                            </button>
                        </div>

                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-bold text-dark mb-1 d-none d-md-block">&nbsp;</label>
                            <button type="button" id="scanFaceBtn" class="btn btn-primary w-100 fw-bold d-flex align-items-center justify-content-center gap-2" style="border-radius: 10px; font-size: 0.88rem; height: 38px; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);">
                                <i class="bi bi-scan"></i>
                                <span id="scanBtnLabel">Scan Face Now</span>
                            </button>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-center gap-2 text-success small fw-semibold" id="autoAttendanceStatus">
                                <i class="bi bi-broadcast-pin"></i>
                                <span>Automatic check-in is active when Auto-Detect Identity is selected.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTTOM SCANNED EMPLOYEE INFO CARD (Requested Feature) -->
    <div class="row justify-content-center mb-5">
        <div class="col-12 col-lg-8">
            <div id="employeeResultCard" class="card card-custom p-4 employee-result-card" style="display: none; border-left: 5px solid #22c55e;">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3 flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success px-3 py-1 rounded-pill fw-bold" style="font-size: 0.78rem;">
                            <i class="bi bi-check-circle-fill me-1"></i> FACE RECOGNIZED & VERIFIED
                        </span>
                        <span class="badge bg-light text-dark border px-2 py-1 rounded-pill" id="matchConfidenceBadge">
                            Match: 99.4% Confidence
                        </span>
                    </div>
                    <span class="text-muted small fw-semibold" id="scanTimestampText">Just Now</span>
                </div>

                <div class="row align-items-center g-3">
                    <!-- Employee Avatar with Verified Badge -->
                    <div class="col-auto">
                        <div class="position-relative">
                            <img id="resEmployeeAvatar" src="" alt="Employee" class="avatar-lg" style="width: 80px; height: 80px; border-radius: 20px; object-fit: cover; border: 3px solid #e2e8f0;">
                            <span class="position-absolute bottom-0 end-0 bg-success text-white rounded-circle p-1 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.75rem; border: 2px solid #fff;">
                                <i class="bi bi-check"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="col">
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="fw-bold mb-0 text-slate-900" id="resEmployeeName">Cody Fisher</h4>
                            <span class="badge bg-light text-secondary border py-0 px-2" id="resEmployeeCode" style="font-size: 0.75rem;">EMP-101</span>
                        </div>
                        <div class="text-primary fw-semibold mb-1" id="resEmployeeRole" style="font-size: 0.88rem;">Lead Full-Stack Engineer</div>
                        <div class="text-muted small mb-2 d-flex align-items-center gap-3">
                            <span><i class="bi bi-building me-1"></i> <strong id="resEmployeeDept">Engineering</strong></span>
                            <span><i class="bi bi-envelope me-1"></i> <span id="resEmployeeEmail">cody.fisher@easyteam.com</span></span>
                        </div>
                    </div>

                    <!-- Punch Status Info -->
                    <div class="col-12 col-md-auto text-md-end border-start-md ps-md-4">
                        <div class="punch-action-pill" id="punchActionBadge">
                            <i class="bi bi-box-arrow-in-right me-1"></i> CHECKED IN
                        </div>
                        <div class="fw-bold text-dark mt-2" id="punchTimeText" style="font-size: 1.15rem;">09:48 AM</div>
                        <small class="text-muted d-block" id="punchLocationText">Facial Terminal Gate 01</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Facial Scans Activity Table -->
    <div class="card card-custom p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold mb-0 text-slate-900" style="font-size: 1.1rem;">Today's Facial Attendance Logs</h5>
            <span class="text-muted" style="font-size: 0.85rem;">Live updated via Biometric Terminal</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light" style="font-size: 0.8rem;">
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Time</th>
                        <th>Terminal Location</th>
                        <th>Method</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="scansTableBody" style="font-size: 0.88rem;">
                    @forelse($recentScans as $scan)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $scan->employee->avatar ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=faces' }}" class="avatar-sm" alt="Emp">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $scan->employee->name ?? 'Staff' }}</div>
                                        <div class="text-muted" style="font-size: 0.74rem;">{{ $scan->employee->designation->title ?? 'Staff' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $scan->employee->department->name ?? 'General' }}</span></td>
                            <td><span class="fw-bold text-primary">{{ $scan->check_out ? date('h:i A', strtotime($scan->check_out)) : ($scan->check_in ? date('h:i A', strtotime($scan->check_in)) : '--:--') }}</span></td>
                            <td class="text-muted" style="font-size: 0.82rem;"><i class="bi bi-geo-alt text-primary"></i> {{ $scan->location_note ?? 'Facial Terminal Gate 01' }}</td>
                            <td><span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill"><i class="bi bi-camera-video me-1"></i> Facial AI</span></td>
                            <td>
                                @if($scan->status === 'present')
                                    <span class="badge-approved d-inline-flex align-items-center gap-1">
                                        <span style="width: 5px; height: 5px; background: #16a34a; border-radius: 50%;"></span> Present
                                    </span>
                                @else
                                    <span class="badge-pending">{{ ucfirst($scan->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyScanRow">
                            <td colspan="6" class="text-center text-muted py-4">No facial scans logged today yet. Position your face in front of the camera and click "Scan Face Now".</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Biometric Terminal HUD Styling */
    .scanner-viewport-wrapper {
        width: 100%;
        max-width: 520px;
        height: 360px;
        background: #0f172a;
        border-radius: 20px;
        overflow: hidden;
        position: relative;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.35);
        border: 2px solid #1e293b;
    }
    .scanner-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1); /* Mirror camera */
    }
    .scanner-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        z-index: 2;
    }
    .scanner-placeholder-icon {
        font-size: 4rem;
        color: #3b82f6;
        margin-bottom: 12px;
        opacity: 0.85;
        animation: pulseIcon 2s infinite ease-in-out;
    }
    @keyframes pulseIcon {
        0%, 100% { transform: scale(1); opacity: 0.85; }
        50% { transform: scale(1.08); opacity: 1; }
    }

    /* Biometric Reticle & Laser */
    .biometric-hud {
        position: absolute;
        inset: 0;
        z-index: 3;
        pointer-events: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .reticle-box {
        width: 240px;
        height: 270px;
        position: relative;
        border-radius: 16px;
    }
    .corner-bracket {
        position: absolute;
        width: 24px;
        height: 24px;
        border: 3px solid #38bdf8;
    }
    .corner-bracket.top-left { top: 0; left: 0; border-right: none; border-bottom: none; border-radius: 8px 0 0 0; }
    .corner-bracket.top-right { top: 0; right: 0; border-left: none; border-bottom: none; border-radius: 0 8px 0 0; }
    .corner-bracket.bottom-left { bottom: 0; left: 0; border-right: none; border-top: none; border-radius: 0 0 0 8px; }
    .corner-bracket.bottom-right { bottom: 0; right: 0; border-left: none; border-top: none; border-radius: 0 0 8px 0; }

    .scanner-laser {
        position: absolute;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, rgba(56, 189, 248, 0) 0%, #38bdf8 50%, rgba(56, 189, 248, 0) 100%);
        box-shadow: 0 0 15px #38bdf8, 0 0 25px #0284c7;
        top: 0;
        animation: laserScan 2.4s infinite ease-in-out;
    }
    @keyframes laserScan {
        0% { top: 5%; opacity: 0.3; }
        50% { top: 92%; opacity: 1; }
        100% { top: 5%; opacity: 0.3; }
    }

    .hud-facial-points {
        position: absolute;
        inset: 0;
    }
    .mesh-point {
        position: absolute;
        width: 6px;
        height: 6px;
        background: #38bdf8;
        border-radius: 50%;
        opacity: 0.6;
    }
    .mesh-point.point-1 { top: 35%; left: 35%; }
    .mesh-point.point-2 { top: 35%; right: 35%; }
    .mesh-point.point-3 { top: 52%; left: 50%; transform: translateX(-50%); }
    .mesh-point.point-4 { top: 68%; left: 40%; }
    .mesh-point.point-5 { top: 68%; right: 40%; }

    .detection-status-pill {
        position: absolute;
        bottom: 16px;
        background: rgba(15, 23, 42, 0.85);
        color: #38bdf8;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid rgba(56, 189, 248, 0.4);
        backdrop-filter: blur(6px);
    }

    /* Pulsing Green Indicator */
    .status-indicator-dot {
        width: 10px;
        height: 10px;
        background: #22c55e;
        border-radius: 50%;
    }
    .pulsing-green {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
        animation: pulseGreen 1.8s infinite;
    }
    @keyframes pulseGreen {
        0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }

    /* Result Card Styling */
    .employee-result-card {
        animation: slideUpFade 0.35s ease forwards;
        background: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08);
        border-radius: 18px;
    }
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .punch-action-pill {
        background: #ecfdf5;
        color: #166534;
        border: 1px solid #bbf7d0;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
    }
    .punch-action-checkout {
        background: #fff1f2;
        color: #9f1239;
        border-color: #fecdd3;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/face-api.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const webcamVideo = document.getElementById('webcamVideo');
    const placeholder = document.getElementById('scannerPlaceholder');
    const startCamBtn = document.getElementById('startCamBtn');
    const camBtnText = document.getElementById('camBtnText');
    const scanFaceBtn = document.getElementById('scanFaceBtn');
    const employeeSelect = document.getElementById('employeeSelect');
    const detectionStatusText = document.getElementById('detectionStatusText');
    const resultCard = document.getElementById('employeeResultCard');

    let stream = null;
    let modelsLoaded = false;
    let lastDetectedFace = null;
    let isTracking = false;
    let autoScanTimer = null;
    let autoScanBusy = false;

    // Load Local Neural Network Models
    async function loadFaceModels() {
        try {
            await faceapi.nets.tinyFaceDetector.load('/models');
            modelsLoaded = true;
            console.log('Biometric face detector models loaded from local repository.');
        } catch (err) {
            console.warn('Local face models notice:', err);
        }
    }
    loadFaceModels();

    // Continuous Real-Time Face Tracking Loop
    async function trackLiveFace() {
        if (!isTracking) return;

        if (stream && webcamVideo.readyState === 4 && modelsLoaded) {
            try {
                const detection = await faceapi.detectSingleFace(
                    webcamVideo,
                    new faceapi.TinyFaceDetectorOptions({ scoreThreshold: 0.45 })
                );

                if (detection) {
                    lastDetectedFace = detection;
                    const score = Math.round(detection.score * 100);
                    detectionStatusText.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-shield-check me-1"></i> Face Detected (${score}% clarity)</span>`;
                    document.querySelectorAll('.corner-bracket').forEach(b => b.style.borderColor = '#22c55e');
                } else {
                    lastDetectedFace = null;
                    detectionStatusText.innerHTML = `<span class="text-warning"><i class="bi bi-person-bounding-box me-1"></i> Align face inside reticle</span>`;
                    document.querySelectorAll('.corner-bracket').forEach(b => b.style.borderColor = '#38bdf8');
                }
            } catch (e) {}
        }

        setTimeout(trackLiveFace, 350);
    }

    // Start / Stop WebCam Stream
    async function toggleCamera() {
        if (stream) {
            // Stop camera
            isTracking = false;
            stream.getTracks().forEach(track => track.stop());
            webcamVideo.srcObject = null;
            stream = null;
            clearInterval(autoScanTimer);
            autoScanTimer = null;
            placeholder.style.display = 'flex';
            camBtnText.innerText = 'Start Camera';
            detectionStatusText.innerText = 'Ready for Face Scan';
            document.querySelectorAll('.corner-bracket').forEach(b => b.style.borderColor = '#38bdf8');
        } else {
            // Start camera
            try {
                detectionStatusText.innerText = 'Initializing Camera...';
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { width: 640, height: 480, facingMode: 'user' }
                });
                webcamVideo.srcObject = stream;
                placeholder.style.display = 'none';
                camBtnText.innerText = 'Stop Camera';
                detectionStatusText.innerText = 'Searching for face...';
                isTracking = true;
                setTimeout(trackLiveFace, 500);
                clearInterval(autoScanTimer);
                autoScanTimer = setInterval(() => {
                    if (employeeSelect?.value === 'auto' && stream && !autoScanBusy && (lastDetectedFace || !modelsLoaded)) {
                        scanFaceNow(true);
                    }
                }, 2500);
            } catch (err) {
                console.warn('Camera access unavailable:', err);
                placeholder.style.display = 'flex';
                placeholder.innerHTML = `
                    <i class="bi bi-camera-video-off scanner-placeholder-icon text-warning"></i>
                    <p class="mb-1 fw-bold text-white">Camera Offline</p>
                    <small class="text-white-50">Please allow camera permissions in your browser to scan.</small>
                `;
                detectionStatusText.innerText = 'Camera Offline';
            }
        }
    }

    if (startCamBtn) {
        startCamBtn.addEventListener('click', toggleCamera);
    }

    // Auto-attempt to start camera on load
    toggleCamera();

    // Scan Face Action. Automatic mode only checks in once and never toggles checkout.
    async function scanFaceNow(isAutomatic = false) {
        if (autoScanBusy) return;
        autoScanBusy = true;
        const selectedEmpId = employeeSelect ? employeeSelect.value : 'auto';
        const captureCanvas = document.getElementById('captureCanvas');
        const flashOverlay = document.getElementById('cameraFlashOverlay');
        const faceFrames = [];

        // 1. Strict Authentic Face Validation Check
        if (stream && webcamVideo.readyState === 4 && modelsLoaded) {
            detectionStatusText.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Analyzing camera face...`;
            const check = await faceapi.detectSingleFace(
                webcamVideo,
                new faceapi.TinyFaceDetectorOptions({ scoreThreshold: 0.40 })
            );

            if (!check) {
                detectionStatusText.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-x-circle me-1"></i> No Human Face Detected!</span>`;
                document.querySelectorAll('.corner-bracket').forEach(b => b.style.borderColor = '#ef4444');
                if (!isAutomatic) {
                    alert("⚠️ Biometric Scan Rejected: No human face detected in the camera frame! Please face the camera directly in a well-lit area.");
                }
                autoScanBusy = false;
                return;
            }
        }

        // 2. Capture three actual video frames. The server extracts the embedding itself.
        if (stream && webcamVideo.videoWidth > 0 && captureCanvas) {
            captureCanvas.width = 480;
            captureCanvas.height = 480;
            const ctx = captureCanvas.getContext('2d');
            ctx.translate(480, 0);
            ctx.scale(-1, 1);
            for (let frame = 0; frame < 3; frame++) {
                ctx.drawImage(webcamVideo, 0, 0, 480, 480);
                faceFrames.push(captureCanvas.toDataURL('image/jpeg', 0.85));
                await new Promise(resolve => setTimeout(resolve, 300));
            }

            // Shutter flash effect
            if (flashOverlay) {
                flashOverlay.style.opacity = '0.85';
                setTimeout(() => { flashOverlay.style.opacity = '0'; }, 150);
            }
        }

        if (faceFrames.length < 3) {
            detectionStatusText.innerText = 'Could not capture the required live frames. Please retry.';
            scanFaceBtn.disabled = false;
            autoScanBusy = false;
            return;
        }

        // 3. Visual Scanning HUD Feedback
        detectionStatusText.innerText = 'Matching facial biometrics with database...';
        scanFaceBtn.disabled = true;
        scanFaceBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Verifying Identity...`;

        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

            const selectedMode = document.querySelector('input[name="gatePunchMode"]:checked')?.value || 'auto';
            const punchType = selectedMode === 'auto' ? (isAutomatic ? 'auto_check_in' : 'auto') : selectedMode;

            const response = await fetch("{{ route('attendance.facescan') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    employee_id: selectedEmpId,
                    face_frames: faceFrames,
                    punch_type: punchType
                })
            });

            const data = await response.json();

            if (data.success) {
                const alreadyRecorded = data.action === 'already_checked_in' || data.action === 'already_checked_out';
                detectionStatusText.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> ${alreadyRecorded ? 'Attendance already recorded' : `Verified (${data.confidence})`}</span>`;

                // Update Bottom Employee Info Card
                const avatarImg = document.getElementById('resEmployeeAvatar');
                if (avatarImg) {
                    avatarImg.src = faceFrames[0] || data.employee.avatar;
                }
                document.getElementById('resEmployeeName').innerText = data.employee.name;
                document.getElementById('resEmployeeCode').innerText = data.employee.employee_code;
                document.getElementById('resEmployeeRole').innerText = data.employee.designation;
                document.getElementById('resEmployeeDept').innerText = data.employee.department;
                document.getElementById('resEmployeeEmail').innerText = data.employee.email;
                document.getElementById('matchConfidenceBadge').innerText = 'Match: ' + data.confidence + ' Confidence';
                document.getElementById('scanTimestampText').innerText = data.attendance.time;
                document.getElementById('punchTimeText').innerText = data.attendance.check_out !== '--:--' ? data.attendance.check_out : data.attendance.check_in;
                document.getElementById('punchLocationText').innerText = data.attendance.location;

                const actionBadge = document.getElementById('punchActionBadge');
                if (data.action === 'already_checked_in') {
                    actionBadge.className = 'punch-action-pill bg-primary text-white';
                    actionBadge.innerHTML = `<i class="bi bi-info-circle me-1"></i> ALREADY CHECKED IN`;
                    resultCard.style.borderLeftColor = '#2563eb';
                } else if (data.action === 'already_checked_out') {
                    actionBadge.className = 'punch-action-pill bg-secondary text-white';
                    actionBadge.innerHTML = `<i class="bi bi-check-all me-1"></i> ATTENDANCE COMPLETE`;
                    resultCard.style.borderLeftColor = '#64748b';
                } else if (data.action === 'checked_out') {
                    actionBadge.className = 'punch-action-pill punch-action-checkout';
                    actionBadge.innerHTML = `<i class="bi bi-box-arrow-right me-1"></i> CHECKED OUT`;
                    resultCard.style.borderLeftColor = '#e11d48';
                } else {
                    actionBadge.className = 'punch-action-pill';
                    actionBadge.innerHTML = `<i class="bi bi-box-arrow-in-right me-1"></i> CHECKED IN`;
                    resultCard.style.borderLeftColor = '#22c55e';
                }

                resultCard.style.display = 'block';
                resultCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                // Audio confirmation only for the first automatic/manual punch.
                try {
                    if (alreadyRecorded) throw new Error('repeat');
                    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
                    osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.1); // A5
                    gain.gain.setValueAtTime(0.18, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.35);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.35);
                } catch(e) {}

                if (alreadyRecorded) return;

                // Append to live logs table
                const tbody = document.getElementById('scansTableBody');
                const emptyRow = document.getElementById('emptyScanRow');
                if (emptyRow) emptyRow.remove();

                const newRow = document.createElement('tr');
                newRow.className = 'table-success-subtle';
                newRow.innerHTML = `
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="${faceFrames[0] || data.employee.avatar}" class="avatar-sm" alt="Emp" style="width:34px;height:34px;border-radius:50%;object-fit:cover;">
                            <div>
                                <div class="fw-bold text-dark">${data.employee.name}</div>
                                <div class="text-muted" style="font-size: 0.74rem;">${data.employee.designation}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-light text-dark border">${data.employee.department}</span></td>
                    <td><span class="fw-bold text-primary">${data.attendance.time}</span></td>
                    <td class="text-muted" style="font-size: 0.82rem;"><i class="bi bi-geo-alt text-primary"></i> ${data.attendance.location}</td>
                    <td><span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill"><i class="bi bi-camera-video me-1"></i> Facial AI</span></td>
                    <td>
                        <span class="badge-approved d-inline-flex align-items-center gap-1">
                            <span style="width: 5px; height: 5px; background: #16a34a; border-radius: 50%;"></span> Present
                        </span>
                    </td>
                `;
                tbody.insertBefore(newRow, tbody.firstChild);

            } else {
                if (data.can_request_manual) {
                    detectionStatusText.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-clock-history me-1"></i> ${data.message}</span> <a href="${data.manual_request_url || '{{ route('attendance.manual.create') }}'}" class="btn btn-warning btn-sm text-dark fw-bold ms-2 py-0 px-2" style="font-size:0.75rem;"><i class="bi bi-pencil-square"></i> Request Manual Attendance</a>`;
                    if (!isAutomatic) {
                        if (confirm(data.message + '\n\nWould you like to open the Manual Attendance Request form now?')) {
                            window.location.href = data.manual_request_url || "{{ route('attendance.manual.create') }}";
                        }
                    }
                } else {
                    detectionStatusText.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-shield-x me-1"></i> ${data.message || 'Verification Failed'}</span>`;
                    if (!isAutomatic) {
                        alert(data.message || 'Face verification failed: Identity mismatch.');
                    } else {
                        detectionStatusText.innerHTML = `<span class="text-warning"><i class="bi bi-hourglass-split me-1"></i> Waiting for a verified live face...</span>`;
                    }
                }
            }
        } catch (err) {
            console.error(err);
            detectionStatusText.innerText = 'Scanner Connection Error';
        } finally {
            scanFaceBtn.disabled = false;
            updateScanBtnUI();
            autoScanBusy = false;
        }
    }

    function updateScanBtnUI() {
        const mode = document.querySelector('input[name="gatePunchMode"]:checked')?.value || 'auto';
        if (mode === 'check_out') {
            scanFaceBtn.className = 'btn btn-danger w-100 fw-bold d-flex align-items-center justify-content-center gap-2';
            scanFaceBtn.innerHTML = `<i class="bi bi-box-arrow-right"></i> <span id="scanBtnLabel">Check Out Face Scan</span>`;
        } else if (mode === 'check_in') {
            scanFaceBtn.className = 'btn btn-success w-100 fw-bold d-flex align-items-center justify-content-center gap-2';
            scanFaceBtn.innerHTML = `<i class="bi bi-box-arrow-in-right"></i> <span id="scanBtnLabel">Check In Face Scan</span>`;
        } else {
            scanFaceBtn.className = 'btn btn-primary w-100 fw-bold d-flex align-items-center justify-content-center gap-2';
            scanFaceBtn.innerHTML = `<i class="bi bi-scan"></i> <span id="scanBtnLabel">Scan Face Now</span>`;
        }
    }

    document.querySelectorAll('input[name="gatePunchMode"]').forEach(radio => {
        radio.addEventListener('change', updateScanBtnUI);
    });

    scanFaceBtn.addEventListener('click', () => scanFaceNow(false));

    // Digital clock updater
    setInterval(() => {
        const now = new Date();
        const str = now.toLocaleTimeString();
        const el = document.getElementById('terminalClock');
        if (el) el.innerText = str;
    }, 1000);
});
</script>
@endpush
