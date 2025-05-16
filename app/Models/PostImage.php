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

    protected $imageCacheTtl = 86400;

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function isHidden()
    {
        return $this->hide_image === 'hidden';
    }

    public function isVisible()
    {
        return $this->hide_image === 'visible';
    }

    public function isMissing()
    {
        return $this->hide_image === 'missing';
    }

    public function getImageUrlAttribute()
    {
        $cacheKey = "post_image_{$this->id}_url";

        return Cache::remember($cacheKey, $this->imageCacheTtl, function () {
            if (empty($this->image_path)) {
                return asset('images/default-book.png');
            }

            if (strpos($this->image_path, 'http://') === 0 || strpos($this->image_path, 'https://') === 0) {
                return $this->image_path;
            }

            if (strpos($this->image_path, 'images.balyan.ir/') !== false) {
                return 'https://' . $this->image_path;
            }

            if (strpos($this->image_path, 'post_images/') === 0 || strpos($this->image_path, 'posts/') === 0) {
                return config('app.custom_image_host', 'https://images.balyan.ir') . '/' . $this->image_path;
            }

            return asset('storage/' . $this->image_path);
        });
    }

    public function getDisplayUrlAttribute()
    {
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $cacheKey = "post_image_{$this->id}_display_url_" . ($isAdmin ? 'admin' : 'user');

        return Cache::remember($cacheKey, $this->imageCacheTtl, function () use ($isAdmin) {
            $defaultImage = asset('images/default-book.png');

            if (empty($this->image_path) || $this->hide_image === 'missing') {
                return $defaultImage;
            }

            if ($isAdmin || $this->hide_image === 'visible') {
                return $this->image_url;
            }

            return $defaultImage;
        });
    }
}
