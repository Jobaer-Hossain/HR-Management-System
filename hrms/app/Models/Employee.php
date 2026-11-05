<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'department_id',
        'designation_id',
        'employee_code',
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'avatar',
        'gender',
        'date_of_birth',
        'joining_date',
        'employment_type',
        'status',
        'salary',
        'face_descriptor',
        'face_snapshot',
        'face_enrolled_at',
        'face_biometric_id',
        'face_engine',
        'face_quality',
        'face_consistency',
        'face_enrollment_frames',
    ];

    protected $casts = [
        'face_descriptor' => \App\Casts\EncryptedArray::class,
        'face_enrolled_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function hasReliableFaceBiometric(): bool
    {
        return $this->face_engine === 'scrfd-arcface-onnx'
            && is_array($this->face_descriptor)
            && count($this->face_descriptor) === 512;
    }

    public function leaves()
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function shiftSchedules()
    {
        return $this->hasMany(ShiftSchedule::class);
    }

    public function payrollItems()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function manualAttendanceRequests()
    {
        return $this->hasMany(ManualAttendanceRequest::class);
    }
}
