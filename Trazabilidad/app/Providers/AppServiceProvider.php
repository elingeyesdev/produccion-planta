<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\App;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Add custom views path where we placed Blade files
        $customPath = base_path('routes/resources/views');
        if (is_dir($customPath)) {
            View::addLocation($customPath);
        }

        // Configurar paginación para usar Bootstrap 4 (compatible con AdminLTE)
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.bootstrap-4');
        \Illuminate\Pagination\Paginator::defaultSimpleView('vendor.pagination.simple-bootstrap-4');

        // Use custom permission logic
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasPermissionTo')) {
                return $user->hasPermissionTo($ability) ?: null;
            }
            return null;
        });
    }
}
