<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * اضافه کردن ایندکس‌های عملکردی برای بهبود سرعت صفحه ویرایش پست
     */
    public function up(): void
    {
        // افزودن ایندکس‌های کارآمد به جدول post_tag برای بهبود عملکرد
        if (!$this->hasIndex('post_tag', 'post_tag_retrieval_idx')) {
            Schema::table('post_tag', function (Blueprint $table) {
                $table->index(['post_id', 'tag_id'], 'post_tag_retrieval_idx');
            });
        }

        // اطمینان از وجود ایندکس‌های موردنیاز در جدول tags
        if (!$this->hasIndex('tags', 'tags_name_search_idx')) {
            Schema::table('tags', function (Blueprint $table) {
                $table->index(['name'], 'tags_name_search_idx');
            });
        }

        // ایندکس FULLTEXT برای جستجوی متنی (فقط برای MySQL)
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                if (!$this->hasFullTextIndex('tags', 'tags_name_fulltext')) {
                    DB::statement('ALTER TABLE tags ADD FULLTEXT INDEX tags_name_fulltext (name)');
                }

                // اجرای آنالیز جداول برای بهبود عملکرد
                DB::statement('ANALYZE TABLE post_tag, tags');
            } catch (\Exception $e) {
                \Log::error('Error creating fulltext index: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف ایندکس‌های اضافه شده
        try {
            Schema::table('post_tag', function (Blueprint $table) {
                $table->dropIndex('post_tag_retrieval_idx');
            });
        } catch (\Exception $e) {
            \Log::info('Index post_tag_retrieval_idx may not exist: ' . $e->getMessage());
        }

        try {
            Schema::table('tags', function (Blueprint $table) {
                $table->dropIndex('tags_name_search_idx');
            });
        } catch (\Exception $e) {
            \Log::info('Index tags_name_search_idx may not exist: ' . $e->getMessage());
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE tags DROP INDEX tags_name_fulltext');
            } catch (\Exception $e) {
                \Log::info('Fulltext index may not exist: ' . $e->getMessage());
            }
        }
    }

    /**
     * بررسی وجود ایندکس در جدول
     */
    private function hasIndex($table, $index)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$index}'");
        return !empty($indexes);
    }

    /**
     * بررسی وجود ایندکس FULLTEXT در جدول
     */
    private function hasFullTextIndex($table, $index)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$index}' AND Index_type = 'FULLTEXT'");
        return !empty($indexes);
    }
};
