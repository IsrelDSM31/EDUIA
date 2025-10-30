<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=========================================\n";
echo "RECALCULANDO TODAS LAS CALIFICACIONES\n";
echo "=========================================\n\n";

$grades = \App\Models\Grade::whereNotNull('evaluations')->get();

$updated = 0;
foreach ($grades as $grade) {
    $evaluations = $grade->evaluations;
    
    if (is_array($evaluations) && !empty($evaluations)) {
        $newEvaluations = [];
        $validProms = [];
        
        foreach ($evaluations as $eval) {
            // Obtener valores (puede estar en formato antiguo o nuevo)
            $P = floatval($eval['P'] ?? $eval['teamwork'] ?? 0);
            $Pr = floatval($eval['Pr'] ?? $eval['project'] ?? 0);
            $A = floatval($eval['A'] ?? $eval['attendance'] ?? 0);
            $E = floatval($eval['E'] ?? $eval['exam'] ?? 0);
            $Ex = floatval($eval['Ex'] ?? $eval['extra'] ?? 0);
            
            // Calcular promedio
            $Prom = round(($P + $Pr + $A + $E + $Ex) / 5, 2);
            
            if ($Prom > 0) {
                $validProms[] = $Prom;
            }
            
            $newEvaluations[] = [
                'P' => $P,
                'Pr' => $Pr,
                'A' => $A,
                'E' => $E,
                'Ex' => $Ex,
                'Prom' => $Prom,
            ];
        }
        
        // Calcular promedio final
        $promedioFinal = 0;
        if (!empty($validProms)) {
            $promedioFinal = round(array_sum($validProms) / count($validProms), 2);
        }
        
        // Determinar estado
        if ($promedioFinal >= 7) {
            $estado = 'Aprobado';
        } elseif ($promedioFinal >= 5) {
            $estado = 'Riesgo';
        } else {
            $estado = 'Reprobado';
        }
        
        // Actualizar
        $grade->update([
            'evaluations' => $newEvaluations,
            'promedio_final' => $promedioFinal,
            'estado' => $estado,
            'puntos_faltantes' => $promedioFinal < 7 ? (7 - $promedioFinal) : 0,
        ]);
        
        $updated++;
    }
}

echo "✅ Actualizadas {$updated} calificaciones\n\n";

// Verificar una materia
echo "🔍 VERIFICANDO JUAN GARCÍA - MATEMÁTICAS:\n\n";

$student = \App\Models\Student::where('matricula', '1000001')->first();
$matematicas = \App\Models\Subject::find(1);

if ($student && $matematicas) {
    echo "Materia: {$matematicas->nombre}\n";
    
    $grade = \App\Models\Grade::where('student_id', $student->id)
        ->where('subject_id', 1)
        ->whereNotNull('evaluations')
        ->first();
    
    if ($grade) {
        echo "Promedio: {$grade->promedio_final}\n";
        echo "Estado: {$grade->estado}\n\n";
        
        echo "Evaluaciones:\n";
        foreach ($grade->evaluations as $i => $eval) {
            echo "  U" . ($i + 1) . ": ";
            echo "P={$eval['P']} Pr={$eval['Pr']} A={$eval['A']} E={$eval['E']} Ex={$eval['Ex']}";
            echo " → Prom={$eval['Prom']}\n";
        }
    }
}

echo "\n=========================================\n";
echo "✨ RECALCULACIÓN COMPLETADA\n";
echo "=========================================\n";







