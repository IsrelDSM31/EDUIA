<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;

function getRecommendations($riskLevel, $metrics) {
    $recs = [];
    if ($riskLevel === 'alto') {
        $recs[] = 'Programar tutorías académicas personalizadas.';
        $recs[] = 'Contactar a los padres o tutores para informar la situación.';
        $recs[] = 'Asignar actividades extra de recuperación.';
        if (($metrics['attendance_rate'] ?? 1) < 0.8) {
            $recs[] = 'Implementar plan de mejora de asistencia.';
        }
        if (($metrics['grade_average'] ?? 10) < 7) {
            $recs[] = 'Reforzar materias con bajo desempeño.';
        }
    } else if ($riskLevel === 'medio') {
        $recs[] = 'Monitorear el desempeño semanalmente.';
        $recs[] = 'Sugerir participación en talleres de hábitos de estudio.';
        if (($metrics['attendance_rate'] ?? 1) < 0.9) {
            $recs[] = 'Enviar recordatorios de asistencia.';
        }
    } else {
        $recs[] = 'Mantener seguimiento regular.';
    }
    return $recs;
}

$students = Student::with(['grades', 'attendances'])->get();

$riesgos = ['alto' => [], 'medio' => [], 'bajo' => []];

foreach ($students as $student) {
    // Calcular métricas
    $totalClasses = $student->attendances->count();
    $attendedClasses = $student->attendances->where('status', 'present')->count();
    $attendanceRate = $totalClasses > 0 ? $attendedClasses / $totalClasses : 0;
    $grades = $student->grades;
    $gradeAverage = $grades->avg('promedio_final') ?? 0;
    $failedSubjects = $grades->where('promedio_final', '<', 7)->count();
    $recentGrades = $grades->sortByDesc('created_at')->take(5);
    $recentImprovement = 0;
    if ($recentGrades->count() >= 2) {
        $oldAverage = $recentGrades->slice(1)->avg('promedio_final');
        $newAverage = $recentGrades->first()->promedio_final;
        $recentImprovement = $oldAverage > 0 ? ($newAverage - $oldAverage) / $oldAverage : 0;
    }
    $metrics = [
        'attendance_rate' => $attendanceRate,
        'grade_average' => $gradeAverage,
        'failed_subjects' => $failedSubjects,
        'recent_improvement' => $recentImprovement
    ];
    // Calcular score de riesgo
    $riskScore = (1 - $metrics['attendance_rate']) * 0.3 * 100
        + (1 - ($metrics['grade_average'] / 10)) * 0.4 * 100
        + ($metrics['failed_subjects'] / 5) * 0.2 * 100
        + (1 - $metrics['recent_improvement']) * 0.1 * 100;
    $riskLevel = 'bajo';
    if ($riskScore >= 70) $riskLevel = 'alto';
    else if ($riskScore >= 40) $riskLevel = 'medio';
    $recs = getRecommendations($riskLevel, $metrics);
    $riesgos[$riskLevel][] = [
        'nombre' => $student->nombre . ' ' . $student->apellido_paterno,
        'promedio' => round($gradeAverage,2),
        'asistencia' => round($attendanceRate*100,1) . '%',
        'puntaje' => round($riskScore,1),
        'recomendaciones' => $recs
    ];
}

foreach(['alto','medio','bajo'] as $nivel) {
    echo "\n\n--- Alumnos en riesgo $nivel ---\n";
    foreach($riesgos[$nivel] as $alumno) {
        echo 'Alumno: ' . $alumno['nombre'] . ' | Promedio: ' . $alumno['promedio'] . ' | Asistencia: ' . $alumno['asistencia'] . ' | Puntaje: ' . $alumno['puntaje'] . "\n";
        echo "Recomendaciones:\n";
        foreach($alumno['recomendaciones'] as $rec) {
            echo '  - ' . $rec . "\n";
        }
        echo "\n";
    }
} 