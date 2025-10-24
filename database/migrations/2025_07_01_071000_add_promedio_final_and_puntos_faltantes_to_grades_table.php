<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('grades', function (Blueprint $table) {
            // $table->decimal('promedio_final', 5, 2)->nullable()->after('score');
            // $table->decimal('puntos_faltantes', 5, 2)->nullable()->after('promedio_final');
        });
    }

    public function down()
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('promedio_final');
            $table->dropColumn('puntos_faltantes');
        });
    }
}; 