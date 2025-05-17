<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mews\Purifier\Facades\Purifier;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    use HasSlug;

    /**
     * فیلدهای قابل پر شدن
     *
     * @var array<int, string>
     */
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
        'language',
        'publication_year',
        'format',
        'book_codes',
        'purchase_link',
        'hide_content',
        'is_published',
        'md5_hash',
    ];

    /**
     * فیلدهایی که باید به نوع خاص تبدیل شوند
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_published' => 'boolean',
        'hide_content' => 'boolean',
        'publication_year' => 'integer',
    ];

    /**
     * گزینه‌های ایجاد اسلاگ
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    /**
     * رابطه با کاربر
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->select(['id', 'name', 'email']);
    }

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
        return $this->belongsTo(Publisher::class)->select(['id', 'name', 'slug', 'logo']);
    }

    /**
     * رابطه با تصویر شاخص
     */
    public function featuredImage(): HasOne
    {
        return $this->hasOne(PostImage::class)
            ->select(['id', 'post_id', 'image_path', 'caption', 'hide_image', 'sort_order'])
            ->orderBy('sort_order');
    }

    /**
     * رابطه با تمام تصاویر
     */
    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class)
            ->select(['id', 'post_id', 'image_path', 'caption', 'hide_image', 'sort_order'])
            ->orderBy('sort_order');
    }

    /**
     * رابطه با تمام نویسندگان همکار
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'post_author')
            ->select(['authors.id', 'name', 'slug']);
    }

    /**
     * رابطه با برچسب‌ها
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)
            ->select(['tags.id', 'name', 'slug']);
    }

    /**
     * محتوای پاکسازی شده
     *
     * accessor برای دریافت محتوای پاکسازی شده
     */
    public function getPurifiedContentAttribute(): string
    {
        $cacheKey = "post_{$this->id}_purified_content_" . md5($this->content);
        return Cache::remember($cacheKey, 86400 * 7, function () {
            return Purifier::clean($this->content ?? '');
        });
    }

    /**
     * محدود کردن کوئری به پست‌های قابل نمایش برای کاربر
     */
    public function scopeVisibleToUser(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->when(!auth()->check() || !auth()->user()->isAdmin(), function ($q) {
                $q->where('hide_content', false);
            });
    }

    /**
     * محدود کردن کوئری به پست‌های یک دسته‌بندی خاص
     */
    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * محدود کردن کوئری به پست‌های یک نویسنده خاص
     */
    public function scopeByAuthor(Builder $query, int $authorId): Builder
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

    /**
     * محدود کردن کوئری به پست‌های یک ناشر خاص
     */
    public function scopeByPublisher(Builder $query, int $publisherId): Builder
    {
        return $query->where('publisher_id', $publisherId);
    }

    /**
     * جستجوی متن کامل
     */
    public function scopeFullTextSearch(Builder $query, string $searchTerm): Builder
    {
        $searchTerm = preg_replace('/[^\p{L}\p{N}_\s-]/u', '', $searchTerm);
        return $query->where(function($q) use ($searchTerm) {
            $q->where('title', 'like', "%{$searchTerm}%")
                ->orWhere('english_title', 'like', "%{$searchTerm}%")
                ->orWhere('book_codes', 'like', "%{$searchTerm}%");
        });
    }
}
