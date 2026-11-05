<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualAttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'branch_id',
        'department_id',
        'attendance_date',
        'punch_type',
        'requested_check_in',
        'requested_check_out',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_by_name',
        'manager_remarks',
        'reviewed_at',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
