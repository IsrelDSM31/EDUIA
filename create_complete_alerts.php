<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$studentsAtRisk = \App\Models\StudentRisk::whereIn('risk_level', ['alto', 'high'])
    ->with('student')->take(10)->get();

$created = 0;
foreach ($studentsAtRisk as $risk) {
    if ($risk->student) {
        $alert = new \App\Models\Alert();
        $alert->student_id = $risk->student->id;
        $alert->type = 'academic_risk';
        $alert->title = 'Riesgo Alto Detectado';
        $alert->description = 'El estudiante requiere intervención inmediata';
        $alert->urgency = 'high';
        $alert->evidence = json_encode(['risk_level' => 'alto']);
        $alert->suggested_actions = json_encode(['Tutoría', 'Reunión padres']);
        $alert->intervention_plan = json_encode(['plan' => 'Intervención urgente']);
        $alert->save();
        $created++;
        echo ".";
    }
}
echo "\nCreadas: {$created}\n";







