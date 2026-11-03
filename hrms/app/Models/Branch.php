<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['tenant_id', 'branch_name', 'branch_code', 'address', 'city', 'status'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
