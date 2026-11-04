@extends('layouts.app')

@section('content')
<div class="employee-dashboard-page">
    <!-- Welcome Header & Profile Context Switcher -->
    <div class="card card-custom p-4 mb-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; border: none;">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <div class="position-relative">
                    <img src="{{ $employee->face_snapshot ?? $employee->avatar ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&auto=format&fit=crop&q=80' }}" class="avatar-lg" alt="{{ $employee->name }}" style="width: 82px; height: 82px; border-radius: 20px; object-fit: cover; border: 3px solid rgba(255,255,255,0.2);">
                    @if($employee->hasReliableFaceBiometric())
                        <span class="position-absolute bottom-0 end-0 bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.75rem; border: 2px solid #0f172a;" title="Facial Biometrics Stored in DB">
                            <i class="bi bi-shield-check"></i>
                        </span>
                    @endif
                </div>
            </div>

            <div class="col">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-primary px-2 py-1 rounded-pill" style="font-size: 0.72rem; letter-spacing: 0.04em;">STAFF PORTAL</span>
                    <span class="badge bg-light text-dark py-0 px-2" style="font-size: 0.75rem;">{{ $employee->employee_code ?? 'EMP-' . str_pad($employee->id, 3, '0', STR_PAD_LEFT) }}</span>
                </div>
                <h3 class="fw-bold mb-1" style="font-size: 1.55rem; letter-spacing: -0.5px;">Welcome back, {{ $employee->name }}!</h3>
                <div class="text-white-50 small d-flex align-items-center gap-3 flex-wrap">
                    <span><i class="bi bi-briefcase me-1"></i> {{ $employee->designation->title ?? 'Staff Member' }}</span>
                    <span><i class="bi bi-building me-1"></i> {{ $employee->department->name ?? 'General' }}</span>
                    <span><i class="bi bi-calendar-check me-1"></i> Joined: {{ $employee->joining_date ?? 'Jan 2024' }}</span>
                </div>
            </div>

            <!-- Profile Switcher dropdown is only available to administrators -->
            @if(!auth()->user()->isEmployee())
            <div class="col-12 col-md-auto text-md-end">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle px-3 py-2 fw-semibold" type="button" data-bs-toggle="dropdown" style="border-radius: 10px; font-size: 0.82rem;">
                        <i class="bi bi-person-bounding-box me-1"></i> Switch Profile View
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: 0.82rem; max-height: 280px; overflow-y: auto;">
                        <li><h6 class="dropdown-header">Preview Staff Portal As:</h6></li>
                        @foreach($allStaff as $st)
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 {{ $st->id === $employee->id ? 'active fw-bold' : '' }}" href="{{ route('employee.dashboard', ['emp_id' => $st->id]) }}">
                                    <img src="{{ $st->avatar }}" style="width: 20px; height: 20px; border-radius: 50%; object-fit: cover;">
                                    <span>{{ $st->name }} ({{ $st->department->name ?? 'General' }})</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="workspace-actions" aria-label="Employee quick actions">
        <div class="workspace-actions-label">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>My shortcuts</span>
        </div>
        <div class="workspace-action-list">
            <a href="{{ route('attendance.mobile') }}" class="workspace-action primary">
                <i class="bi bi-camera-video"></i> Attendance Punch
            </a>
            <a href="{{ route('leaves.apply') }}" class="workspace-action">
                <i class="bi bi-calendar-plus"></i> Request Leave
            </a>
            <a href="#shifts" class="workspace-action">
                <i class="bi bi-calendar-week"></i> My Shifts
            </a>
            <a href="#payslips" class="workspace-action">
                <i class="bi bi-receipt"></i> My Payslips
            </a>
        </div>
    </div>

    <!-- Biometric Face Enrollment Status Card -->
    <div class="card card-custom p-4 mb-4 border-start border-4 {{ $employee->hasReliableFaceBiometric() ? 'border-success' : 'border-warning' }}">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width: 32px; height: 32px; background: {{ $employee->hasReliableFaceBiometric() ? '#dcfce7' : '#fef3c7' }}; color: {{ $employee->hasReliableFaceBiometric() ? '#15803d' : '#b45309' }}; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi {{ $employee->hasReliableFaceBiometric() ? 'bi-shield-fill-check' : 'bi-exclamation-triangle-fill' }}"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-slate-900" style="font-size: 1.1rem;">
                        Facial Biometrics Enrollment Status
                    </h5>
                    @if($employee->hasReliableFaceBiometric())
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                            <i class="bi bi-check-circle-fill me-1"></i> Enrolled by Admin
                        </span>
                    @else
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                            <i class="bi bi-clock-fill me-1"></i> Biometrics Pending
                        </span>
                    @endif
                </div>

                @if($employee->hasReliableFaceBiometric())
                    <p class="text-muted small mb-2">
                        Your facial biometric profile is enrolled in the database. When you position your face before the attendance scanner, your identity is verified and attendance is logged.
                    </p>
                    <div class="d-flex align-items-center gap-3 text-secondary small flex-wrap">
                        <span><strong>Biometric ID:</strong> <code>{{ $employee->face_biometric_id ?? 'BIO-FACE-' . $employee->id }}</code></span>
                        <span><strong>Enrolled:</strong> {{ $employee->face_enrolled_at ? $employee->face_enrolled_at->format('d M Y, h:i A') : 'Enrolled' }}</span>
                        <span><strong>Status:</strong> <span class="text-success fw-bold"><i class="bi bi-shield-check"></i> Active & Verified</span></span>
                    </div>
                @else
                    <p class="text-muted small mb-0">
                        Your facial biometrics have not been enrolled yet. Please contact <strong>HR Administration</strong> to take your facial photo in the Employee Directory before you can clock attendance.
                    </p>
                @endif
            </div>

            <div class="col-12 col-md-4 text-md-end">
                @if(auth()->user()?->isHrManager() || auth()->user()?->role === 'super_admin')
                    <button type="button" class="btn btn-primary fw-bold px-3 py-2 d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#faceEnrollModal" style="border-radius: 10px; font-size: 0.88rem;">
                        <i class="bi bi-camera"></i>
                        <span>{{ $employee->hasReliableFaceBiometric() ? 'Update Face (Admin)' : 'Enroll Face (Admin)' }}</span>
                    </button>
                @else
                    @if($employee->hasReliableFaceBiometric())
                        <a href="{{ route('attendance.mobile') }}" class="btn btn-dark fw-bold px-3 py-2 d-inline-flex align-items-center gap-2" style="border-radius: 10px; font-size: 0.88rem;">
                            <i class="bi bi-fingerprint"></i>
                            <span>Punch Attendance Now</span>
                        </a>
                    @else
                        <span class="badge bg-light text-secondary border px-3 py-2" style="font-size: 0.8rem;">
                            <i class="bi bi-shield-lock me-1"></i> Requires Admin Enrollment
                        </span>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid: Attendance Punch & Leave Balances -->
    <div class="row g-4 mb-4">
        <!-- Today's Attendance Widget -->
        <div class="col-12 col-lg-4">
            <div class="card card-custom p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0 text-slate-900" style="font-size: 1.05rem;">Today's Punch Status</h5>
                    <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.75rem;">{{ date('d M Y') }}</span>
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 mb-3">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Check In</small>
                        <div class="fw-bold text-dark fs-5">
                            {{ $todayAttendance && $todayAttendance->check_in ? date('h:i A', strtotime($todayAttendance->check_in)) : '--:--' }}
                        </div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Check Out</small>
                        <div class="fw-bold text-dark fs-5">
                            {{ $todayAttendance && $todayAttendance->check_out ? date('h:i A', strtotime($todayAttendance->check_out)) : '--:--' }}
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 small text-muted">
                    <span>Hours Worked: <strong>{{ $todayAttendance && $todayAttendance->worked_minutes ? round($todayAttendance->worked_minutes / 60, 1) . ' hrs' : '0.0 hrs' }}</strong></span>
                    <span>Source: <span class="badge bg-primary-subtle text-primary">{{ $todayAttendance->source ?? 'Pending' }}</span></span>
                </div>

                <div class="mt-auto">
                    <a href="{{ route('attendance.face') }}" class="btn btn-outline-primary w-100 fw-bold py-2" style="border-radius: 10px; font-size: 0.85rem;">
                        <i class="bi bi-camera-video me-1"></i> Open Facial Scanner
                    </a>
                </div>
            </div>
        </div>

        <!-- Statutory Leave Balances Widget -->
        <div class="col-12 col-lg-8">
            <div class="card card-custom p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0 text-slate-900" style="font-size: 1.05rem;">My Leave Balances (Statutory)</h5>
                    <a href="{{ route('leaves.apply') }}" class="btn btn-sm btn-primary fw-bold" style="border-radius: 8px; font-size: 0.78rem;">
                        <i class="bi bi-plus-lg me-1"></i> Request Leave
                    </a>
                </div>

                <div class="row g-3">
                    @foreach($leaveStats as $stat)
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded-3 text-center border h-100">
                                <div class="text-muted fw-bold" style="font-size: 0.75rem;">
                                    @if(str_contains($stat['type']->name, 'Paid')) 🌴 Paid Leave
                                    @elseif(str_contains($stat['type']->name, 'Sick')) 🤒 Sick Leave
                                    @elseif(str_contains($stat['type']->name, 'Casual')) ✨ Casual Leave
                                    @else ⚠️ Emergency
                                    @endif
                                </div>
                                <div class="fw-bold text-dark fs-4 my-1">{{ $stat['remaining'] }}</div>
                                <small class="text-muted" style="font-size: 0.7rem;">of {{ $stat['allowed'] }} days</small>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center small text-muted">
                    <span>Bangladesh Labor Act 2006 Statutory Entitlement</span>
                    @if(auth()->user()?->isEmployee())
                        <a href="{{ route('leaves.apply') }}" class="text-decoration-none fw-semibold"><i class="bi bi-plus-circle me-1"></i>Apply for Leave &rarr;</a>
                    @else
                        <a href="{{ route('leaves.calendar') }}" class="text-decoration-none fw-semibold">View Leave Calendar &rarr;</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Shifts & Payslips Row -->
    <div class="row g-4 mb-4">
        <!-- Upcoming Shifts (Agendrix Match) -->
        <div class="col-12 col-lg-6" id="shifts">
            <div class="card card-custom p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0 text-slate-900" style="font-size: 1.05rem;">My Weekly Shift Schedule</h5>
                    @if(!auth()->user()?->isEmployee())
                        <a href="{{ route('schedule.index') }}" class="text-decoration-none small fw-semibold">Shift Planner &rarr;</a>
                    @else
                        <span class="badge bg-light text-secondary border">This Week</span>
                    @endif
                </div>

                <div class="d-flex flex-column gap-2">
                    @forelse($shifts as $shift)
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-3 border bg-light">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-dark px-2 py-1" style="font-size: 0.75rem;">{{ $shift->day_name }}</span>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $shift->time_slot }}</div>
                                    <small class="text-muted">{{ $shift->role_title }} &bull; {{ $shift->break_minutes }}m break</small>
                                </div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Assigned</span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            No individual shifts scheduled for this week. Standard office hours apply (09:00 AM - 05:00 PM).
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Payslips & Compensation -->
        <div class="col-12 col-lg-6" id="payslips">
            <div class="card card-custom p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0 text-slate-900" style="font-size: 1.05rem;">Recent Payslips</h5>
                    <span class="text-muted small">Bangladesh Tax & PF Compliant</span>
                </div>

                <div class="d-flex flex-column gap-2">
                    @forelse($payslips as $pay)
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-3 border bg-light">
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $pay->run->month_year ?? 'January 2026' }}</div>
                                <small class="text-muted">Net Disbursed: <strong class="text-primary">৳{{ number_format($pay->net_salary, 2) }}</strong></small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $pay->payment_status === 'paid' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} rounded-pill">
                                    {{ ucfirst($pay->payment_status) }}
                                </span>
                                <a href="{{ route('payrolls.payslip', $pay->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="View Payslip">
                                    <i class="bi bi-receipt"></i> Slip
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">No payslip records available yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Attendance Logs Table -->
    <div class="card card-custom p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold mb-0 text-slate-900" style="font-size: 1.05rem;">My Recent Attendance Logs</h5>
            <span class="text-muted small">Last 7 Days</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light" style="font-size: 0.8rem;">
                    <tr>
                        <th>Date</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Hours Worked</th>
                        <th>Source / Method</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.85rem;">
                    @forelse($recentAttendances as $att)
                        <tr>
                            <td class="fw-bold text-dark">{{ date('d M, Y', strtotime($att->attendance_date)) }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $att->check_in ?? '--:--' }}</span></td>
                            <td><span class="badge bg-light text-dark border">{{ $att->check_out ?? '--:--' }}</span></td>
                            <td>{{ $att->worked_minutes ? round($att->worked_minutes / 60, 1) . ' hrs' : '0 hrs' }}</td>
                            <td>
                                @if($att->source === 'facial_recognition')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="bi bi-camera-video me-1"></i> Facial AI</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($att->source ?? 'Biometric') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($att->status === 'present')
                                    <span class="badge bg-success-subtle text-success">Present</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">{{ ucfirst($att->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted small">No attendance logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: Enroll Facial Biometrics via Camera -->
    <div class="modal fade" id="faceEnrollModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px;">
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-camera-video text-primary me-2"></i> Facial Biometric Enrollment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <p class="text-muted small mb-3">
                        Position your face in the center of the camera frame. The server captures three live frames, detects the face with SCRFD, and creates a 512-dimensional ArcFace embedding. A liveness consistency check runs before storing the profile.
                    </p>

                    <!-- Camera Container -->
                    <div class="position-relative mx-auto mb-3" style="width: 280px; height: 280px; background: #0f172a; border-radius: 50%; overflow: hidden; border: 4px solid #38bdf8; box-shadow: 0 8px 24px rgba(56, 189, 248, 0.3);">
                        <video id="enrollVideo" autoplay playsinline muted style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1);"></video>
                        <canvas id="enrollCanvas" style="display: none;"></canvas>
                        <div class="position-absolute top-50 start-50 translate-middle text-white text-center" id="enrollCameraPlaceholder">
                            <i class="bi bi-person-circle fs-1 text-info"></i>
                            <div class="small fw-semibold mt-1">Starting camera...</div>
                        </div>
                    </div>

                    <div id="enrollAlert" class="alert alert-info py-2 small mb-3" style="display: none;"></div>

                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" id="startEnrollCamBtn" class="btn btn-outline-secondary btn-sm fw-semibold">
                            <i class="bi bi-arrow-clockwise"></i> Restart Camera
                        </button>
                        <button type="button" id="captureAndEnrollBtn" class="btn btn-primary btn-sm fw-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> Capture & Store in DB
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const enrollModal = document.getElementById('faceEnrollModal');
    const enrollVideo = document.getElementById('enrollVideo');
    const enrollCanvas = document.getElementById('enrollCanvas');
    const placeholder = document.getElementById('enrollCameraPlaceholder');
    const captureBtn = document.getElementById('captureAndEnrollBtn');
    const restartCamBtn = document.getElementById('startEnrollCamBtn');
    const enrollAlert = document.getElementById('enrollAlert');

    let enrollStream = null;

    async function initEnrollCamera() {
        try {
            if (enrollStream) {
                enrollStream.getTracks().forEach(t => t.stop());
            }
            enrollStream = await navigator.mediaDevices.getUserMedia({
                video: { width: 480, height: 480, facingMode: 'user' }
            });
            enrollVideo.srcObject = enrollStream;
            placeholder.style.display = 'none';
        } catch (err) {
            console.warn('Enroll camera access failed, falling back to simulated capture:', err);
            placeholder.style.display = 'block';
            placeholder.innerHTML = `
                <i class="bi bi-camera-video-off fs-1 text-warning"></i>
                <div class="small fw-semibold mt-1">Camera Offline</div>
                <div class="text-white-50" style="font-size: 0.68rem;">Ready for simulated capture</div>
            `;
        }
    }

    if (enrollModal) {
        enrollModal.addEventListener('shown.bs.modal', function() {
            initEnrollCamera();
        });
        enrollModal.addEventListener('hidden.bs.modal', function() {
            if (enrollStream) {
                enrollStream.getTracks().forEach(t => t.stop());
                enrollStream = null;
            }
        });
    }

    if (restartCamBtn) {
        restartCamBtn.addEventListener('click', initEnrollCamera);
    }

    // Capture & Enroll in Database
    captureBtn.addEventListener('click', async function() {
        captureBtn.disabled = true;
        captureBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Generating Biometrics...`;
        enrollAlert.style.display = 'block';
        enrollAlert.className = 'alert alert-info py-2 small mb-3';
        enrollAlert.innerText = 'Capturing three live frames and creating a secure ArcFace biometric profile...';

        // Capture snapshot from video or use existing avatar
        const faceFrames = [];
        if (enrollStream && enrollVideo.videoWidth > 0) {
            enrollCanvas.width = enrollVideo.videoWidth;
            enrollCanvas.height = enrollVideo.videoHeight;
            const ctx = enrollCanvas.getContext('2d');
            for (let frame = 0; frame < 3; frame++) {
                ctx.drawImage(enrollVideo, 0, 0);
                faceFrames.push(enrollCanvas.toDataURL('image/jpeg', 0.8));
                await new Promise(resolve => setTimeout(resolve, 350));
            }
        }
        if (faceFrames.length < 3) {
            enrollAlert.className = 'alert alert-warning py-2 small mb-3';
            enrollAlert.innerText = 'Start the camera and capture three live frames. A single uploaded image is not accepted for secure enrollment.';
            captureBtn.disabled = false;
            captureBtn.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Capture & Store in DB`;
            return;
        }

        try {
            const res = await fetch("{{ route('employee.enroll-face') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    employee_id: {{ $employee->id }},
                    face_frames: faceFrames
                })
            });

            const data = await res.json();
            if (data.success) {
                enrollAlert.className = 'alert alert-success py-2 small mb-3';
                enrollAlert.innerHTML = `<strong>Success!</strong> ${data.message}`;

                setTimeout(() => {
                    window.location.reload();
                }, 1200);
            } else {
                enrollAlert.className = 'alert alert-danger py-2 small mb-3';
                enrollAlert.innerText = data.message || 'Enrollment failed.';
            }
        } catch (err) {
            console.error(err);
            enrollAlert.className = 'alert alert-danger py-2 small mb-3';
            enrollAlert.innerText = 'Error storing biometrics in database.';
        } finally {
            captureBtn.disabled = false;
            captureBtn.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Capture & Store in DB`;
        }
    });
});
</script>
@endpush
