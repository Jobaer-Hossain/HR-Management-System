<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\ShiftSchedule;
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
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $tenant = Tenant::find($tenantId) ?? Tenant::first();

        // Active employee context (allow switching active employee profile to preview portal for any staff)
        $employeeId = $request->get('emp_id', session('portal_employee_id', 1));
        $employee = Employee::with(['department', 'designation', 'branch'])
            ->where('tenant_id', $tenant->id)
            ->find($employeeId);

        if (!$employee) {
            $employee = Employee::with(['department', 'designation', 'branch'])
                ->where('tenant_id', $tenant->id)
                ->first();
        }

        if ($employee) {
            session(['portal_employee_id' => $employee->id]);
        }

        $allStaff = Employee::where('tenant_id', $tenant->id)->get();

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
    public function enrollFace(Request $request)
    {
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'face_descriptor' => 'nullable', // Array of floats or extracted via snapshot
            'face_snapshot' => 'nullable|string', // Base64 snapshot or image URL
        ]);

        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($validated['employee_id']);

        $descriptor = $request->input('face_descriptor');
        if (is_string($descriptor)) {
            $descriptor = json_decode($descriptor, true);
        }

        // If descriptor not provided, extract authentically from face_snapshot
        if ((!$descriptor || (count($descriptor) !== 512 && count($descriptor) !== 128)) && !empty($validated['face_snapshot'])) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'enroll_');
            file_put_contents($tmpFile, $validated['face_snapshot']);
            $output = shell_exec("python " . escapeshellarg(base_path('app/Services/face_extractor.py')) . " " . escapeshellarg($tmpFile));
            @unlink($tmpFile);

            if ($output) {
                $res = json_decode($output, true);
                if (!empty($res['success']) && !empty($res['descriptor'])) {
                    $descriptor = $res['descriptor'];
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Enrollment Failed: ' . ($res['error'] ?? 'No clear human face detected in camera capture.')
                    ], 422);
                }
            }
        }

        if (!$descriptor) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to extract facial biometric features. Please look directly into the camera.'
            ], 422);
        }

        $employee->face_descriptor = $descriptor;
        if (!empty($validated['face_snapshot'])) {
            $employee->face_snapshot = $validated['face_snapshot'];
        }
        $employee->face_enrolled_at = now();
        $employee->face_biometric_id = 'BIO-FACE-' . ($employee->employee_code ?? 'EMP-' . $employee->id);
        $employee->save();

        return response()->json([
            'success' => true,
            'message' => 'Facial biometrics successfully enrolled and stored in database!',
            'enrolled_at' => $employee->face_enrolled_at->format('d M Y, h:i A'),
            'biometric_id' => $employee->face_biometric_id,
        ]);
    }
}
