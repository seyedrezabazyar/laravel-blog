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
            $table->string('md5_hash', 32)->unique()->charset('ascii');
            $table->unsignedMediumInteger('user_id');
            $table->unsignedMediumInteger('category_id');
            $table->unsignedMediumInteger('author_id')->nullable();
            $table->unsignedMediumInteger('publisher_id')->nullable();

            // عناوین کتاب
            $table->string('title', 800)->charset('utf8mb4');
            $table->string('english_title', 800)->nullable()->charset('utf8mb4');
            $table->string('slug', 800)->unique()->charset('ascii');

            // محتوای کتاب
            $table->longText('content')->charset('utf8mb4');
            $table->longText('english_content')->nullable()->charset('utf8mb4');

            // جزئیات کتاب
            $table->string('language', 50)->nullable()->charset('ascii');
            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->enum('format', ['pdf', 'epub', 'mobi', 'doc', 'txt', 'other'])->nullable();
            $table->string('book_codes', 200)->nullable()->charset('ascii');
            $table->string('edition', 50)->nullable()->charset('utf8mb4');
            $table->string('pages', 10)->nullable()->charset('ascii');
            $table->string('size', 10)->nullable()->charset('ascii');

            // لینک خرید
            $table->string('purchase_link', 500)->nullable()->charset('ascii');

            // وضعیت انتشار
            $table->boolean('hide_content')->default(false);
            $table->boolean('is_published')->default(false);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // ایندکس‌های بهینه
            $table->index(['is_published', 'hide_content', 'created_at']);
            $table->index(['category_id', 'is_published', 'hide_content']);
            $table->index(['author_id', 'is_published', 'hide_content']);
            $table->index(['publisher_id', 'is_published', 'hide_content']);
            $table->index(['publication_year', 'format']);
            $table->index('book_codes');
            $table->index('created_at');

            // کلیدهای خارجی
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('author_id')->references('id')->on('authors')->nullOnDelete();
            $table->foreign('publisher_id')->references('id')->on('publishers')->nullOnDelete();
        });

        // ایندکس‌های FULLTEXT برای جستجو
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX posts_title_fulltext (title, english_title)');
                DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX posts_search_fulltext (title, english_title, book_codes)');
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
