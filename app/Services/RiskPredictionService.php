<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio para predicción de riesgo usando Machine Learning
 * 
 * Este servicio se conecta al servicio Python ML para obtener predicciones.
 * Si el servicio ML no está disponible, usa el sistema de reglas existente como fallback.
 */
class RiskPredictionService
{
    /**
     * URL del servicio ML (configurable desde .env)
     */
    private $mlServiceUrl;

    /**
     * Timeout para las peticiones HTTP (en segundos)
     */
    private $timeout = 3;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mlServiceUrl = config('services.ml_service.url', 'http://localhost:5000');
    }

    /**
     * Predecir riesgo de un estudiante usando ML
     * 
     * @param Student $student
     * @return array|null Retorna predicción o null si falla
     */
    public function predictRisk(Student $student): ?array
    {
        try {
            // Extraer características del estudiante
            $features = $this->extractFeatures($student);

            // Intentar predicción con ML
            $prediction = $this->callMLService($features);

            if ($prediction) {
                return $prediction;
            }

            // Si falla, retornar null para usar fallback
            return null;

        } catch (Exception $e) {
            Log::warning('Error en predicción ML: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Extraer características del estudiante
     * 
     * @param Student $student
     * @return array
     */
    private function extractFeatures(Student $student): array
    {
        // Calcular métricas (usando la misma lógica que el controlador)
        $metrics = $this->calculateStudentMetrics($student);

        return [
            'grade_average' => $metrics['grade_average'] ?? 0,
            'attendance_rate' => $metrics['attendance_rate'] ?? 0,
            'failed_subjects' => $metrics['failed_subjects'] ?? 0,
            'recent_improvement' => $metrics['recent_improvement'] ?? 0,
            'group_id' => $student->group_id ?? 1,
        ];
    }

    /**
     * Calcular métricas del estudiante (misma lógica que StudentRiskController)
     * 
     * @param Student $student
     * @return array
     */
    private function calculateStudentMetrics(Student $student): array
    {
        // Tasa de asistencia
        $totalClasses = $student->attendances->count();
        $attendedClasses = $student->attendances->where('status', 'present')->count();
        $attendanceRate = $totalClasses > 0 ? $attendedClasses / $totalClasses : 0;
        
        // Asegurar que attendanceRate sea un número válido
        if (!is_numeric($attendanceRate) || is_nan($attendanceRate)) {
            $attendanceRate = 0;
        }

        // Promedio de calificaciones
        $grades = $student->grades;
        $gradeAverage = 0;
        if ($grades && $grades->count() > 0) {
            $avg = $grades->avg('promedio_final');
            $gradeAverage = is_numeric($avg) && !is_nan($avg) ? (float)$avg : 0;
        }

        // Contar materias reprobadas (solo calificaciones con promedio_final < 7 y > 0)
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

    /**
     * Llamar al servicio ML de Python
     * 
     * @param array $features
     * @return array|null
     */
    private function callMLService(array $features): ?array
    {
        try {
            $payload = [
                'grade_average' => (float) ($features['grade_average'] ?? 0),
                'attendance_rate' => (float) ($features['attendance_rate'] ?? 0),
                'failed_subjects' => (int) ($features['failed_subjects'] ?? 0),
                'recent_improvement' => (float) ($features['recent_improvement'] ?? 0),
                'group_id' => (int) ($features['group_id'] ?? 1),
            ];

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->mlServiceUrl . '/predict', $payload);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === 'success') {
                    // Mapear niveles de riesgo si es necesario
                    $riskLevel = $this->mapRiskLevel($data['risk_level'] ?? 'medio');

                    return [
                        'risk_level' => $riskLevel,
                        'risk_score' => (float) ($data['confidence'] ?? $data['risk_score'] ?? 0.5),
                        'confidence' => (float) ($data['confidence'] ?? 0.5),
                        'probabilities' => $data['probabilities'] ?? [],
                        'source' => 'ml', // Indicar que viene del ML
                        'features_used' => $data['features_used'] ?? [],
                    ];
                } elseif (isset($data['error'])) {
                    // El servicio respondió con un error
                    Log::warning('Error del servicio ML: ' . ($data['error'] ?? 'Error desconocido'));
                    return null;
                }
            } else {
                // Respuesta HTTP no exitosa
                Log::warning('Servicio ML respondió con error HTTP: ' . $response->status());
                return null;
            }

            return null;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Error de conexión (servicio no disponible)
            Log::debug('Servicio ML no disponible (conexión): ' . $e->getMessage());
            return null;
        } catch (Exception $e) {
            // Otro tipo de error
            Log::warning('Error al llamar al servicio ML: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mapear niveles de riesgo del modelo a los valores esperados
     * 
     * @param string $level
     * @return string
     */
    private function mapRiskLevel(string $level): string
    {
        $level = strtolower($level);

        // Mapear diferentes variaciones
        $mapping = [
            'low' => 'bajo',
            'medium' => 'medio',
            'high' => 'alto',
            'bajo' => 'bajo',
            'medio' => 'medio',
            'alto' => 'alto',
        ];

        return $mapping[$level] ?? 'medio';
    }

    /**
     * Verificar si el servicio ML está disponible
     * 
     * @return bool
     */
    public function isMLServiceAvailable(): bool
    {
        try {
            $response = Http::timeout(2)->get($this->mlServiceUrl . '/health');

            if ($response->successful()) {
                $data = $response->json();
                return isset($data['model_loaded']) && $data['model_loaded'] === true;
            }

            return false;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Obtener información del modelo ML
     * 
     * @return array|null
     */
    public function getModelInfo(): ?array
    {
        try {
            $response = Http::timeout(2)->get($this->mlServiceUrl . '/model-info');

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (Exception $e) {
            return null;
        }
    }
}

