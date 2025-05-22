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

        // درج تنظیمات پیش‌فرض برای posts_content
        DB::table('elasticsearch_configs')->insert([
            'index_name' => 'posts_content',
            'mapping_config' => json_encode([
                'properties' => [
                    'post_id' => ['type' => 'integer'],
                    'content' => [
                        'properties' => [
                            'fa' => [
                                'type' => 'text',
                                'analyzer' => 'persian_analyzer',
                                'search_analyzer' => 'persian_analyzer'
                            ],
                            'en' => [
                                'type' => 'text',
                                'analyzer' => 'english_analyzer'
                            ]
                        ]
                    ],
                    'metadata' => [
                        'properties' => [
                            'title' => [
                                'type' => 'text',
                                'analyzer' => 'persian_analyzer',
                                'fields' => [
                                    'keyword' => ['type' => 'keyword'],
                                    'suggest' => ['type' => 'completion', 'analyzer' => 'persian_analyzer']
                                ]
                            ],
                            'english_title' => [
                                'type' => 'text',
                                'analyzer' => 'english_analyzer'
                            ],
                            'author' => ['type' => 'keyword'],
                            'category' => ['type' => 'keyword'],
                            'publisher' => ['type' => 'keyword'],
                            'publication_year' => ['type' => 'integer'],
                            'language' => ['type' => 'keyword'],
                            'format' => ['type' => 'keyword']
                        ]
                    ],
                    'search_data' => [
                        'properties' => [
                            'summary' => [
                                'type' => 'text',
                                'analyzer' => 'persian_analyzer'
                            ],
                            'keywords' => ['type' => 'keyword'],
                            'topics' => ['type' => 'keyword']
                        ]
                    ],
                    'statistics' => [
                        'properties' => [
                            'word_count' => ['type' => 'integer'],
                            'reading_time' => ['type' => 'integer'],
                            'content_length' => ['type' => 'integer']
                        ]
                    ],
                    'timestamps' => [
                        'properties' => [
                            'created_at' => ['type' => 'date'],
                            'updated_at' => ['type' => 'date'],
                            'indexed_at' => ['type' => 'date']
                        ]
                    ]
                ]
            ]),
            'settings_config' => json_encode([
                'number_of_shards' => 2,
                'number_of_replicas' => 1,
                'refresh_interval' => '30s',
                'analysis' => [
                    'analyzer' => [
                        'persian_analyzer' => [
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => [
                                'lowercase',
                                'persian_stop',
                                'persian_normalizer'
                            ]
                        ],
                        'english_analyzer' => [
                            'type' => 'english'
                        ]
                    ],
                    'filter' => [
                        'persian_stop' => [
                            'type' => 'stop',
                            'stopwords' => [
                                'و', 'در', 'به', 'از', 'که', 'با', 'این', 'آن', 'را', 'است',
                                'یک', 'برای', 'تا', 'کی', 'چه', 'چی', 'کجا', 'کدام'
                            ]
                        ],
                        'persian_normalizer' => [
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
