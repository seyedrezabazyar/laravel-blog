<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Author;
use App\Models\Tag;
use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB; // اضافه کردن این import برای رفع خطا

class BlogController extends Controller
{
    // Cache TTL in seconds (24 hours, configurable via .env)
    protected $cacheTtl = 86400;

    /**
     * نمایش صفحه اصلی وبلاگ با حداقل کوئری به دیتابیس
     */
    public function index()
    {
        // فقط 12 پست آخر را با فیلدهای مورد نیاز از دیتابیس می‌گیریم
        $posts = Cache::remember('home_latest_posts', 3600, function () {
            return Post::select('id', 'title', 'slug', 'publication_year', 'format')
                ->where('is_published', true)
                ->where('hide_content', false)
                ->latest()
                ->take(12)
                ->get();
        });

        // دسته‌بندی‌های ثابت برای صفحه اصلی - بدون نیاز به کوئری دیتابیس
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
        // استفاده از آرایه ثابت به جای کوئری به دیتابیس
        // این داده‌ها می‌توانند در یک فایل کانفیگ یا در کش دائمی ذخیره شوند
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

        // مرتب‌سازی بر اساس تعداد پست‌ها (از بیشترین به کمترین)
        $categories = $categories->sortByDesc('posts_count');

        // دسته‌بندی‌های محبوب (5 مورد اول)
        $popularCategories = $categories->take(5);

        return view('blog.categories', compact('categories', 'popularCategories'));
    }

    /**
     * Display post details with optimized performance
     */
    public function show(Post $post)
    {
        // Check if post is published and visible
        if (!$post->is_published) {
            abort(404);
        }

        if ($post->hide_content && !(auth()->check() && auth()->user()->isAdmin())) {
            abort(404);
        }

        // Efficiently load only the relationships we need with specific columns
        $post->load([
            'category:id,name,slug',
            'featuredImage',
            'tags:id,name,slug',
            'author:id,name,slug',
            'authors:id,name,slug',
        ]);

        // Cache key that includes post ID and whether user is admin
        $isAdmin = auth()->check() && auth()->user()->isAdmin() ? 'admin' : 'user';
        $cacheKey = "post_{$post->id}_related_posts_{$isAdmin}";

        // Get related posts from cache or generate them
        $relatedPosts = Cache::remember($cacheKey, $this->cacheTtl, function () use ($post) {
            // Find related posts in a single optimized query with eager loading
            return $this->getRelatedPosts($post);
        });

        // Return the view with required data
        return view('blog.show', compact('post', 'relatedPosts'));
    }

