<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=========================================\n";
echo "VERIFICANDO RESPUESTA DE API\n";
echo "=========================================\n\n";

$student = \App\Models\Student::where('matricula', '1000001')->first();

if (!$student) {
    echo "❌ Estudiante no encontrado\n";
    exit;
}

echo "👨‍🎓 ESTUDIANTE: {$student->nombre} {$student->apellido_paterno}\n\n";

// Simular lo que hace el controlador
$subjects = \App\Models\Subject::all();
$gradesBySubject = [];

foreach ($subjects as $subject) {
    $grades = \App\Models\Grade::where('student_id', $student->id)
                      ->where('subject_id', $subject->id)
                      ->get();

    if ($grades->isNotEmpty()) {
        $firstGrade = $grades->first();
        
        echo "✅ MATERIA ID {$subject->id}: {$subject->name}\n";
        echo "   Promedio: {$firstGrade->promedio_final}\n";
        echo "   Estado: {$firstGrade->estado}\n\n";
        
        if (count($gradesBySubject) >= 5) {
            break; // Solo mostrar 5
        }
        
        $gradesBySubject[$subject->id] = [
            'subject_name' => $subject->name,
            'score' => $firstGrade->promedio_final,
        ];
    }
}

echo "\n📱 RESPUESTA QUE VERÁ LA APP:\n";
echo json_encode(['grades_by_subject' => $gradesBySubject], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

echo "\n\n=========================================\n";
echo "✨ VERIFICACIÓN COMPLETADA\n";
echo "=========================================\n";







