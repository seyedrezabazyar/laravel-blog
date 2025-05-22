<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        Log::info('شروع به‌روزرسانی کلیدهای خارجی...');

        // حذف کلیدهای خارجی قدیمی
        $this->dropOldForeignKeys();

        // اضافه کردن کلیدهای خارجی جدید
        $this->addNewForeignKeys();

        Log::info('به‌روزرسانی کلیدهای خارجی تکمیل شد.');
    }

    private function dropOldForeignKeys()
    {
        $foreignKeys = [
            'post_images' => [
                'post_images_post_id_foreign',
                'post_images_ibfk_1' // نام احتمالی دیگر
            ],
            'post_author' => [
                'post_author_post_id_foreign',
                'post_author_ibfk_1' // نام احتمالی دیگر
            ],
        ];

        foreach ($foreignKeys as $table => $keys) {
            foreach ($keys as $key) {
                try {
                    // بررسی وجود کلید خارجی قبل از حذف
                    $exists = DB::select("
                        SELECT CONSTRAINT_NAME
                        FROM information_schema.KEY_COLUMN_USAGE
                        WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = '{$table}'
                        AND CONSTRAINT_NAME = '{$key}'
                    ");

                    if (!empty($exists)) {
                        DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$key}");
                        Log::info("Foreign key {$key} dropped from {$table}");
                    }

                } catch (\Exception $e) {
                    Log::warning("Could not drop foreign key {$key} from {$table}: " . $e->getMessage());
                }
            }
        }
    }

    private function addNewForeignKeys()
    {
        try {
            // بررسی وجود جدول posts_new
            $tableExists = DB::select("SHOW TABLES LIKE 'posts_new'");

            if (empty($tableExists)) {
                throw new \Exception('Table posts_new does not exist');
            }

            // اضافه کردن کلید خارجی برای post_images
            DB::statement('
                ALTER TABLE post_images
                ADD CONSTRAINT post_images_post_id_foreign
                FOREIGN KEY (post_id) REFERENCES posts_new(id) ON DELETE CASCADE
            ');
            Log::info('Foreign key added: post_images -> posts_new');

            // اضافه کردن کلید خارجی برای post_author
            DB::statement('
                ALTER TABLE post_author
                ADD CONSTRAINT post_author_post_id_foreign
                FOREIGN KEY (post_id) REFERENCES posts_new(id) ON DELETE CASCADE
            ');
            Log::info('Foreign key added: post_author -> posts_new');

        } catch (\Exception $e) {
            Log::error('Error adding foreign keys: ' . $e->getMessage());
            throw $e;
        }
    }

    public function down(): void
    {
        Log::info('شروع rollback کلیدهای خارجی...');

        $this->dropNewForeignKeys();
        $this->restoreOldForeignKeys();

        Log::info('Rollback کلیدهای خارجی تکمیل شد.');
    }

    private function dropNewForeignKeys()
    {
        try {
            DB::statement('ALTER TABLE post_images DROP FOREIGN KEY post_images_post_id_foreign');
            DB::statement('ALTER TABLE post_author DROP FOREIGN KEY post_author_post_id_foreign');
        } catch (\Exception $e) {
            Log::warning('Error dropping new foreign keys: ' . $e->getMessage());
        }
    }

    private function restoreOldForeignKeys()
    {
        try {
            DB::statement('
                ALTER TABLE post_images
                ADD CONSTRAINT post_images_post_id_foreign
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
            ');

            DB::statement('
                ALTER TABLE post_author
                ADD CONSTRAINT post_author_post_id_foreign
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
            ');
        } catch (\Exception $e) {
            Log::warning('Error restoring old foreign keys: ' . $e->getMessage());
        }
    }
};
