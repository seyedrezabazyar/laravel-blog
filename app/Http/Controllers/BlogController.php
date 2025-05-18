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

class BlogController extends Controller
{
    protected $cacheTtl = 86400;

    /**
     * نمایش صفحه اصلی وبلاگ با حداقل کوئری به دیتابیس
     */
    public function index(): View
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
    public function categories(): View
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
     * نمایش جزئیات پست با عملکرد بهینه‌سازی شده
     *
     * در Laravel 12، Route Model Binding به‌طور پیش‌فرض slug را بررسی می‌کند،
     * مگر اینکه در RouteServiceProvider به‌صورت دیگری پیکربندی شده باشد.
     */
    public function show(Post $post): View
    {
        // بررسی مجوزهای دسترسی
        if (!$post->is_published || ($post->hide_content && !(auth()->check() && auth()->user()->isAdmin()))) {
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

        // استفاده از کش برای پست‌های مرتبط
        $isAdmin = auth()->check() && auth()->user()->isAdmin() ? 'admin' : 'user';
        $cacheKey = "post_{$post->id}_related_posts_{$isAdmin}";

        $relatedPosts = Cache::remember($cacheKey, $this->cacheTtl, function () use ($post) {
            return $this->getRelatedPosts($post);
        });

        return view('blog.show', compact('post', 'relatedPosts'));
    }

    /**
     * کوئری بهینه‌سازی شده برای پست‌های مرتبط
     */
    private function getRelatedPosts(Post $post)
    {
        // دریافت پست‌ها از همان دسته‌بندی (محدود به 6 مورد)
        $categoryPosts = Post::visibleToUser()
            ->select('id', 'title', 'slug', 'category_id', 'publication_year', 'format')
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->with([
                'featuredImage' => function($query) {
                    $query->select('id', 'post_id', 'image_path', 'hide_image', 'sort_order');
                }
            ])
            ->latest()
            ->limit(6)
            ->get();

        // اگر به تعداد کافی پست داریم، آنها را برگردانیم
        if ($categoryPosts->count() >= 6) {
            return $categoryPosts;
        }

        // در صورت نیاز به پست‌های بیشتر، پست‌های اخیر را اضافه کنیم
        $currentIds = $categoryPosts->pluck('id')->toArray();
        $currentIds[] = $post->id;

        $otherPosts = Post::visibleToUser()
            ->select('id', 'title', 'slug', 'category_id', 'publication_year', 'format')
            ->whereNotIn('id', $currentIds)
            ->with([
                'featuredImage' => function($query) {
                    $query->select('id', 'post_id', 'image_path', 'hide_image', 'sort_order');
                }
            ])
            ->latest()
            ->limit(6 - $categoryPosts->count())
            ->get();

        return $categoryPosts->merge($otherPosts);
    }

    /**
     * نمایش پست‌های یک دسته‌بندی خاص - نسخه بهینه‌سازی شده
     */
    public function category(Category $category): View
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
    public function author(Author $author): View
    {
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $page = request()->get('page', 1);
        $cacheKey = "author_posts_{$author->id}_page_{$page}_" . ($isAdmin ? 'admin' : 'user');

        $posts = Cache::remember($cacheKey, 3600, function () use ($author, $isAdmin) {
            // استفاده از union برای بهبود کارایی
            $mainPosts = Post::select([
                'posts.id', 'posts.title', 'posts.slug', 'posts.category_id', 'posts.author_id',
                'posts.publication_year', 'posts.format', 'posts.created_at'
            ])
                ->where('posts.is_published', true)
                ->when(!$isAdmin, function ($q) {
                    return $q->where('posts.hide_content', false);
                })
                ->where('posts.author_id', $author->id);

            $coAuthorPosts = Post::select([
                'posts.id', 'posts.title', 'posts.slug', 'posts.category_id', 'posts.author_id',
                'posts.publication_year', 'posts.format', 'posts.created_at'
            ])
                ->join('post_author', 'posts.id', '=', 'post_author.post_id')
                ->where('post_author.author_id', $author->id)
                ->where('posts.is_published', true)
                ->when(!$isAdmin, function ($q) {
                    return $q->where('posts.hide_content', false);
                })
                ->whereNotIn('posts.id', function($query) use ($author) {
                    $query->select('id')
                        ->from('posts')
                        ->where('author_id', $author->id);
                });

            // جلوگیری از شمارش اضافه با استفاده از simplePaginate
            $query = $mainPosts->union($coAuthorPosts);

            return $query
                ->orderBy('created_at', 'DESC')
                ->with([
                    'featuredImage' => function($q) {
                        $q->select('id', 'post_id', 'image_path', 'hide_image', 'sort_order');
                    },
                    'category:id,name,slug'
                ])
                ->simplePaginate(12);
        });

        return view('blog.author', compact('posts', 'author'));
    }

    /**
     * نمایش پست‌های یک ناشر خاص - نسخه نهایی بهینه‌سازی شده
     */
    public function publisher(Publisher $publisher): Response
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
    public function search(Request $request): View
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
                        $query->select('id', 'post_id', 'image_path', 'hide_image');
                    }
                ])
                ->latest()
                ->take(3)
                ->get();
        });

        return view('blog.search', compact('posts', 'query', 'popularPosts'));
    }
}
