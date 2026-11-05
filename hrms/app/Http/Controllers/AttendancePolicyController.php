<?php

namespace App\Http\Controllers;

use App\Models\AttendancePolicy;
use App\Models\Tenant;
use Illuminate\Http\Request;

class AttendancePolicyController extends Controller
{
    public function edit()
    {
        $tenantId = (int) (auth()->user()?->tenant_id ?? session('current_tenant_id') ?? 1);
        $tenant = Tenant::find($tenantId) ?? Tenant::first();
        $policy = AttendancePolicy::forTenant($tenant->id);

        return view('attendance.policy', compact('tenant', 'policy'));
    }

    public function update(Request $request)
    {
        $tenantId = (int) (auth()->user()?->tenant_id ?? session('current_tenant_id') ?? 1);
        $policy = AttendancePolicy::forTenant($tenantId);

        $validated = $request->validate([
            'policy_name' => 'required|string|max:255',
            'check_in_start' => 'required|date_format:H:i',
            'check_in_end' => 'required|date_format:H:i',
            'check_out_start' => 'required|date_format:H:i',
            'check_out_end' => 'required|date_format:H:i',
            'grace_period_minutes' => 'required|integer|min:0|max:120',
            'enforce_checkin_cutoff' => 'nullable|boolean',
            'enforce_checkout_cutoff' => 'nullable|boolean',
            'auto_block_message' => 'required|string|max:255',
        ]);

        $policy->update([
            'policy_name' => $validated['policy_name'],
            'check_in_start' => $validated['check_in_start'] . ':00',
            'check_in_end' => $validated['check_in_end'] . ':00',
            'check_out_start' => $validated['check_out_start'] . ':00',
            'check_out_end' => $validated['check_out_end'] . ':00',
            'grace_period_minutes' => $validated['grace_period_minutes'],
            'enforce_checkin_cutoff' => $request->boolean('enforce_checkin_cutoff'),
            'enforce_checkout_cutoff' => $request->boolean('enforce_checkout_cutoff'),
            'auto_block_message' => $validated['auto_block_message'],
        ]);

        return redirect()->route('attendance.policy.edit')->with('success', 'Attendance timing policy and cutoff rules updated successfully.');
    }
}
