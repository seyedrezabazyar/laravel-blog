<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Si estamos en producción, deshabilitamos el log de consultas SQL
        if ($this->app->environment('production')) {
            DB::disableQueryLog();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Para asegurar que la longitud de las cadenas en las migraciones no cause problemas
        Schema::defaultStringLength(191);

        // Preferir URLs HTTPS en producción
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Usar Bootstrap para paginación
        Paginator::useBootstrap();

        // Registrar componentes Blade para mejora de rendimiento
        $this->registerBladeComponents();

        // Escuchar consultas lentas
        $this->listenToSlowQueries();

        // Compartir vistas globales estáticas (sin consultas a la BD)
        $this->shareGlobalViewsWithoutQueries();

        // Configurar caché de respuestas
        $this->setupResponseCaching();
    }

    /**
     * Registrar componentes Blade para mejora de rendimiento
     */
    protected function registerBladeComponents(): void
    {
        // Registrar componentes Blade importantes
        Blade::component('components.preload', 'preload');
        Blade::component('components.critical-css', 'critical-css');
        Blade::component('components.blog-card', 'blog-card');
        Blade::component('components.simple-blog-card', 'simple-blog-card');
        Blade::component('components.meta-component', 'meta');

        // Definir directivas Blade útiles
        Blade::directive('cacheBuster', function ($expression) {
            return "<?php echo 'v=' . filemtime(public_path($expression)); ?>";
        });
    }

    /**
     * Escuchar consultas lentas
     */
    protected function listenToSlowQueries(): void
    {
        // Solo en entorno no productivo
        if (!$this->app->isProduction()) {
            DB::listen(function ($query) {
                if ($query->time > 100) { // Consultas más lentas que 100ms
                    Log::info('Consulta lenta', [
                        'query' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time,
                    ]);
                }
            });
        }
    }

    /**
     * Compartir vistas globales con todas las plantillas - sin consultas
     */
    protected function shareGlobalViewsWithoutQueries(): void
    {
        // Compartir datos comunes con todas las plantillas
        View::share('appName', config('app.name'));

        // Categorías estáticas - sin necesidad de caché o consultas
        $globalCategories = [
            (object) ['name' => 'Novela', 'slug' => 'novel', 'posts_count' => 25],
            (object) ['name' => 'Ciencia', 'slug' => 'science', 'posts_count' => 18],
            (object) ['name' => 'Historia', 'slug' => 'history', 'posts_count' => 15],
            (object) ['name' => 'Filosofía', 'slug' => 'philosophy', 'posts_count' => 12],
            (object) ['name' => 'Psicología', 'slug' => 'psychology', 'posts_count' => 20],
            (object) ['name' => 'Infantil', 'slug' => 'children', 'posts_count' => 10],
            (object) ['name' => 'Éxito', 'slug' => 'success', 'posts_count' => 22],
            (object) ['name' => 'Arte', 'slug' => 'art', 'posts_count' => 15],
        ];

        // Compartir directamente - sin consulta caché
        View::share('globalCategories', $globalCategories);
    }

    /**
     * Configurar caché de respuestas
     */
    protected function setupResponseCaching(): void
    {
        // Configuramos las respuestas para mejor caché
        Response::macro('cache', function ($seconds = 60) {
            $response = $this;

            if (!$response->headers->has('Cache-Control')) {
                $response->setPublic();
                $response->setMaxAge($seconds);
                $response->headers->addCacheControlDirective('must-revalidate', true);
            }

            return $response;
        });
    }
}
