<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isHrManager(): bool
    {
        return in_array($this->role, ['hr_manager', 'super_admin', 'owner', 'admin']);
    }

    public function isDepartmentManager(): bool
    {
        return in_array($this->role, ['department_manager', 'line_manager', 'team_lead']);
    }

    public function isFinance(): bool
    {
        return in_array($this->role, ['finance', 'finance_manager', 'payroll_officer']);
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }
}
