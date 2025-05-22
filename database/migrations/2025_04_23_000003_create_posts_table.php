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
            $table->increments('id'); // نگه داشتن auto-increment برای سازگاری
            $table->string('md5_hash', 32)->unique()->charset('ascii');
            $table->unsignedMediumInteger('user_id');
            $table->unsignedMediumInteger('category_id');
            $table->unsignedMediumInteger('author_id')->nullable();
            $table->unsignedMediumInteger('publisher_id')->nullable();

            // عناوین کتاب - کاهش طول برای بهینه‌سازی
            $table->string('title', 255)->charset('utf8mb4');
            $table->string('english_title', 255)->nullable()->charset('utf8mb4');
            $table->string('slug', 100)->unique()->charset('ascii');

            // محتوای کتاب
            $table->longText('content')->charset('utf8mb4');
            $table->longText('english_content')->nullable()->charset('utf8mb4');

            // جزئیات کتاب - بهینه‌سازی شده
            $table->char('language', 2)->nullable()->charset('ascii')->index(); // fa, en
            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->enum('format', ['pdf', 'epub', 'mobi', 'doc', 'txt', 'other'])->nullable();
            $table->string('book_codes', 200)->nullable()->charset('ascii');
            $table->string('edition', 50)->nullable()->charset('utf8mb4');
            $table->unsignedSmallInteger('pages')->nullable(); // تبدیل به عدد
            $table->string('size', 10)->nullable()->charset('ascii'); // نگه داشتن string برای مقادیر مثل "2.5MB"

            // لینک خرید
            $table->string('purchase_link', 500)->nullable()->charset('ascii');

            // وضعیت انتشار - ایندکس‌های جداگانه برای کوئری‌های مجزا
            $table->boolean('hide_content')->default(false)->index();
            $table->boolean('is_published')->default(false)->index();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // ایندکس‌های پوششی بهینه شده
            $table->index(['is_published', 'hide_content', 'created_at'], 'idx_posts_status_date');
            $table->index(['category_id', 'is_published', 'hide_content', 'created_at'], 'idx_posts_category_status');
            $table->index(['author_id', 'is_published', 'hide_content'], 'idx_posts_author_status');
            $table->index(['publisher_id', 'is_published', 'hide_content'], 'idx_posts_publisher_status');
            $table->index(['publication_year', 'format'], 'idx_posts_year_format');
            $table->index('book_codes');
            $table->index('created_at');

            // کلیدهای خارجی
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('author_id')->references('id')->on('authors')->nullOnDelete();
            $table->foreign('publisher_id')->references('id')->on('publishers')->nullOnDelete();
        });

        // ایندکس‌های FULLTEXT بهینه شده - فقط برای جستجوی واقعی
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                // فقط عناوین برای جستجوی اصلی
                DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX posts_title_fulltext (title, english_title)');

                // ایندکس جزئی برای پست‌های منتشر شده
                DB::statement('CREATE INDEX idx_posts_published_partial ON posts (id) WHERE is_published = 1');

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
