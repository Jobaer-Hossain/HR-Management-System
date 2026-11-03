<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('shift_name');
            $table->string('role_title')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('break_minutes')->default(30);
            $table->string('color')->default('blue');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('shift_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->date('date');
            $table->string('day_name')->nullable(); // Sat, Sun, Mon, etc.
            $table->string('time_slot'); // e.g. "8:30 a - 3:30 p"
            $table->string('role_title')->nullable(); // Clerk, Manager, Cashier
            $table->integer('break_minutes')->default(30);
            $table->string('color')->default('blue'); // blue, yellow, green, purple, red
            $table->string('status')->default('scheduled');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_schedules');
        Schema::dropIfExists('shifts');
    }
};
