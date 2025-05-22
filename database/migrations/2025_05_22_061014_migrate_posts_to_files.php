<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    private $contentDir = 'posts/content';
    private $englishContentDir = 'posts/content_en';

    public function up(): void
    {
        // ایجاد دایرکتوری‌ها
        Storage::makeDirectory($this->contentDir);
        Storage::makeDirectory($this->englishContentDir);

        // استفاده از Log بجای command
        Log::info('شروع انتقال داده‌ها به فایل...');
        $this->migratePostsToFiles();
        Log::info('انتقال داده‌ها تکمیل شد!');
    }

    private function migratePostsToFiles()
    {
        $chunkSize = 200;
        $totalPosts = DB::table('posts')->count();
        $processed = 0;

        Log::info("شروع انتقال {$totalPosts} پست به فایل...");

        if ($totalPosts === 0) {
            Log::info('هیچ پستی برای انتقال وجود ندارد.');
            return;
        }

        DB::table('posts')->orderBy('id')->chunk($chunkSize, function($posts) use (&$processed) {
            $postsData = [];

            foreach ($posts as $post) {
                $contentFilePath = null;
                $englishContentFilePath = null;

                // ذخیره محتوای فارسی در فایل
                if (!empty($post->content)) {
                    $contentFilePath = $this->saveContentToFile(
                        $post->id,
                        $post->content,
                        $this->contentDir,
                        'fa'
                    );
                }

                // ذخیره محتوای انگلیسی در فایل
                if (!empty($post->english_content)) {
                    $englishContentFilePath = $this->saveContentToFile(
                        $post->id,
                        $post->english_content,
                        $this->englishContentDir,
                        'en'
                    );
                }

                // آماده‌سازی داده‌های جدول
                $postsData[] = [
                    'id' => $post->id,
                    'md5_hash' => $post->md5_hash,
                    'user_id' => $post->user_id,
                    'category_id' => $post->category_id,
                    'author_id' => $post->author_id,
                    'publisher_id' => $post->publisher_id,
                    'title' => $post->title,
                    'english_title' => $post->english_title,
                    'slug' => $post->slug,
                    'language' => $post->language ?? 'fa',
                    'publication_year' => $post->publication_year,
                    'format' => $post->format,
                    'book_codes' => $post->book_codes,
                    'purchase_link' => $post->purchase_link,
                    'summary' => $this->generateSummary($post->content),
                    'english_summary' => $this->generateSummary($post->english_content),
                    'content_file_path' => $contentFilePath,
                    'english_content_file_path' => $englishContentFilePath,
                    'hide_content' => $post->hide_content ?? false,
                    'is_published' => $post->is_published ?? false,
                    'created_at' => $post->created_at,
                    'updated_at' => $post->updated_at,
                ];

                $processed++;

                // پاکسازی حافظه هر 50 پست
                if ($processed % 50 === 0) {
                    gc_collect_cycles();
                }
            }

            try {
                // درج دسته‌ای
                if (!empty($postsData)) {
                    DB::table('posts_new')->insert($postsData);
                }

                Log::info("پردازش شد: {$processed} پست");

            } catch (\Exception $e) {
                Log::error("خطا در انتقال chunk: " . $e->getMessage());
                throw $e;
            }
        });

        Log::info("انتقال {$processed} پست تکمیل شد.");
    }

    private function saveContentToFile($postId, $content, $directory, $language)
    {
        if (empty($content)) {
            return null;
        }

        try {
            // ایجاد نام فایل با hash برای جلوگیری از تکرار
            $hash = md5($content);
            $fileName = "{$postId}_{$hash}.txt";
            $filePath = "{$directory}/{$fileName}";

            // فشرده‌سازی محتوا قبل از ذخیره
            $compressedContent = gzcompress($content, 9);

            // ذخیره فایل
            Storage::put($filePath, $compressedContent);

            // لاگ موفقیت‌آمیز
            Log::debug("Content file saved", [
                'post_id' => $postId,
                'language' => $language,
                'file_name' => $fileName,
                'original_size' => strlen($content),
                'compressed_size' => strlen($compressedContent),
                'compression_ratio' => round((1 - strlen($compressedContent) / strlen($content)) * 100, 1) . '%'
            ]);

            return $fileName;

        } catch (\Exception $e) {
            Log::error("خطا در ذخیره فایل محتوا برای پست {$postId}: " . $e->getMessage());
            return null;
        }
    }

    private function generateSummary($content)
    {
        if (empty($content)) {
            return null;
        }

        // پاکسازی HTML tags
        $cleanContent = strip_tags($content);

        // حذف فاصله‌های اضافی
        $cleanContent = preg_replace('/\s+/', ' ', $cleanContent);

        // برش به 250 کاراکتر
        return mb_substr(trim($cleanContent), 0, 250, 'UTF-8');
    }

    public function down(): void
    {
        // حذف فایل‌ها و داده‌ها
        try {
            // حذف دایرکتوری‌های محتوا
            Storage::deleteDirectory($this->contentDir);
            Storage::deleteDirectory($this->englishContentDir);

            // پاک کردن جدول جدید
            DB::table('posts_new')->truncate();

            Log::info('Rollback completed: files and data removed');

        } catch (\Exception $e) {
            Log::error('Error during rollback: ' . $e->getMessage());
        }
    }
};
