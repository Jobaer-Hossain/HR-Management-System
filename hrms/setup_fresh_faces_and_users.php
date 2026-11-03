<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "=== 1. Resetting all employee face biometrics to EMPTY ===\n";
DB::table('employees')->update([
    'face_descriptor' => null,
    'face_snapshot' => null,
    'face_biometric_id' => null,
    'face_enrolled_at' => null,
]);

$totalEmp = Employee::count();
$enrolledCount = Employee::whereNotNull('face_descriptor')->count();
echo "Total employees: {$totalEmp}, Currently enrolled faces: {$enrolledCount} (All cleared successfully!)\n\n";

echo "=== 2. Creating / Updating User login accounts for all staff ===\n";

// Core Role Demo Users
$coreUsers = [
    [
        'name' => 'William Dolaz',
        'email' => 'admin@hrms.com',
        'password' => Hash::make('password123'),
        'role' => 'hr_manager',
        'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80',
        'status' => 'active',
        'tenant_id' => 1,
    ],
    [
        'name' => 'Robert Fox',
        'email' => 'manager@hrms.com',
        'password' => Hash::make('password123'),
        'role' => 'department_manager',
        'avatar' => 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=100&auto=format&fit=crop&q=80',
        'status' => 'active',
        'tenant_id' => 1,
    ],
    [
        'name' => 'Jacob Jones',
        'email' => 'finance@hrms.com',
        'password' => Hash::make('password123'),
        'role' => 'finance',
        'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop&q=80',
        'status' => 'active',
        'tenant_id' => 1,
    ],
    [
        'name' => 'Cody Fisher',
        'email' => 'employee@hrms.com',
        'password' => Hash::make('password123'),
        'role' => 'employee',
        'avatar' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&auto=format&fit=crop&q=80',
        'status' => 'active',
        'tenant_id' => 1,
    ],
];

foreach ($coreUsers as $u) {
    User::updateOrCreate(
        ['email' => $u['email']],
        $u
    );
    echo "Core User ready: {$u['email']} (Role: {$u['role']})\n";
}

// All 19 Employees
$employees = Employee::with(['department', 'designation'])->get();

foreach ($employees as $emp) {
    // Determine appropriate role based on designation / department
    $role = 'employee';
    $desigTitle = strtolower($emp->designation->designation_name ?? '');
    $deptName = strtolower($emp->department->department_name ?? '');

    if (str_contains($desigTitle, 'hr manager') || str_contains($deptName, 'human resources')) {
        $role = 'hr_manager';
    } elseif (str_contains($desigTitle, 'manager') || str_contains($desigTitle, 'lead')) {
        $role = 'department_manager';
    } elseif (str_contains($deptName, 'finance') || str_contains($desigTitle, 'finance') || str_contains($desigTitle, 'cashier')) {
        $role = 'finance';
    }

    $user = User::updateOrCreate(
        ['email' => $emp->email],
        [
            'tenant_id' => $emp->tenant_id ?? 1,
            'name' => $emp->name,
            'email' => $emp->email,
            'password' => Hash::make('password123'),
            'role' => $role,
            'avatar' => $emp->avatar,
            'status' => 'active',
        ]
    );

    echo "Employee User ready: {$emp->name} | {$emp->email} | Role: {$role} | Pass: password123\n";
}

echo "\nDone! Total Users in DB: " . User::count() . "\n";
