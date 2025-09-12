<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Students",
 *     description="API Endpoints para gestión de estudiantes"
 * )
 */
class StudentApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/students",
     *     summary="Obtener lista de estudiantes",
     *     tags={"Students"},
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
     *         description="Buscar por nombre o matrícula",
     *         required=false,
     *         @OA\Schema(type="string")
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
     *         description="Incluir relaciones (user,group,grades,attendances)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de estudiantes obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Students retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Student")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Student::query();

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('apellido_materno', 'like', "%{$search}%")
                  ->orWhere('matricula', 'like', "%{$search}%");
            });
        }

        // Filtro por grupo
        if ($request->has('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        // Incluir relaciones
        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $students = $query->paginate($request->get('per_page', 15));

        return $this->successResponse($students, 'Students retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/students/{id}",
     *     summary="Obtener estudiante específico",
     *     tags={"Students"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del estudiante",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (user,group,grades,attendances,alerts)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estudiante obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Student retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Student")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estudiante no encontrado"
     *     )
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Student::query();

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $student = $query->find($id);

        if (!$student) {
            return $this->notFoundResponse('Student not found');
        }

        return $this->successResponse($student, 'Student retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/students",
     *     summary="Crear nuevo estudiante",
     *     tags={"Students"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"matricula","nombre","apellido_paterno","apellido_materno","group_id"},
     *             @OA\Property(property="matricula", type="string", example="2024001"),
     *             @OA\Property(property="nombre", type="string", example="Juan"),
     *             @OA\Property(property="apellido_paterno", type="string", example="Pérez"),
     *             @OA\Property(property="apellido_materno", type="string", example="García"),
     *             @OA\Property(property="group_id", type="integer", example=1),
     *             @OA\Property(property="birth_date", type="string", format="date", example="2005-03-15"),
     *             @OA\Property(property="curp", type="string", example="PEGJ050315HDFXXX01"),
     *             @OA\Property(property="blood_type", type="string", example="O+"),
     *             @OA\Property(property="allergies", type="string", example="Polen"),
     *             @OA\Property(property="emergency_contact", type="object"),
     *             @OA\Property(property="parent_data", type="object"),
     *             @OA\Property(property="user_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Estudiante creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Student created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Student")
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
            'matricula' => 'required|string|unique:students,matricula',
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'group_id' => 'required|exists:groups,id',
            'birth_date' => 'nullable|date',
            'curp' => 'nullable|string|max:18',
            'blood_type' => 'nullable|string|max:5',
            'allergies' => 'nullable|string',
            'emergency_contact' => 'nullable|array',
            'parent_data' => 'nullable|array',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $student = Student::create($validated);

        return $this->successResponse($student, 'Student created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/students/{id}",
     *     summary="Actualizar estudiante",
     *     tags={"Students"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del estudiante",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="matricula", type="string", example="2024001"),
     *             @OA\Property(property="nombre", type="string", example="Juan"),
     *             @OA\Property(property="apellido_paterno", type="string", example="Pérez"),
     *             @OA\Property(property="apellido_materno", type="string", example="García"),
     *             @OA\Property(property="group_id", type="integer", example=1),
     *             @OA\Property(property="birth_date", type="string", format="date", example="2005-03-15"),
     *             @OA\Property(property="curp", type="string", example="PEGJ050315HDFXXX01"),
     *             @OA\Property(property="blood_type", type="string", example="O+"),
     *             @OA\Property(property="allergies", type="string", example="Polen"),
     *             @OA\Property(property="emergency_contact", type="object"),
     *             @OA\Property(property="parent_data", type="object"),
     *             @OA\Property(property="user_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estudiante actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Student updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Student")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estudiante no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $student = Student::find($id);

        if (!$student) {
            return $this->notFoundResponse('Student not found');
        }

        $validated = $request->validate([
            'matricula' => 'sometimes|string|unique:students,matricula,' . $id,
            'nombre' => 'sometimes|string|max:255',
            'apellido_paterno' => 'sometimes|string|max:255',
            'apellido_materno' => 'sometimes|string|max:255',
            'group_id' => 'sometimes|exists:groups,id',
            'birth_date' => 'nullable|date',
            'curp' => 'nullable|string|max:18',
            'blood_type' => 'nullable|string|max:5',
            'allergies' => 'nullable|string',
            'emergency_contact' => 'nullable|array',
            'parent_data' => 'nullable|array',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $student->update($validated);

        return $this->successResponse($student, 'Student updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/students/{id}",
     *     summary="Eliminar estudiante",
     *     tags={"Students"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del estudiante",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estudiante eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Student deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estudiante no encontrado"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $student = Student::find($id);

        if (!$student) {
            return $this->notFoundResponse('Student not found');
        }

        $student->delete();

        return $this->successResponse(null, 'Student deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/students/{id}/grades",
     *     summary="Obtener calificaciones del estudiante",
     *     tags={"Students"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del estudiante",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Calificaciones obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Student grades retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Grade"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estudiante no encontrado"
     *     )
     * )
     */
    public function grades($id): JsonResponse
    {
        $student = Student::find($id);

        if (!$student) {
            return $this->notFoundResponse('Student not found');
        }

        $grades = $student->grades()->with('subject')->get();

        return $this->successResponse($grades, 'Student grades retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/students/{id}/attendance",
     *     summary="Obtener asistencia del estudiante",
     *     tags={"Students"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del estudiante",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asistencia obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Student attendance retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Attendance"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estudiante no encontrado"
     *     )
     * )
     */
    public function attendance($id): JsonResponse
    {
        $student = Student::find($id);

        if (!$student) {
            return $this->notFoundResponse('Student not found');
        }

        $attendance = $student->attendances()->get();

        return $this->successResponse($attendance, 'Student attendance retrieved successfully');
    }
} 