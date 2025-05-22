<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                // فقط charset و engine
                $tables = [
                    'users', 'categories', 'authors', 'publishers',
                    'posts', 'post_images', 'post_author', 'settings',
                    'elasticsearch_configs', 'elasticsearch_errors'
                ];

                foreach ($tables as $table) {
                    DB::statement("ALTER TABLE {$table} ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                }

                // ایندکس FULLTEXT برای posts
                DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX posts_title_fulltext (title)');

                \Log::info('Database optimization completed successfully');

            } catch (\Exception $e) {
                \Log::warning('Some database optimizations failed: ' . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        //
    }
};
