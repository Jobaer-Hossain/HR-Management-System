@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h2 class="fw-bold mb-0 text-slate-900" style="font-size: 1.65rem; letter-spacing: -0.5px;">Manual Attendance Request</h2>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                    <i class="bi bi-pencil-square me-1"></i> Facial Fallback
                </span>
            </div>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">If facial recognition is unable to recognize your face, camera fails, or you arrive after cutoff, submit your attendance for Branch Manager review.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('attendance.mobile') }}" class="btn btn-outline-secondary fw-semibold px-3 py-2 bg-white" style="border-radius: 10px; font-size: 0.85rem;">
                <i class="bi bi-camera-video me-1"></i> Self Check-In / Face Terminal
            </a>
            <a href="{{ route('employee.dashboard') }}" class="btn btn-outline-secondary fw-semibold px-3 py-2 bg-white" style="border-radius: 10px; font-size: 0.85rem;">
                <i class="bi bi-grid me-1"></i> Employee Portal
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Request Submission Form -->
        <div class="col-12 col-lg-5">
            <div class="card card-custom p-4 p-md-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <span class="badge bg-primary text-white rounded-circle p-2"><i class="bi bi-pencil"></i></span>
                    <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">Submit Attendance Punch</h5>
                </div>

                <form action="{{ route('attendance.manual.store') }}" method="POST">
                    @csrf

                    <!-- Applicant (Read-only for employee) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Applicant Employee</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person-badge"></i></span>
                            <input type="text" class="form-control bg-light border-start-0 fw-semibold text-dark" value="{{ $employee->name }} ({{ $employee->employee_code ?? 'EMP-'.$employee->id }})" readonly>
                        </div>
                        <div class="form-text small">{{ $employee->department->name ?? 'General' }} &bull; {{ $employee->designation->title ?? 'Staff' }}</div>
                    </div>

                    <!-- Date Field -->
                    <div class="mb-3">
                        <label for="attendance_date" class="form-label fw-semibold small text-dark">Attendance Date</label>
                        <input type="date" class="form-control @error('attendance_date') is-invalid @enderror" id="attendance_date" name="attendance_date" value="{{ old('attendance_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required style="border-radius: 8px;">
                        @error('attendance_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Punch Type -->
                    <div class="mb-3">
                        <label for="punch_type" class="form-label fw-semibold small text-dark">Punch Type</label>
                        <select class="form-select @error('punch_type') is-invalid @enderror" id="punch_type" name="punch_type" required onchange="updateTimeInputs(this.value)" style="border-radius: 8px;">
                            <option value="check_in" {{ old('punch_type') === 'check_in' ? 'selected' : '' }}>Morning Check-In</option>
                            <option value="check_out" {{ old('punch_type') === 'check_out' ? 'selected' : '' }}>Evening Check-Out</option>
                            <option value="both" {{ old('punch_type') === 'both' ? 'selected' : '' }}>Full Day (Both Check-In & Check-Out)</option>
                        </select>
                        @error('punch_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Requested Check-in Time -->
                    <div class="mb-3" id="checkInTimeGroup">
                        <label for="requested_check_in" class="form-label fw-semibold small text-dark">Requested Check-In Time</label>
                        <input type="time" class="form-control @error('requested_check_in') is-invalid @enderror" id="requested_check_in" name="requested_check_in" value="{{ old('requested_check_in', '09:00') }}" style="border-radius: 8px;">
                        @error('requested_check_in')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Requested Check-out Time -->
                    <div class="mb-3 d-none" id="checkOutTimeGroup">
                        <label for="requested_check_out" class="form-label fw-semibold small text-dark">Requested Check-Out Time</label>
                        <input type="time" class="form-control @error('requested_check_out') is-invalid @enderror" id="requested_check_out" name="requested_check_out" value="{{ old('requested_check_out', '17:00') }}" style="border-radius: 8px;">
                        @error('requested_check_out')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Reason / Remarks -->
                    <div class="mb-3">
                        <label for="reason" class="form-label fw-semibold small text-dark">Reason for Manual Submission</label>
                        <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3" placeholder="e.g. Facial scanner camera did not recognize my face due to lighting / Camera device error / Arrived at 10:45 AM due to transport delay" required style="border-radius: 8px;">{{ old('reason') }}</textarea>
                        <div class="form-text small">Please provide an honest description for your Branch Manager.</div>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-warning w-100 fw-bold text-dark py-2" style="border-radius: 10px;">
                        <i class="bi bi-send-check me-1"></i> Submit for Branch Manager Approval
                    </button>
                </form>
            </div>
        </div>

        <!-- History of Past Manual Requests Column -->
        <div class="col-12 col-lg-7">
            <div class="card card-custom p-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom flex-wrap gap-2">
                    <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">My Manual Attendance Submissions</h5>
                    <span class="badge bg-light text-muted border font-monospace">{{ $requests->total() }} Submissions</span>
                </div>

                @if($requests->isEmpty())
                    <div class="text-center py-5">
                        <div style="width: 52px; height: 52px; background: #f8fafc; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #94a3b8;" class="mb-3">
                            <i class="bi bi-journal-check"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">No Manual Requests Yet</h6>
                        <p class="text-muted small mb-0">Whenever your face scan cannot be recognized or you miss the check-in window, submitted requests will appear here.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th>Date</th>
                                    <th>Punch Type</th>
                                    <th>Time</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $req)
                                    <tr>
                                        <td class="fw-semibold text-dark">{{ $req->attendance_date->format('d M, Y') }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ ucfirst(str_replace('_', ' ', $req->punch_type)) }}
                                            </span>
                                        </td>
                                        <td class="font-monospace small">
                                            @if($req->punch_type === 'check_in')
                                                In: {{ date('h:i A', strtotime($req->requested_check_in)) }}
                                            @elseif($req->punch_type === 'check_out')
                                                Out: {{ date('h:i A', strtotime($req->requested_check_out)) }}
                                            @else
                                                In: {{ date('h:i A', strtotime($req->requested_check_in)) }}<br>
                                                Out: {{ date('h:i A', strtotime($req->requested_check_out)) }}
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted text-truncate d-inline-block" style="max-width: 180px;" title="{{ $req->reason }}">
                                                {{ $req->reason }}
                                            </span>
                                            @if($req->manager_remarks)
                                                <div class="small text-secondary" style="font-size: 0.72rem;"><em>Note: {{ $req->manager_remarks }}</em></div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($req->status === 'approved')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Approved
                                                </span>
                                                <div class="small text-muted" style="font-size: 0.68rem;">By {{ $req->reviewed_by_name ?? 'Manager' }}</div>
                                            @elseif($req->status === 'rejected')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
                                                    <i class="bi bi-x-circle-fill me-1"></i> Rejected
                                                </span>
                                                <div class="small text-muted" style="font-size: 0.68rem;">By {{ $req->reviewed_by_name ?? 'Manager' }}</div>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill">
                                                    <i class="bi bi-hourglass-split me-1"></i> Pending Approval
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $requests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateTimeInputs(punchType) {
    const inGroup = document.getElementById('checkInTimeGroup');
    const outGroup = document.getElementById('checkOutTimeGroup');

    if (punchType === 'check_in') {
        inGroup.classList.remove('d-none');
        outGroup.classList.add('d-none');
    } else if (punchType === 'check_out') {
        inGroup.classList.add('d-none');
        outGroup.classList.remove('d-none');
    } else {
        inGroup.classList.remove('d-none');
        outGroup.classList.remove('d-none');
    }
}
document.addEventListener('DOMContentLoaded', function() {
    updateTimeInputs(document.getElementById('punch_type').value);
});
</script>
@endpush
@endsection
