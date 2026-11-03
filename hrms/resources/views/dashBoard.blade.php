@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <!-- Top Bar with Title and Search Input -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-0 text-slate-900" style="font-size: 1.65rem; letter-spacing: -0.5px;">Dashboard</h2>
        </div>
        <div class="search-bar-wrap">
            <i class="bi bi-search"></i>
            <input type="text" class="form-control" placeholder="Search">
        </div>
    </div>

    <!-- Row of 3 Metric Cards (Matching Screenshot 1 EasyTeam) -->
    <div class="row g-4 mb-4">
        <!-- 1. Total Employees -->
        <div class="col-12 col-md-4">
            <div class="card-stat h-100 position-relative">
                <div class="d-flex align-items-center gap-2 mb-3 text-secondary" style="font-size: 0.9rem; font-weight: 600;">
                    <div style="width: 28px; height: 28px; background: #eff6ff; color: #2563eb; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <span>Total Employees</span>
                </div>
                <div class="d-flex align-items-baseline gap-3 mb-3">
                    <span class="fw-bold" style="font-size: 2.1rem; letter-spacing: -1px; color: #0f172a;">{{ $metrics['total_employees'] }}</span>
                    <span class="badge rounded-pill" style="background: #ecfdf5; color: #10b981; font-weight: 600; font-size: 0.78rem; padding: 4px 10px;">
                        +12 this month
                    </span>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="font-size: 0.8rem; color: #64748b;">
                    <span>Active: <strong class="text-dark">{{ $metrics['active_employees'] }}</strong> &nbsp; Inactive: <strong class="text-dark">{{ $metrics['inactive_employees'] }}</strong></span>
                    <a href="{{ route('employees.index') }}" class="text-decoration-none fw-semibold text-secondary d-flex align-items-center gap-1">
                        Details <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Leave Requests -->
        <div class="col-12 col-md-4">
            <div class="card-stat h-100 position-relative">
                <div class="d-flex align-items-center gap-2 mb-3 text-secondary" style="font-size: 0.9rem; font-weight: 600;">
                    <div style="width: 28px; height: 28px; background: #eff6ff; color: #2563eb; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <span>Leave Requests</span>
                </div>
                <div class="d-flex align-items-baseline gap-3 mb-3">
                    <span class="fw-bold" style="font-size: 2.1rem; letter-spacing: -1px; color: #0f172a;">{{ $metrics['pending_leaves'] }}</span>
                    <span class="badge rounded-pill" style="background: #fef2f2; color: #ef4444; font-weight: 600; font-size: 0.78rem; padding: 4px 10px;">
                        <i class="bi bi-arrow-down-short"></i> 28% from last month
                    </span>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="font-size: 0.8rem; color: #64748b;">
                    <span>2 Fewer Request</span>
                    <a href="{{ route('leaves.index') }}" class="text-decoration-none fw-semibold text-secondary d-flex align-items-center gap-1">
                        Details <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- 3. Payroll Completion -->
        <div class="col-12 col-md-4">
            <div class="card-stat h-100 position-relative">
                <div class="d-flex align-items-center gap-2 mb-3 text-secondary" style="font-size: 0.9rem; font-weight: 600;">
                    <div style="width: 28px; height: 28px; background: #eff6ff; color: #2563eb; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <span>Payroll Completion</span>
                </div>
                <div class="d-flex align-items-baseline gap-3 mb-3">
                    <span class="fw-bold" style="font-size: 2.1rem; letter-spacing: -1px; color: #0f172a;">
                        {{ $payrollOverview['paid_count'] }}<span class="text-muted" style="font-size: 1.25rem; font-weight: 500;">/{{ $payrollOverview['total_count'] }}</span>
                    </span>
                    <span class="badge rounded-pill" style="background: #fffbeb; color: #f59e0b; font-weight: 600; font-size: 0.78rem; padding: 4px 10px;">
                        {{ $payrollOverview['completion_rate'] }}% completed
                    </span>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="font-size: 0.8rem; color: #64748b;">
                    <span>{{ $payrollOverview['unpaid_count'] }} Payment Remaining</span>
                    <a href="{{ route('payrolls.index') }}" class="text-decoration-none fw-semibold text-secondary d-flex align-items-center gap-1">
                        Details <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Two-Column Content Grid -->
    <div class="row g-4">
        <!-- Left Column: Leave Management Table (col-lg-8) -->
        <div class="col-12 col-lg-8">
            <div class="card card-custom p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h5 class="fw-bold mb-0" style="font-size: 1.1rem; color: #0f172a;">Leave Management</h5>
                    <a href="{{ route('leaves.index') }}" class="text-decoration-none fw-semibold text-secondary d-flex align-items-center gap-1" style="font-size: 0.84rem;">
                        See All <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-borderless" style="margin-bottom: 0;">
                        <thead>
                            <tr class="text-muted border-bottom" style="font-size: 0.78rem; font-weight: 600; letter-spacing: 0.04em;">
                                <th style="padding-bottom: 14px; width: 35%;">Employee Name</th>
                                <th style="padding-bottom: 14px; width: 18%;">Leave Type</th>
                                <th style="padding-bottom: 14px; width: 22%;">Date</th>
                                <th style="padding-bottom: 14px; width: 15%;">Status</th>
                                <th style="padding-bottom: 14px; text-align: end; width: 10%;">Action</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.88rem;">
                            @forelse($leaveRequests as $leave)
                                <tr class="border-bottom" style="border-color: #f8fafc !important;">
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ $leave->employee->avatar ?? 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop&crop=faces' }}" alt="{{ $leave->employee->name }}" class="avatar-sm">
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $leave->employee->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-secondary fw-semibold">{{ $leave->leave_type ?? 'Annual' }}</td>
                                    <td class="text-secondary">{{ $leave->date_range }}</td>
                                    <td>
                                        @if($leave->status === 'pending')
                                            <span class="badge-pending d-inline-flex align-items-center gap-1">
                                                <span style="width: 5px; height: 5px; background: #f59e0b; border-radius: 50%;"></span>
                                                Pending
                                            </span>
                                        @elseif($leave->status === 'approved')
                                            <span class="badge-approved d-inline-flex align-items-center gap-1">
                                                <span style="width: 5px; height: 5px; background: #10b981; border-radius: 50%;"></span>
                                                Approved
                                            </span>
                                        @else
                                            <span class="badge-rejected d-inline-flex align-items-center gap-1">
                                                <span style="width: 5px; height: 5px; background: #ef4444; border-radius: 50%;"></span>
                                                Rejected
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align: end;">
                                        @if($leave->status === 'pending')
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <a href="{{ route('leaves.reject', $leave->id) }}" class="btn-circle-action btn-circle-reject" title="Reject Leave">
                                                    <i class="bi bi-x-circle"></i>
                                                </a>
                                                <a href="{{ route('leaves.approve', $leave->id) }}" class="btn-circle-action btn-circle-approve" title="Approve Leave">
                                                    <i class="bi bi-check-circle"></i>
                                                </a>
                                            </div>
                                        @else
                                            <button class="btn btn-sm btn-link text-muted p-0" title="View Application Note">
                                                <i class="bi bi-file-earmark-text" style="font-size: 1.1rem;"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No leave requests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Employee Status & Payroll Overview (col-lg-4) -->
        <div class="col-12 col-lg-4 d-flex flex-column gap-4">
            <!-- Card 1: Employee Status -->
            <div class="card card-custom p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0" style="font-size: 1.05rem; color: #0f172a;">Employee Status</h5>
                    <a href="{{ route('employees.index') }}" class="text-decoration-none fw-semibold text-secondary d-flex align-items-center gap-1" style="font-size: 0.82rem;">
                        See All <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <!-- Tabs: On Leave, Inactive, On Boarding -->
                <ul class="nav nav-pills nav-fill mb-3 p-1 bg-light rounded-3" id="statusTabs" role="tablist" style="font-size: 0.78rem;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold py-1" id="onleave-tab" data-bs-toggle="pill" data-bs-target="#tab-onleave" type="button" role="tab" style="border-radius: 8px;">On Leave</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-muted fw-bold py-1" id="inactive-tab" data-bs-toggle="pill" data-bs-target="#tab-inactive" type="button" role="tab" style="border-radius: 8px;">Inactive</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-muted fw-bold py-1" id="onboarding-tab" data-bs-toggle="pill" data-bs-target="#tab-onboarding" type="button" role="tab" style="border-radius: 8px;">On Boarding</button>
                    </li>
                </ul>

                <div class="tab-content" id="statusTabsContent">
                    <!-- Tab: On Leave -->
                    <div class="tab-pane fade show active" id="tab-onleave" role="tabpanel">
                        <div class="d-flex flex-column gap-3">
                            @forelse($onLeaveEmployees as $emp)
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $emp->avatar ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=faces' }}" alt="{{ $emp->name }}" class="avatar-sm">
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.86rem; line-height: 1.2;">{{ $emp->name }}</div>
                                        <div class="text-muted" style="font-size: 0.76rem;">Leave {{ $emp->leave_range ?? 'Active Leave' }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-3 text-muted" style="font-size: 0.82rem;">No employees currently on leave.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Tab: Inactive -->
                    <div class="tab-pane fade" id="tab-inactive" role="tabpanel">
                        <div class="d-flex flex-column gap-3">
                            @forelse($inactiveEmployees as $emp)
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $emp->avatar ?? 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop&crop=faces' }}" alt="{{ $emp->name }}" class="avatar-sm">
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.86rem; line-height: 1.2;">{{ $emp->name }}</div>
                                        <div class="text-muted" style="font-size: 0.76rem;">Resigned / Inactive</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-3 text-muted" style="font-size: 0.82rem;">No inactive employees.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Tab: On Boarding -->
                    <div class="tab-pane fade" id="tab-onboarding" role="tabpanel">
                        <div class="d-flex flex-column gap-3">
                            @forelse($onBoardingEmployees as $emp)
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $emp->avatar ?? 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=100&h=100&fit=crop&crop=faces' }}" alt="{{ $emp->name }}" class="avatar-sm">
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.86rem; line-height: 1.2;">{{ $emp->name }}</div>
                                        <div class="text-muted" style="font-size: 0.76rem;">Orientation & Documentation</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-3 text-muted" style="font-size: 0.82rem;">No new employees onboarding.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Payroll Overview (Matching Screenshot 1 EasyTeam: $512,872.92) -->
            <div class="card card-custom p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0" style="font-size: 1.05rem; color: #0f172a;">Payroll Overview</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border py-1 px-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" style="font-size: 0.78rem; font-weight: 600; border-radius: 8px;">
                            {{ $payrollOverview['month'] }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="font-size: 0.82rem;">
                            <li><a class="dropdown-item" href="#">Jan 2026</a></li>
                            <li><a class="dropdown-item" href="#">Feb 2026</a></li>
                            <li><a class="dropdown-item" href="#">Dec 2025</a></li>
                        </ul>
                    </div>
                </div>

                <div class="text-muted" style="font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Total Payroll</div>
                <div class="fw-bold mb-3" style="font-size: 1.85rem; letter-spacing: -1px; color: #0f172a;">
                    ${{ number_format($payrollOverview['total_payroll'], 2) }}
                </div>

                <!-- Progress Bar: Paid vs Unpaid -->
                <div class="progress mb-3" style="height: 14px; border-radius: 8px; background-color: #e2e8f0; overflow: hidden;">
                    <div class="progress-bar" role="progressbar" style="width: {{ $payrollOverview['paid_percentage'] }}%; background-color: #2563eb;" aria-valuenow="{{ $payrollOverview['paid_percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    <div class="progress-bar" role="progressbar" style="width: {{ $payrollOverview['unpaid_percentage'] }}%; background-color: #cbd5e1;" aria-valuenow="{{ $payrollOverview['unpaid_percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <!-- Amount Breakdown Legend -->
                <div class="d-flex flex-column gap-2 mb-4" style="font-size: 0.84rem;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span style="width: 10px; height: 10px; background-color: #2563eb; border-radius: 3px; display: inline-block;"></span>
                            <span class="text-secondary fw-semibold">Paid Amount</span>
                        </div>
                        <span class="fw-bold text-dark">{{ number_format($payrollOverview['paid_amount'], 2) }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span style="width: 10px; height: 10px; background-color: #cbd5e1; border-radius: 3px; display: inline-block;"></span>
                            <span class="text-secondary fw-semibold">Unpaid Amount</span>
                        </div>
                        <span class="fw-bold text-dark">{{ number_format($payrollOverview['unpaid_amount'], 2) }}</span>
                    </div>
                </div>

                <a href="{{ route('payrolls.index') }}" class="btn btn-outline-secondary w-100 fw-bold py-2" style="border-radius: 10px; font-size: 0.85rem; border-color: #e2e8f0; color: #334155;">
                    View All
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
