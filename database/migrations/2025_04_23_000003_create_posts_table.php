<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('md5_hash')->unique(); // Unique MD5 hash for each book
            $table->foreignId('user_id')->constrained();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('author_id')->nullable()->constrained('authors')->nullOnDelete();
            $table->foreignId('publisher_id')->nullable()->constrained('publishers')->nullOnDelete();

            // Book titles - updating based on your requirements
            $table->string('title', 1500); // Persian title - 1500 characters
            $table->string('english_title', 1500)->nullable(); // English title - 1500 characters
            $table->string('slug')->unique();

            // Book contents - تغییر به longText برای پشتیبانی از داده‌های بزرگ
            $table->longText('content'); // Persian content
            $table->longText('english_content')->nullable(); // English content

            // Book details
            $table->string('language', 70)->nullable(); // Language of the book
            $table->string('publication_year', 14)->nullable(); // Publication year
            $table->string('format', 7)->nullable(); // Book format
            $table->string('book_codes', 300)->nullable(); // ISBN codes

            // Additional fields
            $table->string('edition', 60)->nullable(); // Book edition
            $table->string('pages', 100)->nullable(); // Book pages
            $table->string('size', 10)->nullable(); // Book size

            // Purchase information
            $table->string('purchase_link')->nullable(); // Link to purchase the book

            // Publication status
            $table->boolean('hide_content')->default(false); // Flag to hide the content
            $table->boolean('is_published')->default(false);

            // شاخص‌های بهینه‌سازی شده
            $table->index(['is_published', 'hide_content', 'created_at']);

            // شاخص اصلی برای کوئری‌های دسته‌بندی - بهینه‌سازی شده برای کوئری کند
            $table->index(['category_id', 'is_published', 'hide_content']); // بهبود شاخص مخصوص دسته‌بندی‌ها

            // افزودن شاخص بهینه برای صفحه ناشر
            $table->index(['publisher_id', 'is_published', 'hide_content', 'created_at'], 'idx_publisher_posts');

            // شاخص بهینه‌سازی شده برای صفحه نویسنده
            $table->index(['author_id', 'is_published', 'hide_content'], 'posts_author_visibility_index');
            $table->index(['is_published', 'hide_content', 'created_at'], 'posts_published_created_index');

            // شاخص‌های بهینه‌سازی شده برای نویسنده
            $table->index(['author_id', 'is_published', 'hide_content', 'created_at'], 'idx_posts_by_author_status');
            $table->index(['author_id', 'created_at'], 'idx_author_post_created');

            $table->index(['author_id', 'is_published']);
            $table->index(['publisher_id', 'is_published']);
            $table->index(['format', 'publication_year']);
            $table->index('book_codes');
            $table->index('slug');

            $table->timestamps();
        });

        // ایندکس‌های FULLTEXT برای جستجوی متنی سریع
        DB::statement('ALTER TABLE posts ADD FULLTEXT posts_title_fulltext (title, english_title)');
        DB::statement('ALTER TABLE posts ADD FULLTEXT posts_content_fulltext (content, english_content)');

        // ایندکس FULLTEXT بهینه‌سازی شده فقط برای عنوان
        DB::statement('ALTER TABLE posts ADD FULLTEXT posts_title_only_fulltext (title)');

        // ایندکس جامع برای همه فیلدهای متنی
        DB::statement('ALTER TABLE posts ADD FULLTEXT posts_all_fulltext (title, english_title, book_codes, content)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
