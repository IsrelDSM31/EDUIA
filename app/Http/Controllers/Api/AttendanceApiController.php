<?php

namespace App\Http\Controllers\Api;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Attendance",
 *     description="API Endpoints para gestión de asistencia"
 * )
 */
class AttendanceApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/attendance",
     *     summary="Obtener lista de asistencias",
     *     tags={"Attendance"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="student_id",
     *         in="query",
     *         description="Filtrar por estudiante",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filtrar por fecha",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por estado (presente, ausente, tardanza)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (student)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de asistencias obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Attendance records retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Attendance")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Attendance::with(['student', 'student.group', 'subject']);

        // Filtros
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $attendance = $query->latest('date')->paginate($request->get('per_page', 50));

        // Transformar los datos para la app móvil
        $transformedData = $attendance->getCollection()->map(function ($item) {
            return [
                'id' => $item->id,
                'student_id' => $item->student_id,
                'student_name' => $item->student 
                    ? trim($item->student->nombre . ' ' . $item->student->apellido_paterno . ' ' . $item->student->apellido_materno)
                    : 'Estudiante',
                'student_code' => $item->student->matricula ?? 'N/A',
                'subject_id' => $item->subject_id,
                'subject_name' => $item->subject->name ?? 'Sin materia',
                'status' => $item->status,
                'date' => $item->date,
                'time' => $item->created_at->format('H:i'),
                'justification_type' => $item->justification_type,
                'justification_document' => $item->justification_document,
                'observations' => $item->observations,
            ];
        });

        $attendance->setCollection($transformedData);

        return $this->successResponse($attendance, 'Attendance records retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/attendance/{id}",
     *     summary="Obtener asistencia específica",
     *     tags={"Attendance"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la asistencia",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (student)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asistencia obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Attendance record retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Attendance")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Asistencia no encontrada"
     *     )
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Attendance::query();

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $attendance = $query->find($id);

        if (!$attendance) {
            return $this->notFoundResponse('Attendance record not found');
        }

        return $this->successResponse($attendance, 'Attendance record retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/attendance",
     *     summary="Crear nueva asistencia",
     *     tags={"Attendance"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id","fecha","estado"},
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(property="fecha", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="estado", type="string", enum={"presente","ausente","tardanza"}, example="presente"),
     *             @OA\Property(property="observaciones", type="string", example="Llegó a tiempo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Asistencia creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Attendance record created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Attendance")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'fecha' => 'nullable|date',
            'date' => 'nullable|date',
            'estado' => 'nullable|in:presente,ausente,tardanza,justificado',
            'status' => 'nullable|in:presente,ausente,tardanza,justificado,present,absent,late,justified',
            'observaciones' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        // Normalizar datos (aceptar español e inglés)
        $data = [
            'student_id' => $validated['student_id'],
            'subject_id' => $validated['subject_id'],
            'date' => $validated['date'] ?? $validated['fecha'] ?? now()->toDateString(),
            'status' => $validated['status'] ?? $validated['estado'] ?? 'presente',
            'observations' => $validated['observations'] ?? $validated['observaciones'] ?? null,
        ];

        $attendance = Attendance::create($data);

        return $this->successResponse($attendance, 'Attendance record created successfully', 201);
    }

    /**
     * Registrar asistencia para múltiples estudiantes
     */
    public function bulkStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'fecha' => 'nullable|date',
            'date' => 'nullable|date',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.estado' => 'nullable|in:presente,ausente,tardanza,justificado',
            'attendances.*.status' => 'nullable|in:presente,ausente,tardanza,justificado,present,absent,late,justified',
            'attendances.*.observaciones' => 'nullable|string',
            'attendances.*.observations' => 'nullable|string',
        ]);

        $date = $validated['date'] ?? $validated['fecha'] ?? now()->toDateString();

        $created = [];
        foreach ($validated['attendances'] as $attendanceData) {
            $created[] = Attendance::create([
                'student_id' => $attendanceData['student_id'],
                'subject_id' => $validated['subject_id'],
                'date' => $date,
                'status' => $attendanceData['status'] ?? $attendanceData['estado'] ?? 'presente',
                'observations' => $attendanceData['observations'] ?? $attendanceData['observaciones'] ?? null,
            ]);
        }

        return $this->successResponse($created, 'Attendance records created successfully', 201);
    }

    /**
     * Justificar una asistencia
     */
    public function justify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'justification_type' => 'required|string',
            'observaciones' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        // Buscar última inasistencia sin justificar
        $attendance = Attendance::where('student_id', $validated['student_id'])
            ->where('subject_id', $validated['subject_id'])
            ->whereIn('status', ['ausente', 'absent'])
            ->whereNull('justification_type')
            ->orderBy('date', 'desc')
            ->first();

        if (!$attendance) {
            return $this->errorResponse('No hay inasistencias para justificar', 404);
        }

        // Agregar justificación
        $attendance->justification_type = $validated['justification_type'];
        $attendance->observations = $validated['observations'] ?? $validated['observaciones'] ?? $attendance->observations;
        $attendance->status = 'justified';

        // Guardar archivo si existe
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('justifications', 'public');
            $attendance->justification_document = $path;
        }

        $attendance->save();

        return $this->successResponse($attendance, 'Attendance justified successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/attendance/{id}",
     *     summary="Actualizar asistencia",
     *     tags={"Attendance"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la asistencia",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(property="fecha", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="estado", type="string", enum={"presente","ausente","tardanza"}, example="presente"),
     *             @OA\Property(property="observaciones", type="string", example="Llegó a tiempo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asistencia actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Attendance record updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Attendance")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Asistencia no encontrada"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return $this->notFoundResponse('Attendance record not found');
        }

        $validated = $request->validate([
            'student_id' => 'sometimes|exists:students,id',
            'fecha' => 'sometimes|date',
            'estado' => 'sometimes|in:presente,ausente,tardanza',
            'observaciones' => 'nullable|string',
        ]);

        $attendance->update($validated);

        return $this->successResponse($attendance, 'Attendance record updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/attendance/{id}",
     *     summary="Eliminar asistencia",
     *     tags={"Attendance"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la asistencia",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asistencia eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Attendance record deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Asistencia no encontrada"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return $this->notFoundResponse('Attendance record not found');
        }

        $attendance->delete();

        return $this->successResponse(null, 'Attendance record deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/attendance/statistics",
     *     summary="Obtener estadísticas de asistencia",
     *     tags={"Attendance"},
     *     @OA\Parameter(
     *         name="student_id",
     *         in="query",
     *         description="Filtrar por estudiante",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Attendance statistics retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_days", type="integer"),
     *                 @OA\Property(property="present", type="integer"),
     *                 @OA\Property(property="absent", type="integer"),
     *                 @OA\Property(property="late", type="integer"),
     *                 @OA\Property(property="attendance_rate", type="number", format="float")
     *             )
     *         )
     *     )
     * )
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = Attendance::query();

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('start_date')) {
            $query->where('fecha', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('fecha', '<=', $request->end_date);
        }

        $attendance = $query->get();

        $totalDays = $attendance->count();
        $present = $attendance->where('estado', 'presente')->count();
        $absent = $attendance->where('estado', 'ausente')->count();
        $late = $attendance->where('estado', 'tardanza')->count();
        $attendanceRate = $totalDays > 0 ? (($present + $late) / $totalDays) * 100 : 0;

        $statistics = [
            'total_days' => $totalDays,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'attendance_rate' => round($attendanceRate, 2),
        ];

        return $this->successResponse($statistics, 'Attendance statistics retrieved successfully');
    }
} 