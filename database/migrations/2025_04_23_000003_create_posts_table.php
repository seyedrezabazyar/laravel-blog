<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('elasticsearch_id', 50)->unique()->charset('ascii');
            $table->unsignedMediumInteger('user_id')->index();
            $table->unsignedMediumInteger('category_id')->index();
            $table->unsignedMediumInteger('author_id')->nullable()->index();
            $table->unsignedMediumInteger('publisher_id')->nullable()->index();

            // فقط اطلاعات ضروری برای مدیریت
            $table->string('title', 255)->charset('utf8mb4'); // برای admin panel
            $table->string('slug', 100)->unique()->charset('ascii'); // برای URL

            // وضعیت‌ها
            $table->boolean('hide_content')->default(false);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_indexed')->default(false); // آیا در Elasticsearch است؟

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('indexed_at')->nullable(); // آخرین بار که در ES به‌روز شد

            // ایندکس‌های ضروری
            $table->index(['is_published', 'hide_content', 'is_indexed']);
            $table->index(['category_id', 'is_published']);
            $table->index('elasticsearch_id');
            $table->index('created_at');

            // کلیدهای خارجی
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('author_id')->references('id')->on('authors')->nullOnDelete();
            $table->foreign('publisher_id')->references('id')->on('publishers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
