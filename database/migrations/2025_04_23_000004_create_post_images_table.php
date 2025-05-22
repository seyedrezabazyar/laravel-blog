<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_images', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('post_id');
            $table->string('image_path', 300)->charset('ascii');
            $table->string('caption', 300)->nullable()->charset('utf8mb4');
            $table->enum('hide_image', ['visible', 'hidden', 'missing'])->default('visible');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // ایندکس‌های بهینه
            $table->index(['post_id', 'sort_order']);
            $table->index(['post_id', 'hide_image']);
            $table->index('hide_image');
            $table->index('approved_at');

            // کلید خارجی
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_images');
    }
};
