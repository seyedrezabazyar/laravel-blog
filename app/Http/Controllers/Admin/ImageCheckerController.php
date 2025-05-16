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
        $request->validate([
            'start_id' => 'required|integer|min:1',
            'end_id' => 'required|integer|min:1|gte:start_id',
            'batch_size' => 'nullable|integer|min:10|max:1000',
        ]);

        $startId = $request->input('start_id');
        $endId = $request->input('end_id');
        $batchSize = $request->input('batch_size', 50);
        $totalImages = PostImage::whereBetween('id', [$startId, $endId])->count();
        $processedCount = 0;
        $missingCount = 0;
        $errors = [];

        try {
            $currentId = $startId;
            Log::info('بررسی تصاویر آغاز شد', compact('startId', 'endId', 'batchSize', 'totalImages'));

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
                        $response = Http::timeout(5)->head($imageUrl);
                        if ($response->status() !== 200) {
                            $this->markImageAsMissing($image, "کد پاسخ: " . $response->status());
                            $missingCount++;
                        }
                    } catch (\Exception $e) {
                        $this->markImageAsMissing($image, $e->getMessage());
                        $missingCount++;
                        $errors[] = "خطا در بررسی تصویر {$image->id}: " . $e->getMessage();
                    }
                }

                $currentId = $batchEndId + 1;
                Log::info('پیشرفت بررسی تصاویر', compact('currentId', 'processedCount', 'missingCount'));
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
                'error' => $e->getMessage(),
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

    private function markImageAsMissing(PostImage $image, string $reason = ''): bool
    {
        try {
            $result = $image->update([
                'hide_image' => 'missing',
                'updated_at' => now()
            ]);

            Log::info('تصویر به عنوان گمشده علامت‌گذاری شد', [
                'image_id' => $image->id,
                'reason' => $reason
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('خطا در علامت‌گذاری تصویر به عنوان گمشده', [
                'image_id' => $image->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
