<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Models\Tenant;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index()
    {
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $leaves = LeaveApplication::with(['employee.department', 'leaveType'])
            ->where('tenant_id', $tenantId)
            ->latest()
            ->paginate(12);

        return view('leaves.index', compact('leaves'));
    }

    public function calendar(Request $request)
    {
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $tenant = Tenant::find($tenantId) ?? Tenant::first();

        // 7 days of the calendar week (matching Screenshot 3: 10 Wed, 11 Thu, 12 Fri, 13 Sat, 14 Sun, 15 Mon, 16 Tue)
        $days = [
            ['day' => '10', 'weekday' => 'Wed', 'date' => '2026-01-10'],
            ['day' => '11', 'weekday' => 'Thu', 'date' => '2026-01-11'],
            ['day' => '12', 'weekday' => 'Fri', 'date' => '2026-01-12'],
            ['day' => '13', 'weekday' => 'Sat', 'date' => '2026-01-13'],
            ['day' => '14', 'weekday' => 'Sun', 'date' => '2026-01-14'],
            ['day' => '15', 'weekday' => 'Mon', 'date' => '2026-01-15'],
            ['day' => '16', 'weekday' => 'Tue', 'date' => '2026-01-16'],
        ];

        // Fetch team members (Robert Fox, Ralph Edwards, Jane Cooper, Leslie Alexander, Esther Howard, Guy Hawkins, Bessie Cooper, Kathryn Murphy)
        $teamMembers = Employee::with(['department', 'leaves.leaveType'])
            ->where('tenant_id', $tenant->id)
            ->take(8)
            ->get();

        $leaveTypes = LeaveType::where('tenant_id', $tenant->id)->get();

        return view('leaves.calendar', compact('tenant', 'days', 'teamMembers', 'leaveTypes'));
    }

    public function apply()
    {
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $employees = Employee::where('tenant_id', $tenantId)->where('status', '!=', 'inactive')->get();
        $leaveTypes = LeaveType::where('tenant_id', $tenantId)->get();

        return view('leaves.apply', compact('employees', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;

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

        return redirect()->route('leaves.index')->with('success', 'Leave request submitted successfully.');
    }

    public function approve($id)
    {
        $leave = LeaveApplication::findOrFail($id);
        $leave->status = 'approved';
        $leave->save();

        return redirect()->back()->with('success', 'Leave application approved!');
    }

    public function reject($id)
    {
        $leave = LeaveApplication::findOrFail($id);
        $leave->status = 'rejected';
        $leave->save();

        return redirect()->back()->with('info', 'Leave application rejected.');
    }
}
