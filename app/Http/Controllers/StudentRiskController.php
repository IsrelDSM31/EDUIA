<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentRisk;
use App\Models\Grade;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentRiskController extends Controller
{
    public function index()
    {
        try {
            $students = Student::with(['grades', 'attendances'])->get();
            $riskData = $students->map(function ($student) {
                try {
                    $metrics = $this->calculateStudentMetrics($student);
                    $riskResult = $this->calculateRiskScore($student);
                    
                    return [
                        'student' => $student,
                        'risk' => [
                            'risk_level' => $riskResult['risk_level'] ?? 'bajo',
                            'risk_score' => $riskResult['risk_score'] ?? 0,
                            'progress_metrics' => $this->calculateProgressMetrics($student)
                        ],
                        'metrics' => $metrics
                    ];
                } catch (\Exception $e) {
                    // Si hay error con un estudiante específico, devolver datos básicos
                    return [
                        'student' => $student,
                        'risk' => [
                            'risk_level' => 'bajo',
                            'risk_score' => 0,
                            'progress_metrics' => []
                        ],
                        'metrics' => [
                            'attendance_rate' => 0,
                            'grade_average' => 0,
                            'failed_subjects' => 0,
                            'recent_improvement' => 0
                        ]
                    ];
                }
            });

            return Inertia::render('RiskAnalysis/Index', [
                'riskData' => $riskData,
            ]);
        } catch (\Exception $e) {
            // Si hay error general, devolver página vacía pero funcional
            return Inertia::render('RiskAnalysis/Index', [
                'riskData' => []
            ]);
        }
    }

    public function calculateRiskScore(Student $student)
    {
        $metrics = $this->calculateStudentMetrics($student);
        $attendance = $metrics['attendance_rate'];
        $promedio = $metrics['grade_average'];
        // Si el promedio es null, no numérico o NaN, ponerlo en 0
        if (!is_numeric($promedio) || is_null($promedio)) {
            $metrics['grade_average'] = 0;
            $promedio = 0;
        }
        // Si la asistencia es null, no numérica o NaN, ponerlo en 0
        if (!is_numeric($attendance) || is_null($attendance)) {
            $metrics['attendance_rate'] = 0;
            $attendance = 0;
        }
        $riskLevel = 'bajo';
        // Lógica IA 
        if ($promedio === 0 || $attendance === 0) {
            $riskLevel = 'alto';
        } else if ($promedio >= 8 && $attendance >= 0.95) {
            $riskLevel = 'bajo';
        } else if ($promedio >= 8 && $attendance < 0.95) {
            $riskLevel = 'medio';
        } else if ($promedio < 8 && $attendance < 0.8) {
            $riskLevel = 'alto';
        } else if ($promedio < 8 && $attendance >= 0.8) {
            $riskLevel = 'medio';
        }
        // Generar recomendaciones IA personalizadas
        $recommendations = $this->generateRecommendationsIA($metrics, $riskLevel);
        // Actualizar o crear registro de riesgo
        StudentRisk::updateOrCreate(
            ['student_id' => $student->id],
            [
                'risk_score' => 0, // Ya no se usa el score numérico
                'risk_level' => $riskLevel,
                'performance_metrics' => $metrics,
                'intervention_recommendations' => $recommendations,
                'progress_metrics' => $this->calculateProgressMetrics($student)
            ]
        );
        return [
            'risk_score' => 0,
            'risk_level' => $riskLevel,
            'metrics' => $metrics,
            'recommendations' => $recommendations
        ];
    }

    private function calculateStudentMetrics(Student $student)
    {
        // Calcular tasa de asistencia
        $totalClasses = $student->attendances->count();
        $attendedClasses = $student->attendances->where('status', 'present')->count();
        $attendanceRate = $totalClasses > 0 ? $attendedClasses / $totalClasses : 0;

        // Calcular promedio de calificaciones usando promedio_final
        $grades = $student->grades;
        $gradeAverage = $grades->avg('promedio_final') ?? 0;

        // Contar materias reprobadas
        $failedSubjects = $grades->where('promedio_final', '<', 7)->count();

        // Calcular mejora reciente
        $recentGrades = $grades->sortByDesc('created_at')->take(5);
        $recentImprovement = 0;
        if ($recentGrades->count() >= 2) {
            $oldAverage = $recentGrades->slice(1)->avg('promedio_final');
            $newAverage = $recentGrades->first()->promedio_final;
            $recentImprovement = $oldAverage > 0 ? ($newAverage - $oldAverage) / $oldAverage : 0;
        }

        return [
            'attendance_rate' => $attendanceRate,
            'grade_average' => $gradeAverage,
            'failed_subjects' => $failedSubjects,
            'recent_improvement' => $recentImprovement
        ];
    }

    // Nueva función IA para recomendaciones inteligentes
    private function generateRecommendationsIA($metrics, $riskLevel)
    {
        $recs = [];
        $attendance = $metrics['attendance_rate'];
        $promedio = $metrics['grade_average'];
        if ($riskLevel === 'bajo') {
            $recs[] = [
                'type' => 'success',
                'priority' => 'low',
                'message' => '¡Felicidades! El estudiante mantiene un buen desempeño académico y asistencia. Motivar a continuar así.'
            ];
        } elseif ($riskLevel === 'medio') {
            if ($promedio >= 8 && $attendance < 0.95) {
                $recs[] = [
                    'type' => 'attendance',
                    'priority' => 'medium',
                    'message' => 'El rendimiento académico es bueno, pero la asistencia podría mejorar. Recomendar estrategias de puntualidad y asistencia.'
                ];
            } else {
                $recs[] = [
                    'type' => 'academic',
                    'priority' => 'medium',
                    'message' => 'El promedio puede mejorar. Sugerir tutorías o talleres de hábitos de estudio.'
                ];
            }
        } elseif ($riskLevel === 'alto') {
            $recs[] = [
                'type' => 'critical',
                'priority' => 'high',
                'message' => 'El estudiante presenta bajo promedio y baja asistencia. Programar intervención inmediata, tutorías personalizadas y contacto con padres.'
            ];
            if ($attendance < 0.8) {
                $recs[] = [
                    'type' => 'attendance',
                    'priority' => 'high',
                    'message' => 'Implementar plan urgente de mejora de asistencia.'
                ];
            }
            if ($promedio < 7) {
                $recs[] = [
                    'type' => 'academic',
                    'priority' => 'high',
                    'message' => 'Asignar actividades de recuperación y reforzar materias con bajo desempeño.'
                ];
            }
        }
        return $recs;
    }

    private function calculateProgressMetrics(Student $student)
    {
        $grades = $student->grades;
        $attendance = $student->attendances;

        return [
            'academic_progress' => [
                'current_average' => $grades->avg('promedio_final') ?? 0,
                'trend' => $this->calculateTrend($grades),
                'improvement_rate' => $this->calculateImprovementRate($grades)
            ],
            'attendance_progress' => [
                'current_rate' => $attendance->where('status', 'present')->count() / max($attendance->count(), 1),
                'trend' => $this->calculateAttendanceTrend($attendance)
            ]
        ];
    }

    private function calculateTrend($grades)
    {
        if ($grades->count() < 2) return 'stable';
        
        $recentGrades = $grades->sortByDesc('created_at')->take(5);
        $oldAverage = $recentGrades->slice(1)->avg('promedio_final');
        $newAverage = $recentGrades->first()->promedio_final;
        
        if ($newAverage > $oldAverage) return 'improving';
        if ($newAverage < $oldAverage) return 'declining';
        return 'stable';
    }

    private function calculateImprovementRate($grades)
    {
        if ($grades->count() < 2) return 0;
        
        $recentGrades = $grades->sortByDesc('created_at')->take(5);
        $oldAverage = $recentGrades->slice(1)->avg('promedio_final');
        $newAverage = $recentGrades->first()->promedio_final;
        
        return $oldAverage > 0 ? (($newAverage - $oldAverage) / $oldAverage) * 100 : 0;
    }

    private function calculateAttendanceTrend($attendance)
    {
        if ($attendance->count() < 2) return 'stable';
        
        $recentAttendance = $attendance->sortByDesc('date')->take(5);
        $oldRate = $recentAttendance->slice(1)->where('status', 'present')->count() / max($recentAttendance->slice(1)->count(), 1);
        $newRate = $recentAttendance->first()->status === 'present' ? 1 : 0;
        
        if ($newRate > $oldRate) return 'improving';
        if ($newRate < $oldRate) return 'declining';
        return 'stable';
    }

    public function apiShow($id)
    {
        $student = \App\Models\Student::with(['grades', 'attendances'])->findOrFail($id);
        $riskResult = $this->calculateRiskScore($student);
        return response()->json([
            'student' => $student,
            'risk' => $riskResult,
        ]);
    }
}
