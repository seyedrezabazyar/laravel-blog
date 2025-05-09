<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'posts_count' // اضافه کردن فیلد جدید به لیست fillable
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
     * فقط پست‌های منتشر شده و غیر مخفی
     */
    public function visiblePosts()
    {
        return $this->hasMany(Post::class)
            ->where('is_published', true)
            ->where('hide_content', false);
    }

    /**
     * به‌روزرسانی شمارنده پست‌ها
     * این تابع باید بعد از ایجاد، به‌روزرسانی، یا حذف پست فراخوانی شود
     */
    public function updatePostCount()
    {
        $this->posts_count = $this->visiblePosts()->count();
        // به‌روزرسانی بدون تغییر زمان به‌روزرسانی
        $this->timestamps = false;
        $this->save();
        $this->timestamps = true;
    }
}
