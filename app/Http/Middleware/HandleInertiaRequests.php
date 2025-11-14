<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Asegurar que siempre tengamos un token CSRF válido
        $csrfToken = $request->session()->token();
        
        // Si por alguna razón no hay token, generar uno nuevo
        if (!$csrfToken) {
            $csrfToken = csrf_token();
        }
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'csrf_token' => $csrfToken,
            'csrfToken' => $csrfToken, // Alias para compatibilidad
        ];
    }
}
