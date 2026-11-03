<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->longText('face_descriptor')->nullable()->after('avatar');
            $table->longText('face_snapshot')->nullable()->after('face_descriptor');
            $table->timestamp('face_enrolled_at')->nullable()->after('face_snapshot');
            $table->string('face_biometric_id')->nullable()->after('face_enrolled_at');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['face_descriptor', 'face_snapshot', 'face_enrolled_at', 'face_biometric_id']);
        });
    }
};
