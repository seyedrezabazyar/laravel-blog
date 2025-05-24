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
     * دریافت URL تصویر - سادهٔ و بدون کش برای جلوگیری از مشکل
     */
    public function getImageUrlAttribute(): string
    {
        $post = $this->post;

        if (!$post || !$post->md5) {
            return asset('images/default-book.png');
        }

        return ImageUrlService::generateImageUrl($post->id, $post->md5);
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

        return ImageUrlService::getResponsiveImageUrls($post->id, $post->md5);
    }

    /**
     * URL با اندازه مشخص
     */
    public function getImageUrlWithSize(string $size = 'medium'): string
    {
        $responsiveUrls = $this->responsive_urls;
        return $responsiveUrls[$size] ?? $responsiveUrls['medium'] ?? asset('images/default-book.png');
    }
}
