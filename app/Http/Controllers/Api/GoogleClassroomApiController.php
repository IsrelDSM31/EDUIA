<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GoogleClassroomApiController extends ApiController
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;

    public function __construct()
    {
        $this->clientId = env('GOOGLE_CLIENT_ID', '');
        $this->clientSecret = env('GOOGLE_CLIENT_SECRET', '');
        $this->redirectUri = env('GOOGLE_REDIRECT_URI', '');
    }

    /**
     * Obtener configuración de OAuth
     */
    public function getAuthConfig(): JsonResponse
    {
        return $this->successResponse([
            'auth_url' => $this->getAuthUrl(),
            'is_configured' => !empty($this->clientId) && !empty($this->clientSecret),
        ], 'Google Classroom auth config retrieved');
    }

    /**
     * Generar URL de autenticación
     */
    private function getAuthUrl(): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', [
                'https://www.googleapis.com/auth/classroom.courses.readonly',
                'https://www.googleapis.com/auth/classroom.rosters.readonly',
                'https://www.googleapis.com/auth/classroom.student-submissions.students.readonly',
                'https://www.googleapis.com/auth/classroom.coursework.students.readonly',
            ]),
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Intercambiar código por token
     */
    public function exchangeCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $validated['code'],
                'redirect_uri' => $this->redirectUri,
                'grant_type' => 'authorization_code',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Guardar tokens en cache por usuario
                $userId = auth()->id();
                Cache::put("google_classroom_token_{$userId}", $data['access_token'], now()->addSeconds($data['expires_in'] ?? 3600));
                
                if (isset($data['refresh_token'])) {
                    Cache::put("google_classroom_refresh_{$userId}", $data['refresh_token'], now()->addDays(30));
                }

                return $this->successResponse([
                    'access_token' => $data['access_token'],
                    'expires_in' => $data['expires_in'] ?? 3600,
                ], 'Successfully connected to Google Classroom');
            }

            return $this->errorResponse('Failed to exchange code for token', 400);
        } catch (\Exception $e) {
            return $this->errorResponse('Error connecting to Google: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener cursos de Google Classroom
     */
    public function getCourses(Request $request): JsonResponse
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return $this->errorResponse('Not connected to Google Classroom', 401);
        }

        try {
            $response = Http::withToken($accessToken)
                ->get('https://classroom.googleapis.com/v1/courses', [
                    'courseStates' => 'ACTIVE',
                ]);

            if ($response->successful()) {
                $courses = $response->json()['courses'] ?? [];
                
                return $this->successResponse([
                    'courses' => collect($courses)->map(function ($course) {
                        return [
                            'id' => $course['id'],
                            'name' => $course['name'],
                            'section' => $course['section'] ?? '',
                            'description' => $course['description'] ?? '',
                            'room' => $course['room'] ?? '',
                            'enrollment_code' => $course['enrollmentCode'] ?? null,
                        ];
                    }),
                ], 'Courses retrieved successfully');
            }

            return $this->errorResponse('Failed to retrieve courses', 400);
        } catch (\Exception $e) {
            return $this->errorResponse('Error fetching courses: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener estudiantes de un curso
     */
    public function getCourseStudents(Request $request, $courseId): JsonResponse
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return $this->errorResponse('Not connected to Google Classroom', 401);
        }

        try {
            $response = Http::withToken($accessToken)
                ->get("https://classroom.googleapis.com/v1/courses/{$courseId}/students");

            if ($response->successful()) {
                $students = $response->json()['students'] ?? [];
                
                return $this->successResponse([
                    'students' => collect($students)->map(function ($student) {
                        return [
                            'id' => $student['userId'],
                            'name' => $student['profile']['name']['fullName'] ?? '',
                            'email' => $student['profile']['emailAddress'] ?? '',
                            'photo_url' => $student['profile']['photoUrl'] ?? null,
                        ];
                    }),
                ], 'Students retrieved successfully');
            }

            return $this->errorResponse('Failed to retrieve students', 400);
        } catch (\Exception $e) {
            return $this->errorResponse('Error fetching students: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener tareas de un curso
     */
    public function getCourseWork(Request $request, $courseId): JsonResponse
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return $this->errorResponse('Not connected to Google Classroom', 401);
        }

        try {
            $response = Http::withToken($accessToken)
                ->get("https://classroom.googleapis.com/v1/courses/{$courseId}/courseWork");

            if ($response->successful()) {
                $courseWork = $response->json()['courseWork'] ?? [];
                
                return $this->successResponse([
                    'assignments' => collect($courseWork)->map(function ($work) {
                        return [
                            'id' => $work['id'],
                            'title' => $work['title'],
                            'description' => $work['description'] ?? '',
                            'state' => $work['state'],
                            'max_points' => $work['maxPoints'] ?? 0,
                            'due_date' => $work['dueDate'] ?? null,
                            'created_time' => $work['creationTime'] ?? null,
                        ];
                    }),
                ], 'Course work retrieved successfully');
            }

            return $this->errorResponse('Failed to retrieve course work', 400);
        } catch (\Exception $e) {
            return $this->errorResponse('Error fetching course work: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Sincronizar estudiantes de un curso con el sistema
     */
    public function syncCourseStudents(Request $request, $courseId): JsonResponse
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return $this->errorResponse('Not connected to Google Classroom', 401);
        }

        try {
            // Obtener estudiantes de Google Classroom
            $response = Http::withToken($accessToken)
                ->get("https://classroom.googleapis.com/v1/courses/{$courseId}/students");

            if (!$response->successful()) {
                return $this->errorResponse('Failed to retrieve students from Google Classroom', 400);
            }

            $students = $response->json()['students'] ?? [];
            $syncedCount = 0;

            foreach ($students as $student) {
                $email = $student['profile']['emailAddress'] ?? null;
                $name = $student['profile']['name']['fullName'] ?? '';
                
                if ($email) {
                    // Buscar o crear usuario
                    $user = \App\Models\User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name' => $name,
                            'password' => \Hash::make(\Str::random(16)),
                            'role' => 'student',
                        ]
                    );

                    // Buscar o crear estudiante
                    \App\Models\Student::firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'nombre' => explode(' ', $name)[0] ?? $name,
                            'apellido_paterno' => explode(' ', $name)[1] ?? '',
                            'apellido_materno' => explode(' ', $name)[2] ?? '',
                            'matricula' => 'GC-' . substr($student['userId'], -6),
                            'email' => $email,
                            'group_id' => 1, // Grupo por defecto
                        ]
                    );

                    $syncedCount++;
                }
            }

            return $this->successResponse([
                'synced_count' => $syncedCount,
                'total_students' => count($students),
            ], "Successfully synced {$syncedCount} students");
        } catch (\Exception $e) {
            return $this->errorResponse('Error syncing students: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Desconectar de Google Classroom
     */
    public function disconnect(): JsonResponse
    {
        $userId = auth()->id();
        Cache::forget("google_classroom_token_{$userId}");
        Cache::forget("google_classroom_refresh_{$userId}");

        return $this->successResponse(null, 'Disconnected from Google Classroom');
    }

    /**
     * Verificar estado de conexión
     */
    public function getConnectionStatus(): JsonResponse
    {
        $userId = auth()->id();
        $hasToken = Cache::has("google_classroom_token_{$userId}");

        return $this->successResponse([
            'is_connected' => $hasToken,
            'user_id' => $userId,
        ], 'Connection status retrieved');
    }

    /**
     * Obtener access token del cache
     */
    private function getAccessToken(): ?string
    {
        $userId = auth()->id();
        return Cache::get("google_classroom_token_{$userId}");
    }
}







