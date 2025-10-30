<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=========================================\n";
echo "CORRIGIENDO MATERIAS Y EVALUACIONES\n";
echo "=========================================\n\n";

// 1. Actualizar nombres de materias
echo "📚 PASO 1: Actualizando nombres de materias...\n\n";

$materias = [
    1 => 'Matemáticas',
    2 => 'Español',
    3 => 'Ciencias Naturales',
    4 => 'Historia',
    5 => 'Geografía',
];

foreach ($materias as $id => $nombre) {
    $subject = \App\Models\Subject::find($id);
    if ($subject) {
        $subject->update(['nombre' => $nombre]);
        echo "   ✅ Materia ID $id → $nombre\n";
    } else {
        // Crear si no existe
        \App\Models\Subject::create([
            'id' => $id,
            'nombre' => $nombre,
            'clave' => strtoupper(substr($nombre, 0, 3)),
            'creditos' => 5,
        ]);
        echo "   ✅ Materia ID $id → $nombre (CREADA)\n";
    }
}

echo "\n📊 PASO 2: Transformando evaluaciones...\n\n";

// 2. Transformar evaluaciones de formato antiguo a nuevo
$grades = \App\Models\Grade::whereNotNull('evaluations')->get();

$transformados = 0;
foreach ($grades as $grade) {
    $evaluations = $grade->evaluations;
    
    if (is_array($evaluations) && !empty($evaluations)) {
        $needsTransform = false;
        
        // Verificar si necesita transformación (tiene "teamwork" en lugar de "P")
        if (isset($evaluations[0]['teamwork'])) {
            $needsTransform = true;
        }
        
        if ($needsTransform) {
            $newEvaluations = [];
            
            foreach ($evaluations as $eval) {
                $newEvaluations[] = [
                    'P' => floatval($eval['teamwork'] ?? $eval['P'] ?? 0),
                    'Pr' => floatval($eval['project'] ?? $eval['Pr'] ?? 0),
                    'A' => floatval($eval['attendance'] ?? $eval['A'] ?? 0),
                    'E' => floatval($eval['exam'] ?? $eval['E'] ?? 0),
                    'Ex' => floatval($eval['extra'] ?? $eval['Ex'] ?? 0),
                    'Prom' => floatval($eval['Prom'] ?? 0),
                ];
            }
            
            $grade->update(['evaluations' => $newEvaluations]);
            $transformados++;
        }
    }
}

echo "   ✅ Transformadas $transformados calificaciones\n";

echo "\n=========================================\n";
echo "✨ CORRECCIÓN COMPLETADA\n";
echo "=========================================\n\n";

echo "📱 Ahora recarga la app y verás:\n";
echo "   1. Nombres de materias correctos\n";
echo "   2. Evaluaciones con valores reales\n";







