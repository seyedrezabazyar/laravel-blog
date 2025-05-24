<?php

namespace App\Services;

use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ElasticsearchError;

class ElasticsearchService
{
    private $client;
    private $indexName = 'books_content'; // نام ایندکس واقعی

    public function __construct()
    {
        try {
            $this->client = ClientBuilder::create()
                ->setHosts([env('ELASTICSEARCH_HOST', 'localhost:9200')])
                ->setRetries(2)
                ->build();
        } catch (\Exception $e) {
            Log::error('Failed to initialize Elasticsearch client: ' . $e->getMessage());
        }
    }

    /**
     * دریافت محتوای یک پست خاص - تطبیق با ساختار واقعی
     */
    public function getPostContent(int $postId): array
    {
        if (!$this->client) {
            return [];
        }

        try {
            // ابتدا سعی کنیم مستقیماً با document ID دریافت کنیم
            try {
                $response = $this->client->get([
                    'index' => $this->indexName,
                    'id' => (string) $postId
                ]);

                if (isset($response['_source'])) {
                    return $response['_source'];
                }
            } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
                // Document با ID مستقیم یافت نشد
            }

            // اگر document یافت نشد، با جستجو امتحان کنیم
            $response = $this->client->search([
                'index' => $this->indexName,
                'body' => [
                    'query' => [
                        'bool' => [
                            'should' => [
                                ['term' => ['post_id' => $postId]],
                                ['term' => ['id' => $postId]]
                            ]
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
            $this->logError($postId, 'get_content', $e->getMessage());
            return [];
        }
    }

    /**
     * ایندکس کردن یک کتاب - ساختار واقعی
     */
    public function indexBook(int $postId, array $bookData): bool
    {
        try {
            if (!$this->client) {
                return false;
            }

            // ساختار واقعی که در Elasticsearch موجود است
            $indexData = [
                'id' => $postId,
                'post_id' => $postId,
                'title_fa' => $bookData['title'] ?? '',
                'title_en' => $bookData['english_title'] ?? '',
                'description_fa' => $bookData['description']['persian'] ?? '',
                'description_en' => $bookData['description']['english'] ?? '',
                'author' => $bookData['author'] ?? '',
                'category' => $bookData['category'] ?? '',
                'publisher' => $bookData['publisher'] ?? '',
                'year' => $bookData['publication_year'] ?? null,
                'format' => $bookData['format'] ?? '',
                'language' => $bookData['language'] ?? '',
                'isbn' => $bookData['isbn'] ?? null,
                'pages_count' => $bookData['pages_count'] ?? null,
            ];

            $this->client->index([
                'index' => $this->indexName,
                'id' => $postId,
                'body' => $indexData
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
            if (!$this->client) {
                return ['success' => 0, 'failed' => count($books), 'errors' => ['Elasticsearch client not available']];
            }

            $body = [];
            foreach ($books as $book) {
                $postId = $book['post_id'] ?? null;
                if (!$postId) {
                    continue;
                }

                $body[] = [
                    'index' => [
                        '_index' => $this->indexName,
                        '_id' => $postId
                    ]
                ];

                // ساختار واقعی
                $indexData = [
                    'id' => $postId,
                    'post_id' => $postId,
                    'title_fa' => $book['title'] ?? '',
                    'title_en' => $book['english_title'] ?? '',
                    'description_fa' => $book['description']['persian'] ?? '',
                    'description_en' => $book['description']['english'] ?? '',
                    'author' => $book['author'] ?? '',
                    'category' => $book['category'] ?? '',
                    'publisher' => $book['publisher'] ?? '',
                    'year' => $book['publication_year'] ?? null,
                    'format' => $book['format'] ?? '',
                    'language' => $book['language'] ?? '',
                    'isbn' => $book['isbn'] ?? null,
                    'pages_count' => $book['pages_count'] ?? null,
                ];

                $body[] = $indexData;
            }

            if (empty($body)) {
                return ['success' => 0, 'failed' => 0, 'errors' => ['No valid books to index']];
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
     * جستجوی کتاب‌ها - ساختار واقعی
     */
    public function searchBooks(string $query, array $filters = [], int $from = 0, int $size = 20): array
    {
        try {
            if (!$this->client) {
                return ['total' => 0, 'books' => []];
            }

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
                        'title_fa' => (object)[],
                        'title_en' => (object)[],
                        'description_fa' => (object)[],
                        'description_en' => (object)[],
                        'author' => (object)[]
                    ]
                ]
            ];

            // جستجوی متنی
            if (!empty($query)) {
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
                                'title_fa^3',
                                'title_en^3',
                                'author^2',
                                'description_fa^1.5',
                                'description_en^1.5',
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
            $searchBody['query']['bool']['filter'][] = ['term' => ['format' => $filters['format']]];
        }

        if (!empty($filters['language'])) {
            $searchBody['query']['bool']['filter'][] = ['term' => ['language' => $filters['language']]];
        }

        if (!empty($filters['category'])) {
            $searchBody['query']['bool']['filter'][] = ['term' => ['category' => $filters['category']]];
        }

        if (!empty($filters['author'])) {
            $searchBody['query']['bool']['filter'][] = ['term' => ['author' => $filters['author']]];
        }

        if (!empty($filters['publisher'])) {
            $searchBody['query']['bool']['filter'][] = ['term' => ['publisher' => $filters['publisher']]];
        }

        if (!empty($filters['publication_year'])) {
            if (is_array($filters['publication_year'])) {
                $searchBody['query']['bool']['filter'][] = [
                    'range' => [
                        'year' => [
                            'gte' => $filters['publication_year']['from'] ?? 1900,
                            'lte' => $filters['publication_year']['to'] ?? date('Y')
                        ]
                    ]
                ];
            } else {
                $searchBody['query']['bool']['filter'][] = ['term' => ['year' => $filters['publication_year']]];
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
     * دریافت آمار ایندکس
     */
    public function getIndexStats(): array
    {
        try {
            if (!$this->client) {
                return [
                    'document_count' => 0,
                    'index_size' => 0,
                    'status' => 'client_error',
                    'error' => 'Elasticsearch client not available'
                ];
            }

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
     * تست اتصال به Elasticsearch
     */
    public function testConnection(): bool
    {
        try {
            if (!$this->client) {
                return false;
            }

            $this->client->ping();
            return true;
        } catch (\Exception $e) {
            Log::error('Elasticsearch connection failed: ' . $e->getMessage());
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
                'error_message' => mb_substr($message, 0, 500)
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to log Elasticsearch error: " . $e->getMessage());
        }
    }
}
