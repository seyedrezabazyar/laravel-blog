<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class ImageUrlService
{
    /**
     * هاست پیش‌فرض سرور تصاویر
     */
    private const IMAGE_HOST = 'https://images.balyan.ir';

    /**
     * مدت زمان کش آدرس تصاویر (۲۴ ساعت)
     */
    private const CACHE_TTL = 86400;

    /**
     * محاسبه دایرکتوری بر اساس آیدی پست
     *
     * @param int $postId آیدی پست
     * @return int شماره دایرکتوری
     */
    public static function calculateDirectory(int $postId): int
    {
        return intval(($postId - 1) / 10000) * 10000;
    }

    /**
     * ایجاد آدرس کامل تصویر بر اساس آیدی پست و md5
     *
     * @param int $postId آیدی پست
     * @param string $md5 هش md5 پست
     * @param string $format فرمت تصویر (پیش‌فرض jpg)
     * @return string آدرس کامل تصویر
     */
    public static function generateImageUrl(int $postId, string $md5, string $format = 'jpg'): string
    {
        // محاسبه دایرکتوری
        $directory = self::calculateDirectory($postId);

        // هاست تصاویر از config یا مقدار ثابت
        $imageHost = Config::get('app.custom_image_host', self::IMAGE_HOST);

        // ایجاد آدرس کامل
        return "{$imageHost}/{$directory}/{$md5}.{$format}";
    }

    /**
     * ایجاد آدرس تصویر با کش برای عملکرد بهتر
     */
    public static function getCachedImageUrl(int $postId, string $md5, string $format = 'jpg'): string
    {
        $cacheKey = "image_url_{$postId}_{$md5}_{$format}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($postId, $md5, $format) {
            return self::generateImageUrl($postId, $md5, $format);
        });
    }

    /**
     * ایجاد چندین آدرس تصویر با اندازه‌های مختلف
     */
    public static function getResponsiveImageUrls(int $postId, string $md5): array
    {
        $baseUrl = self::generateImageUrl($postId, $md5);

        return [
            'thumbnail' => $baseUrl . '?w=150&h=200&fit=crop',
            'small' => $baseUrl . '?w=300&h=400&fit=crop',
            'medium' => $baseUrl . '?w=600&h=800&fit=crop',
            'large' => $baseUrl . '?w=900&h=1200&fit=crop',
            'original' => $baseUrl,
        ];
    }

    /**
     * تولید آدرس تصویر پیش‌فرض
     */
    public static function getDefaultImageUrl(): string
    {
        return asset('images/default-book.png');
    }

    /**
     * بررسی وجود تصویر (با کش)
     */
    public static function imageExists(string $imageUrl): bool
    {
        $cacheKey = "image_exists_" . md5($imageUrl);

        return Cache::remember($cacheKey, 3600, function () use ($imageUrl) {
            try {
                $headers = get_headers($imageUrl, true);
                return isset($headers[0]) && strpos($headers[0], '200') !== false;
            } catch (\Exception $e) {
                return false;
            }
        });
    }

    /**
     * پاک کردن کش آدرس تصاویر
     */
    public static function clearImageCache(?int $postId = null, ?string $md5 = null): void
    {
        if ($postId && $md5) {
            $formats = ['jpg', 'jpeg', 'png', 'webp'];
            foreach ($formats as $format) {
                Cache::forget("image_url_{$postId}_{$md5}_{$format}");
                Cache::forget("image_exists_" . md5(self::generateImageUrl($postId, $md5, $format)));
            }
        }
    }

    /**
     * محاسبه آمار تصاویر بر اساس دایرکتوری
     */
    public static function getImageStats(): array
    {
        return Cache::remember('image_stats', 3600, function () {
            $stats = [];

            // محاسبه تعداد دایرکتوری‌های استفاده شده
            $maxPostId = \App\Models\Post::max('id') ?? 0;
            $directoriesCount = intval($maxPostId / 10000) + 1;

            $stats['total_directories'] = $directoriesCount;
            $stats['max_post_id'] = $maxPostId;
            $stats['images_per_directory'] = 10000;

            return $stats;
        });
    }

    /**
     * تبدیل آدرس قدیمی به آدرس جدید (برای migration)
     */
    public static function parseOldImagePath(string $oldImagePath): ?array
    {
        // استخراج اطلاعات از آدرس قدیمی
        // مثال: https://images.balyan.ir/0/d0dec15f11d97ff6f207ed2b972a5a9a.jpg

        $pattern = '/https:\/\/images\.balyan\.ir\/(\d+)\/([a-f0-9]{32})\.(\w+)/';

        if (preg_match($pattern, $oldImagePath, $matches)) {
            return [
                'directory' => (int)$matches[1],
                'md5' => $matches[2],
                'format' => $matches[3],
            ];
        }

        return null;
    }

    /**
     * محاسبه post_id از directory و md5
     */
    public static function calculatePostIdFromMd5(int $directory, string $md5): ?int
    {
        return Cache::remember("post_id_from_md5_{$md5}", 3600, function () use ($md5) {
            $post = \App\Models\Post::where('md5', $md5)->first(['id']);
            return $post ? $post->id : null;
        });
    }
}
