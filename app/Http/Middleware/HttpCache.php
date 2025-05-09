<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class HttpCache
{
    /**
     * Páginas que deben cachearse con sus tiempos de expiración en minutos
     */
    protected $cacheable = [
        'blog.show' => 60, // 1 hora de caché para páginas de posts
        'blog.index' => 30, // 30 minutos para el índice del blog
        'blog.category' => 60, // 60 minutos para páginas de categorías
        'blog.author' => 30, // 30 minutos para páginas de autor
        'blog.publisher' => 30, // 30 minutos para páginas de publishers
        'blog.tag' => 30, // 30 minutos para páginas de tags
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo cacheamos peticiones GET
        if (!$request->isMethod('GET')) {
            return $response;
        }

        // No cacheamos para usuarios autenticados
        if (auth()->check()) {
            return $response->header('Cache-Control', 'no-store, private');
        }

        $routeName = $request->route()?->getName();

        // Si la ruta actual debe cachearse
        if ($routeName === 'blog.category') {
            // Caché de 60 minutos para páginas de categorías
            $response->header('Cache-Control', 'public, max-age=3600');
            $response->header('Expires', now()->addMinutes(60)->format('D, d M Y H:i:s').' GMT');
        }

        return $response;
    }
}
