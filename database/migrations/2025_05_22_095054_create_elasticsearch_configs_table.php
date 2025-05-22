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
            $table->unsignedInteger('document_count')->default(0);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['index_name', 'status']);
        });

        // درج تنظیمات ساده برای posts_content
        DB::table('elasticsearch_configs')->insert([
            'index_name' => 'posts_content',
            'mapping_config' => json_encode([
                'properties' => [
                    'post_id' => ['type' => 'integer'],

                    // توضیحات کتاب (فارسی و انگلیسی)
                    'description' => [
                        'properties' => [
                            'persian' => [
                                'type' => 'text',
                                'analyzer' => 'persian_analyzer'
                            ],
                            'english' => [
                                'type' => 'text',
                                'analyzer' => 'english'
                            ]
                        ]
                    ],

                    // فیلدهای جستجو (از MySQL کپی می‌شوند برای سرعت)
                    'title' => [
                        'type' => 'text',
                        'analyzer' => 'persian_analyzer',
                        'fields' => [
                            'keyword' => ['type' => 'keyword'],
                            'suggest' => [
                                'type' => 'completion',
                                'analyzer' => 'persian_analyzer'
                            ]
                        ]
                    ],

                    'author' => [
                        'type' => 'text',
                        'analyzer' => 'persian_analyzer',
                        'fields' => ['keyword' => ['type' => 'keyword']]
                    ],

                    'category' => [
                        'type' => 'text',
                        'analyzer' => 'persian_analyzer',
                        'fields' => ['keyword' => ['type' => 'keyword']]
                    ],

                    'publisher' => [
                        'type' => 'text',
                        'analyzer' => 'persian_analyzer',
                        'fields' => ['keyword' => ['type' => 'keyword']]
                    ],

                    // فیلدهای فیلتر و جستجو
                    'publication_year' => ['type' => 'integer'],
                    'format' => ['type' => 'keyword'],
                    'language' => ['type' => 'keyword'],
                    'isbn' => ['type' => 'keyword'],
                    'pages_count' => ['type' => 'integer']
                ]
            ]),
            'settings_config' => json_encode([
                'number_of_shards' => 1,    // برای یک سرور
                'number_of_replicas' => 0,  // برای یک سرور
                'refresh_interval' => '30s',
                'max_result_window' => 10000,

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
                                'یک', 'برای', 'تا', 'بر', 'کرد', 'شد', 'نیز', 'پس', 'اما'
                            ]
                        ],
                        'persian_normalize_filter' => [
                            'type' => 'persian_normalization'
                        ]
                    ]
                ]
            ]),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('elasticsearch_configs');
    }
};
