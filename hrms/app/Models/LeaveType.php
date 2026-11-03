<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'icon',
        'badge_color',
        'days_allowed',
        'is_paid',
    ];

    public function applications()
    {
        return $this->hasMany(LeaveApplication::class);
    }
}
