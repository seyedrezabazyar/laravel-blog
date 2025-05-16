<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

            // ثبت لاگ برای اطلاع از شروع عملیات
            Log::info('بررسی تصاویر آغاز شد', [
                'start_id' => $startId,
                'end_id' => $endId,
                'batch_size' => $batchSize,
                'total_images' => $totalImages
            ]);

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
                            $this->markImageAsMissing($image, "مسیر تصویر خالی است");
                            $missingCount++;
                            continue;
                        }

                        // اگر تصویر قبلاً به عنوان گمشده علامت‌گذاری شده، آن را دوباره بررسی نمی‌کنیم
                        if ($image->hide_image === 'missing') {
                            $missingCount++;
                            continue;
                        }

                        // بررسی وجود تصویر با ارسال درخواست HTTP
                        $response = Http::timeout(5)->head($imageUrl);

                        if ($response->status() !== 200) {
                            // تصویر با کد پاسخ غیر 200 را به عنوان گمشده علامت‌گذاری می‌کنیم
                            $this->markImageAsMissing($image, "کد پاسخ: " . $response->status());
                            $missingCount++;
                        }
                    } catch (\Exception $e) {
                        // خطا در دسترسی به تصویر، آن را به عنوان گمشده علامت‌گذاری می‌کنیم
                        $this->markImageAsMissing($image, $e->getMessage());
                        $missingCount++;
                        $errors[] = "خطا در بررسی تصویر {$image->id}: " . $e->getMessage();
                    }
                }

                // به روزرسانی شناسه فعلی برای دسته بعدی
                $currentId = $batchEndId + 1;

                // ثبت لاگ پیشرفت کار
                Log::info('پیشرفت بررسی تصاویر', [
                    'current_id' => $currentId,
                    'processed_count' => $processedCount,
                    'missing_count' => $missingCount
                ]);
            }

            // پیام موفقیت
            $successMessage = "بررسی تصاویر به پایان رسید. {$processedCount} تصویر بررسی شد و {$missingCount} تصویر به عنوان گمشده علامت‌گذاری شد.";

            // ثبت لاگ پایان عملیات
            Log::info('بررسی تصاویر به پایان رسید', [
                'processed_count' => $processedCount,
                'missing_count' => $missingCount,
                'error_count' => count($errors)
            ]);

            return redirect()->route('admin.images.checker')
                ->with('success', $successMessage)
                ->with('processed_count', $processedCount)
                ->with('missing_count', $missingCount)
                ->with('errors', $errors);

        } catch (\Exception $e) {
            // ثبت لاگ خطا
            Log::error('خطا در بررسی تصاویر', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'processed_count' => $processedCount,
                'missing_count' => $missingCount
            ]);

            // در صورت بروز خطای کلی
            return redirect()->route('admin.images.checker')
                ->with('error', 'خطا در بررسی تصاویر: ' . $e->getMessage())
                ->with('processed_count', $processedCount)
                ->with('missing_count', $missingCount)
                ->with('errors', $errors);
        }
    }

    /**
     * تابع کمکی برای علامت‌گذاری تصویر به عنوان گمشده
     *
     * @param PostImage $image تصویر مورد نظر
     * @param string $reason دلیل گمشده بودن تصویر
     * @return bool نتیجه عملیات به‌روزرسانی
     */
    private function markImageAsMissing(PostImage $image, string $reason = ''): bool
    {
        try {
            // علامت‌گذاری به عنوان گمشده با به‌روزرسانی صریح زمان
            $result = $image->update([
                'hide_image' => 'missing',
                'updated_at' => now() // به‌روزرسانی صریح زمان
            ]);

            // ثبت لاگ برای اشکال‌زدایی
            Log::info('تصویر به عنوان گمشده علامت‌گذاری شد', [
                'image_id' => $image->id,
                'image_path' => $image->image_path,
                'reason' => $reason
            ]);

            return $result;
        } catch (\Exception $e) {
            // ثبت لاگ خطا
            Log::error('خطا در علامت‌گذاری تصویر به عنوان گمشده', [
                'image_id' => $image->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
