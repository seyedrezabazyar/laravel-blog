<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                // 1. پارتیشن‌بندی جدول posts بر اساس تاریخ
                DB::statement("
                    ALTER TABLE posts
                    PARTITION BY RANGE (YEAR(created_at)) (
                        PARTITION p2022 VALUES LESS THAN (2023),
                        PARTITION p2023 VALUES LESS THAN (2024),
                        PARTITION p2024 VALUES LESS THAN (2025),
                        PARTITION p2025 VALUES LESS THAN (2026),
                        PARTITION p_future VALUES LESS THAN MAXVALUE
                    )
                ");

                // 2. پارتیشن‌بندی جدول user_activities بر اساس تاریخ
                DB::statement("
                    ALTER TABLE user_activities
                    PARTITION BY RANGE (UNIX_TIMESTAMP(created_at)) (
                        PARTITION p_last_month VALUES LESS THAN (UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))),
                        PARTITION p_current_month VALUES LESS THAN (UNIX_TIMESTAMP(NOW())),
                        PARTITION p_future VALUES LESS THAN MAXVALUE
                    )
                ");

                // 3. پارتیشن‌بندی جدول elasticsearch_logs برای عملکرد بهتر
                DB::statement("
                    ALTER TABLE elasticsearch_logs
                    PARTITION BY RANGE (TO_DAYS(created_at)) (
                        PARTITION p_week1 VALUES LESS THAN (TO_DAYS(DATE_SUB(NOW(), INTERVAL 6 DAY))),
                        PARTITION p_week2 VALUES LESS THAN (TO_DAYS(DATE_SUB(NOW(), INTERVAL 3 DAY))),
                        PARTITION p_current VALUES LESS THAN (TO_DAYS(NOW() + INTERVAL 1 DAY)),
                        PARTITION p_future VALUES LESS THAN MAXVALUE
                    )
                ");

                \Log::info('Database partitioning applied successfully');

            } catch (\Exception $e) {
                \Log::error('Partitioning failed: ' . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                DB::statement("ALTER TABLE posts REMOVE PARTITIONING");
                DB::statement("ALTER TABLE user_activities REMOVE PARTITIONING");
                DB::statement("ALTER TABLE elasticsearch_logs REMOVE PARTITIONING");
            } catch (\Exception $e) {
                \Log::warning('Partition removal failed: ' . $e->getMessage());
            }
        }
    }
};
