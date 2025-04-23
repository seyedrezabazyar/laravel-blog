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
        'keywords',
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
    public function images()
    {
        return $this->hasMany(PostImage::class);
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
}
