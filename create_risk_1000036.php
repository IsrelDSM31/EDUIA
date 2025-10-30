<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$student = \App\Models\Student::where('matricula', '1000036')->first();

if (!$student) {
    echo "Estudiante no encontrado\n";
    exit;
}

echo "Estudiante: {$student->nombre} (ID: {$student->id})\n";

// Eliminar registro incorrecto si existe
\App\Models\StudentRisk::where('student_id', $student->id)->delete();

// Crear registro correcto
$risk = \App\Models\StudentRisk::create([
    'student_id' => $student->id,
    'risk_level' => 'bajo',
    'risk_score' => 0,
    'performance_metrics' => [
        'attendance_rate' => 100,
        'grade_average' => 0,
        'failed_subjects' => 0,
        'recent_improvement' => 0
    ],
    'intervention_recommendations' => [
        [
            'type' => 'monitoring',
            'priority' => 'low',
            'message' => 'Estudiante nuevo. Establecer línea base de rendimiento y asistencia.'
        ]
    ],
    'notes' => 'Estudiante recién inscrito',
]);

echo "✅ Registro de riesgo creado: ID {$risk->id}\n";
echo "Nivel: {$risk->risk_level}\n";







