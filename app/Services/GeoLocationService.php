<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoLocationService
{
    /**
     * کلید API سرویس IPQualityScore
     *
     * @var string
     */
    protected $ipqsApiKey;

    /**
     * مدت زمان ذخیره در کش (به ساعت)
     *
     * @var int
     */
    protected $cacheTime = 24;

    /**
     * ایجاد نمونه جدید از سرویس
     */
    public function __construct()
    {
        $this->ipqsApiKey = config('services.ipqualityscore.api_key');
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
            $ip = request()->ip();
        }

        // اگر در محیط محلی هستیم
        if ($ip === '127.0.0.1' || $ip === '::1') {
            // می‌توانید این مقدار را برای تست تغییر دهید
            return config('app.debug') ? true : false;
        }

        // بررسی کش
        $cacheKey = 'ip_location_' . str_replace(['.', ':'], '_', $ip);

        return Cache::remember($cacheKey, now()->addHours($this->cacheTime), function () use ($ip) {
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

            // استفاده از روش‌های جایگزین
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

        $response = Http::get("https://www.ipqualityscore.com/api/json/ip/{$this->ipqsApiKey}/{$ip}");

        if ($response->successful()) {
            $data = $response->json();

            // بررسی کد کشور در پاسخ API
            return isset($data['country_code']) && $data['country_code'] === 'IR';
        }

        throw new Exception("IPQualityScore API request failed: " . $response->status());
    }

    /**
     * بررسی با استفاده از روش‌های جایگزین قبلی
     *
     * @param string $ip
     * @return bool
     */
    protected function checkWithFallbackMethods(string $ip): bool
    {
        // روش اول: IP-API.com
        try {
            $url = "http://ip-api.com/json/{$ip}?fields=status,countryCode";
            $response = @file_get_contents($url);
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['status']) && $data['status'] === 'success' && isset($data['countryCode'])) {
                    Log::info("IP-API.com detected country code: " . $data['countryCode']);
                    return $data['countryCode'] === 'IR';
                }
            }
        } catch (Exception $e) {
            Log::warning("IP-API.com check failed: {$e->getMessage()}");
        }

        // روش دوم: ipapi.co
        try {
            $response = @file_get_contents("https://ipapi.co/{$ip}/country/");
            if ($response) {
                Log::info("ipapi.co detected country code: " . trim($response));
                return trim($response) === 'IR';
            }
        } catch (Exception $e) {
            Log::warning("ipapi.co check failed: {$e->getMessage()}");
        }

        // روش سوم: بررسی محدوده IP
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
}
