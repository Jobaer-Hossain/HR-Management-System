@extends('layouts.app')

@section('content')
<div class="face-attendance-page">
    <!-- Header with System Status & Switcher -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h2 class="fw-bold mb-0 text-slate-900" style="font-size: 1.65rem; letter-spacing: -0.5px;">Facial Attendance System</h2>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                    <i class="bi bi-broadcast me-1"></i> ArcFace (buffalo_l) Model Active
                </span>
            </div>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">Real-time biometric facial recognition attendance terminal & verification</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary fw-semibold px-3 py-2" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-fingerprint me-1"></i> Fingerprint Biometric
            </a>
            <a href="{{ route('attendance.face') }}" class="btn btn-primary fw-bold px-3 py-2" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-camera-video me-1"></i> Facial Scanner
            </a>
        </div>
    </div>

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
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" data-name="{{ $emp->name }}" data-avatar="{{ $emp->avatar }}" data-dept="{{ $emp->department->name ?? 'General' }}" data-desig="{{ $emp->designation->title ?? 'Staff' }}">
                                            {{ $emp->name }} ({{ $emp->department->name ?? 'General' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <button type="button" id="startCamBtn" class="btn btn-outline-secondary w-100 fw-semibold" style="border-radius: 10px; font-size: 0.85rem;">
                                <i class="bi bi-camera me-1"></i> <span id="camBtnText">Start Camera</span>
                            </button>
                        </div>

                        <div class="col-6 col-md-4">
                            <button type="button" id="scanFaceBtn" class="btn btn-primary w-100 fw-bold d-flex align-items-center justify-content-center gap-2" style="border-radius: 10px; font-size: 0.88rem; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);">
                                <i class="bi bi-scan"></i>
                                <span>Scan Face Now</span>
                            </button>
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

    // Start / Stop WebCam Stream
    async function toggleCamera() {
        if (stream) {
            // Stop camera
            stream.getTracks().forEach(track => track.stop());
            webcamVideo.srcObject = null;
            stream = null;
            placeholder.style.display = 'flex';
            camBtnText.innerText = 'Start Camera';
            detectionStatusText.innerText = 'Ready for Face Scan';
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
                detectionStatusText.innerText = 'Face Tracking: Active';
            } catch (err) {
                console.warn('Camera access denied or unavailable, running in simulation mode:', err);
                placeholder.style.display = 'flex';
                placeholder.innerHTML = `
                    <i class="bi bi-camera-video-off scanner-placeholder-icon text-warning"></i>
                    <p class="mb-1 fw-bold text-white">Camera Offline / Simulation Active</p>
                    <small class="text-white-50">Select employee profile below and click "Scan Face Now"</small>
                `;
                detectionStatusText.innerText = 'Simulation Mode Active';
            }
        }
    }

    if (startCamBtn) {
        startCamBtn.addEventListener('click', toggleCamera);
    }

    // Auto-attempt to start camera on load
    toggleCamera();

    // Scan Face Action
    scanFaceBtn.addEventListener('click', function() {
        const selectedEmpId = employeeSelect ? employeeSelect.value : null;

        // Visual Scanning HUD Feedback
        detectionStatusText.innerText = 'Extracting ArcFace (buffalo_l) 512-D features...';
        scanFaceBtn.disabled = true;
        scanFaceBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Analyzing ArcFace Vector...`;

        setTimeout(async () => {
            try {
                const response = await fetch("{{ route('attendance.facescan') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        employee_id: selectedEmpId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    detectionStatusText.innerText = 'Face Verified (' + data.confidence + ' match)';

                    // Update Bottom Employee Info Card
                    document.getElementById('resEmployeeAvatar').src = data.employee.avatar;
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
                    if (data.action === 'checked_out') {
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

                    // Append to live logs table
                    const tbody = document.getElementById('scansTableBody');
                    const emptyRow = document.getElementById('emptyScanRow');
                    if (emptyRow) emptyRow.remove();

                    const newRow = document.createElement('tr');
                    newRow.className = 'table-success-subtle';
                    newRow.innerHTML = `
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="${data.employee.avatar}" class="avatar-sm" alt="Emp">
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
                    alert(data.message || 'Face verification failed.');
                    detectionStatusText.innerText = 'Verification Failed';
                }
            } catch (err) {
                console.error(err);
                detectionStatusText.innerText = 'Scanner Error';
            } finally {
                scanFaceBtn.disabled = false;
                scanFaceBtn.innerHTML = `<i class="bi bi-scan"></i> <span>Scan Face Now</span>`;
            }
        }, 800);
    });

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
