<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts_new', function (Blueprint $table) {
            $table->increments('id');
            $table->string('md5_hash', 32)->unique()->charset('ascii');
            $table->unsignedMediumInteger('user_id')->index();
            $table->unsignedMediumInteger('category_id')->index();
            $table->unsignedMediumInteger('author_id')->nullable()->index();
            $table->unsignedMediumInteger('publisher_id')->nullable()->index();

            // فقط اطلاعات ضروری برای لیست و جستجو
            $table->string('title', 255)->charset('utf8mb4');
            $table->string('english_title', 255)->nullable()->charset('utf8mb4');
            $table->string('slug', 100)->unique()->charset('ascii');
            $table->char('language', 2)->nullable()->charset('ascii')->default('fa');
            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->enum('format', ['pdf', 'epub', 'mobi', 'doc', 'txt', 'other'])->nullable();
            $table->string('book_codes', 200)->nullable()->charset('ascii');
            $table->string('purchase_link', 500)->nullable()->charset('ascii');

            // خلاصه کوتاه برای نمایش در لیست (بجای محتوای کامل)
            $table->text('summary', 300)->nullable()->charset('utf8mb4');
            $table->text('english_summary', 300)->nullable()->charset('utf8mb4');

            // مسیر فایل‌های محتوا (بجای ذخیره در دیتابیس)
            $table->string('content_file_path', 200)->nullable()->charset('ascii');
            $table->string('english_content_file_path', 200)->nullable()->charset('ascii');

            // وضعیت
            $table->boolean('hide_content')->default(false);
            $table->boolean('is_published')->default(false);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // ایندکس‌های بهینه
            $table->index(['is_published', 'hide_content', 'created_at']);
            $table->index(['category_id', 'is_published', 'hide_content']);
            $table->index(['author_id', 'is_published']);
            $table->index(['publication_year', 'format']);
            $table->index('book_codes');

            // کلیدهای خارجی
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('author_id')->references('id')->on('authors')->nullOnDelete();
            $table->foreign('publisher_id')->references('id')->on('publishers')->nullOnDelete();
        });

        // ایندکس FULLTEXT برای جستجو
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE posts_new ADD FULLTEXT INDEX posts_search (title, english_title, summary, book_codes)');
            } catch (\Exception $e) {
                \Log::info('FULLTEXT index creation failed: ' . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('posts_new');
    }
};
