<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Verificando estudiante 1000036 en API...\n\n";

$student = \App\Models\Student::where('matricula', '1000036')->first();

if (!$student) {
    echo "❌ Estudiante NO existe\n";
    exit;
}

echo "✅ Estudiante: {$student->nombre} (ID: {$student->id})\n";

$risk = \App\Models\StudentRisk::where('student_id', $student->id)->first();

if (!$risk) {
    echo "❌ NO tiene registro de riesgo\n";
    exit;
}

echo "✅ Registro de riesgo: ID {$risk->id}, Nivel: {$risk->risk_level}\n\n";

echo "Simulando respuesta de API:\n";

$result = [
    'id' => $risk->id,
    'student_id' => $risk->student_id,
    'student_name' => trim($student->nombre . ' ' . $student->apellido_paterno . ' ' . $student->apellido_materno),
    'student_code' => $student->matricula,
    'risk_level' => $risk->risk_level,
    'risk_score' => $risk->risk_score ?? 0,
    'risk_factors' => $risk->risk_factors ? json_decode($risk->risk_factors) : [],
    'recommendations' => $risk->recommendations,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

echo "\n\n✅ Este estudiante DEBERÍA aparecer en la app\n";







