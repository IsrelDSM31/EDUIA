<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints para autenticación de usuarios"
 * )
 */
class AuthApiController extends ApiController
{
    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Registrar un nuevo usuario",
     *     description="Crea un usuario y devuelve el token de acceso",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Juan Pérez", description="Nombre completo"),
     *             @OA\Property(property="email", type="string", format="email", example="juan@iaedu.com", description="Email"),
     *             @OA\Property(property="password", type="string", example="Password123!", description="Contraseña"),
     *             @OA\Property(property="password_confirmation", type="string", example="Password123!", description="Confirmación de contraseña"),
     *             @OA\Property(property="role", type="string", enum={"admin","teacher","student"}, example="student", description="Rol opcional (por defecto student)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registro exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Registered successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="token", type="string", example="1|abc123def456...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Datos inválidos"
     *     )
     * )
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'nullable|in:admin,teacher,student',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'], // hashed via casts
            'role' => $validated['role'] ?? 'student',
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'Registered successfully')->setStatusCode(201);
    }
    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Iniciar sesión de usuario",
     *     description="Autentica un usuario con email y contraseña, devuelve un token de acceso",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@iaedu.com", description="Email del usuario"),
     *             @OA\Property(property="password", type="string", example="password123", description="Contraseña del usuario")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Admin User"),
     *                     @OA\Property(property="email", type="string", example="admin@iaedu.com"),
     *                     @OA\Property(property="role", type="string", example="admin"),
     *                     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true)
     *                 ),
     *                 @OA\Property(property="token", type="string", example="1|abc123def456...", description="Token de acceso")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Credenciales inválidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid credentials"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="These credentials do not match our records."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Demasiados intentos de login",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Too many login attempts. Please try again later.")
     *         )
     *     )
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 'Login successful');
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Cerrar sesión de usuario",
     *     description="Invalida el token de acceso del usuario autenticado",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logged out successfully"),
     *             @OA\Property(property="data", type="null", example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/auth/user",
     *     summary="Obtener información del usuario autenticado",
     *     description="Devuelve la información del usuario actualmente autenticado",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Información del usuario obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User information retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function user(Request $request): JsonResponse
    {
        return $this->successResponse($request->user(), 'User information retrieved successfully');
    }
} 