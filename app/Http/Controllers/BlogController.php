<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * نمایش صفحه اصلی وبلاگ
     */
    public function index()
    {
        $posts = Post::where('is_published', true)
            ->with(['user', 'category'])
            ->latest()
            ->paginate(12);

        $categories = Category::withCount('posts')->get();

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

        // دریافت پست‌های مرتبط
        $relatedPosts = Post::where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->where('is_published', true)
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
            ->with(['user', 'category'])
            ->latest()
            ->paginate(12);

        $allCategories = Category::withCount('posts')->get();

        return view('blog.category', compact('posts', 'category', 'allCategories'));
    }

    /**
     * نمایش تمام دسته‌بندی‌ها
     */
    public function categories()
    {
        $categories = Category::withCount('posts')
            ->orderByDesc('posts_count')
            ->get();

        // دریافت یک پست نمونه برای هر دسته‌بندی
        $categories->each(function ($category) {
            $category->sample_post = Post::where('category_id', $category->id)
                ->where('is_published', true)
                ->latest()
                ->first();
        });

        // دسته‌بندی‌های پربازدید
        $popularCategories = $categories->take(5);

        return view('blog.categories', compact('categories', 'popularCategories'));
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
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->with(['user', 'category'])
            ->latest()
            ->paginate(12);

        $categories = Category::withCount('posts')->get();

        // پست‌های محبوب برای نمایش در صورت عدم یافتن نتیجه
        $popularPosts = Post::where('is_published', true)
            ->latest()
            ->take(3)
            ->get();

        return view('blog.search', compact('posts', 'query', 'categories', 'popularPosts'));
    }
}
