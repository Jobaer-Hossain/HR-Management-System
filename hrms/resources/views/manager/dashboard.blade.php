@extends('layouts.app')

@section('content')
<div class="manager-dashboard-page">
    <!-- Header with Department Context Switcher -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h2 class="fw-bold mb-0 text-slate-900" style="font-size: 1.65rem; letter-spacing: -0.5px;">Department Manager Dashboard</h2>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                    <i class="bi bi-people-fill me-1"></i> Team Oversight & Approvals
                </span>
            </div>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">Manage team attendance, approve time-off requests, and plan weekly shift schedules</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Department Switcher Dropdown -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle fw-semibold px-3 py-2 bg-white" type="button" data-bs-toggle="dropdown" style="border-radius: 10px; font-size: 0.85rem;">
                    <i class="bi bi-building text-primary me-1"></i> {{ $department->department_name }} Team
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="font-size: 0.85rem;">
                    <li><h6 class="dropdown-header">Switch Managing Department:</h6></li>
                    @foreach($allDepartments as $dept)
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 {{ $dept->id === $department->id ? 'active fw-bold' : '' }}" href="{{ route('dashboard.manager', ['dept_id' => $dept->id]) }}">
                                <i class="bi bi-diagram-3"></i> {{ $dept->department_name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <a href="{{ route('leaves.calendar') }}" class="btn btn-primary fw-bold px-3 py-2" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-calendar3-range me-1"></i> Team Leave Calendar
            </a>
        </div>
    </div>

    <!-- Row of 4 Team KPI Cards -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="card-stat h-100">
                <div class="d-flex align-items-center gap-2 mb-2 text-secondary" style="font-size: 0.85rem; font-weight: 600;">
                    <div style="width: 26px; height: 26px; background: #eff6ff; color: #2563eb; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-people"></i>
                    </div>
                    <span>Team Roster</span>
                </div>
                <div class="fw-bold text-dark fs-3 mb-1">{{ $teamSize }} <small class="text-muted fs-6 fw-normal">Members</small></div>
                <div class="small text-muted">{{ $department->department_name }} Dept</div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card-stat h-100">
                <div class="d-flex align-items-center gap-2 mb-2 text-secondary" style="font-size: 0.85rem; font-weight: 600;">
                    <div style="width: 26px; height: 26px; background: #ecfdf5; color: #10b981; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <span>Present Today</span>
                </div>
                <div class="fw-bold text-dark fs-3 mb-1">{{ $presentToday }} <small class="text-muted fs-6 fw-normal">Active</small></div>
                <div class="small text-success fw-semibold"><i class="bi bi-check2"></i> Live Clocked In</div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card-stat h-100">
                <div class="d-flex align-items-center gap-2 mb-2 text-secondary" style="font-size: 0.85rem; font-weight: 600;">
                    <div style="width: 26px; height: 26px; background: #fef3c7; color: #d97706; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <span>On Leave Today</span>
                </div>
                <div class="fw-bold text-dark fs-3 mb-1">{{ $onLeaveToday }} <small class="text-muted fs-6 fw-normal">Members</small></div>
                <div class="small text-muted">Statutory Leave</div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card-stat h-100">
                <div class="d-flex align-items-center gap-2 mb-2 text-secondary" style="font-size: 0.85rem; font-weight: 600;">
                    <div style="width: 26px; height: 26px; background: #fee2e2; color: #dc2626; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <span>Pending Approvals</span>
                </div>
                <div class="fw-bold text-dark fs-3 mb-1">{{ $pendingTeamLeavesCount + $pendingManualAttendanceCount }} <small class="text-muted fs-6 fw-normal">Items</small></div>
                <div class="small text-danger fw-semibold">
                    <a href="{{ route('attendance.manual.manage', ['status' => 'pending']) }}" class="text-danger text-decoration-none">
                        {{ $pendingManualAttendanceCount }} Manual Att.
                    </a> &bull; {{ $pendingTeamLeavesCount }} Leaves
                </div>
            </div>
        </div>
    </div>

    @if($pendingManualAttendanceCount > 0)
        <div class="alert alert-warning border-0 rounded-4 shadow-sm p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 38px; height: 38px; background: #fef3c7; color: #b45309; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="bi bi-person-exclamation"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark">{{ $pendingManualAttendanceCount }} Employee Manual Attendance Request(s) Awaiting Your Review</div>
                    <div class="text-muted small">Employees submitted manual punches due to facial camera issues or arriving after cutoff.</div>
                </div>
            </div>
            <a href="{{ route('attendance.manual.manage', ['status' => 'pending']) }}" class="btn btn-warning fw-bold text-dark btn-sm px-3 py-2" style="border-radius: 8px;">
                <i class="bi bi-check-circle me-1"></i> Review & Approve Now
            </a>
        </div>
    @endif

    <!-- Main Two-Column Grid: Team Leaves & Today's Attendance -->
    <div class="row g-4 mb-4">
        <!-- Team Leave Requests Requiring Approval (col-lg-7) -->
        <div class="col-12 col-lg-7">
            <div class="card card-custom p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0 text-slate-900" style="font-size: 1.1rem;">Team Leave Requests & Approvals</h5>
                    <a href="{{ route('leaves.index') }}" class="text-decoration-none small fw-semibold">See All &rarr;</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light" style="font-size: 0.78rem;">
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.85rem;">
                            @forelse($teamLeaves as $leave)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $leave->employee->avatar ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=faces' }}" class="avatar-sm" alt="Emp">
                                            <div>
                                                <div class="fw-bold text-dark">{{ $leave->employee->name }}</div>
                                                <small class="text-muted">{{ $leave->employee->designation->title ?? 'Staff' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $leave->leaveType->name ?? 'Leave' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small text-secondary">{{ date('d M', strtotime($leave->start_date)) }} - {{ date('d M', strtotime($leave->end_date)) }}</div>
                                        <small class="text-muted">{{ $leave->total_days }} day(s)</small>
                                    </td>
                                    <td>
                                        @if($leave->status === 'pending')
                                            <span class="badge-pending">Pending</span>
                                        @elseif($leave->status === 'approved')
                                            <span class="badge-approved">Approved</span>
                                        @else
                                            <span class="badge-rejected">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($leave->status === 'pending')
                                            <div class="d-flex align-items-center justify-content-end gap-1">
                                                <a href="{{ route('leaves.approve', $leave->id) }}" class="btn-circle-action btn-circle-approve" title="Approve Request">
                                                    <i class="bi bi-check"></i>
                                                </a>
                                                <a href="{{ route('leaves.reject', $leave->id) }}" class="btn-circle-action btn-circle-reject" title="Reject Request">
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
                                    <td colspan="5" class="text-center py-4 text-muted small">No pending leave requests for this department.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Today's Team Attendance Roster (col-lg-5) -->
        <div class="col-12 col-lg-5">
            <div class="card card-custom p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0 text-slate-900" style="font-size: 1.1rem;">Team Attendance Today</h5>
                    <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.72rem;">{{ date('d M Y') }}</span>
                </div>

                <div class="d-flex flex-column gap-2" style="max-height: 380px; overflow-y: auto;">
                    @forelse($teamMembers as $member)
                        @php
                            $att = $member->attendances->first();
                        @endphp
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-3 border bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $member->avatar }}" class="avatar-sm" alt="{{ $member->name }}">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $member->name }}</div>
                                    <small class="text-muted">{{ $member->designation->title ?? 'Staff' }}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                @if($member->status === 'on_leave')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">On Leave</span>
                                @elseif($att && $att->check_in)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                        {{ date('h:i A', strtotime($att->check_in)) }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill">Not Clocked In</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">No employees in this department.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Team Weekly Shift Schedule Widget (Agendrix Match) -->
    <div class="card card-custom p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h5 class="fw-bold mb-0 text-slate-900" style="font-size: 1.1rem;">{{ $department->department_name }} Weekly Shift Roster</h5>
                <small class="text-muted">Assigned weekly shifts and break durations</small>
            </div>
            <a href="{{ route('schedule.index') }}" class="btn btn-sm btn-outline-primary fw-semibold" style="border-radius: 8px;">
                <i class="bi bi-pencil me-1"></i> Edit Shifts
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered mb-0 align-middle text-center" style="font-size: 0.82rem;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 200px; text-align: left;" class="ps-3">Team Member</th>
                        @foreach($days as $day)
                            <th>{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($teamMembers as $emp)
                        <tr>
                            <td class="text-start ps-3 fw-bold bg-white">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $emp->avatar }}" class="avatar-sm" style="width: 28px; height: 28px;">
                                    <span>{{ $emp->name }}</span>
                                </div>
                            </td>
                            @foreach($days as $day)
                                @php
                                    $slot = $teamShifts->where('employee_id', $emp->id)->where('day_name', $day)->first();
                                @endphp
                                <td class="p-1">
                                    @if($slot)
                                        <div class="p-1 rounded bg-light border text-start small">
                                            <div class="fw-bold text-primary" style="font-size: 0.72rem;">{{ $slot->time_slot }}</div>
                                            <div class="text-muted" style="font-size: 0.68rem;">{{ $slot->role_title }} ({{ $slot->break_minutes }}m)</div>
                                        </div>
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No team shifts recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
