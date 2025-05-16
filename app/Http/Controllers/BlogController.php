<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Author;
use App\Models\Tag;
use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BlogController extends Controller
{
    protected $cacheTtl = 86400;

    /**
     * نمایش صفحه اصلی وبلاگ با حداقل کوئری به دیتابیس
     */
    public function index()
    {
        $posts = Cache::remember('home_latest_posts', 3600, function () {
            return Post::select('id', 'title', 'slug', 'publication_year', 'format')
                ->where('is_published', true)
                ->where('hide_content', false)
                ->latest()
                ->take(12)
                ->get();
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
     * نمایش تمام دسته‌بندی‌ها - با بهینه‌سازی کوئری
     */
    public function categories()
    {
        $categories = collect([
            (object) ['id' => 1, 'name' => 'رمان', 'slug' => 'roman', 'posts_count' => 25],
            (object) ['id' => 2, 'name' => 'علمی', 'slug' => 'scientific', 'posts_count' => 18],
            (object) ['id' => 3, 'name' => 'تاریخی', 'slug' => 'historical', 'posts_count' => 15],
            (object) ['id' => 4, 'name' => 'فلسفه', 'slug' => 'philosophy', 'posts_count' => 12],
            (object) ['id' => 5, 'name' => 'روانشناسی', 'slug' => 'psychology', 'posts_count' => 20],
            (object) ['id' => 6, 'name' => 'کودک', 'slug' => 'children', 'posts_count' => 10],
            (object) ['id' => 7, 'name' => 'موفقیت', 'slug' => 'success', 'posts_count' => 22],
            (object) ['id' => 8, 'name' => 'هنر', 'slug' => 'art', 'posts_count' => 15],
            (object) ['id' => 9, 'name' => 'ادبیات', 'slug' => 'literature', 'posts_count' => 30],
            (object) ['id' => 10, 'name' => 'زندگینامه', 'slug' => 'biography', 'posts_count' => 8],
            (object) ['id' => 11, 'name' => 'خودیاری', 'slug' => 'self-help', 'posts_count' => 14],
            (object) ['id' => 12, 'name' => 'مذهبی', 'slug' => 'religious', 'posts_count' => 16],
            (object) ['id' => 13, 'name' => 'آشپزی', 'slug' => 'cooking', 'posts_count' => 7],
            (object) ['id' => 14, 'name' => 'سفر', 'slug' => 'travel', 'posts_count' => 9],
            (object) ['id' => 15, 'name' => 'ورزش', 'slug' => 'sports', 'posts_count' => 5],
            (object) ['id' => 16, 'name' => 'اقتصاد', 'slug' => 'economics', 'posts_count' => 11],
        ]);

        $categories = $categories->sortByDesc('posts_count');
        $popularCategories = $categories->take(5);

        return view('blog.categories', compact('categories', 'popularCategories'));
    }

    /**
     * Display post details with optimized performance
     */
    public function show(Post $post)
    {
        if (!$post->is_published || ($post->hide_content && !(auth()->check() && auth()->user()->isAdmin()))) {
            abort(404);
        }

        $post->load([
            'category:id,name,slug',
            'featuredImage',
            'tags:id,name,slug',
            'author:id,name,slug',
            'authors:id,name,slug',
        ]);

        $isAdmin = auth()->check() && auth()->user()->isAdmin() ? 'admin' : 'user';
        $cacheKey = "post_{$post->id}_related_posts_{$isAdmin}";

        $relatedPosts = Cache::remember($cacheKey, $this->cacheTtl, function () use ($post) {
            return $this->getRelatedPosts($post);
        });

        return view('blog.show', compact('post', 'relatedPosts'));
    }

    /**
     * Optimized related posts query
     */
    private function getRelatedPosts(Post $post)
    {
        // Get posts from same category (limit to 6)
        $categoryPosts = Post::visibleToUser()
            ->select('id', 'title', 'slug', 'category_id', 'publication_year', 'format')
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->with([
                'featuredImage' => function($query) {
                    $query->select('id', 'post_id', 'image_path', 'hide_image');
                },
                'author:id,name,slug',
                'authors:id,name,slug'
            ])
            ->limit(6)
            ->get();

        // If we already have 6 posts, return them
        if ($categoryPosts->count() >= 6) {
            return $categoryPosts;
        }

        // Get posts with the same tags if needed
        $existingIds = $categoryPosts->pluck('id')->toArray();
        $existingIds[] = $post->id;
        $tagPosts = collect();

        if ($post->tags && $post->tags->isNotEmpty()) {
            $tagIds = $post->tags->pluck('id')->toArray();

            $tagPosts = Post::visibleToUser()
                ->select('id', 'title', 'slug', 'category_id', 'publication_year', 'format')
                ->whereHas('tags', function ($query) use ($tagIds) {
                    $query->whereIn('tags.id', $tagIds);
                })
                ->whereNotIn('id', $existingIds)
                ->with([
                    'featuredImage' => function($query) {
                        $query->select('id', 'post_id', 'image_path', 'hide_image');
                    },
                    'author:id,name,slug',
                    'authors:id,name,slug'
                ])
                ->limit(6 - $categoryPosts->count())
                ->get();
        }

        // Combine results
        $result = $categoryPosts->merge($tagPosts);

        // Add recent posts if needed
        if ($result->count() < 6) {
            $currentIds = $result->pluck('id')->toArray();
            $currentIds[] = $post->id;

            $otherPosts = Post::visibleToUser()
                ->select('id', 'title', 'slug', 'category_id', 'publication_year', 'format')
                ->whereNotIn('id', $currentIds)
                ->with([
                    'featuredImage' => function($query) {
                        $query->select('id', 'post_id', 'image_path', 'hide_image');
                    },
                    'author:id,name,slug',
                    'authors:id,name,slug'
                ])
                ->latest()
                ->limit(6 - $result->count())
                ->get();

            $result = $result->merge($otherPosts);
        }

        return $result;
    }

    /**
     * Display posts from a specific category - optimized version
     */
    public function category(Category $category)
    {
        $page = request()->get('page', 1);
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $cacheKey = "category_posts_{$category->id}_page_{$page}_" . ($isAdmin ? 'admin' : 'user');

        $posts = Post::where('is_published', true)
            ->when(!$isAdmin, function ($query) {
                $query->where('hide_content', false);
            })
            ->where('category_id', $category->id)
            ->with([
                'featuredImage' => function($query) {
                    $query->select('id', 'post_id', 'image_path', 'hide_image', 'sort_order');
                },
                'author:id,name,slug'
            ])
            ->latest()
            ->simplePaginate(12);

        return view('blog.category', compact('posts', 'category'));
    }

    /**
     * نمایش پست‌های یک نویسنده خاص
     */
    public function author(Author $author)
    {
        $isAdmin = auth()->check() && auth()->user()->isAdmin();

        // Query for main posts where the author is the primary author
        $mainPosts = DB::table('posts')
            ->select('posts.*')
            ->where('author_id', $author->id)
            ->where('is_published', true)
            ->when(!$isAdmin, function ($query) {
                return $query->where('hide_content', false);
            })
            ->orderBy('created_at', 'DESC')
            ->get();

        // Query for co-authored posts
        $coAuthoredPosts = DB::table('posts as p')
            ->select('p.*')
            ->join('post_author as pa', 'p.id', '=', 'pa.post_id')
            ->where('pa.author_id', $author->id)
            ->where('p.is_published', true)
            ->when(!$isAdmin, function ($query) {
                return $query->where('p.hide_content', false);
            })
            ->orderBy('p.created_at', 'DESC')
            ->get();

        // Combine and deduplicate posts
        $postIds = [];
        $postsArray = [];

        foreach ($mainPosts as $post) {
            if (!in_array($post->id, $postIds)) {
                $postIds[] = $post->id;
                $postsArray[] = $post;
            }
        }

        foreach ($coAuthoredPosts as $post) {
            if (!in_array($post->id, $postIds)) {
                $postIds[] = $post->id;
                $postsArray[] = $post;
            }
        }

        // Paginate the combined results
        $posts = new \Illuminate\Pagination\LengthAwarePaginator(
            $postsArray,
            count($postsArray),
            12,
            null,
            ['path' => request()->url()]
        );

        return view('blog.author', compact('posts', 'author'));
    }

    /**
     * نمایش پست‌های یک ناشر خاص - نسخه نهایی بهینه‌سازی شده
     */
    public function publisher(Publisher $publisher)
    {
        $page = request()->get('page', 1);
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $cacheKey = "publisher_posts_{$publisher->id}_page_{$page}_" . ($isAdmin ? 'admin' : 'user');

        $posts = Cache::remember($cacheKey, 3600, function () use ($publisher, $isAdmin) {
            return Post::where('is_published', true)
                ->when(!$isAdmin, function ($query) {
                    $query->where('hide_content', false);
                })
                ->where('publisher_id', $publisher->id)
                ->select([
                    'id', 'title', 'slug', 'category_id', 'author_id',
                    'publication_year', 'format'
                ])
                ->with([
                    'featuredImage' => function($query) {
                        $query->select('id', 'post_id', 'image_path', 'hide_image', 'sort_order');
                    },
                    'author' => function($query) {
                        $query->select(['id', 'name', 'slug'])->without(['posts', 'coAuthoredPosts']);
                    }
                ])
                ->latest()
                ->simplePaginate(12);
        });

        $etag = md5($publisher->id . $page . $isAdmin . time() / 3600);
        $response = response()->view('blog.publisher', compact('posts', 'publisher'));
        $response->header('Cache-Control', 'public, max-age=300');
        $response->header('ETag', $etag);

        return $response;
    }

    /**
     * جستجو در وبلاگ - نسخه بهینه‌سازی شده با حفظ پارامترهای جستجو
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return redirect()->route('blog.index');
        }

        $cacheKey = 'search_results_' . md5($query . '_page_' . $request->get('page', 1));

        $posts = Cache::remember($cacheKey, $this->cacheTtl, function () use ($query, $request) {
            $postsQuery = Post::visibleToUser()
                ->select(['id', 'title', 'slug', 'category_id', 'author_id', 'publisher_id', 'publication_year', 'format'])
                ->with([
                    'category:id,name,slug',
                    'featuredImage' => function($query) {
                        $query->select('id', 'post_id', 'image_path', 'hide_image', 'sort_order');
                    },
                    'author:id,name,slug',
                    'authors:id,name,slug'
                ]);

            if (method_exists(Post::class, 'scopeFullTextSearch')) {
                try {
                    $postsQuery->fullTextSearch($query);
                } catch (\Exception $e) {
                    $postsQuery->where(function ($q) use ($query) {
                        $q->where('title', 'like', "%{$query}%")
                            ->orWhere('english_title', 'like', "%{$query}%")
                            ->orWhere('book_codes', 'like', "%{$query}%");
                    });
                }
            } else {
                $postsQuery->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('english_title', 'like', "%{$query}%")
                        ->orWhere('book_codes', 'like', "%{$query}%");
                });
            }

            $result = $postsQuery->latest()->simplePaginate(12);
            return $result->appends(['q' => $query]);
        });

        $popularPosts = Cache::remember('popular_posts', $this->cacheTtl * 24, function () {
            return Post::visibleToUser()
                ->select(['id', 'title', 'slug'])
                ->with([
                    'featuredImage' => function($query) {
                        $query->select('id', 'post_id', 'imagemeat', 'hide_image');
                    }
                ])
                ->latest()
                ->take(3)
                ->get();
        });

        return view('blog.search', compact('posts', 'query', 'popularPosts'));
    }

    /**
     * نمایش پست‌های یک برچسب خاص
     */
    public function tag(Tag $tag)
    {
        $posts = $tag->posts()
            ->visibleToUser()
            ->with(['category', 'featuredImage', 'author', 'authors'])
            ->latest()
            ->paginate(12);

        return view('blog.tag', compact('posts', 'tag'));
    }
}
