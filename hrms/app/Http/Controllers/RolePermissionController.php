<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserPermission;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $users = User::where('tenant_id', $tenantId)->orderBy('name')->get();
        $selectedUser = $users->firstWhere('id', (int) $request->input('user_id')) ?? $users->first();
        $labels = config('permissions.labels', []);
        $roles = array_keys(config('permissions.roles', []));
        $selectedRole = in_array($request->input('role'), $roles, true)
            ? $request->input('role')
            : ($selectedUser?->role ?? 'hr_manager');
        $roleOverrides = RolePermission::where('role', $selectedRole)->pluck('allowed', 'permission')->all();
        $roleEffective = [];
        foreach ($labels as $permission => $label) {
            $roleEffective[$permission] = array_key_exists($permission, $roleOverrides)
                ? (bool) $roleOverrides[$permission]
                : (in_array('*', config('permissions.roles.' . $selectedRole, []), true)
                    || in_array($permission, config('permissions.roles.' . $selectedRole, []), true));
        }
        $effective = [];

        if ($selectedUser) {
            foreach (array_keys($labels) as $permission) {
                $effective[$permission] = $selectedUser->hasPermission($permission);
            }
        }

        return view('settings.permissions', compact('users', 'selectedUser', 'labels', 'effective', 'roles', 'selectedRole', 'roleEffective'));
    }

    public function updateRole(Request $request)
    {
        $roles = array_keys(config('permissions.roles', []));
        $validated = $request->validate([
            'role' => 'required|in:' . implode(',', $roles),
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', array_keys(config('permissions.labels', []))),
        ]);

        abort_if(in_array($validated['role'], ['super_admin', 'admin', 'owner'], true), 403, 'Protected platform roles cannot be restricted.');

        $selected = array_flip($validated['permissions'] ?? []);
        $permissions = array_keys(config('permissions.labels', []));

        DB::transaction(function () use ($validated, $selected, $permissions) {
            RolePermission::where('role', $validated['role'])->delete();
            RolePermission::insert(collect($permissions)->map(fn ($permission) => [
                'role' => $validated['role'],
                'permission' => $permission,
                'allowed' => isset($selected[$permission]),
                'created_at' => now(),
                'updated_at' => now(),
            ])->all());
        });

        return redirect()->route('permissions.index', ['role' => $validated['role']])
            ->with('success', 'Role permissions updated successfully. Changes apply immediately.');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', array_keys(config('permissions.labels', []))),
        ]);

        $user = User::where('tenant_id', auth()->user()->tenant_id)->findOrFail($validated['user_id']);
        $selected = array_flip($validated['permissions'] ?? []);
        $permissions = array_keys(config('permissions.labels', []));

        DB::transaction(function () use ($user, $selected, $permissions) {
            UserPermission::where('user_id', $user->id)->delete();
            $rows = collect($permissions)->map(fn ($permission) => [
                'user_id' => $user->id,
                'permission' => $permission,
                'allowed' => isset($selected[$permission]),
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();
            UserPermission::insert($rows);
        });

        return redirect()->route('permissions.index', ['user_id' => $user->id])
            ->with('success', 'Permissions updated for ' . $user->name . '.');
    }
}
