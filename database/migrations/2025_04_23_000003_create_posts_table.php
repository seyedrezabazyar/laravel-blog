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
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('md5_hash')->unique(); // Unique MD5 hash for each book
            $table->foreignId('user_id')->constrained();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('author_id')->nullable()->constrained('authors')->nullOnDelete();
            $table->foreignId('publisher_id')->nullable()->constrained('publishers')->nullOnDelete();

            // Book titles - updating based on your requirements
            $table->string('title', 1500); // Persian title - 1500 characters
            $table->string('english_title', 1500)->nullable(); // English title - 1500 characters
            $table->string('slug')->unique();

            // Book contents
            $table->text('content'); // Persian content
            $table->text('english_content')->nullable(); // English content

            // Book details
            $table->string('language', 70)->nullable(); // Language of the book
            $table->string('publication_year', 14)->nullable(); // Publication year
            $table->string('format', 7)->nullable(); // Book format
            $table->string('book_codes', 300)->nullable(); // ISBN codes

            // Additional fields
            $table->string('edition', 60)->nullable(); // Book edition
            $table->string('pages', 100)->nullable(); // Book pages
            $table->string('size', 10)->nullable(); // Book size

            // Purchase information
            $table->string('purchase_link')->nullable(); // Link to purchase the book

            // Publication status
            $table->boolean('hide_content')->default(false); // Flag to hide the content
            $table->boolean('is_published')->default(false);

            // Índices optimizados
            $table->index(['is_published', 'hide_content', 'created_at']);

            // Índice principal para consultas de categorías - optimizado para la consulta lenta
            $table->index(['category_id', 'is_published', 'hide_content']); // Mejora index específico para categorías

            $table->index(['author_id', 'is_published']);
            $table->index(['publisher_id', 'is_published']);
            $table->index(['format', 'publication_year']);
            $table->index('book_codes');
            $table->index('slug');

            $table->timestamps();
        });

        // ایندکس‌های FULLTEXT برای جستجوی متنی سریع
        DB::statement('ALTER TABLE posts ADD FULLTEXT posts_title_fulltext (title, english_title)');
        DB::statement('ALTER TABLE posts ADD FULLTEXT posts_content_fulltext (content, english_content)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
