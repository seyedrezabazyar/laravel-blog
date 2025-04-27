<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\DownloadHostService;

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

    /**
     * دریافت URL تصویر
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if (strpos($this->image_path, 'http') === 0) {
            return $this->image_path;
        }

        // اگر تصویر در هاست دانلود باشد
        if (strpos($this->image_path, 'post_images/') === 0) {
            return app(DownloadHostService::class)->url($this->image_path);
        }

        // برای سازگاری با تصاویر قدیمی
        return asset('storage/' . $this->image_path);
    }
}
