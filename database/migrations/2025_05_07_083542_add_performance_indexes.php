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
        DB::statement('CREATE INDEX idx_posts_published_hidden_created ON posts(is_published, hide_content, created_at)');
        DB::statement('CREATE INDEX idx_posts_category_published ON posts(category_id, is_published)');
        DB::statement('CREATE INDEX idx_posts_author_published ON posts(author_id, is_published)');
        DB::statement('CREATE INDEX idx_posts_format_year ON posts(format, publication_year)');
        DB::statement('CREATE INDEX idx_posts_publisher ON posts(publisher_id)');
        DB::statement('CREATE INDEX idx_posts_slug ON posts(slug)');

        // شاخص جستجوی متنی کامل
        DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX ftx_posts_content (title, english_title, content, english_content)');

        // شاخص‌های جدول post_images
        DB::statement('CREATE INDEX idx_post_images_post_id_sort ON post_images(post_id, sort_order)');
        DB::statement('CREATE INDEX idx_post_images_visibility ON post_images(hide_image)');

        // شاخص‌های جدول post_author
        DB::statement('CREATE INDEX idx_post_author_post ON post_author(post_id)');
        DB::statement('CREATE INDEX idx_post_author_author ON post_author(author_id)');

        // شاخص‌های جدول post_tag
        DB::statement('CREATE INDEX idx_post_tag_post ON post_tag(post_id)');
        DB::statement('CREATE INDEX idx_post_tag_tag ON post_tag(tag_id)');

        // شاخص‌های جدول categories
        DB::statement('CREATE INDEX idx_categories_slug ON categories(slug)');
        DB::statement('CREATE INDEX idx_categories_name ON categories(name)');

        // شاخص‌های جدول authors
        DB::statement('CREATE INDEX idx_authors_slug ON authors(slug)');
        DB::statement('CREATE INDEX idx_authors_name ON authors(name)');

        // شاخص‌های جدول publishers
        DB::statement('CREATE INDEX idx_publishers_slug ON publishers(slug)');
        DB::statement('CREATE INDEX idx_publishers_name ON publishers(name)');

        // شاخص‌های جدول tags
        DB::statement('CREATE INDEX idx_tags_slug ON tags(slug)');
        DB::statement('CREATE INDEX idx_tags_name ON tags(name)');
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
