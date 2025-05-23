<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                // تنظیمات بهینه برای میلیون‌ها رکورد
                $tables = [
                    'categories', 'authors', 'publishers', 'posts',
                    'post_images', 'post_author', 'elasticsearch_configs', 'elasticsearch_errors'
                ];

                foreach ($tables as $table) {
                    // تنظیم ENGINE و CHARSET
                    DB::statement("ALTER TABLE {$table} ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                    // تنظیمات بهینه برای InnoDB
                    DB::statement("ALTER TABLE {$table} ROW_FORMAT=DYNAMIC");
                }

                // تنظیمات ویژه برای جدول posts (بزرگ‌ترین جدول)
                DB::statement("ALTER TABLE posts
                    ENGINE=InnoDB
                    ROW_FORMAT=DYNAMIC
                    KEY_BLOCK_SIZE=16
                    COMMENT='Main posts table - optimized for millions of records - denormalized design'");

                \Log::info('Database optimization completed successfully for high-volume denormalized design');

            } catch (\Exception $e) {
                \Log::warning('Some database optimizations failed: ' . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        // بازگردانی تنظیمات پیش‌فرض در صورت نیاز
    }
};
