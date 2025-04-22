<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::where('is_published', true)
            ->with(['user', 'category'])
            ->latest()
            ->paginate(10);

        return view('blog.index', compact('posts'));
    }

    public function show(Post $post)
    {
        if (!$post->is_published) {
            abort(404);
        }

        return view('blog.show', compact('post'));
    }

    public function category(Category $category)
    {
        $posts = Post::where('category_id', $category->id)
            ->where('is_published', true)
            ->with(['user', 'category'])
            ->latest()
            ->paginate(10);

        return view('blog.category', compact('posts', 'category'));
    }
}
