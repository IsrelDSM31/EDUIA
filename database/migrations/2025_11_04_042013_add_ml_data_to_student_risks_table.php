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
        Schema::table('student_risks', function (Blueprint $table) {
            $table->json('ml_data')->nullable()->after('progress_metrics');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_risks', function (Blueprint $table) {
            $table->dropColumn('ml_data');
        });
    }
};
