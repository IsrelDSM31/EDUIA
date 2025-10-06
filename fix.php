<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$risk = \App\Models\StudentRisk::find(36);
$risk->risk_factors = json_encode(['Estudiante nuevo sin historial']);
$risk->recommendations = 'Seguimiento regular. Establecer linea base de rendimiento y asistencia.';
$risk->save();
echo "OK\n";


