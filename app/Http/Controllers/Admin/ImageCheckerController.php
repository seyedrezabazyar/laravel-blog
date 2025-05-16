<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageCheckerController extends Controller
{
    public function index()
    {
        return view('admin.images.checker');
    }

    public function check(Request $request)
    {
        // اعتبارسنجی ورودی‌ها
        $validated = $request->validate([
            'start_id' => 'required|integer|min:1',
            'end_id' => 'required|integer|min:1|gte:start_id',
            'batch_size' => 'nullable|integer|min:10|max:1000',
        ]);

        // استفاده از مقادیر اعتبارسنجی شده به جای ورودی‌های خام
        $startId = $validated['start_id'];
        $endId = $validated['end_id'];
        $batchSize = $validated['batch_size'] ?? 50;

        $totalImages = PostImage::whereBetween('id', [$startId, $endId])->count();
        $processedCount = 0;
        $missingCount = 0;
        $errors = [];

        try {
            $currentId = $startId;
            Log::info('بررسی تصاویر آغاز شد', [
                'start_id' => $startId,
                'end_id' => $endId,
                'batch_size' => $batchSize,
                'total_images' => $totalImages
            ]);

            while ($currentId <= $endId) {
                $batchEndId = min($currentId + $batchSize - 1, $endId);
                $images = PostImage::whereBetween('id', [$currentId, $batchEndId])
                    ->select(['id', 'image_path', 'hide_image'])
                    ->get();

                foreach ($images as $image) {
                    $processedCount++;

                    // تصاویر با مسیر خالی یا قبلاً علامت‌گذاری شده به عنوان گمشده، پردازش نمی‌شوند
                    $imageUrl = $image->image_url ?? $image->image_path;
                    if (empty($imageUrl) || $image->hide_image === 'missing') {
                        $missingCount++;
                        if (empty($imageUrl)) {
                            $this->markImageAsMissing($image, "مسیر تصویر خالی است");
                        }
                        continue;
                    }

                    try {
                        // اعتبارسنجی URL تصویر قبل از درخواست
                        if (filter_var($imageUrl, FILTER_VALIDATE_URL) === false) {
                            $this->markImageAsMissing($image, "آدرس تصویر نامعتبر است: " . substr($imageUrl, 0, 100));
                            $missingCount++;
                            continue;
                        }

                        // استفاده از timeout و تعیین محدودیت تلاش‌ها
                        $response = Http::timeout(5)->retry(2, 1000)->head($imageUrl);

                        if ($response->status() !== 200) {
                            $this->markImageAsMissing($image, "کد پاسخ: " . $response->status());
                            $missingCount++;
                        }
                    } catch (\Exception $e) {
                        $this->markImageAsMissing($image, $e->getMessage());
                        $missingCount++;
                        $errors[] = "خطا در بررسی تصویر {$image->id}: " . $e->getMessage();

                        // لاگ کردن خطا با جزئیات کمتر (بدون URL کامل)
                        Log::error('خطا در بررسی تصویر', [
                            'image_id' => $image->id,
                            'error_type' => get_class($e),
                            'error_code' => $e->getCode(),
                            'error_message' => $e->getMessage()
                        ]);
                    }
                }

                $currentId = $batchEndId + 1;
                Log::info('پیشرفت بررسی تصاویر', [
                    'current_id' => $currentId,
                    'processed_count' => $processedCount,
                    'missing_count' => $missingCount
                ]);
            }

            $successMessage = "بررسی تصاویر به پایان رسید. {$processedCount} تصویر بررسی شد و {$missingCount} تصویر به عنوان گمشده علامت‌گذاری شد.";
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
            Log::error('خطا در بررسی تصاویر', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'processed_count' => $processedCount,
                'missing_count' => $missingCount
            ]);

            return redirect()->route('admin.images.checker')
                ->with('error', 'خطا در بررسی تصاویر: ' . $e->getMessage())
                ->with('processed_count', $processedCount)
                ->with('missing_count', $missingCount)
                ->with('errors', $errors);
        }
    }

    /**
     * علامت‌گذاری تصویر به عنوان گمشده
     *
     * @param PostImage $image
     * @param string $reason
     * @return bool
     */
    private function markImageAsMissing(PostImage $image, string $reason = ''): bool
    {
        try {
            $result = $image->update([
                'hide_image' => 'missing',
                'updated_at' => now()
            ]);

            Log::info('تصویر به عنوان گمشده علامت‌گذاری شد', [
                'image_id' => $image->id,
                'reason_type' => substr($reason, 0, 30) // فقط بخش ابتدایی دلیل برای جلوگیری از افشای اطلاعات
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('خطا در علامت‌گذاری تصویر به عنوان گمشده', [
                'image_id' => $image->id,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ]);

            return false;
        }
    }
}
