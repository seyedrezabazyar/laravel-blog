<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Author;
use App\Models\Tag;
use App\Models\Publisher;
use App\Services\GeoLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlogController extends Controller
{
    /**
     * سرویس موقعیت‌یابی IP
     *
     * @var GeoLocationService
     */
    protected $geoLocationService;

    /**
     * ایجاد نمونه جدید از کنترلر
     *
     * @param GeoLocationService $geoLocationService
     */
    public function __construct(GeoLocationService $geoLocationService)
    {
        $this->geoLocationService = $geoLocationService;
    }

    /**
     * نمایش صفحه اصلی وبلاگ
     */
    public function index()
    {
        $posts = Post::visibleToUser()
            ->with(['user', 'category', 'author', 'publisher', 'authors', 'featuredImage'])
            ->latest()
            ->paginate(12);

        $categories = Category::withCount(['posts' => function($query) {
            $query->visibleToUser();
        }])->get();

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

        $post->load(['user', 'category', 'author', 'publisher', 'authors', 'featuredImage']);

        $relatedPosts = Post::visibleToUser()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->latest()
            ->take(3)
            ->get();

        // استفاده از سرویس جدید GeoLocationService برای تشخیص IP ایرانی
        $isIranianIp = $this->geoLocationService->isIranianIp(request()->ip());

        return view('blog.show', compact('post', 'relatedPosts', 'isIranianIp'));
    }

    /**
     * نمایش پست‌های یک دسته‌بندی خاص
     */
    public function category(Category $category)
    {
        $posts = Post::visibleToUser()
            ->where('category_id', $category->id)
            ->with(['user', 'category', 'author', 'publisher', 'authors'])
            ->latest()
            ->paginate(12);

        $allCategories = Category::withCount(['posts' => function($query) {
            $query->visibleToUser();
        }])->get();

        return view('blog.category', compact('posts', 'category', 'allCategories'));
    }

    /**
     * نمایش تمام دسته‌بندی‌ها
     */
    public function categories()
    {
        $categories = Category::withCount(['posts' => function($query) {
            $query->visibleToUser();
        }])
            ->orderByDesc('posts_count')
            ->get();

        $categories->each(function ($category) {
            $category->sample_post = Post::visibleToUser()
                ->where('category_id', $category->id)
                ->latest()
                ->first();
        });

        $popularCategories = $categories->take(5);

        return view('blog.categories', compact('categories', 'popularCategories'));
    }

    /**
     * نمایش پست‌های یک نویسنده خاص
     */
    public function author(Author $author)
    {
        $postIds = collect();

        $mainAuthorPostIds = Post::visibleToUser()
            ->where('author_id', $author->id)
            ->pluck('id');
        $postIds = $postIds->concat($mainAuthorPostIds);

        $coAuthorPostIds = DB::table('post_author')
            ->where('post_author.author_id', $author->id)
            ->join('posts', 'post_author.post_id', '=', 'posts.id')
            ->where('posts.is_published', true)
            ->where('posts.hide_content', false)
            ->pluck('posts.id');
        $postIds = $postIds->concat($coAuthorPostIds)->unique();

        $posts = Post::whereIn('id', $postIds)
            ->with(['user', 'category', 'author', 'publisher', 'authors'])
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
            ->with(['user', 'category', 'author', 'publisher', 'authors'])
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
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('english_title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%")
                    ->orWhere('english_content', 'like', "%{$query}%")
                    ->orWhere('book_codes', 'like', "%{$query}%");
            })
            ->with(['user', 'category', 'author', 'publisher', 'authors'])
            ->latest()
            ->paginate(12);

        $categories = Category::withCount(['posts' => function($query) {
            $query->visibleToUser();
        }])->get();

        $popularPosts = Post::visibleToUser()
            ->latest()
            ->take(3)
            ->get();

        return view('blog.search', compact('posts', 'query', 'categories', 'popularPosts'));
    }

    /**
     * نمایش پست‌های یک برچسب خاص
     */
    public function tag(Tag $tag)
    {
        $posts = $tag->posts()
            ->visibleToUser()
            ->with(['user', 'category', 'author', 'publisher', 'authors'])
            ->latest()
            ->paginate(12);

        return view('blog.tag', compact('posts', 'tag'));
    }
}
