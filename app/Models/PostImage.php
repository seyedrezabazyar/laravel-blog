<?php

namespace App\Models;

use App\Services\ImageUrlService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PostImage extends Model
{
    protected $fillable = [
        'post_id',
        'status',
    ];

    protected $imageCacheTtl = 86400; // 24 ساعت

    /**
     * رابطه با پست
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * بررسی اینکه آیا تصویر مخفی است
     */
    public function isHidden(): bool
    {
        return $this->status === 'hidden';
    }

    /**
     * بررسی اینکه آیا تصویر قابل نمایش است
     */
    public function isVisible(): bool
    {
        return $this->status === 'visible';
    }

    /**
     * بررسی اینکه آیا تصویر گمشده است
     */
    public function isMissing(): bool
    {
        return $this->status === 'missing';
    }

    /**
     * دریافت URL کامل تصویر بر اساس فرمول
     */
    public function getImageUrlAttribute(): string
    {
        $cacheKey = "post_image_{$this->id}_url_calculated";

        return Cache::remember($cacheKey, $this->imageCacheTtl, function () {
            // بارگذاری پست برای دریافت md5
            $post = $this->post;

            if (!$post || !$post->md5) {
                return ImageUrlService::getDefaultImageUrl();
            }

            return ImageUrlService::generateImageUrl($post->id, $post->md5);
        });
    }

    /**
     * دریافت URL تصویر برای نمایش با در نظر گرفتن وضعیت مخفی بودن
     */
    public function getDisplayUrlAttribute(): string
    {
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $cacheKey = "post_image_{$this->id}_display_url_" . ($isAdmin ? 'admin' : 'user');

        return Cache::remember($cacheKey, $this->imageCacheTtl, function () use ($isAdmin) {
            $defaultImage = ImageUrlService::getDefaultImageUrl();

            // اگر تصویر گمشده باشد
            if ($this->isMissing()) {
                return $defaultImage;
            }

            // اگر کاربر مدیر باشد یا تصویر قابل نمایش باشد
            if ($isAdmin || $this->isVisible()) {
                return $this->image_url;
            }

            return $defaultImage;
        });
    }

    /**
     * دریافت آدرس‌های تصویر با اندازه‌های مختلف
     */
    public function getResponsiveUrlsAttribute(): array
    {
        $cacheKey = "post_image_{$this->id}_responsive_urls";

        return Cache::remember($cacheKey, $this->imageCacheTtl, function () {
            $post = $this->post;

            if (!$post || !$post->md5) {
                $defaultUrl = ImageUrlService::getDefaultImageUrl();
                return [
                    'thumbnail' => $defaultUrl,
                    'small' => $defaultUrl,
                    'medium' => $defaultUrl,
                    'large' => $defaultUrl,
                    'original' => $defaultUrl,
                ];
            }

            return ImageUrlService::getResponsiveImageUrls($post->id, $post->md5);
        });
    }

    /**
     * دریافت URL تصویر با اندازه مشخص
     */
    public function getImageUrlWithSize(string $size = 'medium'): string
    {
        $responsiveUrls = $this->responsive_urls;
        return $responsiveUrls[$size] ?? $responsiveUrls['medium'] ?? ImageUrlService::getDefaultImageUrl();
    }

    /**
     * تولید srcset برای تصاویر responsive
     */
    public function getSrcsetAttribute(): string
    {
        $responsiveUrls = $this->responsive_urls;

        return implode(', ', [
            $responsiveUrls['small'] . ' 300w',
            $responsiveUrls['medium'] . ' 600w',
            $responsiveUrls['large'] . ' 900w',
        ]);
    }

    /**
     * تولید HTML تگ img با قابلیت responsive
     */
    public function toResponsiveImageHtml(string $alt = '', string $cssClass = 'w-full h-full object-cover'): string
    {
        $defaultUrl = ImageUrlService::getDefaultImageUrl();
        $displayUrl = $this->display_url;
        $srcset = $this->srcset;

        $alt = htmlspecialchars($alt ?: ($this->post->title ?? 'تصویر کتاب'));

        return sprintf(
            '<img src="%s" srcset="%s" sizes="(max-width: 768px) 100vw, 50vw" alt="%s" class="%s" loading="lazy" onerror="this.onerror=null;this.src=\'%s\';">',
            $displayUrl,
            $srcset,
            $alt,
            $cssClass,
            $defaultUrl
        );
    }

    /**
     * بررسی وجود فیزیکی تصویر
     */
    public function checkImageExists(): bool
    {
        $imageUrl = $this->image_url;
        return ImageUrlService::imageExists($imageUrl);
    }

    /**
     * به‌روزرسانی وضعیت تصویر بر اساس وجود فیزیکی آن
     */
    public function updateStatusBasedOnExistence(): bool
    {
        if ($this->checkImageExists()) {
            if ($this->isMissing()) {
                $this->status = 'visible';
                return $this->save();
            }
        } else {
            if (!$this->isMissing()) {
                $this->status = 'missing';
                return $this->save();
            }
        }

        return false;
    }

    /**
     * پاکسازی کش تصویر
     */
    public function clearImageCache(): void
    {
        $post = $this->post;

        $cacheKeys = [
            "post_image_{$this->id}_url_calculated",
            "post_image_{$this->id}_display_url_admin",
            "post_image_{$this->id}_display_url_user",
            "post_image_{$this->id}_responsive_urls",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // پاک کردن کش سرویس تصاویر
        if ($post && $post->md5) {
            ImageUrlService::clearImageCache($post->id, $post->md5);
        }
    }

    /**
     * رویداد boot برای پاک کردن کش هنگام تغییر
     */
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($postImage) {
            $postImage->clearImageCache();
        });

        static::deleted(function ($postImage) {
            $postImage->clearImageCache();
        });
    }

    /**
     * دریافت اطلاعات تصویر برای debug
     */
    public function getImageDebugInfo(): array
    {
        $post = $this->post;

        return [
            'post_id' => $this->post_id,
            'post_md5' => $post ? $post->md5 : null,
            'calculated_directory' => $post ? ImageUrlService::calculateDirectory($post->id) : null,
            'generated_url' => $this->image_url,
            'status' => $this->status,
            'exists' => $this->checkImageExists(),
            'responsive_urls' => $this->responsive_urls,
        ];
    }
}
