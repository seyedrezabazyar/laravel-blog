<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    protected $fillable = [
        'post_id',
        'image_path',
        'caption',
        'hide_image',
        'sort_order',
    ];

    // Relación con el post al que pertenece la imagen
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    // Verificar si la imagen está oculta
    public function isHidden()
    {
        return $this->hide_image;
    }
}
