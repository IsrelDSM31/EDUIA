<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RBAC - Role Based Access Control Middleware
 * Control de acceso basado en roles
 */
class RoleBasedAccessControl
{
    /**
     * Roles y sus permisos
     */
    protected $permissions = [
        'admin' => [
            'students.*',
            'teachers.*',
            'grades.*',
            'attendance.*',
            'alerts.*',
            'dashboard.*',
            'risk-analysis.*',
            'users.*',
            'google-classroom.*',
            'profile.*',
            'chatbot.*',
        ],
        'teacher' => [
            'students.index',
            'students.show',
            'teachers.show',
            'grades.*',
            'attendance.*',
            'alerts.index',
            'alerts.show',
            'dashboard.stats',
            'risk-analysis.*',
            'google-classroom.*',
            'profile.*',
            'chatbot.*',
        ],
        'student' => [
            'students.show',
            'grades.index',
            'grades.show',
            'attendance.index',
            'attendance.show',
            'alerts.index',
            'alerts.show',
            'dashboard.stats',
            'profile.*',
            'chatbot.message',
        ],
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $userRole = $request->user()->role;

        // Si no se especifican roles, permitir acceso
        if (empty($roles)) {
            return $next($request);
        }

        // Verificar si el usuario tiene alguno de los roles requeridos
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Insufficient permissions',
                'required_roles' => $roles,
                'user_role' => $userRole,
            ], 403);
        }

        // Verificar permisos específicos
        $route = $request->route()->getName();
        if (!$this->hasPermission($userRole, $route)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden - Access denied to this resource',
                'route' => $route,
            ], 403);
        }

        return $next($request);
    }

    /**
     * Verificar si un rol tiene permiso para una ruta
     */
    protected function hasPermission($role, $route): bool
    {
        if (!isset($this->permissions[$role])) {
            return false;
        }

        foreach ($this->permissions[$role] as $permission) {
            if ($this->matchPermission($permission, $route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Comparar permiso con ruta usando wildcards
     */
    protected function matchPermission($permission, $route): bool
    {
        // Convertir wildcards a regex
        $pattern = str_replace('\*', '.*', preg_quote($permission, '/'));
        return (bool) preg_match('/^' . $pattern . '$/', $route);
    }
}



