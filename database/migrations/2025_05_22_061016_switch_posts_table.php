<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        Log::info('شروع تعویض جداول...');

        try {
            // بررسی وجود جداول
            $postsExists = DB::select("SHOW TABLES LIKE 'posts'");
            $postsNewExists = DB::select("SHOW TABLES LIKE 'posts_new'");

            if (empty($postsExists)) {
                throw new \Exception('Table posts does not exist');
            }

            if (empty($postsNewExists)) {
                throw new \Exception('Table posts_new does not exist');
            }

            // تعداد رکوردها برای مقایسه
            $oldCount = DB::table('posts')->count();
            $newCount = DB::table('posts_new')->count();

            Log::info("Posts count - Old: {$oldCount}, New: {$newCount}");

            // تعویض جداول
            DB::statement('RENAME TABLE posts TO posts_backup');
            Log::info('Table posts renamed to posts_backup');

            DB::statement('RENAME TABLE posts_new TO posts');
            Log::info('Table posts_new renamed to posts');

            // بهینه‌سازی جدول جدید
            DB::statement('OPTIMIZE TABLE posts');
            Log::info('Table posts optimized');

            $this->logResults($oldCount, $newCount);

        } catch (\Exception $e) {
            Log::error('Error during table switch: ' . $e->getMessage());
            throw $e;
        }
    }

    private function logResults($oldCount, $newCount)
    {
        try {
            // محاسبه حجم جداول
            $dbName = DB::connection()->getDatabaseName();

            $sizes = DB::select("
                SELECT
                    table_name,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb'
                FROM information_schema.tables
                WHERE table_schema = '{$dbName}'
                AND table_name IN ('posts', 'posts_backup')
            ");

            $newSize = 0;
            $oldSize = 0;

            foreach ($sizes as $size) {
                if ($size->table_name === 'posts') {
                    $newSize = $size->size_mb;
                } elseif ($size->table_name === 'posts_backup') {
                    $oldSize = $size->size_mb;
                }
            }

            // محاسبه تعداد فایل‌ها
            $contentFiles = count(\Storage::files('posts/content'));
            $englishFiles = count(\Storage::files('posts/content_en'));

            Log::info('=== نتایج تعویض جداول ===');
            Log::info("✅ تعداد پست‌ها: {$oldCount} -> {$newCount}");
            Log::info("📊 حجم جدول posts: {$oldSize} MB -> {$newSize} MB");
            Log::info("📁 فایل‌های فارسی: {$contentFiles}");
            Log::info("📁 فایل‌های انگلیسی: {$englishFiles}");

            if ($oldSize > 0) {
                $reduction = round((1 - $newSize / $oldSize) * 100, 1);
                Log::info("🗜️ کاهش حجم دیتابیس: {$reduction}%");
            }

        } catch (\Exception $e) {
            Log::warning('Could not calculate results: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        Log::info('شروع rollback تعویض جداول...');

        try {
            // بازگردانی نام جداول
            DB::statement('RENAME TABLE posts TO posts_new');
            DB::statement('RENAME TABLE posts_backup TO posts');

            Log::info('Tables reverted successfully');

        } catch (\Exception $e) {
            Log::error('Error during table revert: ' . $e->getMessage());
            throw $e;
        }
    }
};
