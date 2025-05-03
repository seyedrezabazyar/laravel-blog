<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Mews\Purifier\Facades\Purifier;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    use HasSlug;

    protected $fillable = [
        'user_id',
        'category_id',
        'author_id',
        'publisher_id',
        'title',
        'english_title',
        'slug',
        'content',
        'english_content',
        'featured_image',
        'language',
        'publication_year',
        'format',
        'book_codes',
        'purchase_link',
        'hide_image',
        'hide_content',
        'is_published',
        'md5_hash',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'hide_image' => 'boolean',
        'hide_content' => 'boolean',
        'publication_year' => 'integer',
    ];

    /**
     * تنظیمات Slug
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->usingLanguage('fa');
    }

    /**
     * بهینه‌سازی کوئری: ایندکس‌های مورد نیاز
     *
     * ALTER TABLE posts ADD INDEX idx_visible_posts (is_published, hide_content);
     * ALTER TABLE posts ADD INDEX idx_post_category (category_id);
     * ALTER TABLE posts ADD INDEX idx_post_author (author_id);
     * ALTER TABLE posts ADD INDEX idx_post_publisher (publisher_id);
     * ALTER TABLE posts ADD FULLTEXT INDEX ftx_post_content (title, english_title, content, english_content);
     */

    /**
     * رابطه با کاربر
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * رابطه با دسته‌بندی
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * رابطه با نویسنده اصلی
     */
    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    /**
     * رابطه با ناشر
     */
    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    /**
     * رابطه با تصاویر پست - بهینه شده
     */
    public function featuredImage()
    {
        return $this->hasOne(PostImage::class)->orderBy('sort_order');
    }

    /**
     * بهینه‌سازی: رابطه با تمام تصاویر
     */
    public function images()
    {
        return $this->hasMany(PostImage::class)->orderBy('sort_order');
    }

    /**
     * رابطه با نویسندگان دیگر (چند به چند)
     */
    public function authors()
    {
        return $this->belongsToMany(Author::class, 'post_author');
    }

    /**
     * رابطه با تگ‌ها - بهینه شده با eager loading محدود
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * دریافت محتوای پاکسازی شده پست - کش شده
     */
    public function getPurifiedContentAttribute()
    {
        $cacheKey = "post_{$this->id}_purified_content";

        return Cache::remember($cacheKey, 3600, function () {
            return Purifier::clean($this->content);
        });
    }

    /**
     * بهینه‌سازی: Scope برای پست‌های قابل نمایش
     */
    public function scopeVisibleToUser($query)
    {
        $query->where('is_published', true);

        if (!auth()->check() || !auth()->user()->isAdmin()) {
            $query->where('hide_content', false);
        }

        return $query;
    }

    /**
     * بهینه‌سازی: Scope برای پست‌های یک دسته‌بندی
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * بهینه‌سازی: Scope برای پست‌های یک نویسنده
     */
    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    /**
     * بهینه‌سازی: Scope برای پست‌های یک ناشر
     */
    public function scopeByPublisher($query, $publisherId)
    {
        return $query->where('publisher_id', $publisherId);
    }

    /**
     * بهینه‌سازی: Scope برای جستجوی متن کامل
     */
    public function scopeFullTextSearch($query, $searchTerm)
    {
        return $query->whereRaw("MATCH(title, english_title, content, english_content) AGAINST(? IN BOOLEAN MODE)", [$searchTerm . '*']);
    }
}
