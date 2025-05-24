<?php

namespace App\Models;

use App\Services\ImageUrlService;
use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    protected $fillable = ['post_id', 'status'];

    /**
     * رابطه با پست
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * بررسی وضعیت‌های تصویر
     */
    public function isHidden(): bool
    {
        return $this->status === 'hidden';
    }

    public function isVisible(): bool
    {
        return $this->status === 'visible' || $this->status === null;
    }

    public function isMissing(): bool
    {
        return $this->status === 'missing';
    }

    /**
     * دریافت URL تصویر بر اساس فرمول محاسباتی
     */
    public function getImageUrlAttribute(): string
    {
        $post = $this->post;

        if (!$post || !$post->md5) {
            return asset('images/default-book.png');
        }

        // محاسبه دایرکتوری بر اساس آیدی پست
        $directory = intval(($post->id - 1) / 10000) * 10000;

        // هاست تصاویر
        $imageHost = config('app.custom_image_host', 'https://images.balyan.ir');

        // تولید URL کامل
        return "{$imageHost}/{$directory}/{$post->md5}.jpg";
    }

    /**
     * URL نمایش بر اساس دسترسی کاربر
     */
    public function getDisplayUrlAttribute(): string
    {
        $isAdmin = auth()->check() && auth()->user()->isAdmin();

        // اگر تصویر گمشده باشد
        if ($this->isMissing()) {
            return asset('images/default-book.png');
        }

        // اگر کاربر مدیر باشد یا تصویر قابل نمایش باشد
        if ($isAdmin || $this->isVisible()) {
            return $this->image_url;
        }

        return asset('images/default-book.png');
    }

    /**
     * آدرس‌های responsive
     */
    public function getResponsiveUrlsAttribute(): array
    {
        $post = $this->post;

        if (!$post || !$post->md5) {
            $defaultUrl = asset('images/default-book.png');
            return [
                'thumbnail' => $defaultUrl,
                'small' => $defaultUrl,
                'medium' => $defaultUrl,
                'large' => $defaultUrl,
                'original' => $defaultUrl,
            ];
        }

        $baseUrl = $this->image_url;

        return [
            'thumbnail' => $baseUrl . '?w=150&h=200&fit=crop',
            'small' => $baseUrl . '?w=300&h=400&fit=crop',
            'medium' => $baseUrl . '?w=600&h=800&fit=crop',
            'large' => $baseUrl . '?w=900&h=1200&fit=crop',
            'original' => $baseUrl,
        ];
    }

    /**
     * URL با اندازه مشخص
     */
    public function getImageUrlWithSize(string $size = 'medium'): string
    {
        $responsiveUrls = $this->responsive_urls;
        return $responsiveUrls[$size] ?? $responsiveUrls['medium'] ?? asset('images/default-book.png');
    }

    /**
     * پاک کردن کش تصویر
     */
    public function clearImageCache(): void
    {
        $post = $this->post;
        if ($post && $post->md5) {
            \Cache::forget("image_url_{$post->id}_{$post->md5}");
        }
    }
}
