<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('attendance_policies')) {
            Schema::create('attendance_policies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->default(1);
                $table->string('policy_name')->default('Standard Shift Policy');
                $table->time('check_in_start')->default('07:00:00');
                $table->time('check_in_end')->default('10:30:00'); // Cutoff after which check-in is restricted
                $table->time('check_out_start')->default('16:00:00'); // Earliest normal checkout
                $table->time('check_out_end')->default('23:59:00'); // Latest normal checkout
                $table->unsignedSmallInteger('grace_period_minutes')->default(15);
                $table->boolean('enforce_checkin_cutoff')->default(true);
                $table->boolean('enforce_checkout_cutoff')->default(false);
                $table->string('auto_block_message')->default('Check-in window closed. Please submit a Manual Attendance Request for Branch Manager approval.');
                $table->timestamps();

                $table->index(['tenant_id']);
            });

            // Seed default policy for existing tenants
            DB::table('attendance_policies')->insert([
                [
                    'tenant_id' => 1,
                    'policy_name' => 'Standard Corporate Timing',
                    'check_in_start' => '07:00:00',
                    'check_in_end' => '10:30:00',
                    'check_out_start' => '16:00:00',
                    'check_out_end' => '23:59:00',
                    'grace_period_minutes' => 15,
                    'enforce_checkin_cutoff' => true,
                    'enforce_checkout_cutoff' => false,
                    'auto_block_message' => 'Check-in window closed at 10:30 AM. Direct check-in is blocked. Please submit a Manual Attendance Request for Branch Manager approval.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tenant_id' => 2,
                    'policy_name' => 'Dhaka Enterprise Shift Policy',
                    'check_in_start' => '07:30:00',
                    'check_in_end' => '11:00:00',
                    'check_out_start' => '16:30:00',
                    'check_out_end' => '23:59:00',
                    'grace_period_minutes' => 15,
                    'enforce_checkin_cutoff' => true,
                    'enforce_checkout_cutoff' => false,
                    'auto_block_message' => 'Check-in window closed at 11:00 AM. Please submit a Manual Attendance Request for Branch Manager approval.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_policies');
    }
};
