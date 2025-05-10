<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Author extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'biography',
        'image',
    ];

    /**
     * رابطه با پست‌ها (به عنوان نویسنده اصلی)
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * رابطه با پست‌هایی که این نویسنده به عنوان یکی از نویسندگان در آن‌ها حضور دارد
     */
    public function coAuthoredPosts()
    {
        return $this->belongsToMany(Post::class, 'post_author');
    }

    /**
     * دریافت همه پست‌هایی که این نویسنده در آن‌ها نقش دارد - نسخه کش شده
     * (چه به عنوان نویسنده اصلی و چه به عنوان یکی از نویسندگان)
     *
     * @param bool $isAdmin آیا کاربر مدیر است؟
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPostsCached($isAdmin = false)
    {
        $cacheKey = "author_{$this->id}_all_posts_" . ($isAdmin ? 'admin' : 'user');

        return Cache::remember($cacheKey, 3600, function () use ($isAdmin) {
            return Post::select(['id', 'title', 'slug', 'category_id', 'publication_year', 'format'])
                ->where('is_published', true)
                ->when(!$isAdmin, function ($query) {
                    $query->where('hide_content', false);
                })
                ->where(function ($query) {
                    $query->where('author_id', $this->id)
                        ->orWhereHas('authors', function ($q) {
                            $q->where('authors.id', $this->id);
                        });
                })
                ->with([
                    'featuredImage' => function($query) {
                        $query->select('id', 'post_id', 'image_path', 'hide_image');
                    },
                    'category:id,name,slug'
                ])
                ->latest()
                ->get();
        });
    }

    /**
     * دریافت تعداد پست‌های این نویسنده - نسخه کش شده
     *
     * @param bool $isAdmin آیا کاربر مدیر است؟
     * @return int
     */
    public function getPostsCountCached($isAdmin = false)
    {
        $cacheKey = "author_{$this->id}_posts_count_" . ($isAdmin ? 'admin' : 'user');

        return Cache::remember($cacheKey, 3600, function () use ($isAdmin) {
            return Post::where('is_published', true)
                ->when(!$isAdmin, function ($query) {
                    $query->where('hide_content', false);
                })
                ->where(function ($query) {
                    $query->where('author_id', $this->id)
                        ->orWhereHas('authors', function ($q) {
                            $q->where('authors.id', $this->id);
                        });
                })
                ->count();
        });
    }

    /**
     * دریافت همه پست‌های این نویسنده
     * (چه به عنوان نویسنده اصلی و چه به عنوان یکی از نویسندگان)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPostsAttribute()
    {
        $mainPosts = $this->posts;
        $coAuthoredPosts = $this->coAuthoredPosts;

        return $mainPosts->merge($coAuthoredPosts)->unique('id');
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
}
