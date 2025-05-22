<?php

namespace App\Http\Controllers;

use App\Services\ElasticsearchService;
use App\Models\Post;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    protected $elasticsearchService;

    public function __construct(ElasticsearchService $elasticsearchService)
    {
        $this->elasticsearchService = $elasticsearchService;
    }

    /**
     * صفحه جستجوی اصلی
     */
    public function index(Request $request)
    {
        $query = $request->input('q', '');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;
        $from = ($page - 1) * $perPage;

        $results = ['total' => 0, 'books' => []];
        $searchTime = 0;

        if (!empty($query)) {
            $startTime = microtime(true);

            // دریافت فیلترها
            $filters = [
                'format' => $request->input('format'),
                'language' => $request->input('language'),
                'category' => $request->input('category'),
                'author' => $request->input('author'),
                'publisher' => $request->input('publisher'),
            ];

            // فیلتر سال انتشار
            if ($request->has('year_from') || $request->has('year_to')) {
                $filters['publication_year'] = [
                    'from' => $request->input('year_from', 1900),
                    'to' => $request->input('year_to', date('Y'))
                ];
            }

            // حذف فیلترهای خالی
            $filters = array_filter($filters, function($value) {
                return !empty($value);
            });

            try {
                $results = $this->elasticsearchService->searchBooks($query, $filters, $from, $perPage);
                $searchTime = round((microtime(true) - $startTime) * 1000, 2);

                // اضافه کردن اطلاعات اضافی از MySQL
                $results['books'] = $this->enrichSearchResults($results['books']);

            } catch (\Exception $e) {
                Log::error('خطا در جستجوی Elasticsearch: ' . $e->getMessage());

                // بازگشت به جستجوی معمولی MySQL
                $results = $this->fallbackToMysqlSearch($query, $from, $perPage);
                $searchTime = 0;
            }
        }

        // محاسبه اطلاعات صفحه‌بندی
        $totalPages = ceil($results['total'] / $perPage);
        $hasNextPage = $page < $totalPages;
        $hasPrevPage = $page > 1;

        // دریافت فیلترهای موجود برای نمایش در sidebar
        $availableFilters = $this->getAvailableFilters();

        return view('search.index', compact(
            'query', 'results', 'page', 'totalPages', 'hasNextPage', 'hasPrevPage',
            'searchTime', 'perPage', 'availableFilters'
        ));
    }

    /**
     * API برای autocomplete
     */
    public function autocomplete(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $cacheKey = 'autocomplete_' . md5($query);

        $suggestions = Cache::remember($cacheKey, 300, function () use ($query) {
            try {
                return $this->elasticsearchService->suggestTitles($query, 8);
            } catch (\Exception $e) {
                Log::error('خطا در autocomplete: ' . $e->getMessage());

                // بازگشت به جستجوی MySQL
                return Post::where('title', 'like', "%{$query}%")
                    ->where('is_published', true)
                    ->where('hide_content', false)
                    ->select('title')
                    ->limit(8)
                    ->pluck('title')
                    ->toArray();
            }
        });

        return response()->json($suggestions);
    }

    /**
     * جستجوی پیشرفته
     */
    public function advanced(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'format' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:50',
            'year_from' => 'nullable|integer|min:1800|max:' . date('Y'),
            'year_to' => 'nullable|integer|min:1800|max:' . date('Y'),
            'pages_min' => 'nullable|integer|min:1',
            'pages_max' => 'nullable|integer|min:1',
        ]);

        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;
        $from = ($page - 1) * $perPage;

        // ساخت query متنی
        $queryParts = [];
        if (!empty($validated['title'])) {
            $queryParts[] = $validated['title'];
        }
        if (!empty($validated['author'])) {
            $queryParts[] = $validated['author'];
        }

        $query = implode(' ', $queryParts);

        // ساخت فیلترها
        $filters = [];

        if (!empty($validated['category'])) {
            $filters['category'] = $validated['category'];
        }
        if (!empty($validated['publisher'])) {
            $filters['publisher'] = $validated['publisher'];
        }
        if (!empty($validated['format'])) {
            $filters['format'] = $validated['format'];
        }
        if (!empty($validated['language'])) {
            $filters['language'] = $validated['language'];
        }

        // فیلتر سال
        if (!empty($validated['year_from']) || !empty($validated['year_to'])) {
            $filters['publication_year'] = [
                'from' => $validated['year_from'] ?? 1800,
                'to' => $validated['year_to'] ?? date('Y')
            ];
        }

        // فیلتر تعداد صفحات
        if (!empty($validated['pages_min']) || !empty($validated['pages_max'])) {
            $filters['pages_range'] = [
                'min' => $validated['pages_min'] ?? 1,
                'max' => $validated['pages_max'] ?? 10000
            ];
        }

        try {
            $results = $this->elasticsearchService->searchBooks($query, $filters, $from, $perPage);
            $results['books'] = $this->enrichSearchResults($results['books']);
        } catch (\Exception $e) {
            Log::error('خطا در جستجوی پیشرفته: ' . $e->getMessage());
            $results = ['total' => 0, 'books' => []];
        }

        $totalPages = ceil($results['total'] / $perPage);
        $availableFilters = $this->getAvailableFilters();

        return view('search.advanced', compact(
            'results', 'validated', 'page', 'totalPages', 'perPage', 'availableFilters'
        ));
    }

    /**
     * غنی‌سازی نتایج جستجو با اطلاعات اضافی از MySQL
     */
    private function enrichSearchResults(array $books): array
    {
        if (empty($books)) {
            return [];
        }

        // استخراج شناسه‌های پست از نتایج Elasticsearch
        $postIds = array_column($books, 'post_id');

        // دریافت اطلاعات کامل از MySQL
        $posts = Post::whereIn('id', $postIds)
            ->with(['category:id,name,slug', 'author:id,name,slug', 'publisher:id,name,slug', 'featuredImage'])
            ->get()
            ->keyBy('id');

        // ترکیب داده‌های Elasticsearch با MySQL
        foreach ($books as &$book) {
            $postId = $book['post_id'] ?? null;
            if ($postId && isset($posts[$postId])) {
                $post = $posts[$postId];
                $book['post'] = $post;
                $book['url'] = route('blog.show', $post->slug);
                $book['category_url'] = $post->category ? route('blog.category', $post->category->slug) : null;
                $book['author_url'] = $post->author ? route('blog.author', $post->author->slug) : null;
                $book['publisher_url'] = $post->publisher ? route('blog.publisher', $post->publisher->slug) : null;
                $book['image_url'] = $post->featuredImage ? $post->featuredImage->display_url : asset('images/default-book.png');
            }
        }

        return $books;
    }

    /**
     * بازگشت به جستجوی MySQL در صورت خطا در Elasticsearch
     */
    private function fallbackToMysqlSearch(string $query, int $from, int $perPage): array
    {
        $posts = Post::where('title', 'like', "%{$query}%")
            ->orWhere('english_title', 'like', "%{$query}%")
            ->orWhere('book_codes', 'like', "%{$query}%")
            ->where('is_published', true)
            ->where('hide_content', false)
            ->with(['category:id,name,slug', 'author:id,name,slug', 'publisher:id,name,slug', 'featuredImage'])
            ->offset($from)
            ->limit($perPage)
            ->get();

        $total = Post::where('title', 'like', "%{$query}%")
            ->orWhere('english_title', 'like', "%{$query}%")
            ->orWhere('book_codes', 'like', "%{$query}%")
            ->where('is_published', true)
            ->where('hide_content', false)
            ->count();

        $books = $posts->map(function ($post) {
            return [
                'post_id' => $post->id,
                'title' => $post->title,
                'author' => $post->author ? $post->author->name : '',
                'category' => $post->category ? $post->category->name : '',
                'publisher' => $post->publisher ? $post->publisher->name : '',
                'publication_year' => $post->publication_year,
                'format' => $post->format,
                'score' => 1.0,
                'post' => $post,
                'url' => route('blog.show', $post->slug),
                'category_url' => $post->category ? route('blog.category', $post->category->slug) : null,
                'author_url' => $post->author ? route('blog.author', $post->author->slug) : null,
                'publisher_url' => $post->publisher ? route('blog.publisher', $post->publisher->slug) : null,
                'image_url' => $post->featuredImage ? $post->featuredImage->display_url : asset('images/default-book.png'),
            ];
        })->toArray();

        return ['total' => $total, 'books' => $books];
    }

    /**
     * دریافت فیلترهای موجود
     */
    private function getAvailableFilters(): array
    {
        return Cache::remember('search_filters', 3600, function () {
            return [
                'formats' => Post::select('format')
                    ->where('is_published', true)
                    ->where('hide_content', false)
                    ->whereNotNull('format')
                    ->distinct()
                    ->pluck('format')
                    ->filter()
                    ->values()
                    ->toArray(),

                'languages' => Post::select('languages')
                    ->where('is_published', true)
                    ->where('hide_content', false)
                    ->whereNotNull('languages')
                    ->distinct()
                    ->pluck('languages')
                    ->filter()
                    ->values()
                    ->toArray(),

                'years' => [
                    'min' => Post::where('is_published', true)
                            ->where('hide_content', false)
                            ->whereNotNull('publication_year')
                            ->min('publication_year') ?? 1900,
                    'max' => Post::where('is_published', true)
                            ->where('hide_content', false)
                            ->whereNotNull('publication_year')
                            ->max('publication_year') ?? date('Y')
                ],

                'categories' => Category::select('name', 'slug')
                    ->where('posts_count', '>', 0)
                    ->orderBy('posts_count', 'desc')
                    ->limit(20)
                    ->get()
                    ->toArray(),

                'authors' => Author::select('name', 'slug')
                    ->where('posts_count', '>', 0)
                    ->orderBy('posts_count', 'desc')
                    ->limit(20)
                    ->get()
                    ->toArray(),

                'publishers' => Publisher::select('name', 'slug')
                    ->where('posts_count', '>', 0)
                    ->orderBy('posts_count', 'desc')
                    ->limit(20)
                    ->get()
                    ->toArray(),
            ];
        });
    }

    /**
     * آمار جستجو برای داشبورد
     */
    public function stats()
    {
        // بررسی دسترسی مدیر
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'دسترسی غیرمجاز');
        }

        $stats = $this->elasticsearchService->getIndexStats();

        $mysqlStats = [
            'total_posts' => Post::where('is_published', true)->where('hide_content', false)->count(),
            'indexed_posts' => Post::where('is_indexed', true)->count(),
            'pending_posts' => Post::where('is_published', true)->where('hide_content', false)->where('is_indexed', false)->count(),
        ];

        $recentErrors = \App\Models\ElasticsearchError::with('post:id,title')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.search.stats', compact('stats', 'mysqlStats', 'recentErrors'));
    }

    /**
     * بازنمایه‌سازی دستی
     */
    public function reindex(Request $request)
    {
        // بررسی دسترسی مدیر
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'دسترسی غیرمجاز'], 403);
        }

        $postId = $request->input('post_id');

        if ($postId) {
            // بازنمایه‌سازی یک پست خاص
            $post = Post::findOrFail($postId);
            $success = $this->reindexSinglePost($post);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'پست با موفقیت بازنمایه‌سازی شد' : 'خطا در بازنمایه‌سازی پست'
            ]);
        }

        // شروع بازنمایه‌سازی کامل در پس‌زمینه
        try {
            \Artisan::queue('elasticsearch:index', ['--force' => true]);

            return response()->json([
                'success' => true,
                'message' => 'بازنمایه‌سازی کامل در پس‌زمینه شروع شد'
            ]);
        } catch (\Exception $e) {
            Log::error('خطا در شروع بازنمایه‌سازی: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'خطا در شروع بازنمایه‌سازی: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * بازنمایه‌سازی یک پست
     */
    private function reindexSinglePost(Post $post): bool
    {
        try {
            $post->load(['category', 'author', 'authors', 'publisher']);

            $bookData = [
                'title' => $post->title,
                'description' => [
                    'persian' => strip_tags($post->content ?? ''),
                    'english' => strip_tags($post->english_content ?? '')
                ],
                'author' => $this->getPostAuthorsNames($post),
                'category' => $post->category ? $post->category->name : '',
                'publisher' => $post->publisher ? $post->publisher->name : '',
                'publication_year' => $post->publication_year,
                'format' => $post->format,
                'language' => $post->languages ?? 'fa',
                'isbn' => $post->isbn,
                'pages_count' => $post->pages_count
            ];

            $success = $this->elasticsearchService->indexBook($post->id, $bookData);

            if ($success) {
                $post->update(['is_indexed' => true, 'indexed_at' => now()]);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('خطا در بازنمایه‌سازی پست ' . $post->id . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * دریافت نام تمام نویسندگان پست
     */
    private function getPostAuthorsNames(Post $post): string
    {
        $names = [];

        // نویسنده اصلی
        if ($post->author) {
            $names[] = $post->author->name;
        }

        // نویسندگان همکار
        if ($post->authors && $post->authors->count() > 0) {
            $coAuthors = $post->authors->pluck('name')->toArray();
            $names = array_merge($names, $coAuthors);
        }

        // حذف تکراری‌ها
        $names = array_unique($names);

        return implode(' ', $names);
    }

    /**
     * صفحه جستجوی پیشرفته (فرم)
     */
    public function showAdvanced()
    {
        $availableFilters = $this->getAvailableFilters();
        return view('search.advanced-form', compact('availableFilters'));
    }
}
