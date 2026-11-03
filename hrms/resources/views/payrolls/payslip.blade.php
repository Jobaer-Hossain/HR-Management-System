<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Payslip - {{ $item->employee->name ?? 'Staff' }} - {{ $item->run->month_year ?? 'Payroll' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8fafc; padding: 40px 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .payslip-container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 44px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }
        @media print {
            body { background: white; padding: 0; }
            .payslip-container { box-shadow: none; padding: 0; border: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="payslip-container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center border-bottom pb-4 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div style="width: 32px; height: 32px; background: #2563eb; color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">EasyTeam Global Ltd.</h3>
                </div>
                <small class="text-muted">Multi-tenant Cloud HRMS & Payroll System (Dhaka, Bangladesh)</small>
            </div>
            <div class="text-end">
                <span class="badge {{ $item->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-2 rounded-pill fw-bold" style="font-size: 0.82rem;">
                    {{ $item->payment_status === 'paid' ? 'DISBURSED (PAID)' : 'PENDING APPROVAL' }}
                </span>
                <div class="mt-2"><small class="text-muted">Advice Ref: <strong>{{ $item->transaction_ref ?? 'BEFTN-2026-001' }}</strong></small></div>
            </div>
        </div>

        <!-- Employee Info -->
        <div class="row g-3 mb-4">
            <div class="col-6">
                <span class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Employee Details</span>
                <div class="fw-bold fs-5 text-dark mt-1">{{ $item->employee->name ?? 'Staff Member' }}</div>
                <div class="text-secondary fw-semibold">{{ $item->employee->designation->title ?? 'Executive' }}</div>
                <div class="text-muted small">Department: {{ $item->employee->department->name ?? 'Operations' }}</div>
                <div class="text-muted small">Employee ID: #{{ str_pad($item->employee->id ?? 1, 5, '0', STR_PAD_LEFT) }}</div>
            </div>
            <div class="col-6 text-end">
                <span class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Payment Details</span>
                <div class="mt-1"><strong>Period:</strong> {{ $item->run->month_year ?? 'January 2026' }}</div>
                <div><strong>Disbursement Mode:</strong> Bank Transfer (BEFTN)</div>
                <div><strong>Currency:</strong> BDT (৳)</div>
                <div><strong>Generated Date:</strong> {{ date('d M, Y') }}</div>
            </div>
        </div>

        <!-- Breakdown Table (Bangladesh Labor Law Standard) -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light" style="font-size: 0.85rem;">
                    <tr>
                        <th style="width: 50%;">Earnings (Allowances)</th>
                        <th class="text-end" style="width: 20%;">Amount (BDT)</th>
                        <th style="width: 30%;">Deductions</th>
                        <th class="text-end" style="width: 20%;">Amount (BDT)</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.88rem;">
                    <tr>
                        <td>Basic Salary (50% Standard)</td>
                        <td class="text-end fw-semibold">৳{{ number_format($item->basic_salary, 2) }}</td>
                        <td>Tax Deduction (IT Act 2023)</td>
                        <td class="text-end text-danger">-৳{{ number_format($item->tax_deduction, 2) }}</td>
                    </tr>
                    <tr>
                        <td>House Rent Allowance (25%)</td>
                        <td class="text-end fw-semibold">৳{{ number_format($item->house_rent, 2) }}</td>
                        <td>Provident Fund (PF 8%)</td>
                        <td class="text-end text-danger">-৳{{ number_format($item->provident_fund, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Medical Allowance</td>
                        <td class="text-end fw-semibold">৳{{ number_format($item->medical_allowance, 2) }}</td>
                        <td>Unpaid Leave Deduction</td>
                        <td class="text-end text-danger">৳0.00</td>
                    </tr>
                    <tr>
                        <td>Conveyance Allowance</td>
                        <td class="text-end fw-semibold">৳{{ number_format($item->conveyance, 2) }}</td>
                        <td>Other Deductions</td>
                        <td class="text-end text-danger">৳0.00</td>
                    </tr>
                    <tr>
                        <td>Festival Bonus / Incentives</td>
                        <td class="text-end text-success fw-semibold">৳{{ number_format($item->festival_bonus, 2) }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr class="table-light fw-bold">
                        <td>Total Gross Earnings</td>
                        <td class="text-end text-dark">৳{{ number_format($item->gross_earnings, 2) }}</td>
                        <td>Total Deductions</td>
                        <td class="text-end text-danger">-৳{{ number_format($item->total_deductions, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Net Disbursed Card -->
        <div class="p-3 bg-light rounded-4 d-flex justify-content-between align-items-center mb-4 border">
            <div>
                <span class="fs-6 fw-bold text-uppercase text-secondary">Net Disbursed Take-Home Salary:</span>
                <div class="text-muted small">Deposited directly to employee salary bank account</div>
            </div>
            <div class="fs-2 fw-bold text-primary">
                ৳{{ number_format($item->net_salary, 2) }}
            </div>
        </div>

        <div class="row pt-4 text-center" style="font-size: 0.8rem; color: #64748b;">
            <div class="col-4">
                <div class="border-top pt-2 mx-3">Prepared By (HR)</div>
            </div>
            <div class="col-4">
                <div class="border-top pt-2 mx-3">Checked By (Accounts)</div>
            </div>
            <div class="col-4">
                <div class="border-top pt-2 mx-3">Employee Acknowledgment</div>
            </div>
        </div>

        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center text-muted small">
            <div>EasyTeam HRMS SaaS Engine v12.70</div>
            <div>Strictly Confidential & Legally Compliant</div>
        </div>

        <div class="mt-4 text-center no-print">
            <button onclick="window.print()" class="btn btn-primary px-4 py-2 fw-bold me-2" style="border-radius: 10px;">
                <i class="bi bi-printer me-1"></i> Print Payslip Voucher
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary px-4 py-2" style="border-radius: 10px;">
                Close Window
            </button>
        </div>
    </div>
</body>
</html>
