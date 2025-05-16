<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            // لاگ کردن تلاش دسترسی غیرمجاز
            Log::warning('تلاش دسترسی غیرمجاز به بخش مدیریت', [
                'user_id' => auth()->id() ?? 'غیر احراز هویت شده',
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            // اگر کاربر لاگین کرده ولی مدیر نیست، یک پیام خطای 403 نمایش بده
            if (auth()->check()) {
                abort(403, 'شما دسترسی به این بخش را ندارید.');
            }

            // اگر کاربر لاگین نکرده است، به صفحه لاگین هدایت کن
            return redirect()->route('login')
                ->with('error', 'برای دسترسی به این بخش ابتدا باید وارد شوید.');
        }

        return $next($request);
    }
}
