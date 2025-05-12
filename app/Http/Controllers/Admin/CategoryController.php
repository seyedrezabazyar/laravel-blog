<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('posts')->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        // ثبت تمام داده‌های درخواست در لاگ
        Log::info('Category Store Request Data:', $request->all());

        $validated = $request->validate([
            'name' => 'required|max:255|unique:categories',
            'slug' => 'nullable|max:255|unique:categories',
            'description' => 'nullable|max:1000',
        ]);

        // ثبت داده‌های اعتبارسنجی شده در لاگ
        Log::info('Category Store Validated Data:', $validated);

        // اگر اسلاگ ارائه نشده باشد، آن را از نام ایجاد کنید
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        } else {
            // در غیر این صورت، اسلاگ ارائه شده را اسلاگ کنید
            $validated['slug'] = Str::slug($validated['slug']);
        }

        try {
            // ثبت ساختار جدول categories در لاگ
            $tableStructure = DB::select("DESCRIBE categories");
            Log::info('Categories Table Structure:', $tableStructure);

            // بررسی مدل
            $category = new Category();
            Log::info('Category Model Fillable:', ['fillable' => $category->getFillable()]);

            // ایجاد دسته‌بندی با استفاده از insert مستقیم به جای create
            $validated['created_at'] = now();
            $validated['updated_at'] = now();

            $id = DB::table('categories')->insertGetId($validated);

            // بررسی نتیجه درج
            $newCategory = Category::find($id);
            Log::info('New Category Data:', ['category' => $newCategory ? $newCategory->toArray() : 'Not found']);

            if (!$newCategory) {
                throw new \Exception('دسته‌بندی ایجاد شد اما قابل بازیابی نیست');
            }

            return redirect()->route('admin.categories.index')
                ->with('success', 'دسته‌بندی با موفقیت ایجاد شد.');
        } catch (\Exception $e) {
            // ثبت خطا در لاگ
            Log::error('Category Store Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);

            return redirect()->back()
                ->with('error', 'خطا در ایجاد دسته‌بندی: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Category $category)
    {
        return redirect()->route('blog.category', $category->slug);
    }

    public function edit(Category $category)
    {
        // ثبت اطلاعات دسته‌بندی موجود
        Log::info('Editing Category:', ['category' => $category->toArray()]);

        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        // ثبت تمام داده‌های درخواست در لاگ
        Log::info('Category Update Request Data:', $request->all());
        Log::info('Current Category Data:', ['category' => $category->toArray()]);

        $validated = $request->validate([
            'name' => 'required|max:255|unique:categories,name,' . $category->id,
            'slug' => 'nullable|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|max:1000',
        ]);

        // ثبت داده‌های اعتبارسنجی شده
        Log::info('Category Update Validated Data:', $validated);

        // اگر اسلاگ ارائه نشده باشد، آن را از نام ایجاد کنید
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        } else {
            // در غیر این صورت، اسلاگ ارائه شده را اسلاگ کنید
            $validated['slug'] = Str::slug($validated['slug']);
        }

        try {
            // به‌روزرسانی مستقیم با استفاده از query builder
            $updated = DB::table('categories')
                ->where('id', $category->id)
                ->update($validated);

            Log::info('Direct DB Update Result:', ['updated_rows' => $updated]);

            // بارگذاری مجدد دسته‌بندی برای اطمینان از به‌روزرسانی
            $refreshedCategory = Category::find($category->id);
            Log::info('Refreshed Category After Update:', ['category' => $refreshedCategory->toArray()]);

            if (!$updated) {
                Log::warning('Update had no effect on database');
            }

            return redirect()->route('admin.categories.index')
                ->with('success', 'دسته‌بندی با موفقیت بروزرسانی شد.');
        } catch (\Exception $e) {
            // ثبت خطا در لاگ
            Log::error('Category Update Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);

            return redirect()->back()
                ->with('error', 'خطا در بروزرسانی دسته‌بندی: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Category $category)
    {
        if ($category->posts()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'این دسته‌بندی دارای پست است و نمی‌توان آن را حذف کرد.');
        }

        try {
            Log::info('Attempting to delete category:', ['category' => $category->toArray()]);
            $deleted = $category->delete();
            Log::info('Category deleted:', ['result' => $deleted]);

            return redirect()->route('admin.categories.index')
                ->with('success', 'دسته‌بندی با موفقیت حذف شد.');
        } catch (\Exception $e) {
            Log::error('Category delete error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.categories.index')
                ->with('error', 'خطا در حذف دسته‌بندی: ' . $e->getMessage());
        }
    }
}
