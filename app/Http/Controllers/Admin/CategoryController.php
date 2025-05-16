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
        // ثبت مقادیر امن در لاگ - فقط اشاره به عملیات انجام شده
        Log::info('درخواست ذخیره دسته‌بندی جدید دریافت شد');

        $validated = $request->validate([
            'name' => 'required|max:255|unique:categories',
            'slug' => 'nullable|max:255|unique:categories',
            'description' => 'nullable|max:1000',
        ]);

        // ثبت داده‌های امن در لاگ - بدون اطلاعات حساس
        Log::info('داده‌های دسته‌بندی اعتبارسنجی شد', [
            'name_length' => strlen($validated['name']),
            'has_slug' => !empty($validated['slug']),
            'has_description' => !empty($validated['description'])
        ]);

        // اگر اسلاگ ارائه نشده باشد، آن را از نام ایجاد کنید
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        } else {
            // در غیر این صورت، اسلاگ ارائه شده را اسلاگ کنید
            $validated['slug'] = Str::slug($validated['slug']);
        }

        try {
            // ثبت ساختار جدول categories در لاگ - فقط اطلاعات ساختاری
            $tableStructure = DB::select("DESCRIBE categories");
            Log::info('ساختار جدول categories بررسی شد', [
                'column_count' => count($tableStructure)
            ]);

            // بررسی مدل
            $category = new Category();
            Log::info('مدل Category آماده شد', [
                'fillable_count' => count($category->getFillable())
            ]);

            // ایجاد دسته‌بندی با استفاده از insert مستقیم به جای create
            $validated['created_at'] = now();
            $validated['updated_at'] = now();

            $id = DB::table('categories')->insertGetId($validated);

            // بررسی نتیجه درج
            $newCategory = Category::find($id);
            Log::info('دسته‌بندی جدید ایجاد شد', [
                'id' => $id,
                'name' => $newCategory ? $newCategory->name : 'Not found'
            ]);

            if (!$newCategory) {
                throw new \Exception('دسته‌بندی ایجاد شد اما قابل بازیابی نیست');
            }

            return redirect()->route('admin.categories.index')
                ->with('success', 'دسته‌بندی با موفقیت ایجاد شد.');
        } catch (\Exception $e) {
            // ثبت خطا در لاگ - بدون اطلاعات حساس
            Log::error('خطا در ایجاد دسته‌بندی', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode()
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
        // ثبت اطلاعات امن - فقط شناسه
        Log::info('صفحه ویرایش دسته‌بندی فراخوانی شد', [
            'category_id' => $category->id
        ]);

        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        // ثبت درخواست به‌روزرسانی - بدون اطلاعات حساس
        Log::info('درخواست به‌روزرسانی دسته‌بندی دریافت شد', [
            'category_id' => $category->id
        ]);

        $validated = $request->validate([
            'name' => 'required|max:255|unique:categories,name,' . $category->id,
            'slug' => 'nullable|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|max:1000',
        ]);

        // ثبت داده‌های اعتبارسنجی شده - بدون اطلاعات حساس
        Log::info('داده‌های به‌روزرسانی دسته‌بندی اعتبارسنجی شد', [
            'category_id' => $category->id,
            'name_changed' => $category->name !== $validated['name'],
            'slug_provided' => !empty($validated['slug']),
            'description_changed' => $category->description !== ($validated['description'] ?? null)
        ]);

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

            Log::info('به‌روزرسانی مستقیم دسته‌بندی انجام شد', [
                'category_id' => $category->id,
                'update_result' => $updated ? 'successful' : 'no changes'
            ]);

            // بارگذاری مجدد دسته‌بندی برای اطمینان از به‌روزرسانی
            $refreshedCategory = Category::find($category->id);
            Log::info('دسته‌بندی پس از به‌روزرسانی بازیابی شد', [
                'category_id' => $refreshedCategory->id,
                'name' => $refreshedCategory->name
            ]);

            if (!$updated) {
                Log::warning('به‌روزرسانی تأثیری در پایگاه داده نداشت', [
                    'category_id' => $category->id
                ]);
            }

            return redirect()->route('admin.categories.index')
                ->with('success', 'دسته‌بندی با موفقیت بروزرسانی شد.');
        } catch (\Exception $e) {
            // ثبت خطا در لاگ - بدون اطلاعات حساس
            Log::error('خطا در به‌روزرسانی دسته‌بندی', [
                'category_id' => $category->id,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode()
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
            Log::info('تلاش برای حذف دسته‌بندی', [
                'category_id' => $category->id,
                'category_name' => $category->name
            ]);

            $deleted = $category->delete();

            Log::info('دسته‌بندی حذف شد', [
                'category_id' => $category->id,
                'result' => $deleted ? 'successful' : 'failed'
            ]);

            return redirect()->route('admin.categories.index')
                ->with('success', 'دسته‌بندی با موفقیت حذف شد.');
        } catch (\Exception $e) {
            Log::error('خطا در حذف دسته‌بندی', [
                'category_id' => $category->id,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ]);

            return redirect()->route('admin.categories.index')
                ->with('error', 'خطا در حذف دسته‌بندی: ' . $e->getMessage());
        }
    }
}
