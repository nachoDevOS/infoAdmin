<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('app.api_token');

        // Si no hay token configurado, permitir sin autenticación (backwards compat)
        if (empty($token)) {
            return $next($request);
        }

        $enviado = $request->header('X-Api-Token')
            ?? $request->input('api_token');

        if ($enviado !== $token) {
            return response()->json(['ok' => false, 'error' => 'Token inválido'], 401);
        }

        return $next($request);
    }
}
