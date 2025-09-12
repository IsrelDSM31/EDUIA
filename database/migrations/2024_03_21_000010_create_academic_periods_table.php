<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcademicPeriodsTable extends Migration
{
    public function up()
    {
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('type');
            $table->json('important_events')->nullable();
            $table->json('evaluation_parameters');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('academic_periods');
    }
} 