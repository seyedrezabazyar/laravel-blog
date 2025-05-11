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
     * تنظیمات برای عدم بارگذاری خودکار روابط - کاهش فشار روی دیتابیس
     * از $with استفاده نمی‌کنیم تا هر جا لازم است روابط را بارگذاری کنیم
     */
    protected $with = [];

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
     * رابطه با ناشر - بهینه‌سازی شده
     */
    public function publisher()
    {
        return $this->belongsTo(Publisher::class, 'publisher_id')
            ->select(['id', 'name', 'slug', 'logo']); // انتخاب فقط فیلدهای مورد نیاز
    }

    public function featuredImage()
    {
        $cacheKey = "post_{$this->id}_featured_image";

        // استفاده از کش کوتاه مدت برای کاهش فشار روی دیتابیس
        $cachedImage = Cache::remember($cacheKey, 60, function() {
            return $this->hasOne(PostImage::class)
                ->select(['id', 'post_id', 'image_path', 'caption', 'hide_image', 'sort_order'])
                ->orderBy('sort_order')
                ->first();
        });

        if ($cachedImage) {
            // اینجا مشکل است - wherePivot با hasOne سازگار نیست
            // از where معمولی استفاده کنید
            return $this->hasOne(PostImage::class)->where('id', $cachedImage->id);
        }

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
     * Get purified content with caching - بهینه‌سازی شده با TTL طولانی‌تر
     */
    public function getPurifiedContentAttribute()
    {
        $cacheKey = "post_{$this->id}_purified_content_" . md5($this->content);

        return Cache::remember($cacheKey, 86400 * 14, function () {
            return Purifier::clean($this->content);
        });
    }

    /**
     * اسکوپ برای پست‌های قابل مشاهده برای کاربر فعلی
     * این اسکوپ به جای استفاده از چندین کوئری جداگانه در هر بار فراخوانی،
     * یک کوئری ساده و بهینه ایجاد می‌کند
     */
    public function scopeVisibleToUser($query)
    {
        // همه پست‌ها باید منتشر شده باشند
        $query->where('is_published', true);

        // اگر کاربر مدیر نیست، فقط پست‌های غیر مخفی را نشان می‌دهیم
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
            ->orWhereExists(function ($subq) use ($authorId) {
                $subq->select(DB::raw(1))
                    ->from('post_author')
                    ->whereRaw('post_author.post_id = posts.id')
                    ->where('post_author.author_id', $authorId);
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
        try {
            // استفاده از ایندکس مشخص شده برای بهینه‌سازی جستجو
            return $query->whereRaw("MATCH(title) AGAINST(? IN BOOLEAN MODE)", [$searchTerm . '*']);
        } catch (\Exception $e) {
            // در صورت بروز خطا از جستجوی ساده استفاده می‌کنیم
            \Log::error('Search error: ' . $e->getMessage());

            // جستجو فقط در عنوان برای کارایی بیشتر
            return $query->where('title', 'like', "%{$searchTerm}%")
                ->orWhere('english_title', 'like', "%{$searchTerm}%");
        }
    }
}
