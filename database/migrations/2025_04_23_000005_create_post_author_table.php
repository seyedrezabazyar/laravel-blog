<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * For books with multiple authors
     */
    public function up(): void
    {
        Schema::create('post_author', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('author_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Make sure a book can't have the same author twice
            $table->unique(['post_id', 'author_id']);

            // ایندکس‌های اضافی برای بهبود عملکرد
            $table->index('post_id');
            $table->index('author_id');

            // شاخص مرکب برای جستجوی سریع‌تر بر اساس نویسنده در جدول پیوت
            $table->index(['author_id', 'post_id'], 'post_author_author_post_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_author');
    }
};
