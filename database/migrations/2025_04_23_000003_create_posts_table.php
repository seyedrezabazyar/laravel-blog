<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('elasticsearch_id', 50)->unique()->charset('ascii');
            $table->unsignedMediumInteger('user_id')->index();
            $table->unsignedMediumInteger('category_id')->index();
            $table->unsignedMediumInteger('author_id')->nullable()->index();
            $table->unsignedMediumInteger('publisher_id')->nullable()->index();

            // اطلاعات اصلی کتاب (بدون محتوا)
            $table->string('title', 255)->charset('utf8mb4');
            $table->string('english_title', 255)->nullable()->charset('utf8mb4');
            $table->string('slug', 100)->unique()->charset('ascii');
            $table->char('language', 2)->default('fa')->charset('ascii');
            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->enum('format', ['pdf', 'epub', 'mobi', 'doc', 'txt', 'other'])->nullable();
            $table->string('book_codes', 200)->nullable()->charset('ascii');
            $table->string('edition', 50)->nullable()->charset('utf8mb4');
            $table->unsignedSmallInteger('pages')->nullable();
            $table->string('size', 10)->nullable()->charset('ascii');
            $table->string('purchase_link', 500)->nullable()->charset('ascii');

            // آمار محاسبه شده از Elasticsearch
            $table->unsignedInteger('content_word_count')->default(0);
            $table->unsignedSmallInteger('reading_time_minutes')->default(0);
            $table->boolean('has_english_content')->default(false);

            // وضعیت
            $table->boolean('hide_content')->default(false);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_indexed')->default(false);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('indexed_at')->nullable();

            // ایندکس‌های بهینه شده
            $table->index(['is_published', 'hide_content', 'is_indexed']);
            $table->index(['category_id', 'is_published', 'hide_content']);
            $table->index(['author_id', 'is_published']);
            $table->index(['publisher_id', 'is_published']);
            $table->index(['publication_year', 'format']);
            $table->index('elasticsearch_id');
            $table->index('book_codes');
            $table->index('created_at');

            // کلیدهای خارجی
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('author_id')->references('id')->on('authors')->nullOnDelete();
            $table->foreign('publisher_id')->references('id')->on('publishers')->nullOnDelete();
        });

        // ایندکس FULLTEXT فقط برای عناوین و کدها
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX posts_search (title, english_title, book_codes)');
            } catch (\Exception $e) {
                \Log::info('FULLTEXT index creation failed: ' . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
