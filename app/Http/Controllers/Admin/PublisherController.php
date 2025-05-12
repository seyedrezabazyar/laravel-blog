<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublisherController extends Controller
{
    /**
     * نمایش لیست ناشران
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // بررسی وجود جدول
        if (!Schema::hasTable('publishers')) {
            Log::error('Table publishers does not exist');
            return redirect()->route('dashboard')
                ->with('error', 'جدول ناشران وجود ندارد. لطفاً با مدیر سیستم تماس بگیرید.');
        }

        try {
            $publishers = Publisher::query();

            // اگر متد withCount وجود دارد، از آن استفاده کنیم
            if (method_exists($publishers, 'withCount')) {
                $publishers = $publishers->withCount('posts');
            }

            $publishers = $publishers->paginate(10);

            return view('admin.publishers.index', compact('publishers'));
        } catch (\Exception $e) {
            Log::error('Error in publishers index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'خطا در بارگذاری لیست ناشران: ' . $e->getMessage());
        }
    }

    /**
     * نمایش فرم ویرایش ناشر
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            // لاگ برای اشکال‌زدایی
            Log::info('Edit publisher page accessed', ['publisher_id' => $id]);

            // دریافت ناشر با ID
            $publisher = Publisher::findOrFail($id);

            // نمایش فرم ویرایش
            return view('admin.publishers.edit', compact('publisher'));
        } catch (\Exception $e) {
            Log::error('Error in edit publisher', [
                'publisher_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.publishers.index')
                ->with('error', 'خطا در بارگذاری صفحه ویرایش ناشر: ' . $e->getMessage());
        }
    }

    /**
     * بروزرسانی ناشر در دیتابیس
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // لاگ برای اشکال‌زدایی
            Log::info('Update publisher request', [
                'publisher_id' => $id,
                'data' => $request->all()
            ]);

            // دریافت ناشر با ID
            $publisher = Publisher::findOrFail($id);

            // اعتبارسنجی داده‌های ورودی - بدون فیلد لوگو
            $validated = $request->validate([
                'name' => 'required|max:255',
                'slug' => 'nullable|max:255|unique:publishers,slug,' . $id,
                'description' => 'nullable',
            ]);

            // اگر اسلاگ ارائه نشده باشد، آن را از نام ایجاد کنید
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            } else {
                // در غیر این صورت، اسلاگ ارائه شده را اسلاگ کنید
                $validated['slug'] = Str::slug($validated['slug']);
            }

            // بروزرسانی ناشر
            $publisher->update($validated);

            Log::info('Publisher updated', ['id' => $publisher->id, 'name' => $publisher->name]);

            return redirect()->route('admin.publishers.index')
                ->with('success', 'ناشر با موفقیت به‌روزرسانی شد.');
        } catch (\Exception $e) {
            Log::error('Error updating publisher', [
                'publisher_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'خطا در به‌روزرسانی ناشر: ' . $e->getMessage())
                ->withInput();
        }
    }
}
