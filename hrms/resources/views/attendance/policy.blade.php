@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h2 class="fw-bold mb-0 text-slate-900" style="font-size: 1.65rem; letter-spacing: -0.5px;">Attendance Policy & Timing Rules</h2>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                    <i class="bi bi-clock-history me-1"></i> Cutoff Configuration
                </span>
            </div>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">Configure daily check-in windows, late cutoffs, and checkout rules for your organization.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary fw-semibold px-3 py-2 bg-white" style="border-radius: 10px; font-size: 0.85rem;">
                <i class="bi bi-arrow-left me-1"></i> Back to Attendance
            </a>
            <a href="{{ route('attendance.manual.manage') }}" class="btn btn-outline-primary fw-semibold px-3 py-2 bg-white" style="border-radius: 10px; font-size: 0.85rem;">
                <i class="bi bi-check2-circle me-1"></i> Review Manual Requests
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

    <div class="row g-4">
        <!-- Settings Form Column -->
        <div class="col-12 col-lg-8">
            <div class="card card-custom p-4 p-md-5">
                <form action="{{ route('attendance.policy.update') }}" method="POST">
                    @csrf

                    <!-- Policy Name -->
                    <div class="mb-4">
                        <label for="policy_name" class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em;">Policy Name</label>
                        <input type="text" class="form-control @error('policy_name') is-invalid @enderror" id="policy_name" name="policy_name" value="{{ old('policy_name', $policy->policy_name) }}" required style="border-radius: 10px; height: 44px;">
                        @error('policy_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Check-in Timing Window -->
                    <div class="card bg-light border-0 p-3 rounded-4 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-success text-white rounded-pill p-2"><i class="bi bi-box-arrow-in-right"></i></span>
                            <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">Morning Check-In Timing & Cutoff</h5>
                        </div>
                        <p class="text-muted small mb-3">Employees can only record their morning attendance within this window. If an employee tries to check in after the cutoff time, direct check-in will be blocked, and they must submit a manual request to their Branch Manager.</p>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="check_in_start" class="form-label fw-semibold small text-dark">Check-In Opens (Earliest Time)</label>
                                <input type="time" class="form-control @error('check_in_start') is-invalid @enderror" id="check_in_start" name="check_in_start" value="{{ old('check_in_start', substr($policy->check_in_start, 0, 5)) }}" required style="border-radius: 8px;">
                                <div class="form-text small">Earliest allowed arrival (e.g., 07:00 AM)</div>
                                @error('check_in_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="check_in_end" class="form-label fw-semibold small text-danger">Check-In Cutoff Time (Latest Allowed)</label>
                                <input type="time" class="form-control @error('check_in_end') is-invalid @enderror border-danger" id="check_in_end" name="check_in_end" value="{{ old('check_in_end', substr($policy->check_in_end, 0, 5)) }}" required style="border-radius: 8px;">
                                <div class="form-text small text-danger fw-semibold">Direct check-in is blocked after this time (e.g., 10:30 AM)</div>
                                @error('check_in_end')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="enforce_checkin_cutoff" name="enforce_checkin_cutoff" value="1" {{ old('enforce_checkin_cutoff', $policy->enforce_checkin_cutoff) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark small" for="enforce_checkin_cutoff">
                                        Enforce strict cutoff (Block direct check-in after cutoff time and prompt for Manual Approval)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checkout Timing Window -->
                    <div class="card bg-light border-0 p-3 rounded-4 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary text-white rounded-pill p-2"><i class="bi bi-box-arrow-right"></i></span>
                            <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">Evening Check-Out Window</h5>
                        </div>
                        <p class="text-muted small mb-3">Defines when regular check-out opens. When an employee scans their face in the morning, the system registers check-in and will not check them out until this time.</p>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="check_out_start" class="form-label fw-semibold small text-dark">Check-Out Opens (Earliest Time)</label>
                                <input type="time" class="form-control @error('check_out_start') is-invalid @enderror" id="check_out_start" name="check_out_start" value="{{ old('check_out_start', substr($policy->check_out_start, 0, 5)) }}" required style="border-radius: 8px;">
                                <div class="form-text small">Earliest allowed checkout without manager approval (e.g., 16:00 / 04:00 PM)</div>
                                @error('check_out_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="check_out_end" class="form-label fw-semibold small text-dark">Check-Out Closes (Latest Time)</label>
                                <input type="time" class="form-control @error('check_out_end') is-invalid @enderror" id="check_out_end" name="check_out_end" value="{{ old('check_out_end', substr($policy->check_out_end, 0, 5)) }}" required style="border-radius: 8px;">
                                <div class="form-text small">Latest time checkout is accepted (e.g., 23:59 / 11:59 PM)</div>
                                @error('check_out_end')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="grace_period_minutes" class="form-label fw-semibold small text-dark">Grace Period (Minutes)</label>
                                <input type="number" class="form-control @error('grace_period_minutes') is-invalid @enderror" id="grace_period_minutes" name="grace_period_minutes" value="{{ old('grace_period_minutes', $policy->grace_period_minutes) }}" min="0" max="120" required style="border-radius: 8px;">
                                <div class="form-text small">Tolerance window before marking arrival as late</div>
                                @error('grace_period_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Cutoff Message Displayed to Employees -->
                    <div class="mb-4">
                        <label for="auto_block_message" class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em;">Cutoff Exceeded Alert Message</label>
                        <textarea class="form-control @error('auto_block_message') is-invalid @enderror" id="auto_block_message" name="auto_block_message" rows="2" style="border-radius: 10px;">{{ old('auto_block_message', $policy->auto_block_message) }}</textarea>
                        <div class="form-text small">This message will be shown on the facial gate kiosk and mobile punch screen when an employee attempts to check in after the cutoff time.</div>
                        @error('auto_block_message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Save Action -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary fw-bold px-4 py-2" style="border-radius: 10px;">
                            <i class="bi bi-check-lg me-1"></i> Save Attendance Policy Rules
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar Info Guide -->
        <div class="col-12 col-lg-4">
            <div class="card card-custom p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div style="width: 36px; height: 36px; background: #eff6ff; color: #2563eb; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <h6 class="fw-bold mb-0 text-slate-900">How Timing Rules Work</h6>
                </div>
                <ul class="text-muted small ps-3 mb-0" style="line-height: 1.6;">
                    <li class="mb-2"><strong>Morning Arrival:</strong> Scanning the face on arrival records Check-In. Repeat scans in the morning will not trigger an accidental Check-Out.</li>
                    <li class="mb-2"><strong>Late Cutoff:</strong> Once the clock passes the Check-In Cutoff (e.g., 10:30 AM), direct facial punch is blocked.</li>
                    <li class="mb-2"><strong>Manager Approval:</strong> Employees arriving after cutoff or with biometric camera difficulties must submit a <em>Manual Attendance Request</em>.</li>
                    <li><strong>Branch Verification:</strong> The Branch Manager or Department Manager reviews the reason and approves the punch with a single click.</li>
                </ul>
            </div>

            <div class="card card-custom p-4 bg-light border-0">
                <h6 class="fw-bold text-dark mb-2">Current Active Policy Summary</h6>
                <div class="d-flex justify-content-between py-2 border-bottom small">
                    <span class="text-muted">Check-In Opens:</span>
                    <span class="fw-bold text-dark">{{ date('h:i A', strtotime($policy->check_in_start)) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom small">
                    <span class="text-muted">Check-In Cutoff:</span>
                    <span class="fw-bold text-danger">{{ date('h:i A', strtotime($policy->check_in_end)) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom small">
                    <span class="text-muted">Check-Out Opens:</span>
                    <span class="fw-bold text-dark">{{ date('h:i A', strtotime($policy->check_out_start)) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 small">
                    <span class="text-muted">Strict Cutoff:</span>
                    <span class="badge {{ $policy->enforce_checkin_cutoff ? 'bg-success' : 'bg-secondary' }}">
                        {{ $policy->enforce_checkin_cutoff ? 'Active' : 'Disabled' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
