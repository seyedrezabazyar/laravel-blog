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
        'blog.category' => 60, // 60 minutes for category pages
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

        // فقط برای درخواست‌های GET کش می‌کنیم
        if (!$request->isMethod('GET')) {
            return $response;
        }

        // برای کاربران احراز هویت شده کش نمی‌کنیم
        if (auth()->check()) {
            return $response->header('Cache-Control', 'no-store, private');
        }

        $routeName = $request->route()?->getName();

        // اگر مسیر فعلی باید کش شود
        if ($routeName === 'blog.category') {
            // کش ۶۰ دقیقه‌ای برای صفحه دسته‌بندی
            $response->header('Cache-Control', 'public, max-age=3600');
            $response->header('Expires', now()->addMinutes(60)->format('D, d M Y H:i:s').' GMT');
        }

        return $response;
    }
}
