<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Buscando estudiante 1000036...\n";

$student = \App\Models\Student::where('matricula', '1000036')->first();

if (!$student) {
    echo "❌ Estudiante NO encontrado\n";
    exit;
}

echo "✅ Estudiante encontrado: {$student->id} - {$student->nombre} {$student->apellido_paterno}\n\n";

$risk = \App\Models\StudentRisk::where('student_id', $student->id)->first();

if ($risk) {
    echo "✅ YA tiene registro de riesgo\n";
    echo "   Nivel: {$risk->risk_level}\n";
    echo "   ID: {$risk->id}\n";
} else {
    echo "⚠️  NO tiene registro de riesgo - CREANDO...\n";
    
    $newRisk = \App\Models\StudentRisk::create([
        'student_id' => $student->id,
        'risk_level' => 'bajo',
        'risk_score' => 0,
        'risk_factors' => json_encode(['Estudiante nuevo sin historial']),
        'recommendations' => 'Seguimiento regular. Establecer línea base de rendimiento y asistencia.',
    ]);
    
    echo "✅ Registro creado exitosamente\n";
    echo "   ID: {$newRisk->id}\n";
    echo "   Nivel: {$newRisk->risk_level}\n";
}

echo "\n✅ LISTO - Ahora aparecerá en el análisis de riesgo\n";



