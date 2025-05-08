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
            ->orderBy('created_at', 'desc')
            ->paginate(100);
        
        // افزودن ویژگی‌های محاسبه شده به هر تصویر
        $images->getCollection()->transform(function ($image) {
            $image->makeVisible(['image_url']);
            return $image;
        });
            
        return response()->json($images);
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