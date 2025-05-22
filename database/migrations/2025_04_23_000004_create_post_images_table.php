<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * جدول تصاویر پست - نسخه نهایی بهینه‌سازی شده
     */
    public function up(): void
    {
        Schema::create('post_images', function (Blueprint $table) {
            $table->unsignedInteger('id', true)->primary();
            $table->unsignedInteger('post_id');
            $table->string('image_path', 191)->charset('ascii')->collation('ascii_general_ci');

            $table->enum('hide_image', ['visible', 'hidden', 'missing'])->default('visible');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');

            $table->index(['post_id', 'hide_image'], 'idx_post_images_visibility');
            $table->index('hide_image', 'idx_images_status'); // برای کوئری‌های کلی روی وضعیت

            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });

        // فشرده‌سازی برای کاهش بیشتر حجم (اختیاری)
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE post_images ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=8');
            } catch (\Exception $e) {
                // اگر سرور از فشرده‌سازی پشتیبانی نکند، نادیده بگیریم
                \Log::info('Table compression not supported: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_images');
    }
};
