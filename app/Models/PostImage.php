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
    public function isHidden()
    {
        return $this->hide_image === 'hidden';
    }

    /**
     * بررسی اینکه آیا تصویر قابل نمایش است
     */
    public function isVisible()
    {
        return $this->hide_image === 'visible' || $this->hide_image === null;
    }

    /**
     * بررسی اینکه آیا تصویر گمشده است
     */
    public function isMissing()
    {
        return $this->hide_image === 'missing';
    }

    /**
     * دریافت URL کامل تصویر
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        $cacheKey = "post_image_{$this->id}_url";

        return Cache::remember($cacheKey, $this->imageCacheTtl, function () {
            if (empty($this->image_path)) {
                return asset('images/default-book.png');
            }

            // اگر آدرس کامل باشد آن را برگردان
            if (strpos($this->image_path, 'http://') === 0 || strpos($this->image_path, 'https://') === 0) {
                return $this->image_path;
            }

            // اگر آدرس شامل دامنه مخصوص باشد
            if (strpos($this->image_path, 'images.balyan.ir/') !== false) {
                return 'https://' . $this->image_path;
            }

            // اگر آدرس با پوشه تصاویر شروع شود
            if (strpos($this->image_path, 'post_images/') === 0 || strpos($this->image_path, 'posts/') === 0) {
                return config('app.custom_image_host', 'https://images.balyan.ir') . '/' . $this->image_path;
            }

            // در غیر این صورت، تصویر را از استوریج نمایش بده
            return asset('storage/' . $this->image_path);
        });
    }

    /**
     * دریافت URL تصویر برای نمایش
     * با در نظر گرفتن وضعیت مخفی بودن
     *
     * @return string
     */
    public function getDisplayUrlAttribute()
    {
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $cacheKey = "post_image_{$this->id}_display_url_" . ($isAdmin ? 'admin' : 'user');

        return Cache::remember($cacheKey, $this->imageCacheTtl, function () use ($isAdmin) {
            $defaultImage = asset('images/default-book.png');

            if (empty($this->image_path) || $this->hide_image === 'missing') {
                return $defaultImage;
            }

            if ($isAdmin || $this->isVisible()) {
                return $this->image_url;
            }

            return $defaultImage;
        });
    }
}
