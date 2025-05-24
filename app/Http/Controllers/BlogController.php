<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class BlogController extends Controller
{
    protected $cacheTtl = 3600; // 1 ساعت

    /**
     * نمایش صفحه اصلی وبلاگ
     */
    public function index(): View
    {
        // دریافت آخرین پست‌ها با کش
        $posts = Cache::remember('home_latest_posts', $this->cacheTtl, function () {
            try {
                return Post::visibleToUser()
                    ->forListing()
                    ->with([
                        'category:id,name,slug',
                        'author:id,name,slug',
                        'publisher:id,name,slug',
                        'featuredImage'
                    ])
                    ->latest('created_at')
                    ->take(12)
                    ->get();
            } catch (\Exception $e) {
                \Log::error('خطا در دریافت آخرین پست‌ها: ' . $e->getMessage());
                return collect(); // مجموعه خالی برگردان
            }
        });

        // دسته‌بندی‌های ثابت برای صفحه اصلی
        $categories = [
            (object) ['name' => 'رمان', 'slug' => 'roman', 'icon' => 'book', 'description' => 'داستان‌های خیال‌انگیز'],
            (object) ['name' => 'علمی', 'slug' => 'scientific', 'icon' => 'academic-cap', 'description' => 'دانش و پژوهش'],
            (object) ['name' => 'تاریخی', 'slug' => 'historical', 'icon' => 'clock', 'description' => 'گذشته را بشناسید'],
            (object) ['name' => 'فلسفه', 'slug' => 'philosophy', 'icon' => 'question-mark-circle', 'description' => 'اندیشه و تفکر'],
            (object) ['name' => 'روانشناسی', 'slug' => 'psychology', 'icon' => 'user-group', 'description' => 'شناخت ذهن و رفتار'],
            (object) ['name' => 'کودک', 'slug' => 'children', 'icon' => 'puzzle', 'description' => 'برای نسل آینده'],
            (object) ['name' => 'موفقیت', 'slug' => 'success', 'icon' => 'chart-bar', 'description' => 'توسعه فردی و حرفه‌ای'],
            (object) ['name' => 'هنر', 'slug' => 'art', 'icon' => 'pencil', 'description' => 'خلاقیت و زیبایی'],
        ];

        return view('blog.index', compact('posts', 'categories'));
    }

    /**
     * نمایش تمام دسته‌بندی‌ها
     */
    public function categories(): View
    {
        $categories = Cache::remember('all_categories', $this->cacheTtl, function () {
            return Category::select(['id', 'name', 'slug', 'posts_count'])
                ->where('posts_count', '>', 0)
                ->orderByDesc('posts_count')
                ->get();
        });

        $popularCategories = $categories->take(5);

        return view('blog.categories', compact('categories', 'popularCategories'));
    }

    /**
     * نمایش جزئیات پست
     */
    public function show(Post $post): View
    {
        // بررسی مجوزهای دسترسی
        $isAdmin = auth()->check() && auth()->user()->isAdmin();

        if (!$post->is_published || ($post->hide_content && !$isAdmin)) {
            abort(404);
        }

        // بارگذاری روابط مورد نیاز
        $post->load([
            'category:id,name,slug',
            'featuredImage',
            'author:id,name,slug',
            'authors:id,name,slug',
            'publisher:id,name,slug',
        ]);

        // فورس کردن لود شدن همه داده‌های Elasticsearch
        try {
            // بارگذاری داده‌های Elasticsearch
            $post->purified_content;
            $post->english_content;
            $post->elasticsearch_title;
            $post->elasticsearch_author;
            $post->elasticsearch_category;
            $post->elasticsearch_publisher;
            $post->elasticsearch_publication_year;
            $post->elasticsearch_format;
            $post->elasticsearch_language;
            $post->elasticsearch_isbn;
            $post->elasticsearch_pages_count;
        } catch (\Exception $e) {
            \Log::warning("خطا در بارگذاری داده‌های Elasticsearch برای پست {$post->id}: " . $e->getMessage());
        }

        // دریافت پست‌های مرتبط با کش
        $cacheKey = "post_{$post->id}_related_posts_" . ($isAdmin ? 'admin' : 'user');

        $relatedPosts = Cache::remember($cacheKey, $this->cacheTtl, function () use ($post, $isAdmin) {
            return $this->getRelatedPosts($post, $isAdmin);
        });

        return view('blog.show', compact('post', 'relatedPosts'));
    }

    /**
     * دریافت پست‌های مرتبط
     */
    private function getRelatedPosts(Post $post, bool $isAdmin = false)
    {
        $query = Post::where('id', '!=', $post->id)
            ->forListing()
            ->with(['featuredImage']);

        if ($isAdmin) {
            $query->visibleToAdmin();
        } else {
            $query->visibleToUser();
        }

        // ابتدا از همان دسته‌بندی
        $categoryPosts = (clone $query)
            ->where('category_id', $post->category_id)
            ->latest('created_at')
            ->limit(6)
            ->get();

        if ($categoryPosts->count() >= 6) {
            return $categoryPosts;
        }

        // اگر کافی نبود، از سایر دسته‌بندی‌ها
        $otherPosts = (clone $query)
            ->whereNotIn('id', $categoryPosts->pluck('id')->toArray())
            ->latest('created_at')
            ->limit(6 - $categoryPosts->count())
            ->get();

        return $categoryPosts->merge($otherPosts);
    }

    /**
     * نمایش پست‌های یک دسته‌بندی
     */
    public function category(Category $category): View
    {
        $isAdmin = auth()->check() && auth()->user()->isAdmin();

        $query = Post::where('category_id', $category->id)
            ->forListing()
            ->with([
                'featuredImage',
                'author:id,name,slug'
            ]);

        if ($isAdmin) {
            $query->visibleToAdmin();
        } else {
            $query->visibleToUser();
        }

        $posts = $query->latest('created_at')->simplePaginate(12);

        return view('blog.category', compact('posts', 'category'));
    }

    /**
     * نمایش پست‌های یک نویسنده
     */
    public function author(Author $author): View
    {
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $perPage = 12;
        $currentPage = request()->get('page', 1);

        // پست‌های نویسنده اصلی
        $mainPostsQuery = Post::where('author_id', $author->id)->forListing();

        // پست‌های نویسنده همکار
        $coAuthorPostsQuery = Post::select([
            'posts.id', 'posts.title', 'posts.slug', 'posts.category_id',
            'posts.author_id', 'posts.publication_year', 'posts.format',
            'posts.created_at', 'posts.md5'
        ])
            ->join('post_author', 'posts.id', '=', 'post_author.post_id')
            ->where('post_author.author_id', $author->id)
            ->where('posts.author_id', '!=', $author->id);

        if ($isAdmin) {
            $mainPostsQuery->visibleToAdmin();
            $coAuthorPostsQuery->where('posts.is_published', true);
        } else {
            $mainPostsQuery->visibleToUser();
            $coAuthorPostsQuery->where('posts.is_published', true)
                ->where('posts.hide_content', false);
        }

        // گرفتن تمام پست‌ها
        $mainPosts = $mainPostsQuery->get();
        $coAuthorPosts = $coAuthorPostsQuery->get();

        // ترکیب و مرتب‌سازی
        $allPosts = $mainPosts->merge($coAuthorPosts)
            ->sortByDesc('created_at')
            ->values();

        // ایجاد pagination دستی
        $total = $allPosts->count();
        $offset = ($currentPage - 1) * $perPage;
        $paginatedItems = $allPosts->slice($offset, $perPage);

        // بارگذاری روابط برای پست‌های صفحه‌بندی شده
        $postIds = $paginatedItems->pluck('id')->toArray();
        if (!empty($postIds)) {
            $postsWithRelations = Post::whereIn('id', $postIds)
                ->with([
                    'featuredImage',
                    'category:id,name,slug'
                ])
                ->get()
                ->keyBy('id');

            // جایگزینی پست‌ها با نسخه‌های دارای رابطه
            $paginatedItems = $paginatedItems->map(function($post) use ($postsWithRelations) {
                return $postsWithRelations[$post->id] ?? $post;
            });
        }

        // ایجاد LengthAwarePaginator
        $posts = new LengthAwarePaginator(
            $paginatedItems,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );

        return view('blog.author', compact('posts', 'author'));
    }

    /**
     * نمایش پست‌های یک ناشر
     */
    public function publisher(Publisher $publisher): Response
    {
        $isAdmin = auth()->check() && auth()->user()->isAdmin();

        $query = Post::where('publisher_id', $publisher->id)
            ->forListing()
            ->with([
                'featuredImage',
                'author:id,name,slug'
            ]);

        if ($isAdmin) {
            $query->visibleToAdmin();
        } else {
            $query->visibleToUser();
        }

        $posts = $query->latest('created_at')->simplePaginate(12);

        $response = response()->view('blog.publisher', compact('posts', 'publisher'));

        // تنظیم هدرهای کش
        $etag = md5($publisher->id . request()->get('page', 1) . $isAdmin . time() / 3600);
        $response->header('Cache-Control', 'public, max-age=300');
        $response->header('ETag', $etag);

        return $response;
    }

    /**
     * جستجو در وبلاگ
     */
    public function search(Request $request): View
    {
        $query = $request->input('q');

        if (empty($query)) {
            return redirect()->route('blog.index');
        }

        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $cacheKey = 'search_results_' . md5($query . '_page_' . $request->get('page', 1) . '_' . ($isAdmin ? 'admin' : 'user'));

        $posts = Cache::remember($cacheKey, $this->cacheTtl, function () use ($query, $isAdmin) {
            $searchQuery = Post::search($query)
                ->forListing()
                ->with([
                    'category:id,name,slug',
                    'featuredImage',
                    'author:id,name,slug',
                    'authors:id,name,slug'
                ]);

            if ($isAdmin) {
                $searchQuery->visibleToAdmin();
            } else {
                $searchQuery->visibleToUser();
            }

            $result = $searchQuery->latest('created_at')->simplePaginate(12);
            return $result->appends(['q' => $query]);
        });

        $popularPosts = Cache::remember('popular_posts', $this->cacheTtl * 24, function () use ($isAdmin) {
            $query = Post::forListing()
                ->select(['id', 'title', 'slug'])
                ->with(['featuredImage'])
                ->latest('created_at')
                ->take(3);

            if ($isAdmin) {
                $query->visibleToAdmin();
            } else {
                $query->visibleToUser();
            }

            return $query->get();
        });

        return view('blog.search', compact('posts', 'query', 'popularPosts'));
    }
}
