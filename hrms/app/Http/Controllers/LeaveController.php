<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function index()
    {
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $currentRole = session('active_role', auth()->check() ? auth()->user()->role : 'hr_manager');

        $query = LeaveApplication::with(['employee.department', 'leaveType'])
            ->where('tenant_id', $tenantId);

        // Role-Based Leave Distribution
        if ($currentRole === 'employee') {
            $empId = session('portal_employee_id', 1);
            $query->where('employee_id', $empId);
        } elseif ($currentRole === 'department_manager') {
            $managerEmp = Employee::find(session('portal_employee_id', 2));
            if ($managerEmp && $managerEmp->department_id) {
                $query->whereHas('employee', function ($q) use ($managerEmp) {
                    $q->where('department_id', $managerEmp->department_id);
                });
            }
        }

        $leaves = $query->latest()
            ->paginate(12);

        return view('leaves.index', compact('leaves'));
    }

    public function calendar(Request $request)
    {
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $tenant = Tenant::find($tenantId) ?? Tenant::first();

        // Base date for weekly calendar (default 2026-01-10 matching Screenshot 3)
        $startDateStr = $request->get('date', '2026-01-10');
        try {
            $startDate = Carbon::parse($startDateStr);
        } catch (\Exception $e) {
            $startDate = Carbon::parse('2026-01-10');
        }

        // 7 days of the calendar week
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $currentDay = $startDate->copy()->addDays($i);
            $days[] = [
                'day' => $currentDay->format('d'),
                'weekday' => $currentDay->format('D'),
                'date' => $currentDay->format('Y-m-d'),
            ];
        }

        $prevDate = $startDate->copy()->subDays(7)->format('Y-m-d');
        $nextDate = $startDate->copy()->addDays(7)->format('Y-m-d');
        $currentLabel = $startDate->format('d F Y');
        $rangeLabel = $days[0]['day'] . ' ' . $startDate->format('M Y') . ' - ' . $days[6]['day'] . ' ' . $startDate->copy()->addDays(6)->format('M Y');

        // Query team members with department and leaves
        $query = Employee::with(['department', 'designation', 'leaves.leaveType'])
            ->where('tenant_id', $tenant->id);

        // Filter 1: Employee Search (Name or Email)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter 2: Team / Department Filter
        if ($request->filled('team') && $request->team !== 'all') {
            $team = $request->team;
            $query->whereHas('department', function($q) use ($team) {
                $q->where('department_name', $team)
                  ->orWhere('id', $team);
            });
        }

        // Filter 3: Leave Type Filter
        if ($request->filled('leave_type') && $request->leave_type !== 'all') {
            $leaveType = $request->leave_type;
            $query->whereHas('leaves', function($q) use ($leaveType) {
                $q->where('leave_type_id', $leaveType)
                  ->orWhereHas('leaveType', function($lq) use ($leaveType) {
                      $lq->where('name', $leaveType)
                         ->orWhere('name', 'like', "%{$leaveType}%");
                  });
            });
        }

        $teamMembers = $query->get();

        // If no filter is applied, sort to show Screenshot 3 prototype members first
        if (!$request->filled('search') && (!$request->filled('team') || $request->team === 'all') && (!$request->filled('leave_type') || $request->leave_type === 'all')) {
            $priorityNames = [
                'Robert Fox', 'Ralph Edwards', 'Jane Cooper', 'Leslie Alexander',
                'Esther Howard', 'Guy Hawkins', 'Bessie Cooper', 'Kathryn Murphy'
            ];
            $teamMembers = $teamMembers->sortBy(function($emp) use ($priorityNames) {
                $idx = array_search($emp->name, $priorityNames);
                return $idx === false ? 999 : $idx;
            })->values();
        }

        $departments = Department::where('tenant_id', $tenant->id)->get();
        $leaveTypes = LeaveType::where('tenant_id', $tenant->id)->get();

        return view('leaves.calendar', compact(
            'tenant',
            'days',
            'teamMembers',
            'departments',
            'leaveTypes',
            'startDate',
            'prevDate',
            'nextDate',
            'currentLabel',
            'rangeLabel'
        ));
    }

    public function apply()
    {
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;

        if (auth()->user()?->isEmployee()) {
            $employees = Employee::where('tenant_id', $tenantId)
                ->where(function($q) {
                    $q->where('id', auth()->user()->employee_id)
                      ->orWhere('email', auth()->user()->email);
                })->get();
        } else {
            $employees = Employee::where('tenant_id', $tenantId)->where('status', '!=', 'inactive')->get();
        }

        $leaveTypes = LeaveType::where('tenant_id', $tenantId)->get();

        return view('leaves.apply', compact('employees', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;

        // If employee, force submission for their own employee profile
        if (auth()->user()?->isEmployee()) {
            $ownEmp = Employee::where('tenant_id', $tenantId)
                ->where(function($q) {
                    $q->where('id', auth()->user()->employee_id)
                      ->orWhere('email', auth()->user()->email);
                })->first();
            $targetEmpId = $ownEmp ? $ownEmp->id : $request->input('employee_id');
        } else {
            $targetEmpId = $request->input('employee_id') ?? $request->input('emp_id');
        }

        $request->merge(['employee_id' => $targetEmpId]);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        $validated['tenant_id'] = $tenantId;
        $validated['status'] = 'pending';
        $validated['total_days'] = max(1, (strtotime($validated['end_date']) - strtotime($validated['start_date'])) / 86400 + 1);

        LeaveApplication::create($validated);

        if (auth()->user()?->isEmployee()) {
            return redirect()->route('employee.dashboard')->with('success', 'Your leave request has been submitted successfully.');
        }

        return redirect()->route('leaves.index')->with('success', 'Leave request submitted successfully.');
    }

    public function approve($id)
    {
        $leave = LeaveApplication::with('employee')->findOrFail($id);

        if (auth()->user()?->role === 'department_manager') {
            $managerEmp = Employee::where('tenant_id', $leave->tenant_id)
                ->where(function($q) {
                    $q->where('id', auth()->user()->employee_id)
                      ->orWhere('email', auth()->user()->email);
                })->first();
            if ($managerEmp && $managerEmp->department_id && $leave->employee) {
                abort_unless((int)$managerEmp->department_id === (int)$leave->employee->department_id, 403, 'Unauthorized: You can only approve leave for employees in your department.');
            }
        }

        $leave->status = 'approved';
        $leave->save();

        return redirect()->back()->with('success', 'Leave application approved!');
    }

    public function reject($id)
    {
        $leave = LeaveApplication::with('employee')->findOrFail($id);

        if (auth()->user()?->role === 'department_manager') {
            $managerEmp = Employee::where('tenant_id', $leave->tenant_id)
                ->where(function($q) {
                    $q->where('id', auth()->user()->employee_id)
                      ->orWhere('email', auth()->user()->email);
                })->first();
            if ($managerEmp && $managerEmp->department_id && $leave->employee) {
                abort_unless((int)$managerEmp->department_id === (int)$leave->employee->department_id, 403, 'Unauthorized: You can only reject leave for employees in your department.');
            }
        }

        $leave->status = 'rejected';
        $leave->save();

        return redirect()->back()->with('info', 'Leave application rejected.');
    }
}
