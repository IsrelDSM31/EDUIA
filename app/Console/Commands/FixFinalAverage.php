<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Grade;

class FixFinalAverage extends Command
{
    protected $signature = 'grades:fix-final-average';
    protected $description = 'Corrige el promedio_final de todas las calificaciones según la media de las 4 evaluaciones';

    public function handle()
    {
        $grades = Grade::all();
        $count = 0;

        foreach ($grades as $grade) {
            $evals = is_array($grade->evaluations) ? $grade->evaluations : json_decode($grade->evaluations, true);
            if (is_array($evals) && count($evals) === 4) {
                $proms = [];
                foreach ($evals as $e) {
                    $p = floatval($e['P'] ?? $e['teamwork'] ?? 0);
                    $pr = floatval($e['Pr'] ?? $e['project'] ?? 0);
                    $a = floatval($e['A'] ?? $e['attendance'] ?? 0);
                    $ex = floatval($e['Ex'] ?? $e['extra'] ?? 0);
                    $exam = floatval($e['E'] ?? $e['exam'] ?? 0);
                    $prom = $p*0.3 + $pr*0.3 + $a*0.1 + $exam*0.3 + $ex*0.0;
                    $proms[] = $prom;
                }
                $final = round(array_sum($proms) / 4, 2);
                $grade->promedio_final = $final;
                $grade->save();
                $count++;
            }
        }

        $this->info("Promedios finales corregidos: $count");
    }
}
