<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PostImage extends Model
{
    protected $fillable = [
        'post_id',
        'image_path',
        'caption',
        'hide_image',
        'sort_order',
        'approved_at',
    ];

    // TTL for image URL cache - 7 days
    protected $imageCacheTtl = 604800;

    /**
     * Relationship with post - optimized
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Check if image is hidden
     */
    public function isHidden()
    {
        return $this->hide_image === 'hidden';
    }

    /**
     * Check if image is visible
     */
    public function isVisible()
    {
        return $this->hide_image === 'visible';
    }

    /**
     * Check if image is missing
     */
    public function isMissing()
    {
        return $this->hide_image === 'missing';
    }

    /**
     * Check if image is restricted (NULL or hidden)
     */
    public function isRestricted()
    {
        return $this->hide_image === null || $this->hide_image === 'hidden' || $this->hide_image === 'missing';
    }

    /**
     * Get cached image URL
     */
    public function getImageUrlAttribute()
    {
        $cacheKey = "post_image_{$this->id}_url";

        return Cache::remember($cacheKey, $this->imageCacheTtl, function () {
            if (empty($this->image_path)) {
                return asset('images/default-book.png');
            }

            // Direct URL for HTTP/HTTPS paths
            if (strpos($this->image_path, 'http://') === 0 || strpos($this->image_path, 'https://') === 0) {
                return $this->image_path;
            }

            // Handle images.balyan.ir domain
            if (strpos($this->image_path, 'images.balyan.ir/') !== false) {
                return 'https://' . $this->image_path;
            }

            // Handle images from download host
            if (strpos($this->image_path, 'post_images/') === 0 || strpos($this->image_path, 'posts/') === 0) {
                return config('app.custom_image_host', 'https://images.balyan.ir') . '/' . $this->image_path;
            }

            // Local storage fallback
            return asset('storage/' . $this->image_path);
        });
    }

    /**
     * Get display URL for the image based on visibility and user role
     */
    public function getDisplayUrlAttribute()
    {
        // Generate cache key including admin status
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $cacheKey = "post_image_{$this->id}_display_url_" . ($isAdmin ? 'admin' : 'user');

        return Cache::remember($cacheKey, $this->imageCacheTtl, function () use ($isAdmin) {
            // Default image
            $defaultImage = asset('images/default-book.png');

            // If image path is empty or marked as missing, return default image regardless of user role
            if (empty($this->image_path) || $this->hide_image === 'missing') {
                return $defaultImage;
            }

            // For admins, always show the actual image
            if ($isAdmin) {
                return $this->image_url;
            }

            // For non-admins:
            // - Show actual image only if hide_image is 'visible'
            // - Show default image if hide_image is NULL, 'hidden', or any other value
            if ($this->hide_image === 'visible') {
                return $this->image_url;
            }

            return $defaultImage;
        });
    }

    public function getFullImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return asset('images/default-book.png');
        }

        // URL مستقیم برای مسیرهای HTTP/HTTPS
        if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
            return $imagePath;
        }

        // مدیریت دامنه images.balyan.ir
        if (strpos($imagePath, 'images.balyan.ir/') !== false) {
            return 'https://' . $imagePath;
        }

        // مدیریت تصاویر از هاست دانلود
        if (strpos($imagePath, 'post_images/') === 0 || strpos($imagePath, 'posts/') === 0) {
            return config('app.custom_image_host', 'https://images.balyan.ir') . '/' . $imagePath;
        }

        // فالبک به ذخیره‌سازی محلی
        return asset('storage/' . $imagePath);
    }
}
