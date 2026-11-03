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

// Default Landing / Redirect
Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('welcome');

// Authentication routes
Auth::routes();

// Tenant & Role Switchers
Route::post('/tenant/switch', [DashboardController::class, 'switchTenant'])->name('tenant.switch');
Route::match(['get', 'post'], '/role/switch', [DashboardController::class, 'switchRole'])->name('role.switch');

// Role-Based Central Dashboards
Route::get('/home', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.view');

// Dedicated Role Dashboards (Direct Access)
Route::get('/dashboard/hr', function () {
    session(['active_role' => 'hr_manager']);
    return redirect()->route('dashboard', ['force_admin' => 1]);
})->name('dashboard.hr');

Route::get('/dashboard/manager', [ManagerDashboardController::class, 'index'])->name('dashboard.manager');
Route::get('/dashboard/finance', [PayrollDashboardController::class, 'index'])->name('dashboard.finance');
Route::get('/dashboard/employee', [EmployeeDashboardController::class, 'index'])->name('dashboard.employee');

// Employee Self-Service Dashboard / Portal
Route::get('/portal', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('portal.dashboard');
Route::post('/employee/enroll-face', [EmployeeDashboardController::class, 'enrollFace'])->name('employee.enroll-face');

// Employee Management
Route::prefix('employees')->group(function () {
    Route::get('/', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/{id}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
});

// Leave Management (Screenshot 3: P-HR Leave Calendar + Approval workflows)
Route::prefix('leaves')->group(function () {
    Route::get('/', [LeaveController::class, 'index'])->name('leaves.index');
    Route::get('/calendar', [LeaveController::class, 'calendar'])->name('leaves.calendar');
    Route::get('/apply', [LeaveController::class, 'apply'])->name('leaves.apply');
    Route::post('/apply', [LeaveController::class, 'store'])->name('leaves.store');
    Route::get('/approve/{id}', [LeaveController::class, 'approve'])->name('leaves.approve');
    Route::get('/reject/{id}', [LeaveController::class, 'reject'])->name('leaves.reject');
});

// Attendance Management (Screenshot 2: Numla Time & Attendance Biometric + AI Face Scanner)
Route::prefix('attendance')->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/face', [AttendanceController::class, 'faceScanner'])->name('attendance.face');
    Route::get('/mobile', [AttendanceController::class, 'mobileAttendance'])->name('attendance.mobile');
    Route::post('/face-scan', [AttendanceController::class, 'processFaceScan'])->name('attendance.facescan');
    Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
    Route::post('/break', [AttendanceController::class, 'toggleBreak'])->name('attendance.break');
});
Route::get('/mobile/attendance', [AttendanceController::class, 'mobileAttendance']);

// Shift Schedule Management (Screenshot 4: Agendrix Schedule Planner)
Route::prefix('schedule')->group(function () {
    Route::get('/', [ShiftScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/', [ShiftScheduleController::class, 'store'])->name('schedule.store');
    Route::delete('/{id}', [ShiftScheduleController::class, 'destroy'])->name('schedule.destroy');
});

// Payroll (Screenshot 1: Payroll Overview + Bangladesh Tax & PF Salary voucher)
Route::prefix('payrolls')->group(function () {
    Route::get('/', [PayrollController::class, 'index'])->name('payrolls.index');
    Route::get('/generate', [PayrollController::class, 'generate'])->name('payrolls.generate');
    Route::post('/generate', [PayrollController::class, 'store'])->name('payrolls.store');
    Route::post('/{id}/toggle-payment', [PayrollController::class, 'togglePayment'])->name('payrolls.toggle-payment');
    Route::get('/payslip/{id}', [PayrollController::class, 'payslip'])->name('payrolls.payslip');
});

// Static Pages
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/services', [PageController::class, 'services'])->name('services');
