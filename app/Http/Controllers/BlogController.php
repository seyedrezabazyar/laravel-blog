<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Author;
use App\Models\Tag;
use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
     * نمایش جزئیات یک پست
     */
    public function show(Post $post)
    {
        if (!$post->is_published) {
            abort(404);
        }

        if ($post->hide_content && !(auth()->check() && auth()->user()->isAdmin())) {
            abort(404);
        }

        // لود کردن تمام روابط مورد نیاز
        $post->loadMissing(['category', 'featuredImage', 'tags', 'author', 'authors']);

        // استفاده از کش برای پست‌های مرتبط
        $relatedPosts = Cache::remember("related_posts_{$post->id}", $this->cacheTtl, function () use ($post) {
            // 1. ابتدا پست‌های مشابه در همان دسته‌بندی
            $categoryPosts = Post::visibleToUser()
                ->select('id', 'title', 'slug', 'category_id', 'publication_year', 'format')
                ->where('category_id', $post->category_id)
                ->where('id', '!=', $post->id)
                ->with(['featuredImage', 'author', 'authors'])
                ->latest()
                ->take(12)
                ->get();

            if ($categoryPosts->count() >= 12) {
                return $categoryPosts;
            }

            // 2. اگر کافی نبود، از تگ‌های مشترک استفاده می‌کنیم
            $existingIds = $categoryPosts->pluck('id')->toArray();
            $existingIds[] = $post->id;

            if ($post->tags && $post->tags->count() > 0) {
                $tagIds = $post->tags->pluck('idHooman')->toArray();

                $tagPosts = Post::visibleToUser()
                    ->select('id', 'title', 'slug', 'category_id', 'publication_year', 'format')
                    ->whereHas('tags', function ($query) use ($tagIds) {
                        $query->whereIn('tags.id', $tagIds);
                    })
                    ->whereNotIn('id', $existingIds)
                    ->with(['featuredImage', 'author', 'authors'])
                    ->latest()
                    ->take(12 - $categoryPosts->count())
                    ->get();

                $result = $categoryPosts->concat($tagPosts);

                if ($result->count() < 12) {
                    $currentIds = $result->pluck('id')->toArray();

                    $otherPosts = Post::visibleToUser()
                        ->select('id', 'title', 'slug', 'category_id', 'publication_year', 'format')
                        ->whereNotIn('id', $currentIds)
                        ->with(['featuredImage', 'author', 'authors'])
                        ->latest()
                        ->take(12 - $result->count())
                        ->get();

                    return $result->concat($otherPosts);
                }

                return $result;
            }

            // 3. اگر تگی نیست، فقط پست‌های دیگر
            $otherPosts = Post::visibleToUser()
                ->select('id', 'title', 'slug', 'category_id', 'publication_year', 'format')
                ->whereNotIn('id', $existingIds)
                ->with(['featuredImage', 'author', 'authors'])
                ->latest()
                ->take(12 - $categoryPosts->count())
                ->get();

            return $categoryPosts->concat($otherPosts);
        });

        $totalCategoryBooks = Cache::remember("category_{$post->category_id}_count", $this->cacheTtl, function () use ($post) {
            return Post::visibleToUser()
                ->where('category_id', $post->category_id)
                ->where('id', '!=', $post->id)
                ->count();
        });

        return view('blog.show', compact('post', 'relatedPosts', 'totalCategoryBooks'));
    }

    /**
     * نمایش پست‌های یک دسته‌بندی خاص
     */
    public function category(Category $category)
    {
        $posts = Post::visibleToUser()
            ->where('category_id', $category->id)
            ->with(['category', 'featuredImage', 'author', 'authors'])
            ->latest()
            ->paginate(12);

        $allCategories = Cache::remember('all_categories_with_count', $this->cacheTtl, function () {
            return Category::withCount(['posts' => function ($query) {
                $query->visibleToUser();
            }])->get();
        });

        return view('blog.category', compact('posts', 'category', 'allCategories'));
    }

    /**
     * نمایش تمام دسته‌بندی‌ها
     */
    public function categories()
    {
        $categories = Cache::remember('all_categories_with_samples', $this->cacheTtl, function () {
            $categoriesWithCount = Category::withCount(['posts' => function ($query) {
                $query->visibleToUser();
            }])
                ->orderByDesc('posts_count')
                ->get();

            $latestPostsByCategory = Post::visibleToUser()
                ->select('id', 'title', 'slug', 'category_id')
                ->whereIn('category_id', $categoriesWithCount->pluck('id'))
                ->with(['featuredImage', 'author', 'authors'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('category_id');

            $categoriesWithCount->each(function ($category) use ($latestPostsByCategory) {
                $posts = $latestPostsByCategory->get($category->id);
                $category->sample_post = $posts ? $posts->first() : null;
            });

            return $categoriesWithCount;
        });

        $popularCategories = $categories->take(5);

        return view('blog.categories', compact('categories', 'popularCategories'));
    }

    /**
     * نمایش پست‌های یک نویسنده خاص
     */
    public function author(Author $author)
    {
        $posts = Post::visibleToUser()
            ->with(['category', 'featuredImage', 'author', 'authors'])
            ->where(function ($query) use ($author) {
                $query->where('author_id', $author->id)
                    ->orWhereHas('authors', function ($q) use ($author) {
                        $q->where('authors.id', $author->id);
                    });
            })
            ->latest()
            ->paginate(12);

        return view('blog.author', compact('posts', 'author'));
    }

    /**
     * نمایش پست‌های یک ناشر خاص
     */
    public function publisher(Publisher $publisher)
    {
        $posts = Post::visibleToUser()
            ->where('publisher_id', $publisher->id)
            ->with(['category', 'featuredImage', 'author', 'authors'])
            ->latest()
            ->paginate(12);

        return view('blog.publisher', compact('posts', 'publisher'));
    }

    /**
     * جستجو در وبلاگ
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return redirect()->route('blog.index');
        }

        $posts = Post::visibleToUser()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('english_title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%")
                    ->orWhere('english_content', 'like', "%{$query}%")
                    ->orWhere('book_codes', 'like', "%{$query}%");
            })
            ->with(['category', 'featuredImage', 'author', 'authors'])
            ->latest()
            ->paginate(12);

        $categories = Cache::remember('all_categories_search', $this->cacheTtl, function () {
            return Category::withCount(['posts' => function ($query) {
                $query->visibleToUser();
            }])->get();
        });

        $popularPosts = Cache::remember('popular_posts', $this->cacheTtl, function () {
            return Post::visibleToUser()
                ->select('id', 'title', 'slug')
                ->with(['featuredImage', 'author', 'authors'])
                ->latest()
                ->take(3)
                ->get();
        });

        return view('blog.search', compact('posts', 'query', 'categories', 'popularPosts'));
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
