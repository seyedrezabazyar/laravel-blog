<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'elasticsearch_id', 'user_id', 'category_id', 'author_id', 'publisher_id',
        'title', 'slug', 'publication_year', 'format', 'languages', 'isbn', 'pages_count',
        'hide_content', 'is_published', 'is_indexed'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'hide_content' => 'boolean',
        'is_indexed' => 'boolean',
        'publication_year' => 'integer',
        'pages_count' => 'integer',
        'indexed_at' => 'datetime',
    ];

    /**
     * Scope برای پست‌های قابل مشاهده توسط کاربران عادی
     */
    public function scopeVisibleToUser($query)
    {
        return $query->where('is_published', true)
            ->where('hide_content', false);
    }

    /**
     * Scope برای پست‌های قابل مشاهده توسط مدیران
     */
    public function scopeVisibleToAdmin($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope برای لیست‌های بهینه (فقط فیلدهای ضروری)
     */
    public function scopeForListing($query)
    {
        return $query->select([
            'id', 'title', 'slug', 'category_id', 'author_id', 'publisher_id',
            'publication_year', 'format', 'languages', 'is_published', 'hide_content',
            'created_at', 'updated_at'
        ]);
    }

    /**
     * Scope برای جستجو در عنوان و فیلدهای مرتبط
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('isbn', 'like', "%{$term}%");
        });
    }

    /**
     * رابطه با دسته‌بندی
     */
    public function category()
    {
        return $this->belongsTo(Category::class)->select(['id', 'name', 'slug']);
    }

    /**
     * رابطه با نویسنده اصلی
     */
    public function author()
    {
        return $this->belongsTo(Author::class)->select(['id', 'name', 'slug']);
    }

    /**
     * رابطه با ناشر
     */
    public function publisher()
    {
        return $this->belongsTo(Publisher::class)->select(['id', 'name', 'slug']);
    }

    /**
     * رابطه با نویسندگان همکار
     */
    public function authors()
    {
        return $this->belongsToMany(Author::class, 'post_author')
            ->select(['authors.id', 'name', 'slug']);
    }

    /**
     * رابطه با تصویر اصلی
     */
    public function featuredImage()
    {
        return $this->hasOne(PostImage::class)
            ->select(['id', 'post_id', 'image_path', 'hide_image'])
            ->where(function($query) {
                $query->where('hide_image', '!=', 'hidden')
                    ->orWhereNull('hide_image');
            })
            ->orderBy('id');
    }

    /**
     * رابطه با همه تصاویر
     */
    public function images()
    {
        return $this->hasMany(PostImage::class)
            ->select(['id', 'post_id', 'image_path', 'hide_image'])
            ->orderBy('id');
    }

    /**
     * رابطه با کاربر
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * دریافت محتوای تمیز شده
     */
    public function getPurifiedContentAttribute()
    {
        $cacheKey = "post_content_{$this->id}";

        return Cache::remember($cacheKey, 3600, function () {
            $content = $this->getContentFromElasticsearch();

            if (!empty($content['description']['persian'])) {
                return $content['description']['persian'];
            }

            return $this->getContentFromFile();
        });
    }

    /**
     * دریافت محتوای انگلیسی تمیز شده
     */
    public function getEnglishContentAttribute()
    {
        $cacheKey = "post_english_content_{$this->id}";

        return Cache::remember($cacheKey, 3600, function () {
            $content = $this->getContentFromElasticsearch();

            if (!empty($content['description']['english'])) {
                return $content['description']['english'];
            }

            return $this->getEnglishContentFromFile();
        });
    }

    /**
     * دریافت عنوان از Elasticsearch (در صورت وجود)
     */
    public function getElasticsearchTitleAttribute()
    {
        $content = $this->getContentFromElasticsearch();
        return $content['title'] ?? $this->title;
    }

    /**
     * دریافت نام نویسنده از Elasticsearch
     */
    public function getElasticsearchAuthorAttribute()
    {
        $content = $this->getContentFromElasticsearch();
        return $content['author'] ?? ($this->author ? $this->author->name : '');
    }

    /**
     * دریافت دسته‌بندی از Elasticsearch
     */
    public function getElasticsearchCategoryAttribute()
    {
        $content = $this->getContentFromElasticsearch();
        return $content['category'] ?? ($this->category ? $this->category->name : '');
    }

    /**
     * دریافت ناشر از Elasticsearch
     */
    public function getElasticsearchPublisherAttribute()
    {
        $content = $this->getContentFromElasticsearch();
        return $content['publisher'] ?? ($this->publisher ? $this->publisher->name : '');
    }

    /**
     * دریافت سال انتشار از Elasticsearch
     */
    public function getElasticsearchPublicationYearAttribute()
    {
        $content = $this->getContentFromElasticsearch();
        return $content['publication_year'] ?? $this->publication_year;
    }

    /**
     * دریافت فرمت از Elasticsearch
     */
    public function getElasticsearchFormatAttribute()
    {
        $content = $this->getContentFromElasticsearch();
        return $content['format'] ?? $this->format;
    }

    /**
     * دریافت زبان از Elasticsearch
     */
    public function getElasticsearchLanguageAttribute()
    {
        $content = $this->getContentFromElasticsearch();
        return $content['language'] ?? $this->languages;
    }

    /**
     * دریافت ISBN از Elasticsearch
     */
    public function getElasticsearchIsbnAttribute()
    {
        $content = $this->getContentFromElasticsearch();
        return $content['isbn'] ?? $this->isbn;
    }

    /**
     * دریافت تعداد صفحات از Elasticsearch
     */
    public function getElasticsearchPagesCountAttribute()
    {
        $content = $this->getContentFromElasticsearch();
        return $content['pages_count'] ?? $this->pages_count;
    }

    /**
     * دریافت محتوا از Elasticsearch
     */
    private function getContentFromElasticsearch()
    {
        static $content = null;

        // اگر قبلاً محتوا دریافت شده، آن را برگردان
        if ($content !== null) {
            return $content;
        }

        $cacheKey = "post_elasticsearch_data_{$this->id}";

        $content = Cache::remember($cacheKey, 3600, function () {
            try {
                if (!app()->bound('App\Services\ElasticsearchService')) {
                    return [];
                }

                $elasticsearchService = app('App\Services\ElasticsearchService');

                // استفاده از متد جدید getPostContent
                return $elasticsearchService->getPostContent($this->id);

            } catch (\Exception $e) {
                \Log::error("خطا در دریافت محتوا از Elasticsearch برای پست {$this->id}: " . $e->getMessage());
                return [];
            }
        });

        return $content;
    }

    /**
     * دریافت محتوا از فایل (برای آینده)
     */
    private function getContentFromFile()
    {
        // این متد در آینده پیاده‌سازی خواهد شد
        // فعلاً محتوای خالی برمی‌گردانیم
        return '';
    }

    /**
     * دریافت محتوای انگلیسی از فایل (برای آینده)
     */
    private function getEnglishContentFromFile()
    {
        // این متد در آینده پیاده‌سازی خواهد شد
        // فعلاً محتوای خالی برمی‌گردانیم
        return '';
    }

    /**
     * تولید elasticsearch_id در هنگام ایجاد
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->elasticsearch_id)) {
                $post->elasticsearch_id = 'post_' . Str::random(40);
            }

            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title') && empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    /**
     * پاکسازی کش مرتبط با این پست
     */
    public function clearCache()
    {
        $cacheKeys = [
            "post_{$this->id}_featured_image",
            "post_{$this->id}_related_posts_admin",
            "post_{$this->id}_related_posts_user",
            "home_latest_posts",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * پیدا کردن پست بر اساس slug
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
