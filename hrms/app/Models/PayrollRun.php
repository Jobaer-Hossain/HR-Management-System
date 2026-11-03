<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'month_year',
        'total_employees',
        'paid_count',
        'unpaid_count',
        'total_payroll',
        'paid_amount',
        'unpaid_amount',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(PayrollItem::class);
    }
}
