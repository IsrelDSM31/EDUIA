<?php

namespace App\Imports;

use App\Models\Student;
// use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;

// class StudentsImport implements ToModel, WithHeadingRow
class StudentsImport
{
    public function model(array $row)
    {
        return new Student([
            'matricula' => $row['matrícula'] ?? $row['matricula'] ?? '',
            'nombre' => $row['nombre'] ?? '',
            'apellido_paterno' => $row['apellido_paterno'] ?? '',
            'apellido_materno' => $row['apellido_materno'] ?? '',
            'group_id' => $row['grupo'] ?? $row['group_id'] ?? null,
            'birth_date' => $row['fecha_de_nacimiento'] ?? $row['birth_date'] ?? null,
        ]);
    }
} 