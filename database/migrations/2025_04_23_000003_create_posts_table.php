<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            // Book titles
            $table->string('title'); // Persian title
            $table->string('english_title')->nullable(); // English title
            $table->string('slug')->unique();

            // Book contents
            $table->text('content'); // Persian content
            $table->text('english_content')->nullable(); // English content

            // Book details
            $table->string('language')->nullable(); // Language of the book
            $table->year('publication_year')->nullable(); // Publication year
            $table->string('format')->nullable(); // Book format (PDF, EPUB, etc.)
            $table->text('book_codes')->nullable(); // ISBN codes (10 or 13 digits)

            // Purchase information
            $table->string('purchase_link')->nullable(); // Link to purchase the book

            // Publication status
            $table->boolean('hide_content')->default(false); // Flag to hide the content from users
            $table->boolean('is_published')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
