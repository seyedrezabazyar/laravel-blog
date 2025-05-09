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
                // حذف ایندکس FULLTEXT موجود اگر وجود داشته باشد
                DB::statement('ALTER TABLE posts DROP INDEX IF EXISTS posts_title_fulltext');
                DB::statement('ALTER TABLE posts DROP INDEX IF EXISTS posts_content_fulltext');
                DB::statement('ALTER TABLE posts DROP INDEX IF EXISTS posts_fulltext');

                // ایجاد ایندکس‌های بهینه‌شده
                // ایندکس اول: فقط عناوین (برای جستجوی سریع)
                DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX posts_title_fulltext_optimized (title, english_title) WITH PARSER ngram');

                // ایندکس دوم: همه فیلدها با وزن‌های متفاوت
                DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX posts_fulltext_optimized (title, english_title, book_codes, content, english_content) WITH PARSER ngram');
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
            DB::statement('ALTER TABLE posts DROP INDEX IF EXISTS posts_title_fulltext_optimized');
            DB::statement('ALTER TABLE posts DROP INDEX IF EXISTS posts_fulltext_optimized');
        }
    }
};
