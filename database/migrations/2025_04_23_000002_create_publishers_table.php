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
            $table->unsignedMediumInteger('posts_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // ایندکس‌های بهینه برای میلیون‌ها رکورد
            $table->index(['posts_count', 'id']);
            $table->index('slug');
        });

        // ایندکس FULLTEXT برای جستجو
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE publishers ADD FULLTEXT INDEX publishers_fulltext (name)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('publishers');
    }
};
