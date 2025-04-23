<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique(); // MD5 یونیک
            $table->foreignId('category_id')->constrained(); // دسته‌بندی
            $table->string('slug')->unique();
            $table->string('title_fa'); // عنوان فارسی
            $table->string('title_en')->nullable(); // عنوان انگلیسی
            $table->text('description_fa')->nullable(); // توضیحات فارسی
            $table->text('description_en')->nullable(); // توضیحات انگلیسی
            $table->string('purchase_link')->nullable(); // لینک خرید
            $table->string('cover_image')->nullable(); // تصویر جلد
            $table->boolean('hide_cover')->default(false); // مخفی کردن تصویر
            $table->boolean('is_restricted')->default(false); // محدود کردن نمایش محتوا
            $table->string('keywords')->nullable(); // کلمات کلیدی
            $table->string('language')->nullable(); // زبان کتاب
            $table->string('publish_year')->nullable(); // سال انتشار
            $table->string('publisher')->nullable(); // ناشر
            $table->string('format')->nullable(); // فرمت کتاب
            $table->text('isbn_codes')->nullable(); // کدهای کتاب
            $table->boolean('is_published')->default(true); // وضعیت انتشار
            $table->foreignId('user_id')->constrained(); // کاربر ایجاد کننده
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('books');
    }
};
