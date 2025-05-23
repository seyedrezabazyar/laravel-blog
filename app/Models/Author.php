<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Author extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'posts_count',
    ];

    protected $casts = [
        'posts_count' => 'integer',
    ];

    /**
     * رابطه با پست‌ها (به عنوان نویسنده اصلی)
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * رابطه با پست‌هایی که این نویسنده به عنوان همکار در آن‌ها حضور دارد
     */
    public function coAuthoredPosts()
    {
        return $this->belongsToMany(Post::class, 'post_author');
    }

    /**
     * دریافت همه پست‌های این نویسنده
     * (چه به عنوان نویسنده اصلی و چه به عنوان همکار)
     */
    public function getAllPostsAttribute()
    {
        $mainPosts = $this->posts;
        $coAuthoredPosts = $this->coAuthoredPosts;

        return $mainPosts->merge($coAuthoredPosts)->unique('id');
    }

    /**
     * دریافت تعداد کل پست‌های نویسنده
     */
    public function getTotalPostsCountAttribute()
    {
        return $this->posts_count;
    }

    /**
     * به‌روزرسانی شمارنده‌های پست
     */
    public function updatePostCounts()
    {
        // شمارش پست‌های اصلی
        $postsCount = Post::where('author_id', $this->id)
            ->where('is_published', true)
            ->where('hide_content', false)
            ->count();

        // شمارش پست‌های همکاری
        $coAuthoredCount = DB::table('post_author')
            ->join('posts', 'posts.id', '=', 'post_author.post_id')
            ->where('post_author.author_id', $this->id)
            ->where('posts.is_published', true)
            ->where('posts.hide_content', false)
            ->count();

        // ذخیره مجموع
        $this->posts_count = $postsCount + $coAuthoredCount;

        // به‌روزرسانی بدون تغییر زمان به‌روزرسانی
        $this->timestamps = false;
        $this->save();
        $this->timestamps = true;

        // پاک کردن کش‌های مرتبط
        $this->clearCache();
    }

    /**
     * پاک کردن کش‌های مرتبط با این نویسنده
     */
    public function clearCache()
    {
        $cacheKeys = [
            "author_{$this->id}_all_posts_admin",
            "author_{$this->id}_all_posts_user",
            "author_posts_{$this->id}_page_1_admin",
            "author_posts_{$this->id}_page_1_user"
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * فقط پست‌های منتشر شده و غیر محدود شده را برگرداند
     */
    public function publicPosts()
    {
        return $this->posts()
            ->where('is_published', true)
            ->where('hide_content', false);
    }

    /**
     * پیدا کردن نویسنده بر اساس slug
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
