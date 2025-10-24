<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de puntos de estudiantes
        Schema::create('student_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->integer('total_points')->default(0);
            $table->integer('attendance_points')->default(0);
            $table->integer('grade_points')->default(0);
            $table->integer('participation_points')->default(0);
            $table->integer('achievement_points')->default(0);
            $table->integer('level')->default(1);
            $table->integer('points_to_next_level')->default(100);
            $table->integer('streak_days')->default(0); // Racha de asistencia
            $table->date('last_attendance_date')->nullable();
            $table->timestamps();
            
            $table->unique('student_id');
        });

        // Tabla de logros/achievements
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('icon')->default('trophy');
            $table->string('category'); // attendance, grades, participation, special
            $table->enum('rarity', ['common', 'rare', 'epic', 'legendary'])->default('common');
            $table->integer('points')->default(10);
            $table->json('requirements'); // Condiciones para obtenerlo
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tabla de logros desbloqueados por estudiantes
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('achievement_id')->constrained('achievements')->onDelete('cascade');
            $table->timestamp('unlocked_at');
            $table->timestamps();
            
            $table->unique(['student_id', 'achievement_id']);
        });

        // Tabla de historial de puntos
        Schema::create('points_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->integer('points');
            $table->string('type'); // attendance, grade, participation, achievement
            $table->string('description');
            $table->json('metadata')->nullable(); // Info adicional
            $table->timestamps();
        });

        // Tabla de ranking semanal/mensual
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->enum('period', ['weekly', 'monthly', 'all_time']);
            $table->date('period_start');
            $table->date('period_end')->nullable();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->integer('rank');
            $table->integer('points');
            $table->json('details')->nullable(); // Desglose de puntos
            $table->timestamps();
            
            $table->index(['period', 'period_start', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rankings');
        Schema::dropIfExists('points_history');
        Schema::dropIfExists('student_achievements');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('student_points');
    }
};



