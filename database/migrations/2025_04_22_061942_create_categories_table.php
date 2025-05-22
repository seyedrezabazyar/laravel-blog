<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * جدول دسته‌بندی‌ها - کاملاً بهینه‌سازی شده
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            // ID بهینه - تبدیل از bigint به unsignedInteger
            $table->unsignedInteger('id', true)->primary();

            // نام دسته‌بندی بهینه‌شده
            $table->string('name', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');

            // slug بهینه شده
            $table->string('slug', 150)->unique()->charset('ascii')->collation('ascii_general_ci');

            // توضیحات کوتاه به جای text
            $table->string('description', 1000)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');

            // مسیر تصویر بهینه‌شده
            $table->string('image', 191)->nullable()->charset('ascii')->collation('ascii_general_ci');

            // شمارنده پست‌ها - mediumint کافی است
            $table->unsignedMediumInteger('posts_count')->default(0);

            // timestamps بهینه‌سازی شده
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // ایندکس‌های بهینه
            $table->index('name', 'idx_categories_name');
            $table->index('posts_count', 'idx_categories_posts_count');
            $table->index('created_at', 'idx_categories_created');

            // تنظیمات جدول
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });

        // ایندکس FULLTEXT برای جستجو در نام و توضیحات
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE categories ADD FULLTEXT INDEX categories_search_fulltext (name, description) WITH PARSER ngram');
            } catch (\Exception $e) {
                // fallback برای سرورهایی که ngram ندارند
                DB::statement('ALTER TABLE categories ADD FULLTEXT INDEX categories_search_fulltext (name, description)');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
