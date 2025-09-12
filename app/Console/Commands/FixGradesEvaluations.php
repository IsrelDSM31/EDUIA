<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Grade;

class FixGradesEvaluations extends Command
{
    protected $signature = 'grades:fix-evaluations';
    protected $description = 'Pobla el campo evaluations en grades usando los valores actuales de teamwork, project, attendance, exam, extra';

    public function handle()
    {
        $grades = \App\Models\Grade::orderBy('student_id')->orderBy('subject_id')->orderBy('date')->get();
        $grouped = [];
        foreach ($grades as $grade) {
            $key = $grade->student_id . '-' . $grade->subject_id;
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $grade;
        }
        $updated = 0;
        foreach ($grouped as $key => $gradeGroup) {
            $evaluations = [];
            foreach ($gradeGroup as $g) {
                $evaluations[] = [
                    'teamwork' => $g->teamwork ?? 0,
                    'project' => $g->project ?? 0,
                    'attendance' => $g->attendance ?? 0,
                    'exam' => $g->exam ?? 0,
                    'extra' => $g->extra ?? 0,
                ];
                if (count($evaluations) >= 4) break;
            }
            // Completar hasta 4 evaluaciones
            while (count($evaluations) < 4) {
                $evaluations[] = [
                    'teamwork' => 0,
                    'project' => 0,
                    'attendance' => 0,
                    'exam' => 0,
                    'extra' => 0,
                ];
            }
            // Usar el primer registro como base
            $main = $gradeGroup[0];
            $main->evaluations = $evaluations;
            $main->save();
            // Opcional: eliminar los demás registros (solo si quieres dejar uno por alumno/materia)
            for ($i = 1; $i < count($gradeGroup); $i++) {
                // $gradeGroup[$i]->delete(); // Descomenta si quieres eliminar duplicados
            }
            $updated++;
        }
        $this->info("Registros fusionados y actualizados: $updated");
    }
} 