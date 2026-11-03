@extends('layouts.app')

@section('content')
<div class="employees-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-size: 1.65rem; letter-spacing: -0.5px;">Employee Directory</h2>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">Manage staff records, departments, designations, and salary structures.</p>
        </div>
        <a href="{{ route('employees.create') }}" class="btn btn-primary fw-bold px-3 py-2" style="border-radius: 10px; font-size: 0.88rem;">
            <i class="bi bi-person-plus-fill me-1"></i> Add New Employee
        </a>
    </div>

    <div class="card card-custom p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light" style="font-size: 0.8rem;">
                    <tr>
                        <th>#</th>
                        <th>Employee</th>
                        <th>Contact</th>
                        <th>Department & Role</th>
                        <th>Base Salary (BDT)</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.88rem;">
                    @forelse ($employees as $employee)
                        <tr>
                            <td class="text-muted fw-bold">#{{ $employee->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $employee->avatar ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=faces' }}" class="avatar-sm" alt="Emp">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $employee->name }}</div>
                                        <small class="text-muted">Joined: {{ $employee->joining_date ?? 'Jan 2024' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><i class="bi bi-envelope me-1 text-muted"></i>{{ $employee->email }}</div>
                                <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $employee->phone ?? '+880 1700-000000' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-primary border mb-1">
                                    {{ is_object($employee->department) ? $employee->department->name : ($employee->department ?? 'General') }}
                                </span>
                                <div class="small fw-semibold text-secondary">
                                    {{ is_object($employee->designation) ? $employee->designation->title : ($employee->designation ?? 'Staff') }}
                                </div>
                            </td>
                            <td class="fw-bold text-dark">৳{{ number_format($employee->salary ?? 45000, 2) }}</td>
                            <td>
                                @if($employee->status === 'active')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">Active</span>
                                @elseif($employee->status === 'on_leave')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill">On Leave</span>
                                @elseif($employee->status === 'on_boarding')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill">On Boarding</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-outline-secondary me-1" style="font-size: 0.78rem;">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size: 0.78rem;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No employees registered yet. Click "Add New Employee" to get started.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $employees->links() }}
        </div>
    </div>
</div>
@endsection
