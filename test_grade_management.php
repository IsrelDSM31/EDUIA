<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=========================================\n";
echo "PROBANDO SISTEMA DE CALIFICACIONES\n";
echo "=========================================\n\n";

// Obtener primer estudiante
$student = \App\Models\Student::first();

if (!$student) {
    echo "❌ No hay estudiantes en la base de datos.\n";
    exit;
}

echo "👨‍🎓 ESTUDIANTE: " . $student->nombre . " " . $student->apellido_paterno . "\n";
echo "   Matrícula: {$student->matricula}\n\n";

// Obtener materias
$subjects = \App\Models\Subject::take(3)->get();

echo "📚 MATERIAS DISPONIBLES:\n";
foreach ($subjects as $subject) {
    echo "   - {$subject->nombre} (ID: {$subject->id})\n";
}
echo "\n";

// Crear calificaciones de ejemplo
echo "✏️  CREANDO CALIFICACIONES DE EJEMPLO...\n\n";

foreach ($subjects as $subject) {
    // Verificar si ya existe
    $existingGrade = \App\Models\Grade::where('student_id', $student->id)
        ->where('subject_id', $subject->id)
        ->first();
    
    if ($existingGrade) {
        echo "   ✅ Ya existe calificación para {$subject->nombre}\n";
        echo "      Promedio: {$existingGrade->promedio_final}\n";
        echo "      Estado: {$existingGrade->estado}\n\n";
        continue;
    }
    
    // Crear evaluaciones de ejemplo
    $evaluations = [
        ['P' => 8, 'Pr' => 9, 'A' => 10, 'E' => 7, 'Ex' => 0, 'Prom' => 6.8],
        ['P' => 7, 'Pr' => 8, 'A' => 9, 'E' => 8, 'Ex' => 0, 'Prom' => 6.4],
        ['P' => 9, 'Pr' => 9, 'A' => 10, 'E' => 9, 'Ex' => 0, 'Prom' => 7.4],
        ['P' => 8, 'Pr' => 8, 'A' => 10, 'E' => 8, 'Ex' => 0, 'Prom' => 6.8],
    ];
    
    $promedioFinal = (6.8 + 6.4 + 7.4 + 6.8) / 4;
    $estado = $promedioFinal >= 7 ? 'Aprobado' : ($promedioFinal >= 5 ? 'Riesgo' : 'Reprobado');
    
    $grade = \App\Models\Grade::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'evaluations' => $evaluations,
        'promedio_final' => $promedioFinal,
        'estado' => $estado,
        'faltantes' => 0,
        'puntos_faltantes' => $promedioFinal < 7 ? (7 - $promedioFinal) : 0,
    ]);
    
    echo "   ✅ Calificación creada para {$subject->nombre}\n";
    echo "      Promedio: " . number_format($promedioFinal, 2) . "\n";
    echo "      Estado: {$estado}\n\n";
}

echo "=========================================\n";
echo "✨ PRUEBA COMPLETADA\n";
echo "=========================================\n\n";

echo "📱 Ahora puedes probar en la app:\n";
echo "   1. Ve a Calificaciones\n";
echo "   2. Busca a: {$student->nombre} {$student->apellido_paterno}\n";
echo "   3. Presiona sobre el estudiante\n";
echo "   4. Verás todas sus materias con calificaciones\n";







