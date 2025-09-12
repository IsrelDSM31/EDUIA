<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;

class SeedDemoGrades extends Command
{
    protected $signature = 'demo:seed-grades';
    protected $description = 'Llena las calificaciones de los primeros 25 alumnos con todos los estados y evaluaciones';

    public function handle()
    {
        $estados = ['Aprobado', 'Reprobado', 'Riesgo', 'Extraordinario', 'Baja'];
        $scores = [
            'Aprobado' => [9.5, 9.0, 9.2, 9.7],
            'Reprobado' => [4.0, 3.5, 4.2, 4.1],
            'Riesgo' => [6.2, 6.5, 6.0, 6.1],
            'Extraordinario' => [7.1, 7.0, 7.2, 7.3],
            'Baja' => [0.0, 0.0, 0.0, 0.0]
        ];
        $faltantes = [
            'Aprobado' => 0,
            'Reprobado' => 3,
            'Riesgo' => 0.8,
            'Extraordinario' => 0,
            'Baja' => 7
        ];

        $students = Student::orderBy('id')->take(25)->get();
        $subjects = Subject::all();

        if ($students->count() < 25) {
            $this->error('No hay suficientes alumnos (se requieren al menos 25).');
            return 1;
        }

        foreach ($students as $i => $student) {
            $grupo = intdiv($i, 5); // 0: aprobado, 1: reprobado, ...
            $estado = $estados[$grupo];
            $scoreArr = $scores[$estado];
            $faltan = $faltantes[$estado];

            foreach ($subjects as $subject) {
                $evaluations = [];
                for ($e = 0; $e < 4; $e++) {
                    $score = $scoreArr[$e];
                    $evaluations[] = [
                        'teamwork' => $score,
                        'project' => $score,
                        'attendance' => $score,
                        'exam' => $score,
                        'extra' => $score
                    ];
                }
                Grade::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'subject_id' => $subject->id
                    ],
                    [
                        'evaluation_type' => 'evaluacion',
                        'score' => array_sum($scoreArr) / count($scoreArr),
                        'evaluations' => $evaluations,
                        'estado' => $estado,
                        'faltantes' => $faltan,
                        'period' => 'Primer Semestre',
                        'opportunity' => 'Primera',
                        'competencies' => '',
                        'observations' => '',
                        'feedback' => '',
                        'teamwork' => $scoreArr[0],
                        'project' => $scoreArr[0],
                        'attendance' => $scoreArr[0],
                        'exam' => $scoreArr[0],
                        'extra' => $scoreArr[0],
                        'evaluation_date' => now(),
                    ]
                );
            }
        }
        $this->info('Calificaciones demo generadas correctamente para los primeros 25 alumnos.');
        return 0;
    }
} 