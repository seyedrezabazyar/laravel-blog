<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elasticsearch_configs', function (Blueprint $table) {
            $table->id();
            $table->string('index_name', 100)->unique()->charset('ascii');
            $table->json('mapping_config');
            $table->json('settings_config');
            $table->enum('status', ['active', 'inactive', 'rebuilding'])->default('active');
            $table->unsignedBigInteger('document_count')->default(0);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['index_name', 'status']);
        });

        // تنظیمات Elasticsearch برای محتوای کتاب‌ها
        DB::table('elasticsearch_configs')->insert([
            'index_name' => 'books_content',
            'mapping_config' => json_encode([
                'properties' => [
                    'id' => ['type' => 'long'],
                    'post_id' => ['type' => 'long'],

                    // عنوان‌ها (فارسی و انگلیسی) - فقط در Elasticsearch
                    'title_fa' => [
                        'type' => 'text',
                        'analyzer' => 'persian_analyzer',
                        'fields' => [
                            'keyword' => ['type' => 'keyword'],
                            'suggest' => ['type' => 'completion']
                        ]
                    ],
                    'title_en' => [
                        'type' => 'text',
                        'analyzer' => 'english',
                        'fields' => [
                            'keyword' => ['type' => 'keyword'],
                            'suggest' => ['type' => 'completion']
                        ]
                    ],

                    // توضیحات (فارسی و انگلیسی) - فقط در Elasticsearch
                    'description_fa' => [
                        'type' => 'text',
                        'analyzer' => 'persian_analyzer'
                    ],
                    'description_en' => [
                        'type' => 'text',
                        'analyzer' => 'english'
                    ],

                    // فیلدهای کپی‌شده از MySQL برای فیلتر سریع
                    'category' => ['type' => 'keyword'],
                    'author' => ['type' => 'keyword'],
                    'publisher' => ['type' => 'keyword'],
                    'format' => ['type' => 'keyword'],
                    'language' => ['type' => 'keyword'],
                    'year' => ['type' => 'integer'],
                    'pages_count' => ['type' => 'integer'],

                    // فیلدهای فقط در Elasticsearch - کاهش حجم MySQL
                    'isbn' => ['type' => 'keyword'],
                    'edition' => ['type' => 'keyword'],

                    // متادیتای اضافی
                    'file_size' => ['type' => 'long'],
                    'download_count' => ['type' => 'integer'],
                    'rating' => ['type' => 'float'],

                    // فیلدهای زمانی
                    'created_at' => ['type' => 'date'],
                    'updated_at' => ['type' => 'date']
                ]
            ]),
            'settings_config' => json_encode([
                'number_of_shards' => 5,    // برای میلیون‌ها رکورد
                'number_of_replicas' => 1,
                'refresh_interval' => '30s',
                'max_result_window' => 50000,

                'analysis' => [
                    'analyzer' => [
                        'persian_analyzer' => [
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => [
                                'lowercase',
                                'persian_stop_filter',
                                'persian_normalize_filter'
                            ]
                        ]
                    ],

                    'filter' => [
                        'persian_stop_filter' => [
                            'type' => 'stop',
                            'stopwords' => [
                                'و', 'در', 'به', 'از', 'که', 'با', 'این', 'آن', 'را', 'است',
                                'یک', 'برای', 'تا', 'بر', 'کرد', 'شد', 'نیز', 'پس', 'اما', 'کتاب'
                            ]
                        ],
                        'persian_normalize_filter' => [
                            'type' => 'persian_normalization'
                        ]
                    ]
                ]
            ]),
            'status' => 'active',
            'document_count' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('elasticsearch_configs');
    }
};
