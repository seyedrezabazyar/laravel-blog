<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'biography',
        'image',
    ];

    // رابطه با کتاب‌ها
    public function books()
    {
        return $this->belongsToMany(Book::class);
    }

    // فقط کتاب‌های منتشر شده و غیر محدود شده را برگرداند
    public function publicBooks()
    {
        return $this->belongsToMany(Book::class)
            ->where('is_published', true)
            ->where('is_restricted', false);
    }
}
