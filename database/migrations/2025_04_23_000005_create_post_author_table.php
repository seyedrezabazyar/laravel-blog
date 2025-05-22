<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_author', function (Blueprint $table) {
            $table->unsignedInteger('post_id');
            $table->unsignedMediumInteger('author_id');
            $table->timestamp('created_at')->useCurrent();

            // کلید اصلی ترکیبی
            $table->primary(['post_id', 'author_id']);

            // ایندکس‌های بهینه
            $table->index('author_id');
            $table->index(['author_id', 'created_at']);

            // کلیدهای خارجی
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_author');
    }
};
