@extends('layouts.app')

@section('content')
<div class="employees-edit-page" style="max-width: 900px; margin: 0 auto;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="font-size: 1.65rem; letter-spacing: -0.5px;">Edit Employee Profile</h2>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">Update details and compensation for {{ $employee->name }}.</p>
        </div>
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1" style="border-radius: 10px; font-size: 0.88rem;">
            <i class="bi bi-arrow-left"></i> Back to Directory
        </a>
    </div>

    <div class="card card-custom p-4">
        <form action="{{ route('employees.update', $employee->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size: 0.85rem;">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $employee->name) }}" required style="border-radius: 10px; font-size: 0.88rem;">
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size: 0.85rem;">Work Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email) }}" required style="border-radius: 10px; font-size: 0.88rem;">
                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size: 0.85rem;">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}" style="border-radius: 10px; font-size: 0.88rem;">
                    @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size: 0.85rem;">Joining Date</label>
                    <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', $employee->joining_date) }}" style="border-radius: 10px; font-size: 0.88rem;">
                    @error('joining_date') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size: 0.85rem;">Department <span class="text-danger">*</span></label>
                    <select name="department_id" class="form-select" required style="border-radius: 10px; font-size: 0.88rem;">
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>
                                {{ $dept->department_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size: 0.85rem;">Designation / Title <span class="text-danger">*</span></label>
                    <select name="designation_id" class="form-select" required style="border-radius: 10px; font-size: 0.88rem;">
                        @foreach($designations as $desig)
                            <option value="{{ $desig->id }}" {{ old('designation_id', $employee->designation_id) == $desig->id ? 'selected' : '' }}>
                                {{ $desig->designation_name }} ({{ $desig->designation_level ?? 'Standard' }})
                            </option>
                        @endforeach
                    </select>
                    @error('designation_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size: 0.85rem;">Monthly Base Salary (BDT) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light" style="border-radius: 10px 0 0 10px;">৳</span>
                        <input type="number" step="0.01" name="salary" class="form-control" value="{{ old('salary', $employee->salary) }}" required style="border-radius: 0 10px 10px 0; font-size: 0.88rem;">
                    </div>
                    @error('salary') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size: 0.85rem;">Employment Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required style="border-radius: 10px; font-size: 0.88rem;">
                        <option value="active" {{ old('status', $employee->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="on_leave" {{ old('status', $employee->status) === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                        <option value="on_boarding" {{ old('status', $employee->status) === 'on_boarding' ? 'selected' : '' }}>On Boarding</option>
                        <option value="inactive" {{ old('status', $employee->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold" style="font-size: 0.85rem;">Avatar Image URL (Optional)</label>
                <input type="url" name="avatar" class="form-control" value="{{ old('avatar', $employee->avatar) }}" style="border-radius: 10px; font-size: 0.88rem;">
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="{{ route('employees.index') }}" class="btn btn-light border px-3" style="border-radius: 10px; font-size: 0.88rem;">Cancel</a>
                <button type="submit" class="btn btn-primary fw-bold px-4" style="border-radius: 10px; font-size: 0.88rem;">
                    <i class="bi bi-check-lg me-1"></i> Update Employee Profile
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
