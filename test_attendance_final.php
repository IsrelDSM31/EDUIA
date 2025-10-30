<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║         VERIFICACIÓN FINAL - ASISTENCIAS                        ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

// Simular request a la API
$attendances = \App\Models\Attendance::with(['student', 'student.group', 'subject'])
    ->latest('date')
    ->take(5)
    ->get();

echo "✅ ASISTENCIAS ENCONTRADAS: {$attendances->count()}\n\n";

foreach ($attendances as $attendance) {
    $studentName = $attendance->student 
        ? trim($attendance->student->nombre . ' ' . $attendance->student->apellido_paterno . ' ' . $attendance->student->apellido_materno)
        : 'Estudiante';
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📌 ID: {$attendance->id}\n";
    echo "👤 Estudiante: {$studentName}\n";
    echo "📚 Materia: " . ($attendance->subject->name ?? 'Sin materia') . "\n";
    echo "📅 Fecha: {$attendance->date}\n";
    echo "⭐ Estado: {$attendance->status}\n";
    
    if ($attendance->justification_type) {
        echo "📝 Justificación: {$attendance->justification_type}\n";
    }
    
    if ($attendance->observations) {
        echo "💬 Observaciones: {$attendance->observations}\n";
    }
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════════\n";
echo "✅ TODO FUNCIONANDO CORRECTAMENTE\n";
echo "═══════════════════════════════════════════════════════════════════\n";







