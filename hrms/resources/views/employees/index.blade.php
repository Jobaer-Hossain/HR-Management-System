@extends('layouts.app')

@section('content')
<div class="employees-page">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-size: 1.65rem; letter-spacing: -0.5px;">Employee Directory</h2>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">Manage staff records, departments, designations, and salary structures.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('employees.create') }}" class="btn btn-primary fw-bold px-3 py-2 d-flex align-items-center gap-2" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-person-plus-fill"></i>
                <span>Add New Employee</span>
            </a>
        </div>
    </div>

    <!-- Status Tabs Bar (Matching EasyTeam Prototype) -->
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <a href="{{ route('employees.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
           class="status-tab-pill {{ !request('status') || request('status') === 'all' ? 'active' : '' }}">
            <span>All Staff</span>
            <span class="tab-count">{{ $statusCounts['all'] }}</span>
        </a>
        <a href="{{ route('employees.index', array_merge(request()->except('status', 'page'), ['status' => 'active'])) }}"
           class="status-tab-pill {{ request('status') === 'active' ? 'active' : '' }}">
            <span class="status-dot bg-success"></span>
            <span>Active</span>
            <span class="tab-count">{{ $statusCounts['active'] }}</span>
        </a>
        <a href="{{ route('employees.index', array_merge(request()->except('status', 'page'), ['status' => 'on_leave'])) }}"
           class="status-tab-pill {{ request('status') === 'on_leave' ? 'active' : '' }}">
            <span class="status-dot bg-warning"></span>
            <span>On Leave</span>
            <span class="tab-count">{{ $statusCounts['on_leave'] }}</span>
        </a>
        <a href="{{ route('employees.index', array_merge(request()->except('status', 'page'), ['status' => 'on_boarding'])) }}"
           class="status-tab-pill {{ request('status') === 'on_boarding' ? 'active' : '' }}">
            <span class="status-dot bg-info"></span>
            <span>On Boarding</span>
            <span class="tab-count">{{ $statusCounts['on_boarding'] }}</span>
        </a>
        <a href="{{ route('employees.index', array_merge(request()->except('status', 'page'), ['status' => 'inactive'])) }}"
           class="status-tab-pill {{ request('status') === 'inactive' ? 'active' : '' }}">
            <span class="status-dot bg-secondary"></span>
            <span>Inactive</span>
            <span class="tab-count">{{ $statusCounts['inactive'] }}</span>
        </a>
    </div>

    <!-- Search & Department Filter Bar -->
    <div class="card card-custom p-3 mb-4">
        <form method="GET" action="{{ route('employees.index') }}" id="employeeFilterForm">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="search" id="employeeSearchInput" class="form-control ps-5" placeholder="Search employee by name, code, email, or phone..." value="{{ request('search') }}" autocomplete="off" style="border-radius: 10px; font-size: 0.88rem;">
                    </div>
                </div>

                <div class="col-8 col-md-4">
                    <select name="department_id" class="form-select" onchange="document.getElementById('employeeFilterForm').submit()" style="border-radius: 10px; font-size: 0.88rem;">
                        <option value="all">Department (All Departments)</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-4 col-md-3 text-end d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-outline-secondary d-flex align-items-center gap-1" style="border-radius: 10px; font-size: 0.85rem;">
                        <i class="bi bi-sliders"></i> Filter
                    </button>
                    @if(request('search') || (request('department_id') && request('department_id') !== 'all') || (request('status') && request('status') !== 'all'))
                        <a href="{{ route('employees.index') }}" class="btn btn-outline-danger d-flex align-items-center gap-1" style="border-radius: 10px; font-size: 0.85rem;">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Employee Directory Table -->
    <div class="card card-custom p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light" style="font-size: 0.78rem;">
                    <tr>
                        <th style="width: 28%;">Employee</th>
                        <th style="width: 22%;">Department & Role</th>
                        <th style="width: 22%;">Contact</th>
                        <th style="width: 14%;">Monthly Salary</th>
                        <th style="width: 14%;">Status</th>
                        <th style="text-align: end; width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.88rem;">
                    @forelse ($employees as $employee)
                        <tr>
                            <!-- Employee Profile -->
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="position-relative">
                                        <img src="{{ $employee->avatar ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=faces' }}" class="avatar-sm" alt="{{ $employee->name }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                        @if($employee->status === 'active')
                                            <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle" style="width: 10px; height: 10px;"></span>
                                        @elseif($employee->status === 'on_leave')
                                            <span class="position-absolute bottom-0 end-0 p-1 bg-warning border border-white rounded-circle" style="width: 10px; height: 10px;"></span>
                                        @elseif($employee->status === 'on_boarding')
                                            <span class="position-absolute bottom-0 end-0 p-1 bg-info border border-white rounded-circle" style="width: 10px; height: 10px;"></span>
                                        @else
                                            <span class="position-absolute bottom-0 end-0 p-1 bg-secondary border border-white rounded-circle" style="width: 10px; height: 10px;"></span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.9rem; line-height: 1.2;">{{ $employee->name }}</div>
                                        <div class="d-flex align-items-center gap-1 mt-1">
                                            <span class="badge bg-light text-muted border py-0 px-1" style="font-size: 0.7rem;">{{ $employee->employee_code ?? 'EMP-' . str_pad($employee->id, 3, '0', STR_PAD_LEFT) }}</span>
                                            <small class="text-muted" style="font-size: 0.72rem;">Joined: {{ $employee->joining_date ?? 'Jan 2024' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Department & Role -->
                            <td>
                                <span class="badge bg-light text-primary border mb-1" style="font-size: 0.75rem; font-weight: 600;">
                                    {{ $employee->department->name ?? 'General' }}
                                </span>
                                <div class="small fw-semibold text-secondary" style="font-size: 0.8rem;">
                                    {{ $employee->designation->title ?? 'Staff' }}
                                </div>
                            </td>

                            <!-- Contact -->
                            <td>
                                <div class="text-dark" style="font-size: 0.82rem;"><i class="bi bi-envelope me-1 text-muted"></i>{{ $employee->email }}</div>
                                <div class="text-muted" style="font-size: 0.78rem;"><i class="bi bi-telephone me-1 text-muted"></i>{{ $employee->phone ?? '+880 1700-000000' }}</div>
                            </td>

                            <!-- Base Salary -->
                            <td>
                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">৳{{ number_format($employee->salary ?? 45000, 2) }}</div>
                                <small class="text-muted" style="font-size: 0.72rem;">BDT / Month</small>
                            </td>

                            <!-- Status Pill -->
                            <td>
                                @if($employee->status === 'active')
                                    <span class="badge-approved d-inline-flex align-items-center gap-1">
                                        <span style="width: 6px; height: 6px; background-color: #16a34a; border-radius: 50%;"></span>
                                        Active
                                    </span>
                                @elseif($employee->status === 'on_leave')
                                    <span class="badge-pending d-inline-flex align-items-center gap-1">
                                        <span style="width: 6px; height: 6px; background-color: #d97706; border-radius: 50%;"></span>
                                        On Leave
                                    </span>
                                @elseif($employee->status === 'on_boarding')
                                    <span class="badge d-inline-flex align-items-center gap-1" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-weight: 600; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem;">
                                        <span style="width: 6px; height: 6px; background-color: #0284c7; border-radius: 50%;"></span>
                                        On Boarding
                                    </span>
                                @else
                                    <span class="badge-rejected d-inline-flex align-items-center gap-1" style="background: #f1f5f9; color: #475569; border-color: #e2e8f0;">
                                        <span style="width: 6px; height: 6px; background-color: #64748b; border-radius: 50%;"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td style="text-align: end;">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-size: 0.78rem;" title="Edit Employee">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove {{ $employee->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 8px; font-size: 0.78rem;" title="Delete Employee">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                                <div class="fw-bold text-dark fs-6">No employees found</div>
                                <p class="small text-muted mb-3">No staff members match the selected filter or search term.</p>
                                <a href="{{ route('employees.index') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Clean Bootstrap 5 Pagination -->
        @if($employees->hasPages())
            <div class="mt-4 pt-3 border-top">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Status Tab Pills (Matching EasyTeam Prototype) */
    .status-tab-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 10px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.84rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .status-tab-pill:hover {
        background: #f8fafc;
        color: #0f172a;
        border-color: #cbd5e1;
    }
    .status-tab-pill.active {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }
    .status-tab-pill .tab-count {
        background: #f1f5f9;
        color: #334155;
        font-size: 0.72rem;
        padding: 2px 7px;
        border-radius: 20px;
    }
    .status-tab-pill.active .tab-count {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
</style>
@endpush
