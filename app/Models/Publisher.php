<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Publisher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'posts_count',
    ];

    protected $casts = [
        'posts_count' => 'integer',
    ];

    /**
     * رابطه با پست‌ها
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * رابطه با پست‌های قابل مشاهده
     */
    public function visiblePosts()
    {
        return $this->hasMany(Post::class)
            ->where('is_published', true)
            ->where('hide_content', false);
    }

    /**
     * به‌روزرسانی شمارنده پست‌ها
     */
    public function updatePostCount()
    {
        $this->posts_count = $this->visiblePosts()->count();

        // به‌روزرسانی بدون تغییر زمان به‌روزرسانی
        $this->timestamps = false;
        $this->save();
        $this->timestamps = true;

        // پاکسازی کش
        $this->clearCache();
    }

    /**
     * پاکسازی کش مرتبط
     */
    public function clearCache()
    {
        $cacheKeys = [
            "publisher_posts_{$this->id}_page_1_admin",
            "publisher_posts_{$this->id}_page_1_user"
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * پیدا کردن ناشر بر اساس slug
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
