<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;

class HandleCsrfToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (TokenMismatchException $e) {
            \Log::warning('CSRF Token mismatch', [
                'url' => $request->url(),
                'method' => $request->method(),
                'user_id' => $request->user()?->id,
                'session_id' => $request->session()->getId(),
                'has_csrf_header' => $request->hasHeader('X-CSRF-TOKEN'),
                'csrf_token' => $request->header('X-CSRF-TOKEN'),
                'session_token' => $request->session()->token(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Token CSRF expirado o inválido. Por favor, recarga la página.',
                    'code' => 'csrf_mismatch'
                ], 419);
            }

            return redirect()->back()->withErrors(['csrf' => 'Token CSRF expirado. Por favor, intenta de nuevo.']);
        }
    }
} 