<?php

namespace App\Http\Controllers\Api;

use App\Models\Rubric;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Rubrics",
 *     description="API Endpoints para gestión de rúbricas"
 * )
 */
class RubricApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/rubrics",
     *     summary="Obtener lista de rúbricas",
     *     tags={"Rubrics"},
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
     *         description="Buscar por nombre de la rúbrica",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de rúbricas obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rubrics retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Rubric")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Rubric::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nombre', 'like', "%{$search}%");
        }

        $rubrics = $query->paginate($request->get('per_page', 15));

        return $this->successResponse($rubrics, 'Rubrics retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/rubrics/{id}",
     *     summary="Obtener rúbrica específica",
     *     tags={"Rubrics"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la rúbrica",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rúbrica obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rubric retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Rubric")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rúbrica no encontrada"
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        $rubric = Rubric::find($id);

        if (!$rubric) {
            return $this->notFoundResponse('Rubric not found');
        }

        return $this->successResponse($rubric, 'Rubric retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/rubrics",
     *     summary="Crear nueva rúbrica",
     *     tags={"Rubrics"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre"},
     *             @OA\Property(property="nombre", type="string", example="Rúbrica de Evaluación"),
     *             @OA\Property(property="descripcion", type="string", example="Criterios de evaluación para proyectos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rúbrica creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rubric created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Rubric")
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
            'descripcion' => 'nullable|string',
        ]);

        $rubric = Rubric::create($validated);

        return $this->successResponse($rubric, 'Rubric created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/rubrics/{id}",
     *     summary="Actualizar rúbrica",
     *     tags={"Rubrics"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la rúbrica",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nombre", type="string", example="Rúbrica de Evaluación"),
     *             @OA\Property(property="descripcion", type="string", example="Criterios de evaluación para proyectos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rúbrica actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rubric updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Rubric")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rúbrica no encontrada"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $rubric = Rubric::find($id);

        if (!$rubric) {
            return $this->notFoundResponse('Rubric not found');
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $rubric->update($validated);

        return $this->successResponse($rubric, 'Rubric updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/rubrics/{id}",
     *     summary="Eliminar rúbrica",
     *     tags={"Rubrics"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la rúbrica",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rúbrica eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rubric deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rúbrica no encontrada"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $rubric = Rubric::find($id);

        if (!$rubric) {
            return $this->notFoundResponse('Rubric not found');
        }

        $rubric->delete();

        return $this->successResponse(null, 'Rubric deleted successfully');
    }
} 