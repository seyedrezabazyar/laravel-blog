<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class GeoLocationService
{
    /**
     * کلید API سرویس IPQualityScore
     *
     * @var string
     */
    protected $ipqsApiKey;

    /**
     * مدت زمان ذخیره در کش (به دقیقه)
     *
     * @var int
     */
    protected $cacheTime = 30;

    /**
     * ایجاد نمونه جدید از سرویس
     */
    public function __construct()
    {
        $this->ipqsApiKey = config('services.ipqualityscore.api_key');
    }

    /**
     * دریافت آی‌پی واقعی کاربر با در نظر گرفتن هدرهای پروکسی
     *
     * @param Request|null $request
     * @return string
     */
    public function getRealIp(?Request $request = null): string
    {
        // اگر درخواست تعریف نشده، از درخواست فعلی استفاده می‌کنیم
        if (is_null($request)) {
            $request = request();
        }

        // آی‌پی‌های محلی
        $localIps = ['127.0.0.1', '::1'];
        $ip = $request->ip();

        // لاگ برای دیباگ
        Log::debug('Getting real IP', [
            'request_ip' => $ip,
            'server_vars' => $request->server->all()
        ]);

        // بررسی حالت شبیه‌سازی در محیط محلی
        if (in_array($ip, $localIps)) {
            // اگر در محیط محلی هستیم، وضعیت شبیه‌سازی را بررسی کنیم
            if (Session::has('simulate_iranian_ip')) {
                Log::info('Using simulated IP mode in local environment', [
                    'is_iranian' => Session::get('simulate_iranian_ip')
                ]);
                return Session::get('simulate_iranian_ip') ? '185.88.112.1' : '8.8.8.8';
            }

            // اگر شبیه‌سازی نشده، IP محلی را برگردانیم
            return $ip;
        }

        // آرایه‌ای از هدرهای ممکن برای یافتن IP واقعی
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',  // پروکسی‌های عمومی
            'HTTP_X_REAL_IP',        // Nginx
            'HTTP_X_CLIENT_IP',      // برخی پروکسی‌ها
            'HTTP_CLIENT_IP',        // برخی پروکسی‌ها
            'REMOTE_ADDR'            // آدرس IP اصلی
        ];

        // بررسی هدرهای مختلف برای دریافت IP واقعی
        foreach ($headers as $header) {
            $headerValue = $request->server($header);
            if (!empty($headerValue)) {
                // برای X-Forwarded-For که می‌تواند چندین IP داشته باشد
                if ($header === 'HTTP_X_FORWARDED_FOR') {
                    $ips = explode(',', $headerValue);
                    $clientIp = trim($ips[0]);
                    Log::info("IP detected from {$header}: {$clientIp}");
                    return $clientIp;
                }

                Log::info("IP detected from {$header}: {$headerValue}");
                return $headerValue;
            }
        }

        // اگر هیچ هدری پیدا نشد، به IP پیش‌فرض بازگردیم
        Log::info('No special headers found, using default request IP', ['ip' => $ip]);
        return $ip;
    }

    /**
     * بررسی اینکه آیا آدرس IP ایرانی است
     *
     * @param string|null $ip آدرس IP
     * @return bool
     */
    public function isIranianIp(?string $ip = null): bool
    {
        if (is_null($ip)) {
            $ip = $this->getRealIp();
        }

        // عملیات پاکسازی را انجام می‌دهیم تا مطمئن شویم آی‌پی فاقد کاراکترهای اضافی است
        $ip = trim($ip);

        // لاگ برای دیباگ
        Log::info("Checking if IP is Iranian: {$ip}");

        // در محیط محلی با شبیه‌سازی
        if ($ip === '185.88.112.1') {
            Log::info("Simulated Iranian IP detected");
            return true;  // IP ایرانی شبیه‌سازی شده
        } elseif ($ip === '8.8.8.8') {
            Log::info("Simulated foreign IP detected");
            return false; // IP خارجی شبیه‌سازی شده
        }

        // کلید کش شامل آی‌پی و نسخه سرویس
        $cacheKey = 'ip_location_v3_' . str_replace(['.', ':'], '_', $ip);

        // کاهش مدت زمان کش برای بهبود دقت
        return Cache::remember($cacheKey, now()->addMinutes($this->cacheTime), function () use ($ip) {
            // اگر IPQualityScore کانفیگ شده است، از آن استفاده می‌کنیم
            try {
                if (!empty($this->ipqsApiKey)) {
                    $result = $this->checkWithIPQS($ip);
                    Log::info("IPQualityScore result for {$ip}: " . ($result ? 'Iranian' : 'Not Iranian'));
                    return $result;
                }
            } catch (Exception $e) {
                Log::warning("IPQualityScore check failed: {$e->getMessage()}");
            }

            // استفاده از روش‌های جایگزین با افزودن روش‌های جدید
            $result = $this->checkWithFallbackMethods($ip);
            Log::info("Fallback methods result for {$ip}: " . ($result ? 'Iranian' : 'Not Iranian'));
            return $result;
        });
    }

    /**
     * بررسی با استفاده از IPQualityScore
     *
     * @param string $ip
     * @return bool
     * @throws Exception
     */
    protected function checkWithIPQS(string $ip): bool
    {
        if (empty($this->ipqsApiKey)) {
            throw new Exception("IPQualityScore API key not configured");
        }

        $response = Http::timeout(5)
            ->get("https://www.ipqualityscore.com/api/json/ip/{$this->ipqsApiKey}/{$ip}");

        if ($response->successful()) {
            $data = $response->json();

            // ثبت اطلاعات کامل در لاگ برای دیباگ
            Log::debug("IPQualityScore full response", $data);

            // بررسی کد کشور در پاسخ API
            return isset($data['country_code']) && $data['country_code'] === 'IR';
        }

        throw new Exception("IPQualityScore API request failed: " . $response->status() . " - " . $response->body());
    }

    /**
     * بررسی با استفاده از روش‌های جایگزین قبلی با بهبود عملکرد
     *
     * @param string $ip
     * @return bool
     */
    protected function checkWithFallbackMethods(string $ip): bool
    {
        // روش اول: IP-API.com
        try {
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=status,countryCode");

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] === 'success' && isset($data['countryCode'])) {
                    Log::info("IP-API.com detected country code: " . $data['countryCode']);
                    return $data['countryCode'] === 'IR';
                }
            }
        } catch (Exception $e) {
            Log::warning("IP-API.com check failed: {$e->getMessage()}");
        }

        // روش دوم: ipinfo.io - افزودن یک سرویس جدید برای دقت بیشتر
        try {
            $response = Http::timeout(3)->get("https://ipinfo.io/{$ip}/country");

            if ($response->successful()) {
                $countryCode = trim($response->body());
                Log::info("ipinfo.io detected country code: " . $countryCode);
                return $countryCode === 'IR';
            }
        } catch (Exception $e) {
            Log::warning("ipinfo.io check failed: {$e->getMessage()}");
        }

        // روش سوم: ipapi.co
        try {
            $response = Http::timeout(3)->get("https://ipapi.co/{$ip}/country/");

            if ($response->successful()) {
                $countryCode = trim($response->body());
                Log::info("ipapi.co detected country code: " . $countryCode);
                return $countryCode === 'IR';
            }
        } catch (Exception $e) {
            Log::warning("ipapi.co check failed: {$e->getMessage()}");
        }

        // روش چهارم: GeoPlugin
        try {
            $response = Http::timeout(3)->get("http://www.geoplugin.net/json.gp?ip={$ip}");

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['geoplugin_countryCode'])) {
                    Log::info("GeoPlugin detected country code: " . $data['geoplugin_countryCode']);
                    return $data['geoplugin_countryCode'] === 'IR';
                }
            }
        } catch (Exception $e) {
            Log::warning("GeoPlugin check failed: {$e->getMessage()}");
        }

        // روش پنجم: بررسی محدوده IP
        // توجه: این روش کمترین دقت را دارد و فقط به عنوان آخرین راه حل استفاده می‌شود
        $ipLong = ip2long($ip);
        if ($ipLong === false) {
            return false; // IP نامعتبر است
        }

        // محدوده‌های مهم IP ایران
        $iranianRanges = [
            // محدوده‌های موجود
            [ip2long('5.160.0.0'), ip2long('5.191.255.255')],
            [ip2long('5.208.0.0'), ip2long('5.223.255.255')],
            [ip2long('2.144.0.0'), ip2long('2.147.255.255')],
            [ip2long('37.98.0.0'), ip2long('37.98.255.255')],
            [ip2long('91.98.0.0'), ip2long('91.99.255.255')],
            [ip2long('46.51.0.0'), ip2long('46.51.255.255')],
            [ip2long('185.88.112.0'), ip2long('185.88.115.255')],
            [ip2long('178.216.248.0'), ip2long('178.216.251.255')],

            // محدوده‌های اضافی
            [ip2long('79.127.0.0'), ip2long('79.127.255.255')],
            [ip2long('80.66.0.0'), ip2long('80.66.255.255')],
            [ip2long('81.12.0.0'), ip2long('81.12.255.255')],
            [ip2long('94.182.0.0'), ip2long('94.182.255.255')],
            [ip2long('95.38.0.0'), ip2long('95.38.255.255')],
            [ip2long('188.211.0.0'), ip2long('188.211.255.255')],
            [ip2long('217.218.0.0'), ip2long('217.219.255.255')],
            [ip2long('31.14.80.0'), ip2long('31.14.95.255')],
            [ip2long('82.99.192.0'), ip2long('82.99.255.255')],
            [ip2long('89.235.64.0'), ip2long('89.235.127.255')],
            [ip2long('93.110.0.0'), ip2long('93.110.255.255')],
            [ip2long('176.65.128.0'), ip2long('176.65.255.255')],
            [ip2long('213.233.160.0'), ip2long('213.233.191.255')],
        ];

        foreach ($iranianRanges as $range) {
            if ($ipLong >= $range[0] && $ipLong <= $range[1]) {
                Log::info("IP range check detected {$ip} as Iranian IP");
                return true;
            }
        }

        Log::info("IP {$ip} not detected as Iranian in any method");
        return false;
    }

    /**
     * تنظیم کردن حالت شبیه‌سازی IP ایرانی (برای تست در محیط توسعه)
     *
     * @param bool $isIranian
     * @return void
     */
    public function simulateIranianIp(bool $isIranian): void
    {
        Session::put('simulate_iranian_ip', $isIranian);

        // پاک کردن کش IP‌های شبیه‌سازی شده
        $this->clearIpCache('185.88.112.1'); // آی‌پی ایرانی نمونه
        $this->clearIpCache('8.8.8.8');      // آی‌پی خارجی نمونه

        // لاگ برای دیباگ
        Log::info('IP simulation mode set', [
            'is_iranian' => $isIranian,
            'simulate_ip' => $isIranian ? '185.88.112.1' : '8.8.8.8'
        ]);
    }

    /**
     * پاک کردن کش IP
     *
     * @param string|null $ip آدرس IP
     * @return void
     */
    public function clearIpCache(?string $ip = null): void
    {
        if (is_null($ip)) {
            $ip = $this->getRealIp();
        }

        // پاک کردن تمام نسخه‌های کلید کش
        $keys = [
            'ip_location_' . str_replace(['.', ':'], '_', $ip),     // کلید قدیمی
            'ip_location_v2_' . str_replace(['.', ':'], '_', $ip),  // کلید نسخه ۲
            'ip_location_v3_' . str_replace(['.', ':'], '_', $ip),  // کلید نسخه ۳
        ];

        foreach ($keys as $cacheKey) {
            Cache::forget($cacheKey);
        }

        Log::info("IP cache cleared for {$ip}");
    }
}
