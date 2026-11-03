<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(\Illuminate\Http\Request $request, $user)
    {
        session(['active_role' => $user->role]);
        session(['current_tenant_id' => $user->tenant_id ?? 1]);

        $employee = $user->employee
            ?? \App\Models\Employee::where('email', $user->email)->first();
        if ($employee) {
            session(['portal_employee_id' => $employee->id]);
        }

        if (in_array($user->role, ['super_admin', 'admin', 'owner', 'hr_manager'], true)) {
            if ($user->role === 'super_admin') {
                return redirect()->route('dashboard.superadmin');
            }
            return redirect()->route('dashboard.hr');
        } elseif ($user->role === 'department_manager') {
            return redirect()->route('dashboard.manager');
        } elseif ($user->role === 'finance') {
            return redirect()->route('dashboard.finance');
        } else {
            return redirect()->route('dashboard.employee');
        }
    }
}
