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

            // Book titles - updating based on your requirements
            $table->string('title', 1500); // Persian title - 1500 characters
            $table->string('english_title', 1500)->nullable(); // English title - 1500 characters
            $table->string('slug')->unique();

            // Book contents - updating based on your requirements
            $table->text('content', 90000); // Persian content - 90000 characters
            $table->text('english_content', 90000)->nullable(); // English content - 90000 characters

            // Book details - updating based on your requirements
            $table->string('language', 70)->nullable(); // Language of the book - 70 characters
            $table->string('publication_year', 14)->nullable(); // Publication year - 14 characters (changing from year to string)
            $table->string('format', 7)->nullable(); // Book format - 7 characters
            $table->string('book_codes', 300)->nullable(); // ISBN codes - 300 characters

            // Adding new fields based on your requirements
            $table->string('edition', 60)->nullable(); // Book edition - 60 characters
            $table->string('pages', 100)->nullable(); // Book pages - 100 characters (numeric)
            $table->string('size', 10)->nullable(); // Book size - 10 characters (numeric)

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
