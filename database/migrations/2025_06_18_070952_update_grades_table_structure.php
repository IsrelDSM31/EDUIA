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
            $table->date('evaluation_date')->nullable();
        });

        // Copiar datos de 'date' a 'evaluation_date'
        DB::statement('UPDATE grades SET evaluation_date = date');

        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->date('date')->nullable();
        });

        // Copiar datos de 'evaluation_date' a 'date'
        DB::statement('UPDATE grades SET date = evaluation_date');

        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('evaluation_date');
        });
    }
};
