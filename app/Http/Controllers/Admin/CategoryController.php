<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('posts')->paginate(10);
        return view('admin.categories.blade.php.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.blade.php.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:categories.blade.php',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Category::create($validated);

        return redirect()->route('admin.categories.blade.php.index')
            ->with('success', 'دسته‌بندی با موفقیت ایجاد شد.');
    }

    public function show(Category $category)
    {
        return redirect()->route('blog.category', $category->slug);
    }

    public function edit(Category $category)
    {
        return view('admin.categories.blade.php.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:categories.blade.php,name,' . $category->id,
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $category->update($validated);

        return redirect()->route('admin.categories.blade.php.index')
            ->with('success', 'دسته‌بندی با موفقیت بروزرسانی شد.');
    }

    public function destroy(Category $category)
    {
        if ($category->posts()->count() > 0) {
            return redirect()->route('admin.categories.blade.php.index')
                ->with('error', 'این دسته‌بندی دارای پست است و نمی‌توان آن را حذف کرد.');
        }

        $category->delete();

        return redirect()->route('admin.categories.blade.php.index')
            ->with('success', 'دسته‌بندی با موفقیت حذف شد.');
    }
}
