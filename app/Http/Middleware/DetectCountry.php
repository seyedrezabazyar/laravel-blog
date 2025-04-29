<?php

namespace App\Http\Middleware;

use App\Services\GeoLocationService;
use Closure;
use Illuminate\Http\Request;

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
        // بررسی آدرس IP برای تشخیص کشور ایران
        $isIranianIp = $this->geoLocationService->isIranianIp($request->ip());

        // اشتراک‌گذاری نتیجه با تمام ویوها
        view()->share('isIranianIp', $isIranianIp);

        return $next($request);
    }
}
