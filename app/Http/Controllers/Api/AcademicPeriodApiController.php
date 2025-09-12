<?php

namespace App\Http\Controllers\Api;

use App\Models\AcademicPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Academic Periods",
 *     description="API Endpoints para gestión de períodos académicos"
 * )
 */
class AcademicPeriodApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/academic-periods",
     *     summary="Obtener lista de períodos académicos",
     *     tags={"Academic Periods"},
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
     *         description="Buscar por nombre del período",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="estado",
     *         in="query",
     *         description="Filtrar por estado (activo, inactivo)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de períodos académicos obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Academic periods retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AcademicPeriod")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = AcademicPeriod::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nombre', 'like', "%{$search}%");
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        $periods = $query->paginate($request->get('per_page', 15));

        return $this->successResponse($periods, 'Academic periods retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/academic-periods/{id}",
     *     summary="Obtener período académico específico",
     *     tags={"Academic Periods"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del período académico",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Período académico obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Academic period retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/AcademicPeriod")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Período académico no encontrado"
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        $period = AcademicPeriod::find($id);

        if (!$period) {
            return $this->notFoundResponse('Academic period not found');
        }

        return $this->successResponse($period, 'Academic period retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/academic-periods",
     *     summary="Crear nuevo período académico",
     *     tags={"Academic Periods"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre","fecha_inicio","fecha_fin"},
     *             @OA\Property(property="nombre", type="string", example="Primer Semestre 2024"),
     *             @OA\Property(property="fecha_inicio", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="fecha_fin", type="string", format="date", example="2024-06-15"),
     *             @OA\Property(property="estado", type="string", enum={"activo","inactivo"}, example="activo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Período académico creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Academic period created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/AcademicPeriod")
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
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'estado' => 'nullable|in:activo,inactivo',
        ]);

        $period = AcademicPeriod::create($validated);

        return $this->successResponse($period, 'Academic period created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/academic-periods/{id}",
     *     summary="Actualizar período académico",
     *     tags={"Academic Periods"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del período académico",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nombre", type="string", example="Primer Semestre 2024"),
     *             @OA\Property(property="fecha_inicio", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="fecha_fin", type="string", format="date", example="2024-06-15"),
     *             @OA\Property(property="estado", type="string", enum={"activo","inactivo"}, example="activo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Período académico actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Academic period updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/AcademicPeriod")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Período académico no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $period = AcademicPeriod::find($id);

        if (!$period) {
            return $this->notFoundResponse('Academic period not found');
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'sometimes|date|after:fecha_inicio',
            'estado' => 'nullable|in:activo,inactivo',
        ]);

        $period->update($validated);

        return $this->successResponse($period, 'Academic period updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/academic-periods/{id}",
     *     summary="Eliminar período académico",
     *     tags={"Academic Periods"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del período académico",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Período académico eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Academic period deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Período académico no encontrado"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $period = AcademicPeriod::find($id);

        if (!$period) {
            return $this->notFoundResponse('Academic period not found');
        }

        $period->delete();

        return $this->successResponse(null, 'Academic period deleted successfully');
    }
} 