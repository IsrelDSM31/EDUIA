<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Rutas de API que no requieren CSRF (protegidas por Sanctum)
        '/api/*',
        '/sanctum/*',
    ];

    /**
     * Determine if the session and input CSRF tokens match.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function tokensMatch($request)
    {
        // Obtener el token de la sesión
        $sessionToken = $request->session()->token();
        
        // Si no hay sesión, no podemos validar
        if (!$sessionToken) {
            return false;
        }
        
        // Intentar obtener el token de diferentes fuentes
        $token = $this->getTokenFromRequest($request);

        // Si no hay token en el request pero es una petición de Inertia válida
        if (!$token && $this->isInertiaRequest($request)) {
            // Para peticiones de Inertia, si la sesión es válida, permitir la petición
            // Inertia maneja el CSRF a través de las cookies de sesión
            if ($request->hasSession() && $sessionToken) {
                return true;
            }
        }

        // Si aún no hay token, intentar obtenerlo desde la cookie XSRF-TOKEN
        // Esto puede pasar si el header no se envió pero la cookie está disponible
        if (!$token) {
            $cookieToken = $request->cookie('XSRF-TOKEN');
            if ($cookieToken && hash_equals($sessionToken, $cookieToken)) {
                return true;
            }
        }

        // Comparar tokens normalmente
        if ($token && is_string($sessionToken) && is_string($token)) {
            return hash_equals($sessionToken, $token);
        }

        return false;
    }

    /**
     * Get the CSRF token from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function getTokenFromRequest($request)
    {
        // Intentar obtener el token del header
        $token = $request->header('X-CSRF-TOKEN');

        // Si no está en el header, intentar desde la cookie XSRF-TOKEN
        if (!$token) {
            $token = $request->cookie('XSRF-TOKEN');
        }

        // Si aún no está, intentar desde el input _token
        if (!$token) {
            $token = $request->input('_token');
        }

        return $token;
    }

    /**
     * Determine if the request is an Inertia request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isInertiaRequest($request)
    {
        // Inertia envía estos headers en sus peticiones
        return $request->header('X-Inertia') === 'true' ||
               $request->header('X-Inertia-Version') !== null ||
               ($request->header('X-Requested-With') === 'XMLHttpRequest' && 
                $request->header('Accept') && 
                str_contains($request->header('Accept'), 'text/html'));
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, $next)
    {
        if ($this->isReading($request) ||
            $this->runningUnitTests() ||
            $this->inExceptArray($request) ||
            $this->tokensMatch($request)) {
            return $this->addCookieToResponse($request, $next($request));
        }

        throw new TokenMismatchException('CSRF token mismatch.');
    }
}
