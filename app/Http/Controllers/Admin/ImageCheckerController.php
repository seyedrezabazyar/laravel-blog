<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostImage;
use App\Services\ImageUrlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageCheckerController extends Controller
{
    public function index()
    {
        $stats = ImageUrlService::getImageStats();
        return view('admin.images.checker', compact('stats'));
    }

    public function check(Request $request)
    {
        // اعتبارسنجی ورودی‌ها
        $validated = $request->validate([
            'start_id' => 'required|integer|min:1',
            'end_id' => 'required|integer|min:1|gte:start_id',
            'batch_size' => 'nullable|integer|min:10|max:1000',
        ]);

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
                    ->with(['post:id,md5,title'])
                    ->select(['id', 'post_id', 'status'])
                    ->get();

                foreach ($images as $image) {
                    $processedCount++;

                    // تصاویر با وضعیت مخفی یا فاقد پست، پردازش نمی‌شوند
                    if ($image->status === 'hidden' || !$image->post || !$image->post->md5) {
                        if (!$image->post || !$image->post->md5) {
                            $this->markImageAsMissing($image, "پست فاقد md5 است");
                            $missingCount++;
                        }
                        continue;
                    }

                    try {
                        // تولید آدرس تصویر با فرمول جدید
                        $imageUrl = ImageUrlService::generateImageUrl($image->post->id, $image->post->md5);

                        // اعتبارسنجی URL تصویر
                        if (filter_var($imageUrl, FILTER_VALIDATE_URL) === false) {
                            $this->markImageAsMissing($image, "آدرس تصویر نامعتبر است");
                            $missingCount++;
                            continue;
                        }

                        // بررسی وجود تصویر
                        $response = Http::timeout(5)->retry(2, 1000)->head($imageUrl);

                        if ($response->status() !== 200) {
                            $this->markImageAsMissing($image, "کد پاسخ: " . $response->status());
                            $missingCount++;
                        } else {
                            // اگر تصویر موجود باشد و قبلاً missing بوده، آن را visible کنیم
                            if ($image->status === 'missing') {
                                $image->update(['status' => 'visible']);
                                Log::info('تصویر گمشده یافت شد و به visible تغییر یافت', [
                                    'image_id' => $image->id,
                                    'url' => $imageUrl
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        $this->markImageAsMissing($image, $e->getMessage());
                        $missingCount++;
                        $errors[] = "خطا در بررسی تصویر {$image->id}: " . $e->getMessage();

                        Log::error('خطا در بررسی تصویر', [
                            'image_id' => $image->id,
                            'post_id' => $image->post_id,
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
                ->with('errors', array_slice($errors, 0, 10)); // فقط ۱۰ خطای اول

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
     * بررسی تصاویر بر اساس محدوده post_id
     */
    public function checkByPostRange(Request $request)
    {
        $validated = $request->validate([
            'start_post_id' => 'required|integer|min:1',
            'end_post_id' => 'required|integer|min:1|gte:start_post_id',
            'batch_size' => 'nullable|integer|min:10|max:500',
        ]);

        $startPostId = $validated['start_post_id'];
        $endPostId = $validated['end_post_id'];
        $batchSize = $validated['batch_size'] ?? 100;

        $processedCount = 0;
        $missingCount = 0;
        $errors = [];

        try {
            $images = PostImage::whereHas('post', function($query) use ($startPostId, $endPostId) {
                $query->whereBetween('id', [$startPostId, $endPostId]);
            })
                ->with(['post:id,md5,title'])
                ->get();

            foreach ($images as $image) {
                if (!$image->post || !$image->post->md5) {
                    $this->markImageAsMissing($image, "پست فاقد md5");
                    $missingCount++;
                    continue;
                }

                try {
                    $imageUrl = ImageUrlService::generateImageUrl($image->post->id, $image->post->md5);
                    $exists = ImageUrlService::imageExists($imageUrl);

                    if (!$exists) {
                        $this->markImageAsMissing($image, "تصویر در سرور موجود نیست");
                        $missingCount++;
                    } elseif ($image->status === 'missing') {
                        $image->update(['status' => 'visible']);
                    }

                    $processedCount++;

                } catch (\Exception $e) {
                    $errors[] = "خطا در بررسی تصویر {$image->id}: " . $e->getMessage();
                    $missingCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "بررسی انجام شد. {$processedCount} تصویر بررسی و {$missingCount} تصویر گمشده یافت شد.",
                'processed_count' => $processedCount,
                'missing_count' => $missingCount,
                'errors' => array_slice($errors, 0, 5)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بررسی: ' . $e->getMessage(),
                'processed_count' => $processedCount,
                'missing_count' => $missingCount
            ], 500);
        }
    }

    /**
     * تولید گزارش آمار تصاویر
     */
    public function generateReport()
    {
        $stats = [
            'total_images' => PostImage::count(),
            'visible_images' => PostImage::where('status', 'visible')->count(),
            'hidden_images' => PostImage::where('status', 'hidden')->count(),
            'missing_images' => PostImage::where('status', 'missing')->count(),
            'pending_images' => PostImage::whereNull('status')->count(),
        ];

        $directoryStats = ImageUrlService::getImageStats();

        $sampleImages = PostImage::with(['post:id,md5,title'])
            ->limit(5)
            ->get()
            ->map(function($image) {
                return [
                    'id' => $image->id,
                    'post_id' => $image->post_id,
                    'status' => $image->status,
                    'calculated_url' => $image->image_url,
                    'debug_info' => $image->getImageDebugInfo()
                ];
            });

        return response()->json([
            'database_stats' => $stats,
            'directory_stats' => $directoryStats,
            'sample_images' => $sampleImages,
            'system_info' => [
                'image_host' => config('app.custom_image_host', 'https://images.balyan.ir'),
                'cache_enabled' => config('cache.default') !== 'array',
                'migration_completed' => !$this->hasImagePathField(),
            ]
        ]);
    }

    /**
     * بررسی وجود فیلد image_path
     */
    private function hasImagePathField(): bool
    {
        try {
            \Schema::hasColumn('post_images', 'image_path');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * علامت‌گذاری تصویر به عنوان گمشده
     */
    private function markImageAsMissing(PostImage $image, string $reason = ''): bool
    {
        try {
            $result = $image->update([
                'status' => 'missing',
                'updated_at' => now()
            ]);

            // پاک کردن کش تصویر
            $image->clearImageCache();

            Log::info('تصویر به عنوان گمشده علامت‌گذاری شد', [
                'image_id' => $image->id,
                'post_id' => $image->post_id,
                'reason_type' => substr($reason, 0, 30)
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('خطا در علامت‌گذاری تصویر به عنوان گمشده', [
                'image_id' => $image->id,
                'post_id' => $image->post_id,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ]);

            return false;
        }
    }

    /**
     * تست تولید آدرس تصویر برای یک پست خاص
     */
    public function testImageGeneration(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|integer|exists:posts,id'
        ]);

        $postId = $validated['post_id'];

        try {
            $post = \App\Models\Post::select(['id', 'md5', 'title'])->find($postId);

            if (!$post->md5) {
                return response()->json([
                    'success' => false,
                    'message' => 'این پست فاقد md5 است'
                ], 400);
            }

            $generatedUrl = ImageUrlService::generateImageUrl($post->id, $post->md5);
            $directory = ImageUrlService::calculateDirectory($post->id);
            $exists = ImageUrlService::imageExists($generatedUrl);

            $responsiveUrls = ImageUrlService::getResponsiveImageUrls($post->id, $post->md5);

            return response()->json([
                'success' => true,
                'post_info' => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'md5' => $post->md5
                ],
                'calculated_directory' => $directory,
                'generated_url' => $generatedUrl,
                'image_exists' => $exists,
                'responsive_urls' => $responsiveUrls,
                'image_record' => $post->featuredImage ? [
                    'id' => $post->featuredImage->id,
                    'status' => $post->featuredImage->status,
                    'display_url' => $post->featuredImage->display_url
                ] : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در تست: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تولید md5 برای پست‌های فاقد آن
     */
    public function generateMissingMd5()
    {
        try {
            $postsWithoutMd5 = \App\Models\Post::where(function($query) {
                $query->whereNull('md5')->orWhere('md5', '');
            })->count();

            if ($postsWithoutMd5 === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'همه پست‌ها دارای md5 هستند',
                    'updated_count' => 0
                ]);
            }

            $updatedCount = 0;
            \App\Models\Post::where(function($query) {
                $query->whereNull('md5')->orWhere('md5', '');
            })->chunk(1000, function($posts) use (&$updatedCount) {
                foreach ($posts as $post) {
                    $md5 = md5($post->title . $post->id . microtime() . uniqid());
                    \DB::table('posts')->where('id', $post->id)->update(['md5' => $md5]);
                    $updatedCount++;
                }
            });

            Log::info('md5 های فاقد تولید شدند', [
                'updated_count' => $updatedCount
            ]);

            return response()->json([
                'success' => true,
                'message' => "md5 برای {$updatedCount} پست تولید شد",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            Log::error('خطا در تولید md5: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'خطا در تولید md5: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * پاک کردن کش تصاویر
     */
    public function clearImageCache(Request $request)
    {
        try {
            $type = $request->input('type', 'all'); // all, specific_post, specific_image
            $cleared = 0;

            switch ($type) {
                case 'specific_post':
                    $postId = $request->input('post_id');
                    if ($postId) {
                        $post = \App\Models\Post::find($postId);
                        if ($post && $post->md5) {
                            ImageUrlService::clearImageCache($post->id, $post->md5);
                            $post->clearCache();
                            $cleared = 1;
                        }
                    }
                    break;

                case 'specific_image':
                    $imageId = $request->input('image_id');
                    if ($imageId) {
                        $image = PostImage::find($imageId);
                        if ($image) {
                            $image->clearImageCache();
                            $cleared = 1;
                        }
                    }
                    break;

                case 'all':
                default:
                    // پاک کردن کش همه تصاویر (با احتیاط)
                    \Cache::flush();
                    $cleared = 'all';
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'کش تصاویر پاک شد',
                'cleared_count' => $cleared
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در پاک کردن کش: ' . $e->getMessage()
            ], 500);
        }
    }
}
