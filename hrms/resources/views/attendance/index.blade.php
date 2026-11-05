@extends('layouts.app')

@section('content')
<div class="attendance-page">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-size: 1.6rem; letter-spacing: -0.5px;">Check In / Check Out</h2>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">Biometric & Geofenced Time Tracking (Numla System)</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('attendance.manual.manage') }}" class="btn btn-outline-secondary fw-semibold px-3 py-2 bg-white" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-shield-check me-1"></i> Manual Approvals
            </a>
            @if(in_array(auth()->user()?->role, ['hr_manager', 'super_admin', 'admin']))
                <a href="{{ route('attendance.policy.edit') }}" class="btn btn-outline-secondary fw-semibold px-3 py-2 bg-white" style="border-radius: 10px; font-size: 0.88rem;">
                    <i class="bi bi-sliders me-1"></i> Cutoff Rules
                </a>
            @endif
            <a href="{{ route('attendance.face') }}" class="btn btn-primary fw-bold px-3 py-2" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-camera-video me-1"></i> Facial AI Scanner
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

    <!-- Center Card matching Screenshot 2 (Numla) -->
    <div class="row justify-content-center mb-5">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card card-custom p-4 text-center" style="box-shadow: 0 10px 30px -10px rgba(0,0,0,0.06); border-radius: 24px;">
                <!-- User avatar & Welcome back -->
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                    <img src="{{ $employee->avatar ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=faces' }}" alt="Avatar" class="avatar-sm" style="width: 44px; height: 44px; border: 2px solid #e2e8f0;">
                    <h5 class="fw-bold mb-0 text-slate-900" style="font-size: 1.2rem;">Welcome Back, {{ $employee->name ?? 'William' }}!</h5>
                </div>

                <!-- Digital Time & Date -->
                <div id="liveClock" class="fw-bold text-dark" style="font-size: 2.5rem; letter-spacing: -1px; line-height: 1.1;">
                    {{ date('h:i A') }}
                </div>
                <div class="text-muted fw-semibold mb-4" style="font-size: 0.95rem;">
                    {{ date('l, d F Y') }}
                </div>

                <!-- Biometric Fingerprint Button (Check In / Check Out) -->
                <div class="my-3 d-flex justify-content-center">
                    @if($todayAttendance && $todayAttendance->check_in && !$todayAttendance->check_out)
                        <!-- Already Checked in -> Show Check Out button (Red Gradient matching Numla) -->
                        <form action="{{ route('attendance.checkout') }}" method="POST">
                            @csrf
                            <input type="hidden" name="employee_id" value="{{ $employee->id ?? 1 }}">
                            <button type="submit" class="fingerprint-btn checkout-glow" title="Click to Check Out">
                                <div class="fingerprint-inner">
                                    <i class="bi bi-fingerprint"></i>
                                    <span class="btn-label">Check Out</span>
                                </div>
                            </button>
                        </form>
                    @else
                        <!-- Checked out or Not checked in -> Show Check In button (Blue Gradient matching Numla) -->
                        <form action="{{ route('attendance.checkin') }}" method="POST">
                            @csrf
                            <input type="hidden" name="employee_id" value="{{ $employee->id ?? 1 }}">
                            <button type="submit" class="fingerprint-btn checkin-glow" title="Click to Check In">
                                <div class="fingerprint-inner">
                                    <i class="bi bi-fingerprint"></i>
                                    <span class="btn-label">Check In</span>
                                </div>
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Break Time Action -->
                <div class="mt-3 mb-3">
                    <form action="{{ route('attendance.break') }}" method="POST">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $employee->id ?? 1 }}">
                        <button type="submit" class="btn btn-link text-decoration-none fw-bold text-primary p-0" style="font-size: 0.95rem;">
                            <i class="bi bi-cup-hot me-1"></i>
                            {{ ($todayAttendance && $todayAttendance->break_start && !$todayAttendance->break_end) ? 'End Break Time' : 'Start Break Time' }}
                        </button>
                    </form>
                </div>

                <!-- Location Note (Matching Screenshot 2: "You are 10 Km away from your office") -->
                <div class="d-flex align-items-center justify-content-center gap-1 text-muted" style="font-size: 0.85rem;">
                    <i class="bi bi-geo-alt text-secondary"></i>
                    <span>{{ $todayAttendance->location_note ?? 'You are 10 Km away from your office.' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3 Bottom Stat Cards (Matching Screenshot 2) -->
    <div class="row g-4 justify-content-center mb-5">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="row g-3">
                <!-- Check In Card -->
                <div class="col-4">
                    <div class="card card-custom p-3 text-center h-100">
                        <div class="mx-auto mb-2 icon-box-blue">
                            <i class="bi bi-box-arrow-in-right"></i>
                        </div>
                        <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                            {{ $todayAttendance && $todayAttendance->check_in ? date('h:i a', strtotime($todayAttendance->check_in)) : '09:41 am' }}
                        </div>
                        <div class="text-muted" style="font-size: 0.75rem;">Check In</div>
                    </div>
                </div>

                <!-- Check Out Card -->
                <div class="col-4">
                    <div class="card card-custom p-3 text-center h-100">
                        <div class="mx-auto mb-2 icon-box-blue">
                            <i class="bi bi-box-arrow-right"></i>
                        </div>
                        <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                            {{ $todayAttendance && $todayAttendance->check_out ? date('h:i a', strtotime($todayAttendance->check_out)) : '-----' }}
                        </div>
                        <div class="text-muted" style="font-size: 0.75rem;">Check Out</div>
                    </div>
                </div>

                <!-- Hours Worked Card -->
                <div class="col-4">
                    <div class="card card-custom p-3 text-center h-100">
                        <div class="mx-auto mb-2 icon-box-blue">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                            {{ $todayAttendance && $todayAttendance->worked_minutes ? round($todayAttendance->worked_minutes / 60, 1) . ' hrs' : '5.2 hrs' }}
                        </div>
                        <div class="text-muted" style="font-size: 0.75rem;">Hours Worked</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Attendance Activity Table -->
    <div class="card card-custom p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold mb-0" style="font-size: 1.1rem;">Today's Attendance Logs</h5>
            <span class="text-muted" style="font-size: 0.85rem;">Live synced with Biometric Terminal</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light" style="font-size: 0.8rem;">
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Source / Location</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.88rem;">
                    @forelse($attendances as $att)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $att->employee->avatar ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=faces' }}" class="avatar-sm" alt="Emp">
                                    <div>
                                        <div class="fw-bold">{{ $att->employee->name ?? 'Staff' }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $att->employee->designation->title ?? 'Employee' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $att->attendance_date }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $att->check_in ?? '--:--' }}</span></td>
                            <td><span class="badge bg-light text-dark border">{{ $att->check_out ?? '--:--' }}</span></td>
                            <td class="text-muted" style="font-size: 0.8rem;">
                                <i class="bi bi-geo-alt text-primary"></i> {{ $att->location_note ?? 'Biometric Office HQ' }}
                            </td>
                            <td>
                                @if($att->status === 'present')
                                    <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Present</span>
                                @elseif($att->status === 'on_leave')
                                    <span class="badge bg-warning-subtle text-warning px-2 py-1 rounded-pill">On Leave</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill">{{ ucfirst($att->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No records found.</td>
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
    /* Biometric Fingerprint Button Styles (Matching Screenshot 2) */
    .fingerprint-btn {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        border: none;
        outline: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        padding: 0;
    }
    .fingerprint-btn:hover {
        transform: scale(1.05);
    }
    .checkin-glow {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        box-shadow: 0 12px 30px rgba(59, 130, 246, 0.4);
    }
    .checkout-glow {
        background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
        box-shadow: 0 12px 30px rgba(244, 63, 94, 0.4);
    }
    .fingerprint-inner {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: white;
    }
    .fingerprint-inner i {
        font-size: 3.2rem;
        line-height: 1;
        margin-bottom: 4px;
    }
    .fingerprint-inner .btn-label {
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .icon-box-blue {
        width: 44px;
        height: 44px;
        background: #2563eb;
        color: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
</style>
@endpush

@push('scripts')
<script>
    // Live clock updater
    setInterval(function() {
        const now = new Date();
        let hours = now.getHours();
        let minutes = now.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        hours = hours < 10 ? '0' + hours : hours;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        const strTime = hours + ':' + minutes + ' ' + ampm;
        const el = document.getElementById('liveClock');
        if (el) el.innerText = strTime;
    }, 1000);
</script>
@endpush
