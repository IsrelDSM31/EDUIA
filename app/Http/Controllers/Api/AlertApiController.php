<?php

namespace App\Http\Controllers\Api;

use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Alerts",
 *     description="API Endpoints para gestión de alertas"
 * )
 */
class AlertApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/alerts",
     *     summary="Obtener lista de alertas",
     *     tags={"Alerts"},
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
     *         name="tipo",
     *         in="query",
     *         description="Filtrar por tipo de alerta",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="estado",
     *         in="query",
     *         description="Filtrar por estado (activa, resuelta)",
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
     *         description="Lista de alertas obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Alerts retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Alert")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Alert::with('student')->latest();

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('urgency')) {
            $query->where('urgency', $request->urgency);
        }

        $alerts = $query->paginate($request->get('per_page', 50));

        // Transformar datos para la app móvil
        $transformedData = $alerts->getCollection()->map(function ($alert) {
            $student = $alert->student;
            $studentName = $student 
                ? trim($student->nombre . ' ' . $student->apellido_paterno . ' ' . $student->apellido_materno)
                : 'Estudiante';
            
            return [
                'id' => $alert->id,
                'student_id' => $alert->student_id,
                'student_name' => $studentName,
                'type' => $alert->type,
                'title' => $alert->title,
                'message' => $alert->description,
                'urgency' => $alert->urgency,
                'severity' => $alert->urgency, // Para compatibilidad
                'date' => $alert->created_at->toDateString(),
                'created_at' => $alert->created_at->format('Y-m-d H:i:s'),
                'is_read' => $alert->urgency === 'low',
            ];
        });

        $alerts->setCollection($transformedData);

        return $this->successResponse($alerts, 'Alerts retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/alerts/{id}",
     *     summary="Obtener alerta específica",
     *     tags={"Alerts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la alerta",
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
     *         description="Alerta obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Alert retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Alert")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Alerta no encontrada"
     *     )
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Alert::query();

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $alert = $query->find($id);

        if (!$alert) {
            return $this->notFoundResponse('Alert not found');
        }

        return $this->successResponse($alert, 'Alert retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/alerts",
     *     summary="Crear nueva alerta",
     *     tags={"Alerts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id","tipo","mensaje"},
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(property="tipo", type="string", example="academica"),
     *             @OA\Property(property="mensaje", type="string", example="Bajo rendimiento académico"),
     *             @OA\Property(property="estado", type="string", enum={"activa","resuelta"}, example="activa"),
     *             @OA\Property(property="fecha", type="string", format="date", example="2024-01-15")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Alerta creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Alert created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Alert")
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
            'type' => 'required|string|max:255',
            'title' => 'required|string',
            'description' => 'required|string',
            'urgency' => 'nullable|in:low,medium,high',
        ]);

        $alert = Alert::create([
            'student_id' => $validated['student_id'],
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'urgency' => $validated['urgency'] ?? 'medium',
            'evidence' => [],
            'suggested_actions' => [],
            'intervention_plan' => [],
        ]);

        return $this->successResponse($alert, 'Alert created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/alerts/{id}",
     *     summary="Actualizar alerta",
     *     tags={"Alerts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la alerta",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(property="tipo", type="string", example="academica"),
     *             @OA\Property(property="mensaje", type="string", example="Bajo rendimiento académico"),
     *             @OA\Property(property="estado", type="string", enum={"activa","resuelta"}, example="activa"),
     *             @OA\Property(property="fecha", type="string", format="date", example="2024-01-15")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Alerta actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Alert updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Alert")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Alerta no encontrada"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $alert = Alert::find($id);

        if (!$alert) {
            return $this->notFoundResponse('Alert not found');
        }

        $validated = $request->validate([
            'student_id' => 'sometimes|exists:students,id',
            'type' => 'sometimes|string|max:255',
            'title' => 'sometimes|string',
            'description' => 'sometimes|string',
            'urgency' => 'nullable|in:low,medium,high',
        ]);

        $alert->update($validated);

        return $this->successResponse($alert, 'Alert updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/alerts/{id}",
     *     summary="Eliminar alerta",
     *     tags={"Alerts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la alerta",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Alerta eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Alert deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Alerta no encontrada"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $alert = Alert::find($id);

        if (!$alert) {
            return $this->notFoundResponse('Alert not found');
        }

        $alert->delete();

        return $this->successResponse(null, 'Alert deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/alerts/statistics",
     *     summary="Obtener estadísticas de alertas",
     *     tags={"Alerts"},
     *     @OA\Parameter(
     *         name="student_id",
     *         in="query",
     *         description="Filtrar por estudiante",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Alert statistics retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_alerts", type="integer"),
     *                 @OA\Property(property="active_alerts", type="integer"),
     *                 @OA\Property(property="resolved_alerts", type="integer"),
     *                 @OA\Property(property="alerts_by_type", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = Alert::query();

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $alerts = $query->get();

        $statistics = [
            'total_alerts' => $alerts->count(),
            'high_urgency' => $alerts->where('urgency', 'high')->count(),
            'medium_urgency' => $alerts->where('urgency', 'medium')->count(),
            'low_urgency' => $alerts->where('urgency', 'low')->count(),
            'alerts_by_type' => $alerts->groupBy('type')->map->count(),
        ];

        return $this->successResponse($statistics, 'Alert statistics retrieved successfully');
    }

    public function unread(): JsonResponse
    {
        $alerts = Alert::where('urgency', 'high')->latest()->get();
        return $this->successResponse($alerts, 'Unread alerts retrieved successfully');
    }

    public function markAsRead($id): JsonResponse
    {
        $alert = Alert::find($id);

        if (!$alert) {
            return $this->notFoundResponse('Alert not found');
        }

        // Cambiar urgencia a baja como "leída"
        $alert->update(['urgency' => 'low']);

        return $this->successResponse($alert, 'Alert marked as read successfully');
    }
} 