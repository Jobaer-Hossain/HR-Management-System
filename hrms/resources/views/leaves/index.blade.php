@extends('layouts.app')

@section('content')
<div class="leaves-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-size: 1.65rem; letter-spacing: -0.5px;">Leave Applications</h2>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">Manage staff time-off requests, statutory leave types, and approvals.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('leaves.calendar') }}" class="btn btn-outline-primary fw-semibold px-3 py-2" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-calendar3-range me-1"></i> Interactive Calendar
            </a>
            <a href="{{ route('leaves.apply') }}" class="btn btn-primary fw-bold px-3 py-2" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-plus-lg me-1"></i> Apply for Leave
            </a>
        </div>
    </div>

    <div class="card card-custom p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light" style="font-size: 0.8rem;">
                    <tr>
                        <th>#</th>
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>Date Range</th>
                        <th>Days</th>
                        <th>Reason / Note</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.88rem;">
                    @forelse ($leaves as $leave)
                        <tr>
                            <td class="text-muted fw-bold">#{{ $leave->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $leave->employee->avatar ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=faces' }}" class="avatar-sm" alt="Emp">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $leave->employee->name ?? 'Staff Member' }}</div>
                                        <small class="text-muted">{{ $leave->employee->department->name ?? 'General' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    {{ $leave->leaveType->name ?? 'Annual Leave' }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-secondary">{{ $leave->start_date }} <span class="text-muted">to</span> {{ $leave->end_date }}</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill">{{ $leave->total_days }} days</span>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $leave->reason }}">{{ $leave->reason }}</div>
                            </td>
                            <td>
                                @if($leave->status === 'approved')
                                    <span class="badge-approved d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-check-circle-fill"></i> Approved
                                    </span>
                                @elseif($leave->status === 'rejected')
                                    <span class="badge-rejected d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-x-circle-fill"></i> Rejected
                                    </span>
                                @else
                                    <span class="badge-pending d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-clock-fill"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($leave->status === 'pending')
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <a href="{{ route('leaves.approve', $leave->id) }}" class="btn-circle-action btn-circle-approve" title="Approve">
                                            <i class="bi bi-check"></i>
                                        </a>
                                        <a href="{{ route('leaves.reject', $leave->id) }}" class="btn-circle-action btn-circle-reject" title="Reject">
                                            <i class="bi bi-x"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="text-muted small">Processed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No leave requests found. Click "Apply for Leave" to create one.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $leaves->links() }}
        </div>
    </div>
</div>
@endsection
