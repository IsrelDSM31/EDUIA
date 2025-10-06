<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GradeManagementApiController extends ApiController
{
    /**
     * Lista de todos los estudiantes para gestión de calificaciones
     */
    public function students(Request $request): JsonResponse
    {
        $query = Student::with(['group', 'user']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('apellido_materno', 'like', "%{$search}%")
                  ->orWhere('matricula', 'like', "%{$search}%");
            });
        }

        $students = $query->get()->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => trim($student->nombre . ' ' . $student->apellido_paterno . ' ' . $student->apellido_materno),
                'matricula' => $student->matricula,
                'group' => $student->group->name ?? 'Sin grupo',
            ];
        });

        return $this->successResponse($students, 'Students retrieved successfully');
    }

    /**
     * Obtener calificaciones completas de un estudiante
     */
    public function studentGrades(Request $request, $studentId): JsonResponse
    {
        $student = Student::with(['group', 'grades.subject'])->find($studentId);

        if (!$student) {
            return $this->notFoundResponse('Student not found');
        }

        // Obtener todas las materias
        $subjects = Subject::all();

        // Organizar calificaciones por materia
        $gradesBySubject = [];

        foreach ($subjects as $subject) {
            // Buscar las calificaciones de esta materia para este estudiante
            $grades = Grade::where('student_id', $studentId)
                          ->where('subject_id', $subject->id)
                          ->get();

            // Si hay calificaciones para esta materia
            if ($grades->isNotEmpty()) {
                $firstGrade = $grades->first();
                $evaluations = $firstGrade->evaluations ?? [];
                
                // Asegurar que siempre haya 4 evaluaciones
                $formattedEvaluations = [];
                for ($i = 0; $i < 4; $i++) {
                    if (isset($evaluations[$i])) {
                        $eval = $evaluations[$i];
                        
                        // Convertir todos los valores a float
                        $P = floatval($eval['P'] ?? $eval['teamwork'] ?? 0);
                        $Pr = floatval($eval['Pr'] ?? $eval['project'] ?? 0);
                        $A = floatval($eval['A'] ?? $eval['attendance'] ?? 0);
                        $E = floatval($eval['E'] ?? $eval['exam'] ?? 0);
                        $Ex = floatval($eval['Ex'] ?? $eval['extra'] ?? 0);
                        
                        // Calcular promedio si no existe
                        $Prom = isset($eval['Prom']) && $eval['Prom'] > 0 
                            ? floatval($eval['Prom'])
                            : round(($P + $Pr + $A + $E + $Ex) / 5, 2);
                        
                        $formattedEvaluations[] = [
                            'P' => $P,
                            'Pr' => $Pr,
                            'A' => $A,
                            'E' => $E,
                            'Ex' => $Ex,
                            'Prom' => $Prom,
                        ];
                    } else {
                        $formattedEvaluations[] = [
                            'P' => 0, 'Pr' => 0, 'A' => 0, 'E' => 0, 'Ex' => 0, 'Prom' => 0
                        ];
                    }
                }

                // Calcular promedio final
                $promedioFinal = $firstGrade->promedio_final ?? $this->calculateFinalAverage($formattedEvaluations);

                $gradesBySubject[$subject->id] = [
                    'grade_id' => $firstGrade->id,
                    'subject_name' => $subject->name,
                    'evaluations' => $formattedEvaluations,
                    'score' => round($promedioFinal, 2),
                    'estado' => $this->getEstado($promedioFinal),
                    'faltantes' => $this->getPuntosFaltantes($promedioFinal),
                ];
            } else {
                // Materia sin calificaciones
                $gradesBySubject[$subject->id] = [
                    'grade_id' => null,
                    'subject_name' => $subject->name,
                    'evaluations' => [
                        ['P' => 0, 'Pr' => 0, 'A' => 0, 'E' => 0, 'Ex' => 0, 'Prom' => 0],
                        ['P' => 0, 'Pr' => 0, 'A' => 0, 'E' => 0, 'Ex' => 0, 'Prom' => 0],
                        ['P' => 0, 'Pr' => 0, 'A' => 0, 'E' => 0, 'Ex' => 0, 'Prom' => 0],
                        ['P' => 0, 'Pr' => 0, 'A' => 0, 'E' => 0, 'Ex' => 0, 'Prom' => 0],
                    ],
                    'score' => 0,
                    'estado' => 'Sin calificación',
                    'faltantes' => 7,
                ];
            }
        }

        $data = [
            'id' => $student->id,
            'matricula' => $student->matricula,
            'nombre' => trim($student->nombre . ' ' . $student->apellido_paterno . ' ' . $student->apellido_materno),
            'group' => $student->group->name ?? 'Sin grupo',
            'grades_by_subject' => $gradesBySubject,
        ];

        return $this->successResponse($data, 'Student grades retrieved successfully');
    }

    /**
     * Crear o actualizar calificaciones de una materia
     */
    public function storeOrUpdateGrade(Request $request, $studentId): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'evaluations' => 'required|array|size:4',
            'evaluations.*.P' => 'required|numeric|min:0|max:10',
            'evaluations.*.Pr' => 'required|numeric|min:0|max:10',
            'evaluations.*.A' => 'required|numeric|min:0|max:10',
            'evaluations.*.E' => 'required|numeric|min:0|max:10',
            'evaluations.*.Ex' => 'required|numeric|min:0|max:10',
        ]);

        $student = Student::find($studentId);
        if (!$student) {
            return $this->notFoundResponse('Student not found');
        }

        // Calcular promedios de cada evaluación
        $evaluations = [];
        foreach ($validated['evaluations'] as $eval) {
            $prom = $this->calculateEvaluationAverage($eval);
            $evaluations[] = array_merge($eval, ['Prom' => $prom]);
        }

        // Calcular promedio final
        $promedioFinal = $this->calculateFinalAverage($evaluations);
        $estado = $this->getEstado($promedioFinal);
        $faltantes = $this->getPuntosFaltantes($promedioFinal);

        // Buscar si ya existe una calificación
        $grade = Grade::where('student_id', $studentId)
                     ->where('subject_id', $validated['subject_id'])
                     ->first();

        if ($grade) {
            // Actualizar
            $grade->update([
                'evaluations' => $evaluations,
                'promedio_final' => $promedioFinal,
                'estado' => $estado,
                'faltantes' => 0,
                'puntos_faltantes' => $faltantes,
            ]);
        } else {
            // Crear
            $grade = Grade::create([
                'student_id' => $studentId,
                'subject_id' => $validated['subject_id'],
                'evaluations' => $evaluations,
                'promedio_final' => $promedioFinal,
                'estado' => $estado,
                'faltantes' => 0,
                'puntos_faltantes' => $faltantes,
            ]);
        }

        return $this->successResponse($grade, 'Grade saved successfully', 201);
    }

    /**
     * Eliminar calificación de una materia
     */
    public function deleteGrade($studentId, $subjectId): JsonResponse
    {
        $grade = Grade::where('student_id', $studentId)
                     ->where('subject_id', $subjectId)
                     ->first();

        if (!$grade) {
            return $this->notFoundResponse('Grade not found');
        }

        $grade->delete();

        return $this->successResponse(null, 'Grade deleted successfully');
    }

    // Métodos auxiliares privados

    private function calculateEvaluationAverage($evaluation): float
    {
        if (empty($evaluation)) {
            return 0;
        }

        $sum = ($evaluation['P'] ?? 0) + ($evaluation['Pr'] ?? 0) + 
               ($evaluation['A'] ?? 0) + ($evaluation['E'] ?? 0) + 
               ($evaluation['Ex'] ?? 0);
        
        return round($sum / 5, 2);
    }

    private function calculateFinalAverage($evaluations): float
    {
        $validPromedios = array_filter(array_column($evaluations, 'Prom'), function($p) {
            return $p > 0;
        });

        if (empty($validPromedios)) {
            return 0;
        }

        return round(array_sum($validPromedios) / count($validPromedios), 2);
    }

    private function getEstado($promedio): string
    {
        if ($promedio == 0) {
            return 'Sin calificación';
        } elseif ($promedio >= 7) {
            return 'Aprobado';
        } elseif ($promedio >= 5) {
            return 'Riesgo';
        } else {
            return 'Reprobado';
        }
    }

    private function getPuntosFaltantes($promedio): float
    {
        if ($promedio >= 7) {
            return 0;
        }
        return round(7 - $promedio, 2);
    }
}

