<?php

namespace App\Http\Controllers\Api;

use App\Models\StudentRisk;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Student Risks",
 *     description="API Endpoints para gestión de riesgos de estudiantes"
 * )
 */
class StudentRiskApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/student-risks",
     *     summary="Obtener lista de riesgos de estudiantes",
     *     tags={"Student Risks"},
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
     *         name="nivel_riesgo",
     *         in="query",
     *         description="Filtrar por nivel de riesgo (bajo, medio, alto)",
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
     *         description="Lista de riesgos obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Student risks retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/StudentRisk")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = StudentRisk::query();

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('nivel_riesgo')) {
            $query->where('nivel_riesgo', $request->nivel_riesgo);
        }

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $risks = $query->paginate($request->get('per_page', 15));

        return $this->successResponse($risks, 'Student risks retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/student-risks/{id}",
     *     summary="Obtener riesgo específico",
     *     tags={"Student Risks"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del riesgo",
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
     *         description="Riesgo obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Student risk retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StudentRisk")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Riesgo no encontrado"
     *     )
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = StudentRisk::query();

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $risk = $query->find($id);

        if (!$risk) {
            return $this->notFoundResponse('Student risk not found');
        }

        return $this->successResponse($risk, 'Student risk retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/student-risks",
     *     summary="Crear nuevo riesgo",
     *     tags={"Student Risks"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id","nivel_riesgo","descripcion"},
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(property="nivel_riesgo", type="string", enum={"bajo","medio","alto"}, example="medio"),
     *             @OA\Property(property="descripcion", type="string", example="Bajo rendimiento académico"),
     *             @OA\Property(property="factores", type="string", example="Faltas frecuentes, bajas calificaciones"),
     *             @OA\Property(property="recomendaciones", type="string", example="Seguimiento académico intensivo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Riesgo creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Student risk created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StudentRisk")
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
            'nivel_riesgo' => 'required|in:bajo,medio,alto',
            'descripcion' => 'required|string',
            'factores' => 'nullable|string',
            'recomendaciones' => 'nullable|string',
        ]);

        $risk = StudentRisk::create($validated);

        return $this->successResponse($risk, 'Student risk created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/student-risks/{id}",
     *     summary="Actualizar riesgo",
     *     tags={"Student Risks"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del riesgo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(property="nivel_riesgo", type="string", enum={"bajo","medio","alto"}, example="medio"),
     *             @OA\Property(property="descripcion", type="string", example="Bajo rendimiento académico"),
     *             @OA\Property(property="factores", type="string", example="Faltas frecuentes, bajas calificaciones"),
     *             @OA\Property(property="recomendaciones", type="string", example="Seguimiento académico intensivo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Riesgo actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Student risk updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StudentRisk")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Riesgo no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $risk = StudentRisk::find($id);

        if (!$risk) {
            return $this->notFoundResponse('Student risk not found');
        }

        $validated = $request->validate([
            'student_id' => 'sometimes|exists:students,id',
            'nivel_riesgo' => 'sometimes|in:bajo,medio,alto',
            'descripcion' => 'sometimes|string',
            'factores' => 'nullable|string',
            'recomendaciones' => 'nullable|string',
        ]);

        $risk->update($validated);

        return $this->successResponse($risk, 'Student risk updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/student-risks/{id}",
     *     summary="Eliminar riesgo",
     *     tags={"Student Risks"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del riesgo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Riesgo eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Student risk deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Riesgo no encontrado"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $risk = StudentRisk::find($id);

        if (!$risk) {
            return $this->notFoundResponse('Student risk not found');
        }

        $risk->delete();

        return $this->successResponse(null, 'Student risk deleted successfully');
    }
} 