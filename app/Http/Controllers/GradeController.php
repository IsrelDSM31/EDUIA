<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Rubric;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GradesExport;
use App\Imports\GradesImport;
use App\Models\ChangeLog;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    public function index()
    {
        $students = Student::with(['grades.subject'])->get();
        $subjects = Subject::all();
        $rubrics = Rubric::all();

        // Array asociativo id => nombre
        $subjectsById = [];
        foreach ($subjects as $subject) {
            $subjectsById[$subject->id] = $subject->name;
        }

        $studentsGrades = $students->map(function ($student) use ($subjects, $subjectsById) {
            $allSubjectsGrades = [];
            foreach ($subjects as $subject) {
                $allSubjectsGrades[$subject->id] = [
                    'id' => null,
                    'subject_id' => $subject->id,
                    'subject_name' => $subject->name,
                    'evaluations' => [
                        ['teamwork' => 0, 'project' => 0, 'attendance' => 0, 'exam' => 0, 'extra' => 0],
                        ['teamwork' => 0, 'project' => 0, 'attendance' => 0, 'exam' => 0, 'extra' => 0],
                        ['teamwork' => 0, 'project' => 0, 'attendance' => 0, 'exam' => 0, 'extra' => 0],
                        ['teamwork' => 0, 'project' => 0, 'attendance' => 0, 'exam' => 0, 'extra' => 0]
                    ],
                    'score' => 0,
                    'estado' => 'Pendiente',
                    'faltantes' => 7,
                ];
            }

            // Agrupar calificaciones por materia
            $groupedGrades = $this->groupGradesBySubject($student->grades);

            foreach ($groupedGrades as $subjectId => $gradeData) {
                if (isset($allSubjectsGrades[$subjectId])) {
                    $allSubjectsGrades[$subjectId] = [
                        'id' => $gradeData['id'],
                        'subject_id' => $subjectId,
                        'subject_name' => $subjectsById[$subjectId] ?? 'Sin nombre',
                        'evaluations' => $gradeData['evaluations'],
                        'score' => $gradeData['score'] ?? 0,
                        'estado' => $this->getEstado($gradeData['score'] ?? 0),
                        'faltantes' => $this->getFaltantes($gradeData['score'] ?? 0),
                    ];
                }
            }

            return [
                'id' => $student->id,
                'matricula' => $student->matricula,
                'nombre' => $student->nombre,
                'apellido_paterno' => $student->apellido_paterno,
                'apellido_materno' => $student->apellido_materno,
                'grades_by_subject' => $allSubjectsGrades,
            ];
        });

        \Log::info('STUDENTS GRADES SENT', $studentsGrades->toArray());

        return Inertia::render('Grades', [
            'grades' => $studentsGrades,
            'subjects' => $subjects,
            'rubrics' => $rubrics,
        ]);
    }

    /**
     * Agrupa las calificaciones por materia y crea el array de evaluaciones
     */
    private function groupGradesBySubject($grades)
    {
        $grouped = [];
        
        // Primero, agrupar por materia
        foreach ($grades as $grade) {
            $subjectId = $grade->subject_id;
            
            if (!isset($grouped[$subjectId])) {
                $grouped[$subjectId] = [
                    'id' => $grade->id,
                    'grades' => []
                ];
            }
            
            $grouped[$subjectId]['grades'][] = $grade;
        }
        
        // Ahora procesar cada materia
        $result = [];
        foreach ($grouped as $subjectId => $data) {
            // Ordenar las calificaciones por ID para mantener el orden cronológico
            $sortedGrades = collect($data['grades'])->sortBy('id')->values();
            
            $evaluations = [
                ['teamwork' => 0, 'project' => 0, 'attendance' => 0, 'exam' => 0, 'extra' => 0],
                ['teamwork' => 0, 'project' => 0, 'attendance' => 0, 'exam' => 0, 'extra' => 0],
                ['teamwork' => 0, 'project' => 0, 'attendance' => 0, 'exam' => 0, 'extra' => 0],
                ['teamwork' => 0, 'project' => 0, 'attendance' => 0, 'exam' => 0, 'extra' => 0]
            ];
            
            $totalScore = 0;
            $validEvaluations = 0;
            
            // Si hay evaluaciones guardadas en formato JSON, usarlas
            if (!empty($sortedGrades)) {
                $firstGrade = $sortedGrades[0];
                $savedEvaluations = $firstGrade->evaluations ?? [];
                
                // Si hay evaluaciones guardadas en JSON, usarlas
                if (is_array($savedEvaluations) && count($savedEvaluations) > 0) {
                    // Tomar hasta 4 evaluaciones del JSON
                    for ($i = 0; $i < min(4, count($savedEvaluations)); $i++) {
                        $eval = $savedEvaluations[$i] ?? [];
                        
                        // Mapear del formato guardado (P, Pr, A, E, Ex) al formato esperado
                        $teamwork = $this->parseGradeValue($eval['P'] ?? $eval['teamwork'] ?? 0);
                        $project = $this->parseGradeValue($eval['Pr'] ?? $eval['project'] ?? 0);
                        $attendance = $this->parseGradeValue($eval['A'] ?? $eval['attendance'] ?? 0);
                        $exam = $this->parseGradeValue($eval['E'] ?? $eval['exam'] ?? 0);
                        $extra = $this->parseGradeValue($eval['Ex'] ?? $eval['extra'] ?? 0);
                        
                        $evaluations[$i] = [
                            'teamwork' => $teamwork,
                            'project' => $project,
                            'attendance' => $attendance,
                            'exam' => $exam,
                            'extra' => $extra
                        ];
                        
                        // Calcular el promedio de esta evaluación
                        $validValues = array_filter([$teamwork, $project, $attendance, $exam, $extra], function($v) {
                            return is_numeric($v) && $v > 0;
                        });
                        
                        if (count($validValues) > 0) {
                            $evaluationScore = array_sum($validValues) / count($validValues);
                            $totalScore += $evaluationScore;
                            $validEvaluations++;
                        }
                    }
                } else {
                    // Fallback: leer de campos directos si existen
                    for ($i = 0; $i < min(4, count($sortedGrades)); $i++) {
                        $grade = $sortedGrades[$i];
                        
                        $evaluations[$i] = [
                            'teamwork' => (float)($grade->teamwork ?? 0),
                            'project' => (float)($grade->project ?? 0),
                            'attendance' => (float)($grade->attendance ?? 0),
                            'exam' => (float)($grade->exam ?? 0),
                            'extra' => (float)($grade->extra ?? 0)
                        ];
                        
                        // Calcular el promedio de esta evaluación
                        $evaluationScore = ($grade->teamwork + $grade->project + $grade->attendance + $grade->exam + $grade->extra) / 5;
                        $totalScore += $evaluationScore;
                        $validEvaluations++;
                    }
                }
            }
            
            // Calcular score final usando promedio_final si está disponible
            $finalScore = 0;
            if (!empty($sortedGrades)) {
                $firstGrade = $sortedGrades[0];
                if (isset($firstGrade->promedio_final) && $firstGrade->promedio_final > 0) {
                    $finalScore = $firstGrade->promedio_final;
                } else {
                    $finalScore = $validEvaluations > 0 ? $totalScore / $validEvaluations : 0;
                }
            }
            
            $result[$subjectId] = [
                'id' => $data['id'],
                'evaluations' => $evaluations,
                'score' => $finalScore
            ];
        }
        
        return $result;
    }

    /**
     * Parsea un valor de calificación que puede ser string, número, o '-'
     */
    private function parseGradeValue($value)
    {
        if ($value === null || $value === '' || $value === '-') {
            return 0;
        }
        if (is_numeric($value)) {
            return (float)$value;
        }
        if (is_string($value)) {
            $cleaned = str_replace(',', '.', $value);
            return is_numeric($cleaned) ? (float)$cleaned : 0;
        }
        return 0;
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'sometimes|exists:subjects,id', // Opcional
            'evaluations' => 'required|array',
        ]);

        $studentId = $request->student_id;
        $subjectId = $request->subject_id;

        // Si no se provee subject_id, se crea para todas las materias
        if (!$subjectId) {
            $subjects = Subject::all();
            $grades = [];
            foreach ($subjects as $subject) {
                $grade = $this->createOrUpdateGrade($studentId, $subject->id, $request->evaluations);
                if ($grade) {
                    $grades[] = $grade;
                    // Registro en bitácora para cada calificación creada
                    ChangeLog::create([
                        'user_id' => Auth::id(),
                        'model_type' => Grade::class,
                        'model_id' => $grade->id,
                        'action' => 'create',
                        'changes' => [
                            'after' => $grade->toArray(),
                        ],
                    ]);
                }
            }
            $message = 'Calificaciones iniciales creadas para todas las materias.';
            return response()->json([
                'message' => $message,
                'success' => true,
                'grades' => $grades,
            ]);
        } else {
            $grade = $this->createOrUpdateGrade($studentId, $subjectId, $request->evaluations);
            $message = 'Calificaciones guardadas correctamente.';
            
            // Registro en bitácora para creación
            if ($grade) {
                ChangeLog::create([
                    'user_id' => Auth::id(),
                    'model_type' => Grade::class,
                    'model_id' => $grade->id,
                    'action' => 'create',
                    'changes' => [
                        'after' => $grade->toArray(),
                    ],
                ]);
            }
        }

        return response()->json([
            'message' => $message,
            'success' => true,
            'grade' => $grade,
        ]);
    }

    private function createOrUpdateGrade($studentId, $subjectId, $evaluationsData)
    {
        // Procesar y validar cada evaluación
        $evaluations = [];
        foreach ($evaluationsData as $eval) {
            $evaluations[] = [
                'P' => is_numeric($eval['P'] ?? null) ? number_format((float)$eval['P'], 2) : '0.00',
                'Pr' => is_numeric($eval['Pr'] ?? null) ? number_format((float)$eval['Pr'], 2) : '0.00',
                'A' => is_numeric($eval['A'] ?? null) ? number_format((float)$eval['A'], 2) : '0.00',
                'E' => is_numeric($eval['E'] ?? null) ? number_format((float)$eval['E'], 2) : '0.00',
                'Ex' => is_numeric($eval['Ex'] ?? null) ? number_format((float)$eval['Ex'], 2) : '0.00',
                'Prom' => is_numeric($eval['Prom'] ?? null) ? number_format((float)$eval['Prom'], 2) : '0.00'
            ];
        }
        // Asegurar que siempre haya 4 evaluaciones (una por unidad)
        while (count($evaluations) < 4) {
            $evaluations[] = [
                'P' => '0.00', 'Pr' => '0.00', 'A' => '0.00', 'E' => '0.00', 'Ex' => '0.00', 'Prom' => '0.00'
            ];
        }
        if (count($evaluations) > 4) {
            $evaluations = array_slice($evaluations, 0, 4);
        }
        // Calcular promedio final solo de los promedios que no son '0.00'
        $promedios = array_map(function($eval) {
            return $eval['Prom'] !== '0.00' ? (float)$eval['Prom'] : 0;
        }, $evaluations);
        $promedioFinal = 0;
        $promediosValidos = array_filter($promedios);
        if (count($promediosValidos) > 0) {
            $promedioFinal = array_sum($promediosValidos) / count($promediosValidos);
        }
        // Determinar estado y puntos faltantes
        $estado = $promedioFinal >= 7 ? 'Aprobado' : 'Reprobado';
        $puntosFaltantes = $promedioFinal >= 7 ? 0 : 7 - $promedioFinal;
        // Guardar en la base de datos
        $grade = Grade::updateOrCreate(
            ['student_id' => $studentId, 'subject_id' => $subjectId],
            [
                'evaluations' => $evaluations,
                'promedio_final' => $promedioFinal,
                'estado' => $estado,
                'puntos_faltantes' => $puntosFaltantes,
            ]
        );
        
        return $grade;
    }

    public function storeRubric(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'criteria' => 'required|array',
            'criteria.*.name' => 'required|string',
            'criteria.*.levels' => 'required|array',
            'criteria.*.levels.*.name' => 'required|string',
            'criteria.*.levels.*.score' => 'required|numeric|min:0|max:10',
        ]);

        $rubric = Rubric::create([
            'name' => $request->name,
            'description' => $request->description,
            'criteria' => $request->criteria,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'rubric' => $rubric]);
        }
        return redirect()->back()->with('success', 'Rúbrica creada correctamente.');
    }

    public function show($id)
    {
        $grade = Grade::with(['student', 'subject'])->findOrFail($id);
        return response()->json($grade);
    }

    public function update(Request $request, Grade $grade)
    {
        $request->validate([
            'evaluations' => 'required|array',
        ]);

        $oldData = $grade->toArray();
        // Procesar y validar cada evaluación
        $evaluations = [];
        foreach ($request->evaluations as $eval) {
            $evaluations[] = [
                'P' => is_numeric($eval['P'] ?? null) ? number_format((float)$eval['P'], 2) : '0.00',
                'Pr' => is_numeric($eval['Pr'] ?? null) ? number_format((float)$eval['Pr'], 2) : '0.00',
                'A' => is_numeric($eval['A'] ?? null) ? number_format((float)$eval['A'], 2) : '0.00',
                'E' => is_numeric($eval['E'] ?? null) ? number_format((float)$eval['E'], 2) : '0.00',
                'Ex' => is_numeric($eval['Ex'] ?? null) ? number_format((float)$eval['Ex'], 2) : '0.00',
                'Prom' => is_numeric($eval['Prom'] ?? null) ? number_format((float)$eval['Prom'], 2) : '0.00'
            ];
        }
        
        // Asegurar que siempre haya 4 evaluaciones
        while (count($evaluations) < 4) {
            $evaluations[] = [
                'P' => '0.00', 'Pr' => '0.00', 'A' => '0.00', 'E' => '0.00', 'Ex' => '0.00', 'Prom' => '0.00'
            ];
        }
        if (count($evaluations) > 4) {
            $evaluations = array_slice($evaluations, 0, 4);
        }

        // Calcular promedio final solo de los promedios que no son '0.00'
        $promedios = array_map(function($eval) {
            return ($eval['Prom'] !== '0.00' && $eval['Prom'] !== '-') ? (float)$eval['Prom'] : 0;
        }, $evaluations);

        $promedioFinal = 0;
        $promediosValidos = array_filter($promedios);
        if (count($promediosValidos) > 0) {
            $promedioFinal = array_sum($promediosValidos) / count($promediosValidos);
        }

        // Determinar estado y puntos faltantes
        $estado = $promedioFinal >= 7 ? 'Aprobado' : 'Reprobado';
        $puntosFaltantes = $promedioFinal >= 7 ? 0 : 7 - $promedioFinal;

        // Actualizar en la base de datos
        $grade->update([
            'evaluations' => $evaluations,
            'promedio_final' => $promedioFinal,
            'estado' => $estado,
            'puntos_faltantes' => $puntosFaltantes,
        ]);
        // Registro en bitácora
        ChangeLog::create([
            'user_id' => Auth::id(),
            'model_type' => Grade::class,
            'model_id' => $grade->id,
            'action' => 'update',
            'changes' => [
                'before' => $oldData,
                'after' => $grade->fresh()->toArray(),
            ],
        ]);
        return response()->json([
            'message' => 'Calificaciones actualizadas correctamente',
            'grade' => $grade,
            'success' => true
        ]);
    }

    public function destroy($id)
    {
        $grade = Grade::findOrFail($id);
        $oldData = $grade->toArray();
        $grade->delete();
        // Registro en bitácora
        ChangeLog::create([
            'user_id' => Auth::id(),
            'model_type' => Grade::class,
            'model_id' => $grade->id,
            'action' => 'delete',
            'changes' => [
                'before' => $oldData,
                'after' => null,
            ],
        ]);
        return response()->json(['success' => true]);
    }

    private function getEstado($score)
    {
        if ($score >= 7) return 'Aprobado';
        if ($score >= 6) return 'Riesgo';
        return 'Reprobado';
    }
    private function getFaltantes($score)
    {
        if ($score >= 7) return 0;
        return number_format(max(0, 7 - $score), 2);
    }

    public function export()
    {
        return Excel::download(new GradesExport, 'calificaciones.xlsx', \Maatwebsite\Excel\Excel::XLSX, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="calificaciones.xlsx"'
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);
        Excel::import(new GradesImport, $request->file('file'));
        return back()->with('success', 'Importación de calificaciones completada');
    }
}
