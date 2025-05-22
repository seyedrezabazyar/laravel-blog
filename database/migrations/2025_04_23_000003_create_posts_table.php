<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement()->primary();
            $table->string('elasticsearch_id', 50)->unique()->charset('ascii');
            $table->unsignedMediumInteger('user_id')->index();
            $table->unsignedMediumInteger('category_id')->index();
            $table->unsignedMediumInteger('author_id')->nullable()->index();
            $table->unsignedMediumInteger('publisher_id')->nullable()->index();

            // اطلاعات اصلی کتاب
            $table->string('title', 255)->charset('utf8mb4');
            $table->string('slug', 100)->unique()->charset('ascii');

            // فیلدهای جدید برای جستجو
            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->string('format', 15)->nullable()->charset('ascii'); // pdf, epub, mobi, etc.
            $table->string('languages', 50)->nullable()->charset('ascii'); // fa,en,ar or fa or en
            $table->string('isbn', 20)->nullable()->charset('ascii');
            $table->unsignedSmallInteger('pages_count')->nullable();

            // وضعیت‌ها
            $table->boolean('hide_content')->default(false);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_indexed')->default(false);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('indexed_at')->nullable();

            // ایندکس‌های بهینه برای جستجو
            $table->index(['is_published', 'hide_content'], 'posts_visibility_idx');
            $table->index(['category_id', 'is_published', 'hide_content'], 'posts_category_idx');
            $table->index(['author_id', 'is_published', 'hide_content'], 'posts_author_idx');
            $table->index(['publisher_id', 'is_published', 'hide_content'], 'posts_publisher_idx');
            $table->index(['publication_year', 'is_published'], 'posts_year_idx');
            $table->index(['format', 'is_published'], 'posts_format_idx');
            $table->index(['languages', 'is_published'], 'posts_languages_idx');
            $table->index('isbn');
            $table->index('elasticsearch_id');

            // کلیدهای خارجی
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('author_id')->references('id')->on('authors')->nullOnDelete();
            $table->foreign('publisher_id')->references('id')->on('publishers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
