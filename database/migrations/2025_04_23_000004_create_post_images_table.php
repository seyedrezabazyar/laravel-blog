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
            $table->string('caption', 1500)->nullable(); // تغییر طول به 1500 کاراکتر
            // تغییر به enum با سه حالت NULL، visible و hidden. پیش‌فرض NULL
            $table->enum('hide_image', ['visible', 'hidden'])->nullable()->default(null);
            $table->integer('sort_order')->default(0);
            $table->index(['post_id', 'sort_order']); // ایندکس برای مرتب‌سازی تصاویر
            $table->index('hide_image'); // ایندکس برای فیلتر تصاویر مخفی
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
