<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB; // اضافه کردن DB Facade
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
     * Default relationships to eager load
     * This reduces the N+1 query problem by loading common relationships by default
     */
    protected $with = ['featuredImage'];

    /**
     * SlugOptions configuration
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->usingLanguage('fa');
    }

    /**
     * Relationship with user - optimized to select only needed fields
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->select(['id', 'name', 'email']);
    }

    /**
     * Relationship with category - optimized
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id')->select(['id', 'name', 'slug']);
    }

    /**
     * Relationship with author - optimized
     */
    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id')->select(['id', 'name', 'slug']);
    }

    /**
     * Relationship with publisher - optimized
     */
    public function publisher()
    {
        return $this->belongsTo(Publisher::class, 'publisher_id')->select(['id', 'name', 'slug']);
    }

    /**
     * Optimized featured image relationship
     * Always selects only the needed columns and orders by sort_order
     */
    public function featuredImage()
    {
        return $this->hasOne(PostImage::class)
            ->select(['id', 'post_id', 'image_path', 'caption', 'hide_image', 'sort_order'])
            ->orderBy('sort_order');
    }

    /**
     * Relationship with all images - optimized
     */
    public function images()
    {
        return $this->hasMany(PostImage::class)
            ->select(['id', 'post_id', 'image_path', 'caption', 'hide_image', 'sort_order'])
            ->orderBy('sort_order');
    }

    /**
     * Relationship with co-authors - optimized
     */
    public function authors()
    {
        return $this->belongsToMany(Author::class, 'post_author')
            ->select(['authors.id', 'name', 'slug']);
    }

    /**
     * Relationship with tags - optimized
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class)
            ->select(['tags.id', 'name', 'slug']);
    }

    /**
     * Get purified content with caching
     * Uses a longer cache time for better performance
     */
    public function getPurifiedContentAttribute()
    {
        $cacheKey = "post_{$this->id}_purified_content_" . md5($this->content);

        return Cache::remember($cacheKey, 86400 * 7, function () {
            return Purifier::clean($this->content);
        });
    }

    /**
     * Scope for posts visible to the current user
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
     * Scope for posts in a specific category - optimized
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope for posts by a specific author - optimized
     */
    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('author_id', $authorId)
            ->orWhereHas('authors', function ($q) use ($authorId) {
                $q->where('authors.id', $authorId);
            });
    }

    /**
     * Scope for posts by a specific publisher - optimized
     */
    public function scopeByPublisher($query, $publisherId)
    {
        return $query->where('publisher_id', $publisherId);
    }

    /**
     * جستجوی متنی بهینه شده با استفاده از ایندکس FULLTEXT یا LIKE
     */
    public function scopeFullTextSearch($query, $searchTerm)
    {
        // پاکسازی عبارت جستجو برای جلوگیری از SQL Injection
        $searchTerm = preg_replace('/[^\p{L}\p{N}_\s-]/u', '', $searchTerm);

        // بررسی وجود ایندکس FULLTEXT - با استفاده از try-catch برای امنیت بیشتر
        $fullTextEnabled = false;

        try {
            // جستجوی ساده با LIKE را استفاده می‌کنیم
            return $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('english_title', 'like', "%{$searchTerm}%")
                    ->orWhere('book_codes', 'like', "%{$searchTerm}%");

                // محدود کردن جستجو در محتوا فقط برای کلمات کلیدی بلندتر از 3 حرف
                if (mb_strlen($searchTerm) > 3) {
                    $q->orWhere('content', 'like', "%{$searchTerm}%")
                        ->orWhere('english_content', 'like', "%{$searchTerm}%");
                }
            });
        } catch (\Exception $e) {
            \Log::error('Search error: ' . $e->getMessage());

            // در صورت خطا، فقط در عنوان جستجو می‌کنیم
            return $query->where('title', 'like', "%{$searchTerm}%");
        }
    }
}
