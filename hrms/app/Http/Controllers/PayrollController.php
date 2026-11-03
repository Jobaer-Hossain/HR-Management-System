<?php

namespace App\Http\Controllers;

use App\Models\PayrollRun;
use App\Models\PayrollItem;
use App\Models\Employee;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * Display payroll list & overview matching Screenshot 1 + SRS
     */
    public function index(Request $request)
    {
        $tenantId = session('current_tenant_id', 1);

        // Get latest payroll run for the tenant
        $activeRun = PayrollRun::where('tenant_id', $tenantId)
            ->latest()
            ->first();

        if (!$activeRun) {
            $activeRun = PayrollRun::latest()->first();
        }

        $itemsQuery = PayrollItem::with('employee.department', 'employee.designation');
        if ($activeRun) {
            $itemsQuery->where('payroll_run_id', $activeRun->id);
        }

        if ($request->filled('status')) {
            $itemsQuery->where('payment_status', $request->status);
        }

        $items = $itemsQuery->paginate(15);
        $runs = PayrollRun::where('tenant_id', $tenantId)->latest()->get();

        return view('payrolls.index', compact('activeRun', 'items', 'runs'));
    }

    /**
     * Show generate form
     */
    public function generate()
    {
        $tenantId = session('current_tenant_id', 1);
        $employees = Employee::where('tenant_id', $tenantId)->where('status', 'active')->get();
        return view('payrolls.generate', compact('employees'));
    }

    /**
     * Process payroll generation for all active employees
     */
    public function store(Request $request)
    {
        $tenantId = session('current_tenant_id', 1);

        $monthYear = $request->input('month_year', date('F Y'));
        $employees = Employee::where('tenant_id', $tenantId)->where('status', 'active')->get();

        $totalPayroll = 0;
        $totalEmployees = $employees->count();

        $payrollRun = PayrollRun::create([
            'tenant_id' => $tenantId,
            'month_year' => $monthYear,
            'total_employees' => $totalEmployees,
            'paid_count' => 0,
            'unpaid_count' => $totalEmployees,
            'total_payroll' => 0,
            'paid_amount' => 0,
            'unpaid_amount' => 0,
            'status' => 'processing',
        ]);

        foreach ($employees as $emp) {
            $salary = $emp->salary ?? 45000;
            $basic = $salary * 0.50;
            $houseRent = $salary * 0.25;
            $medical = 4000.00;
            $conveyance = 3000.00;
            $bonus = $request->has('include_bonus') ? 5000.00 : 0.00;
            $gross = $basic + $houseRent + $medical + $conveyance + $bonus;

            $tax = $gross > 50000 ? $gross * 0.05 : 0;
            $pf = $basic * 0.08;
            $deductions = $tax + $pf;
            $net = $gross - $deductions;

            $totalPayroll += $net;

            PayrollItem::create([
                'tenant_id' => $tenantId,
                'payroll_run_id' => $payrollRun->id,
                'employee_id' => $emp->id,
                'basic_salary' => $basic,
                'house_rent' => $houseRent,
                'medical_allowance' => $medical,
                'conveyance' => $conveyance,
                'festival_bonus' => $bonus,
                'gross_earnings' => $gross,
                'tax_deduction' => $tax,
                'provident_fund' => $pf,
                'total_deductions' => $deductions,
                'net_salary' => $net,
                'payment_status' => 'unpaid',
                'payment_method' => 'bank_transfer',
                'transaction_ref' => 'BEFTN-' . date('Ym') . '-' . str_pad($emp->id, 5, '0', STR_PAD_LEFT),
            ]);
        }

        $payrollRun->update([
            'total_payroll' => $totalPayroll,
            'unpaid_amount' => $totalPayroll,
        ]);

        return redirect()->route('payrolls.index')->with('success', 'Payroll batch generated successfully!');
    }

    /**
     * Toggle item payment status (Paid / Unpaid)
     */
    public function togglePayment($id)
    {
        $item = PayrollItem::findOrFail($id);
        $newStatus = $item->payment_status === 'paid' ? 'unpaid' : 'paid';

        $item->update([
            'payment_status' => $newStatus,
            'paid_at' => $newStatus === 'paid' ? now() : null,
        ]);

        // Recalculate run totals
        if ($item->payroll_run_id) {
            $run = PayrollRun::find($item->payroll_run_id);
            if ($run) {
                $paidItems = PayrollItem::where('payroll_run_id', $run->id)->where('payment_status', 'paid');
                $unpaidItems = PayrollItem::where('payroll_run_id', $run->id)->where('payment_status', 'unpaid');

                $run->update([
                    'paid_count' => $paidItems->count(),
                    'unpaid_count' => $unpaidItems->count(),
                    'paid_amount' => $paidItems->sum('net_salary'),
                    'unpaid_amount' => $unpaidItems->sum('net_salary'),
                ]);
            }
        }

        return back()->with('success', "Payment status updated to {$newStatus}.");
    }

    /**
     * Show printable Payslip voucher
     */
    public function payslip($id)
    {
        $item = PayrollItem::with(['employee.department', 'employee.designation', 'run'])->findOrFail($id);
        return view('payrolls.payslip', compact('item'));
    }
}
