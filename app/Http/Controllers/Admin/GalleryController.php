<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GalleryController extends Controller
{
    /**
     * نمایش صفحه گالری
     */
    public function index()
    {
        return view('admin.images.gallery');
    }

    /**
     * دریافت تصاویر دسته‌بندی نشده
     */
    public function getImages(Request $request)
    {
        $images = PostImage::whereNull('hide_image')
            ->with('post:id,title')
            ->orderBy('id', 'asc') // ترتیب صعودی بر اساس ID
            ->paginate(100);

        // افزودن ویژگی‌های محاسبه شده و برگرداندن مسیر اصلی تصویر
        $images->getCollection()->transform(function ($image) {
            // استفاده مستقیم از image_path به جای image_url
            $image->makeVisible(['image_path']);

            // اضافه کردن مسیر کامل تصویر به عنوان یک ویژگی جدید
            $image->raw_image_url = $this->getFullImageUrl($image->image_path);

            return $image;
        });

        return response()->json($images);
    }

    /**
     * تبدیل مسیر تصویر به URL کامل
     */
    private function getFullImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return asset('images/default-book.png');
        }

        // Direct URL for HTTP/HTTPS paths
        if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
            return $imagePath;
        }

        // Handle images.balyan.ir domain
        if (strpos($imagePath, 'images.balyan.ir/') !== false) {
            return 'https://' . $imagePath;
        }

        // Handle download host images
        if (strpos($imagePath, 'post_images/') === 0 || strpos($imagePath, 'posts/') === 0) {
            return config('app.custom_image_host', 'https://images.balyan.ir') . '/' . $imagePath;
        }

        // Local storage fallback
        return asset('storage/' . $imagePath);
    }

    /**
     * دسته‌بندی تصویر
     */
    public function categorizeImage(Request $request)
    {
        $request->validate([
            'image_id' => 'required|exists:post_images,id',
            'hide_image' => 'required|boolean',
        ]);

        $image = PostImage::findOrFail($request->image_id);

        // تبدیل مقدار boolean به enum
        $image->hide_image = $request->hide_image ? 'hidden' : 'visible';
        $image->save();

        // پاک کردن کش مربوط به این تصویر
        $this->clearImageCache($image->id);

        return response()->json([
            'success' => true,
            'message' => 'تصویر با موفقیت دسته‌بندی شد'
        ]);
    }

    /**
     * پاک کردن کش مربوط به یک تصویر
     */
    private function clearImageCache($imageId)
    {
        Cache::forget("post_image_{$imageId}_url");
        Cache::forget("post_image_{$imageId}_display_url_admin");
        Cache::forget("post_image_{$imageId}_display_url_user");
    }
}
