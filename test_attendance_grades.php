<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=========================================\n";
echo "PROBANDO ASISTENCIAS Y CALIFICACIONES\n";
echo "=========================================\n\n";

// Probar Asistencias
echo "📅 ASISTENCIAS:\n";
echo "-----------------\n";
$attendances = \App\Models\Attendance::with(['student', 'student.group'])->take(5)->get();

if ($attendances->isEmpty()) {
    echo "❌ No hay registros de asistencia en la base de datos.\n";
} else {
    foreach ($attendances as $attendance) {
        $studentName = $attendance->student 
            ? trim($attendance->student->nombre . ' ' . $attendance->student->apellido_paterno . ' ' . $attendance->student->apellido_materno)
            : 'Estudiante';
        
        echo "✅ ID: {$attendance->id}\n";
        echo "   Estudiante: {$studentName}\n";
        echo "   Grupo: " . ($attendance->student->group->name ?? 'Sin grupo') . "\n";
        echo "   Estado: {$attendance->estado}\n";
        echo "   Fecha: {$attendance->fecha}\n\n";
    }
}

echo "\n📊 CALIFICACIONES:\n";
echo "-----------------\n";
$grades = \App\Models\Grade::with(['student', 'subject'])->take(5)->get();

if ($grades->isEmpty()) {
    echo "❌ No hay calificaciones en la base de datos.\n";
} else {
    foreach ($grades as $grade) {
        $studentName = $grade->student 
            ? trim($grade->student->nombre . ' ' . $grade->student->apellido_paterno . ' ' . $grade->student->apellido_materno)
            : 'Estudiante';
        
        echo "✅ ID: {$grade->id}\n";
        echo "   Estudiante: {$studentName}\n";
        echo "   Materia: " . ($grade->subject->nombre ?? 'Materia') . "\n";
        echo "   Calificación: {$grade->promedio_final}\n";
        echo "   Estado: " . ($grade->estado ?? 'N/A') . "\n\n";
    }
}

echo "=========================================\n";
echo "✨ PRUEBA COMPLETADA\n";
echo "=========================================\n";







