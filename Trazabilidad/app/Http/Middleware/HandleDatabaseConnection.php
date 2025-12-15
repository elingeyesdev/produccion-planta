<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HandleDatabaseConnection
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
        } catch (\Illuminate\Database\QueryException $e) {
            // Si es un error de conexión a la base de datos
            if (strpos($e->getMessage(), 'Connection refused') !== false || 
                strpos($e->getMessage(), 'could not connect') !== false) {
                
                Log::error('Error de conexión a la base de datos', [
                    'error' => $e->getMessage(),
                    'url' => $request->fullUrl(),
                ]);

                // Limpiar sesión si hay error de conexión
                try {
                    Auth::logout();
                    session()->flush();
                } catch (\Exception $logoutException) {
                    // Ignorar errores al limpiar sesión
                }

                // Si es una ruta de API, devolver JSON
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error de conexión a la base de datos. Por favor, verifique que el servidor de base de datos esté ejecutándose.',
                        'error' => config('app.debug') ? $e->getMessage() : null
                    ], 503);
                }

                // Si es una ruta web, redirigir al login con mensaje
                return redirect()->route('login')
                    ->with('error', 'Error de conexión a la base de datos. Por favor, verifique que el servidor de base de datos esté ejecutándose.');
            }

            // Si no es un error de conexión, lanzar la excepción normalmente
            throw $e;
        }
    }
}


