<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Tenant;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $tenant = Tenant::find($tenantId) ?? Tenant::first();

        // Get or create today's record for employee #1 (or logged-in staff)
        $employee = Employee::where('tenant_id', $tenant->id)->first();
        
        $todayAttendance = null;
        if ($employee) {
            $todayAttendance = Attendance::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'employee_id' => $employee->id,
                    'attendance_date' => date('Y-m-d')
                ],
                [
                    'check_in' => '09:41:00',
                    'check_out' => null,
                    'worked_minutes' => 310,
                    'status' => 'present',
                    'location_note' => 'You are 10 Km away from your office.',
                    'source' => 'web_biometric',
                ]
            );
        }

        $attendances = Attendance::with('employee')
            ->where('tenant_id', $tenant->id)
            ->latest('attendance_date')
            ->paginate(10);

        return view('attendance.index', compact('tenant', 'employee', 'todayAttendance', 'attendances'));
    }

    public function checkIn(Request $request)
    {
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $empId = $request->input('employee_id', 1);

        $attendance = Attendance::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'employee_id' => $empId,
                'attendance_date' => date('Y-m-d')
            ],
            [
                'check_in' => date('H:i:s'),
                'status' => 'present',
                'location_note' => 'You are at Office HQ (Verified Geofence).',
                'source' => 'web_biometric'
            ]
        );

        return redirect()->back()->with('success', 'Checked In successfully at ' . date('h:i A') . '!');
    }

    public function checkOut(Request $request)
    {
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $empId = $request->input('employee_id', 1);

        $attendance = Attendance::where('tenant_id', $tenantId)
            ->where('employee_id', $empId)
            ->where('attendance_date', date('Y-m-d'))
            ->first();

        if ($attendance) {
            $attendance->check_out = date('H:i:s');
            $attendance->save();
        }

        return redirect()->back()->with('success', 'Checked Out successfully at ' . date('h:i A') . '. Have a great evening!');
    }

    public function toggleBreak(Request $request)
    {
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $empId = $request->input('employee_id', 1);

        $attendance = Attendance::where('tenant_id', $tenantId)
            ->where('employee_id', $empId)
            ->where('attendance_date', date('Y-m-d'))
            ->first();

        if ($attendance) {
            if (!$attendance->break_start) {
                $attendance->break_start = date('H:i:s');
                $attendance->save();
                return redirect()->back()->with('info', 'Break time started at ' . date('h:i A'));
            } else {
                $attendance->break_end = date('H:i:s');
                $attendance->save();
                return redirect()->back()->with('success', 'Break time ended. Welcome back to work!');
            }
        }

        return redirect()->back()->with('info', 'Break toggled.');
    }
}
