<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['tenant_id', 'department_name', 'department_code', 'status'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function getNameAttribute()
    {
        return $this->attributes['department_name'] ?? ($this->attributes['name'] ?? 'General');
    }
}
