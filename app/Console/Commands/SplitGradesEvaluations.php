<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;

class SplitGradesEvaluations extends Command
{
    protected $signature = 'grades:split-evaluations';
    protected $description = 'Separa cada evaluación del campo evaluations en registros independientes en la tabla grades, sin borrar los originales.';

    public function handle()
    {
        $grades = Grade::whereNotNull('evaluations')->get();
        $created = 0;
        foreach ($grades as $grade) {
            $evals = $grade->evaluations;
            if (!is_array($evals)) continue;
            foreach ($evals as $idx => $eval) {
                $evalType = 'E' . ($idx + 1);
                // Evitar duplicados: solo crear si no existe ya un registro migrado para este alumno, materia y tipo
                $exists = Grade::where('student_id', $grade->student_id)
                    ->where('subject_id', $grade->subject_id)
                    ->where('evaluation_type', $evalType)
                    ->where('migrado_split', 1)
                    ->exists();
                if ($exists) continue;
                $new = $grade->replicate(['id', 'created_at', 'updated_at']);
                $new->evaluation_type = $evalType;
                $new->teamwork = $eval['teamwork'] ?? null;
                $new->project = $eval['project'] ?? null;
                $new->attendance = $eval['attendance'] ?? null;
                $new->exam = $eval['exam'] ?? null;
                $new->extra = $eval['extra'] ?? null;
                $new->score = $eval['score'] ?? null;
                $new->evaluations = null;
                $new->migrado_split = 1;
                $new->save();
                $created++;
            }
        }
        $this->info("Nuevos registros creados: $created");
    }
} 