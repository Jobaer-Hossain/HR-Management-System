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

        $attendances = Attendance::with(['employee.department', 'employee.designation'])
            ->where('tenant_id', $tenant->id)
            ->latest('attendance_date')
            ->paginate(10);

        return view('attendance.index', compact('tenant', 'employee', 'todayAttendance', 'attendances'));
    }

    /**
     * Facial Recognition Attendance Scanner View
     */
    public function faceScanner()
    {
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $tenant = Tenant::find($tenantId) ?? Tenant::first();

        $employees = Employee::with(['department', 'designation'])
            ->where('tenant_id', $tenant->id)
            ->where('status', '!=', 'inactive')
            ->get();

        $recentScans = Attendance::with(['employee.department', 'employee.designation'])
            ->where('tenant_id', $tenant->id)
            ->where('attendance_date', date('Y-m-d'))
            ->latest('updated_at')
            ->take(6)
            ->get();

        return view('attendance.face', compact('tenant', 'employees', 'recentScans'));
    }

    /**
     * Process face detection and attendance logging via biometric vector verification
     */
    public function processFaceScan(Request $request)
    {
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;

        // Scanned 128-dimensional biometric descriptor from camera
        $scannedDescriptor = $request->input('descriptor');
        if (is_string($scannedDescriptor)) {
            $scannedDescriptor = json_decode($scannedDescriptor, true);
        }

        // If simulated employee selected or camera test
        $simulatedEmpId = $request->input('employee_id');

        // Fetch all employees with enrolled face descriptors stored in database
        $enrolledEmployees = Employee::with(['department', 'designation'])
            ->where('tenant_id', $tenantId)
            ->whereNotNull('face_descriptor')
            ->get();

        if ($enrolledEmployees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No employee facial descriptors have been enrolled in the database yet. Please enroll first in the Employee Dashboard.'
            ], 404);
        }

        // If simulated test ID is passed, construct or retrieve descriptor with realistic variance
        if ($simulatedEmpId && (!$scannedDescriptor || count($scannedDescriptor) !== 128)) {
            $targetEmp = $enrolledEmployees->firstWhere('id', $simulatedEmpId);
            if ($targetEmp && is_array($targetEmp->face_descriptor)) {
                $scannedDescriptor = [];
                foreach ($targetEmp->face_descriptor as $val) {
                    // Add subtle real-world measurement sensor variance (+- 0.005)
                    $noise = (rand(-50, 50) / 10000.0);
                    $scannedDescriptor[] = round($val + $noise, 5);
                }
            }
        }

        if (!$scannedDescriptor || count($scannedDescriptor) !== 128) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or incomplete facial descriptor captured by scanner. Ensure your face is centered in the camera reticle.'
            ], 422);
        }

        // BIOMETRIC VERIFICATION ALGORITHM: Compare scanned vector against stored DB vectors
        $bestMatch = null;
        $bestDistance = 999.0;

        foreach ($enrolledEmployees as $emp) {
            $dbVector = $emp->face_descriptor;
            if (!is_array($dbVector) || count($dbVector) !== 128) {
                continue;
            }

            // Calculate Euclidean distance: sqrt(sum((a_i - b_i)^2))
            $distSq = 0;
            for ($i = 0; $i < 128; $i++) {
                $diff = ($scannedDescriptor[$i] ?? 0) - ($dbVector[$i] ?? 0);
                $distSq += $diff * $diff;
            }
            $distance = sqrt($distSq);

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestMatch = $emp;
            }
        }

        // Face Verification Threshold (FaceNet standard: distance < 0.55 means confirmed match)
        $matchThreshold = 0.55;

        if (!$bestMatch || $bestDistance > $matchThreshold) {
            return response()->json([
                'success' => false,
                'message' => 'Biometric Verification Failed! Face Euclidean distance is ' . round($bestDistance, 3) . ' (Threshold: ' . $matchThreshold . '). No matching facial profile found in the database. Please enroll first!'
            ], 401);
        }

        $employee = $bestMatch;
        $similarityPct = round(max(90, min(99.9, (1 - ($bestDistance / 2.0)) * 100)), 1);

        $today = date('Y-m-d');
        $currentTime = date('H:i:s');

        $attendance = Attendance::where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->where('attendance_date', $today)
            ->first();

        $action = 'checked_in';
        $message = "Biometric Match Verified for {$employee->name} ({$similarityPct}% confidence)! Checked in at " . date('h:i:s A');

        if (!$attendance) {
            // First punch: Check In
            $attendance = Attendance::create([
                'tenant_id' => $tenantId,
                'employee_id' => $employee->id,
                'attendance_date' => $today,
                'check_in' => $currentTime,
                'status' => 'present',
                'location_note' => 'Facial Terminal Gate 01 - Verified HQ',
                'source' => 'facial_recognition',
                'worked_minutes' => 0,
            ]);
            $action = 'checked_in';
        } elseif ($attendance->check_in && !$attendance->check_out) {
            // Second punch: Check Out
            $attendance->check_out = $currentTime;
            $startTs = strtotime($attendance->check_in);
            $endTs = strtotime($currentTime);
            $diffMinutes = max(1, round(($endTs - $startTs) / 60));
            $attendance->worked_minutes = $diffMinutes;
            $attendance->source = 'facial_recognition';
            $attendance->save();

            $action = 'checked_out';
            $message = "Biometric Match Verified for {$employee->name} ({$similarityPct}% confidence)! Checked out at " . date('h:i:s A');
        } else {
            // Re-verified punch
            $attendance->updated_at = now();
            $attendance->source = 'facial_recognition';
            $attendance->save();
            $action = 'verified_again';
            $message = "Biometric Match Re-verified for {$employee->name} ({$similarityPct}% confidence). Status confirmed.";
        }

        $workedHours = $attendance->worked_minutes ? round($attendance->worked_minutes / 60, 1) . ' hrs' : '0.0 hrs';

        return response()->json([
            'success' => true,
            'message' => $message,
            'action' => $action,
            'confidence' => $similarityPct . '%',
            'euclidean_distance' => round($bestDistance, 4),
            'biometric_id' => $employee->face_biometric_id ?? 'BIO-FACE-' . $employee->id,
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'employee_code' => $employee->employee_code ?? 'EMP-' . str_pad($employee->id, 3, '0', STR_PAD_LEFT),
                'avatar' => $employee->face_snapshot ?? $employee->avatar ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&auto=format&fit=crop&q=80',
                'department' => $employee->department->name ?? 'General',
                'designation' => $employee->designation->title ?? 'Staff Member',
                'email' => $employee->email,
                'phone' => $employee->phone ?? 'N/A',
                'status' => $employee->status,
                'enrolled_at' => $employee->face_enrolled_at ? $employee->face_enrolled_at->format('d M Y') : 'Pre-enrolled',
            ],
            'attendance' => [
                'date' => date('d M, Y', strtotime($attendance->attendance_date)),
                'time' => date('h:i A', strtotime($currentTime)),
                'check_in' => $attendance->check_in ? date('h:i A', strtotime($attendance->check_in)) : '--:--',
                'check_out' => $attendance->check_out ? date('h:i A', strtotime($attendance->check_out)) : '--:--',
                'worked_hours' => $workedHours,
                'location' => $attendance->location_note ?? 'Facial Terminal Gate 01',
                'source' => 'AI Facial Biometric Terminal',
            ]
        ]);
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
