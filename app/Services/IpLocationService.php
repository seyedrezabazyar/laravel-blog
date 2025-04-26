<?php

namespace App\Services;

class IpLocationService
{
    public function isIranianIp(?string $ip = null): bool
    {
        if (is_null($ip)) {
            $ip = request()->ip();
        }

        // اگر در محیط محلی هستیم، یک مقدار تصادفی برمی‌گردانیم
        // تا بتوانید هر دو حالت را ببینید
        if ($ip === '127.0.0.1' || $ip === '::1') {
            // برای تست - در هر بار بارگذاری به صورت تصادفی
            // true یا false برمی‌گرداند
            return (rand(0, 1) === 1);
        }

        // استفاده از IP-API (کاملاً رایگان)
        $url = "http://ip-api.com/json/{$ip}?fields=status,countryCode";
        try {
            $response = @file_get_contents($url);
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['status']) && $data['status'] === 'success' && isset($data['countryCode'])) {
                    return $data['countryCode'] === 'IR';
                }
            }
        } catch (\Exception $e) {
            // سرویس در دسترس نیست، روش بعدی را امتحان می‌کنیم
        }

        // روش دوم: API دیگر (اگر اولی کار نکرد)
        try {
            $response = @file_get_contents("https://ipapi.co/{$ip}/country/");
            if ($response) {
                return trim($response) === 'IR';
            }
        } catch (\Exception $e) {
            // سرویس دوم هم در دسترس نیست
        }

        // اگر هر دو روش شکست خورد، بررسی محدوده IP را انجام می‌دهیم
        $ipLong = ip2long($ip);
        if ($ipLong === false) {
            return false; // IP نامعتبر است
        }

        // فقط چند محدوده مهم IP ایران
        $iranianRanges = [
            [ip2long('5.160.0.0'), ip2long('5.191.255.255')],
            [ip2long('5.208.0.0'), ip2long('5.223.255.255')],
            [ip2long('2.144.0.0'), ip2long('2.147.255.255')],
            [ip2long('37.98.0.0'), ip2long('37.98.255.255')],
            [ip2long('91.98.0.0'), ip2long('91.99.255.255')],
            [ip2long('46.51.0.0'), ip2long('46.51.255.255')],
            [ip2long('185.88.112.0'), ip2long('185.88.115.255')],
            [ip2long('178.216.248.0'), ip2long('178.216.251.255')],
        ];

        foreach ($iranianRanges as $range) {
            if ($ipLong >= $range[0] && $ipLong <= $range[1]) {
                return true;
            }
        }

        return false;
    }
}
