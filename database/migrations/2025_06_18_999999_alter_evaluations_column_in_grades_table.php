<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('grades', function (Blueprint $table) {
            if (!Schema::hasColumn('grades', 'evaluations')) {
                $table->json('evaluations')->nullable();
            } else {
                $table->json('evaluations')->nullable()->change();
            }
        });
    }

    public function down()
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->text('evaluations')->nullable()->change();
        });
    }
}; 