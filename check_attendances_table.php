<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=========================================\n";
echo "VERIFICANDO TABLA ATTENDANCES\n";
echo "=========================================\n\n";

echo "📋 COLUMNAS DE LA TABLA:\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('attendances');
print_r($columns);

echo "\n\n📊 PRIMER REGISTRO:\n";
$attendance = \App\Models\Attendance::first();
if ($attendance) {
    print_r($attendance->toArray());
} else {
    echo "❌ No hay registros de asistencia\n";
}

echo "\n=========================================\n";







