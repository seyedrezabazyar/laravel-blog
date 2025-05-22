<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                // تنظیمات بهینه‌سازی جداول
                $tables = ['users', 'categories', 'authors', 'publishers', 'posts', 'post_images', 'post_author', 'settings'];

                foreach ($tables as $table) {
                    // تنظیم موتور InnoDB و charset
                    DB::statement("ALTER TABLE {$table} ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                    // بهینه‌سازی جدول
                    DB::statement("OPTIMIZE TABLE {$table}");
                }

                // تنظیمات خاص برای جداول پرترافیک
                DB::statement("ALTER TABLE posts ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=8");
                DB::statement("ALTER TABLE post_images ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4");

                // تنظیمات کش جداول
                DB::statement("ALTER TABLE cache ENGINE=MEMORY");
                DB::statement("ALTER TABLE cache_locks ENGINE=MEMORY");

                \Log::info('Database optimization completed successfully');

            } catch (\Exception $e) {
                \Log::warning('Some database optimizations failed: ' . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        // برگشت تنظیمات در صورت نیاز
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                $tables = ['posts', 'post_images'];
                foreach ($tables as $table) {
                    DB::statement("ALTER TABLE {$table} ROW_FORMAT=DEFAULT");
                }

                DB::statement("ALTER TABLE cache ENGINE=InnoDB");
                DB::statement("ALTER TABLE cache_locks ENGINE=InnoDB");

            } catch (\Exception $e) {
                \Log::warning('Rollback of database optimizations failed: ' . $e->getMessage());
            }
        }
    }
};
