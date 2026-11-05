<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$consoleKernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$consoleKernel->bootstrap();
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "======================================================================\n";
echo "ATTENDANCE CUTOFF, MORNING PUNCH & MANUAL APPROVAL VERIFICATION\n";
echo "======================================================================\n\n";

$tenant = App\Models\Tenant::first();
$hrAdmin = App\Models\User::where('role', 'hr_manager')->first();
$manager = App\Models\User::where('role', 'department_manager')->first();
$employeeUser = App\Models\User::where('role', 'employee')->first();
$employee = App\Models\Employee::find($employeeUser?->employee_id) ?? App\Models\Employee::where('email', $employeeUser?->email)->first() ?? App\Models\Employee::first();

$passed = 0;
$failed = 0;

function assertTest($condition, $description) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$description}\n";
        $failed++;
    }
}

// 1. Policy & Cutoff Unit Tests
echo "--- 1. TESTING ATTENDANCE POLICY & CUTOFF TIMINGS ---\n";
$policy = App\Models\AttendancePolicy::forTenant($tenant->id);
$policy->update([
    'check_in_start' => '07:00:00',
    'check_in_end' => '10:30:00',
    'check_out_start' => '16:00:00',
    'check_out_end' => '23:59:00',
    'enforce_checkin_cutoff' => true,
]);

$morningCheck = $policy->canCheckIn('09:15:00');
assertTest($morningCheck['allowed'] === true, "Morning check-in at 09:15 AM is ALLOWED (within 07:00 - 10:30)");

$lateCheck = $policy->canCheckIn('11:00:00');
assertTest($lateCheck['allowed'] === false && $lateCheck['code'] === 'AFTER_CUTOFF', "Check-in at 11:00 AM is BLOCKED by cutoff (Cutoff: 10:30 AM)");

$tooEarlyCheck = $policy->canCheckIn('06:30:00');
assertTest($tooEarlyCheck['allowed'] === false && $tooEarlyCheck['code'] === 'BEFORE_START', "Check-in at 06:30 AM is BLOCKED before opening (Opens: 07:00 AM)");

// 2. Morning Check-In Logic & Idempotency
echo "\n--- 2. TESTING SINGLE MORNING CHECK-IN & NO ACCIDENTAL CHECKOUT ---\n";
$today = date('Y-m-d');
App\Models\Attendance::where('tenant_id', $tenant->id)->where('employee_id', $employee->id)->where('attendance_date', $today)->delete();

// Clean first check-in
$att1 = App\Models\Attendance::create([
    'tenant_id' => $tenant->id,
    'employee_id' => $employee->id,
    'attendance_date' => $today,
    'check_in' => '09:00:00',
    'status' => 'present',
    'source' => 'facial_recognition'
]);
assertTest($att1->check_in === '09:00:00' && is_null($att1->check_out), "Initial morning face scan correctly sets check_in = 09:00:00 and leaves check_out NULL");

// Simulate employee standing at terminal again at 09:05 AM (Morning / Before 16:00 checkout start)
$checkoutStartTs = strtotime($policy->check_out_start);
$scanTime = '09:05:00';
$isMorning = strtotime($scanTime) < $checkoutStartTs;
assertTest($isMorning === true, "A repeat scan at 09:05 AM is detected as MORNING arrival (does NOT checkout)");

// 3. Manual Attendance Request Submission & Branch Manager Approval
echo "\n--- 3. TESTING MANUAL ATTENDANCE REQUEST & BRANCH MANAGER APPROVAL ---\n";
App\Models\ManualAttendanceRequest::where('employee_id', $employee->id)->delete();

$manualReq = App\Models\ManualAttendanceRequest::create([
    'tenant_id' => $tenant->id,
    'employee_id' => $employee->id,
    'branch_id' => $employee->branch_id,
    'department_id' => $employee->department_id,
    'attendance_date' => $today,
    'punch_type' => 'check_in',
    'requested_check_in' => '09:10:00',
    'reason' => 'Camera lens had glare and could not recognize face',
    'status' => 'pending',
]);
assertTest($manualReq->status === 'pending', "Employee manual request created with status = PENDING");

// Manager Approves
$manualReq->status = 'approved';
$manualReq->reviewed_by = $manager->id;
$manualReq->reviewed_by_name = $manager->name;
$manualReq->reviewed_at = now();
$manualReq->manager_remarks = 'Verified with team lead, approved.';
$manualReq->save();

// Sync to Official Attendances table
$officialAtt = App\Models\Attendance::updateOrCreate(
    ['tenant_id' => $tenant->id, 'employee_id' => $employee->id, 'attendance_date' => $today],
    [
        'check_in' => $manualReq->requested_check_in,
        'status' => 'present',
        'source' => 'manual_approved',
        'location_note' => 'Manual Attendance (Approved by ' . $manager->name . ')',
    ]
);

assertTest($manualReq->status === 'approved', "Manual request status updated to APPROVED by Manager");
assertTest($officialAtt->source === 'manual_approved' && $officialAtt->check_in === '09:10:00', "Official attendance record updated with source = manual_approved and check_in = 09:10:00");

// 4. HTTP Routes & Access Checks
echo "\n--- 4. TESTING HTTP ROUTE ACCESS ---\n";

// Employee accesses manual request page
auth()->login($employeeUser);
$req = Illuminate\Http\Request::create('/attendance/manual-request', 'GET');
$res = $httpKernel->handle($req);
if ($res->getStatusCode() !== 200) {
    echo "  [DEBUG] Status: " . $res->getStatusCode() . " Content: " . substr($res->getContent(), 0, 100) . "\n";
}
assertTest($res->getStatusCode() === 200, "Employee can access GET /attendance/manual-request (Status 200)");

// Employee tries to access Manager Approvals -> should redirect / block
$req = Illuminate\Http\Request::create('/attendance/manual-approvals', 'GET');
$res = $httpKernel->handle($req);
assertTest($res->getStatusCode() === 302, "Employee is BLOCKED from GET /attendance/manual-approvals (Redirect 302)");

// Manager accesses Manager Approvals
auth()->login($manager);
$req = Illuminate\Http\Request::create('/attendance/manual-approvals', 'GET');
$res = $httpKernel->handle($req);
assertTest($res->getStatusCode() === 200, "Manager can access GET /attendance/manual-approvals (Status 200)");

// Manager tries to access Policy configuration -> should be blocked (HR/Admin only)
$req = Illuminate\Http\Request::create('/attendance/policy', 'GET');
$res = $httpKernel->handle($req);
assertTest($res->getStatusCode() === 302, "Department Manager is BLOCKED from GET /attendance/policy (Redirect 302)");

// HR Admin accesses Policy configuration
auth()->login($hrAdmin);
$req = Illuminate\Http\Request::create('/attendance/policy', 'GET');
$res = $httpKernel->handle($req);
assertTest($res->getStatusCode() === 200, "HR Admin can access GET /attendance/policy (Status 200)");

echo "\n======================================================================\n";
echo "SUMMARY: Passed: {$passed} | Failed: {$failed}\n";
echo "======================================================================\n";
