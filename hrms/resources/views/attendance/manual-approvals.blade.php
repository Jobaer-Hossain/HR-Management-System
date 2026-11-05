@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h2 class="fw-bold mb-0 text-slate-900" style="font-size: 1.65rem; letter-spacing: -0.5px;">Manual Attendance Approvals</h2>
                @if($pendingCount > 0)
                    <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                        {{ $pendingCount }} Pending Review
                    </span>
                @else
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                        All Caught Up
                    </span>
                @endif
            </div>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">Review employee manual attendance requests when facial recognition failed or check-in occurred after cutoff.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary fw-semibold px-3 py-2 bg-white" style="border-radius: 10px; font-size: 0.85rem;">
                <i class="bi bi-clock-history me-1"></i> Attendance Log
            </a>
            @if(in_array(auth()->user()->role, ['hr_manager', 'super_admin', 'admin']))
                <a href="{{ route('attendance.policy.edit') }}" class="btn btn-outline-primary fw-semibold px-3 py-2 bg-white" style="border-radius: 10px; font-size: 0.85rem;">
                    <i class="bi bi-sliders me-1"></i> Cutoff Timing Rules
                </a>
            @endif
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

    <!-- Filter Tabs -->
    <div class="card card-custom p-3 mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="btn-group" role="group">
                <a href="{{ route('attendance.manual.manage') }}" class="btn btn-sm {{ !request()->filled('status') || request('status') === 'all' ? 'btn-dark' : 'btn-outline-secondary bg-white' }}" style="font-size: 0.82rem; border-radius: 8px 0 0 8px;">
                    All Requests
                </a>
                <a href="{{ route('attendance.manual.manage', ['status' => 'pending']) }}" class="btn btn-sm {{ request('status') === 'pending' ? 'btn-dark' : 'btn-outline-secondary bg-white' }}" style="font-size: 0.82rem;">
                    Pending ({{ $pendingCount }})
                </a>
                <a href="{{ route('attendance.manual.manage', ['status' => 'approved']) }}" class="btn btn-sm {{ request('status') === 'approved' ? 'btn-dark' : 'btn-outline-secondary bg-white' }}" style="font-size: 0.82rem;">
                    Approved
                </a>
                <a href="{{ route('attendance.manual.manage', ['status' => 'rejected']) }}" class="btn btn-sm {{ request('status') === 'rejected' ? 'btn-dark' : 'btn-outline-secondary bg-white' }}" style="font-size: 0.82rem; border-radius: 0 8px 8px 0;">
                    Rejected
                </a>
            </div>

            <div class="text-muted small">
                Showing {{ $requests->count() }} of {{ $requests->total() }} requests
            </div>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card card-custom p-4">
        @if($requests->isEmpty())
            <div class="text-center py-5">
                <div style="width: 56px; height: 56px; background: #f8fafc; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.8rem; color: #94a3b8;" class="mb-3">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">No Attendance Requests Found</h5>
                <p class="text-muted small mb-0">No manual attendance requests are currently matching this filter.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="bg-light text-muted small">
                        <tr>
                            <th class="ps-3">Employee</th>
                            <th>Department</th>
                            <th>Date</th>
                            <th>Punch Type</th>
                            <th>Requested Time</th>
                            <th>Reason / Explanation</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $req->employee->face_snapshot ?? $req->employee->avatar ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=60' }}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                                        <div>
                                            <div class="fw-bold text-dark">{{ $req->employee->name }}</div>
                                            <div class="text-muted small" style="font-size: 0.72rem;">{{ $req->employee->employee_code ?? 'EMP-'.$req->employee->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $req->employee->department->name ?? 'General' }}
                                    </span>
                                </td>
                                <td class="fw-semibold text-dark">{{ $req->attendance_date->format('d M, Y') }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        {{ ucfirst(str_replace('_', ' ', $req->punch_type)) }}
                                    </span>
                                </td>
                                <td class="font-monospace small">
                                    @if($req->punch_type === 'check_in')
                                        <strong>In:</strong> {{ date('h:i A', strtotime($req->requested_check_in)) }}
                                    @elseif($req->punch_type === 'check_out')
                                        <strong>Out:</strong> {{ date('h:i A', strtotime($req->requested_check_out)) }}
                                    @else
                                        <strong>In:</strong> {{ date('h:i A', strtotime($req->requested_check_in)) }}<br>
                                        <strong>Out:</strong> {{ date('h:i A', strtotime($req->requested_check_out)) }}
                                    @endif
                                </td>
                                <td>
                                    <div class="text-dark small" style="max-width: 260px;" title="{{ $req->reason }}">
                                        {{ $req->reason }}
                                    </div>
                                    <div class="text-muted" style="font-size: 0.7rem;">
                                        Submitted {{ $req->created_at->diffForHumans() }}
                                    </div>
                                </td>
                                <td>
                                    @if($req->status === 'approved')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                            <i class="bi bi-check-circle-fill me-1"></i> Approved
                                        </span>
                                        <div class="text-muted" style="font-size: 0.68rem;">By {{ $req->reviewed_by_name ?? 'Manager' }}</div>
                                    @elseif($req->status === 'rejected')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
                                            <i class="bi bi-x-circle-fill me-1"></i> Rejected
                                        </span>
                                        <div class="text-muted" style="font-size: 0.68rem;">By {{ $req->reviewed_by_name ?? 'Manager' }}</div>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill">
                                            <i class="bi bi-hourglass-split me-1"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    @if($req->status === 'pending')
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <form action="{{ route('attendance.manual.approve', $req->id) }}" method="POST" onsubmit="return confirm('Approve manual attendance for {{ $req->employee->name }} on {{ $req->attendance_date->format('d M, Y') }}?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success fw-bold px-3 py-1" style="border-radius: 8px; font-size: 0.78rem;">
                                                    <i class="bi bi-check-lg me-1"></i> Approve
                                                </button>
                                            </form>

                                            <form action="{{ route('attendance.manual.reject', $req->id) }}" method="POST" onsubmit="return confirm('Reject manual attendance request for {{ $req->employee->name }}?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" style="border-radius: 8px; font-size: 0.78rem;" title="Reject request">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-muted small" style="font-size: 0.76rem;">Closed</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
