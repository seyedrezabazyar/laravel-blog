<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EmergencyImageFix extends Command
{
    protected $signature = 'emergency:fix-images';
    protected $description = 'اصلاح اضطراری مشکل نمایش تصاویر';

    public function handle()
    {
        $this->info('🚨 شروع اصلاح اضطراری تصاویر...');

        // مرحله 1: بررسی و تولید MD5 برای پست‌های فاقد آن
        $this->fixMissingMd5();

        // مرحله 2: تست نمایش تصاویر
        $this->testImageUrls();

        // مرحله 3: اطمینان از وجود رکوردهای PostImage
        $this->ensurePostImageRecords();

        $this->info('✅ اصلاح اضطراری تکمیل شد!');
    }

    private function fixMissingMd5()
    {
        $this->info('🔧 اصلاح MD5های گمشده...');

        $postsWithoutMd5 = Post::where(function($q) {
            $q->whereNull('md5')->orWhere('md5', '');
        })->count();

        if ($postsWithoutMd5 > 0) {
            $this->warn("⚠️  {$postsWithoutMd5} پست فاقد MD5 یافت شد!");

            $bar = $this->output->createProgressBar($postsWithoutMd5);
            $bar->start();

            Post::where(function($q) {
                $q->whereNull('md5')->orWhere('md5', '');
            })->chunk(100, function($posts) use ($bar) {
                foreach ($posts as $post) {
                    $md5 = md5($post->title . $post->id . microtime() . uniqid());
                    DB::table('posts')->where('id', $post->id)->update(['md5' => $md5]);
                    $bar->advance();
                }
            });

            $bar->finish();
            $this->newLine();
            $this->info("✅ MD5 برای {$postsWithoutMd5} پست تولید شد");
        } else {
            $this->info('✅ همه پست‌ها دارای MD5 معتبر هستند');
        }
    }

    private function testImageUrls()
    {
        $this->info('🖼️  تست URLهای تصاویر...');

        $testPosts = Post::whereNotNull('md5')->limit(3)->get();

        foreach ($testPosts as $post) {
            $directory = intval(($post->id - 1) / 10000) * 10000;
            $imageHost = config('app.custom_image_host', 'https://images.balyan.ir');
            $imageUrl = "{$imageHost}/{$directory}/{$post->md5}.jpg";

            $this->line("پست #{$post->id}: {$post->title}");
            $this->line("  MD5: {$post->md5}");
            $this->line("  دایرکتوری: {$directory}");
            $this->line("  URL: {$imageUrl}");

            // تست دسترسی
            try {
                $headers = @get_headers($imageUrl, true);
                if ($headers && strpos($headers[0], '200') !== false) {
                    $this->info("  ✅ تصویر در دسترس است");
                } else {
                    $this->error("  ❌ تصویر در دسترس نیست");
                }
            } catch (\Exception $e) {
                $this->error("  ❌ خطا در تست: " . $e->getMessage());
            }

            $this->newLine();
        }
    }

    private function ensurePostImageRecords()
    {
        $this->info('📸 اطمینان از وجود رکوردهای PostImage...');

        $result = DB::statement("
            INSERT INTO post_images (post_id, status, created_at, updated_at)
            SELECT p.id, 'visible', NOW(), NOW()
            FROM posts p
            WHERE NOT EXISTS (
                SELECT 1 FROM post_images pi WHERE pi.post_id = p.id
            )
        ");

        $createdCount = DB::table('post_images')->count();
        $this->info("✅ تعداد رکوردهای PostImage: {$createdCount}");
    }
}
