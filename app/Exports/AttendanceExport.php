<?php

namespace App\Exports;

use App\Models\Attendance;
// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\WithHeadings;


class AttendanceExport // implements FromCollection, WithHeadings
{
    public function collection()
    {
        $attendances = Attendance::with(['student', 'subject'])->get();
        return $attendances->map(function($attendance) {
            return [
                'matricula' => $attendance->student->matricula ?? '',
                'nombre' => $attendance->student->nombre ?? '',
                'materia' => $attendance->subject->name ?? '',
                'fecha' => $attendance->date ? $attendance->date->format('Y-m-d') : '',
                'estado' => $attendance->status,
                'tipo_justificacion' => $attendance->justification_type ?? '',
                'observaciones' => $attendance->observations ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Matrícula',
            'Nombre',
            'Materia',
            'Fecha',
            'Estado',
            'Tipo de Justificación',
            'Observaciones',
        ];
    }
} 