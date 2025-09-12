<?php

namespace App\Http\Controllers\Api;

use App\Models\ChangeLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Change Logs",
 *     description="API Endpoints para gestión de registros de cambios"
 * )
 */
class ChangeLogApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/change-logs",
     *     summary="Obtener lista de registros de cambios",
     *     tags={"Change Logs"},
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
     *         name="action",
     *         in="query",
     *         description="Filtrar por acción (create, update, delete)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="table_name",
     *         in="query",
     *         description="Filtrar por tabla",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (user)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de registros de cambios obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Change logs retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ChangeLog")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = ChangeLog::query();

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('table_name')) {
            $query->where('table_name', $request->table_name);
        }

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return $this->successResponse($logs, 'Change logs retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/change-logs/{id}",
     *     summary="Obtener registro de cambios específico",
     *     tags={"Change Logs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del registro de cambios",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (user)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro de cambios obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Change log retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ChangeLog")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Registro de cambios no encontrado"
     *     )
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = ChangeLog::query();

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $log = $query->find($id);

        if (!$log) {
            return $this->notFoundResponse('Change log not found');
        }

        return $this->successResponse($log, 'Change log retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/change-logs",
     *     summary="Crear nuevo registro de cambios",
     *     tags={"Change Logs"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","action","table_name","record_id"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="action", type="string", enum={"create","update","delete"}, example="update"),
     *             @OA\Property(property="table_name", type="string", example="students"),
     *             @OA\Property(property="record_id", type="integer", example=1),
     *             @OA\Property(property="old_values", type="object"),
     *             @OA\Property(property="new_values", type="object"),
     *             @OA\Property(property="description", type="string", example="Actualización de datos del estudiante")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registro de cambios creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Change log created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ChangeLog")
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
            'action' => 'required|in:create,update,delete',
            'table_name' => 'required|string|max:255',
            'record_id' => 'required|integer',
            'old_values' => 'nullable|array',
            'new_values' => 'nullable|array',
            'description' => 'nullable|string',
        ]);

        $log = ChangeLog::create($validated);

        return $this->successResponse($log, 'Change log created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/change-logs/{id}",
     *     summary="Actualizar registro de cambios",
     *     tags={"Change Logs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del registro de cambios",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="action", type="string", enum={"create","update","delete"}, example="update"),
     *             @OA\Property(property="table_name", type="string", example="students"),
     *             @OA\Property(property="record_id", type="integer", example=1),
     *             @OA\Property(property="old_values", type="object"),
     *             @OA\Property(property="new_values", type="object"),
     *             @OA\Property(property="description", type="string", example="Actualización de datos del estudiante")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro de cambios actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Change log updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ChangeLog")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Registro de cambios no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $log = ChangeLog::find($id);

        if (!$log) {
            return $this->notFoundResponse('Change log not found');
        }

        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'action' => 'sometimes|in:create,update,delete',
            'table_name' => 'sometimes|string|max:255',
            'record_id' => 'sometimes|integer',
            'old_values' => 'nullable|array',
            'new_values' => 'nullable|array',
            'description' => 'nullable|string',
        ]);

        $log->update($validated);

        return $this->successResponse($log, 'Change log updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/change-logs/{id}",
     *     summary="Eliminar registro de cambios",
     *     tags={"Change Logs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del registro de cambios",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro de cambios eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Change log deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Registro de cambios no encontrado"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $log = ChangeLog::find($id);

        if (!$log) {
            return $this->notFoundResponse('Change log not found');
        }

        $log->delete();

        return $this->successResponse(null, 'Change log deleted successfully');
    }
} 