<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request for Role-Based Access Control (RBAC).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $currentRole = auth()->user()->role;

        // HR Manager / Admin has full administrative access to all modules
        if ($currentRole === 'hr_manager' || $currentRole === 'admin' || $currentRole === 'super_admin' || $currentRole === 'owner') {
            return $next($request);
        }

        // Check if the current role is allowed for this route
        if (!in_array($currentRole, $roles)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Your role (' . strtoupper(str_replace('_', ' ', $currentRole)) . ') does not have permission to access this module.'
                ], 403);
            }

            $readableRole = ucwords(str_replace('_', ' ', $currentRole));
            $allowedRoles = implode(', ', array_map(fn($r) => ucwords(str_replace('_', ' ', $r)), $roles));

            return redirect()->route('dashboard')->with('error', "Access Restricted: You are currently viewing as [{$readableRole}]. This feature requires [{$allowedRoles}] access.");
        }

        return $next($request);
    }
}
