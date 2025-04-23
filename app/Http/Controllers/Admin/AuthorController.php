<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Author::withCount('posts')->paginate(10);
        return view('admin.authors.index', compact('authors'));
    }

    public function create()
    {
        return view('admin.authors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'biography' => 'nullable',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('authors', 'public');
            $validated['image'] = $path;
        }

        Author::create($validated);

        return redirect()->route('admin.authors.index')
            ->with('success', 'نویسنده با موفقیت ایجاد شد.');
    }

    public function show(Author $author)
    {
        // بارگذاری پست‌هایی که این نویسنده در آن‌ها نقش دارد
        // (هم به عنوان نویسنده اصلی و هم نویسنده همکار)
        $author->load(['posts', 'coAuthoredPosts']);

        // ترکیب هر دو نوع پست
        $books = $author->posts->merge($author->coAuthoredPosts)->unique('id');

        return view('admin.authors.show', compact('author', 'books'));
    }

    public function edit(Author $author)
    {
        return view('admin.authors.edit', compact('author'));
    }

    public function update(Request $request, Author $author)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'biography' => 'nullable',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            if ($author->image) {
                Storage::disk('public')->delete($author->image);
            }

            $path = $request->file('image')->store('authors', 'public');
            $validated['image'] = $path;
        }

        $author->update($validated);

        return redirect()->route('admin.authors.index')
            ->with('success', 'نویسنده با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Author $author)
    {
        // بررسی اینکه آیا نویسنده دارای پست است
        if ($author->posts()->count() > 0 || $author->coAuthoredPosts()->count() > 0) {
            return redirect()->route('admin.authors.index')
                ->with('error', 'این نویسنده دارای کتاب است و نمی‌توان آن را حذف کرد.');
        }

        if ($author->image) {
            Storage::disk('public')->delete($author->image);
        }

        $author->delete();

        return redirect()->route('admin.authors.index')
            ->with('success', 'نویسنده با موفقیت حذف شد.');
    }
}
