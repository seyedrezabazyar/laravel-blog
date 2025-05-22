<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    use HasSlug;

    protected $fillable = [
        'md5_hash', 'user_id', 'category_id', 'author_id', 'publisher_id',
        'title', 'english_title', 'slug', 'language', 'publication_year',
        'format', 'book_codes', 'purchase_link', 'summary', 'english_summary',
        'content_file_path', 'english_content_file_path',
        'hide_content', 'is_published'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'hide_content' => 'boolean',
        'publication_year' => 'integer',
    ];

    private $contentDir = 'posts/content';
    private $englishContentDir = 'posts/content_en';

    // تنظیم slug
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    // Scope برای لیست‌ها (فقط فیلدهای ضروری)
    public function scopeForListing($query)
    {
        return $query->select([
            'id', 'title', 'english_title', 'slug', 'category_id',
            'author_id', 'publisher_id', 'publication_year', 'format',
            'summary', 'english_summary', 'is_published', 'hide_content', 'created_at'
        ]);
    }

    // Scope برای جستجو
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('english_title', 'like', "%{$term}%")
                ->orWhere('summary', 'like', "%{$term}%")
                ->orWhere('book_codes', 'like', "%{$term}%");
        });
    }

    // روابط
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
        return $this->belongsTo(Publisher::class)->select(['id', 'name', 'slug']);
    }

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'post_author')
            ->select(['authors.id', 'name', 'slug']);
    }

    public function featuredImage()
    {
        return $this->hasOne(PostImage::class)
            ->select(['id', 'post_id', 'image_path', 'hide_image'])
            ->where('hide_image', '!=', 'hidden')
            ->orderBy('sort_order');
    }

    public function images()
    {
        return $this->hasMany(PostImage::class)->orderBy('sort_order');
    }

    // Accessors برای بارگذاری محتوا از فایل
    public function getContentAttribute()
    {
        if (empty($this->content_file_path)) {
            return '';
        }

        $cacheKey = "post_content_{$this->id}";

        return Cache::remember($cacheKey, 1800, function() {
            return $this->loadContentFromFile($this->content_file_path, $this->contentDir);
        });
    }

    public function getEnglishContentAttribute()
    {
        if (empty($this->english_content_file_path)) {
            return '';
        }

        $cacheKey = "post_english_content_{$this->id}";

        return Cache::remember($cacheKey, 1800, function() {
            return $this->loadContentFromFile($this->english_content_file_path, $this->englishContentDir);
        });
    }

    // Helper method برای بارگذاری محتوا
    private function loadContentFromFile($fileName, $directory)
    {
        if (empty($fileName)) {
            return '';
        }

        $filePath = "{$directory}/{$fileName}";

        try {
            if (!Storage::exists($filePath)) {
                \Log::warning("Content file not found: {$filePath}");
                return '';
            }

            $compressedContent = Storage::get($filePath);
            $content = gzuncompress($compressedContent);

            if ($content === false) {
                \Log::error("Failed to decompress content from: {$filePath}");
                return '';
            }

            return $content;

        } catch (\Exception $e) {
            \Log::error("Error loading content from {$filePath}: " . $e->getMessage());
            return '';
        }
    }

    // Helper method برای ذخیره محتوا در فایل
    public function saveContentToFile($content, $language = 'fa')
    {
        if (empty($content)) {
            return null;
        }

        $directory = $language === 'en' ? $this->englishContentDir : $this->contentDir;
        $hash = md5($content);
        $fileName = "{$this->id}_{$hash}.txt";
        $filePath = "{$directory}/{$fileName}";

        try {
            $compressedContent = gzcompress($content, 9);
            Storage::put($filePath, $compressedContent);

            // پاک کردن کش
            $cacheKey = $language === 'en' ? "post_english_content_{$this->id}" : "post_content_{$this->id}";
            Cache::forget($cacheKey);

            return $fileName;

        } catch (\Exception $e) {
            \Log::error("Error saving content to file for post {$this->id}: " . $e->getMessage());
            return null;
        }
    }

    // Helper method برای حذف فایل‌های محتوا
    public function deleteContentFiles()
    {
        if ($this->content_file_path) {
            Storage::delete("{$this->contentDir}/{$this->content_file_path}");
        }

        if ($this->english_content_file_path) {
            Storage::delete("{$this->englishContentDir}/{$this->english_content_file_path}");
        }

        // پاک کردن کش
        Cache::forget("post_content_{$this->id}");
        Cache::forget("post_english_content_{$this->id}");
    }

    // Event handlers
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($post) {
            $post->deleteContentFiles();
        });
    }
}
