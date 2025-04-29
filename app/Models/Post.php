<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mews\Purifier\Facades\Purifier;

class Post extends Model
{
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
        'md5_hash'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'hide_image' => 'boolean',
        'hide_content' => 'boolean',
        'publication_year' => 'integer'
    ];

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
     * رابطه با تصاویر پست
     */
    public function featuredImage()
    {
        return $this->hasOne(PostImage::class)->orderBy('sort_order');
    }

    /**
     * رابطه با نویسندگان دیگر (چند به چند)
     */
    public function authors()
    {
        return $this->belongsToMany(Author::class, 'post_author');
    }

    /**
     * دریافت محتوای پاکسازی شده پست
     *
     * @return string
     */
    public function getPurifiedContentAttribute()
    {
        return Purifier::clean($this->content);
    }

    /**
     * آیا پست قابل نمایش است؟
     *
     * @return bool
     */
    public function isViewable()
    {
        return $this->is_published && !$this->hide_content;
    }

    /**
     * آیا تصویر پست قابل نمایش است؟
     *
     * @return bool
     */
    public function hasVisibleImage()
    {
        return $this->featured_image && !$this->hide_image;
    }

    /**
     * دریافت همه تصاویر قابل نمایش
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVisibleImagesAttribute()
    {
        return $this->images()->where('hide_image', false)->orderBy('sort_order')->get();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * دریافت URL کامل تصویر شاخص
     *
     * @return string|null
     */
    public function getFeaturedImageUrlAttribute()
    {
        if (!$this->featured_image) {
            return null;
        }

        // اگر URL کامل HTTP یا HTTPS باشد، مستقیماً برگردانده شود
        if (strpos($this->featured_image, 'http://') === 0 || strpos($this->featured_image, 'https://') === 0) {
            return $this->featured_image;
        }

        // اگر مسیر با images.balyan.ir شروع شود
        if (strpos($this->featured_image, 'images.balyan.ir/') !== false) {
            return 'https://' . $this->featured_image;
        }

        // اگر تصویر در هاست دانلود باشد (با الگوی posts/ یا post_images/)
        if (strpos($this->featured_image, 'posts/') === 0 || strpos($this->featured_image, 'post_images/') === 0) {
            return config('app.custom_image_host', 'https://images.balyan.ir') . '/' . $this->featured_image;
        }

        // برای سازگاری با تصاویر قدیمی ذخیره شده در استوریج محلی
        return asset('storage/' . $this->featured_image);
    }

    public function scopeVisibleToUser($query)
    {
        $query->where('is_published', true);

        if (!auth()->check() || !auth()->user()->isAdmin()) {
            $query->where('hide_content', false);
        }

        return $query;
    }
}
