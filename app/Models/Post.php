<?php

namespace App\Models;

use App\Services\ImageUrlService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'elasticsearch_id', 'user_id', 'category_id', 'author_id', 'publisher_id',
        'title', 'slug', 'publication_year', 'format', 'language', 'isbn', 'pages_count',
        'hide_content', 'is_published', 'is_indexed', 'md5'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'hide_content' => 'boolean',
        'is_indexed' => 'boolean',
        'publication_year' => 'integer',
        'pages_count' => 'integer',
        'indexed_at' => 'datetime',
    ];

    // ===========================================
    // SCOPES - کوئری‌های بهینه‌شده
    // ===========================================

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
            'publication_year', 'format', 'language', 'is_published', 'hide_content',
            'created_at', 'updated_at', 'md5'
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

    // ===========================================
    // RELATIONSHIPS - روابط دیتابیس
    // ===========================================

    /**
     * رابطه با دسته‌بندی
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->select(['id', 'name', 'slug']);
    }

    /**
     * رابطه با نویسنده اصلی
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class)->select(['id', 'name', 'slug']);
    }

    /**
     * رابطه با ناشر
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class)->select(['id', 'name', 'slug']);
    }

    /**
     * رابطه با نویسندگان همکار
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'post_author')
            ->select(['authors.id', 'name', 'slug']);
    }

    /**
     * رابطه با تصویر اصلی - نسخه بهینه‌شده (بدون image_path)
     */
    public function featuredImage(): HasOne
    {
        return $this->hasOne(PostImage::class)
            ->select(['id', 'post_id', 'status'])
            ->where(function($query) {
                $query->where('status', '!=', 'hidden')
                    ->orWhereNull('status');
            })
            ->orderBy('id');
    }

    /**
     * رابطه با همه تصاویر - نسخه بهینه‌شده (بدون image_path)
     */
    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class)
            ->select(['id', 'post_id', 'status'])
            ->orderBy('id');
    }

    /**
     * رابطه با کاربر
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ===========================================
    // IMAGE URL METHODS - متدهای آدرس تصاویر
    // ===========================================

    /**
     * دریافت URL تصویر اصلی با سیستم محاسباتی
     */
    public function getFeaturedImageUrlAttribute(): string
    {
        $cacheKey = "post_{$this->id}_featured_image_url";

        return Cache::remember($cacheKey, 3600, function () {
            if (!$this->md5) {
                return ImageUrlService::getDefaultImageUrl();
            }

            return ImageUrlService::generateImageUrl($this->id, $this->md5);
        });
    }

    /**
     * دریافت URL تصویر اصلی با اندازه مشخص
     */
    public function getFeaturedImageUrlWithSize(string $size = 'medium'): string
    {
        $cacheKey = "post_{$this->id}_featured_image_url_{$size}";

        return Cache::remember($cacheKey, 3600, function () use ($size) {
            if (!$this->md5) {
                return ImageUrlService::getDefaultImageUrl();
            }

            $responsiveUrls = ImageUrlService::getResponsiveImageUrls($this->id, $this->md5);
            return $responsiveUrls[$size] ?? $responsiveUrls['medium'] ?? ImageUrlService::getDefaultImageUrl();
        });
    }

    /**
     * دریافت آدرس‌های تصویر اصلی با اندازه‌های مختلف
     */
    public function getFeaturedImageResponsiveUrlsAttribute(): array
    {
        $cacheKey = "post_{$this->id}_featured_responsive_urls";

        return Cache::remember($cacheKey, 3600, function () {
            if (!$this->md5) {
                $defaultUrl = ImageUrlService::getDefaultImageUrl();
                return [
                    'thumbnail' => $defaultUrl,
                    'small' => $defaultUrl,
                    'medium' => $defaultUrl,
                    'large' => $defaultUrl,
                    'original' => $defaultUrl,
                ];
            }

            return ImageUrlService::getResponsiveImageUrls($this->id, $this->md5);
        });
    }

    /**
     * تولید HTML تگ img برای تصویر اصلی
     */
    public function getFeaturedImageHtml(string $alt = '', string $cssClass = 'w-full h-full object-cover'): string
    {
        $responsiveUrls = $this->featured_image_responsive_urls;
        $defaultUrl = ImageUrlService::getDefaultImageUrl();

        $alt = htmlspecialchars($alt ?: $this->title);

        $srcset = implode(', ', [
            $responsiveUrls['small'] . ' 300w',
            $responsiveUrls['medium'] . ' 600w',
            $responsiveUrls['large'] . ' 900w',
        ]);

        return sprintf(
            '<img src="%s" srcset="%s" sizes="(max-width: 768px) 100vw, 50vw" alt="%s" class="%s" loading="lazy" onerror="this.onerror=null;this.src=\'%s\';">',
            $responsiveUrls['medium'],
            $srcset,
            $alt,
            $cssClass,
            $defaultUrl
        );
    }

    /**
     * بررسی وجود تصویر اصلی
     */
    public function hasFeaturedImage(): bool
    {
        return !empty($this->md5) && $this->featuredImage()->exists();
    }

    // ===========================================
    // ELASTICSEARCH CONTENT - محتوای Elasticsearch
    // ===========================================

    /**
     * دریافت محتوای تمیز شده از Elasticsearch
     */
    public function getPurifiedContentAttribute(): string
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
     * دریافت محتوای انگلیسی تمیز شده از Elasticsearch
     */
    public function getEnglishContentAttribute(): string
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
    public function getElasticsearchTitleAttribute(): string
    {
        $content = $this->getContentFromElasticsearch();
        return $content['title'] ?? $this->title;
    }

    /**
     * دریافت نام نویسنده از Elasticsearch
     */
    public function getElasticsearchAuthorAttribute(): string
    {
        $content = $this->getContentFromElasticsearch();
        return $content['author'] ?? ($this->author ? $this->author->name : '');
    }

    /**
     * دریافت دسته‌بندی از Elasticsearch
     */
    public function getElasticsearchCategoryAttribute(): string
    {
        $content = $this->getContentFromElasticsearch();
        return $content['category'] ?? ($this->category ? $this->category->name : '');
    }

    /**
     * دریافت ناشر از Elasticsearch
     */
    public function getElasticsearchPublisherAttribute(): string
    {
        $content = $this->getContentFromElasticsearch();
        return $content['publisher'] ?? ($this->publisher ? $this->publisher->name : '');
    }

    /**
     * دریافت سال انتشار از Elasticsearch
     */
    public function getElasticsearchPublicationYearAttribute(): ?int
    {
        $content = $this->getContentFromElasticsearch();
        return $content['publication_year'] ?? $this->publication_year;
    }

    /**
     * دریافت فرمت از Elasticsearch
     */
    public function getElasticsearchFormatAttribute(): ?string
    {
        $content = $this->getContentFromElasticsearch();
        return $content['format'] ?? $this->format;
    }

    /**
     * دریافت زبان از Elasticsearch
     */
    public function getElasticsearchLanguageAttribute(): ?string
    {
        $content = $this->getContentFromElasticsearch();
        return $content['language'] ?? $this->language;
    }

    /**
     * دریافت ISBN از Elasticsearch
     */
    public function getElasticsearchIsbnAttribute(): ?string
    {
        $content = $this->getContentFromElasticsearch();
        return $content['isbn'] ?? $this->isbn;
    }

    /**
     * دریافت تعداد صفحات از Elasticsearch
     */
    public function getElasticsearchPagesCountAttribute(): ?int
    {
        $content = $this->getContentFromElasticsearch();
        return $content['pages_count'] ?? $this->pages_count;
    }

    // ===========================================
    // PRIVATE HELPER METHODS - متدهای کمکی خصوصی
    // ===========================================

    /**
     * دریافت محتوا از Elasticsearch
     */
    private function getContentFromElasticsearch(): array
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
    private function getContentFromFile(): string
    {
        // این متد در آینده پیاده‌سازی خواهد شد
        // فعلاً محتوای خالی برمی‌گردانیم
        return '';
    }

    /**
     * دریافت محتوای انگلیسی از فایل (برای آینده)
     */
    private function getEnglishContentFromFile(): string
    {
        // این متد در آینده پیاده‌سازی خواهد شد
        // فعلاً محتوای خالی برمی‌گردانیم
        return '';
    }

    // ===========================================
    // DEBUG AND UTILITIES - متدهای ابزاری و اشکال‌زدایی
    // ===========================================

    /**
     * دریافت اطلاعات کامل تصویر برای debug
     */
    public function getImageDebugInfo(): array
    {
        return [
            'post_id' => $this->id,
            'md5' => $this->md5,
            'calculated_directory' => $this->md5 ? ImageUrlService::calculateDirectory($this->id) : null,
            'featured_image_url' => $this->featured_image_url,
            'responsive_urls' => $this->featured_image_responsive_urls,
            'has_featured_image_record' => $this->featuredImage()->exists(),
            'image_status' => $this->featuredImage ? $this->featuredImage->status : null,
        ];
    }

    // ===========================================
    // MODEL EVENTS - رویدادهای مدل
    // ===========================================

    /**
     * تولید elasticsearch_id و md5 در هنگام ایجاد
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

            // تولید md5 منحصر به فرد
            if (empty($post->md5)) {
                $post->md5 = md5($post->title . microtime() . Str::random(10));
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title') && empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });

        static::updated(function ($post) {
            $post->clearCache();
        });

        static::deleted(function ($post) {
            $post->clearCache();
        });
    }

    // ===========================================
    // CACHE MANAGEMENT - مدیریت کش
    // ===========================================

    /**
     * پاکسازی کش مرتبط با این پست
     */
    public function clearCache(): void
    {
        $cacheKeys = [
            "post_{$this->id}_featured_image_url",
            "post_{$this->id}_featured_responsive_urls",
            "post_{$this->id}_featured_image_url_thumbnail",
            "post_{$this->id}_featured_image_url_small",
            "post_{$this->id}_featured_image_url_medium",
            "post_{$this->id}_featured_image_url_large",
            "post_{$this->id}_related_posts_admin",
            "post_{$this->id}_related_posts_user",
            "post_content_{$this->id}",
            "post_english_content_{$this->id}",
            "post_elasticsearch_data_{$this->id}",
            "home_latest_posts",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // پاک کردن کش سرویس تصاویر
        if ($this->md5) {
            ImageUrlService::clearImageCache($this->id, $this->md5);
        }
    }

    // ===========================================
    // ROUTE MODEL BINDING - مسیریابی مدل
    // ===========================================

    /**
     * پیدا کردن پست بر اساس slug
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ===========================================
    // LEGACY COMPATIBILITY - سازگاری عقب‌گرد (حذف شده)
    // ===========================================

    // متدهای compressedContent و description حذف شدند
    // چون احتمالاً PostContentCompressed کلاس وجود ندارد
    // و ContentCompressionService نیز تعریف نشده است
}
