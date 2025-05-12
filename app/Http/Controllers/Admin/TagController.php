<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TagController extends Controller
{
    /**
     * نمایش لیست تگ‌ها
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $tags = Tag::withCount(['posts as posts_count' => function($query) {
            $query->where('is_published', true);
        }])->paginate(20);

        return view('admin.tags.index', compact('tags'));
    }

    /**
     * نمایش فرم ویرایش تگ
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            // لاگ برای اشکال‌زدایی
            Log::info('Edit tag page accessed', ['tag_id' => $id]);

            // دریافت تگ با ID
            $tag = Tag::findOrFail($id);

            // نمایش فرم ویرایش
            return view('admin.tags.edit', compact('tag'));
        } catch (\Exception $e) {
            Log::error('Error in edit tag', [
                'tag_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.tags.index')
                ->with('error', 'خطا در بارگذاری صفحه ویرایش تگ: ' . $e->getMessage());
        }
    }

    /**
     * بروزرسانی تگ در دیتابیس
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // لاگ برای اشکال‌زدایی
            Log::info('Update tag request', [
                'tag_id' => $id,
                'data' => $request->all()
            ]);

            // دریافت تگ با ID
            $tag = Tag::findOrFail($id);

            // اعتبارسنجی داده‌های ورودی
            $validated = $request->validate([
                'name' => 'required|max:255',
                'description' => 'nullable',
            ]);

            // اسلاگ به صورت خودکار از نام ساخته می‌شود
            $validated['slug'] = Str::slug($validated['name']);

            // بروزرسانی تگ
            $tag->update($validated);

            Log::info('Tag updated', ['id' => $tag->id, 'name' => $tag->name]);

            return redirect()->route('admin.tags.index')
                ->with('success', 'تگ با موفقیت به‌روزرسانی شد.');
        } catch (\Exception $e) {
            Log::error('Error updating tag', [
                'tag_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'خطا در به‌روزرسانی تگ: ' . $e->getMessage())
                ->withInput();
        }
    }
}
