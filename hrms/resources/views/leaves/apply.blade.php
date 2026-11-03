@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 700px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Apply for Leave</h2>
            <p class="text-muted mb-0">Submit a leave request for administrative review.</p>
        </div>
        <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Leaves
        </a>
    </div>

    <div class="card p-4 shadow-sm">
        <form action="{{ route('leaves.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Select Employee <span class="text-danger">*</span></label>
                <select name="emp_id" class="form-select" required>
                    <option value="">-- Choose Staff Member --</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('emp_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }} ({{ $employee->department }} - {{ $employee->designation }})
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
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', date('Y-m-d', strtotime('+3 days'))) }}" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Reason for Absence <span class="text-danger">*</span></label>
                <textarea name="reason" rows="4" class="form-control" placeholder="Provide reason or context for requested leave..." required>{{ old('reason') }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('leaves.index') }}" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-warning text-dark fw-semibold px-4">
                    <i class="bi bi-send me-1"></i> Submit Application
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
