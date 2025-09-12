<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si no hay usuario autenticado, redirigir al login
        if (!$user) {
            return redirect()->route('login');
        }

        // Los admins siempre tienen acceso
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Verificar si el usuario tiene suscripción activa
        if (!$user->canAccessSystem()) {
            // Si no tiene suscripción activa, redirigir a los planes
            return redirect()->route('subscription.plans')
                ->with('error', 'Necesitas una suscripción activa para acceder al sistema.');
        }

        return $next($request);
    }
}
