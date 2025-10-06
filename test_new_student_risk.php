<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=========================================\n";
echo "PROBANDO ESTUDIANTES SIN RIESGO\n";
echo "=========================================\n\n";

// 1. Contar estudiantes totales
$totalStudents = \App\Models\Student::count();
echo "📊 TOTAL DE ESTUDIANTES: {$totalStudents}\n\n";

// 2. Contar estudiantes con registro de riesgo
$studentsWithRisk = \App\Models\StudentRisk::distinct('student_id')->count('student_id');
echo "🧠 ESTUDIANTES CON ANÁLISIS DE RIESGO: {$studentsWithRisk}\n\n";

// 3. Encontrar estudiantes sin registro de riesgo
$studentsWithoutRisk = \App\Models\Student::whereDoesntHave('studentRisks')->get();
echo "⚠️  ESTUDIANTES SIN REGISTRO DE RIESGO: {$studentsWithoutRisk->count()}\n\n";

if ($studentsWithoutRisk->count() > 0) {
    echo "Creando registros de riesgo para estudiantes nuevos...\n\n";
    
    foreach ($studentsWithoutRisk as $student) {
        \App\Models\StudentRisk::create([
            'student_id' => $student->id,
            'risk_level' => 'bajo',
            'risk_score' => 0,
            'risk_factors' => json_encode(['Estudiante nuevo sin historial']),
            'recommendations' => 'Seguimiento regular. Establecer línea base de rendimiento y asistencia.',
        ]);
        
        echo "✅ Registro de riesgo creado para: {$student->nombre} {$student->apellido_paterno} (ID: {$student->id})\n";
    }
}

echo "\n=========================================\n";
echo "✨ VERIFICACIÓN FINAL\n";
echo "=========================================\n\n";

$finalCount = \App\Models\StudentRisk::distinct('student_id')->count('student_id');
echo "🎯 ESTUDIANTES CON ANÁLISIS: {$finalCount} / {$totalStudents}\n\n";

if ($finalCount == $totalStudents) {
    echo "✅ TODOS LOS ESTUDIANTES TIENEN ANÁLISIS DE RIESGO!\n";
} else {
    echo "⚠️  Aún faltan " . ($totalStudents - $finalCount) . " estudiantes\n";
}

echo "\n=========================================\n";



