<?php

namespace App\Services;

use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ElasticsearchError;

class ElasticsearchService
{
    private $client;
    private $indexName = 'posts_content';

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts([env('ELASTICSEARCH_HOST', 'localhost:9200')])
            ->setRetries(2)
            ->build();
    }

    /**
     * دریافت محتوای یک پست خاص بر اساس post_id
     */
    public function getPostContent(int $postId): array
    {
        try {
            // ابتدا سعی کنیم مستقیماً با document ID دریافت کنیم
            $response = $this->client->get([
                'index' => $this->indexName,
                'id' => $postId
            ]);

            if (isset($response['_source'])) {
                return $response['_source'];
            }

            return [];

        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            // اگر document یافت نشد، با جستجو امتحان کنیم
            try {
                $response = $this->client->search([
                    'index' => $this->indexName,
                    'body' => [
                        'query' => [
                            'term' => [
                                'post_id' => $postId
                            ]
                        ],
                        'size' => 1
                    ]
                ]);

                if (!empty($response['hits']['hits'])) {
                    return $response['hits']['hits'][0]['_source'];
                }

                return [];

            } catch (\Exception $e) {
                $this->logError($postId, 'get_content_search', $e->getMessage());
                return [];
            }
        } catch (\Exception $e) {
            $this->logError($postId, 'get_content', $e->getMessage());
            return [];
        }
    }

    /**
     * ایجاد ایندکس با تنظیمات فارسی
     */
    public function createIndex(): bool
    {
        try {
            // بررسی وجود ایندکس
            if ($this->client->indices()->exists(['index' => $this->indexName])) {
                return true;
            }

            // دریافت تنظیمات از دیتابیس
            $config = DB::table('elasticsearch_configs')
                ->where('index_name', $this->indexName)
                ->first();

            if (!$config) {
                throw new \Exception("پیکربندی ایندکس {$this->indexName} یافت نشد");
            }

            $params = [
                'index' => $this->indexName,
                'body' => [
                    'settings' => json_decode($config->settings_config, true),
                    'mappings' => json_decode($config->mapping_config, true)
                ]
            ];

            $this->client->indices()->create($params);
            Log::info("ایندکس {$this->indexName} با موفقیت ایجاد شد");

            return true;

        } catch (\Exception $e) {
            Log::error('خطا در ایجاد ایندکس: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * حذف ایندکس
     */
    public function deleteIndex(): bool
    {
        try {
            if ($this->client->indices()->exists(['index' => $this->indexName])) {
                $this->client->indices()->delete(['index' => $this->indexName]);
                Log::info("ایندکس {$this->indexName} حذف شد");
            }
            return true;
        } catch (\Exception $e) {
            Log::error('خطا در حذف ایندکس: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ایندکس کردن یک کتاب
     */
    public function indexBook(int $postId, array $bookData): bool
    {
        try {
            // اطمینان از وجود ایندکس
            $this->createIndex();

            $this->client->index([
                'index' => $this->indexName,
                'id' => $postId,
                'body' => $bookData
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logError($postId, 'index', $e->getMessage());
            return false;
        }
    }

    /**
     * ایندکس کردن چندین کتاب به‌صورت bulk
     */
    public function bulkIndexBooks(array $books): array
    {
        try {
            // اطمینان از وجود ایندکس
            $this->createIndex();

            $body = [];
            foreach ($books as $book) {
                $body[] = [
                    'index' => [
                        '_index' => $this->indexName,
                        '_id' => $book['post_id']
                    ]
                ];
                unset($book['post_id']); // حذف post_id از body
                $body[] = $book;
            }

            $response = $this->client->bulk(['body' => $body]);

            $result = ['success' => 0, 'failed' => 0, 'errors' => []];

            if (isset($response['items'])) {
                foreach ($response['items'] as $item) {
                    if (isset($item['index']['error'])) {
                        $result['failed']++;
                        $result['errors'][] = $item['index']['error'];
                    } else {
                        $result['success']++;
                    }
                }
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Bulk index failed: ' . $e->getMessage());
            return ['success' => 0, 'failed' => count($books), 'errors' => [$e->getMessage()]];
        }
    }

    /**
     * جستجوی کتاب‌ها با قابلیت‌های بهبود یافته
     */
    public function searchBooks(string $query, array $filters = [], int $from = 0, int $size = 20): array
    {
        try {
            $searchBody = [
                'query' => [
                    'bool' => [
                        'must' => [],
                        'filter' => []
                    ]
                ],
                'from' => $from,
                'size' => $size,
                'sort' => ['_score' => ['order' => 'desc']],
                'highlight' => [
                    'fields' => [
                        'title' => (object)[],
                        'description.persian' => (object)[],
                        'description.english' => (object)[],
                        'author' => (object)[]
                    ]
                ]
            ];

            // جستجوی متنی
            if (!empty($query)) {
                // بررسی اینکه آیا جستجو بر اساس post_id است
                if (preg_match('/^post_id:(\d+)$/', $query, $matches)) {
                    $searchBody['query']['bool']['must'][] = [
                        'term' => [
                            'post_id' => (int)$matches[1]
                        ]
                    ];
                } else {
                    $searchBody['query']['bool']['must'][] = [
                        'multi_match' => [
                            'query' => $query,
                            'fields' => [
                                'title^3',
                                'author^2',
                                'description.persian^1.5',
                                'description.english^1.5',
                                'category',
                                'publisher'
                            ],
                            'type' => 'best_fields',
                            'fuzziness' => 'AUTO',
                            'operator' => 'or'
                        ]
                    ];
                }
            } else {
                $searchBody['query']['bool']['must'][] = ['match_all' => (object)[]];
            }

            // اعمال فیلترها
            $this->applyFilters($searchBody, $filters);

            $response = $this->client->search([
                'index' => $this->indexName,
                'body' => $searchBody
            ]);

            return [
                'total' => $response['hits']['total']['value'] ?? 0,
                'books' => array_map(function($hit) {
                    $book = array_merge($hit['_source'], ['score' => $hit['_score']]);

                    // اضافه کردن highlight
                    if (isset($hit['highlight'])) {
                        $book['highlight'] = $hit['highlight'];
                    }

                    return $book;
                }, $response['hits']['hits'] ?? [])
            ];

        } catch (\Exception $e) {
            $this->logError(null, 'search', $e->getMessage());
            return ['total' => 0, 'books' => []];
        }
    }

    /**
     * اعمال فیلترها به جستجو
     */
    private function applyFilters(array &$searchBody, array $filters): void
    {
        if (!empty($filters['format'])) {
            $searchBody['query']['bool']['filter'][] = ['term' => ['format.keyword' => $filters['format']]];
        }

        if (!empty($filters['language'])) {
            $searchBody['query']['bool']['filter'][] = ['term' => ['language' => $filters['language']]];
        }

        if (!empty($filters['category'])) {
            $searchBody['query']['bool']['filter'][] = ['term' => ['category.keyword' => $filters['category']]];
        }

        if (!empty($filters['author'])) {
            $searchBody['query']['bool']['filter'][] = ['term' => ['author.keyword' => $filters['author']]];
        }

        if (!empty($filters['publisher'])) {
            $searchBody['query']['bool']['filter'][] = ['term' => ['publisher.keyword' => $filters['publisher']]];
        }

        if (!empty($filters['publication_year'])) {
            if (is_array($filters['publication_year'])) {
                $searchBody['query']['bool']['filter'][] = [
                    'range' => [
                        'publication_year' => [
                            'gte' => $filters['publication_year']['from'] ?? 1900,
                            'lte' => $filters['publication_year']['to'] ?? date('Y')
                        ]
                    ]
                ];
            } else {
                $searchBody['query']['bool']['filter'][] = ['term' => ['publication_year' => $filters['publication_year']]];
            }
        }

        if (!empty($filters['pages_range'])) {
            $searchBody['query']['bool']['filter'][] = [
                'range' => [
                    'pages_count' => [
                        'gte' => $filters['pages_range']['min'] ?? 0,
                        'lte' => $filters['pages_range']['max'] ?? 10000
                    ]
                ]
            ];
        }
    }

    /**
     * پیشنهاد عنوان برای autocomplete
     */
    public function suggestTitles(string $query, int $size = 10): array
    {
        try {
            $response = $this->client->search([
                'index' => $this->indexName,
                'body' => [
                    'suggest' => [
                        'title_suggest' => [
                            'prefix' => $query,
                            'completion' => [
                                'field' => 'title.suggest',
                                'size' => $size
                            ]
                        ]
                    ],
                    'size' => 0
                ]
            ]);

            $suggestions = [];
            if (isset($response['suggest']['title_suggest'][0]['options'])) {
                foreach ($response['suggest']['title_suggest'][0]['options'] as $option) {
                    $suggestions[] = $option['text'];
                }
            }

            return $suggestions;

        } catch (\Exception $e) {
            $this->logError(null, 'suggest', $e->getMessage());
            return [];
        }
    }

    /**
     * دریافت آمار ایندکس
     */
    public function getIndexStats(): array
    {
        try {
            $response = $this->client->indices()->stats(['index' => $this->indexName]);

            return [
                'document_count' => $response['indices'][$this->indexName]['total']['docs']['count'] ?? 0,
                'index_size' => $response['indices'][$this->indexName]['total']['store']['size_in_bytes'] ?? 0,
                'status' => 'healthy'
            ];
        } catch (\Exception $e) {
            return [
                'document_count' => 0,
                'index_size' => 0,
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * حذف کتاب از ایندکس
     */
    public function deleteBook(int $postId): bool
    {
        try {
            $this->client->delete([
                'index' => $this->indexName,
                'id' => $postId
            ]);

            return true;

        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'not_found') === false) {
                $this->logError($postId, 'delete', $e->getMessage());
            }
            return false;
        }
    }

    /**
     * ثبت خطاها
     */
    private function logError(?int $postId, string $action, string $message): void
    {
        try {
            ElasticsearchError::create([
                'post_id' => $postId,
                'action' => $action,
                'error_message' => mb_substr($message, 0, 500) // محدود کردن طول پیام
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to log Elasticsearch error: " . $e->getMessage());
        }
    }

    /**
     * تست اتصال به Elasticsearch
     */
    public function testConnection(): bool
    {
        try {
            $this->client->ping();
            return true;
        } catch (\Exception $e) {
            Log::error('Elasticsearch connection failed: ' . $e->getMessage());
            return false;
        }
    }
}
