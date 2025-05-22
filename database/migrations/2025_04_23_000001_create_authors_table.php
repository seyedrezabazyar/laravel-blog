<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * جدول نویسندگان بهینه‌سازی شده
     */
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->unsignedInteger('id', true)->primary();

            $table->string('name', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');

            $table->string('slug', 100)->unique()->charset('ascii')->collation('ascii_general_ci');

            $table->unsignedMediumInteger('posts_count')->default(0);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('name', 'idx_authors_name');
            $table->index('posts_count', 'idx_authors_posts_count');
            $table->index('created_at', 'idx_authors_created');

            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });

        // ایندکس FULLTEXT فقط در صورت ضرورت
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                // ایندکس FULLTEXT بهینه شده برای فارسی
                DB::statement('ALTER TABLE authors ADD FULLTEXT INDEX authors_name_fulltext (name) WITH PARSER ngram');
            } catch (\Exception $e) {
                // اگر ngram parser در دسترس نباشد، از parser معمولی استفاده کنیم
                DB::statement('ALTER TABLE authors ADD FULLTEXT INDEX authors_name_fulltext (name)');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
