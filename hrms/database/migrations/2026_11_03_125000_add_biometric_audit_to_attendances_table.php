<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('biometric_similarity', 6, 5)->nullable()->after('source');
            $table->string('biometric_engine')->nullable()->after('biometric_similarity');
            $table->unsignedTinyInteger('biometric_frames')->nullable()->after('biometric_engine');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['biometric_similarity', 'biometric_engine', 'biometric_frames']);
        });
    }
};
