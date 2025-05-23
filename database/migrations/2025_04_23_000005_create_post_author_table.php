<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_author', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id');
            $table->unsignedMediumInteger('author_id');
            $table->timestamp('created_at')->useCurrent();

            // کلید اصلی ترکیبی
            $table->primary(['post_id', 'author_id']);

            // ایندکس برای reverse lookup
            $table->index('author_id');

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
