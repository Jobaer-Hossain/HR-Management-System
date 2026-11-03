<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('face_engine')->nullable()->after('face_biometric_id');
            $table->decimal('face_quality', 5, 3)->nullable()->after('face_engine');
            $table->decimal('face_consistency', 5, 3)->nullable()->after('face_quality');
            $table->unsignedTinyInteger('face_enrollment_frames')->nullable()->after('face_consistency');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['face_engine', 'face_quality', 'face_consistency', 'face_enrollment_frames']);
        });
    }
};
