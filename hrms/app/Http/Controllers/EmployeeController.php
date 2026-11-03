<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Tenant;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id ?? session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $currentRole = auth()->user()->role;

        // Employee role doesn't manage directory - redirect to personal portal
        if ($currentRole === 'employee') {
            return redirect()->route('employee.dashboard')->with('info', 'Redirected to your Employee Portal.');
        }

        $query = Employee::with(['department', 'designation'])
            ->where('tenant_id', $tenantId);

        // Department Manager only manages their department members
        if ($currentRole === 'department_manager') {
            $managerEmp = Employee::where('tenant_id', $tenantId)->find(session('portal_employee_id'));
            if ($managerEmp && $managerEmp->department_id) {
                $query->where('department_id', $managerEmp->department_id);
            }
        }

        // Status tab filter (active, on_leave, on_boarding, inactive)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search filter (name, email, phone, employee code)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Department filter
        if ($request->filled('department_id') && $request->department_id !== 'all') {
            $query->where('department_id', $request->department_id);
        }

        $employees = $query->latest()->paginate(10)->withQueryString();

        // Calculate status counts for pills/tabs
        $statusCounts = [
            'all' => Employee::where('tenant_id', $tenantId)->count(),
            'active' => Employee::where('tenant_id', $tenantId)->where('status', 'active')->count(),
            'on_leave' => Employee::where('tenant_id', $tenantId)->where('status', 'on_leave')->count(),
            'on_boarding' => Employee::where('tenant_id', $tenantId)->where('status', 'on_boarding')->count(),
            'inactive' => Employee::where('tenant_id', $tenantId)->where('status', 'inactive')->count(),
        ];

        $departments = Department::where('tenant_id', $tenantId)->get();

        return view('employees.index', compact('employees', 'statusCounts', 'departments'));
    }

    public function create()
    {
        $tenantId = auth()->user()->tenant_id ?? session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $departments = Department::where('tenant_id', $tenantId)->get();
        $designations = Designation::where('tenant_id', $tenantId)->get();

        return view('employees.create', compact('departments', 'designations'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id ?? session('current_tenant_id') ?? session('active_tenant_id') ?? 1;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:25',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'salary' => 'required|numeric|min:0',
            'joining_date' => 'nullable|date',
            'status' => 'required|in:active,on_leave,on_boarding,inactive',
            'employment_type' => 'nullable|string',
            'avatar' => 'nullable|string|max:500',
        ]);

        $nameParts = explode(' ', trim($validated['name']));
        $firstName = $nameParts[0];
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

        $avatar = $validated['avatar'] ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&auto=format&fit=crop&q=80';

        Employee::create([
            'tenant_id' => $tenantId,
            'branch_id' => 1,
            'department_id' => $validated['department_id'],
            'designation_id' => $validated['designation_id'],
            'employee_code' => 'EMP-' . (Employee::max('id') + 101),
            'name' => $validated['name'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? '+880-1700-000000',
            'avatar' => $avatar,
            'status' => $validated['status'],
            'salary' => $validated['salary'],
            'joining_date' => $validated['joining_date'] ?? date('Y-m-d'),
            'employment_type' => $validated['employment_type'] ?? 'full_time',
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee registered successfully!');
    }

    public function edit($id)
    {
        $tenantId = auth()->user()->tenant_id ?? session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($id);
        $departments = Department::where('tenant_id', $tenantId)->get();
        $designations = Designation::where('tenant_id', $tenantId)->get();

        return view('employees.edit', compact('employee', 'departments', 'designations'));
    }

    public function update(Request $request, $id)
    {
        $tenantId = auth()->user()->tenant_id ?? session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $id,
            'phone' => 'nullable|string|max:25',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'salary' => 'required|numeric|min:0',
            'joining_date' => 'nullable|date',
            'status' => 'required|in:active,on_leave,on_boarding,inactive',
            'employment_type' => 'nullable|string',
            'avatar' => 'nullable|string|max:500',
        ]);

        $nameParts = explode(' ', trim($validated['name']));
        $firstName = $nameParts[0];
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

        $employee->update([
            'department_id' => $validated['department_id'],
            'designation_id' => $validated['designation_id'],
            'name' => $validated['name'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $employee->phone,
            'status' => $validated['status'],
            'salary' => $validated['salary'],
            'joining_date' => $validated['joining_date'] ?? $employee->joining_date,
            'employment_type' => $validated['employment_type'] ?? $employee->employment_type,
            'avatar' => !empty($validated['avatar']) ? $validated['avatar'] : $employee->avatar,
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee profile updated successfully!');
    }

    public function destroy($id)
    {
        $tenantId = auth()->user()->tenant_id ?? session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($id);
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully!');
    }
}
