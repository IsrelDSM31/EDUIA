<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
use App\Models\Attendance;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttendanceManagementApiController extends ApiController
{
    /**
     * Obtener asistencias de un estudiante agrupadas por materia
     */
    public function studentAttendance($studentId): JsonResponse
    {
        $student = Student::with(['group'])->find($studentId);

        if (!$student) {
            return $this->notFoundResponse('Student not found');
        }

        // Obtener todas las materias
        $subjects = Subject::all();

        // Organizar asistencias por materia
        $attendanceBySubject = [];

        foreach ($subjects as $subject) {
            // Buscar asistencias de esta materia para este estudiante
            $attendances = Attendance::where('student_id', $studentId)
                ->where('subject_id', $subject->id)
                ->orderBy('date', 'desc')
                ->get();

            if ($attendances->isNotEmpty()) {
                $attendanceRecords = $attendances->map(function ($att) {
                    return [
                        'id' => $att->id,
                        'date' => $att->date,
                        'status' => $att->status,
                        'justification_type' => $att->justification_type,
                        'justification_document' => $att->justification_document,
                        'observations' => $att->observations,
                    ];
                });

                // Calcular estadísticas
                $total = $attendances->count();
                $present = $attendances->whereIn('status', ['present', 'presente'])->count();
                $absent = $attendances->whereIn('status', ['absent', 'ausente'])->count();
                $late = $attendances->whereIn('status', ['late', 'tarde', 'tardanza'])->count();
                $justified = $attendances->whereIn('status', ['justified', 'justificado'])->count();
                
                $attendanceRate = $total > 0 ? round((($present + $late + $justified) / $total) * 100, 1) : 0;

                $attendanceBySubject[$subject->id] = [
                    'subject_id' => $subject->id,
                    'subject_name' => $subject->name,
                    'records' => $attendanceRecords,
                    'statistics' => [
                        'total' => $total,
                        'present' => $present,
                        'absent' => $absent,
                        'late' => $late,
                        'justified' => $justified,
                        'attendance_rate' => $attendanceRate,
                    ],
                ];
            } else {
                $attendanceBySubject[$subject->id] = [
                    'subject_id' => $subject->id,
                    'subject_name' => $subject->name,
                    'records' => [],
                    'statistics' => [
                        'total' => 0,
                        'present' => 0,
                        'absent' => 0,
                        'late' => 0,
                        'justified' => 0,
                        'attendance_rate' => 0,
                    ],
                ];
            }
        }

        $data = [
            'id' => $student->id,
            'matricula' => $student->matricula,
            'nombre' => trim($student->nombre . ' ' . $student->apellido_paterno . ' ' . $student->apellido_materno),
            'group' => $student->group->name ?? 'Sin grupo',
            'attendance_by_subject' => $attendanceBySubject,
        ];

        return $this->successResponse($data, 'Student attendance retrieved successfully');
    }

    /**
     * Crear o actualizar asistencia de una materia
     */
    public function storeOrUpdate(Request $request, $studentId): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,justified,presente,ausente,tardanza,justificado',
            'observations' => 'nullable|string',
        ]);

        $student = Student::find($studentId);
        if (!$student) {
            return $this->notFoundResponse('Student not found');
        }

        // Buscar si ya existe registro para esta fecha y materia
        $attendance = Attendance::where('student_id', $studentId)
            ->where('subject_id', $validated['subject_id'])
            ->whereDate('date', $validated['date'])
            ->first();

        if ($attendance) {
            // Actualizar
            $attendance->update([
                'status' => $validated['status'],
                'observations' => $validated['observations'] ?? null,
            ]);
        } else {
            // Crear
            $attendance = Attendance::create([
                'student_id' => $studentId,
                'subject_id' => $validated['subject_id'],
                'date' => $validated['date'],
                'status' => $validated['status'],
                'observations' => $validated['observations'] ?? null,
            ]);
        }

        return $this->successResponse($attendance, 'Attendance saved successfully', 201);
    }

    /**
     * Eliminar asistencia
     */
    public function destroy($studentId, $attendanceId): JsonResponse
    {
        $attendance = Attendance::where('student_id', $studentId)
            ->where('id', $attendanceId)
            ->first();

        if (!$attendance) {
            return $this->notFoundResponse('Attendance not found');
        }

        $attendance->delete();

        return $this->successResponse(null, 'Attendance deleted successfully');
    }
}







