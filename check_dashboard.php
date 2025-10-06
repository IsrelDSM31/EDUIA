<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "VERIFICANDO DATOS DEL DASHBOARD\n\n";

$totalStudents = \App\Models\Student::count();
$totalTeachers = \App\Models\Teacher::count();
$totalAlerts = \App\Models\Alert::count();
$highRisk = \App\Models\StudentRisk::whereIn('risk_level', ['alto', 'high'])->distinct('student_id')->count();

echo "Total Estudiantes: {$totalStudents}\n";
echo "Total Profesores: {$totalTeachers}\n";
echo "Total Alertas: {$totalAlerts}\n";
echo "Riesgo Alto: {$highRisk}\n\n";

if ($totalAlerts == 0) {
    echo "⚠️  NO HAY ALERTAS - Creando alertas de ejemplo...\n\n";
    
    $studentsAtRisk = \App\Models\StudentRisk::whereIn('risk_level', ['alto', 'high'])
        ->with('student')
        ->take(5)
        ->get();
    
    foreach ($studentsAtRisk as $risk) {
        if ($risk->student) {
            \App\Models\Alert::create([
                'student_id' => $risk->student->id,
                'type' => 'academic',
                'severity' => 'high',
                'title' => 'Estudiante en Riesgo Alto',
                'message' => 'El estudiante ' . $risk->student->nombre . ' requiere atención inmediata.',
                'is_read' => false,
            ]);
            echo "✅ Alerta creada para: {$risk->student->nombre}\n";
        }
    }
    
    $totalAlerts = \App\Models\Alert::count();
    echo "\nTotal de alertas ahora: {$totalAlerts}\n";
}

echo "\n✅ DATOS ACTUALIZADOS\n";



