<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Grade;

class MigrateGradesFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:grades-fields';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra los datos del campo evaluations a los campos individuales';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $grades = Grade::whereNotNull('evaluations')->get();
        $count = 0;

        foreach ($grades as $grade) {
            $eval = $grade->evaluations;
            if (is_array($eval) && count($eval) > 0) {
                // Tomar la primera evaluación (o la que corresponda)
                $first = $eval[0];
                $grade->teamwork = $first['teamwork'] ?? null;
                $grade->project = $first['project'] ?? null;
                $grade->attendance = $first['attendance'] ?? null;
                $grade->exam = $first['exam'] ?? null;
                $grade->extra = $first['extra'] ?? null;
                $grade->save();
                $count++;
            }
        }

        $this->info("Migración completada. Calificaciones actualizadas: $count");
    }
}
