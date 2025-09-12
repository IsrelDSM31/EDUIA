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
        Schema::create('student_risks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->float('risk_score')->default(0);
            $table->string('risk_level')->default('bajo'); // bajo, medio, alto
            $table->json('performance_metrics')->nullable();
            $table->json('behavior_patterns')->nullable();
            $table->json('intervention_recommendations')->nullable();
            $table->json('progress_metrics')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_risks');
    }
};
