<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            abort(403, 'No tiene permiso para realizar esta acción.');
        }

        // Si el usuario es admin, permitir todo
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Verificar permiso usando Spatie Permission
        if ($permission && !$user->can($permission)) {
            abort(403, 'No tiene permiso para realizar esta acción.');
        }

        return $next($request);
    }
}
