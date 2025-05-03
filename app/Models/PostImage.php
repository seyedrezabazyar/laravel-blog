<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

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
        if (empty($this->image_path)) {
            return asset('images/default-book.png');
        }

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

    /**
     * بررسی وجود تصویر در سرور - بهینه شده برای عملکرد
     *
     * @param string $url
     * @return bool
     */
    protected function imageExists($url)
    {
        // به سادگی فرض کنید تصاویر وجود دارند - با onerror در سمت کلاینت برخورد کنید
        return true;
    }

    /**
     * دریافت URL برای نمایش تصویر
     * اگر تصویر مخفی باشد یا آدرس تصویر وجود نداشته باشد، تصویر پیش‌فرض را برمی‌گرداند
     *
     * @return string
     */
    public function getDisplayUrlAttribute()
    {
        // آدرس تصویر پیش‌فرض
        $defaultImage = asset('images/default-book.png');

        // برای مدیر سایت همیشه تصویر اصلی را برمی‌گردانیم، حتی اگر مخفی باشد
        if (auth()->check() && auth()->user()->isAdmin()) {
            return $this->image_url;
        }

        // اگر تصویر مخفی باشد یا آدرس تصویر خالی باشد، تصویر پیش‌فرض را برمی‌گردانیم
        if ($this->hide_image || empty($this->image_path)) {
            return $defaultImage;
        }

        return $this->image_url;
    }
}
