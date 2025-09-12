<?php

namespace App\Exports;

use App\Models\Student;
// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentsExport // implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Student::select('matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'group_id', 'birth_date')->get();
    }

    public function headings(): array
    {
        return [
            'Matrícula',
            'Nombre',
            'Apellido Paterno',
            'Apellido Materno',
            'Grupo',
            'Fecha de Nacimiento',
        ];
    }
} 