<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ShiftSchedule;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftScheduleController extends Controller
{
    /**
     * Display weekly schedule matching Screenshot 4 (Agendrix)
     */
    public function index(Request $request)
    {
        $tenantId = session('current_tenant_id', 1);

        // Days of week matching the Agendrix prototype
        $days = ['Sat', 'Sun', 'Mon', 'Tues', 'Wed', 'Thu', 'Fri'];
        $selectedRange = $request->get('range', 'July, 15, 2025 - 22, July, 2025');

        $currentRole = session('active_role', auth()->check() ? auth()->user()->role : 'hr_manager');

        $empQuery = Employee::where('tenant_id', $tenantId)
            ->with(['shiftSchedules' => function ($q) {
                $q->orderBy('id', 'asc');
            }]);

        // Role-Based Schedule Distribution
        if ($currentRole === 'employee') {
            $empId = session('portal_employee_id', 1);
            $empQuery->where('id', $empId);
        } elseif ($currentRole === 'department_manager') {
            $managerEmp = Employee::find(session('portal_employee_id', 2));
            if ($managerEmp && $managerEmp->department_id) {
                $empQuery->where('department_id', $managerEmp->department_id);
            }
        }

        $employees = $empQuery->get();

        // If no employees or schedules in other tenant, fallback to all employees with schedules
        if ($employees->isEmpty()) {
            $employees = Employee::with('shiftSchedules')->get();
        }

        // Available shifts for assigning new shifts
        $shifts = Shift::all();

        return view('schedule.index', compact('employees', 'days', 'selectedRange', 'shifts'));
    }

    /**
     * Store a new shift slot
     */
    public function store(Request $request)
    {
        $tenantId = session('current_tenant_id', 1);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'day_name' => 'required|string',
            'time_slot' => 'required|string',
            'role_title' => 'required|string',
            'break_minutes' => 'nullable|integer',
            'color' => 'nullable|string',
        ]);

        ShiftSchedule::create([
            'tenant_id' => $tenantId,
            'employee_id' => $validated['employee_id'],
            'shift_id' => 1,
            'date' => date('Y-m-d'),
            'day_name' => $validated['day_name'],
            'time_slot' => $validated['time_slot'],
            'role_title' => $validated['role_title'],
            'break_minutes' => $validated['break_minutes'] ?? 30,
            'color' => $validated['color'] ?? 'primary',
            'status' => 'scheduled',
        ]);

        return redirect()->route('schedule.index')->with('success', 'Shift slot added successfully!');
    }

    /**
     * Delete a shift slot
     */
    public function destroy($id)
    {
        $slot = ShiftSchedule::findOrFail($id);
        $slot->delete();

        return redirect()->route('schedule.index')->with('success', 'Shift slot removed.');
    }
}
