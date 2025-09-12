<?php

namespace App\Imports;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
// use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;
// use Maatwebsite\Excel\Concerns\WithValidation;

// class GradesImport implements ToModel, WithHeadingRow, WithValidation
class GradesImport
{
    public function model(array $row)
    {
        // Buscar estudiante por matrícula
        $student = Student::where('matricula', $row['matricula'])->first();
        
        // Buscar materia por nombre
        $subject = Subject::where('nombre', $row['materia'])->first();
        
        if (!$student || !$subject) {
            return null;
        }

        return new Grade([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'promedio_final' => $row['promedio_final'] ?? 0,
            'evaluations' => [
                'parcial1' => $row['parcial1'] ?? 0,
                'parcial2' => $row['parcial2'] ?? 0,
                'parcial3' => $row['parcial3'] ?? 0,
                'final' => $row['final'] ?? 0,
            ],
            'estado' => $row['estado'] ?? 'Pendiente',
            'faltantes' => $row['faltantes'] ?? 0,
            'puntos_faltantes' => $row['puntos_faltantes'] ?? 0,
            'date' => $row['fecha'] ?? now(),
        ]);
    }

    public function rules(): array
    {
        return [
            'matricula' => 'required|exists:students,matricula',
            'materia' => 'required|exists:subjects,nombre',
            'promedio_final' => 'nullable|numeric|min:0|max:10',
            'parcial1' => 'nullable|numeric|min:0|max:10',
            'parcial2' => 'nullable|numeric|min:0|max:10',
            'parcial3' => 'nullable|numeric|min:0|max:10',
            'final' => 'nullable|numeric|min:0|max:10',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'matricula.exists' => 'La matrícula no existe en el sistema.',
            'materia.exists' => 'La materia no existe en el sistema.',
        ];
    }
} 