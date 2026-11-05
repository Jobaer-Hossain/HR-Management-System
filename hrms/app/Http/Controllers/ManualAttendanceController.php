<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\ManualAttendanceRequest;
use App\Models\Tenant;
use Illuminate\Http\Request;

class ManualAttendanceController extends Controller
{
    /**
     * Show employee self-service manual attendance request form and history
     */
    public function create()
    {
        $tenantId = (int) (auth()->user()?->tenant_id ?? session('current_tenant_id') ?? 1);
        $tenant = Tenant::find($tenantId) ?? Tenant::first();

        $employee = $this->currentEmployee($tenant->id);
        abort_unless($employee, 404, 'No employee record found for your account.');

        $requests = ManualAttendanceRequest::with(['branch', 'department'])
            ->where('tenant_id', $tenant->id)
            ->where('employee_id', $employee->id)
            ->latest()
            ->paginate(10);

        return view('attendance.manual-request', compact('tenant', 'employee', 'requests'));
    }

    /**
     * Store employee's manual attendance request
     */
    public function store(Request $request)
    {
        $tenantId = (int) (auth()->user()?->tenant_id ?? session('current_tenant_id') ?? 1);
        $employee = $this->currentEmployee($tenantId);
        abort_unless($employee, 404, 'No employee record found for your account.');

        $validated = $request->validate([
            'attendance_date' => 'required|date|before_or_equal:today',
            'punch_type' => 'required|in:check_in,check_out,both',
            'requested_check_in' => 'nullable|required_if:punch_type,check_in,both|date_format:H:i',
            'requested_check_out' => 'nullable|required_if:punch_type,check_out,both|date_format:H:i',
            'reason' => 'required|string|min:5|max:1000',
        ]);

        ManualAttendanceRequest::create([
            'tenant_id' => $tenantId,
            'employee_id' => $employee->id,
            'branch_id' => $employee->branch_id,
            'department_id' => $employee->department_id,
            'attendance_date' => $validated['attendance_date'],
            'punch_type' => $validated['punch_type'],
            'requested_check_in' => !empty($validated['requested_check_in']) ? $validated['requested_check_in'] . ':00' : null,
            'requested_check_out' => !empty($validated['requested_check_out']) ? $validated['requested_check_out'] . ':00' : null,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('attendance.manual.create')->with('success', 'Your manual attendance request has been submitted for Branch Manager approval.');
    }

    /**
     * Manager and HR Admin approval list
     */
    public function manage(Request $request)
    {
        $tenantId = (int) (auth()->user()?->tenant_id ?? session('current_tenant_id') ?? 1);
        $tenant = Tenant::find($tenantId) ?? Tenant::first();

        $query = ManualAttendanceRequest::with(['employee.department', 'employee.designation', 'branch'])
            ->where('tenant_id', $tenant->id);

        // Branch / Department Manager scope
        if (auth()->user()?->role === 'department_manager') {
            $managerEmp = Employee::where('tenant_id', $tenantId)
                ->where(function ($q) {
                    $q->where('id', auth()->user()->employee_id)
                      ->orWhere('email', auth()->user()->email);
                })->first();

            if ($managerEmp && $managerEmp->department_id) {
                $query->where('department_id', $managerEmp->department_id);
            }
        }

        // Filter by status if provided
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(15);
        $pendingCount = ManualAttendanceRequest::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->when(auth()->user()?->role === 'department_manager' && isset($managerEmp->department_id), function($q) use ($managerEmp) {
                $q->where('department_id', $managerEmp->department_id);
            })
            ->count();

        return view('attendance.manual-approvals', compact('tenant', 'requests', 'pendingCount'));
    }

    /**
     * Approve manual attendance request and update official attendance records
     */
    public function approve(Request $request, $id)
    {
        $manualReq = ManualAttendanceRequest::with('employee')->findOrFail($id);

        // Department manager permission check
        if (auth()->user()?->role === 'department_manager') {
            $managerEmp = Employee::where('tenant_id', $manualReq->tenant_id)
                ->where(function ($q) {
                    $q->where('id', auth()->user()->employee_id)
                      ->orWhere('email', auth()->user()->email);
                })->first();

            if ($managerEmp && $managerEmp->department_id && $manualReq->department_id) {
                abort_unless((int) $managerEmp->department_id === (int) $manualReq->department_id, 403, 'Unauthorized: You can only approve attendance for employees in your department.');
            }
        }

        $manualReq->status = 'approved';
        $manualReq->reviewed_by = auth()->id();
        $manualReq->reviewed_by_name = auth()->user()->name;
        $manualReq->reviewed_at = now();
        $manualReq->manager_remarks = $request->input('remarks', 'Approved by ' . auth()->user()->name);
        $manualReq->save();

        // Create or update attendance record
        $attendance = Attendance::firstOrNew([
            'tenant_id' => $manualReq->tenant_id,
            'employee_id' => $manualReq->employee_id,
            'attendance_date' => $manualReq->attendance_date->toDateString(),
        ]);

        if ($manualReq->punch_type === 'check_in') {
            $attendance->check_in = $manualReq->requested_check_in ?? date('H:i:s');
        } elseif ($manualReq->punch_type === 'check_out') {
            $attendance->check_out = $manualReq->requested_check_out ?? date('H:i:s');
        } elseif ($manualReq->punch_type === 'both') {
            $attendance->check_in = $manualReq->requested_check_in ?? '09:00:00';
            $attendance->check_out = $manualReq->requested_check_out ?? '17:00:00';
        }

        // Calculate worked minutes if both check-in and check-out present
        if ($attendance->check_in && $attendance->check_out) {
            $startTs = strtotime($attendance->check_in);
            $endTs = strtotime($attendance->check_out);
            $attendance->worked_minutes = max(0, round(($endTs - $startTs) / 60));
        }

        $attendance->status = 'present';
        $attendance->source = 'manual_approved';
        $attendance->location_note = 'Manual Attendance (Approved by ' . auth()->user()->name . ')';
        $attendance->save();

        return redirect()->back()->with('success', "Manual attendance for {$manualReq->employee->name} on " . $manualReq->attendance_date->format('d M, Y') . " approved successfully.");
    }

    /**
     * Reject manual attendance request
     */
    public function reject(Request $request, $id)
    {
        $manualReq = ManualAttendanceRequest::with('employee')->findOrFail($id);

        if (auth()->user()?->role === 'department_manager') {
            $managerEmp = Employee::where('tenant_id', $manualReq->tenant_id)
                ->where(function ($q) {
                    $q->where('id', auth()->user()->employee_id)
                      ->orWhere('email', auth()->user()->email);
                })->first();

            if ($managerEmp && $managerEmp->department_id && $manualReq->department_id) {
                abort_unless((int) $managerEmp->department_id === (int) $manualReq->department_id, 403, 'Unauthorized: You can only reject attendance for employees in your department.');
            }
        }

        $manualReq->status = 'rejected';
        $manualReq->reviewed_by = auth()->id();
        $manualReq->reviewed_by_name = auth()->user()->name;
        $manualReq->reviewed_at = now();
        $manualReq->manager_remarks = $request->input('remarks', 'Rejected by ' . auth()->user()->name);
        $manualReq->save();

        return redirect()->back()->with('info', "Manual attendance request for {$manualReq->employee->name} has been rejected.");
    }

    private function currentEmployee(int $tenantId): ?Employee
    {
        $query = Employee::with(['department', 'designation'])->where('tenant_id', $tenantId);

        if (auth()->user()?->isEmployee()) {
            return $query->whereKey(auth()->user()->employee_id)->first()
                ?? $query->where('email', auth()->user()->email)->first();
        }

        return $query->whereKey(auth()->user()?->employee_id)->first()
            ?? $query->where('email', auth()->user()?->email)->first()
            ?? $query->first();
    }
}
