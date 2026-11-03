@extends('layouts.app')

@section('content')
<div class="payroll-page">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-size: 1.65rem; letter-spacing: -0.5px;">Payroll & Compensation</h2>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">Multi-tenant automated payroll engine & compliance</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('payrolls.generate') }}" class="btn btn-primary fw-bold px-3 py-2" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-play-circle-fill me-1"></i> Run Monthly Payroll
            </a>
        </div>
    </div>

    <!-- Active Run Overview Card Matching Screenshot 1 ($512,872.92 Total) -->
    @if($activeRun)
        <div class="card card-custom p-4 mb-4">
            <div class="row align-items-center g-4">
                <div class="col-12 col-md-4 border-end">
                    <div class="text-muted fw-semibold" style="font-size: 0.82rem;">Pay Period Run</div>
                    <h4 class="fw-bold text-dark mb-1">{{ $activeRun->month_year }}</h4>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                        {{ ucfirst($activeRun->status) }}
                    </span>
                    <div class="mt-3">
                        <span class="text-muted" style="font-size: 0.82rem;">Total Payroll:</span>
                        <div class="fw-bold text-dark" style="font-size: 1.75rem; letter-spacing: -1px;">
                            ${{ number_format($activeRun->total_payroll, 2) }}
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-5 border-end">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-semibold text-secondary" style="font-size: 0.85rem;">Disbursement Progress</span>
                        @php
                            $rate = $activeRun->total_employees > 0 ? round(($activeRun->paid_count / $activeRun->total_employees) * 100) : 0;
                        @endphp
                        <span class="fw-bold text-primary">{{ $rate }}% Completed</span>
                    </div>

                    <div class="progress mb-3" style="height: 12px; border-radius: 6px; background-color: #e2e8f0; overflow: hidden;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $rate }}%;"></div>
                    </div>

                    <div class="d-flex justify-content-between" style="font-size: 0.82rem;">
                        <div>
                            <span class="text-muted">Paid Count:</span>
                            <span class="fw-bold text-success">{{ $activeRun->paid_count }}</span>
                            <div class="fw-bold text-dark">${{ number_format($activeRun->paid_amount, 2) }}</div>
                        </div>
                        <div class="text-end">
                            <span class="text-muted">Pending Count:</span>
                            <span class="fw-bold text-warning">{{ $activeRun->unpaid_count }}</span>
                            <div class="fw-bold text-dark">${{ number_format($activeRun->unpaid_amount, 2) }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 text-center">
                    <div class="p-3 bg-light rounded-4">
                        <div class="text-muted small mb-1">Batch BEFTN Export</div>
                        <button class="btn btn-sm btn-outline-dark w-100 fw-bold mb-2">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Bank Advice File
                        </button>
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Bangladesh Electronic Fund Transfer Network</small>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Individual Employees Compensation Table -->
    <div class="card card-custom p-4">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <h5 class="fw-bold mb-0 text-slate-900" style="font-size: 1.1rem;">Disbursement Breakdown</h5>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('payrolls.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-light border' }}" style="font-size: 0.78rem;">All</a>
                <a href="{{ route('payrolls.index', ['status' => 'paid']) }}" class="btn btn-sm {{ request('status') === 'paid' ? 'btn-success' : 'btn-light border' }}" style="font-size: 0.78rem;">Paid</a>
                <a href="{{ route('payrolls.index', ['status' => 'unpaid']) }}" class="btn btn-sm {{ request('status') === 'unpaid' ? 'btn-warning' : 'btn-light border' }}" style="font-size: 0.78rem;">Unpaid</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light" style="font-size: 0.78rem;">
                    <tr>
                        <th>Employee</th>
                        <th>Basic (50%)</th>
                        <th>Allowances</th>
                        <th>Bonus</th>
                        <th>Gross</th>
                        <th>Tax & PF</th>
                        <th>Net Payable</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.85rem;">
                    @forelse($items as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $item->employee->avatar ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=faces' }}" class="avatar-sm" alt="Emp">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $item->employee->name ?? 'Employee' }}</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">{{ $item->employee->designation->title ?? 'Staff' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>৳{{ number_format($item->basic_salary, 2) }}</td>
                            <td class="text-muted" style="font-size: 0.8rem;">
                                ৳{{ number_format($item->house_rent + $item->medical_allowance + $item->conveyance, 2) }}
                            </td>
                            <td class="text-success fw-semibold">
                                {{ $item->festival_bonus > 0 ? '+৳' . number_format($item->festival_bonus, 2) : '-' }}
                            </td>
                            <td class="fw-semibold">৳{{ number_format($item->gross_earnings, 2) }}</td>
                            <td class="text-danger">-৳{{ number_format($item->total_deductions, 2) }}</td>
                            <td class="fw-bold text-primary fs-6">৳{{ number_format($item->net_salary, 2) }}</td>
                            <td>
                                @if($item->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                        <i class="bi bi-check-circle-fill me-1"></i> Paid
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill">
                                        <i class="bi bi-clock-fill me-1"></i> Unpaid
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <form action="{{ route('payrolls.toggle-payment', $item->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $item->payment_status === 'paid' ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="Toggle Paid/Unpaid Status" style="font-size: 0.75rem;">
                                            {{ $item->payment_status === 'paid' ? 'Mark Unpaid' : 'Mark Paid' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('payrolls.payslip', $item->id) }}" class="btn btn-sm btn-outline-primary" target="_blank" title="Print Payslip" style="font-size: 0.75rem;">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No payroll items available. Run Monthly Payroll to generate items.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection
