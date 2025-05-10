<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 1024);
            $table->string('slug')->unique();
            $table->text('biography')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('posts_count')->default(0); // شمارنده پست‌های اصلی
            $table->unsignedInteger('coauthored_count')->default(0); // شمارنده پست‌های همکاری
            $table->timestamps();
        });

        // ایندکس محدود شده برای نام (با توجه به محدودیت طول ایندکس در InnoDB)
        DB::statement('CREATE INDEX idx_authors_name ON authors(name(768))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
