<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveApplication;
use App\Models\ShiftSchedule;
use App\Models\Department;
use App\Models\Tenant;
use Illuminate\Http\Request;

class ManagerDashboardController extends Controller
{
    /**
     * Department / Line Manager Dashboard
     */
    public function index(Request $request)
    {
        $tenantId = session('current_tenant_id') ?? session('active_tenant_id') ?? 1;
        $tenant = Tenant::find($tenantId) ?? Tenant::first();

        // Department context: default to Department #1 (Human Resources) or selected team
        $deptId = $request->get('dept_id', session('manager_dept_id', 1));
        $department = Department::where('tenant_id', $tenant->id)->find($deptId);

        if (!$department) {
            $department = Department::where('tenant_id', $tenant->id)->first();
        }

        if ($department) {
            session(['manager_dept_id' => $department->id]);
        }

        $allDepartments = Department::where('tenant_id', $tenant->id)->get();

        // Team members in this manager's department
        $teamMembers = Employee::with(['designation', 'attendances' => function ($q) {
            $q->where('attendance_date', date('Y-m-d'));
        }])
        ->where('tenant_id', $tenant->id)
        ->where('department_id', $department->id)
        ->get();

        $teamMemberIds = $teamMembers->pluck('id')->toArray();

        // 1. Team Metrics
        $teamSize = $teamMembers->count();
        $presentToday = Attendance::where('tenant_id', $tenant->id)
            ->whereIn('employee_id', $teamMemberIds)
            ->where('attendance_date', date('Y-m-d'))
            ->where('status', 'present')
            ->count();

        $onLeaveToday = $teamMembers->where('status', 'on_leave')->count();

        $pendingTeamLeavesCount = LeaveApplication::where('tenant_id', $tenant->id)
            ->whereIn('employee_id', $teamMemberIds)
            ->where('status', 'pending')
            ->count();

        $pendingManualAttendanceCount = \App\Models\ManualAttendanceRequest::where('tenant_id', $tenant->id)
            ->whereIn('employee_id', $teamMemberIds)
            ->where('status', 'pending')
            ->count();

        // 2. Pending & Recent Team Leave Applications
        $teamLeaves = LeaveApplication::with(['employee.designation', 'leaveType'])
            ->where('tenant_id', $tenant->id)
            ->whereIn('employee_id', $teamMemberIds)
            ->latest()
            ->take(8)
            ->get();

        // 3. Team Weekly Shift Schedule (Agendrix Match)
        $teamShifts = ShiftSchedule::with('employee')
            ->where('tenant_id', $tenant->id)
            ->whereIn('employee_id', $teamMemberIds)
            ->get();

        // 4. Today's Team Attendance Roster
        $todayAttendances = Attendance::with('employee')
            ->where('tenant_id', $tenant->id)
            ->whereIn('employee_id', $teamMemberIds)
            ->where('attendance_date', date('Y-m-d'))
            ->latest('updated_at')
            ->get();

        $days = ['Sat', 'Sun', 'Mon', 'Tues', 'Wed', 'Thu', 'Fri'];

        return view('manager.dashboard', compact(
            'tenant',
            'department',
            'allDepartments',
            'teamMembers',
            'teamSize',
            'presentToday',
            'onLeaveToday',
            'pendingTeamLeavesCount',
            'pendingManualAttendanceCount',
            'teamLeaves',
            'teamShifts',
            'todayAttendances',
            'days'
        ));
    }
}
