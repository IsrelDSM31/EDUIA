<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Creando alertas para estudiantes en riesgo...\n\n";

$studentsAtRisk = \App\Models\StudentRisk::whereIn('risk_level', ['alto', 'high'])
    ->with('student')
    ->take(10)
    ->get();

$created = 0;
foreach ($studentsAtRisk as $risk) {
    if ($risk->student) {
        try {
            $alert = new \App\Models\Alert();
            $alert->student_id = $risk->student->id;
            $alert->type = 'academic_risk';
            $alert->title = 'Estudiante en Riesgo Alto';
            $alert->description = 'El estudiante ' . $risk->student->nombre . ' ' . $risk->student->apellido_paterno . ' requiere atención inmediata debido a su nivel de riesgo alto.';
            $alert->urgency = 'high';
            $alert->evidence = json_encode(['risk_level' => 'alto', 'score' => $risk->risk_score]);
            $alert->suggested_actions = json_encode(['Tutoría inmediata', 'Reunión con padres', 'Plan de recuperación']);
            $alert->intervention_plan = 'Plan de intervención urgente';
            $alert->save();
            
            $created++;
            echo "✅ Alerta #{$created} creada para: {$risk->student->nombre}\n";
        } catch (\Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n✅ Total de alertas creadas: {$created}\n";







