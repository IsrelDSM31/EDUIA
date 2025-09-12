<?php

namespace App\Http\Controllers\Api;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Teachers",
 *     description="API Endpoints para gestión de profesores"
 * )
 */
class TeacherApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/teachers",
     *     summary="Obtener lista de profesores",
     *     tags={"Teachers"},
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
     *         description="Buscar por nombre o especialidad",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (user,subjects)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de profesores obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Teachers retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Teacher")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Teacher::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('apellido_materno', 'like', "%{$search}%")
                  ->orWhere('especialidad', 'like', "%{$search}%");
            });
        }

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $teachers = $query->paginate($request->get('per_page', 15));

        return $this->successResponse($teachers, 'Teachers retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/teachers/{id}",
     *     summary="Obtener profesor específico",
     *     tags={"Teachers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del profesor",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (user,subjects)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profesor obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Teacher retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Teacher")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profesor no encontrado"
     *     )
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Teacher::query();

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $teacher = $query->find($id);

        if (!$teacher) {
            return $this->notFoundResponse('Teacher not found');
        }

        return $this->successResponse($teacher, 'Teacher retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/teachers",
     *     summary="Crear nuevo profesor",
     *     tags={"Teachers"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre","apellido_paterno","apellido_materno","especialidad"},
     *             @OA\Property(property="nombre", type="string", example="María"),
     *             @OA\Property(property="apellido_paterno", type="string", example="González"),
     *             @OA\Property(property="apellido_materno", type="string", example="López"),
     *             @OA\Property(property="especialidad", type="string", example="Matemáticas"),
     *             @OA\Property(property="email", type="string", format="email", example="maria@example.com"),
     *             @OA\Property(property="telefono", type="string", example="555-123-4567"),
     *             @OA\Property(property="user_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Profesor creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Teacher created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Teacher")
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
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'especialidad' => 'required|string|max:255',
            'email' => 'nullable|email|unique:teachers,email',
            'telefono' => 'nullable|string|max:20',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $teacher = Teacher::create($validated);

        return $this->successResponse($teacher, 'Teacher created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/teachers/{id}",
     *     summary="Actualizar profesor",
     *     tags={"Teachers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del profesor",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nombre", type="string", example="María"),
     *             @OA\Property(property="apellido_paterno", type="string", example="González"),
     *             @OA\Property(property="apellido_materno", type="string", example="López"),
     *             @OA\Property(property="especialidad", type="string", example="Matemáticas"),
     *             @OA\Property(property="email", type="string", format="email", example="maria@example.com"),
     *             @OA\Property(property="telefono", type="string", example="555-123-4567"),
     *             @OA\Property(property="user_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profesor actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Teacher updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Teacher")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profesor no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $teacher = Teacher::find($id);

        if (!$teacher) {
            return $this->notFoundResponse('Teacher not found');
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'apellido_paterno' => 'sometimes|string|max:255',
            'apellido_materno' => 'sometimes|string|max:255',
            'especialidad' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:teachers,email,' . $id,
            'telefono' => 'nullable|string|max:20',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $teacher->update($validated);

        return $this->successResponse($teacher, 'Teacher updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/teachers/{id}",
     *     summary="Eliminar profesor",
     *     tags={"Teachers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del profesor",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profesor eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Teacher deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profesor no encontrado"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $teacher = Teacher::find($id);

        if (!$teacher) {
            return $this->notFoundResponse('Teacher not found');
        }

        $teacher->delete();

        return $this->successResponse(null, 'Teacher deleted successfully');
    }
} 