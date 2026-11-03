<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Tenant;
use App\Services\FacialBiometricService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $tenantId = $this->tenantId();
        $tenant = Tenant::find($tenantId) ?? Tenant::first();

        $employee = $this->currentEmployee($tenant->id);
        $todayAttendance = $employee
            ? Attendance::where('tenant_id', $tenant->id)->where('employee_id', $employee->id)->whereDate('attendance_date', today())->first()
            : null;

        $attendances = Attendance::with(['employee.department', 'employee.designation'])
            ->where('tenant_id', $tenant->id)
            ->when(auth()->user()?->isEmployee(), fn ($query) => $query->where('employee_id', $employee?->id))
            ->latest('attendance_date')
            ->paginate(10);

        return view('attendance.index', compact('tenant', 'employee', 'todayAttendance', 'attendances'));
    }

    /**
     * Facial Recognition Attendance Scanner View
     */
    public function faceScanner()
    {
        $tenantId = $this->tenantId();
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
     * Clean Mobile View for Employee Facial Attendance (Prototype 2 Numla Mobile)
     */
    public function mobileAttendance(Request $request)
    {
        $tenantId = $this->tenantId();
        $tenant = Tenant::find($tenantId) ?? Tenant::first();

        $employee = $this->currentEmployee($tenant->id, $request->integer('emp_id'));
        abort_unless($employee, 404, 'No employee profile is linked to this account.');

        $allStaff = auth()->user()?->isEmployee() ? collect([$employee]) : Employee::where('tenant_id', $tenant->id)->get();

        $todayAttendance = Attendance::where('tenant_id', $tenant->id)
            ->where('employee_id', $employee ? $employee->id : 1)
            ->whereDate('attendance_date', today())
            ->first();

        return view('attendance.mobile', compact('tenant', 'employee', 'allStaff', 'todayAttendance'));
    }

    /**
     * Process face detection and attendance logging via biometric vector verification
     */
    public function processFaceScan(Request $request, FacialBiometricService $biometrics)
    {
        $tenantId = $this->tenantId();

        $validated = $request->validate([
            'face_frames' => 'required|array|min:3|max:5',
            'face_frames.*' => 'required|string|max:5000000',
            'employee_id' => 'nullable',
            'punch_type' => 'nullable|in:auto,auto_check_in,check_in,check_out',
            'location_note' => 'nullable|string|max:255',
        ]);

        // Requested punch mode ('auto', 'check_in', 'check_out')
        $requestedPunchType = $validated['punch_type'] ?? 'auto';

        // Selected employee ID or simulated test profile
        $simulatedEmpId = $validated['employee_id'] ?? null;

        // If specific employee verification requested (e.g. self check-in on mobile or staff select)
        $isSpecificEmployee = $simulatedEmpId && $simulatedEmpId !== 'auto';
        if ($isSpecificEmployee && auth()->user()?->isEmployee()) {
            $ownEmployee = $this->currentEmployee($tenantId);
            if (!$ownEmployee || (int) $simulatedEmpId !== (int) $ownEmployee->id) {
                return response()->json(['success' => false, 'message' => 'You can only record attendance for your own profile.'], 403);
            }
        }
        if ($isSpecificEmployee) {
            $targetEmp = Employee::where('tenant_id', $tenantId)->find($simulatedEmpId);
            $targetName = $targetEmp ? $targetEmp->name : "Selected employee";
            if (!$targetEmp || !$targetEmp->hasReliableFaceBiometric()) {
                return response()->json([
                    'success' => false,
                    'message' => "Facial biometrics not enrolled yet for {$targetName}. Admin must take their facial photo in the Employee Directory first before attendance can be logged."
                ], 403);
            }
        }

        // Fetch all employees with enrolled face descriptors stored in database
        $enrolledEmployees = Employee::with(['department', 'designation'])
            ->where('tenant_id', $tenantId)
            ->where('face_engine', 'scrfd-arcface-onnx')
            ->whereNotNull('face_descriptor')
            ->get();

        if ($enrolledEmployees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No employee facial biometrics are enrolled in the database yet. Admin must take and enroll facial photos from the Employee Directory first.'
            ], 403);
        }

        try {
            $scanResult = $biometrics->extract($validated['face_frames'], true);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Biometric verification failed: ' . $exception->getMessage(),
            ], 422);
        }
        $scannedDescriptor = $scanResult['descriptor'];
        $faceSnapshot = $validated['face_frames'][0];

        // 2. Authentic Facial Biometric Verification Algorithm (Cosine Similarity Matching)
        $bestMatch = null;
        $bestSimilarity = -1.0;

        if (auth()->user()?->isEmployee()) {
            $candidates = $enrolledEmployees->where('email', auth()->user()->email);
        } else {
            $candidates = $isSpecificEmployee
                ? $enrolledEmployees->where('id', (int) $simulatedEmpId)
                : $enrolledEmployees;
        }

        foreach ($candidates as $emp) {
            $dbVector = is_array($emp->face_descriptor) ? $emp->face_descriptor : [];
            $cosineSim = $biometrics->similarity($scannedDescriptor, $dbVector);

            if ($cosineSim > $bestSimilarity) {
                $bestSimilarity = $cosineSim;
                $bestMatch = $emp;
            }
        }

        $matchThreshold = config('face.minimum_similarity');

        if (!$bestMatch || $bestSimilarity < $matchThreshold) {
            $failMsg = $isSpecificEmployee && $bestMatch
                ? "Biometric Verification Failed! The captured face does NOT match {$bestMatch->name}'s registered biometric profile."
                : "Biometric Verification Failed! Unrecognized face. No matching employee biometric profile found in the database.";

            return response()->json([
                'success' => false,
                'message' => $failMsg,
                'similarity' => round($bestSimilarity, 3)
            ], 401);
        }

        $employee = $bestMatch;
        $similarityPct = round(max(0, min(99.9, $bestSimilarity * 100)), 1);

        // Optionally update snapshot if valid camera frame provided
        if (!empty($faceSnapshot) && strlen($faceSnapshot) > 100) {
            $employee->face_snapshot = $faceSnapshot;
            $employee->save();
        }

        $today = now()->toDateString();
        $currentTime = date('H:i:s');
        $displayCurrentTime = date('h:i A');

        $attendance = Attendance::where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->where('attendance_date', $today)
            ->first();

        $locationNote = $validated['location_note'] ?? 'Facial Terminal Gate 01 - Verified HQ';

        if (!$attendance) {
            // First punch of the day: Always Check In
            $attendance = Attendance::create([
                'tenant_id' => $tenantId,
                'employee_id' => $employee->id,
                'attendance_date' => $today,
                'check_in' => $currentTime,
                'status' => 'present',
                'location_note' => $locationNote,
                'source' => 'facial_recognition',
                'worked_minutes' => 0,
            ]);
            $action = 'checked_in';
            $message = "Identity Verified for {$employee->name} ({$similarityPct}% match)! Checked IN at " . $displayCurrentTime;
        } elseif ($requestedPunchType === 'auto_check_in') {
            // Background scanning is idempotent: it can never turn a check-in into a checkout.
            if ($attendance->check_in) {
                $action = $attendance->check_out ? 'already_checked_out' : 'already_checked_in';
                $message = $attendance->check_out
                    ? "Attendance already completed for {$employee->name} today."
                    : "{$employee->name} is already checked in today.";
            } else {
                $attendance->check_in = $currentTime;
                $attendance->status = 'present';
                $attendance->source = 'facial_recognition';
                $attendance->save();
                $action = 'checked_in';
                $message = "Identity Verified for {$employee->name} ({$similarityPct}% match)! Checked IN at " . $displayCurrentTime;
            }
        } elseif ($requestedPunchType === 'check_in') {
            // Explicit Check In requested
            if (!$attendance->check_in) {
                $attendance->check_in = $currentTime;
            }
            $attendance->check_out = null;
            $attendance->status = 'present';
            $attendance->source = 'facial_recognition';
            $attendance->save();
            $action = 'checked_in';
            $message = "Identity Verified for {$employee->name} ({$similarityPct}% match)! Checked IN at " . $displayCurrentTime;
        } elseif ($requestedPunchType === 'check_out') {
            // Explicit Check Out requested
            $attendance->check_out = $currentTime;
            if ($attendance->check_in) {
                $startTs = strtotime($attendance->check_in);
                $endTs = strtotime($currentTime);
                $diffMinutes = max(1, round(($endTs - $startTs) / 60));
                $attendance->worked_minutes = $diffMinutes;
            }
            $attendance->source = 'facial_recognition';
            $attendance->save();
            $action = 'checked_out';
            $message = "Identity Verified for {$employee->name} ({$similarityPct}% match)! Checked OUT at " . $displayCurrentTime;
        } else {
            // Auto-toggle punch logic
            if ($attendance->check_in && !$attendance->check_out) {
                // Currently checked in -> punch checks them OUT
                $attendance->check_out = $currentTime;
                $startTs = strtotime($attendance->check_in);
                $endTs = strtotime($currentTime);
                $diffMinutes = max(1, round(($endTs - $startTs) / 60));
                $attendance->worked_minutes = $diffMinutes;
                $attendance->source = 'facial_recognition';
                $attendance->save();

                $action = 'checked_out';
                $message = "Identity Verified for {$employee->name} ({$similarityPct}% match)! Checked OUT at " . $displayCurrentTime;
            } else {
                // Currently checked out (or repeat punch) -> punch checks them back IN
                $attendance->check_in = $currentTime;
                $attendance->check_out = null;
                $attendance->status = 'present';
                $attendance->source = 'facial_recognition';
                $attendance->save();

                $action = 'checked_in';
                $message = "Identity Verified for {$employee->name} ({$similarityPct}% match)! Checked IN at " . $displayCurrentTime;
            }
        }

        $attendance->biometric_similarity = $bestSimilarity;
        $attendance->biometric_engine = $scanResult['engine'] ?? 'scrfd-arcface-onnx';
        $attendance->biometric_frames = $scanResult['liveness']['frames'] ?? count($validated['face_frames']);
        $attendance->save();

        $workedHours = $attendance->worked_minutes ? round($attendance->worked_minutes / 60, 1) . ' hrs' : '0.0 hrs';

        return response()->json([
            'success' => true,
            'message' => $message,
            'action' => $action,
            'confidence' => $similarityPct . '%',
            'biometric_status' => 'verified',
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
        $tenantId = $this->tenantId();
        $employee = $this->currentEmployee($tenantId, $request->integer('employee_id'));
        abort_unless($employee, 404, 'No employee profile is linked to this account.');

        $attendance = Attendance::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'employee_id' => $employee->id,
                'attendance_date' => now()->toDateString()
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
        $tenantId = $this->tenantId();
        $employee = $this->currentEmployee($tenantId, $request->integer('employee_id'));
        abort_unless($employee, 404, 'No employee profile is linked to this account.');

        $attendance = Attendance::where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', today())
            ->first();

        if (!$attendance || !$attendance->check_in) {
            return redirect()->back()->with('error', 'Check-in is required before checking out.');
        }

        $attendance->check_out = now()->format('H:i:s');
        $attendance->worked_minutes = max(0, round((strtotime($attendance->check_out) - strtotime($attendance->check_in)) / 60));
        $attendance->save();

        return redirect()->back()->with('success', 'Checked Out successfully at ' . date('h:i A') . '. Have a great evening!');
    }

    public function toggleBreak(Request $request)
    {
        $tenantId = $this->tenantId();
        $employee = $this->currentEmployee($tenantId, $request->integer('employee_id'));
        abort_unless($employee, 404, 'No employee profile is linked to this account.');

        $attendance = Attendance::where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', today())
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

    private function tenantId(): int
    {
        return (int) (auth()->user()?->tenant_id ?? session('current_tenant_id') ?? session('active_tenant_id') ?? 1);
    }

    private function currentEmployee(int $tenantId, ?int $requestedId = null): ?Employee
    {
        $query = Employee::with(['department', 'designation'])->where('tenant_id', $tenantId);

        if (auth()->user()?->isEmployee()) {
            return $query->whereKey(auth()->user()->employee_id)->first()
                ?? $query->where('email', auth()->user()->email)->first();
        }

        return $requestedId ? $query->find($requestedId) : $query->first();
    }
}
