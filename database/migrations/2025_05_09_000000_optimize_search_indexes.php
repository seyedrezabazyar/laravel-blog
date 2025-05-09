<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * بهینه‌سازی ایندکس‌های جستجو
     */
    public function up(): void
    {
        // برای MySQL، ایندکس FULLTEXT با اولویت بالاتر برای عناوین
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                // حذف ایندکس‌های FULLTEXT موجود اگر وجود داشته باشند (با روش سازگار با نسخه‌های قدیمی‌تر MySQL)
                $this->dropIndexIfExists('posts', 'posts_title_fulltext');
                $this->dropIndexIfExists('posts', 'posts_content_fulltext');
                $this->dropIndexIfExists('posts', 'posts_fulltext');
                $this->dropIndexIfExists('posts', 'posts_title_fulltext_optimized');
                $this->dropIndexIfExists('posts', 'posts_fulltext_optimized');

                // ایجاد ایندکس‌های بهینه‌شده
                // ایندکس اول: فقط عناوین (برای جستجوی سریع)
                DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX posts_title_fulltext (title, english_title)');

                // ایندکس دوم: همه فیلدها
                DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX posts_fulltext (title, english_title, book_codes, content, english_content)');
            } catch (\Exception $e) {
                // خطا را ثبت کنید و ادامه دهید
                \Log::error('Error optimizing search indexes: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            $this->dropIndexIfExists('posts', 'posts_title_fulltext');
            $this->dropIndexIfExists('posts', 'posts_fulltext');
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
        }
    }
};
