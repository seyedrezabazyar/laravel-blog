<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
     * دریافت همه پست‌هایی که این نویسنده در آن‌ها نقش دارد
     * (چه به عنوان نویسنده اصلی و چه به عنوان یکی از نویسندگان)
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
