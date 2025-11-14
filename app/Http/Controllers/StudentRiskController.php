<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentRisk;
use App\Models\Grade;
use App\Models\Attendance;
use App\Services\RiskPredictionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentRiskController extends Controller
{
    public function index()
    {
        try {
            // Optimización: Usar datos guardados en StudentRisk en lugar de recalcular todo
            // Solo cargar estudiantes que tienen análisis de riesgo guardado
            $studentRisks = StudentRisk::with(['student.grades', 'student.attendances'])
                ->whereHas('student')
                ->get();

            // Obtener IDs de estudiantes que ya tienen análisis
            $analyzedStudentIds = $studentRisks->pluck('student_id')->toArray();

            // Obtener estudiantes sin análisis (para calcularlos después si es necesario)
            $studentsWithoutRisk = Student::whereNotIn('id', $analyzedStudentIds)
                ->with(['grades', 'attendances'])
                ->get();

            // Mapear datos de riesgo existentes (rápido)
            $riskData = $studentRisks->map(function ($studentRisk) {
                $student = $studentRisk->student;
                if (!$student) {
                    return null;
                }

                // Recalcular métricas para asegurar que estén actualizadas
                $metrics = $this->calculateStudentMetrics($student);
                $progressMetrics = $this->calculateProgressMetrics($student);

                // Recalcular riesgo si las métricas han cambiado significativamente
                // O si el riesgo fue calculado con la lógica antigua (sin failed_subjects)
                $mlData = [];
                if ($studentRisk->ml_data) {
                    $mlData = is_string($studentRisk->ml_data) 
                        ? json_decode($studentRisk->ml_data, true) 
                        : $studentRisk->ml_data;
                }

                // Recalcular siempre con la nueva lógica mejorada para asegurar precisión
                // Esto es necesario porque la lógica anterior era incorrecta
                // Solo recalcular si las métricas han cambiado o si fue calculado con lógica antigua
                $savedMetrics = $studentRisk->performance_metrics ?? [];
                $shouldRecalculate = false;
                
                // Si no tiene métricas guardadas o no tiene failed_subjects, recalcular
                if (empty($savedMetrics) || !isset($savedMetrics['failed_subjects'])) {
                    $shouldRecalculate = true;
                } else {
                    // Recalcular si las métricas han cambiado significativamente
                    if (abs(($savedMetrics['grade_average'] ?? 0) - ($metrics['grade_average'] ?? 0)) > 0.5 ||
                        abs(($savedMetrics['attendance_rate'] ?? 0) - ($metrics['attendance_rate'] ?? 0)) > 0.1 ||
                        ($savedMetrics['failed_subjects'] ?? 0) != ($metrics['failed_subjects'] ?? 0)) {
                        $shouldRecalculate = true;
                    }
                }

                // Si debe recalcular, hacerlo con la nueva lógica
                if ($shouldRecalculate) {
                    $riskResult = $this->calculateRiskScore($student);
                    $studentRisk->refresh();
                    $mlData = $studentRisk->ml_data ?? [];
                    if (is_string($mlData)) {
                        $mlData = json_decode($mlData, true);
                    }
                }

                return [
                    'student' => $student,
                    'risk' => [
                        'risk_level' => $studentRisk->risk_level ?? 'bajo',
                        'risk_score' => $studentRisk->risk_score ?? 0,
                        'progress_metrics' => $progressMetrics,
                        'source' => $mlData['source'] ?? 'rules',
                        'confidence' => $mlData['confidence'] ?? null,
                        'probabilities' => $mlData['probabilities'] ?? [],
                        'features_used' => $mlData['features_used'] ?? [],
                        'ml_service_available' => isset($mlData['source']) && $mlData['source'] === 'ml',
                    ],
                    'metrics' => $metrics,
                    'recommendations' => $studentRisk->intervention_recommendations ?? []
                ];
            })->filter();

            // Si hay estudiantes sin análisis, calcularlos (pero sin bloquear la carga)
            // Esto se puede hacer en background o lazy loading
            if ($studentsWithoutRisk->count() > 0 && $studentsWithoutRisk->count() <= 10) {
                // Solo calcular si hay pocos estudiantes sin análisis (para no bloquear)
                foreach ($studentsWithoutRisk as $student) {
                    try {
                        $metrics = $this->calculateStudentMetrics($student);
                        $riskResult = $this->calculateRiskScore($student);
                        $progressMetrics = $this->calculateProgressMetrics($student);

                        // Extraer información del ML del resultado
                        $mlData = [];
                        if (isset($riskResult['source']) && $riskResult['source'] === 'ml') {
                            $mlData = [
                                'source' => 'ml',
                                'confidence' => $riskResult['confidence'] ?? $riskResult['risk_score'] ?? 0,
                                'probabilities' => $riskResult['probabilities'] ?? [],
                                'features_used' => $riskResult['features_used'] ?? [],
                            ];
                        }

                        $riskData->push([
                            'student' => $student,
                            'risk' => [
                                'risk_level' => $riskResult['risk_level'] ?? 'bajo',
                                'risk_score' => $riskResult['risk_score'] ?? 0,
                                'progress_metrics' => $progressMetrics,
                                'source' => $mlData['source'] ?? 'rules',
                                'confidence' => $mlData['confidence'] ?? null,
                                'probabilities' => $mlData['probabilities'] ?? [],
                                'features_used' => $mlData['features_used'] ?? [],
                                'ml_service_available' => isset($mlData['source']) && $mlData['source'] === 'ml',
                            ],
                            'metrics' => $metrics,
                            'recommendations' => $riskResult['recommendations'] ?? []
                        ]);
                    } catch (\Exception $e) {
                        // Continuar con otros estudiantes
                    }
                }
            }

            return Inertia::render('RiskAnalysis/Index', [
                'riskData' => $riskData->values()->all(),
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
        
        // Intentar usar ML primero
        $mlPrediction = null;
        try {
            $mlService = app(RiskPredictionService::class);
            $mlPrediction = $mlService->predictRisk($student);
        } catch (\Exception $e) {
            // Si falla, continuar con reglas
        }
        
        // Si ML está disponible, usarlo
        if ($mlPrediction && isset($mlPrediction['source']) && $mlPrediction['source'] === 'ml') {
            $riskLevel = $mlPrediction['risk_level'];
            $riskScore = $mlPrediction['risk_score'];
            
            // Generar recomendaciones basadas en el nivel de riesgo del ML
            $recommendations = $this->generateRecommendationsIA($metrics, $riskLevel);
            
            // Guardar información completa del ML para uso futuro
            $mlData = [
                'source' => 'ml',
                'confidence' => $mlPrediction['confidence'] ?? $riskScore,
                'probabilities' => $mlPrediction['probabilities'] ?? [],
                'features_used' => $mlPrediction['features_used'] ?? [],
            ];
            
            // Actualizar o crear registro de riesgo
            StudentRisk::updateOrCreate(
                ['student_id' => $student->id],
                [
                    'risk_score' => $riskScore,
                    'risk_level' => $riskLevel,
                    'performance_metrics' => $metrics,
                    'intervention_recommendations' => $recommendations,
                    'progress_metrics' => $this->calculateProgressMetrics($student),
                    'ml_data' => $mlData // Guardar datos del ML
                ]
            );
            
            return [
                'risk_score' => $riskScore,
                'risk_level' => $riskLevel,
                'metrics' => $metrics,
                'recommendations' => $recommendations,
                'source' => 'ml',
                'confidence' => $mlPrediction['confidence'] ?? $riskScore,
                'probabilities' => $mlPrediction['probabilities'] ?? [],
                'features_used' => $mlPrediction['features_used'] ?? [],
            ];
        }
        
        // Fallback: usar reglas heurísticas mejoradas (lógica balanceada)
        $attendance = $metrics['attendance_rate'];
        $promedio = $metrics['grade_average'];
        $failedSubjects = $metrics['failed_subjects'] ?? 0;
        
        // Validar y normalizar valores
        if (!is_numeric($promedio) || is_null($promedio)) {
            $promedio = 0;
        }
        if (!is_numeric($attendance) || is_null($attendance)) {
            $attendance = 0;
        }
        if (!is_numeric($failedSubjects) || is_null($failedSubjects)) {
            $failedSubjects = 0;
        }
        
        // Lógica mejorada de detección de riesgo (balanceada)
        $riskLevel = 'bajo'; // Por defecto
        
        // Caso 1: Sin datos académicos (ninguna calificación registrada)
        // IMPORTANTE: Si no hay calificaciones, NUNCA puede ser riesgo "bajo"
        // La asistencia sola no es suficiente para determinar buen desempeño académico
        if ($promedio === 0 && $attendance === 0) {
            $riskLevel = 'alto'; // Sin datos de ningún tipo
        } elseif ($promedio === 0 && $attendance > 0) {
            // Tiene asistencia pero no calificaciones aún
            // Sin calificaciones, no puede ser "bajo". Al menos "medio" si tiene buena asistencia
            if ($attendance >= 0.9) {
                $riskLevel = 'medio'; // Buena asistencia pero falta evaluar desempeño académico
            } elseif ($attendance >= 0.7) {
                $riskLevel = 'medio'; // Asistencia regular, falta evaluar desempeño académico
            } else {
                $riskLevel = 'alto'; // Baja asistencia y sin calificaciones = alto riesgo
            }
        } elseif ($promedio > 0 && $attendance === 0) {
            // Tiene calificaciones pero no asistencia registrada
            // Evaluar basado en el promedio y materias reprobadas
            if ($promedio >= 9 && $failedSubjects === 0) {
                $riskLevel = 'medio'; // Excelente promedio, solo falta asistencia
            } elseif ($promedio >= 8 && $failedSubjects <= 1) {
                $riskLevel = 'medio';
            } elseif ($promedio < 7 || $failedSubjects >= 2) {
                $riskLevel = 'alto'; // Bajo promedio o muchas reprobadas
            } else {
                $riskLevel = 'medio';
            }
        } else {
            // Caso normal: tiene ambos datos
            // Evaluar combinación de promedio, asistencia y materias reprobadas
            
            // Excelente: promedio alto, asistencia alta, sin reprobadas
            if ($promedio >= 9 && $attendance >= 0.95 && $failedSubjects === 0) {
                $riskLevel = 'bajo';
            }
            // Muy bueno: promedio alto, asistencia buena
            elseif ($promedio >= 8 && $attendance >= 0.9 && $failedSubjects <= 1) {
                $riskLevel = 'bajo';
            }
            // Bueno: promedio alto pero asistencia regular
            elseif ($promedio >= 8 && $attendance >= 0.8) {
                $riskLevel = 'medio';
            }
            // Promedio alto pero asistencia baja
            elseif ($promedio >= 8 && $attendance < 0.8) {
                $riskLevel = 'medio';
            }
            // Promedio regular pero asistencia alta
            elseif ($promedio >= 7 && $promedio < 8 && $attendance >= 0.9 && $failedSubjects <= 1) {
                $riskLevel = 'medio';
            }
            // Promedio regular pero asistencia baja o muchas reprobadas
            elseif ($promedio >= 7 && $promedio < 8 && ($attendance < 0.8 || $failedSubjects >= 2)) {
                $riskLevel = 'alto';
            }
            // Bajo promedio o muchas reprobadas
            elseif ($promedio < 7 || $failedSubjects >= 3) {
                $riskLevel = 'alto';
            }
            // Bajo promedio con asistencia regular
            elseif ($promedio < 7 && $attendance >= 0.8) {
                $riskLevel = 'alto';
            }
            // Bajo promedio con asistencia baja
            elseif ($promedio < 7 && $attendance < 0.8) {
                $riskLevel = 'alto';
            }
            // Caso por defecto
            else {
                $riskLevel = 'medio';
            }
        }
        // Generar recomendaciones IA personalizadas
        $recommendations = $this->generateRecommendationsIA($metrics, $riskLevel);
        
        // Guardar información de que viene de reglas
        $mlData = [
            'source' => 'rules',
        ];
        
        // Actualizar o crear registro de riesgo
        StudentRisk::updateOrCreate(
            ['student_id' => $student->id],
            [
                'risk_score' => 0, // Ya no se usa el score numérico
                'risk_level' => $riskLevel,
                'performance_metrics' => $metrics,
                'intervention_recommendations' => $recommendations,
                'progress_metrics' => $this->calculateProgressMetrics($student),
                'ml_data' => $mlData // Guardar source
            ]
        );
        return [
            'risk_score' => 0,
            'risk_level' => $riskLevel,
            'metrics' => $metrics,
            'recommendations' => $recommendations,
            'source' => 'rules' // Indicar que viene de reglas
        ];
    }

    private function calculateStudentMetrics(Student $student)
    {
        // Calcular tasa de asistencia
        $totalClasses = $student->attendances->count();
        $attendedClasses = $student->attendances->where('status', 'present')->count();
        $attendanceRate = $totalClasses > 0 ? $attendedClasses / $totalClasses : 0;
        
        // Asegurar que attendanceRate sea un número válido
        if (!is_numeric($attendanceRate) || is_nan($attendanceRate)) {
            $attendanceRate = 0;
        }

        // Calcular promedio de calificaciones usando promedio_final
        $grades = $student->grades;
        $gradeAverage = 0;
        if ($grades && $grades->count() > 0) {
            $avg = $grades->avg('promedio_final');
            $gradeAverage = is_numeric($avg) && !is_nan($avg) ? (float)$avg : 0;
        }

        // Contar materias reprobadas (solo calificaciones con promedio_final < 7)
        $failedSubjects = 0;
        if ($grades && $grades->count() > 0) {
            $failedSubjects = $grades->filter(function($grade) {
                $promedio = $grade->promedio_final ?? 0;
                return is_numeric($promedio) && $promedio < 7 && $promedio > 0;
            })->count();
        }

        // Calcular mejora reciente
        $recentImprovement = 0;
        if ($grades && $grades->count() >= 2) {
            $recentGrades = $grades->sortByDesc('created_at')->take(5);
            if ($recentGrades->count() >= 2) {
                $oldAverage = $recentGrades->slice(1)->avg('promedio_final');
                $newAverage = $recentGrades->first()->promedio_final ?? 0;
                
                $oldAvg = is_numeric($oldAverage) && !is_nan($oldAverage) ? (float)$oldAverage : 0;
                $newAvg = is_numeric($newAverage) && !is_nan($newAverage) ? (float)$newAverage : 0;
                
                if ($oldAvg > 0) {
                    $recentImprovement = ($newAvg - $oldAvg) / $oldAvg;
                }
            }
        }

        return [
            'attendance_rate' => (float)$attendanceRate,
            'grade_average' => (float)$gradeAverage,
            'failed_subjects' => (int)$failedSubjects,
            'recent_improvement' => (float)$recentImprovement
        ];
    }

    // Nueva función IA para recomendaciones inteligentes
    public function generateRecommendationsIA($metrics, $riskLevel)
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
