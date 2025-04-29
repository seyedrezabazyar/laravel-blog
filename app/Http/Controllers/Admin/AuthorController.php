<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Services\DownloadHostService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AuthorController extends Controller
{
    /**
     * سرویس مدیریت هاست دانلود
     *
     * @var DownloadHostService
     */
    protected $downloadHostService;

    /**
     * ایجاد نمونه جدید از کنترلر
     *
     * @param DownloadHostService $downloadHostService
     */
    public function __construct(DownloadHostService $downloadHostService)
    {
        $this->downloadHostService = $downloadHostService;
    }

    /**
     * نمایش لیست نویسندگان
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $authors = Author::withCount('posts')->paginate(10);
        return view('admin.authors.index', compact('authors'));
    }

    /**
     * نمایش فرم ایجاد نویسنده جدید
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.authors.create');
    }

    /**
     * ذخیره نویسنده جدید در دیتابیس
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'biography' => 'nullable',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            // آپلود تصویر به هاست دانلود
            $path = $this->downloadHostService->upload($request->file('image'), 'authors');

            // اگر آپلود به هاست دانلود با خطا مواجه شد، از روش قبلی استفاده می‌کنیم
            if (!$path) {
                $path = $request->file('image')->store('authors', config('filesystems.default_public', 'public'));
            }

            $validated['image'] = $path;
        }

        Author::create($validated);

        return redirect()->route('admin.authors.index')
            ->with('success', 'نویسنده با موفقیت ایجاد شد.');
    }

    /**
     * نمایش جزئیات یک نویسنده
     *
     * @param  \App\Models\Author  $author
     * @return \Illuminate\View\View
     */
    public function show(Author $author)
    {
        // بارگذاری پست‌هایی که این نویسنده در آن‌ها نقش دارد
        // (هم به عنوان نویسنده اصلی و هم نویسنده همکار)
        $author->load(['posts', 'coAuthoredPosts']);

        // ترکیب هر دو نوع پست
        $books = $author->posts->merge($author->coAuthoredPosts)->unique('id');

        return view('admin.authors.show', compact('author', 'books'));
    }

    /**
     * نمایش فرم ویرایش نویسنده
     *
     * @param  \App\Models\Author  $author
     * @return \Illuminate\View\View
     */
    public function edit(Author $author)
    {
        return view('admin.authors.edit', compact('author'));
    }

    /**
     * به‌روزرسانی نویسنده در دیتابیس
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Author  $author
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Author $author)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'biography' => 'nullable',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            // حذف تصویر قبلی
            if ($author->image) {
                // بررسی کنیم که تصویر در هاست دانلود است یا در استوریج محلی
                if (strpos($author->image, 'http') === 0 || strpos($author->image, 'authors/') === 0) {
                    $this->downloadHostService->delete($author->image);
                } else {
                    Storage::disk('public')->delete($author->image);
                }
            }

            // آپلود تصویر جدید به هاست دانلود
            $path = $this->downloadHostService->upload($request->file('image'), 'authors');

            // اگر آپلود به هاست دانلود با خطا مواجه شد، از روش قبلی استفاده می‌کنیم
            if (!$path) {
                $path = $request->file('image')->store('authors', config('filesystems.default_public'));
            }

            $validated['image'] = $path;
        }

        $author->update($validated);

        return redirect()->route('admin.authors.index')
            ->with('success', 'نویسنده با موفقیت به‌روزرسانی شد.');
    }

    /**
     * حذف نویسنده از دیتابیس
     *
     * @param  \App\Models\Author  $author
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Author $author)
    {
        // بررسی اینکه آیا نویسنده دارای پست است
        if ($author->posts()->count() > 0 || $author->coAuthoredPosts()->count() > 0) {
            return redirect()->route('admin.authors.index')
                ->with('error', 'این نویسنده دارای کتاب است و نمی‌توان آن را حذف کرد.');
        }

        // حذف تصویر نویسنده
        if ($author->image) {
            // بررسی کنیم که تصویر در هاست دانلود است یا در استوریج محلی
            if (strpos($author->image, 'http') === 0 || strpos($author->image, 'authors/') === 0) {
                $this->downloadHostService->delete($author->image);
            } else {
                Storage::disk('public')->delete($author->image);
            }
        }

        $author->delete();

        return redirect()->route('admin.authors.index')
            ->with('success', 'نویسنده با موفقیت حذف شد.');
    }
}
