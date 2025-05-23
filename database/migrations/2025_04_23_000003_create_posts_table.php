<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            // کلیدهای اصلی
            $table->bigIncrements('id'); // برای ده‌ها میلیون رکورد
            $table->string('md5', 32)->unique()->charset('ascii'); // جلوگیری از تکرار
            $table->string('elasticsearch_id', 50)->unique()->charset('ascii');

            // کلیدهای خارجی
            $table->unsignedMediumInteger('user_id')->index();
            $table->unsignedMediumInteger('category_id')->index();
            $table->unsignedMediumInteger('author_id')->nullable()->index();
            $table->unsignedMediumInteger('publisher_id')->nullable()->index();

            // اطلاعات اصلی کتاب - فقط در MySQL
            $table->string('title', 255)->charset('utf8mb4');
            $table->string('slug', 150)->unique()->charset('ascii');
            $table->unsignedSmallInteger('publication_year')->nullable()->index();
            $table->unsignedSmallInteger('pages_count')->nullable();

            // فیلدهای denormalized برای عملکرد بهتر
            $table->string('format', 20)->charset('ascii')->index(); // pdf, epub, mobi - denormalized
            $table->string('language', 15)->charset('ascii')->index(); // fa, en, fa-en - denormalized

            // وضعیت‌ها
            $table->boolean('hide_content')->default(false);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_indexed')->default(false);

            // تاریخ‌ها
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('indexed_at')->nullable();

            // ایندکس‌های بهینه برای میلیون‌ها رکورد
            $table->index(['is_published', 'hide_content', 'id'], 'posts_visibility_idx');
            $table->index(['category_id', 'is_published', 'hide_content'], 'posts_category_idx');
            $table->index(['author_id', 'is_published', 'hide_content'], 'posts_author_idx');
            $table->index(['publisher_id', 'is_published', 'hide_content'], 'posts_publisher_idx');
            $table->index(['format', 'is_published'], 'posts_format_idx');
            $table->index(['language', 'is_published'], 'posts_language_idx');
            $table->index(['publication_year', 'is_published'], 'posts_year_idx');
            $table->index('md5');
            $table->index('elasticsearch_id');

            // کلیدهای خارجی
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('author_id')->references('id')->on('authors')->nullOnDelete();
            $table->foreign('publisher_id')->references('id')->on('publishers')->nullOnDelete();
        });

        // ایندکس FULLTEXT برای جستجوی عنوان
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX posts_title_fulltext (title)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
