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

    // SCOPES
    public function scopeVisibleToUser($query)
    {
        return $query->where('is_published', true)->where('hide_content', false);
    }

    public function scopeVisibleToAdmin($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeForListing($query)
    {
        return $query->select([
            'id', 'title', 'slug', 'category_id', 'author_id', 'publisher_id',
            'publication_year', 'format', 'language', 'is_published', 'hide_content',
            'created_at', 'updated_at', 'md5'
        ]);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")->orWhere('isbn', 'like', "%{$term}%");
        });
    }

    // RELATIONSHIPS
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->select(['id', 'name', 'slug']);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class)->select(['id', 'name', 'slug']);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class)->select(['id', 'name', 'slug']);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'post_author')->select(['authors.id', 'name', 'slug']);
    }

    public function featuredImage(): HasOne
    {
        return $this->hasOne(PostImage::class)
            ->select(['id', 'post_id', 'status'])
            ->where(function($query) {
                $query->where('status', '!=', 'hidden')->orWhereNull('status');
            })
            ->orderBy('id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class)->select(['id', 'post_id', 'status'])->orderBy('id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // IMAGE URL METHODS
    public function getFeaturedImageUrlAttribute(): string
    {
        if (empty($this->md5)) {
            $this->generateMd5IfMissing();
        }

        if (empty($this->md5)) {
            return asset('images/default-book.png');
        }

        $directory = $this->calculateImageDirectory();
        $imageHost = config('app.custom_image_host', 'https://images.balyan.ir');

        return "{$imageHost}/{$directory}/{$this->md5}.jpg";
    }

    private function calculateImageDirectory(): int
    {
        return intval(($this->id - 1) / 10000) * 10000;
    }

    private function generateMd5IfMissing(): void
    {
        if (empty($this->md5)) {
            $this->md5 = md5($this->title . $this->id . microtime() . uniqid());
            \DB::table('posts')->where('id', $this->id)->update(['md5' => $this->md5]);
        }
    }

    public function getFeaturedImageUrlWithSize(string $size = 'medium'): string
    {
        $baseUrl = $this->featured_image_url;

        if (str_contains($baseUrl, 'default-book.png')) {
            return $baseUrl;
        }

        $sizeParams = [
            'thumbnail' => '?w=150&h=200&fit=crop',
            'small' => '?w=300&h=400&fit=crop',
            'medium' => '?w=600&h=800&fit=crop',
            'large' => '?w=900&h=1200&fit=crop',
        ];

        return $baseUrl . ($sizeParams[$size] ?? $sizeParams['medium']);
    }

    public function getFeaturedImageResponsiveUrlsAttribute(): array
    {
        $baseUrl = $this->featured_image_url;

        if (str_contains($baseUrl, 'default-book.png')) {
            return [
                'thumbnail' => $baseUrl,
                'small' => $baseUrl,
                'medium' => $baseUrl,
                'large' => $baseUrl,
                'original' => $baseUrl,
            ];
        }

        return [
            'thumbnail' => $baseUrl . '?w=150&h=200&fit=crop',
            'small' => $baseUrl . '?w=300&h=400&fit=crop',
            'medium' => $baseUrl . '?w=600&h=800&fit=crop',
            'large' => $baseUrl . '?w=900&h=1200&fit=crop',
            'original' => $baseUrl,
        ];
    }

    public function hasFeaturedImage(): bool
    {
        return !empty($this->md5);
    }

    // ELASTICSEARCH METHODS - اصلاح شده برای ساختار واقعی
    public function getPurifiedContentAttribute(): string
    {
        $content = $this->getContentFromElasticsearch();
        // استفاده از ساختار واقعی: description_fa
        if (!empty($content['description_fa'])) {
            return $content['description_fa'];
        }
        return '';
    }

    public function getEnglishContentAttribute(): string
    {
        $content = $this->getContentFromElasticsearch();
        // استفاده از ساختار واقعی: description_en
        if (!empty($content['description_en'])) {
            return $content['description_en'];
        }
        return '';
    }

    public function getElasticsearchTitleAttribute(): string
    {
        $content = $this->getContentFromElasticsearch();
        // استفاده از ساختار واقعی: title_fa
        return $content['title_fa'] ?? $this->title;
    }

    public function getElasticsearchEnglishTitleAttribute(): string
    {
        $content = $this->getContentFromElasticsearch();
        // استفاده از ساختار واقعی: title_en
        return $content['title_en'] ?? ($this->english_title ?? '');
    }

    public function getElasticsearchAuthorAttribute(): string
    {
        $content = $this->getContentFromElasticsearch();
        return $content['author'] ?? ($this->author ? $this->author->name : '');
    }

    public function getElasticsearchCategoryAttribute(): string
    {
        $content = $this->getContentFromElasticsearch();
        return $content['category'] ?? ($this->category ? $this->category->name : '');
    }

    public function getElasticsearchPublisherAttribute(): string
    {
        $content = $this->getContentFromElasticsearch();
        return $content['publisher'] ?? ($this->publisher ? $this->publisher->name : '');
    }

    public function getElasticsearchPublicationYearAttribute(): ?int
    {
        $content = $this->getContentFromElasticsearch();
        // استفاده از ساختار واقعی: year
        return $content['year'] ?? $this->publication_year;
    }

    public function getElasticsearchFormatAttribute(): ?string
    {
        $content = $this->getContentFromElasticsearch();
        return $content['format'] ?? $this->format;
    }

    public function getElasticsearchLanguageAttribute(): ?string
    {
        $content = $this->getContentFromElasticsearch();
        return $content['language'] ?? $this->language;
    }

    public function getElasticsearchIsbnAttribute(): ?string
    {
        $content = $this->getContentFromElasticsearch();
        return $content['isbn'] ?? $this->isbn;
    }

    public function getElasticsearchPagesCountAttribute(): ?int
    {
        $content = $this->getContentFromElasticsearch();
        return $content['pages_count'] ?? $this->pages_count;
    }

    // PRIVATE METHODS
    private function getContentFromElasticsearch(): array
    {
        static $cache = [];

        if (isset($cache[$this->id])) {
            return $cache[$this->id];
        }

        try {
            if (!app()->bound('App\Services\ElasticsearchService')) {
                return $cache[$this->id] = [];
            }

            $elasticsearchService = app('App\Services\ElasticsearchService');
            $content = $elasticsearchService->getPostContent($this->id);

            return $cache[$this->id] = $content ?: [];

        } catch (\Exception $e) {
            return $cache[$this->id] = [];
        }
    }

    // MODEL EVENTS
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
            if (empty($post->md5)) {
                $post->md5 = md5($post->title . microtime() . Str::random(10));
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title') && empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    // CACHE MANAGEMENT
    public function clearCache(): void
    {
        $cacheKeys = [
            "image_url_{$this->id}_{$this->md5}",
            "post_{$this->id}_featured_image"
        ];

        foreach ($cacheKeys as $key) {
            \Cache::forget($key);
        }
    }

    // ROUTE MODEL BINDING
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // DEBUG METHODS
    public function getImageDebugInfo(): array
    {
        return [
            'post_id' => $this->id,
            'md5' => $this->md5,
            'calculated_directory' => $this->calculateImageDirectory(),
            'featured_image_url' => $this->featured_image_url,
            'responsive_urls' => $this->featured_image_responsive_urls,
            'has_featured_image_record' => $this->featuredImage()->exists(),
            'image_status' => $this->featuredImage ? $this->featuredImage->status : null,
        ];
    }
}
