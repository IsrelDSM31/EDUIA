<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvaluationsToGradesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->json('evaluations')->nullable()->after('score');
            $table->decimal('promedio_final', 4, 2)->nullable()->after('evaluations');
            $table->string('estado')->default('Reprobado')->after('promedio_final');
            $table->decimal('puntos_faltantes', 4, 2)->default(7.00)->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn(['evaluations', 'promedio_final', 'estado', 'puntos_faltantes']);
        });
    }
}
