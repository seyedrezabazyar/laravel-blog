<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * اجرای migration - ایجاد جدول post_images بهینه‌شده
     */
    public function up(): void
    {
        Schema::create('post_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id')->index();
            $table->enum('status', ['visible', 'hidden', 'missing'])->default('visible')->index();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // ایندکس‌های بهینه برای میلیون‌ها رکورد
            $table->index(['post_id', 'status'], 'post_images_post_status_idx');
            $table->index(['status', 'created_at'], 'post_images_status_created_idx');
            $table->index(['post_id', 'created_at'], 'post_images_post_created_idx');

            // کلید خارجی
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        });

        // بهینه‌سازی جدول برای حجم بالا
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE post_images
                ENGINE=InnoDB
                ROW_FORMAT=DYNAMIC
                COMMENT='Post images table - optimized for calculated URLs based on post_id and md5'");
        }
    }

    /**
     * بازگردانی migration - بازگرداندن جدول به حالت اولیه
     */
    public function down(): void
    {
        Schema::dropIfExists('post_images');
    }
};
