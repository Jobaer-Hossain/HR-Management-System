@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 700px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Apply for Leave</h2>
            <p class="text-muted mb-0">Submit a leave request for administrative review.</p>
        </div>
        <a href="{{ auth()->user()?->isEmployee() ? route('employee.dashboard') : route('leaves.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card p-4 shadow-sm border-0" style="border-radius: 14px;">
        <form action="{{ route('leaves.store') }}" method="POST">
            @csrf

            @if($employees->count() === 1)
                <input type="hidden" name="employee_id" value="{{ $employees->first()->id }}">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Applicant</label>
                    <input type="text" class="form-control bg-light" value="{{ $employees->first()->name }} ({{ $employees->first()->department->name ?? 'General' }} - {{ $employees->first()->designation->title ?? 'Staff' }})" readonly>
                </div>
            @else
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Employee <span class="text-danger">*</span></label>
                    <select name="employee_id" class="form-select" required>
                        <option value="">-- Choose Staff Member --</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }} ({{ $employee->department->name ?? 'General' }} - {{ $employee->designation->title ?? 'Staff' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label fw-semibold">Leave Type <span class="text-danger">*</span></label>
                <select name="leave_type_id" class="form-select" required>
                    <option value="">-- Select Leave Category --</option>
                    @foreach ($leaveTypes as $type)
                        <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }} ({{ $type->days_allowed ?? 14 }} days allowed)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', date('Y-m-d', strtotime('+2 days'))) }}" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Reason for Absence <span class="text-danger">*</span></label>
                <textarea name="reason" rows="4" class="form-control" placeholder="Provide reason or context for requested leave..." required>{{ old('reason') }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ auth()->user()?->isEmployee() ? route('employee.dashboard') : route('leaves.index') }}" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-primary fw-semibold px-4" style="border-radius: 8px;">
                    <i class="bi bi-send me-1"></i> Submit Application
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
