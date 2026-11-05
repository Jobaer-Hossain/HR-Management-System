<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('manual_attendance_requests')) {
            Schema::create('manual_attendance_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->default(1);
                $table->unsignedBigInteger('employee_id');
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('department_id')->nullable();
                $table->date('attendance_date');
                $table->enum('punch_type', ['check_in', 'check_out', 'both'])->default('check_in');
                $table->time('requested_check_in')->nullable();
                $table->time('requested_check_out')->nullable();
                $table->text('reason');
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->string('reviewed_by_name')->nullable();
                $table->text('manager_remarks')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'employee_id']);
                $table->index(['tenant_id', 'status']);
                $table->index(['branch_id']);
                $table->index(['department_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_attendance_requests');
    }
};
