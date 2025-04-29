<?php

namespace App\Http\Middleware;

use App\Services\GeoLocationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DetectCountry
{
    /**
     * سرویس موقعیت‌یابی IP
     *
     * @var GeoLocationService
     */
    protected $geoLocationService;

    /**
     * ایجاد نمونه جدید از میدلور
     *
     * @param GeoLocationService $geoLocationService
     * @return void
     */
    public function __construct(GeoLocationService $geoLocationService)
    {
        $this->geoLocationService = $geoLocationService;
    }

    /**
     * اجرای میدلور
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function __invoke(Request $request, Closure $next)
    {
        // استفاده از متد جدید برای دریافت آی‌پی واقعی کاربر
        $ip = $this->geoLocationService->getRealIp($request);

        // بررسی آدرس IP برای تشخیص کشور ایران
        $isIranianIp = $this->geoLocationService->isIranianIp($ip);

        // لاگ برای دیباگ
        Log::debug('Country detection middleware', [
            'ip' => $ip,
            'is_iranian' => $isIranianIp
        ]);

        // اشتراک‌گذاری نتیجه با تمام ویوها
        view()->share('isIranianIp', $isIranianIp);

        // اضافه کردن مقدار به متغیرهای request برای دسترسی آسان‌تر در کنترلرها
        $request->attributes->add([
            'isIranianIp' => $isIranianIp
        ]);

        return $next($request);
    }
}
