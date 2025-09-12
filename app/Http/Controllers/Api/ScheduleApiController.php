<?php

namespace App\Http\Controllers\Api;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Schedules",
 *     description="API Endpoints para gestión de horarios"
 * )
 */
class ScheduleApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/schedules",
     *     summary="Obtener lista de horarios",
     *     tags={"Schedules"},
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
     *         name="group_id",
     *         in="query",
     *         description="Filtrar por grupo",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="subject_id",
     *         in="query",
     *         description="Filtrar por materia",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="teacher_id",
     *         in="query",
     *         description="Filtrar por profesor",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="dia_semana",
     *         in="query",
     *         description="Filtrar por día de la semana",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (group,subject,teacher)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de horarios obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Schedules retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Schedule")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Schedule::query();

        if ($request->has('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->has('dia_semana')) {
            $query->where('dia_semana', $request->dia_semana);
        }

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $schedules = $query->paginate($request->get('per_page', 15));

        return $this->successResponse($schedules, 'Schedules retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/schedules/{id}",
     *     summary="Obtener horario específico",
     *     tags={"Schedules"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del horario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (group,subject,teacher)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Horario obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Schedule retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Schedule")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Horario no encontrado"
     *     )
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Schedule::query();

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $schedule = $query->find($id);

        if (!$schedule) {
            return $this->notFoundResponse('Schedule not found');
        }

        return $this->successResponse($schedule, 'Schedule retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/schedules",
     *     summary="Crear nuevo horario",
     *     tags={"Schedules"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"group_id","subject_id","teacher_id","dia_semana","hora_inicio","hora_fin"},
     *             @OA\Property(property="group_id", type="integer", example=1),
     *             @OA\Property(property="subject_id", type="integer", example=1),
     *             @OA\Property(property="teacher_id", type="integer", example=1),
     *             @OA\Property(property="dia_semana", type="string", example="Lunes"),
     *             @OA\Property(property="hora_inicio", type="string", example="08:00"),
     *             @OA\Property(property="hora_fin", type="string", example="09:00"),
     *             @OA\Property(property="aula", type="string", example="Aula 101")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Horario creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Schedule created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Schedule")
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
            'group_id' => 'required|exists:groups,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'dia_semana' => 'required|string|max:255',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'aula' => 'nullable|string|max:255',
        ]);

        $schedule = Schedule::create($validated);

        return $this->successResponse($schedule, 'Schedule created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/schedules/{id}",
     *     summary="Actualizar horario",
     *     tags={"Schedules"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del horario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="group_id", type="integer", example=1),
     *             @OA\Property(property="subject_id", type="integer", example=1),
     *             @OA\Property(property="teacher_id", type="integer", example=1),
     *             @OA\Property(property="dia_semana", type="string", example="Lunes"),
     *             @OA\Property(property="hora_inicio", type="string", example="08:00"),
     *             @OA\Property(property="hora_fin", type="string", example="09:00"),
     *             @OA\Property(property="aula", type="string", example="Aula 101")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Horario actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Schedule updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Schedule")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Horario no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return $this->notFoundResponse('Schedule not found');
        }

        $validated = $request->validate([
            'group_id' => 'sometimes|exists:groups,id',
            'subject_id' => 'sometimes|exists:subjects,id',
            'teacher_id' => 'sometimes|exists:teachers,id',
            'dia_semana' => 'sometimes|string|max:255',
            'hora_inicio' => 'sometimes|date_format:H:i',
            'hora_fin' => 'sometimes|date_format:H:i|after:hora_inicio',
            'aula' => 'nullable|string|max:255',
        ]);

        $schedule->update($validated);

        return $this->successResponse($schedule, 'Schedule updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/schedules/{id}",
     *     summary="Eliminar horario",
     *     tags={"Schedules"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del horario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Horario eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Schedule deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Horario no encontrado"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return $this->notFoundResponse('Schedule not found');
        }

        $schedule->delete();

        return $this->successResponse(null, 'Schedule deleted successfully');
    }
} 