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
            
            // Tomar las primeras 4 evaluaciones
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
            
            $result[$subjectId] = [
                'id' => $data['id'],
                'evaluations' => $evaluations,
                'score' => $validEvaluations > 0 ? $totalScore / $validEvaluations : 0
            ];
        }
        
        return $result;
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
            foreach ($subjects as $subject) {
                $this->createOrUpdateGrade($studentId, $subject->id, $request->evaluations);
            }
            $message = 'Calificaciones iniciales creadas para todas las materias.';
            $grade = null;
        } else {
            $grade = $this->createOrUpdateGrade($studentId, $subjectId, $request->evaluations);
            $message = 'Calificaciones guardadas correctamente.';
        }

        // Registro en bitácora para creación
        ChangeLog::create([
            'user_id' => Auth::id(),
            'model_type' => Grade::class,
            'model_id' => $grade->id,
            'action' => 'create',
            'changes' => [
                'after' => $grade->toArray(),
            ],
        ]);

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
        return Grade::updateOrCreate(
            ['student_id' => $studentId, 'subject_id' => $subjectId],
            [
                'evaluations' => $evaluations,
                'promedio_final' => $promedioFinal,
                'estado' => $estado,
                'puntos_faltantes' => $puntosFaltantes,
            ]
        );
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
                'P' => is_numeric($eval['P']) ? number_format((float)$eval['P'], 2) : '-',
                'Pr' => is_numeric($eval['Pr']) ? number_format((float)$eval['Pr'], 2) : '-',
                'A' => is_numeric($eval['A']) ? number_format((float)$eval['A'], 2) : '-',
                'E' => is_numeric($eval['E']) ? number_format((float)$eval['E'], 2) : '-',
                'Ex' => is_numeric($eval['Ex']) ? number_format((float)$eval['Ex'], 2) : '-',
                'Prom' => is_numeric($eval['Prom']) ? number_format((float)$eval['Prom'], 2) : '-'
            ];
        }

        // Calcular promedio final solo de los promedios que no son '-'
        $promedios = array_map(function($eval) {
            return $eval['Prom'] !== '-' ? (float)$eval['Prom'] : 0;
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
