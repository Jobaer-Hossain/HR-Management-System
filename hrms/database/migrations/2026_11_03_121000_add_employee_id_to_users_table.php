<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('tenant_id')->constrained('employees')->nullOnDelete();
        });

        DB::table('users')->whereNotNull('email')->orderBy('id')->each(function ($user) {
            $employeeId = DB::table('employees')->where('tenant_id', $user->tenant_id)->where('email', $user->email)->value('id');
            if ($employeeId) {
                DB::table('users')->where('id', $user->id)->update(['employee_id' => $employeeId]);
            }
        });

        // Keep the four legacy demo accounts linked to the profiles used by the login demo.
        foreach ([
            'admin@hrms.com' => 1,
            'manager@hrms.com' => 2,
            'finance@hrms.com' => 4,
            'employee@hrms.com' => 1,
        ] as $email => $employeeId) {
            DB::table('users')->where('email', $email)->whereNull('employee_id')->update(['employee_id' => $employeeId]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
        });
    }
};
