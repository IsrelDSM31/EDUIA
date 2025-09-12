<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $key = "chatbot_rate_limit_{$ip}";
        
        // Permitir 1 petición por minuto
        $maxRequests = 1;
        $windowMinutes = 1;
        
        $requests = Cache::get($key, 0);
        
        if ($requests >= $maxRequests) {
            return response()->json([
                'error' => 'Demasiadas peticiones. Por favor espera 1 minuto antes de hacer otra pregunta.',
                'retry_after' => 60
            ], 429);
        }
        
        // Incrementar contador
        Cache::put($key, $requests + 1, $windowMinutes * 60);
        
        return $next($request);
    }
}
