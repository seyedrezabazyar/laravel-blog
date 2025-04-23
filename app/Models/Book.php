<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class Book extends Model
{
    protected $fillable = [
        'uuid',
        'category_id',
        'slug',
        'title_fa',
        'title_en',
        'description_fa',
        'description_en',
        'purchase_link',
        'cover_image',
        'hide_cover',
        'is_restricted',
        'keywords',
        'language',
        'publish_year',
        'publisher',
        'format',
        'isbn_codes',
        'is_published',
        'user_id',
    ];

    protected $casts = [
        'hide_cover' => 'boolean',
        'is_restricted' => 'boolean',
        'is_published' => 'boolean',
    ];

    // رابطه با دسته‌بندی
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // رابطه با کاربر
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // رابطه با نویسندگان
    public function authors()
    {
        return $this->belongsToMany(Author::class);
    }

    // متد برای پاکسازی محتوا
    public function getPurifiedDescriptionFaAttribute()
    {
        return Purifier::clean($this->description_fa);
    }

    public function getPurifiedDescriptionEnAttribute()
    {
        return Purifier::clean($this->description_en);
    }

    // متد برای نمایش تصویر کتاب
    public function getDisplayCoverAttribute()
    {
        if ($this->hide_cover) {
            return 'default-book-cover.jpg'; // تصویر پیش‌فرض
        }

        return $this->cover_image ?: 'default-book-cover.jpg';
    }

    // آیا کتاب قابل نمایش است
    public function getIsVisibleAttribute()
    {
        if (auth()->check() && auth()->user()->isAdmin()) {
            return true;
        }

        return !$this->is_restricted && $this->is_published;
    }

    // گرفتن آرایه‌ای از کلمات کلیدی
    public function getKeywordsArrayAttribute()
    {
        return $this->keywords ? array_map('trim', explode(',', $this->keywords)) : [];
    }

    // گرفتن آرایه‌ای از کدهای کتاب
    public function getIsbnCodesArrayAttribute()
    {
        return $this->isbn_codes ? array_map('trim', explode(',', $this->isbn_codes)) : [];
    }
}
