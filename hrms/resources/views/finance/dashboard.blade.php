@extends('layouts.app')

@section('content')
<div class="finance-dashboard-page">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h2 class="fw-bold mb-0 text-slate-900" style="font-size: 1.65rem; letter-spacing: -0.5px;">Finance & Payroll Specialist Dashboard</h2>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                    <i class="bi bi-shield-check me-1"></i> IT Act 2023 & BEFTN Compliant
                </span>
            </div>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">Disbursement batch control, statutory tax withholding, and BEFTN electronic fund transfers</p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button class="btn btn-outline-secondary fw-semibold px-3 py-2 bg-white" onclick="exportBeftnFile()" style="border-radius: 10px; font-size: 0.85rem;">
                <i class="bi bi-file-earmark-arrow-down text-primary me-1"></i> Export BEFTN Advice
            </button>
            <a href="{{ route('payrolls.generate') }}" class="btn btn-primary fw-bold px-3 py-2" style="border-radius: 10px; font-size: 0.85rem;">
                <i class="bi bi-plus-circle me-1"></i> Generate Payroll Run
            </a>
        </div>
    </div>

    <!-- 4 Main Finance Metric Cards -->
    <div class="row g-4 mb-4">
        <!-- Card 1: Total Payroll -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-stat h-100 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 0.85rem;">Total Payroll Volume</span>
                    <div style="width: 32px; height: 32px; background: #eff6ff; color: #2563eb; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <div class="fw-bold text-dark fs-3 mb-1">${{ number_format($totalPayroll, 2) }}</div>
                <div class="d-flex align-items-center justify-content-between small text-muted">
                    <span>Batch: {{ $activeRun->month_year ?? 'Jan 2026' }}</span>
                    <span class="text-primary fw-bold">{{ $totalEmployees }} Staff</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Disbursed Amount -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-stat h-100 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 0.85rem;">Disbursed Net Pay</span>
                    <div style="width: 32px; height: 32px; background: #ecfdf5; color: #10b981; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
                <div class="fw-bold text-success fs-3 mb-1">${{ number_format($paidAmount, 2) }}</div>
                <div class="d-flex align-items-center justify-content-between small">
                    <span class="text-muted">{{ $paidCount }} Disbursed</span>
                    <span class="badge bg-success-subtle text-success">{{ $completionPct }}% Completed</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Pending Authorization -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-stat h-100 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 0.85rem;">Pending Disbursement</span>
                    <div style="width: 32px; height: 32px; background: #fff7ed; color: #ea580c; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
                <div class="fw-bold text-danger fs-3 mb-1">${{ number_format($unpaidAmount, 2) }}</div>
                <div class="d-flex align-items-center justify-content-between small text-muted">
                    <span>{{ $unpaidCount }} Payments Remaining</span>
                    <span class="badge bg-warning-subtle text-warning">Awaiting Bank</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Statutory Deductions Withheld -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-stat h-100 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 0.85rem;">Tax & PF Retained</span>
                    <div style="width: 32px; height: 32px; background: #f5f3ff; color: #7c3aed; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-file-earmark-ruled"></i>
                    </div>
                </div>
                <div class="fw-bold text-dark fs-3 mb-1">${{ number_format($totalTaxWithheld + $totalProvidentFund, 2) }}</div>
                <div class="d-flex align-items-center justify-content-between small text-muted">
                    <span>Tax: ${{ number_format($totalTaxWithheld, 0) }}</span>
                    <span>PF: ${{ number_format($totalProvidentFund, 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Batch Progress & Statutory Audit Overview -->
    <div class="row g-4 mb-4">
        <!-- Batch Disbursement Progress Bar -->
        <div class="col-12 col-lg-7">
            <div class="card card-custom p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="fw-bold mb-1" style="font-size: 1.05rem;">Active Payroll Run: {{ $activeRun->month_year ?? 'January 2026' }}</h6>
                        <p class="text-muted small mb-0">BEFTN Bank Batch ID: <code>BEFTN-{{ date('Ym') }}-BATCH01</code></p>
                    </div>
                    <span class="badge {{ $activeRun && $activeRun->status === 'completed' ? 'bg-success' : 'bg-primary' }} px-3 py-2 rounded-pill fw-bold">
                        <i class="bi bi-cpu me-1"></i> {{ strtoupper($activeRun->status ?? 'Active') }}
                    </span>
                </div>

                <!-- Progress Bar matching EasyTeam Prototype -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-bold mb-1">
                        <span class="text-primary">{{ $paidCount }} Paid of {{ $totalEmployees }} Employees</span>
                        <span class="text-dark">{{ $completionPct }}% Complete</span>
                    </div>
                    <div class="progress" style="height: 14px; border-radius: 8px; background-color: #e2e8f0;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $completionPct }}%; background: linear-gradient(90deg, #2563eb, #1d4ed8);" aria-valuenow="{{ $completionPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="row g-3 pt-2">
                    <div class="col-4">
                        <div class="p-2 border rounded-3 bg-light">
                            <div class="text-muted small">Paid Volume</div>
                            <div class="fw-bold text-success">${{ number_format($paidAmount, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded-3 bg-light">
                            <div class="text-muted small">Pending Volume</div>
                            <div class="fw-bold text-danger">${{ number_format($unpaidAmount, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded-3 bg-light">
                            <div class="text-muted small">Bank Method</div>
                            <div class="fw-bold text-dark">BEFTN Direct</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statutory Deductions & Tax Audit Breakdown -->
        <div class="col-12 col-lg-5">
            <div class="card card-custom p-4 h-100">
                <h6 class="fw-bold mb-3" style="font-size: 1.05rem;">Statutory Compliance Breakdown</h6>
                <div class="list-group list-group-flush small">
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-lock text-primary"></i>
                            <div>
                                <div class="fw-bold">IT Act 2023 Withholding Tax</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Statutory tax deduction (NBR Bangladesh)</div>
                            </div>
                        </div>
                        <span class="fw-bold text-dark">${{ number_format($totalTaxWithheld, 2) }}</span>
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-safe2 text-success"></i>
                            <div>
                                <div class="fw-bold">Provident Fund (8% Matching)</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Employee 8% + Employer 8% Fund</div>
                            </div>
                        </div>
                        <span class="fw-bold text-dark">${{ number_format($totalProvidentFund, 2) }}</span>
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-house-door text-warning"></i>
                            <div>
                                <div class="fw-bold">House Rent & Conveyance</div>
                                <div class="text-muted" style="font-size: 0.72rem;">25% Basic + Medical 4k + Conveyance 3k</div>
                            </div>
                        </div>
                        <span class="fw-bold text-dark">${{ number_format($totalAllowances, 2) }}</span>
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-gift text-info"></i>
                            <div>
                                <div class="fw-bold">Festival Bonus Accrual</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Eid / Puja Statutory Bonus provision</div>
                            </div>
                        </div>
                        <span class="fw-bold text-dark">${{ number_format($totalBonuses, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Disbursement Register Table -->
    <div class="card card-custom p-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-1 text-slate-900" style="font-size: 1.15rem;">Employee Disbursement Register</h5>
                <p class="text-muted small mb-0">Audit salary calculations, bank transfers, and trigger real-time payment disbursements</p>
            </div>

            <!-- Filter Pills -->
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('dashboard.finance', ['status' => 'all']) }}" class="btn btn-sm {{ !request('status') || request('status') === 'all' ? 'btn-primary' : 'btn-light border' }} fw-semibold px-3" style="border-radius: 8px;">
                    All ({{ $totalEmployees }})
                </a>
                <a href="{{ route('dashboard.finance', ['status' => 'paid']) }}" class="btn btn-sm {{ request('status') === 'paid' ? 'btn-success' : 'btn-light border' }} fw-semibold px-3" style="border-radius: 8px;">
                    Disbursed ({{ $paidCount }})
                </a>
                <a href="{{ route('dashboard.finance', ['status' => 'unpaid']) }}" class="btn btn-sm {{ request('status') === 'unpaid' ? 'btn-warning text-dark' : 'btn-light border' }} fw-semibold px-3" style="border-radius: 8px;">
                    Pending ({{ $unpaidCount }})
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead style="background: #f8fafc; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                    <tr>
                        <th class="border-0 px-3 py-3">Employee</th>
                        <th class="border-0 px-3 py-3">Department</th>
                        <th class="border-0 px-3 py-3">Basic (50%)</th>
                        <th class="border-0 px-3 py-3">Gross</th>
                        <th class="border-0 px-3 py-3">Tax Retained</th>
                        <th class="border-0 px-3 py-3">PF (8%)</th>
                        <th class="border-0 px-3 py-3">Net Pay</th>
                        <th class="border-0 px-3 py-3">Transaction Ref</th>
                        <th class="border-0 px-3 py-3">Disbursement</th>
                        <th class="border-0 px-3 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.88rem;">
                    @forelse($items as $item)
                        <tr class="border-top">
                            <td class="px-3 py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $item->employee->avatar ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100' }}" class="avatar-sm" alt="{{ $item->employee->name ?? 'Staff' }}">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $item->employee->name ?? 'Staff Member' }}</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">{{ $item->employee->employee_code ?? 'EMP-' . str_pad($item->employee_id, 3, '0', STR_PAD_LEFT) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-muted">
                                {{ $item->employee->department->name ?? 'General' }}
                            </td>
                            <td class="px-3 py-3 fw-semibold text-secondary">
                                ${{ number_format($item->basic_salary, 2) }}
                            </td>
                            <td class="px-3 py-3 fw-bold text-dark">
                                ${{ number_format($item->gross_earnings, 2) }}
                            </td>
                            <td class="px-3 py-3 text-danger fw-semibold">
                                -${{ number_format($item->tax_deduction, 2) }}
                            </td>
                            <td class="px-3 py-3 text-secondary fw-semibold">
                                -${{ number_format($item->provident_fund, 2) }}
                            </td>
                            <td class="px-3 py-3 fw-bold text-success fs-6">
                                ${{ number_format($item->net_salary, 2) }}
                            </td>
                            <td class="px-3 py-3">
                                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">
                                    {{ $item->transaction_ref ?? 'BEFTN-PENDING' }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                @if($item->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                                        <i class="bi bi-check-circle-fill me-1"></i> Disbursed
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                                        <i class="bi bi-clock me-1"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <form action="{{ route('payrolls.toggle-payment', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @if($item->payment_status === 'paid')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary px-2 py-1" title="Mark Unpaid" style="font-size: 0.75rem; border-radius: 6px;">
                                                <i class="bi bi-arrow-counterclockwise"></i> Revert
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-sm btn-success px-2 py-1 fw-bold" title="Authorize Disbursement" style="font-size: 0.75rem; border-radius: 6px;">
                                                <i class="bi bi-send-check me-1"></i> Disburse
                                            </button>
                                        @endif
                                    </form>

                                    <a href="{{ route('payrolls.payslip', $item->id) }}" class="btn btn-sm btn-light border px-2 py-1 text-primary" title="Print Payslip Voucher" style="font-size: 0.75rem; border-radius: 6px;">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No payroll items found for this filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted small">
                Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} payroll disbursements
            </div>
            <div>
                {{ $items->links() }}
            </div>
        </div>
    </div>
</div>

<script>
function exportBeftnFile() {
    const csvContent = "data:text/csv;charset=utf-8," 
        + "BEFTN_BATCH_ID,EMP_CODE,BENEFICIARY_NAME,BANK_ROUTING,ACCOUNT_NO,NET_AMOUNT_BDT,TAX_WITHHELD,PAY_STATUS\n"
        @foreach($items as $item)
        + `"BEFTN-{{ date('Ym') }}-BATCH01","{{ $item->employee->employee_code ?? 'EMP-'.str_pad($item->employee_id,3,'0',STR_PAD_LEFT) }}","{{ $item->employee->name ?? 'Staff' }}","020260123","150220198762","{{ $item->net_salary }}","{{ $item->tax_deduction }}","{{ $item->payment_status }}"\n`
        @endforeach;

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `BEFTN_ADVICE_BATCH_{{ date('Y_m_d') }}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endsection
