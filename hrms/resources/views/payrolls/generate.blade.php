@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 750px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Generate Payroll</h2>
            <p class="text-muted mb-0">Calculate and disburse compensation for staff members.</p>
        </div>
        <a href="{{ route('payrolls.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Payroll
        </a>
    </div>

    <div class="card p-4 shadow-sm">
        <form action="{{ route('payrolls.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Select Employee <span class="text-danger">*</span></label>
                <select name="emp_id" id="emp_select" class="form-select" required onchange="updateSalary()">
                    <option value="">-- Choose Employee --</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}" data-salary="{{ $emp->salary }}">
                            {{ $emp->name }} ({{ $emp->department }} - ${{ number_format($emp->salary, 2) }}/mo)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Pay Period / Month <span class="text-danger">*</span></label>
                    <input type="text" name="month" class="form-control" value="{{ date('F Y') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Base Salary ($) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="salary" id="salary_input" class="form-control" placeholder="0.00" required>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Bonus / Allowances ($)</label>
                    <input type="number" step="0.01" name="bonus" class="form-control" value="0.00" placeholder="0.00">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Deductions / Taxes ($)</label>
                    <input type="number" step="0.01" name="deductions" class="form-control" value="0.00" placeholder="0.00">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('payrolls.index') }}" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-success px-4">
                    <i class="bi bi-cash-stack me-1"></i> Disburse Payroll
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function updateSalary() {
        const select = document.getElementById('emp_select');
        const salary = select.options[select.selectedIndex].getAttribute('data-salary');
        if (salary) {
            document.getElementById('salary_input').value = salary;
        }
    }
</script>
@endsection
