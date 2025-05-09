<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
    ];

    // Relación con posts (libros publicados por esta editorial)
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * رابطه با ناشر - بهینه‌سازی شده
     */
    public function publisher()
    {
        return $this->belongsTo(Publisher::class, 'publisher_id')
            ->select(['id', 'name', 'slug', 'logo']); // انتخاب فقط فیلدهای مورد نیاز
    }

    /**
     * اسکوپ برای دریافت بهینه پست‌های ناشر
     */
    public function scopeWithOptimizedPosts($query, $limit = 12)
    {
        return $query->withCount(['posts' => function ($query) {
            $query->where('is_published', true)
                ->where('hide_content', false);
        }])->with(['posts' => function ($query) use ($limit) {
            $query->where('is_published', true)
                ->where('hide_content', false)
                ->select(['id', 'title', 'slug', 'publisher_id', 'category_id', 'publication_year', 'format'])
                ->with([
                    'featuredImage' => function($query) {
                        $query->select('id', 'post_id', 'image_path', 'hide_image');
                    }
                ])
                ->latest()
                ->limit($limit);
        }]);
    }

    /**
     * فقط پست‌های منتشر شده و غیر محدود شده را برگرداند
     */
    public function publicPosts()
    {
        return $this->hasMany(Post::class)
            ->where('is_published', true)
            ->where('hide_content', false)
            ->select(['id', 'title', 'slug', 'publisher_id', 'category_id', 'publication_year', 'format']);
    }
}
