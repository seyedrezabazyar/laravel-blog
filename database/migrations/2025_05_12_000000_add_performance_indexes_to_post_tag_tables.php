<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ایندکس‌های بهبود عملکرد برای فرم ویرایش پست
     */
    public function up()
    {
        // بررسی اینکه آیا موتور دیتابیس MySQL است
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                // ایندکس برای بهبود کارایی صفحه ویرایش پست
                if (!$this->hasIndex('posts', 'idx_posts_edit_performance')) {
                    DB::statement('CREATE INDEX idx_posts_edit_performance ON posts(id, title, slug, category_id, author_id, publisher_id)');
                }

                // ایندکس برای بهبود کارایی جستجوی تصویر شاخص
                if (!$this->hasIndex('post_images', 'idx_post_images_featured')) {
                    DB::statement('CREATE INDEX idx_post_images_featured ON post_images(post_id, sort_order)');
                }

                // ایندکس برای بهبود کارایی تغییر وضعیت
                if (!$this->hasIndex('posts', 'idx_posts_status')) {
                    DB::statement('CREATE INDEX idx_posts_status ON posts(id, is_published, hide_content)');
                }

                // ایندکس برای بهبود کارایی جستجوی تگ‌ها
                if (!$this->hasIndex('post_tag', 'idx_post_tag_search')) {
                    DB::statement('CREATE INDEX idx_post_tag_search ON post_tag(post_id)');
                }

                // ایندکس برای بهبود کارایی جستجوی نویسندگان همکار
                if (!$this->hasIndex('post_author', 'idx_post_author_search')) {
                    DB::statement('CREATE INDEX idx_post_author_search ON post_author(post_id)');
                }
            } catch (\Exception $e) {
                // ثبت خطا اما ادامه اجرای migration
                \Log::error('Error creating performance indexes: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                if ($this->hasIndex('posts', 'idx_posts_edit_performance')) {
                    DB::statement('DROP INDEX idx_posts_edit_performance ON posts');
                }

                if ($this->hasIndex('post_images', 'idx_post_images_featured')) {
                    DB::statement('DROP INDEX idx_post_images_featured ON post_images');
                }

                if ($this->hasIndex('posts', 'idx_posts_status')) {
                    DB::statement('DROP INDEX idx_posts_status ON posts');
                }

                if ($this->hasIndex('post_tag', 'idx_post_tag_search')) {
                    DB::statement('DROP INDEX idx_post_tag_search ON post_tag');
                }

                if ($this->hasIndex('post_author', 'idx_post_author_search')) {
                    DB::statement('DROP INDEX idx_post_author_search ON post_author');
                }
            } catch (\Exception $e) {
                \Log::error('Error dropping performance indexes: ' . $e->getMessage());
            }
        }
    }

    /**
     * بررسی وجود ایندکس
     */
    private function hasIndex($table, $index)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$index}'");
        return !empty($indexes);
    }
};
