<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publishers', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('name', 200)->charset('utf8mb4');
            $table->string('slug', 200)->unique()->charset('ascii');
            $table->text('description')->nullable()->charset('utf8mb4');
            $table->string('logo', 150)->nullable()->charset('ascii');
            $table->unsignedSmallInteger('posts_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('name');
            $table->index(['posts_count', 'name']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publishers');
    }
};
