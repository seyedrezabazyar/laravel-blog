<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlogController extends Controller
{
    /**
     * نمایش صفحه اصلی وبلاگ
     */
    public function index()
    {
        $posts = Post::where('is_published', true)
            ->where('hide_content', false)
            ->with(['user', 'category', 'author', 'publisher', 'authors'])
            ->latest()
            ->paginate(12);

        $categories = Category::withCount(['posts' => function($query) {
            $query->where('is_published', true)
                ->where('hide_content', false);
        }])->get();

        return view('blog.index', compact('posts', 'categories'));
    }

    /**
     * نمایش جزئیات یک پست
     */
    public function show(Post $post)
    {
        if (!$post->is_published || $post->hide_content) {
            abort(404);
        }

        // بارگیری اطلاعات مرتبط
        $post->load(['user', 'category', 'author', 'publisher', 'authors', 'images' => function($query) {
            $query->where('hide_image', false)->orderBy('sort_order');
        }]);

        // دریافت پست‌های مرتبط
        $relatedPosts = Post::where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->where('is_published', true)
            ->where('hide_content', false)
            ->latest()
            ->take(3)
            ->get();

        // افزایش تعداد بازدید پست (اختیاری)
        // $post->increment('views');

        return view('blog.show', compact('post', 'relatedPosts'));
    }

    /**
     * نمایش پست‌های یک دسته‌بندی خاص
     */
    public function category(Category $category)
    {
        $posts = Post::where('category_id', $category->id)
            ->where('is_published', true)
            ->where('hide_content', false)
            ->with(['user', 'category', 'author', 'publisher', 'authors'])
            ->latest()
            ->paginate(12);

        $allCategories = Category::withCount(['posts' => function($query) {
            $query->where('is_published', true)
                ->where('hide_content', false);
        }])->get();

        return view('blog.category', compact('posts', 'category', 'allCategories'));
    }

    /**
     * نمایش تمام دسته‌بندی‌ها
     */
    public function categories()
    {
        $categories = Category::withCount(['posts' => function($query) {
            $query->where('is_published', true)
                ->where('hide_content', false);
        }])
            ->orderByDesc('posts_count')
            ->get();

        // دریافت یک پست نمونه برای هر دسته‌بندی
        $categories->each(function ($category) {
            $category->sample_post = Post::where('category_id', $category->id)
                ->where('is_published', true)
                ->where('hide_content', false)
                ->latest()
                ->first();
        });

        // دسته‌بندی‌های پربازدید
        $popularCategories = $categories->take(5);

        return view('blog.categories', compact('categories', 'popularCategories'));
    }

    /**
     * نمایش پست‌های یک نویسنده خاص
     */
    /**
     * نمایش پست‌های یک نویسنده خاص
     */
    /**
     * نمایش پست‌های یک نویسنده خاص
     */
    public function author(Author $author)
    {
        // برای جلوگیری از تکرار کتاب‌ها، از یک متغیر قابل شمارش استفاده می‌کنیم
        $postIds = collect();

        // پست‌هایی که این نویسنده، نویسنده اصلی آن‌ها است
        $mainAuthorPostIds = Post::where('author_id', $author->id)
            ->where('is_published', true)
            ->where('hide_content', false)
            ->pluck('id');
        $postIds = $postIds->concat($mainAuthorPostIds);

        // پست‌هایی که این نویسنده، نویسنده همکار آن‌ها است
        $coAuthorPostIds = DB::table('post_author')
            ->where('post_author.author_id', $author->id)
            ->join('posts', 'post_author.post_id', '=', 'posts.id')
            ->where('posts.is_published', true)
            ->where('posts.hide_content', false)
            ->pluck('posts.id');
        $postIds = $postIds->concat($coAuthorPostIds)->unique();

        // حالا تمام پست‌های مرتبط را با شناسه‌های جمع‌آوری شده بازیابی می‌کنیم
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
        $posts = Post::where('publisher_id', $publisher->id)
            ->where('is_published', true)
            ->where('hide_content', false)
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

        $posts = Post::where('is_published', true)
            ->where('hide_content', false)
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('english_title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%")
                    ->orWhere('english_content', 'like', "%{$query}%")
                    ->orWhere('keywords', 'like', "%{$query}%")
                    ->orWhere('book_codes', 'like', "%{$query}%");
            })
            ->with(['user', 'category', 'author', 'publisher', 'authors'])
            ->latest()
            ->paginate(12);

        $categories = Category::withCount(['posts' => function($query) {
            $query->where('is_published', true)
                ->where('hide_content', false);
        }])->get();

        // پست‌های محبوب برای نمایش در صورت عدم یافتن نتیجه
        $popularPosts = Post::where('is_published', true)
            ->where('hide_content', false)
            ->latest()
            ->take(3)
            ->get();

        return view('blog.search', compact('posts', 'query', 'categories', 'popularPosts'));
    }

    // در app/Http/Controllers/BlogController.php

    /**
     * نمایش پست‌های یک برچسب خاص
     */
    public function tag(Tag $tag)
    {
        $posts = $tag->posts()
            ->where('is_published', true)
            ->where('hide_content', false)
            ->with(['user', 'category', 'author', 'publisher', 'authors'])
            ->latest()
            ->paginate(12);

        return view('blog.tag', compact('posts', 'tag'));
    }
}
