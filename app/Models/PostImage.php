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
    ];

    protected $casts = [
        'hide_image' => 'boolean',
    ];

    /**
     * Cache TTL for image URLs - 7 days
     */
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
        return $this->hide_image;
    }

    /**
     * Get cached image URL
     *
     * This reduces the processing needed for each image request
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

            // Handle download host images
            if (strpos($this->image_path, 'post_images/') === 0 || strpos($this->image_path, 'posts/') === 0) {
                return config('app.custom_image_host', 'https://images.balyan.ir') . '/' . $this->image_path;
            }

            // Local storage fallback
            return asset('storage/' . $this->image_path);
        });
    }

    /**
     * Get display URL for the image
     *
     * Takes into account user permissions and image visibility
     */
    public function getDisplayUrlAttribute()
    {
        // Generate cache key including admin status
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $cacheKey = "post_image_{$this->id}_display_url_" . ($isAdmin ? 'admin' : 'user');

        return Cache::remember($cacheKey, $this->imageCacheTtl, function () use ($isAdmin) {
            // Default image
            $defaultImage = asset('images/default-book.png');

            // Always show actual image to admins
            if ($isAdmin) {
                return $this->image_url;
            }

            // Show default image if hidden or empty
            if ($this->hide_image || empty($this->image_path)) {
                return $defaultImage;
            }

            return $this->image_url;
        });
    }
}
