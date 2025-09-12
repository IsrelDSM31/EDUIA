<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Events",
 *     description="API Endpoints para gestión de eventos"
 * )
 */
class EventApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/events",
     *     summary="Obtener lista de eventos",
     *     tags={"Events"},
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
     *         name="search",
     *         in="query",
     *         description="Buscar por título o descripción",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="fecha_inicio",
     *         in="query",
     *         description="Filtrar por fecha de inicio",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="fecha_fin",
     *         in="query",
     *         description="Filtrar por fecha de fin",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="tipo",
     *         in="query",
     *         description="Filtrar por tipo de evento",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de eventos obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Events retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Event")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        if ($request->has('fecha_inicio')) {
            $query->where('fecha_inicio', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin')) {
            $query->where('fecha_fin', '<=', $request->fecha_fin);
        }

        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $events = $query->paginate($request->get('per_page', 15));

        return $this->successResponse($events, 'Events retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/events/{id}",
     *     summary="Obtener evento específico",
     *     tags={"Events"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del evento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evento obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Event")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evento no encontrado"
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->notFoundResponse('Event not found');
        }

        return $this->successResponse($event, 'Event retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/events",
     *     summary="Crear nuevo evento",
     *     tags={"Events"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titulo","fecha_inicio","fecha_fin"},
     *             @OA\Property(property="titulo", type="string", example="Reunión de padres"),
     *             @OA\Property(property="descripcion", type="string", example="Reunión para discutir el progreso académico"),
     *             @OA\Property(property="fecha_inicio", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="fecha_fin", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="tipo", type="string", example="reunion"),
     *             @OA\Property(property="ubicacion", type="string", example="Auditorio principal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Evento creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Event")
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
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'tipo' => 'nullable|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
        ]);

        $event = Event::create($validated);

        return $this->successResponse($event, 'Event created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/events/{id}",
     *     summary="Actualizar evento",
     *     tags={"Events"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del evento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="titulo", type="string", example="Reunión de padres"),
     *             @OA\Property(property="descripcion", type="string", example="Reunión para discutir el progreso académico"),
     *             @OA\Property(property="fecha_inicio", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="fecha_fin", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="tipo", type="string", example="reunion"),
     *             @OA\Property(property="ubicacion", type="string", example="Auditorio principal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evento actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Event")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evento no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->notFoundResponse('Event not found');
        }

        $validated = $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'sometimes|date|after_or_equal:fecha_inicio',
            'tipo' => 'nullable|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
        ]);

        $event->update($validated);

        return $this->successResponse($event, 'Event updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/events/{id}",
     *     summary="Eliminar evento",
     *     tags={"Events"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del evento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evento eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evento no encontrado"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->notFoundResponse('Event not found');
        }

        $event->delete();

        return $this->successResponse(null, 'Event deleted successfully');
    }
} 