<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ShiftScheduleController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\ManagerDashboardController;
use App\Http\Controllers\PayrollDashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RolePermissionController;

// Default Landing / Redirect
Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('welcome');

// Authentication routes
Auth::routes();

Route::middleware('auth')->group(function () {
// Tenant & Role Switchers
Route::post('/tenant/switch', [DashboardController::class, 'switchTenant'])->middleware('role:hr_manager,admin,super_admin,owner')->name('tenant.switch');
Route::match(['get', 'post'], '/role/switch', [DashboardController::class, 'switchRole'])->name('role.switch');

// Role-Based Central Dashboards
Route::get('/home', [DashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('dashboard.view');

// Dedicated Role Dashboards (Direct Access)
Route::get('/dashboard/hr', function () {
    session(['active_role' => 'hr_manager']);
    return redirect()->route('dashboard', ['force_admin' => 1]);
})->name('dashboard.hr');

Route::get('/dashboard/super-admin', function () {
    abort_unless(auth()->user()->role === 'super_admin', 403);
    return app(DashboardController::class)->index(request());
})->middleware(['role:super_admin', 'permission:dashboard.view'])->name('dashboard.superadmin');

Route::get('/dashboard/manager', [ManagerDashboardController::class, 'index'])
    ->middleware(['role:department_manager,hr_manager', 'permission:dashboard.view'])
    ->name('dashboard.manager');

Route::get('/dashboard/finance', [PayrollDashboardController::class, 'index'])
    ->middleware(['role:finance,hr_manager', 'permission:dashboard.view'])
    ->name('dashboard.finance');

Route::get('/dashboard/employee', [EmployeeDashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('dashboard.employee');

// Employee Self-Service Dashboard / Portal
Route::get('/portal', [EmployeeDashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('employee.dashboard');
Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('portal.dashboard');
Route::post('/employee/enroll-face', [EmployeeDashboardController::class, 'enrollFace'])->middleware(['permission:attendance.self', 'throttle:5,1'])->name('employee.enroll-face');

// Employee Management
Route::prefix('employees')->group(function () {
    Route::get('/', [EmployeeController::class, 'index'])
        ->middleware('permission:employees.view')->name('employees.index');
    
    // Administrative actions restricted to HR Administrator
    Route::middleware(['role:hr_manager', 'permission:employees.create'])->group(function () {
        Route::get('/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/', [EmployeeController::class, 'store'])->name('employees.store');
    });
    Route::middleware(['role:hr_manager', 'permission:employees.update'])->group(function () {
        Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/{id}', [EmployeeController::class, 'update'])->name('employees.update');
    });
    Route::middleware(['role:hr_manager', 'permission:employees.delete'])->group(function () {
        Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    });
});

// Leave Management (Screenshot 3: P-HR Leave Calendar + Approval workflows)
Route::prefix('leaves')->group(function () {
    Route::get('/', [LeaveController::class, 'index'])->middleware('permission:leaves.view')->name('leaves.index');
    Route::get('/calendar', [LeaveController::class, 'calendar'])->middleware('permission:leaves.view')->name('leaves.calendar');
    Route::get('/apply', [LeaveController::class, 'apply'])->middleware('permission:leaves.create')->name('leaves.apply');
    Route::post('/apply', [LeaveController::class, 'store'])->middleware('permission:leaves.create')->name('leaves.store');
    
    // Approvals restricted to Department Managers and HR Admin
    Route::middleware(['role:department_manager,hr_manager', 'permission:leaves.manage'])->group(function () {
        Route::get('/approve/{id}', [LeaveController::class, 'approve'])->name('leaves.approve');
        Route::get('/reject/{id}', [LeaveController::class, 'reject'])->name('leaves.reject');
    });
});

// Attendance Management (Screenshot 2: Numla Time & Attendance Biometric + AI Face Scanner)
Route::prefix('attendance')->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->middleware('permission:attendance.view')->name('attendance.index');
    Route::get('/face', [AttendanceController::class, 'faceScanner'])->middleware('permission:attendance.face')->name('attendance.face');
    Route::get('/mobile', [AttendanceController::class, 'mobileAttendance'])->middleware('permission:attendance.self')->name('attendance.mobile');
    Route::post('/face-scan', [AttendanceController::class, 'processFaceScan'])->middleware('throttle:10,1')->name('attendance.facescan');
    Route::post('/check-in', [AttendanceController::class, 'checkIn'])->middleware('permission:attendance.self')->name('attendance.checkin');
    Route::post('/check-out', [AttendanceController::class, 'checkOut'])->middleware('permission:attendance.self')->name('attendance.checkout');
    Route::post('/break', [AttendanceController::class, 'toggleBreak'])->middleware('permission:attendance.self')->name('attendance.break');
});
Route::get('/mobile/attendance', [AttendanceController::class, 'mobileAttendance'])->middleware('permission:attendance.self');

// Shift Schedule Management (Screenshot 4: Agendrix Schedule Planner)
Route::prefix('schedule')->group(function () {
    Route::get('/', [ShiftScheduleController::class, 'index'])->middleware('permission:schedule.view')->name('schedule.index');
    
    // Shift modifications restricted to Managers and HR
    Route::middleware(['role:department_manager,hr_manager', 'permission:schedule.manage'])->group(function () {
        Route::post('/', [ShiftScheduleController::class, 'store'])->name('schedule.store');
        Route::delete('/{id}', [ShiftScheduleController::class, 'destroy'])->name('schedule.destroy');
    });
});

// Payroll (Screenshot 1: Payroll Overview + Bangladesh Tax & PF Salary voucher)
Route::prefix('payrolls')->group(function () {
    // Individual payslip accessible to staff/employee
    Route::get('/payslip/{id}', [PayrollController::class, 'payslip'])->middleware('permission:payroll.view')->name('payrolls.payslip');

    // Corporate payroll batches restricted to Finance & HR
    Route::middleware(['role:finance,hr_manager', 'permission:payroll.view'])->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('payrolls.index');
        Route::get('/generate', [PayrollController::class, 'generate'])->middleware('permission:payroll.manage')->name('payrolls.generate');
        Route::post('/generate', [PayrollController::class, 'store'])->middleware('permission:payroll.manage')->name('payrolls.store');
        Route::post('/{id}/toggle-payment', [PayrollController::class, 'togglePayment'])->middleware('permission:payroll.manage')->name('payrolls.toggle-payment');
    });
});

// Per-user permission overrides. Role defaults remain available until an administrator explicitly changes them.
Route::prefix('settings/permissions')->middleware(['role:hr_manager,admin,super_admin,owner', 'permission:permissions.manage'])->group(function () {
    Route::get('/', [RolePermissionController::class, 'index'])->name('permissions.index');
    Route::put('/', [RolePermissionController::class, 'update'])->name('permissions.update');
    Route::put('/role', [RolePermissionController::class, 'updateRole'])->name('permissions.role.update');
});
});

// Static Pages
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/services', [PageController::class, 'services'])->name('services');
