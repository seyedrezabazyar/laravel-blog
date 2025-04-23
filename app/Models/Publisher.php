<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
    ];

    // Relación con posts (libros publicados por esta editorial)
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // Solo posts (libros) publicados y no restringidos
    public function publicPosts()
    {
        return $this->hasMany(Post::class)
            ->where('is_published', true)
            ->where('hide_content', false);
    }
}
