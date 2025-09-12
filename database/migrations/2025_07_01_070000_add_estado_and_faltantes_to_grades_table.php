<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->string('estado')->nullable()->after('evaluation_date');
            $table->decimal('faltantes', 5, 2)->nullable()->after('estado');
        });
    }

    public function down()
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('estado');
            $table->dropColumn('faltantes');
        });
    }
}; 