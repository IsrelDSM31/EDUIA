<?php

namespace App\Exports;

use App\Models\Grade;
// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\WithHeadings;

class GradesExport // implements FromCollection, WithHeadings
{
    public function collection()
    {
        $grades = Grade::with(['student', 'subject'])->get();
        return $grades->map(function($grade) {
            $evals = $grade->evaluations ?? [];
            $getEval = function($i, $key) use ($evals) {
                return isset($evals[$i][$key]) ? $evals[$i][$key] : '';
            };
            return [
                'matricula' => $grade->student->matricula ?? '',
                'nombre' => $grade->student->nombre ?? '',
                'materia' => $grade->subject->name ?? '',
                'eval1_teamwork' => $getEval(0, 'teamwork'),
                'eval1_project' => $getEval(0, 'project'),
                'eval1_attendance' => $getEval(0, 'attendance'),
                'eval1_exam' => $getEval(0, 'exam'),
                'eval1_extra' => $getEval(0, 'extra'),
                'eval2_teamwork' => $getEval(1, 'teamwork'),
                'eval2_project' => $getEval(1, 'project'),
                'eval2_attendance' => $getEval(1, 'attendance'),
                'eval2_exam' => $getEval(1, 'exam'),
                'eval2_extra' => $getEval(1, 'extra'),
                'eval3_teamwork' => $getEval(2, 'teamwork'),
                'eval3_project' => $getEval(2, 'project'),
                'eval3_attendance' => $getEval(2, 'attendance'),
                'eval3_exam' => $getEval(2, 'exam'),
                'eval3_extra' => $getEval(2, 'extra'),
                'eval4_teamwork' => $getEval(3, 'teamwork'),
                'eval4_project' => $getEval(3, 'project'),
                'eval4_attendance' => $getEval(3, 'attendance'),
                'eval4_exam' => $getEval(3, 'exam'),
                'eval4_extra' => $getEval(3, 'extra'),
                'promedio' => $grade->score ?? '',
                'estado' => $grade->score >= 7 ? 'Aprobado' : ($grade->score >= 6 ? 'Riesgo' : 'Reprobado'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Matrícula',
            'Nombre',
            'Materia',
            'Eval1 Trabajo en Equipo',
            'Eval1 Proyecto',
            'Eval1 Asistencia',
            'Eval1 Examen',
            'Eval1 Extra',
            'Eval2 Trabajo en Equipo',
            'Eval2 Proyecto',
            'Eval2 Asistencia',
            'Eval2 Examen',
            'Eval2 Extra',
            'Eval3 Trabajo en Equipo',
            'Eval3 Proyecto',
            'Eval3 Asistencia',
            'Eval3 Examen',
            'Eval3 Extra',
            'Eval4 Trabajo en Equipo',
            'Eval4 Proyecto',
            'Eval4 Asistencia',
            'Eval4 Examen',
            'Eval4 Extra',
            'Promedio',
            'Estado',
        ];
    }
} 