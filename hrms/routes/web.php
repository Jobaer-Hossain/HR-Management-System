<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PageController;

// Default
Route::get('/', [PageController::class, 'welcome'])->name('welcome');

// Authentication routes (already by Laravel UI/Auth)
Auth::routes();

// Dashboard
Route::get('/home', [DashboardController::class, 'index'])->name('dashboard');

// Employee Management
Route::prefix('employees')->group(function () {
    Route::get('/', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/{id}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
});

// Leave Management
Route::prefix('leaves')->group(function () {
    Route::get('/', [LeaveController::class, 'index'])->name('leaves.index');
    Route::get('/apply', [LeaveController::class, 'apply'])->name('leaves.apply');
    Route::post('/apply', [LeaveController::class, 'store'])->name('leaves.store');
    Route::get('/approve/{id}', [LeaveController::class, 'approve'])->name('leaves.approve');
    Route::get('/reject/{id}', [LeaveController::class, 'reject'])->name('leaves.reject');
});

// Payroll
Route::prefix('payrolls')->group(function () {
    Route::get('/', [PayrollController::class, 'index'])->name('payrolls.index');
    Route::get('/generate', [PayrollController::class, 'generate'])->name('payrolls.generate');
    Route::post('/generate', [PayrollController::class, 'store'])->name('payrolls.store');
    Route::get('/payslip/{id}', [PayrollController::class, 'payslip'])->name('payrolls.payslip');
});

// Static Pages
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/services', [PageController::class, 'services'])->name('services');
