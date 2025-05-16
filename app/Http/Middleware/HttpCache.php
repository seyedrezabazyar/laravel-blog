<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HttpCache
{
    /**
     * صفحاتی که باید کش شوند با زمان‌های انقضای آنها به دقیقه
     */
    protected $cacheable = [
        'blog.show' => 60,
        'blog.index' => 60,
        'blog.category' => 60,
        'blog.author' => 60,
        'blog.publisher' => 60,
        'blog.tag' => 60,
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // فقط درخواست‌های GET را کش می‌کنیم
        if (!$request->isMethod('GET') || auth()->check()) {
            return $response->header('Cache-Control', 'no-store, private');
        }

        $routeName = $request->route()?->getName();
        if (isset($this->cacheable[$routeName])) {
            $minutes = $this->cacheable[$routeName];
            $response->header('Cache-Control', 'public, max-age=' . ($minutes * 60));
            $response->header('Expires', now()->addMinutes($minutes)->format('D, d M Y H:i:s') . ' GMT');
            $response->header('ETag', md5($request->fullUrl() . time() / 3600));
            $response->header('Last-Modified', now()->format('D, d M Y H:i:s') . ' GMT');
        }

        return $response;
    }
}
