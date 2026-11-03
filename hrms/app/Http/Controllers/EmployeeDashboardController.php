<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\ShiftSchedule;
use App\Services\FacialBiometricService;
use App\Models\PayrollItem;
use App\Models\Tenant;
use Illuminate\Http\Request;

class EmployeeDashboardController extends Controller
{
    /**
     * Display Employee Self-Service Dashboard / Portal
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()?->tenant_id ?? session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $tenant = Tenant::find($tenantId) ?? Tenant::first();

        $employeeQuery = Employee::with(['department', 'designation', 'branch'])
            ->where('tenant_id', $tenant->id);

        if (auth()->user()?->isEmployee()) {
            $employee = $employeeQuery->whereKey(auth()->user()->employee_id)->first()
                ?? $employeeQuery->where('email', auth()->user()->email)->first();
        } else {
            $employeeId = $request->integer('emp_id') ?: session('portal_employee_id');
            $employee = $employeeId
                ? (clone $employeeQuery)->find($employeeId)
                : $employeeQuery->first();
        }

        abort_unless($employee, 404, 'No employee profile is linked to this account.');

        if ($employee) {
            session(['portal_employee_id' => $employee->id]);
        }

        $allStaff = auth()->user()?->isEmployee()
            ? collect([$employee])
            : Employee::where('tenant_id', $tenant->id)->get();

        // 1. Today's Attendance
        $todayAttendance = Attendance::where('tenant_id', $tenant->id)
            ->where('employee_id', $employee->id)
            ->where('attendance_date', date('Y-m-d'))
            ->first();

        // 2. Leave Balances
        $leaveTypes = LeaveType::where('tenant_id', $tenant->id)->get();
        $approvedLeaves = LeaveApplication::where('tenant_id', $tenant->id)
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->get();

        $leaveStats = [];
        foreach ($leaveTypes as $type) {
            $taken = $approvedLeaves->where('leave_type_id', $type->id)->sum('total_days');
            $allowed = $type->days_allowed ?? 14;
            $leaveStats[] = [
                'type' => $type,
                'allowed' => $allowed,
                'taken' => $taken,
                'remaining' => max(0, $allowed - $taken),
            ];
        }

        // 3. Upcoming Shift Schedules this week (Agendrix match)
        $shifts = ShiftSchedule::where('tenant_id', $tenant->id)
            ->where('employee_id', $employee->id)
            ->get();

        // 4. Recent Payslips
        $payslips = PayrollItem::with('run')
            ->where('tenant_id', $tenant->id)
            ->where('employee_id', $employee->id)
            ->latest()
            ->take(5)
            ->get();

        // 5. Recent 7-Day Attendance Logs
        $recentAttendances = Attendance::where('tenant_id', $tenant->id)
            ->where('employee_id', $employee->id)
            ->latest('attendance_date')
            ->take(7)
            ->get();

        return view('employee.dashboard', compact(
            'tenant',
            'employee',
            'allStaff',
            'todayAttendance',
            'leaveStats',
            'shifts',
            'payslips',
            'recentAttendances'
        ));
    }

    /**
     * Store or update facial biometric vector in the database
     */
    public function enrollFace(Request $request, FacialBiometricService $biometrics)
    {
        $tenantId = auth()->user()?->tenant_id ?? session('current_tenant_id') ?? session('active_tenant_id') ?? 1;

        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'face_frames' => 'required|array|min:3|max:5',
            'face_frames.*' => 'required|string|max:5000000',
        ]);

        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($validated['employee_id']);

        if (auth()->user()?->isEmployee() && (int) $employee->id !== (int) auth()->user()->employee_id && $employee->email !== auth()->user()->email) {
            abort(403, 'You can only enroll your own facial profile.');
        }

        try {
            $result = $biometrics->extract($validated['face_frames']);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Enrollment failed: ' . $exception->getMessage(),
            ], 422);
        }

        $employee->face_descriptor = $result['descriptor'];
        $employee->face_snapshot = $validated['face_frames'][0];
        $employee->face_enrolled_at = now();
        $employee->face_biometric_id = 'BIO-FACE-' . ($employee->employee_code ?? 'EMP-' . $employee->id);
        $employee->face_engine = $result['engine'];
        $employee->face_quality = $result['quality'] ?? null;
        $employee->face_consistency = $result['consistency'] ?? null;
        $employee->face_enrollment_frames = $result['liveness']['frames'] ?? count($validated['face_frames']);
        $employee->save();

        return response()->json([
            'success' => true,
            'message' => 'Facial biometrics successfully enrolled and stored in database!',
            'enrolled_at' => $employee->face_enrolled_at->format('d M Y, h:i A'),
            'biometric_id' => $employee->face_biometric_id,
            'engine' => $employee->face_engine,
            'quality' => $employee->face_quality,
            'frames' => $employee->face_enrollment_frames,
        ]);
    }
}
