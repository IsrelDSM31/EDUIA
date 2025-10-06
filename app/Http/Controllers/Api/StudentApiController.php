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

        $students = $query->with(['user', 'group'])->get();

        // Transformar datos para la app móvil
        $transformedStudents = $students->map(function($student) {
            // Obtener el nivel de riesgo más reciente si existe
            $latestRisk = $student->studentRisks()->latest()->first();
            
            return [
                'id' => $student->id,
                'name' => trim($student->nombre . ' ' . $student->apellido_paterno . ' ' . $student->apellido_materno),
                'email' => $student->user->email ?? $student->matricula . '@eduia.com',
                'student_code' => $student->matricula,
                'phone' => $student->emergency_contact['phone'] ?? '',
                'birth_date' => $student->birth_date,
                'grade' => $student->group->name ?? 'Sin grupo',
                'address' => $student->parent_data['address'] ?? '',
                'risk_level' => $latestRisk->risk_level ?? 'low',
            ];
        });

        return $this->successResponse($transformedStudents, 'Students retrieved successfully');
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
        $student = Student::with(['user', 'group'])->find($id);

        if (!$student) {
            return $this->notFoundResponse('Student not found');
        }

        // Obtener el nivel de riesgo más reciente si existe
        $latestRisk = $student->studentRisks()->latest()->first();

        // Transformar datos para la app móvil
        $transformedStudent = [
            'id' => $student->id,
            'name' => trim($student->nombre . ' ' . $student->apellido_paterno . ' ' . $student->apellido_materno),
            'email' => $student->user->email ?? $student->matricula . '@eduia.com',
            'student_code' => $student->matricula,
            'phone' => $student->emergency_contact['phone'] ?? '',
            'birth_date' => $student->birth_date,
            'grade' => $student->group->name ?? 'Sin grupo',
            'address' => $student->parent_data['address'] ?? '',
            'risk_level' => $latestRisk->risk_level ?? 'low',
        ];

        return $this->successResponse($transformedStudent, 'Student retrieved successfully');
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
        // Validar datos de la app móvil
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'student_code' => 'required|string|unique:students,matricula',
        ]);

        // Dividir el nombre en partes
        $nameParts = explode(' ', trim($request->name));
        $nombre = $nameParts[0] ?? '';
        $apellido_paterno = $nameParts[1] ?? '';
        $apellido_materno = isset($nameParts[2]) ? implode(' ', array_slice($nameParts, 2)) : '';

        // Buscar o crear usuario asociado
        $user = \App\Models\User::where('email', $request->email)->first();
        
        if (!$user) {
            $user = \App\Models\User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role' => 'student'
            ]);
        }

        // Obtener grupo por defecto
        $defaultGroup = \App\Models\Group::first();

        $studentData = [
            'user_id' => $user->id,
            'matricula' => $request->student_code,
            'nombre' => $nombre,
            'apellido_paterno' => $apellido_paterno,
            'apellido_materno' => $apellido_materno,
            'group_id' => $defaultGroup->id ?? 1,
            'birth_date' => $request->birth_date,
            'emergency_contact' => ['phone' => $request->phone ?? ''],
            'parent_data' => ['address' => $request->address ?? ''],
        ];

        $student = Student::create($studentData);

        // Crear registro de riesgo inicial para el estudiante
        \App\Models\StudentRisk::create([
            'student_id' => $student->id,
            'risk_level' => 'bajo',
            'risk_score' => 0,
            'performance_metrics' => [
                'attendance_rate' => 100,
                'grade_average' => 0,
                'failed_subjects' => 0,
                'recent_improvement' => 0
            ],
            'intervention_recommendations' => [
                [
                    'type' => 'monitoring',
                    'priority' => 'low',
                    'message' => 'Estudiante nuevo. Establecer línea base de rendimiento y asistencia.'
                ]
            ],
            'notes' => 'Estudiante recién inscrito',
        ]);

        // Transformar respuesta
        $transformedStudent = [
            'id' => $student->id,
            'name' => trim($student->nombre . ' ' . $student->apellido_paterno . ' ' . $student->apellido_materno),
            'email' => $request->email,
            'student_code' => $student->matricula,
            'phone' => $request->phone ?? '',
            'birth_date' => $student->birth_date,
            'grade' => $student->group->name ?? 'Sin grupo',
            'address' => $request->address ?? '',
            'risk_level' => 'bajo',
        ];

        return $this->successResponse($transformedStudent, 'Student created successfully', 201);
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

        // Validar datos de la app móvil
        $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email',
            'student_code' => 'sometimes|string|unique:students,matricula,' . $id,
        ]);

        $updateData = [];

        // Actualizar nombre si viene en la petición
        if ($request->has('name')) {
            $nameParts = explode(' ', trim($request->name));
            $updateData['nombre'] = $nameParts[0] ?? '';
            $updateData['apellido_paterno'] = $nameParts[1] ?? '';
            $updateData['apellido_materno'] = isset($nameParts[2]) ? implode(' ', array_slice($nameParts, 2)) : '';
        }

        // Actualizar matrícula
        if ($request->has('student_code')) {
            $updateData['matricula'] = $request->student_code;
        }

        // Actualizar fecha de nacimiento
        if ($request->has('birth_date')) {
            $updateData['birth_date'] = $request->birth_date;
        }

        // Actualizar teléfono y dirección
        if ($request->has('phone') || $request->has('address')) {
            $emergencyContact = $student->emergency_contact ?? [];
            $parentData = $student->parent_data ?? [];
            
            if ($request->has('phone')) {
                $emergencyContact['phone'] = $request->phone;
                $updateData['emergency_contact'] = $emergencyContact;
            }
            
            if ($request->has('address')) {
                $parentData['address'] = $request->address;
                $updateData['parent_data'] = $parentData;
            }
        }

        $student->update($updateData);

        // Transformar respuesta
        $latestRisk = $student->studentRisks()->latest()->first();
        
        $transformedStudent = [
            'id' => $student->id,
            'name' => trim($student->nombre . ' ' . $student->apellido_paterno . ' ' . $student->apellido_materno),
            'email' => $request->email ?? $student->user->email ?? $student->matricula . '@eduia.com',
            'student_code' => $student->matricula,
            'phone' => $student->emergency_contact['phone'] ?? '',
            'birth_date' => $student->birth_date,
            'grade' => $student->group->name ?? 'Sin grupo',
            'address' => $student->parent_data['address'] ?? '',
            'risk_level' => $latestRisk->risk_level ?? 'low',
        ];

        return $this->successResponse($transformedStudent, 'Student updated successfully');
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

    public function alerts($id): JsonResponse
    {
        $student = Student::find($id);

        if (!$student) {
            return $this->notFoundResponse('Student not found');
        }

        $alerts = $student->alerts()->get();

        return $this->successResponse($alerts, 'Student alerts retrieved successfully');
    }

    public function riskAnalysis($id): JsonResponse
    {
        $student = Student::find($id);

        if (!$student) {
            return $this->notFoundResponse('Student not found');
        }

        $latestRisk = $student->studentRisks()->latest()->first();

        $data = [
            'student_id' => $student->id,
            'student_name' => $student->nombre . ' ' . $student->apellido_paterno . ' ' . $student->apellido_materno,
            'student_code' => $student->matricula,
            'risk_level' => $latestRisk->risk_level ?? 'none',
            'risk_score' => $latestRisk->risk_score ?? 0,
            'risk_factors' => $latestRisk->risk_factors ? json_decode($latestRisk->risk_factors) : [],
            'recommendations' => $latestRisk->recommendations ?? 'Sin recomendaciones',
            'last_analysis' => $latestRisk->updated_at ?? now(),
        ];

        return $this->successResponse($data, 'Student risk analysis retrieved successfully');
    }
} 