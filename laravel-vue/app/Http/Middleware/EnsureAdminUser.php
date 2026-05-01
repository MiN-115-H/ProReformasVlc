<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->unauthorizedResponse($request);
        }

        if ($user->rol !== 'admin' || ! $user->activo) {
            return response()->json(['message' => 'Acceso solo para administradores activos.'], 403);
        }

        return $next($request);
    }

    private function unauthorizedResponse(Request $request): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        return redirect()->route('login');
    }
}
