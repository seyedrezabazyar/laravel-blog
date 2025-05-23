<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('name', 100)->charset('utf8mb4');
            $table->string('slug', 100)->unique()->charset('ascii');
            $table->string('description', 500)->nullable()->charset('utf8mb4');
            $table->string('image', 150)->nullable()->charset('ascii');
            $table->unsignedMediumInteger('posts_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // ایندکس‌های بهینه برای میلیون‌ها رکورد
            $table->index(['posts_count', 'id']);
            $table->index('slug');
        });

        // ایندکس FULLTEXT برای جستجو
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE categories ADD FULLTEXT INDEX categories_fulltext (name, description)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
