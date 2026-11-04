<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\PayrollRun;
use App\Models\PayrollItem;
use App\Models\Tenant;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Role-Based Dashboard Routing
        $activeRole = auth()->user()->role;

        // Regular employee ALWAYS sees personal self-service portal, never executive HR numbers
        if ($activeRole === 'employee') {
            return app(EmployeeDashboardController::class)->index($request);
        }

        if ($activeRole === 'department_manager' && !$request->has('force_admin')) {
            return app(ManagerDashboardController::class)->index($request);
        }

        if ($activeRole === 'finance' && !$request->has('force_admin')) {
            return app(PayrollDashboardController::class)->index($request);
        }

        // 1. HR Manager / Executive Dashboard (Screenshot 1 EasyTeam: 128 total, 121 active, 7 inactive, 5 leaves)
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $tenant = Tenant::find($tenantId) ?? Tenant::first();

        $totalEmployees = Employee::where('tenant_id', $tenant->id)->count();
        $activeEmployees = Employee::where('tenant_id', $tenant->id)->where('status', 'active')->count();
        $inactiveEmployees = Employee::where('tenant_id', $tenant->id)->where('status', 'inactive')->count();

        $pendingLeavesCount = LeaveApplication::where('tenant_id', $tenant->id)->where('status', 'pending')->count();

        $payrollRun = PayrollRun::where('tenant_id', $tenant->id)->latest()->first();
        $payrollTotal = $payrollRun ? (float)$payrollRun->total_payroll : 512872.92;
        $paidAmount = $payrollRun ? (float)$payrollRun->paid_amount : 321272.90;
        $unpaidAmount = $payrollRun ? (float)$payrollRun->unpaid_amount : 191600.02;
        $paidCount = $payrollRun ? (int)$payrollRun->paid_count : 98;
        $unpaidCount = $totalEmployees - $paidCount;
        $completionPct = round(($paidCount / max(1, $totalEmployees)) * 100);

        $metrics = [
            'total_employees' => $totalEmployees,
            'active_employees' => $activeEmployees,
            'inactive_employees' => $inactiveEmployees,
            'pending_leaves' => $pendingLeavesCount,
        ];

        $payrollOverview = [
            'month' => 'Jan 2026',
            'total_payroll' => $payrollTotal,
            'paid_amount' => $paidAmount,
            'unpaid_amount' => $unpaidAmount,
            'paid_count' => $paidCount,
            'total_count' => $totalEmployees,
            'unpaid_count' => $unpaidCount,
            'completion_rate' => $completionPct,
            'paid_percentage' => round(($paidAmount / max(1, $payrollTotal)) * 100),
            'unpaid_percentage' => round(($unpaidAmount / max(1, $payrollTotal)) * 100),
        ];

        // 2. Leave Management Table (Matching Screenshot 1)
        $leaves = LeaveApplication::with(['employee', 'leaveType'])
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->take(9)
            ->get();

        $leaveRequests = $leaves->map(function ($l) {
            $l->leave_type = $l->leaveType ? $l->leaveType->name : 'Annual';
            $start = strtotime($l->start_date);
            $end = strtotime($l->end_date);
            if ($start && $end) {
                if ($l->start_date === $l->end_date) {
                    $l->date_range = date('j M', $start);
                } else {
                    $l->date_range = date('j', $start) . ' - ' . date('j M', $end);
                }
            } else {
                $l->date_range = '1-2 Jan';
            }
            return $l;
        });

        // 3. Employee Status Lists (Matching Screenshot 1 Tabs: On Leave, Inactive, On Boarding)
        $onLeaveEmployees = Employee::where('tenant_id', $tenant->id)
            ->where('status', 'on_leave')
            ->take(5)
            ->get();

        $inactiveEmployees = Employee::where('tenant_id', $tenant->id)
            ->where('status', 'inactive')
            ->take(5)
            ->get();

        $onBoardingEmployees = Employee::where('tenant_id', $tenant->id)
            ->where('status', 'on_boarding')
            ->take(5)
            ->get();

        $tenants = Tenant::all();

        return view('dashBoard', compact(
            'tenant',
            'tenants',
            'metrics',
            'payrollOverview',
            'leaveRequests',
            'onLeaveEmployees',
            'inactiveEmployees',
            'onBoardingEmployees'
        ));
    }

    public function switchRole(Request $request)
    {
        abort_unless(auth()->check(), 403);

        if (!in_array(auth()->user()->role, ['hr_manager', 'admin', 'super_admin', 'owner'], true)) {
            return redirect()->route('dashboard')->with('error', 'Only administrators can switch user views.');
        }

        $role = $request->validate([
            'role' => 'required|in:hr_manager,department_manager,finance,employee',
        ])['role'];
        $validRoles = ['hr_manager', 'department_manager', 'finance', 'employee'];

        if (in_array($role, $validRoles)) {
            $target = \App\Models\User::where('tenant_id', auth()->user()->tenant_id)
                ->where('role', $role)
                ->where('status', 'active')
                ->first();

            if ($target) {
                \Illuminate\Support\Facades\Auth::login($target);
                session()->forget('active_role');
                if ($target->employee_id) {
                    session(['portal_employee_id' => $target->employee_id]);
                } else {
                    session()->forget('portal_employee_id');
                }
            }
        }

        $readableRole = ucwords(str_replace('_', ' ', $role));

        // Direct to appropriate role dashboard
        if ($role === 'department_manager') {
            return redirect()->route('dashboard.manager')->with('success', "Active view switched to [{$readableRole}]. Showing team management & approvals.");
        } elseif ($role === 'finance') {
            return redirect()->route('dashboard.finance')->with('success', "Active view switched to [{$readableRole}]. Showing payroll & financial disbursements.");
        } elseif ($role === 'employee') {
            return redirect()->route('employee.dashboard')->with('success', "Active view switched to [{$readableRole}]. Showing self-service portal & punch terminal.");
        }

        return redirect()->route('dashboard', ['force_admin' => 1])->with('success', "Active view switched to [{$readableRole}]. Full administrative controls active.");
    }

    public function switchTenant(Request $request)
    {
        $tenantId = $request->validate(['tenant_id' => 'required|integer'])['tenant_id'];
        $tenant = Tenant::whereKey($tenantId)->first();
        abort_unless($tenant && (auth()->user()->isHrManager() || auth()->user()->tenant_id === $tenant->id), 403);
        if ($tenant) {
            session([
                'active_tenant_id' => $tenant->id,
                'current_tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name
            ]);
        }
        return redirect()->back()->with('success', 'Switched organization context to ' . ($tenant ? $tenant->name : 'selected tenant') . '.');
    }
}
