<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GalleryRateLimiter
{
    /**
     * تعداد مجاز درخواست در دقیقه
     *
     * @var int
     */
    protected $maxAttempts = 30;

    /**
     * زمان انقضا (به ثانیه)
     *
     * @var int
     */
    protected $decaySeconds = 60;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // کلید منحصر به فرد برای محدودیت نرخ
        $key = 'gallery_rate_limit:' . ($request->user()?->id ?: $request->ip());

        // بررسی تعداد تلاش‌های فعلی
        $currentAttempts = Cache::get($key, 0);

        // بررسی محدودیت نرخ
        if ($currentAttempts >= $this->maxAttempts) {
            // زمان باقیمانده تا انقضای محدودیت
            $remainingSeconds = Cache::ttl($key);

            // اگر ttl ارائه نشد یا 0 بود
            if ($remainingSeconds <= 0) {
                $remainingSeconds = $this->decaySeconds;
            }

            // لاگ کردن محدودیت نرخ
            Log::warning('محدودیت نرخ برای API گالری', [
                'user_id' => $request->user()?->id ?? 'غیر احراز هویت شده',
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'seconds_to_wait' => $remainingSeconds,
                'attempts' => $currentAttempts,
            ]);

            // اگر درخواست AJAX است، پاسخ JSON بده
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "تعداد درخواست‌های شما بیش از حد مجاز است. لطفاً {$remainingSeconds} ثانیه دیگر تلاش کنید."
                ], 429);
            }

            // در غیر این صورت، پیام خطا را با فلش سشن به صفحه قبل برگردان
            return redirect()->back()->with('error',
                "تعداد درخواست‌های شما بیش از حد مجاز است. لطفاً {$remainingSeconds} ثانیه دیگر تلاش کنید."
            );
        }

        // افزایش شمارنده
        Cache::put($key, $currentAttempts + 1, $this->decaySeconds);

        // افزودن هدرهای محدودیت نرخ به پاسخ
        $response = $next($request);

        if (method_exists($response, 'header')) {
            $response->header('X-RateLimit-Limit', $this->maxAttempts);
            $response->header('X-RateLimit-Remaining', $this->maxAttempts - $currentAttempts - 1);
            $response->header('X-RateLimit-Reset', now()->addSeconds($this->decaySeconds)->getTimestamp());
        }

        return $response;
    }
}
