<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Estructura de tabla alerts:\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('alerts');
print_r($columns);

echo "\n\nPrimer registro (si existe):\n";
$alert = \App\Models\Alert::first();
if ($alert) {
    print_r($alert->toArray());
} else {
    echo "No hay alertas\n\n";
    echo "Creando alertas de ejemplo...\n\n";
    
    $students = \App\Models\Student::take(3)->get();
    foreach ($students as $student) {
        $alert = \App\Models\Alert::create([
            'student_id' => $student->id,
            'type' => 'Académico',
            'message' => 'Alerta de ejemplo para ' . $student->nombre,
        ]);
        echo "Alerta creada: ID {$alert->id}\n";
    }
}



