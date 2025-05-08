<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * اضافه کردن شاخص‌های بهینه‌سازی عملکرد به جداول
     */
    public function up(): void
    {
        // شاخص‌های جدول posts
        try {
            DB::statement('CREATE INDEX idx_posts_published_hidden_created ON posts(is_published, hide_content, created_at)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        try {
            DB::statement('CREATE INDEX idx_posts_category_published ON posts(category_id, is_published)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        try {
            DB::statement('CREATE INDEX idx_posts_author_published ON posts(author_id, is_published)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        try {
            DB::statement('CREATE INDEX idx_posts_format_year ON posts(format, publication_year)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        try {
            DB::statement('CREATE INDEX idx_posts_publisher ON posts(publisher_id)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        try {
            DB::statement('CREATE INDEX idx_posts_slug ON posts(slug)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        // شاخص جستجوی متنی کامل
        try {
            DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX ftx_posts_content (title, english_title, content, english_content)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        // شاخص‌های جدول post_images
        try {
            DB::statement('CREATE INDEX idx_post_images_post_id_sort ON post_images(post_id, sort_order)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        try {
            DB::statement('CREATE INDEX idx_post_images_visibility ON post_images(hide_image)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        // شاخص‌های جدول post_author
        try {
            DB::statement('CREATE INDEX idx_post_author_post ON post_author(post_id)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        try {
            DB::statement('CREATE INDEX idx_post_author_author ON post_author(author_id)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        // شاخص‌های جدول post_tag
        try {
            DB::statement('CREATE INDEX idx_post_tag_post ON post_tag(post_id)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        try {
            DB::statement('CREATE INDEX idx_post_tag_tag ON post_tag(tag_id)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        // شاخص‌های جدول categories
        try {
            DB::statement('CREATE INDEX idx_categories_slug ON categories(slug)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        try {
            DB::statement('CREATE INDEX idx_categories_name ON categories(name)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        // شاخص‌های جدول authors
        try {
            DB::statement('CREATE INDEX idx_authors_slug ON authors(slug)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        try {
            // استفاده از محدودیت طول برای ستون name در جدول authors
            DB::statement('CREATE INDEX idx_authors_name ON authors(name(768))');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        // شاخص‌های جدول publishers
        try {
            DB::statement('CREATE INDEX idx_publishers_slug ON publishers(slug)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        try {
            DB::statement('CREATE INDEX idx_publishers_name ON publishers(name)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        // شاخص‌های جدول tags
        try {
            DB::statement('CREATE INDEX idx_tags_slug ON tags(slug)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }

        try {
            DB::statement('CREATE INDEX idx_tags_name ON tags(name)');
        } catch (\Exception $e) {
            // اگر شاخص از قبل وجود داشته باشد، خطا را نادیده بگیر
        }
    }

    /**
     * Reverse the migrations.
     * حذف شاخص‌ها در صورت نیاز به بازگشت تغییرات
     */
    public function down(): void
    {
        // حذف شاخص‌های جدول posts
        DB::statement('DROP INDEX IF EXISTS idx_posts_published_hidden_created ON posts');
        DB::statement('DROP INDEX IF EXISTS idx_posts_category_published ON posts');
        DB::statement('DROP INDEX IF EXISTS idx_posts_author_published ON posts');
        DB::statement('DROP INDEX IF EXISTS idx_posts_format_year ON posts');
        DB::statement('DROP INDEX IF EXISTS idx_posts_publisher ON posts');
        DB::statement('DROP INDEX IF EXISTS idx_posts_slug ON posts');
        DB::statement('DROP INDEX IF EXISTS ftx_posts_content ON posts');

        // حذف شاخص‌های جدول post_images
        DB::statement('DROP INDEX IF EXISTS idx_post_images_post_id_sort ON post_images');
        DB::statement('DROP INDEX IF EXISTS idx_post_images_visibility ON post_images');

        // حذف شاخص‌های جدول post_author
        DB::statement('DROP INDEX IF EXISTS idx_post_author_post ON post_author');
        DB::statement('DROP INDEX IF EXISTS idx_post_author_author ON post_author');

        // حذف شاخص‌های جدول post_tag
        DB::statement('DROP INDEX IF EXISTS idx_post_tag_post ON post_tag');
        DB::statement('DROP INDEX IF EXISTS idx_post_tag_tag ON post_tag');

        // حذف شاخص‌های جدول categories
        DB::statement('DROP INDEX IF EXISTS idx_categories_slug ON categories');
        DB::statement('DROP INDEX IF EXISTS idx_categories_name ON categories');

        // حذف شاخص‌های جدول authors
        DB::statement('DROP INDEX IF EXISTS idx_authors_slug ON authors');
        DB::statement('DROP INDEX IF EXISTS idx_authors_name ON authors');

        // حذف شاخص‌های جدول publishers
        DB::statement('DROP INDEX IF EXISTS idx_publishers_slug ON publishers');
        DB::statement('DROP INDEX IF EXISTS idx_publishers_name ON publishers');

        // حذف شاخص‌های جدول tags
        DB::statement('DROP INDEX IF EXISTS idx_tags_slug ON tags');
        DB::statement('DROP INDEX IF EXISTS idx_tags_name ON tags');
    }
};
