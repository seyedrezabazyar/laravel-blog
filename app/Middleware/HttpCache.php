<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class HttpCache
{
    /**
     * صفحاتی که باید کش شوند با زمان‌های انقضای آنها به دقیقه
     */
    protected $cacheable = [
        'blog.show' => 60, // ۱ ساعت کش برای صفحات پست‌ها
        'blog.index' => 60, // ۳۰ دقیقه برای صفحه اصلی بلاگ
        'blog.category' => 60, // ۶۰ دقیقه برای صفحات دسته‌بندی
        'blog.author' => 60, // ۳۰ دقیقه برای صفحات نویسنده
        'blog.publisher' => 60, // ۳۰ دقیقه برای صفحات ناشر
        'blog.tag' => 60, // ۳۰ دقیقه برای صفحات تگ
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // فقط درخواست‌های GET را کش می‌کنیم
        if (!$request->isMethod('GET')) {
            return $response;
        }

        // برای کاربران احراز هویت شده کش نمی‌کنیم
        if (auth()->check()) {
            return $response->header('Cache-Control', 'no-store, private');
        }

        $routeName = $request->route()?->getName();

        // اگر مسیر فعلی باید کش شود
        if (isset($this->cacheable[$routeName])) {
            $minutes = $this->cacheable[$routeName];
            $response->header('Cache-Control', 'public, max-age=' . ($minutes * 60));
            $response->header('Expires', now()->addMinutes($minutes)->format('D, d M Y H:i:s') . ' GMT');

            // اضافه کردن ETag برای کش بهتر
            $etag = md5($request->fullUrl() . time() / 3600); // زمان را به ساعت گرد می‌کنیم
            $response->header('ETag', $etag);

            // اضافه کردن Last-Modified
            $response->header('Last-Modified', now()->format('D, d M Y H:i:s') . ' GMT');
        }

        return $response;
    }
}
