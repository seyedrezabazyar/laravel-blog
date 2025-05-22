<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('key', 100)->unique()->charset('ascii');
            $table->text('value')->nullable()->charset('utf8mb4');
            $table->string('description', 300)->nullable()->charset('utf8mb4');
            $table->enum('type', ['string', 'integer', 'boolean', 'json'])->default('string');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('key');
            $table->index('type');
            $table->index('type');
        });

        // درج تنظیمات پیش‌فرض
        $defaultSettings = [
            [
                'key' => 'site_name',
                'value' => 'کتابستان',
                'description' => 'نام سایت',
                'type' => 'string'
            ],
            [
                'key' => 'posts_per_page',
                'value' => '12',
                'description' => 'تعداد پست در هر صفحه',
                'type' => 'integer'
            ],
            [
                'key' => 'enable_elasticsearch',
                'value' => '1',
                'description' => 'فعال‌سازی Elasticsearch',
                'type' => 'boolean'
            ],
            [
                'key' => 'search_suggestions_count',
                'value' => '5',
                'description' => 'تعداد پیشنهادات جستجو',
                'type' => 'integer'
            ],
            [
                'key' => 'cache_timeout',
                'value' => '3600',
                'description' => 'مدت زمان کش (ثانیه)',
                'type' => 'integer'
            ]
        ];

        foreach ($defaultSettings as $setting) {
            $setting['created_at'] = now();
            $setting['updated_at'] = now();
            DB::table('settings')->insert($setting);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
