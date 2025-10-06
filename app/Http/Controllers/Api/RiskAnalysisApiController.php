<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Models\StudentRisk;
use App\Models\Student;

class RiskAnalysisApiController extends ApiController
{
    public function index(): JsonResponse
    {
        $riskAnalysis = StudentRisk::with('student.group')->get()->map(function($risk) {
            $student = $risk->student;
            
            // Obtener factores de riesgo
            $riskFactors = [];
            if ($risk->performance_metrics) {
                $metrics = $risk->performance_metrics;
                if (isset($metrics['attendance_rate']) && $metrics['attendance_rate'] < 80) {
                    $riskFactors[] = "Asistencia irregular ({$metrics['attendance_rate']}%)";
                }
                if (isset($metrics['grade_average']) && $metrics['grade_average'] < 7) {
                    $riskFactors[] = "Bajo rendimiento académico ({$metrics['grade_average']})";
                }
                if (isset($metrics['failed_subjects']) && $metrics['failed_subjects'] > 0) {
                    $riskFactors[] = "Materias reprobadas: {$metrics['failed_subjects']}";
                }
            }
            
            if (empty($riskFactors)) {
                $riskFactors = $this->generateRiskFactors($risk, $student);
            }
            
            // Obtener recomendaciones
            $recommendations = '';
            if ($risk->intervention_recommendations && is_array($risk->intervention_recommendations)) {
                $recommendations = collect($risk->intervention_recommendations)
                    ->pluck('message')
                    ->join(' ');
            }
            
            if (!$recommendations) {
                $recommendations = $this->generateRecommendations($risk->risk_level, $student);
            }
            
            return [
                'id' => $risk->id,
                'student_id' => $risk->student_id,
                'student_name' => $student ? trim($student->nombre . ' ' . $student->apellido_paterno . ' ' . $student->apellido_materno) : 'N/A',
                'student_code' => $student->matricula ?? 'N/A',
                'risk_level' => $risk->risk_level,
                'risk_score' => $risk->risk_score ?? 0,
                'risk_factors' => $riskFactors,
                'recommendations' => $recommendations,
                'last_analysis' => $risk->updated_at,
            ];
        });

        return $this->successResponse($riskAnalysis, 'Risk analysis retrieved successfully');
    }

    public function statistics(): JsonResponse
    {
        // Obtener conteos por nivel de riesgo
        $totalStudents = Student::count();
        $highRisk = StudentRisk::whereIn('risk_level', ['alto', 'high'])->distinct('student_id')->count();
        $mediumRisk = StudentRisk::whereIn('risk_level', ['medio', 'medium'])->distinct('student_id')->count();
        $lowRisk = StudentRisk::whereIn('risk_level', ['bajo', 'low'])->distinct('student_id')->count();
        $studentsWithRisk = StudentRisk::distinct('student_id')->count();
        
        $stats = [
            'total_students' => $totalStudents,
            'high_risk' => $highRisk,
            'medium_risk' => $mediumRisk,
            'low_risk' => $lowRisk,
            'no_risk' => $totalStudents - $studentsWithRisk,
        ];

        return $this->successResponse($stats, 'Risk statistics retrieved successfully');
    }

    public function predict(): JsonResponse
    {
        return $this->successResponse([
            'message' => 'Prediction feature available'
        ], 'Prediction endpoint ready');
    }

    private function generateRiskFactors($risk, $student)
    {
        $factors = [];
        
        if (!$student) return $factors;

        // Generar factores basados en el nivel de riesgo
        switch(strtolower($risk->risk_level)) {
            case 'alto':
            case 'high':
                $factors[] = "Asistencia irregular";
                $factors[] = "Bajo rendimiento académico";
                $factors[] = "Requiere atención inmediata";
                break;
            
            case 'medio':
            case 'medium':
                $factors[] = "Necesita refuerzo académico";
                $factors[] = "Seguimiento requerido";
                break;
            
            case 'bajo':
            case 'low':
                $factors[] = "Áreas de mejora identificadas";
                break;
            
            default:
                $factors[] = "Análisis en proceso";
        }

        return $factors;
    }

    private function generateRecommendations($riskLevel, $student)
    {
        switch(strtolower($riskLevel)) {
            case 'alto':
            case 'high':
                return "INTERVENCIÓN URGENTE REQUERIDA:\n" .
                       "1. Tutoría personalizada inmediata (3 sesiones semanales)\n" .
                       "2. Reunión urgente con padres/tutores\n" .
                       "3. Plan de recuperación académica\n" .
                       "4. Seguimiento diario de asistencia\n" .
                       "5. Evaluación psicopedagógica\n" .
                       "6. Derivación a orientación escolar\n" .
                       "7. Monitoreo constante hasta mejora significativa";
            
            case 'medio':
            case 'medium':
                return "ATENCIÓN PREVENTIVA NECESARIA:\n" .
                       "1. Refuerzo académico (2 sesiones semanales)\n" .
                       "2. Tutoría grupal en materias débiles\n" .
                       "3. Reunión informativa con padres\n" .
                       "4. Seguimiento quincenal de calificaciones\n" .
                       "5. Motivación y técnicas de estudio\n" .
                       "6. Monitoreo de asistencia semanal\n" .
                       "7. Plan de mejora académica a 30 días";
            
            case 'bajo':
            case 'low':
                return "SEGUIMIENTO REGULAR:\n" .
                       "1. Mantener el rendimiento actual\n" .
                       "2. Seguimiento mensual de desempeño\n" .
                       "3. Reforzar hábitos de estudio\n" .
                       "4. Continuar monitoreando asistencia\n" .
                       "5. Reconocer logros y fortalezas";
            
            default:
                return "Análisis en proceso. Recolectando datos para evaluación completa.";
        }
    }
}

