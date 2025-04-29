<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Tag;
use Illuminate\Http\Request;

class RssController extends Controller
{
    /**
     * ساخت فید RSS اصلی برای همه پست‌ها
     */
    public function index()
    {
        $posts = Post::where('is_published', true)
            ->where('hide_content', false)
            ->with(['category', 'author', 'featuredImage'])
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return response()->view('feeds.posts', [
            'posts' => $posts,
            'title' => 'آخرین مطالب کتابستان',
            'description' => 'آخرین مطالب مربوط به کتاب و کتابخوانی در وبلاگ کتابستان'
        ])->header('Content-Type', 'application/atom+xml; charset=UTF-8');
    }

    /**
     * ساخت فید RSS برای یک دسته‌بندی خاص
     */
    public function category(Category $category)
    {
        $posts = Post::where('is_published', true)
            ->where('hide_content', false)
            ->where('category_id', $category->id)
            ->with(['category', 'author', 'featuredImage'])
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return response()->view('feeds.posts', [
            'posts' => $posts,
            'title' => "مطالب دسته {$category->name}",
            'description' => "آخرین مطالب مربوط به دسته {$category->name} در وبلاگ کتابستان"
        ])->header('Content-Type', 'application/atom+xml; charset=UTF-8');
    }

    /**
     * ساخت فید RSS برای یک نویسنده خاص
     */
    public function author(Author $author)
    {
        // گرفتن پست‌های نویسنده اصلی
        $mainPosts = Post::where('is_published', true)
            ->where('hide_content', false)
            ->where('author_id', $author->id)
            ->with(['category', 'author', 'featuredImage'])
            ->get();

        // گرفتن پست‌های نویسنده همکار
        $coAuthorPosts = $author->coAuthoredPosts()
            ->where('is_published', true)
            ->where('hide_content', false)
            ->with(['category', 'author', 'featuredImage'])
            ->get();

        // ترکیب هر دو نوع پست و مرتب‌سازی بر اساس تاریخ
        $posts = $mainPosts->merge($coAuthorPosts)
            ->sortByDesc('created_at')
            ->take(50);

        return response()->view('feeds.posts', [
            'posts' => $posts,
            'title' => "مطالب {$author->name}",
            'description' => "آخرین مطالب نوشته شده توسط {$author->name} در وبلاگ کتابستان"
        ])->header('Content-Type', 'application/atom+xml; charset=UTF-8');
    }

    /**
     * ساخت فید RSS برای یک تگ خاص
     */
    public function tag(Tag $tag)
    {
        $posts = $tag->posts()
            ->where('is_published', true)
            ->where('hide_content', false)
            ->with(['category', 'author', 'featuredImage'])
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return response()->view('feeds.posts', [
            'posts' => $posts,
            'title' => "مطالب با برچسب {$tag->name}",
            'description' => "آخرین مطالب با برچسب {$tag->name} در وبلاگ کتابستان"
        ])->header('Content-Type', 'application/atom+xml; charset=UTF-8');
    }
}
