<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\PostImage;
use App\Services\ImageUrlService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateImagePaths extends Command
{
    protected $signature = 'images:migrate-paths {--chunk=1000} {--dry-run} {--validate}';
    protected $description = 'انتقال آدرس‌های تصاویر از فیلد image_path به سیستم محاسبه‌ای جدید';

    private $processedCount = 0;
    private $errorCount = 0;
    private $missingMd5Count = 0;
    private $validatedCount = 0;

    public function handle()
    {
        $chunkSize = (int) $this->option('chunk');
        $dryRun = $this->option('dry-run');
        $validate = $this->option('validate');

        $this->info('شروع فرآیند انتقال آدرس‌های تصاویر...');
        $this->info("اندازه chunk: {$chunkSize}");

        if ($dryRun) {
            $this->warn('حالت DRY RUN - هیچ تغییری اعمال نمی‌شود');
        }

        if ($validate) {
            $this->validateImagePaths($chunkSize);
            return 0;
        }

        // بررسی وجود فیلد image_path
        if (!$this->checkImagePathFieldExists()) {
            $this->error('فیلد image_path در جدول post_images وجود ندارد!');
            return 1;
        }

        // شمارش کل رکوردها
        $totalImages = PostImage::whereNotNull('image_path')->count();
        $this->info("تعداد کل تصاویر: {$totalImages}");

        if ($totalImages === 0) {
            $this->info('هیچ تصویری برای پردازش یافت نشد.');
            return 0;
        }

        $bar = $this->output->createProgressBar($totalImages);
        $bar->start();

        // ابتدا md5 پست‌هایی که ندارند را تولید کنیم
        $this->generateMissingMd5Hashes();

        // پردازش تصاویر به صورت chunk
        PostImage::whereNotNull('image_path')
            ->with(['post:id,md5,title'])
            ->chunk($chunkSize, function ($images) use ($dryRun, $bar) {
                $this->processImageChunk($images, $dryRun);
                $bar->advance($images->count());
            });

        $bar->finish();
        $this->newLine();

        // نمایش خلاصه نتایج
        $this->displayResults();

        return 0;
    }

    /**
     * بررسی وجود فیلد image_path
     */
    private function checkImagePathFieldExists(): bool
    {
        try {
            DB::select("SHOW COLUMNS FROM post_images LIKE 'image_path'");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * تولید md5 برای پست‌هایی که ندارند
     */
    private function generateMissingMd5Hashes()
    {
        $this->info('بررسی و تولید md5 برای پست‌های فاقد آن...');

        $postsWithoutMd5 = Post::whereNull('md5')->orWhere('md5', '')->count();

        if ($postsWithoutMd5 > 0) {
            $this->warn("تعداد {$postsWithoutMd5} پست فاقد md5 یافت شد. در حال تولید...");

            Post::whereNull('md5')->orWhere('md5', '')->chunk(1000, function ($posts) {
                foreach ($posts as $post) {
                    $md5 = md5($post->title . $post->id . microtime());
                    DB::table('posts')->where('id', $post->id)->update(['md5' => $md5]);
                }
            });

            $this->info('md5 های فاقد تولید شدند.');
        }
    }

    /**
     * پردازش یک chunk از تصاویر
     */
    private function processImageChunk($images, $dryRun)
    {
        foreach ($images as $image) {
            try {
                if (!$image->post || !$image->post->md5) {
                    $this->missingMd5Count++;
                    Log::warning('پست فاقد md5', [
                        'image_id' => $image->id,
                        'post_id' => $image->post_id
                    ]);
                    continue;
                }

                // استخراج اطلاعات از آدرس قدیمی
                $oldPathInfo = ImageUrlService::parseOldImagePath($image->image_path);

                if (!$oldPathInfo) {
                    $this->errorCount++;
                    Log::error('آدرس تصویر قدیمی قابل پردازش نیست', [
                        'image_id' => $image->id,
                        'image_path' => $image->image_path
                    ]);
                    continue;
                }

                // محاسبه آدرس جدید
                $newUrl = ImageUrlService::generateImageUrl(
                    $image->post->id,
                    $image->post->md5
                );

                // بررسی تطابق md5
                if ($oldPathInfo['md5'] !== $image->post->md5) {
                    $this->warn("عدم تطابق md5 برای تصویر {$image->id}");
                    Log::warning('عدم تطابق md5', [
                        'image_id' => $image->id,
                        'old_md5' => $oldPathInfo['md5'],
                        'new_md5' => $image->post->md5
                    ]);
                }

                // ثبت نتیجه در لاگ
                Log::info('تبدیل آدرس تصویر', [
                    'image_id' => $image->id,
                    'post_id' => $image->post_id,
                    'old_path' => $image->image_path,
                    'new_url' => $newUrl,
                    'dry_run' => $dryRun
                ]);

                $this->processedCount++;

            } catch (\Exception $e) {
                $this->errorCount++;
                Log::error('خطا در پردازش تصویر', [
                    'image_id' => $image->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * اعتبارسنجی آدرس‌های تصاویر
     */
    private function validateImagePaths($chunkSize)
    {
        $this->info('شروع اعتبارسنجی آدرس‌های تصاویر...');

        $totalImages = PostImage::count();
        $bar = $this->output->createProgressBar($totalImages);
        $bar->start();

        PostImage::with(['post:id,md5,title'])
            ->chunk($chunkSize, function ($images) use ($bar) {
                foreach ($images as $image) {
                    $this->validateSingleImage($image);
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();

        $this->info("اعتبارسنجی کامل شد. {$this->validatedCount} تصویر بررسی شد.");
    }

    /**
     * اعتبارسنجی یک تصویر
     */
    private function validateSingleImage($image)
    {
        try {
            if (!$image->post || !$image->post->md5) {
                $this->missingMd5Count++;
                return;
            }

            $expectedUrl = ImageUrlService::generateImageUrl($image->post->id, $image->post->md5);
            $actualUrl = $image->image_url;

            if ($expectedUrl !== $actualUrl) {
                Log::warning('عدم تطابق آدرس تصویر', [
                    'image_id' => $image->id,
                    'expected' => $expectedUrl,
                    'actual' => $actualUrl
                ]);
                $this->errorCount++;
            }

            $this->validatedCount++;

        } catch (\Exception $e) {
            $this->errorCount++;
            Log::error('خطا در اعتبارسنجی تصویر', [
                'image_id' => $image->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * نمایش خلاصه نتایج
     */
    private function displayResults()
    {
        $this->newLine();
        $this->info('=== خلاصه نتایج ===');
        $this->info("پردازش شده: {$this->processedCount}");
        $this->info("خطاها: {$this->errorCount}");
        $this->info("فاقد md5: {$this->missingMd5Count}");

        if ($this->validatedCount > 0) {
            $this->info("اعتبارسنجی شده: {$this->validatedCount}");
        }

        if ($this->errorCount > 0) {
            $this->warn('برای مشاهده جزئیات خطاها، فایل لاگ را بررسی کنید.');
        }

        // نمایش آمار سیستم تصاویر
        $stats = ImageUrlService::getImageStats();
        $this->newLine();
        $this->info('=== آمار سیستم تصاویر ===');
        $this->info("حداکثر post_id: {$stats['max_post_id']}");
        $this->info("تعداد دایرکتوری‌ها: {$stats['total_directories']}");
        $this->info("تصاویر در هر دایرکتوری: {$stats['images_per_directory']}");
    }
}
