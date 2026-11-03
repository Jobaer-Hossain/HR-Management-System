<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'payroll_run_id',
        'employee_id',
        'basic_salary',
        'house_rent',
        'medical_allowance',
        'conveyance',
        'festival_bonus',
        'overtime_amount',
        'gross_earnings',
        'tax_deduction',
        'provident_fund',
        'unpaid_leave_deduction',
        'total_deductions',
        'net_salary',
        'payment_status',
        'payment_method',
        'transaction_ref',
        'paid_at',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function run()
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }
}
