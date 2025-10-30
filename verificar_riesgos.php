<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StudentRisk;
use App\Models\Student;

echo "===========================================\n";
echo "Verificando Datos de Riesgo\n";
echo "===========================================\n\n";

echo "Total de estudiantes: " . Student::count() . "\n";
echo "Total de registros de riesgo: " . StudentRisk::count() . "\n\n";

echo "Distribución por nivel:\n";
echo "- Alto:  " . StudentRisk::whereIn('risk_level', ['alto', 'high'])->count() . "\n";
echo "- Medio: " . StudentRisk::whereIn('risk_level', ['medio', 'medium'])->count() . "\n";
echo "- Bajo:  " . StudentRisk::whereIn('risk_level', ['bajo', 'low'])->count() . "\n\n";

$risks = StudentRisk::with('student.group')->take(5)->get();

echo "Primeros 5 registros de riesgo:\n\n";

foreach($risks as $risk) {
    $student = $risk->student;
    echo "ID: " . $risk->id . "\n";
    echo "Estudiante: " . ($student ? trim($student->nombre . ' ' . $student->apellido_paterno . ' ' . $student->apellido_materno) : 'N/A') . "\n";
    echo "Matrícula: " . ($student->matricula ?? 'N/A') . "\n";
    echo "Nivel de riesgo: " . $risk->risk_level . "\n";
    echo "Score: " . ($risk->risk_score ?? 'N/A') . "\n";
    echo "Recomendaciones: " . ($risk->recommendations ?? 'Sin recomendaciones') . "\n";
    echo "---\n";
}

echo "\n===========================================\n";







