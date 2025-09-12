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
        Schema::table('grades', function (Blueprint $table) {
            $table->date('date')->nullable()->after('score');
            if (!Schema::hasColumn('grades', 'promedio_final')) {
                $table->decimal('promedio_final', 4, 2)->default(0);
            }
            if (!Schema::hasColumn('grades', 'estado')) {
                $table->string('estado')->default('Pendiente');
            }
            if (!Schema::hasColumn('grades', 'puntos_faltantes')) {
                $table->decimal('puntos_faltantes', 4, 2)->default(0);
            }
            if (!Schema::hasColumn('grades', 'evaluations')) {
                $table->json('evaluations')->nullable();
            }
        });

        // Actualizar los registros existentes con la fecha actual
        DB::table('grades')->whereNull('date')->update(['date' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            if (Schema::hasColumn('grades', 'promedio_final')) {
                $table->dropColumn('promedio_final');
            }
            if (Schema::hasColumn('grades', 'estado')) {
                $table->dropColumn('estado');
            }
            if (Schema::hasColumn('grades', 'puntos_faltantes')) {
                $table->dropColumn('puntos_faltantes');
            }
            if (Schema::hasColumn('grades', 'evaluations')) {
                $table->dropColumn('evaluations');
            }
        });
    }
};
