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

    protected $casts = [
        'hide_image' => 'boolean',
    ];

    // رابطه با پست
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    // بررسی وضعیت مخفی بودن تصویر
    public function isHidden()
    {
        return $this->hide_image;
    }

    /**
     * دریافت URL کامل تصویر
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        // اگر URL کامل HTTP یا HTTPS باشد، مستقیماً برگردانده شود
        if (strpos($this->image_path, 'http://') === 0 || strpos($this->image_path, 'https://') === 0) {
            return $this->image_path;
        }

        // اگر مسیر با images.balyan.ir شروع شود
        if (strpos($this->image_path, 'images.balyan.ir/') !== false) {
            return 'https://' . $this->image_path;
        }

        // اگر تصویر در هاست دانلود باشد (با الگوی post_images/)
        if (strpos($this->image_path, 'post_images/') === 0 || strpos($this->image_path, 'posts/') === 0) {
            return config('app.custom_image_host', 'https://images.balyan.ir') . '/' . $this->image_path;
        }

        // برای سازگاری با تصاویر قدیمی ذخیره شده در استوریج محلی
        return asset('storage/' . $this->image_path);
    }
}
