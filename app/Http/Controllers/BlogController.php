<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Author;
use App\Models\Tag;
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
        $posts = Post::visibleToUser()
            ->with(['user', 'category', 'author', 'publisher', 'authors', 'featuredImage'])
            ->latest()
            ->take(12)  // به جای paginate(12) از take(12) استفاده کردیم
            ->get();    // نیاز به متد get() برای اجرای کوئری

        $categories = Category::withCount(['posts' => function($query) {
            $query->visibleToUser();
        }])
            ->whereHas('posts', function($query) {
                $query->visibleToUser();
            })
            ->orderBy(
                Post::select('created_at')
                    ->whereColumn('category_id', 'categories.id')
                    ->visibleToUser()
                    ->latest()
                    ->limit(1)
                , 'desc')
            ->take(12)
            ->get();

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

        $post->load(['user', 'category', 'author', 'publisher', 'authors', 'featuredImage', 'tags']);

        // بررسی تعداد کل کتاب‌های موجود در این دسته‌بندی
        $totalCategoryBooks = Post::visibleToUser()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->count();

        // بارگذاری کتاب‌های مشابه با استفاده از چند راهبرد
        if ($totalCategoryBooks >= 12) {
            // اگر در دسته‌بندی به اندازه کافی کتاب وجود دارد
            $relatedPosts = Post::visibleToUser()
                ->where('category_id', $post->category_id)
                ->where('id', '!=', $post->id)
                ->latest()
                ->take(12)
                ->get();
        } else {
            // اگر در دسته‌بندی کمتر از 12 کتاب وجود دارد
            // ابتدا کتاب‌های دسته‌بندی فعلی را می‌گیریم
            $categoryBooks = Post::visibleToUser()
                ->where('category_id', $post->category_id)
                ->where('id', '!=', $post->id)
                ->latest()
                ->get();

            // تعداد کتاب‌های دیگری که نیاز داریم
            $neededBooks = 12 - $categoryBooks->count();

            // آیدی‌های کتاب‌های موجود (برای جلوگیری از تکرار)
            $existingIds = $categoryBooks->pluck('id')->toArray();
            $existingIds[] = $post->id; // آیدی خود پست

            // یافتن کتاب‌های مرتبط بر اساس تگ‌های مشترک
            if ($post->tags && $post->tags->count() > 0) {
                $tagIds = $post->tags->pluck('id')->toArray();

                $tagRelatedBooks = Post::visibleToUser()
                    ->whereHas('tags', function($query) use($tagIds) {
                        $query->whereIn('tags.id', $tagIds);
                    })
                    ->whereNotIn('id', $existingIds)
                    ->latest()
                    ->take($neededBooks)
                    ->get();

                // ترکیب دو مجموعه
                $relatedPosts = $categoryBooks->concat($tagRelatedBooks);

                // اگر بعد از جستجو بر اساس تگ هنوز به 12 کتاب نرسیدیم
                if ($relatedPosts->count() < 12) {
                    $remainingNeeded = 12 - $relatedPosts->count();
                    $currentIds = $relatedPosts->pluck('id')->toArray();

                    // کتاب‌های پربازدید یا جدید دیگر
                    $otherBooks = Post::visibleToUser()
                        ->whereNotIn('id', $currentIds)
                        ->latest()
                        ->take($remainingNeeded)
                        ->get();

                    $relatedPosts = $relatedPosts->concat($otherBooks);
                }
            } else {
                // اگر تگی وجود نداشت، فقط بر اساس جدیدترین کتاب‌ها اضافه می‌کنیم
                $otherBooks = Post::visibleToUser()
                    ->whereNotIn('id', $existingIds)
                    ->latest()
                    ->take($neededBooks)
                    ->get();

                $relatedPosts = $categoryBooks->concat($otherBooks);
            }
        }

        // حذف متغیرهای مربوط به IP و کشور
        // دریافت IP ساده برای نمایش - بدون نیاز به سرویس GeoLocation
        $userIp = request()->ip();

        // مقدار پیش‌فرض برای isIranianIp
        $isIranianIp = false;

        // ارسال اطلاعات اضافی به view
        return view('blog.show', compact(
            'post',
            'relatedPosts',
            'isIranianIp',
            'userIp',
            'totalCategoryBooks'
        ));
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
