<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Creando alertas simples...\n\n";

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
            $alert->title = 'Riesgo Alto';
            $alert->description = 'Requiere atención inmediata';
            $alert->urgency = 'high';
            $alert->save();
            
            $created++;
            echo "✅ {$created}. {$risk->student->nombre}\n";
        } catch (\Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            break;
        }
    }
}

echo "\nTotal: {$created}\n";







