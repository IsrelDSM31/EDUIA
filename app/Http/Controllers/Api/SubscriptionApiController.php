<?php

namespace App\Http\Controllers\Api;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Subscriptions",
 *     description="API Endpoints para gestión de suscripciones"
 * )
 */
class SubscriptionApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/subscriptions",
     *     summary="Obtener lista de suscripciones",
     *     tags={"Subscriptions"},
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
     *         name="user_id",
     *         in="query",
     *         description="Filtrar por usuario",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="estado",
     *         in="query",
     *         description="Filtrar por estado (activa, inactiva, cancelada)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (user,invoices)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de suscripciones obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subscriptions retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Subscription")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Subscription::query();

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $subscriptions = $query->paginate($request->get('per_page', 15));

        return $this->successResponse($subscriptions, 'Subscriptions retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/subscriptions/{id}",
     *     summary="Obtener suscripción específica",
     *     tags={"Subscriptions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la suscripción",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (user,invoices)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Suscripción obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subscription retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Subscription")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Suscripción no encontrada"
     *     )
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Subscription::query();

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $subscription = $query->find($id);

        if (!$subscription) {
            return $this->notFoundResponse('Subscription not found');
        }

        return $this->successResponse($subscription, 'Subscription retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/subscriptions",
     *     summary="Crear nueva suscripción",
     *     tags={"Subscriptions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","plan","fecha_inicio"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="plan", type="string", example="premium"),
     *             @OA\Property(property="fecha_inicio", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="fecha_fin", type="string", format="date", example="2024-02-15"),
     *             @OA\Property(property="estado", type="string", enum={"activa","inactiva","cancelada"}, example="activa"),
     *             @OA\Property(property="precio", type="number", format="float", example=99.99)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Suscripción creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subscription created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Subscription")
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
            'user_id' => 'required|exists:users,id',
            'plan' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'estado' => 'nullable|in:activa,inactiva,cancelada',
            'precio' => 'nullable|numeric|min:0',
        ]);

        $subscription = Subscription::create($validated);

        return $this->successResponse($subscription, 'Subscription created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/subscriptions/{id}",
     *     summary="Actualizar suscripción",
     *     tags={"Subscriptions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la suscripción",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="plan", type="string", example="premium"),
     *             @OA\Property(property="fecha_inicio", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="fecha_fin", type="string", format="date", example="2024-02-15"),
     *             @OA\Property(property="estado", type="string", enum={"activa","inactiva","cancelada"}, example="activa"),
     *             @OA\Property(property="precio", type="number", format="float", example=99.99)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Suscripción actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subscription updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Subscription")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Suscripción no encontrada"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $subscription = Subscription::find($id);

        if (!$subscription) {
            return $this->notFoundResponse('Subscription not found');
        }

        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'plan' => 'sometimes|string|max:255',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'estado' => 'nullable|in:activa,inactiva,cancelada',
            'precio' => 'nullable|numeric|min:0',
        ]);

        $subscription->update($validated);

        return $this->successResponse($subscription, 'Subscription updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/subscriptions/{id}",
     *     summary="Eliminar suscripción",
     *     tags={"Subscriptions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la suscripción",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Suscripción eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subscription deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Suscripción no encontrada"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $subscription = Subscription::find($id);

        if (!$subscription) {
            return $this->notFoundResponse('Subscription not found');
        }

        $subscription->delete();

        return $this->successResponse(null, 'Subscription deleted successfully');
    }
} 