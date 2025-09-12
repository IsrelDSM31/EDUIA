<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->decimal('teamwork', 4, 2)->nullable()->after('date');
            $table->decimal('project', 4, 2)->nullable()->after('teamwork');
            $table->decimal('attendance', 4, 2)->nullable()->after('project');
            $table->decimal('exam', 4, 2)->nullable()->after('attendance');
            $table->decimal('extra', 4, 2)->nullable()->after('exam');
            if (!Schema::hasColumn('grades', 'evaluations')) {
                $table->json('evaluations')->nullable()->after('extra');
            } else {
                $table->json('evaluations')->nullable()->change();
            }
        });
    }

    public function down()
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn(['teamwork', 'project', 'attendance', 'exam', 'extra']);
        });
    }
}; 