<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use Illuminate\Support\Facades\DB;

echo "===========================================\n";
echo "Inicializando Puntos para Estudiantes\n";
echo "===========================================\n\n";

$students = Student::all();
$initialized = 0;

foreach ($students as $student) {
    // Verificar si ya tiene puntos
    $exists = DB::table('student_points')
        ->where('student_id', $student->id)
        ->exists();

    if (!$exists) {
        DB::table('student_points')->insert([
            'student_id' => $student->id,
            'total_points' => 0,
            'attendance_points' => 0,
            'grade_points' => 0,
            'participation_points' => 0,
            'achievement_points' => 0,
            'level' => 1,
            'points_to_next_level' => 100,
            'streak_days' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $initialized++;
        echo "✅ Puntos inicializados para: {$student->nombre} {$student->apellido_paterno} (ID: {$student->id})\n";
    }
}

echo "\n===========================================\n";
echo "✨ Proceso completado!\n";
echo "===========================================\n";
echo "Estudiantes inicializados: {$initialized}\n";
echo "Total de estudiantes: " . $students->count() . "\n\n";

// Calcular puntos iniciales basados en datos existentes
echo "Calculando puntos iniciales basados en datos...\n\n";

foreach ($students as $student) {
    $points = 0;
    
    // Puntos por asistencia (1 punto por asistencia)
    $attendances = DB::table('attendances')
        ->where('student_id', $student->id)
        ->whereIn('status', ['present', 'presente'])
        ->count();
    $attendancePoints = $attendances * 1;
    
    // Puntos por calificaciones (promedio * 5)
    $avgGrade = DB::table('grades')
        ->where('student_id', $student->id)
        ->avg('promedio_final');
    $gradePoints = $avgGrade ? round($avgGrade * 5) : 0;
    
    // Actualizar puntos
    if ($attendancePoints > 0 || $gradePoints > 0) {
        $totalPoints = $attendancePoints + $gradePoints;
        $level = floor($totalPoints / 100) + 1;
        
        DB::table('student_points')
            ->where('student_id', $student->id)
            ->update([
                'total_points' => $totalPoints,
                'attendance_points' => $attendancePoints,
                'grade_points' => $gradePoints,
                'level' => $level,
                'updated_at' => now(),
            ]);
        
        echo "📊 {$student->nombre}: {$totalPoints} pts (Asistencia: {$attendancePoints}, Calificaciones: {$gradePoints}) - Nivel {$level}\n";
    }
}

echo "\n===========================================\n";
echo "🎉 ¡Gamificación inicializada exitosamente!\n";
echo "===========================================\n";







