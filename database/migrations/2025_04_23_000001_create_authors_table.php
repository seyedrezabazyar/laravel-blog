<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('name', 150)->charset('utf8mb4');
            $table->string('slug', 150)->unique()->charset('ascii');
            $table->unsignedSmallInteger('posts_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('name');
            $table->index('posts_count');
            $table->index(['posts_count', 'coauthored_count']); // ✅ ویرایش شد
            $table->index('created_at');
        });

        // ایندکس FULLTEXT شامل biography
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE authors ADD FULLTEXT INDEX authors_fulltext (name, biography)'); // ✅ ویرایش شد
            } catch (\Exception $e) {
                \Log::info('FULLTEXT index creation failed: ' . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
