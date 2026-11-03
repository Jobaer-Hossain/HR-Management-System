<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $tenantId = DB::table('tenants')->orderBy('id')->value('id');
        if (!$tenantId) {
            return;
        }

        $now = now();
        DB::table('users')->updateOrInsert(
            ['email' => 'superadmin@hrms.com'],
            [
                'tenant_id' => $tenantId,
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'status' => 'active',
                'avatar' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'superadmin@hrms.com')->delete();
    }
};
