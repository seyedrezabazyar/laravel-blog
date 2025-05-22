<?php

namespace App\Services;

use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Log;
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
     * ایندکس کردن یک کتاب
     */
    public function indexBook(int $postId, array $bookData): bool
    {
        try {
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
            $body = [];
            foreach ($books as $book) {
                $body[] = [
                    'index' => [
                        '_index' => $this->indexName,
                        '_id' => $book['post_id']
                    ]
                ];
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
     * جستجوی کتاب‌ها
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
                'sort' => ['_score' => ['order' => 'desc']]
            ];

            // جستجوی متنی
            if (!empty($query)) {
                $searchBody['query']['bool']['must'][] = [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => [
                            'title^3',           // اولویت بالا برای عنوان
                            'author^2',          // اولویت متوسط برای نویسنده
                            'description.persian^1.5',
                            'description.english^1.5',
                            'category',
                            'publisher'
                        ],
                        'type' => 'best_fields',
                        'fuzziness' => 'AUTO'
                    ]
                ];
            } else {
                $searchBody['query']['bool']['must'][] = ['match_all' => (object)[]];
            }

            // اعمال فیلترها
            if (!empty($filters['format'])) {
                $searchBody['query']['bool']['filter'][] = ['term' => ['format' => $filters['format']]];
            }

            if (!empty($filters['language'])) {
                $searchBody['query']['bool']['filter'][] = ['term' => ['language' => $filters['language']]];
            }

            if (!empty($filters['publication_year'])) {
                $searchBody['query']['bool']['filter'][] = ['term' => ['publication_year' => $filters['publication_year']]];
            }

            if (!empty($filters['year_range'])) {
                $searchBody['query']['bool']['filter'][] = [
                    'range' => [
                        'publication_year' => $filters['year_range']
                    ]
                ];
            }

            $response = $this->client->search([
                'index' => $this->indexName,
                'body' => $searchBody
            ]);

            return [
                'total' => $response['hits']['total']['value'] ?? 0,
                'books' => array_map(function($hit) {
                    return array_merge($hit['_source'], ['score' => $hit['_score']]);
                }, $response['hits']['hits'] ?? [])
            ];

        } catch (\Exception $e) {
            $this->logError(null, 'search', $e->getMessage());
            return ['total' => 0, 'books' => []];
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
                    'size' => 0 // فقط suggestions می‌خواهیم
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
            $this->logError($postId, 'delete', $e->getMessage());
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
                'error_message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to log Elasticsearch error: " . $e->getMessage());
        }
    }
}
