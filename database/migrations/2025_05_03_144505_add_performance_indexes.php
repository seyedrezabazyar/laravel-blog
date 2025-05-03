<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes for frequently queried columns
        Schema::table('posts', function (Blueprint $table) {
            $table->index(['is_published', 'hide_content', 'created_at']);
            $table->index(['category_id', 'is_published']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('post_images', function (Blueprint $table) {
            $table->index(['post_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
