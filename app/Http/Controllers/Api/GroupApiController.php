<?php

namespace App\Http\Controllers\Api;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Groups",
 *     description="API Endpoints para gestión de grupos"
 * )
 */
class GroupApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/groups",
     *     summary="Obtener lista de grupos",
     *     tags={"Groups"},
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
     *         description="Buscar por nombre del grupo",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (students,teacher)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de grupos obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Groups retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Group")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Group::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nombre', 'like', "%{$search}%");
        }

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $groups = $query->paginate($request->get('per_page', 15));

        return $this->successResponse($groups, 'Groups retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/groups/{id}",
     *     summary="Obtener grupo específico",
     *     tags={"Groups"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del grupo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (students,teacher)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grupo obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Group retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Group")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Grupo no encontrado"
     *     )
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Group::query();

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $group = $query->find($id);

        if (!$group) {
            return $this->notFoundResponse('Group not found');
        }

        return $this->successResponse($group, 'Group retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/groups",
     *     summary="Crear nuevo grupo",
     *     tags={"Groups"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre","grado"},
     *             @OA\Property(property="nombre", type="string", example="1A"),
     *             @OA\Property(property="grado", type="string", example="Primero"),
     *             @OA\Property(property="capacidad", type="integer", example=30),
     *             @OA\Property(property="teacher_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Grupo creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Group created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Group")
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
            'nombre' => 'required|string|max:255',
            'grado' => 'required|string|max:255',
            'capacidad' => 'nullable|integer|min:1',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $group = Group::create($validated);

        return $this->successResponse($group, 'Group created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/groups/{id}",
     *     summary="Actualizar grupo",
     *     tags={"Groups"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del grupo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nombre", type="string", example="1A"),
     *             @OA\Property(property="grado", type="string", example="Primero"),
     *             @OA\Property(property="capacidad", type="integer", example=30),
     *             @OA\Property(property="teacher_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grupo actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Group updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Group")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Grupo no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $group = Group::find($id);

        if (!$group) {
            return $this->notFoundResponse('Group not found');
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'grado' => 'sometimes|string|max:255',
            'capacidad' => 'nullable|integer|min:1',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $group->update($validated);

        return $this->successResponse($group, 'Group updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/groups/{id}",
     *     summary="Eliminar grupo",
     *     tags={"Groups"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del grupo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grupo eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Group deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Grupo no encontrado"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $group = Group::find($id);

        if (!$group) {
            return $this->notFoundResponse('Group not found');
        }

        $group->delete();

        return $this->successResponse(null, 'Group deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/groups/{id}/students",
     *     summary="Obtener estudiantes del grupo",
     *     tags={"Groups"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del grupo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estudiantes obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Group students retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Student"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Grupo no encontrado"
     *     )
     * )
     */
    public function students($id): JsonResponse
    {
        $group = Group::find($id);

        if (!$group) {
            return $this->notFoundResponse('Group not found');
        }

        $students = $group->students()->get();

        return $this->successResponse($students, 'Group students retrieved successfully');
    }
} 