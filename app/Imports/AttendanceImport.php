<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\Student;
// use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;

// class AttendanceImport implements ToModel, WithHeadingRow
class AttendanceImport
{
    public function model(array $row)
    {
        $student = Student::where('matricula', $row['matricula'])->first();
        
        if (!$student) {
            return null;
        }

        return new Attendance([
            'student_id' => $student->id,
            'fecha' => $row['fecha'] ?? now(),
            'estado' => $row['estado'] ?? 'presente',
            'observaciones' => $row['observaciones'] ?? '',
        ]);
    }
} 