<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\StudentRisk;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateAutomaticAlerts extends Command
{
    protected $signature = 'alerts:generate';
    protected $description = 'Generate automatic alerts based on attendance and grades';

    public function handle()
    {
        $this->info('Starting automatic alert generation...');
        
        // Get all students
        $students = Student::with(['attendances', 'grades'])->get();
        
        foreach ($students as $student) {
            // Check attendance
            $this->checkAttendance($student);
            
            // Check grades
            $this->checkGrades($student);
            
            // Update risk score
            $this->updateRiskScore($student);
        }
        
        $this->info('Finished generating alerts.');
    }

    private function checkAttendance($student)
    {
        // Get absences by subject
        $absentsBySubject = [];
        foreach ($student->attendances as $att) {
            if ($att->status === 'absent') {
                $absentsBySubject[$att->subject_id] = ($absentsBySubject[$att->subject_id] ?? 0) + 1;
            }
        }
        $extraordinaryCount = count(array_filter($absentsBySubject, function($a) { return $a > 6; }));
        $estado = 'Completas';
        if ($extraordinaryCount >= 3) $estado = 'Baja';
        elseif ($extraordinaryCount === 2) $estado = 'En riesgo';
        elseif ($extraordinaryCount === 1) $estado = 'Extraordinario';

        if ($estado !== 'Completas') {
            $title = $estado === 'Baja' ? 'Baja por Asistencias' : ($estado === 'En riesgo' ? 'En Riesgo por Asistencias' : 'Extraordinario por Asistencias');
            $urgency = $estado === 'Baja' ? 'high' : 'medium';
            $description = "El alumno presenta estado global: $estado según el análisis de asistencias (ML).";
            $suggested_actions = $estado === 'Baja'
                ? ['Contactar al tutor', 'Programar reunión con padres', 'Evaluar plan de regularización']
                : ($estado === 'En riesgo'
                    ? ['Monitorear asistencias', 'Enviar notificación preventiva']
                    : ['Enviar advertencia', 'Revisar causas de inasistencias']);
            Alert::updateOrCreate([
                'student_id' => $student->id,
                'type' => 'asistencia',
                'title' => $title,
            ], [
                'description' => $description,
                'urgency' => $urgency,
                'status' => 'Detectada por ML',
                'suggested_actions' => json_encode($suggested_actions),
            ]);
        }
        
        // Get absences in the last 30 days
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $absences = $student->attendances()
            ->where('date', '>=', $thirtyDaysAgo)
            ->where('status', 'absent')
            ->count();
        
        // Get consecutive absences
        $consecutiveAbsences = $this->getConsecutiveAbsences($student);
        
        // Generate alerts based on absence patterns
        if ($consecutiveAbsences >= 3) {
            $this->createAlert($student, [
                'type' => 'attendance',
                'title' => 'Inasistencias Consecutivas',
                'description' => "El estudiante tiene {$consecutiveAbsences} faltas consecutivas.",
                'urgency' => 'high',
                'suggested_actions' => ['Contactar al tutor', 'Programar reunión con padres'],
                'intervention_plan' => [
                    'objectives' => ['Identificar causa de inasistencias', 'Establecer plan de regularización'],
                    'strategies' => ['Llamada telefónica a padres', 'Reunión con equipo docente'],
                    'responsible' => ['Tutor', 'Orientador'],
                    'timeline' => ['Contacto inmediato', 'Seguimiento semanal']
                ]
            ]);
        }
        
        if ($absences >= 5) {
            $this->createAlert($student, [
                'type' => 'attendance',
                'title' => 'Alto Número de Inasistencias',
                'description' => "El estudiante acumula {$absences} faltas en los últimos 30 días.",
                'urgency' => 'medium',
                'suggested_actions' => ['Revisar justificantes', 'Analizar patrón de ausencias'],
                'intervention_plan' => [
                    'objectives' => ['Reducir ausentismo', 'Recuperar clases perdidas'],
                    'strategies' => ['Plan de asistencia', 'Material de recuperación'],
                    'responsible' => ['Docentes', 'Tutor'],
                    'timeline' => ['Evaluación semanal', 'Reporte mensual']
                ]
            ]);
        }
    }

    private function checkGrades($student)
    {
        // Get recent low grades
        $lowGrades = $student->grades()
            ->where('score', '<', 7)
            ->where('date', '>=', Carbon::now()->subDays(30))
            ->get();
        
        if ($lowGrades->count() >= 3) {
            $subjects = $lowGrades->pluck('subject.name')->unique()->implode(', ');
            
            $this->createAlert($student, [
                'type' => 'academic',
                'title' => 'Bajo Rendimiento Académico',
                'description' => "El estudiante presenta calificaciones bajas en las materias: {$subjects}",
                'urgency' => 'high',
                'suggested_actions' => ['Asesorías académicas', 'Reunión con profesores'],
                'intervention_plan' => [
                    'objectives' => ['Mejorar rendimiento académico', 'Identificar áreas de dificultad'],
                    'strategies' => ['Tutorías personalizadas', 'Plan de estudio'],
                    'responsible' => ['Docentes', 'Asesor académico'],
                    'timeline' => ['Inicio inmediato', 'Evaluación quincenal']
                ]
            ]);
        }
    }

    private function getConsecutiveAbsences($student)
    {
        $recentAttendances = $student->attendances()
            ->orderBy('date', 'desc')
            ->take(10)
            ->get()
            ->sortBy('date');
        
        $consecutive = 0;
        $maxConsecutive = 0;
        
        foreach ($recentAttendances as $attendance) {
            if ($attendance->status === 'absent') {
                $consecutive++;
                $maxConsecutive = max($maxConsecutive, $consecutive);
            } else {
                $consecutive = 0;
            }
        }
        
        return $maxConsecutive;
    }

    private function updateRiskScore($student)
    {
        // Calculate risk factors
        $absenceScore = $this->calculateAbsenceRiskScore($student);
        $gradeScore = $this->calculateGradeRiskScore($student);
        
        // Calculate overall risk score (0-100)
        $riskScore = ($absenceScore + $gradeScore) / 2;
        
        // Determine risk level
        $riskLevel = $riskScore >= 75 ? 'alto' : ($riskScore >= 50 ? 'medio' : 'bajo');
        
        // Update or create student risk record
        StudentRisk::updateOrCreate(
            ['student_id' => $student->id],
            [
                'risk_score' => $riskScore,
                'risk_level' => $riskLevel,
                'performance_metrics' => [
                    'attendance_score' => $absenceScore,
                    'grade_score' => $gradeScore,
                    'last_updated' => now()
                ],
                'intervention_recommendations' => $this->generateRecommendations($riskScore, $absenceScore, $gradeScore)
            ]
        );
    }

    private function calculateAbsenceRiskScore($student)
    {
        $recentAbsences = $student->attendances()
            ->where('date', '>=', Carbon::now()->subDays(30))
            ->where('status', 'absent')
            ->count();
        
        // Convert to a 0-100 score (assuming 10 absences is maximum risk)
        return min(100, ($recentAbsences / 10) * 100);
    }

    private function calculateGradeRiskScore($student)
    {
        $recentGrades = $student->grades()
            ->where('date', '>=', Carbon::now()->subDays(30))
            ->avg('score');
        
        // Convert to a 0-100 risk score (lower grades = higher risk)
        return max(0, (10 - ($recentGrades ?? 10)) * 10);
    }

    private function generateRecommendations($riskScore, $absenceScore, $gradeScore)
    {
        $recommendations = [];
        
        if ($absenceScore >= 70) {
            $recommendations[] = [
                'type' => 'attendance',
                'priority' => 'high',
                'message' => 'Se requiere intervención inmediata por alto índice de inasistencias'
            ];
        }
        
        if ($gradeScore >= 70) {
            $recommendations[] = [
                'type' => 'academic',
                'priority' => 'high',
                'message' => 'Necesita apoyo académico urgente en materias con bajo rendimiento'
            ];
        }
        
        if ($riskScore >= 50 && $riskScore < 70) {
            $recommendations[] = [
                'type' => 'monitoring',
                'priority' => 'medium',
                'message' => 'Establecer plan de seguimiento y apoyo preventivo'
            ];
        }
        
        return $recommendations;
    }

    private function createAlert($student, $data)
    {
        // Avoid duplicate alerts for the same issue in the same day
        $existingAlert = Alert::where('student_id', $student->id)
            ->where('type', $data['type'])
            ->where('title', $data['title'])
            ->whereDate('created_at', Carbon::today())
            ->exists();
        
        if (!$existingAlert) {
            Alert::create(array_merge(
                ['student_id' => $student->id],
                $data
            ));
        }
    }
} 