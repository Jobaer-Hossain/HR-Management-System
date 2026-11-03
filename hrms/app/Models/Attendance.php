<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'attendance_date',
        'check_in',
        'check_out',
        'break_start',
        'break_end',
        'worked_minutes',
        'overtime_minutes',
        'status',
        'location_note',
        'source',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
