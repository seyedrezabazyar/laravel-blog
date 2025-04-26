<?php

namespace App\Http\Middleware;

use App\Services\IpLocationService;
use Closure;
use Illuminate\Http\Request;

class DetectCountry
{
    public function __invoke(Request $request, Closure $next)
    {
        $ipLocationService = new IpLocationService();

        // اگر در محیط محلی هستیم، برای تست
        if ($request->ip() === '127.0.0.1' || $request->ip() === '::1') {
            // می‌توانید این مقدار را برای تست تغییر دهید
            $isIranianIp = true;
        } else {
            $isIranianIp = $ipLocationService->isIranianIp($request->ip());
        }

        // اضافه کردن متغیر به view
        view()->share('isIranianIp', $isIranianIp);

        return $next($request);
    }
}
