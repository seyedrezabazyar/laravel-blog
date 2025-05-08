<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class HttpCache
{
    /**
     * Pages that should be cached with their expiry times in minutes
     */
    protected $cacheable = [
        'blog.show' => 60, // 1 hour cache for blog post pages
        'blog.index' => 30, // 30 minutes for the blog index
        'blog.category' => 30, // 30 minutes for category pages
        'blog.author' => 30, // 30 minutes for author pages
        'blog.publisher' => 30, // 30 minutes for publisher pages
        'blog.tag' => 30, // 30 minutes for tag pages
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only apply caching to GET requests
        if (!$request->isMethod('GET')) {
            return $response;
        }

        // Don't cache for authenticated users
        if (auth()->check()) {
            return $response->header('Cache-Control', 'no-store, private');
        }

        $routeName = $request->route()?->getName();

        // If the current route should be cached
        if (isset($this->cacheable[$routeName])) {
            $minutes = $this->cacheable[$routeName];

            // Set cache headers
            $response->header('Cache-Control', 'public, max-age=' . ($minutes * 60));
            $response->header('Expires', Carbon::now()->addMinutes($minutes)->format('D, d M Y H:i:s').' GMT');

            // Add a cache tag (useful for cache invalidation)
            $response->header('X-Cache-Tag', $routeName);
        } else {
            // Default cache policy for other pages
            $response->header('Cache-Control', 'no-cache, must-revalidate');
        }

        return $response;
    }
}
