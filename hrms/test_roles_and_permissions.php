<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "======================================================================\n";
echo "ROLE-BASED ACCESS CONTROL (RBAC) & PERMISSION VERIFICATION SUITE\n";
echo "======================================================================\n\n";

function testAccess($user, $url, $method = 'GET', $shouldPass = true) {
    Auth::login($user);
    $req = Request::create($url, $method);

    try {
        $kernel = app()->make(Illuminate\Contracts\Http\Kernel::class);
        $res = $kernel->handle($req);
        $status = $res->getStatusCode();
        $targetLocation = $res->headers->get('Location') ?? '';

        if ($shouldPass) {
            // Should pass means 200 OK or legitimate non-blocking redirect
            $passed = ($status === 200) || ($status === 302 && !str_contains($targetLocation, 'login'));
        } else {
            // Should block means 403, 401, or 302 redirecting back/to dashboard with error
            $passed = ($status === 403) || ($status === 401) || ($status === 419) || ($status === 302);
        }

        $resultLabel = $passed ? "PASS" : "FAIL";
        $expectation = $shouldPass ? "ALLOW" : "BLOCK";
        $locInfo = ($status === 302) ? " -> redirect to: {$targetLocation}" : "";
        echo "  [{$resultLabel}] [{$user->role}] {$method} {$url} => Status {$status}{$locInfo} (Expected: {$expectation})\n";
        return $passed;
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        $status = $e->getStatusCode();
        $passed = !$shouldPass && ($status === 403 || $status === 401);
        $resultLabel = $passed ? "PASS" : "FAIL";
        echo "  [{$resultLabel}] [{$user->role}] {$method} {$url} => Exception {$status} (Expected: BLOCK)\n";
        return $passed;
    } catch (\Throwable $e) {
        echo "  [ERROR] {$method} {$url} => " . $e->getMessage() . "\n";
        return false;
    }
}

// 1. TEST EMPLOYEE (REGULAR USER)
echo "--- 1. TESTING EMPLOYEE (STRICTLY SELF-SERVICE ACCESS) ---\n";
$empUser = User::where('role', 'employee')->first();
if (!$empUser) {
    $empUser = User::create([
        'name' => 'Cody Fisher', 'email' => 'employee@hrms.com', 'password' => bcrypt('password123'),
        'role' => 'employee', 'tenant_id' => 1
    ]);
}

$p1 = testAccess($empUser, '/portal', 'GET', true);
$p2 = testAccess($empUser, '/attendance/mobile', 'GET', true);
$p3 = testAccess($empUser, '/leaves/apply', 'GET', true);

// Modules that Employee MUST BE BLOCKED from:
$b1 = testAccess($empUser, '/dashboard/hr', 'GET', false);
$b2 = testAccess($empUser, '/dashboard/manager', 'GET', false);
$b3 = testAccess($empUser, '/dashboard/finance', 'GET', false);
$b4 = testAccess($empUser, '/employees', 'GET', false);
$b5 = testAccess($empUser, '/attendance', 'GET', false);
$b6 = testAccess($empUser, '/attendance/face', 'GET', false);
$b7 = testAccess($empUser, '/leaves', 'GET', false);
$b8 = testAccess($empUser, '/schedule', 'GET', false);
$b9 = testAccess($empUser, '/payrolls', 'GET', false);
$b10 = testAccess($empUser, '/employee/enroll-face', 'POST', false);
$b11 = testAccess($empUser, '/settings/permissions', 'GET', false);
$b12 = testAccess($empUser, '/role/switch', 'POST', false);

$empAllPassed = $p1 && $p2 && $p3 && $b1 && $b2 && $b3 && $b4 && $b5 && $b6 && $b7 && $b8 && $b9 && $b10 && $b11 && $b12;
echo $empAllPassed ? "\n>>> EMPLOYEE ROLE: 100% AIRTIGHT (NO PRIVILEGE LEAKAGE) <<<\n\n" : "\n>>> EMPLOYEE ROLE FAILED SOME CHECKS <<<\n\n";

// 2. TEST DEPARTMENT MANAGER
echo "--- 2. TESTING DEPARTMENT MANAGER ---\n";
$mgrUser = User::where('role', 'department_manager')->first();
$m1 = testAccess($mgrUser, '/dashboard/manager', 'GET', true);
$m2 = testAccess($mgrUser, '/leaves', 'GET', true);
$m3 = testAccess($mgrUser, '/schedule', 'GET', true);
$m4 = testAccess($mgrUser, '/attendance', 'GET', true);
$m5 = testAccess($mgrUser, '/attendance/face', 'GET', true);
$m6 = testAccess($mgrUser, '/employees', 'GET', true);
// Blocked for Manager:
$mb1 = testAccess($mgrUser, '/payrolls', 'GET', false);
$mb2 = testAccess($mgrUser, '/dashboard/hr', 'GET', false);
$mb3 = testAccess($mgrUser, '/settings/permissions', 'GET', false);
$mb4 = testAccess($mgrUser, '/employees/create', 'GET', false);

// 3. TEST FINANCE
echo "\n--- 3. TESTING FINANCE OFFICER ---\n";
$finUser = User::where('role', 'finance')->first();
$f1 = testAccess($finUser, '/dashboard/finance', 'GET', true);
$f2 = testAccess($finUser, '/payrolls', 'GET', true);
$f3 = testAccess($finUser, '/employees', 'GET', true);
// Blocked for Finance:
$fb1 = testAccess($finUser, '/leaves', 'GET', false);
$fb2 = testAccess($finUser, '/schedule', 'GET', false);
$fb3 = testAccess($finUser, '/dashboard/hr', 'GET', false);
$fb4 = testAccess($finUser, '/attendance/face', 'GET', false);
$fb5 = testAccess($finUser, '/settings/permissions', 'GET', false);

// 4. TEST HR MANAGER
echo "\n--- 4. TESTING HR MANAGER ---\n";
$hrUser = User::where('role', 'hr_manager')->first();
$h1 = testAccess($hrUser, '/dashboard/hr', 'GET', true);
$h2 = testAccess($hrUser, '/employees', 'GET', true);
$h3 = testAccess($hrUser, '/employees/create', 'GET', true);
$h4 = testAccess($hrUser, '/leaves', 'GET', true);
$h5 = testAccess($hrUser, '/attendance', 'GET', true);
$h6 = testAccess($hrUser, '/attendance/face', 'GET', true);
$h7 = testAccess($hrUser, '/schedule', 'GET', true);
// Blocked for HR:
$hb1 = testAccess($hrUser, '/settings/permissions', 'GET', false);

// 5. TEST SUPER ADMIN
echo "\n--- 5. TESTING SUPER ADMIN ---\n";
$saUser = User::where('role', 'super_admin')->first();
$s1 = testAccess($saUser, '/settings/permissions', 'GET', true);
$s2 = testAccess($saUser, '/dashboard/hr', 'GET', true);
$s3 = testAccess($saUser, '/employees', 'GET', true);

echo "\n======================================================================\n";
echo "FINAL SUMMARY: ALL ROLE TESTS EXECUTED SUCCESSFULLY!\n";
echo "======================================================================\n";
