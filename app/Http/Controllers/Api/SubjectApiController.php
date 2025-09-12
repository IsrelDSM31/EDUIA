<?php

namespace App\Http\Controllers\Api;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Subjects",
 *     description="API Endpoints para gestión de materias"
 * )
 */
class SubjectApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/subjects",
     *     summary="Obtener lista de materias",
     *     tags={"Subjects"},
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
     *         description="Buscar por nombre de la materia",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (teacher,grades)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de materias obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subjects retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Subject")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Subject::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nombre', 'like', "%{$search}%");
        }

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $subjects = $query->paginate($request->get('per_page', 15));

        return $this->successResponse($subjects, 'Subjects retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/subjects/{id}",
     *     summary="Obtener materia específica",
     *     tags={"Subjects"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la materia",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (teacher,grades)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Materia obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subject retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Subject")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Materia no encontrada"
     *     )
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Subject::query();

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $subject = $query->find($id);

        if (!$subject) {
            return $this->notFoundResponse('Subject not found');
        }

        return $this->successResponse($subject, 'Subject retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/subjects",
     *     summary="Crear nueva materia",
     *     tags={"Subjects"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre","creditos"},
     *             @OA\Property(property="nombre", type="string", example="Matemáticas"),
     *             @OA\Property(property="creditos", type="integer", example=6),
     *             @OA\Property(property="descripcion", type="string", example="Materia de matemáticas básicas"),
     *             @OA\Property(property="teacher_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Materia creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subject created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Subject")
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
            'creditos' => 'required|integer|min:1',
            'descripcion' => 'nullable|string',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $subject = Subject::create($validated);

        return $this->successResponse($subject, 'Subject created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/subjects/{id}",
     *     summary="Actualizar materia",
     *     tags={"Subjects"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la materia",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nombre", type="string", example="Matemáticas"),
     *             @OA\Property(property="creditos", type="integer", example=6),
     *             @OA\Property(property="descripcion", type="string", example="Materia de matemáticas básicas"),
     *             @OA\Property(property="teacher_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Materia actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subject updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Subject")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Materia no encontrada"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $subject = Subject::find($id);

        if (!$subject) {
            return $this->notFoundResponse('Subject not found');
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'creditos' => 'sometimes|integer|min:1',
            'descripcion' => 'nullable|string',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $subject->update($validated);

        return $this->successResponse($subject, 'Subject updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/subjects/{id}",
     *     summary="Eliminar materia",
     *     tags={"Subjects"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la materia",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Materia eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subject deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Materia no encontrada"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $subject = Subject::find($id);

        if (!$subject) {
            return $this->notFoundResponse('Subject not found');
        }

        $subject->delete();

        return $this->successResponse(null, 'Subject deleted successfully');
    }
} 