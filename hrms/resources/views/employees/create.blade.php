@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Add New Employee</h2>
            <p class="text-muted mb-0">Enter staff details and compensation information.</p>
        </div>
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Directory
        </a>
    </div>

    <div class="card p-4 shadow-sm">
        <form action="{{ route('employees.store') }}" method="POST">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Jane Smith" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Work Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="jane.smith@company.com" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+1-555-0199">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Joining Date</label>
                    <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', date('Y-m-d')) }}">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                    <input type="text" name="department" class="form-control" value="{{ old('department') }}" placeholder="e.g. Engineering, Finance, HR" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Designation / Title <span class="text-danger">*</span></label>
                    <input type="text" name="designation" class="form-control" value="{{ old('designation') }}" placeholder="e.g. Senior Software Engineer" required>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Base Monthly Salary ($) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="salary" class="form-control" value="{{ old('salary') }}" placeholder="e.g. 7500.00" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Employment Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="on_leave" {{ old('status') === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('employees.index') }}" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i> Save Employee</button>
            </div>
        </form>
    </div>
</div>
@endsection
