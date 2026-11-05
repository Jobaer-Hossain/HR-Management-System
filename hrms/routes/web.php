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
use App\Http\Controllers\ManualAttendanceController;
use App\Http\Controllers\AttendancePolicyController;

// Default Landing / Redirect
Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('welcome');

// Authentication routes
Auth::routes();

Route::middleware('auth')->group(function () {
    // Platform Role & Organization Switchers (Restricted to Super Admin & Owner)
    Route::post('/tenant/switch', [DashboardController::class, 'switchTenant'])
        ->middleware('role:super_admin,admin,owner')
        ->name('tenant.switch');

    Route::match(['get', 'post'], '/role/switch', [DashboardController::class, 'switchRole'])
        ->middleware('role:super_admin,admin,owner')
        ->name('role.switch');

    // Central Dashboard Router
    Route::get('/home', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.view');

    // Dedicated Role Dashboards
    // 1. HR Executive Dashboard (Restricted to HR Manager, Admin, Super Admin)
    Route::get('/dashboard/hr', function () {
        abort_unless(auth()->user()->isHrManager() || auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin', 403, 'Access Restricted to HR Administrators.');
        return app(DashboardController::class)->index(request());
    })->middleware(['role:hr_manager,admin,super_admin', 'permission:dashboard.hr'])->name('dashboard.hr');

    // 2. Super Admin System Dashboard (Restricted to Super Admin)
    Route::get('/dashboard/super-admin', function () {
        abort_unless(auth()->user()->role === 'super_admin', 403, 'Access Restricted to Super Administrators.');
        return app(DashboardController::class)->index(request());
    })->middleware('role:super_admin')->name('dashboard.superadmin');

    // 3. Department Manager Dashboard (Restricted to Managers & HR/Admin)
    Route::get('/dashboard/manager', [ManagerDashboardController::class, 'index'])
        ->middleware(['role:department_manager,hr_manager,super_admin,admin', 'permission:dashboard.manager'])
        ->name('dashboard.manager');

    // 4. Finance & Payroll Dashboard (Restricted to Finance & HR/Admin)
    Route::get('/dashboard/finance', [PayrollDashboardController::class, 'index'])
        ->middleware(['role:finance,finance_manager,payroll_officer,hr_manager,super_admin,admin', 'permission:dashboard.finance'])
        ->name('dashboard.finance');

    // 5. Employee Self-Service Portal (Accessible to all authenticated staff; staff see their own profile)
    Route::get('/dashboard/employee', [EmployeeDashboardController::class, 'index'])->name('dashboard.employee');
    Route::get('/portal', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
    Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('portal.dashboard');

    // Facial Biometric Enrollment in Database (Admin/HR enrolls employee from Employee Directory)
    Route::post('/employee/enroll-face', [EmployeeDashboardController::class, 'enrollFace'])
        ->middleware(['role:hr_manager,super_admin,admin', 'permission:employees.enroll_face', 'throttle:10,1'])
        ->name('employee.enroll-face');

    // Employee Directory (CRUD) - Restricted to HR, Managers (read-only department), Finance (read-only salary), Super Admin
    // Regular employees do NOT manage or view company directory
    Route::prefix('employees')->middleware(['role:hr_manager,department_manager,finance,finance_manager,payroll_officer,super_admin,admin', 'permission:employees.view'])->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('employees.index');
        
        // Administrative modifications strictly restricted to HR Administrator and Super Admin
        Route::middleware(['role:hr_manager,super_admin,admin', 'permission:employees.create'])->group(function () {
            Route::get('/create', [EmployeeController::class, 'create'])->name('employees.create');
            Route::post('/', [EmployeeController::class, 'store'])->name('employees.store');
        });
        Route::middleware(['role:hr_manager,super_admin,admin', 'permission:employees.update'])->group(function () {
            Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
            Route::put('/{id}', [EmployeeController::class, 'update'])->name('employees.update');
        });
        Route::middleware(['role:hr_manager,super_admin,admin', 'permission:employees.delete'])->group(function () {
            Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        });
    });

    // Leave Management
    Route::prefix('leaves')->group(function () {
        // Staff can apply for their own leave
        Route::get('/apply', [LeaveController::class, 'apply'])->middleware('permission:leaves.create')->name('leaves.apply');
        Route::post('/apply', [LeaveController::class, 'store'])->middleware('permission:leaves.create')->name('leaves.store');
        
        // Company Leave Review, Calendar & Approvals - Restricted to Managers and HR Admin
        Route::middleware(['role:department_manager,hr_manager,super_admin,admin', 'permission:leaves.manage'])->group(function () {
            Route::get('/', [LeaveController::class, 'index'])->name('leaves.index');
            Route::get('/calendar', [LeaveController::class, 'calendar'])->name('leaves.calendar');
            Route::get('/approve/{id}', [LeaveController::class, 'approve'])->name('leaves.approve');
            Route::get('/reject/{id}', [LeaveController::class, 'reject'])->name('leaves.reject');
        });
    });

    // Attendance Management
    Route::prefix('attendance')->group(function () {
        // Personal Mobile Attendance Terminal (Accessible to all staff for their own check-in/out)
        Route::get('/mobile', [AttendanceController::class, 'mobileAttendance'])->middleware('permission:attendance.self')->name('attendance.mobile');
        Route::post('/check-in', [AttendanceController::class, 'checkIn'])->middleware('permission:attendance.self')->name('attendance.checkin');
        Route::post('/check-out', [AttendanceController::class, 'checkOut'])->middleware('permission:attendance.self')->name('attendance.checkout');
        Route::post('/break', [AttendanceController::class, 'toggleBreak'])->middleware('permission:attendance.self')->name('attendance.break');

        // Personal Manual Attendance Request (Fallback when facial recognition fails or after cutoff)
        Route::get('/manual-request', [ManualAttendanceController::class, 'create'])->middleware('permission:attendance.self')->name('attendance.manual.create');
        Route::post('/manual-request', [ManualAttendanceController::class, 'store'])->middleware('permission:attendance.self')->name('attendance.manual.store');

        // Biometric Face Scan API endpoint
        Route::post('/face-scan', [AttendanceController::class, 'processFaceScan'])->middleware('throttle:10,1')->name('attendance.facescan');

        // Branch / Department Manager & HR Admin Approval & Attendance Log
        Route::middleware(['role:department_manager,hr_manager,super_admin,admin', 'permission:attendance.view'])->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');
            Route::get('/face', [AttendanceController::class, 'faceScanner'])->middleware('permission:attendance.face')->name('attendance.face');
            Route::get('/manual-approvals', [ManualAttendanceController::class, 'manage'])->name('attendance.manual.manage');
            Route::post('/manual-approvals/{id}/approve', [ManualAttendanceController::class, 'approve'])->name('attendance.manual.approve');
            Route::post('/manual-approvals/{id}/reject', [ManualAttendanceController::class, 'reject'])->name('attendance.manual.reject');
        });

        // Attendance Policy & Timing Cutoffs Settings - Restricted to HR Admin and Super Admin
        Route::middleware(['role:hr_manager,super_admin,admin', 'permission:attendance.view'])->group(function () {
            Route::get('/policy', [AttendancePolicyController::class, 'edit'])->name('attendance.policy.edit');
            Route::post('/policy', [AttendancePolicyController::class, 'update'])->name('attendance.policy.update');
        });
    });
    Route::get('/mobile/attendance', [AttendanceController::class, 'mobileAttendance'])->middleware('permission:attendance.self');

    // Shift Schedule Management
    Route::prefix('schedule')->group(function () {
        // Company Schedule Planner - Restricted to Managers and HR Admin (Staff see own shifts on /portal)
        Route::middleware(['role:department_manager,hr_manager,super_admin,admin', 'permission:schedule.view'])->group(function () {
            Route::get('/', [ShiftScheduleController::class, 'index'])->name('schedule.index');
            Route::post('/', [ShiftScheduleController::class, 'store'])->middleware('permission:schedule.manage')->name('schedule.store');
            Route::delete('/{id}', [ShiftScheduleController::class, 'destroy'])->middleware('permission:schedule.manage')->name('schedule.destroy');
        });
    });

    // Payroll Management
    Route::prefix('payrolls')->group(function () {
        // Individual payslip voucher - Staff can view their OWN payslip (ownership enforced in controller)
        Route::get('/payslip/{id}', [PayrollController::class, 'payslip'])->middleware('permission:payroll.self')->name('payrolls.payslip');

        // Corporate Payroll Batches & Generation - Restricted to Finance & HR Admin
        Route::middleware(['role:finance,finance_manager,payroll_officer,hr_manager,super_admin,admin', 'permission:payroll.manage'])->group(function () {
            Route::get('/', [PayrollController::class, 'index'])->name('payrolls.index');
            Route::get('/generate', [PayrollController::class, 'generate'])->name('payrolls.generate');
            Route::post('/generate', [PayrollController::class, 'store'])->name('payrolls.store');
            Route::post('/{id}/toggle-payment', [PayrollController::class, 'togglePayment'])->name('payrolls.toggle-payment');
        });
    });

    // System Permission Management - Strictly restricted to Super Admin
    Route::prefix('settings/permissions')->middleware(['role:super_admin', 'permission:permissions.manage'])->group(function () {
        Route::get('/', [RolePermissionController::class, 'index'])->name('permissions.index');
        Route::put('/', [RolePermissionController::class, 'update'])->name('permissions.update');
        Route::put('/role', [RolePermissionController::class, 'updateRole'])->name('permissions.role.update');
    });
});

// Static Pages
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/services', [PageController::class, 'services'])->name('services');
