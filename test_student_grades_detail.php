<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=========================================\n";
echo "PROBANDO CALIFICACIONES DE JUAN GARCÍA\n";
echo "=========================================\n\n";

$student = \App\Models\Student::where('matricula', '1000001')->first();

if (!$student) {
    echo "❌ No se encontró al estudiante.\n";
    exit;
}

echo "👨‍🎓 ESTUDIANTE: {$student->nombre} {$student->apellido_paterno}\n";
echo "   Matrícula: {$student->matricula}\n\n";

// Obtener calificaciones
$grades = \App\Models\Grade::where('student_id', $student->id)
    ->with('subject')
    ->get();

echo "📊 CALIFICACIONES ENCONTRADAS: {$grades->count()}\n\n";

foreach ($grades as $grade) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📚 MATERIA: ";
    if ($grade->subject) {
        echo $grade->subject->nombre . " (ID: {$grade->subject->id})\n";
    } else {
        echo "⚠️  SIN NOMBRE - Subject ID: {$grade->subject_id}\n";
    }
    echo "   Promedio Final: {$grade->promedio_final}\n";
    echo "   Estado: {$grade->estado}\n";
    echo "\n";
    
    echo "   📝 EVALUATIONS (JSON):\n";
    if ($grade->evaluations) {
        echo "   " . json_encode($grade->evaluations, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "   ⚠️  NULL - No hay evaluaciones\n";
    }
    echo "\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔍 VERIFICANDO TABLA SUBJECTS:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$subjects = \App\Models\Subject::take(10)->get();

if ($subjects->isEmpty()) {
    echo "❌ NO HAY MATERIAS EN LA BASE DE DATOS!\n";
    echo "\n🔧 CREANDO MATERIAS DE EJEMPLO...\n\n";
    
    $materiasEjemplo = [
        'Matemáticas',
        'Español',
        'Ciencias Naturales',
        'Historia',
        'Geografía',
        'Inglés',
        'Educación Física',
        'Arte',
    ];
    
    foreach ($materiasEjemplo as $nombre) {
        $subject = \App\Models\Subject::create([
            'nombre' => $nombre,
            'clave' => strtoupper(substr($nombre, 0, 3)),
            'creditos' => 5,
        ]);
        echo "   ✅ Creada: {$nombre} (ID: {$subject->id})\n";
    }
} else {
    echo "✅ MATERIAS DISPONIBLES:\n";
    foreach ($subjects as $subject) {
        echo "   - {$subject->nombre} (ID: {$subject->id})\n";
    }
}

echo "\n=========================================\n";
echo "✨ PRUEBA COMPLETADA\n";
echo "=========================================\n";



