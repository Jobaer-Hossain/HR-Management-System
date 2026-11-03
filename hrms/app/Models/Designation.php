<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;

    protected $fillable = ['tenant_id', 'designation_name', 'designation_level', 'status'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
