<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \App\Http\Middleware\Cors::class,
        ]);
        
        // Establecer idioma español para todas las rutas web
        $middleware->web(prepend: [
            \App\Http\Middleware\SetLocale::class,
        ]);
        
        // Registrar middleware de Spatie Permission
        $middleware->alias([
            // 'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            // 'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return JSON responses for ALL API routes errors
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                // Don't expose sensitive error details in production
                $message = $e->getMessage();
                if (config('app.env') === 'production' && $status === 500) {
                    $message = 'Internal server error';
                }

                return response()->json([
                    'message' => $message,
                    'error' => class_basename($e),
                ], $status);
            }
        });

        // Manejar error 419 (Page Expired) para la creación de pedidos
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->is('mis-pedidos') && $request->isMethod('post')) {
                return redirect()->route('mis-pedidos')
                    ->with('error', 'Su sesión ha expirado. Por favor, intente crear el pedido nuevamente.');
            }
        });
    })->create();
