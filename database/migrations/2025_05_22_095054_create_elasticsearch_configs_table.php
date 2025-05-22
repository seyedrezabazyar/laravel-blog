ا
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

        // درج تنظیمات بهینه شده برای posts_content
        DB::table('elasticsearch_configs')->insert([
            'index_name' => 'posts_content',
            'mapping_config' => json_encode([
                'properties' => [
                    'post_id' => ['type' => 'integer'],

                    // محتوای کتاب
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

                    // اطلاعات اصلی کتاب
                    'metadata' => [
                        'properties' => [
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
                            'english_title' => [
                                'type' => 'text',
                                'analyzer' => 'english_analyzer',
                                'fields' => [
                                    'keyword' => ['type' => 'keyword']
                                ]
                            ],
                            'author' => [
                                'type' => 'text',
                                'analyzer' => 'persian_analyzer',
                                'fields' => [
                                    'keyword' => ['type' => 'keyword']
                                ]
                            ],
                            'category' => [
                                'type' => 'text',
                                'analyzer' => 'persian_analyzer',
                                'fields' => [
                                    'keyword' => ['type' => 'keyword']
                                ]
                            ],
                            'publisher' => [
                                'type' => 'text',
                                'analyzer' => 'persian_analyzer',
                                'fields' => [
                                    'keyword' => ['type' => 'keyword']
                                ]
                            ],
                            'publication_year' => ['type' => 'integer'],
                            'language' => ['type' => 'keyword'],
                            'format' => ['type' => 'keyword'],
                            'book_codes' => [
                                'type' => 'text',
                                'analyzer' => 'keyword',
                                'fields' => [
                                    'search' => [
                                        'type' => 'text',
                                        'analyzer' => 'standard'
                                    ]
                                ]
                            ]
                        ]
                    ],

                    // داده‌های جستجو
                    'search_data' => [
                        'properties' => [
                            'summary' => [
                                'type' => 'text',
                                'analyzer' => 'persian_analyzer'
                            ],
                            'keywords' => ['type' => 'keyword'],
                            'topics' => ['type' => 'keyword']
                        ]
                    ]
                ]
            ]),
            'settings_config' => json_encode([
                'number_of_shards' => 2,
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
                                'persian_stop',
                                'persian_normalizer',
                                'persian_stemmer'
                            ]
                        ],
                        'english_analyzer' => [
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => [
                                'lowercase',
                                'stop',
                                'stemmer'
                            ]
                        ]
                    ],

                    'filter' => [
                        'persian_stop' => [
                            'type' => 'stop',
                            'stopwords' => [
                                'و', 'در', 'به', 'از', 'که', 'با', 'این', 'آن', 'را', 'است',
                                'یک', 'برای', 'تا', 'کی', 'چه', 'چی', 'کجا', 'کدام', 'هم',
                                'نیز', 'یا', 'اما', 'ولی', 'پس', 'اگر', 'چون', 'زیرا'
                            ]
                        ],
                        'persian_normalizer' => [
                            'type' => 'persian_normalization'
                        ],
                        'persian_stemmer' => [
                            'type' => 'stemmer',
                            'language' => 'light_english'
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
