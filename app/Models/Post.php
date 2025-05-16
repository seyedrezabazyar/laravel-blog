<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mews\Purifier\Facades\Purifier;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    use HasSlug;

    protected $fillable = [
        'user_id', 'category_id', 'author_id', 'publisher_id', 'title', 'english_title',
        'slug', 'content', 'english_content', 'language', 'publication_year', 'format',
        'book_codes', 'purchase_link', 'hide_content', 'is_published', 'md5_hash',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'hide_content' => 'boolean',
        'publication_year' => 'integer',
    ];

    // حذف روابط پیش‌فرض برای بهینه‌سازی
    protected $with = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->usingLanguage('fa');
    }

    public function user()
    {
        return $this->belongsTo(User::class)->select(['id', 'name', 'email']);
    }

    public function category()
    {
        return $this->belongsTo(Category::class)->select(['id', 'name', 'slug']);
    }

    public function author()
    {
        return $this->belongsTo(Author::class)->select(['id', 'name', 'slug']);
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class)->select(['id', 'name', 'slug', 'logo']);
    }

    public function featuredImage()
    {
        $cacheKey = "post_{$this->id}_featured_image";

        $cachedImage = Cache::remember($cacheKey, 60, function() {
            return $this->hasOne(PostImage::class)
                ->select(['id', 'post_id', 'image_path', 'caption', 'hide_image', 'sort_order'])
                ->orderBy('sort_order')
                ->first();
        });

        if ($cachedImage) {
            return $this->hasOne(PostImage::class)->where('id', $cachedImage->id);
        }

        return $this->hasOne(PostImage::class)
            ->select(['id', 'post_id', 'image_path', 'caption', 'hide_image', 'sort_order'])
            ->orderBy('sort_order');
    }

    public function images()
    {
        return $this->hasMany(PostImage::class)
            ->select(['id', 'post_id', 'image_path', 'caption', 'hide_image', 'sort_order'])
            ->orderBy('sort_order');
    }

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'post_author')
            ->select(['authors.id', 'name', 'slug']);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)
            ->select(['tags.id', 'name', 'slug']);
    }

    public function getPurifiedContentAttribute()
    {
        $cacheKey = "post_{$this->id}_purified_content_" . md5($this->content);

        return Cache::remember($cacheKey, 86400 * 7, function () {
            return Purifier::clean($this->content);
        });
    }

    public function scopeVisibleToUser($query)
    {
        $query->where('is_published', true);

        if (!auth()->check() || !auth()->user()->isAdmin()) {
            $query->where('hide_content', false);
        }

        return $query;
    }

    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByAuthor($query, $authorId)
    {
        return $query->where(function($q) use ($authorId) {
            $q->where('author_id', $authorId)
                ->orWhereExists(function ($subq) use ($authorId) {
                    $subq->select(DB::raw(1))
                        ->from('post_author')
                        ->whereRaw('post_author.post_id = posts.id')
                        ->where('post_author.author_id', $authorId);
                });
        });
    }

    public function scopeByPublisher($query, $publisherId)
    {
        return $query->where('publisher_id', $publisherId);
    }

    public function scopeFullTextSearch($query, $searchTerm)
    {
        $searchTerm = preg_replace('/[^\p{L}\p{N}_\s-]/u', '', $searchTerm);

        return $query->where(function($q) use ($searchTerm) {
            $q->where('title', 'like', "%{$searchTerm}%")
                ->orWhere('english_title', 'like', "%{$searchTerm}%")
                ->orWhere('book_codes', 'like', "%{$searchTerm}%");
        });
    }
}
