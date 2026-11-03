<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('month_year'); // e.g. "January 2026"
            $table->integer('total_employees')->default(0);
            $table->integer('paid_count')->default(0);
            $table->integer('unpaid_count')->default(0);
            $table->decimal('total_payroll', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('unpaid_amount', 15, 2)->default(0.00);
            $table->enum('status', ['draft', 'processing', 'approved', 'completed'])->default('draft');
            $table->timestamps();
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            
            // Bangladesh Standard Structure
            $table->decimal('basic_salary', 12, 2)->default(0.00);
            $table->decimal('house_rent', 12, 2)->default(0.00);
            $table->decimal('medical_allowance', 12, 2)->default(0.00);
            $table->decimal('conveyance', 12, 2)->default(0.00);
            $table->decimal('festival_bonus', 12, 2)->default(0.00);
            $table->decimal('overtime_amount', 12, 2)->default(0.00);
            $table->decimal('gross_earnings', 12, 2)->default(0.00);

            // Deductions
            $table->decimal('tax_deduction', 12, 2)->default(0.00);
            $table->decimal('provident_fund', 12, 2)->default(0.00);
            $table->decimal('unpaid_leave_deduction', 12, 2)->default(0.00);
            $table->decimal('total_deductions', 12, 2)->default(0.00);

            // Net Payable
            $table->decimal('net_salary', 12, 2)->default(0.00);
            $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid');
            $table->string('payment_method')->default('bank_transfer'); // bank_transfer, bKash, cash
            $table->string('transaction_ref')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_runs');
    }
};
