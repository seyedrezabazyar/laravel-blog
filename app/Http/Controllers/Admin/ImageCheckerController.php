<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ImageCheckerController extends Controller
{
    /**
     * نمایش صفحه بررسی تصاویر گمشده
     */
    public function index()
    {
        return view('admin.images.checker');
    }

    /**
     * بررسی تصاویر گمشده در محدوده مشخص شده
     */
    public function check(Request $request)
    {
        // اعتبارسنجی درخواست
        $request->validate([
            'start_id' => 'required|integer|min:1',
            'end_id' => 'required|integer|min:1|gte:start_id',
            'batch_size' => 'nullable|integer|min:10|max:1000',
        ]);

        $startId = $request->input('start_id');
        $endId = $request->input('end_id');
        $batchSize = $request->input('batch_size', 50); // مقدار پیش‌فرض 50

        // تعداد کل تصاویر برای بررسی
        $totalImages = PostImage::whereBetween('id', [$startId, $endId])->count();
        $processedCount = 0;
        $missingCount = 0;
        $errors = [];

        try {
            // بررسی تصاویر در دسته‌های کوچک‌تر برای جلوگیری از خطای زمان اجرا
            $currentId = $startId;

            while ($currentId <= $endId) {
                $batchEndId = min($currentId + $batchSize - 1, $endId);

                // دریافت تصاویر این دسته
                $images = PostImage::whereBetween('id', [$currentId, $batchEndId])
                    ->select(['id', 'image_path', 'hide_image'])
                    ->get();

                foreach ($images as $image) {
                    $processedCount++;

                    try {
                        $imageUrl = $image->image_url ?? $image->image_path;

                        if (empty($imageUrl)) {
                            // تصویر بدون مسیر را به عنوان گمشده علامت‌گذاری می‌کنیم
                            $image->update(['hide_image' => 'missing']);
                            $missingCount++;
                            continue;
                        }

                        // بررسی وجود تصویر با ارسال درخواست HTTP
                        $response = Http::timeout(5)->head($imageUrl);

                        if ($response->status() !== 200) {
                            // تصویر با کد پاسخ غیر 200 را به عنوان گمشده علامت‌گذاری می‌کنیم
                            $image->update(['hide_image' => 'missing']);
                            $missingCount++;
                        }
                    } catch (\Exception $e) {
                        // خطا در دسترسی به تصویر، آن را به عنوان گمشده علامت‌گذاری می‌کنیم
                        $image->update(['hide_image' => 'missing']);
                        $missingCount++;
                        $errors[] = "خطا در بررسی تصویر {$image->id}: " . $e->getMessage();
                    }
                }

                // به روزرسانی شناسه فعلی برای دسته بعدی
                $currentId = $batchEndId + 1;
            }

            // پیام موفقیت
            $successMessage = "بررسی تصاویر به پایان رسید. {$processedCount} تصویر بررسی شد و {$missingCount} تصویر به عنوان گمشده علامت‌گذاری شد.";

            return redirect()->route('admin.images.checker')
                ->with('success', $successMessage)
                ->with('processed_count', $processedCount)
                ->with('missing_count', $missingCount)
                ->with('errors', $errors);

        } catch (\Exception $e) {
            // در صورت بروز خطای کلی
            return redirect()->route('admin.images.checker')
                ->with('error', 'خطا در بررسی تصاویر: ' . $e->getMessage())
                ->with('processed_count', $processedCount)
                ->with('missing_count', $missingCount)
                ->with('errors', $errors);
        }
    }
}
