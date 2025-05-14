<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * بهینه‌سازی ایندکس‌های جستجو و عملکرد
     */
    public function up(): void
    {
        // فقط برای MySQL اجرا می‌شود
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                // بخش 1: حذف ایندکس‌های FULLTEXT موجود اگر وجود داشته باشند
                $this->dropIndexIfExists('posts', 'posts_title_fulltext');
                $this->dropIndexIfExists('posts', 'posts_content_fulltext');
                $this->dropIndexIfExists('posts', 'posts_fulltext');
                $this->dropIndexIfExists('posts', 'posts_title_fulltext_optimized');
                $this->dropIndexIfExists('posts', 'posts_fulltext_optimized');

                // بخش 2: ایجاد ایندکس‌های FULLTEXT بهینه‌سازی شده
                // ایندکس اول: فقط ستون title (برای سازگاری با متد scopeFullTextSearch)
                DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX posts_title_fulltext (title)');

                // ایندکس دوم: همه فیلدهای متنی برای جستجوی کامل
                DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX posts_fulltext (title, english_title, book_codes, content, english_content)');

                // بخش 3: ایندکس‌های جدید برای بهبود عملکرد
                // ایندکس ترکیبی برای نمایش پست‌های یک دسته‌بندی
                $this->addIndexIfNotExists('posts', 'idx_posts_category_visibility', '(category_id, is_published, hide_content)');

                // ایندکس ترکیبی برای نمایش پست‌های یک نویسنده
                $this->addIndexIfNotExists('posts', 'idx_posts_author_visibility', '(author_id, is_published, hide_content)');

                // ایندکس ترکیبی برای نمایش پست‌های یک ناشر
                $this->addIndexIfNotExists('posts', 'idx_posts_publisher_visibility', '(publisher_id, is_published, hide_content)');

                // ایندکس برای مرتب‌سازی بر اساس تاریخ ایجاد
                $this->addIndexIfNotExists('posts', 'idx_posts_created', '(created_at)');

                // ایندکس روی post_images برای بهبود عملکرد تصاویر پست
                $this->addIndexIfNotExists('post_images', 'idx_post_images_post_sort', '(post_id, sort_order)');

            } catch (\Exception $e) {
                // خطا را ثبت کنید و ادامه دهید
                \Log::error('Error optimizing indexes: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            // حذف ایندکس‌های FULLTEXT
            $this->dropIndexIfExists('posts', 'posts_title_fulltext');
            $this->dropIndexIfExists('posts', 'posts_fulltext');

            // حذف ایندکس‌های جدید
            $this->dropIndexIfExists('posts', 'idx_posts_category_visibility');
            $this->dropIndexIfExists('posts', 'idx_posts_author_visibility');
            $this->dropIndexIfExists('posts', 'idx_posts_publisher_visibility');
            $this->dropIndexIfExists('posts', 'idx_posts_created');
            $this->dropIndexIfExists('post_images', 'idx_post_images_post_sort');
        }
    }

    /**
     * حذف ایندکس اگر وجود داشته باشد - سازگار با نسخه‌های مختلف MySQL
     */
    private function dropIndexIfExists($table, $indexName)
    {
        // بررسی وجود ایندکس قبل از حذف آن
        $indexExists = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$indexName}'");

        if (!empty($indexExists)) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
            \Log::info("Index {$indexName} dropped from table {$table}");
        }
    }

    /**
     * اضافه کردن ایندکس اگر وجود نداشته باشد
     */
    private function addIndexIfNotExists($table, $indexName, $columns)
    {
        // بررسی وجود ایندکس قبل از ایجاد آن
        $indexExists = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$indexName}'");

        if (empty($indexExists)) {
            DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` {$columns}");
            \Log::info("Index {$indexName} added to table {$table}");
        }
    }
};
