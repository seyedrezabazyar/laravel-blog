<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AuthorController extends Controller
{
    /**
     * نمایش لیست نویسندگان
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $authors = Author::withCount(['posts as books_count' => function($query) {
            $query->where('is_published', true);
        }])->paginate(10);

        return view('admin.authors.index', compact('authors'));
    }

    /**
     * نمایش فرم ویرایش نویسنده
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            // لاگ برای اشکال‌زدایی
            Log::info('Edit author page accessed', ['author_id' => $id]);

            // دریافت نویسنده با ID
            $author = Author::findOrFail($id);

            // نمایش فرم ویرایش
            return view('admin.authors.edit', compact('author'));
        } catch (\Exception $e) {
            Log::error('Error in edit author', [
                'author_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.authors.index')
                ->with('error', 'خطا در بارگذاری صفحه ویرایش نویسنده: ' . $e->getMessage());
        }
    }

    /**
     * بروزرسانی نویسنده در دیتابیس
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // لاگ برای اشکال‌زدایی، فقط کلیدهای درخواست لاگ می‌شوند
            Log::info('Update author request', [
                'author_id' => $id,
                'fields' => array_keys($request->all())
            ]);

            // دریافت نویسنده با ID
            $author = Author::findOrFail($id);

            // اعتبارسنجی داده‌های ورودی
            $validated = $request->validate([
                'name' => 'required|max:255',
                'biography' => 'nullable',
            ]);

            // اسلاگ به صورت خودکار از نام ساخته می‌شود
            $validated['slug'] = Str::slug($validated['name']);

            // بروزرسانی نویسنده
            $author->update($validated);

            Log::info('Author updated', ['id' => $author->id, 'name' => $author->name]);

            return redirect()->route('admin.authors.index')
                ->with('success', 'نویسنده با موفقیت به‌روزرسانی شد.');
        } catch (\Exception $e) {
            Log::error('Error updating author', [
                'author_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'خطا در به‌روزرسانی نویسنده: ' . $e->getMessage())
                ->withInput();
        }
    }
}
