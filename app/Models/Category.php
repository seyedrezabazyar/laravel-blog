<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug'
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    protected $withCount = ['visiblePosts'];

    public function visiblePosts()
    {
        return $this->hasMany(Post::class)
            ->where('is_published', true)
            ->where('hide_content', false);
    }
}
