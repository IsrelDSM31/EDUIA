<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Subject;

class TestGradesGrouping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:grades-grouping {student_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the new grades grouping function';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $studentId = $this->argument('student_id');
        
        if ($studentId) {
            $students = Student::where('id', $studentId)->get();
        } else {
            $students = Student::with(['grades.subject'])->get();
        }
        
        $subjects = Subject::all();
        $subjectsById = [];
        foreach ($subjects as $subject) {
            $subjectsById[$subject->id] = $subject->name;
        }
        
        foreach ($students as $student) {
            $this->info("=== Estudiante: {$student->nombre} {$student->apellido_paterno} ===");
            
            // Agrupar calificaciones por materia
            $groupedGrades = $this->groupGradesBySubject($student->grades);
            
            foreach ($groupedGrades as $subjectId => $gradeData) {
                $subjectName = $subjectsById[$subjectId] ?? 'Sin nombre';
                $this->info("  Materia: {$subjectName} (ID: {$subjectId})");
                $this->info("  Score promedio: " . number_format($gradeData['score'], 2));
                $this->info("  Evaluaciones:");
                
                foreach ($gradeData['evaluations'] as $index => $evaluation) {
                    $evalNumber = $index + 1;
                    $this->info("    E{$evalNumber}: T={$evaluation['teamwork']}, P={$evaluation['project']}, A={$evaluation['attendance']}, E={$evaluation['exam']}, Ex={$evaluation['extra']}");
                }
                $this->info("");
            }
        }
        
        $this->info("Prueba completada.");
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
} 