<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->string('caption', 1500)->nullable(); // افزایش طول به 1500 کاراکتر

            // Enum با چهار حالت: NULL, visible, hidden, missing. پیش‌فرض NULL
            $table->enum('hide_image', ['visible', 'hidden', 'missing'])->nullable()->default(null);

            $table->integer('sort_order')->default(0);

            // ایندکس‌های بهینه‌سازی شده
            $table->index(['post_id', 'sort_order']); // ایندکس برای مرتب‌سازی تصاویر
            $table->index(['post_id', 'hide_image']); // ایندکس ترکیبی برای کوئری‌های پرکاربرد
            $table->index('hide_image'); // ایندکس برای فیلتر کردن تصاویر پنهان

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_images');
    }
};