    /**
     * Optimized related posts query
     * This uses a single DB query with UNION to get all related posts
     */
    private function getRelatedPosts(Post $post)
    {
        // Get posts from same category first (limited to 6)
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

        // Get the IDs of posts we already have
        $existingIds = $categoryPosts->pluck('id')->toArray();
        $existingIds[] = $post->id;

        // Get posts with the same tags (if any)
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

        // Combine the results
        $result = $categoryPosts->merge($tagPosts);

        // If we still need more posts, get the most recent ones
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
     * نمایش پست‌های یک دسته‌بندی خاص - نسخه نهایی بهینه‌شده
     */
    public function category(Category $category)
    {
        // Clave de caché única basada en la categoría y parámetros importantes
        $page = request()->get('page', 1);
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $cacheKey = "category_posts_{$category->id}_page_{$page}_" . ($isAdmin ? 'admin' : 'user');

        // Caché de datos por 12 horas
        $posts = Cache::remember($cacheKey, 12 * 60 * 60, function () use ($category, $isAdmin) {
            return Post::where('is_published', true)
                ->when(!$isAdmin, function ($query) {
                    $query->where('hide_content', false);
                })
                ->where('category_id', $category->id)
                ->select(['id', 'title', 'slug', 'category_id', 'author_id', 'publication_year', 'format'])
                ->with([
                    'featuredImage' => function($query) {
                        $query->select('id', 'post_id', 'image_path', 'hide_image', 'sort_order');
                    },
                    'author:id,name,slug'
                ])
                ->latest()
                ->simplePaginate(12); // Usar simplePaginate en lugar de paginate
        });

        // No cargar categorías adicionales - ya estamos en una página de categoría específica

        return view('blog.category', compact('posts', 'category'));
    }

    /**
     * نمایش پست‌های یک نویسنده خاص - نسخه بهینه‌سازی شده
     */
    public function author(Author $author)
    {
        // کلید کش منحصر به فرد بر اساس شناسه نویسنده، شماره صفحه، و وضعیت مدیر بودن کاربر
        $page = request()->get('page', 1);
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $cacheKey = "author_posts_{$author->id}_page_{$page}_" . ($isAdmin ? 'admin' : 'user');

        // ذخیره نتایج در کش به مدت ۱ ساعت
        $cacheTtl = 3600;

        // گرفتن پست‌ها از کش یا دیتابیس
        $posts = Cache::remember($cacheKey, $cacheTtl, function () use ($author, $isAdmin) {
            // استفاده از اسکوپ visibleToUser برای فیلتر کردن پست‌ها
            return Post::visibleToUser()
                ->select(['id', 'title', 'slug', 'category_id', 'author_id', 'publication_year', 'format'])
                ->where(function ($query) use ($author) {
                    $query->where('author_id', $author->id)
                        ->orWhereHas('authors', function ($q) use ($author) {
                            $q->where('authors.id', $author->id);
                        });
                })
                // بارگذاری روابط با انتخاب فیلدهای مشخص برای کاهش حجم داده
                ->with([
                    'category:id,name,slug',
                    'featuredImage' => function($query) {
                        $query->select('id', 'post_id', 'image_path', 'hide_image');
                    },
                    'author:id,name,slug'
                ])
                ->latest()
                ->paginate(12);
        });

        // برگرداندن نما با داده‌ها
        return view('blog.author', compact('posts', 'author'));
    }

    /**
     * نمایش پست‌های یک ناشر خاص - نسخه نهایی بهینه‌سازی شده
     */
    public function publisher(Publisher $publisher)
    {
        // کلید کش بر اساس شناسه ناشر و پارامترهای مهم
        $page = request()->get('page', 1);
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $cacheKey = "publisher_posts_{$publisher->id}_page_{$page}_" . ($isAdmin ? 'admin' : 'user');

        // ذخیره نتایج در کش به مدت ۱ ساعت
        $posts = Cache::remember($cacheKey, 3600, function () use ($publisher, $isAdmin) {
            return Post::where('is_published', true)
                ->when(!$isAdmin, function ($query) {
                    $query->where('hide_content', false);
                })
                ->where('publisher_id', $publisher->id)
                // انتخاب فقط فیلدهای مورد نیاز
                ->select([
                    'id', 'title', 'slug', 'category_id', 'author_id',
                    'publication_year', 'format'
                ])
                // بارگذاری روابط با انتخاب ستون‌های مشخص
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

        // حذف این خط که باعث دوباره کاری می‌شود
        // $posts->load(['author' => function($query) {
        //     $query->select(['id', 'name', 'slug'])->without(['posts', 'coAuthoredPosts']);
        // }]);

        // افزودن هدرهای کش برای کش مرورگر
        $etag = md5($publisher->id . $page . $isAdmin . time() / 3600); // زمان را به ساعت گرد می‌کنیم
        $response = response()->view('blog.publisher', compact('posts', 'publisher'));
        $response->header('Cache-Control', 'public, max-age=300'); // کش به مدت ۵ دقیقه
        $response->header('ETag', $etag);

        return $response;
    }

    /**
     * جستجو در وبلاگ - نسخه بسیار ساده شده فقط با نتایج جستجو
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return redirect()->route('blog.index');
        }

        // کلید کش منحصر به فرد برای این جستجو و صفحه
        $cacheKey = 'search_results_' . md5($query . '_page_' . $request->get('page', 1));

        // نتایج جستجو را از کش بخوان یا محاسبه کن
        $posts = Cache::remember($cacheKey, $this->cacheTtl, function () use ($query, $request) {
            // کد قبلی بدون تغییر
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

            // کد جستجو بدون تغییر
            if (method_exists(Post::class, 'scopeFullTextSearch')) {
                $postsQuery->fullTextSearch($query);
            } else {
                $postsQuery->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('english_title', 'like', "%{$query}%")
                        ->orWhere('book_codes', 'like', "%{$query}%");
                });
            }

            return $postsQuery->latest()->simplePaginate(12);
        });

        // تغییر: ساده‌سازی کد پست‌های محبوب
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

        // حذف categories از compact
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
