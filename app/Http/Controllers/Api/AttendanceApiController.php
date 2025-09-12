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
        $query = Attendance::query();

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('date')) {
            $query->whereDate('fecha', $request->date);
        }

        if ($request->has('status')) {
            $query->where('estado', $request->status);
        }

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $attendance = $query->paginate($request->get('per_page', 15));

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
            'fecha' => 'required|date',
            'estado' => 'required|in:presente,ausente,tardanza',
            'observaciones' => 'nullable|string',
        ]);

        $attendance = Attendance::create($validated);

        return $this->successResponse($attendance, 'Attendance record created successfully', 201);
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