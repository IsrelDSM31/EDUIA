<?php

namespace App\Http\Controllers\Api;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Grades",
 *     description="API Endpoints para gestión de calificaciones"
 * )
 */
class GradeApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/grades",
     *     summary="Obtener lista de calificaciones",
     *     tags={"Grades"},
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
     *         name="subject_id",
     *         in="query",
     *         description="Filtrar por materia",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="group_id",
     *         in="query",
     *         description="Filtrar por grupo",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (student,subject)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de calificaciones obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Grades retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Grade")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Grade::query();

        // Filtros
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->has('group_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('group_id', $request->group_id);
            });
        }

        // Incluir relaciones
        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $grades = $query->paginate($request->get('per_page', 15));

        return $this->successResponse($grades, 'Grades retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/grades/{id}",
     *     summary="Obtener calificación específica",
     *     tags={"Grades"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la calificación",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (student,subject)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Calificación obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Grade retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Grade")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Calificación no encontrada"
     *     )
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Grade::query();

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $grade = $query->find($id);

        if (!$grade) {
            return $this->notFoundResponse('Grade not found');
        }

        return $this->successResponse($grade, 'Grade retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/grades",
     *     summary="Crear nueva calificación",
     *     tags={"Grades"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id","subject_id","promedio_final"},
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(property="subject_id", type="integer", example=1),
     *             @OA\Property(property="promedio_final", type="number", format="float", example=8.5),
     *             @OA\Property(property="evaluations", type="object"),
     *             @OA\Property(property="estado", type="string", example="Aprobado"),
     *             @OA\Property(property="faltantes", type="integer", example=0),
     *             @OA\Property(property="puntos_faltantes", type="number", format="float", example=0.0),
     *             @OA\Property(property="date", type="string", format="date", example="2024-01-15")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Calificación creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Grade created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Grade")
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
            'subject_id' => 'required|exists:subjects,id',
            'promedio_final' => 'required|numeric|min:0|max:10',
            'evaluations' => 'nullable|array',
            'estado' => 'nullable|string|max:50',
            'faltantes' => 'nullable|integer|min:0',
            'puntos_faltantes' => 'nullable|numeric|min:0',
            'date' => 'nullable|date',
        ]);

        $grade = Grade::create($validated);

        return $this->successResponse($grade, 'Grade created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/grades/{id}",
     *     summary="Actualizar calificación",
     *     tags={"Grades"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la calificación",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(property="subject_id", type="integer", example=1),
     *             @OA\Property(property="promedio_final", type="number", format="float", example=8.5),
     *             @OA\Property(property="evaluations", type="object"),
     *             @OA\Property(property="estado", type="string", example="Aprobado"),
     *             @OA\Property(property="faltantes", type="integer", example=0),
     *             @OA\Property(property="puntos_faltantes", type="number", format="float", example=0.0),
     *             @OA\Property(property="date", type="string", format="date", example="2024-01-15")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Calificación actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Grade updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Grade")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Calificación no encontrada"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $grade = Grade::find($id);

        if (!$grade) {
            return $this->notFoundResponse('Grade not found');
        }

        $validated = $request->validate([
            'student_id' => 'sometimes|exists:students,id',
            'subject_id' => 'sometimes|exists:subjects,id',
            'promedio_final' => 'sometimes|numeric|min:0|max:10',
            'evaluations' => 'nullable|array',
            'estado' => 'nullable|string|max:50',
            'faltantes' => 'nullable|integer|min:0',
            'puntos_faltantes' => 'nullable|numeric|min:0',
            'date' => 'nullable|date',
        ]);

        $grade->update($validated);

        return $this->successResponse($grade, 'Grade updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/grades/{id}",
     *     summary="Eliminar calificación",
     *     tags={"Grades"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la calificación",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Calificación eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Grade deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Calificación no encontrada"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $grade = Grade::find($id);

        if (!$grade) {
            return $this->notFoundResponse('Grade not found');
        }

        $grade->delete();

        return $this->successResponse(null, 'Grade deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/grades/statistics",
     *     summary="Obtener estadísticas de calificaciones",
     *     tags={"Grades"},
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
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Grade statistics retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="average", type="number", format="float"),
     *                 @OA\Property(property="highest", type="number", format="float"),
     *                 @OA\Property(property="lowest", type="number", format="float"),
     *                 @OA\Property(property="total_students", type="integer"),
     *                 @OA\Property(property="approved", type="integer"),
     *                 @OA\Property(property="failed", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = Grade::query();

        if ($request->has('group_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('group_id', $request->group_id);
            });
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $grades = $query->get();

        $statistics = [
            'average' => $grades->avg('promedio_final'),
            'highest' => $grades->max('promedio_final'),
            'lowest' => $grades->min('promedio_final'),
            'total_students' => $grades->count(),
            'approved' => $grades->where('promedio_final', '>=', 7)->count(),
            'failed' => $grades->where('promedio_final', '<', 7)->count(),
        ];

        return $this->successResponse($statistics, 'Grade statistics retrieved successfully');
    }
} 