<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\PostImage;
use App\Models\Tag;
use App\Services\DownloadHostService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Mews\Purifier\Facades\Purifier;

class PostController extends Controller
{
    /**
     * سرویس مدیریت هاست دانلود
     */
    protected $downloadHostService;

    /**
     * ایجاد نمونه جدید از کنترلر
     */
    public function __construct(DownloadHostService $downloadHostService)
    {
        $this->downloadHostService = $downloadHostService;
    }

    /**
     * نمایش لیست خیلی ساده پست‌ها با SQL خام
     */
    public function index(Request $request)
    {
        // غیرفعال کردن لاگ کوئری
        DB::connection()->disableQueryLog();

        // برگرداندن HTML ساده به جای استفاده از ویو
        $html = '<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت کتاب‌ها</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">مدیریت کتاب‌ها</h1>
            <a href="' . route('admin.posts.create') . '" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">افزودن کتاب جدید</a>
        </div>';

        // پیام‌های فلش
        if (session('success')) {
            $html .= '<div class="bg-green-100 border-r-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                ' . session('success') . '
            </div>';
        }

        // شروع جدول
        $html .= '<div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">عنوان</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">عملیات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">';

        // گرفتن پست‌ها با SQL خام
        try {
            $page = max(1, (int) $request->input('page', 1));
            $perPage = 20;
            $offset = ($page - 1) * $perPage;

            // کوئری کاملاً خام برای گرفتن فقط ID و عنوان
            $posts = DB::select("SELECT id, title FROM posts ORDER BY created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset]);

            // کوئری برای شمارش کل
            $totalPosts = DB::selectOne("SELECT COUNT(*) as total FROM posts");
            $total = $totalPosts->total;

            // اطلاعات پاگینیشن
            $lastPage = max(1, ceil($total / $perPage));
            $hasPrevious = $page > 1;
            $hasNext = $page < $lastPage;

            // نمایش پست‌ها
            if (count($posts) > 0) {
                foreach ($posts as $post) {
                    $html .= '<tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' . htmlspecialchars($post->title) . '</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2 space-x-reverse">
                                    <a href="' . route('admin.posts.edit', $post->id) . '" class="ml-2 text-indigo-600 hover:text-indigo-900">ویرایش</a>
                                    <a href="' . route('admin.posts.show', $post->id) . '" class="ml-2 text-blue-600 hover:text-blue-900">نمایش</a>
                                    <form action="' . route('admin.posts.destroy', $post->id) . '" method="POST" class="inline" onsubmit="return confirm(\'آیا از حذف این کتاب اطمینان دارید؟\');">
                                        ' . csrf_field() . '
                                        ' . method_field('DELETE') . '
                                        <button type="submit" class="text-red-600 hover:text-red-900">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>';
                }
            } else {
                $html .= '<tr><td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">هیچ کتابی یافت نشد</td></tr>';
            }

            // پایان جدول
            $html .= '</tbody>
            </table>
        </div>';

            // پاگینیشن ساده
            $html .= '<div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    نمایش ' . count($posts) . ' کتاب از ' . $total . ' کتاب
                </div>
                <div class="flex space-x-2 space-x-reverse">';

            // دکمه قبلی
            if ($hasPrevious) {
                $html .= '<a href="' . route('admin.posts.index', ['page' => $page - 1]) . '" class="px-4 py-2 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50">قبلی</a>';
            } else {
                $html .= '<span class="px-4 py-2 border border-gray-200 rounded-md text-sm bg-gray-100 text-gray-400 cursor-not-allowed">قبلی</span>';
            }

            // دکمه بعدی
            if ($hasNext) {
                $html .= '<a href="' . route('admin.posts.index', ['page' => $page + 1]) . '" class="px-4 py-2 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50">بعدی</a>';
            } else {
                $html .= '<span class="px-4 py-2 border border-gray-200 rounded-md text-sm bg-gray-100 text-gray-400 cursor-not-allowed">بعدی</span>';
            }

            $html .= '</div>
            </div>';

        } catch (\Exception $e) {
            // در صورت خطا
            $html .= '<div class="bg-red-100 border-r-4 border-red-500 text-red-700 p-4 rounded mb-6">
                خطا در بارگذاری لیست پست‌ها: ' . $e->getMessage() . '
            </div>';

            \Log::error('Error in posts index: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
        }

        // پایان HTML
        $html .= '</div>
</body>
</html>';

        return response($html);
    }

    /**
     * نمایش فرم ایجاد پست جدید
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // استفاده از کوئری‌های ساده با انتخاب حداقل فیلدها
        $categories = Category::select(['id', 'name'])->orderBy('name')->get();
        $authors = Author::select(['id', 'name'])->orderBy('name')->get();
        $publishers = Publisher::select(['id', 'name'])->orderBy('name')->get();
        $tags = Tag::select(['id', 'name'])->orderBy('name')->get();

        return view('admin.posts.create', compact('categories', 'authors', 'publishers', 'tags'));
    }

    /**
     * ذخیره پست جدید در دیتابیس
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // اعتبارسنجی داده‌ها
        $validated = $this->validatePostData($request);

        // اطلاعات اضافی
        $coAuthors = $request->input('co_authors', []);
        $hideImage = $request->input('hide_image', false);
        $featuredImage = $request->file('featured_image');
        $tags = $request->input('tags');

        // حذف فیلدهای اضافی که مستقیماً در مدل ذخیره نمی‌شوند
        unset($validated['co_authors']);
        unset($validated['featured_image']);
        unset($validated['hide_image']);
        unset($validated['tags']);

        // ایجاد پست و روابط آن در یک ترانزاکشن
        try {
            DB::beginTransaction();

            // تنظیم مقادیر پیش‌فرض
            $validated['user_id'] = auth()->id();
            $validated['slug'] = Str::slug($validated['title']);
            $validated['md5_hash'] = md5($validated['title'] . time());

            // پاکسازی محتوا
            $validated['content'] = Purifier::clean($validated['content']);
            if (isset($validated['english_content'])) {
                $validated['english_content'] = Purifier::clean($validated['english_content']);
            }

            // ایجاد پست
            $post = Post::create($validated);

            // اضافه کردن تصویر اصلی
            if ($featuredImage) {
                $this->savePostImage($post, $featuredImage, $hideImage);
            }

            // اضافه کردن نویسندگان همکار (اگر وجود دارد)
            if (!empty($coAuthors)) {
                $post->authors()->attach($coAuthors);
            }

            // همگام‌سازی برچسب‌ها (اگر وجود دارد)
            if ($tags) {
                $this->syncTags($post, $tags);
            }

            DB::commit();

            return redirect()->route('admin.posts.index')
                ->with('success', 'کتاب با موفقیت ایجاد شد.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'خطا در ایجاد پست: ' . $e->getMessage());
        }
    }

    /**
     * نمایش جزئیات یک پست
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\View\View
     */
    public function show(Post $post)
    {
        // بارگذاری روابط مورد نیاز
        $post->load([
            'user:id,name,email',
            'category:id,name,slug',
            'author:id,name,slug',
            'publisher:id,name,slug',
            'authors:id,name,slug',
            'featuredImage',
            'tags:id,name,slug',
            'images' => function($query) {
                $query->orderBy('sort_order');
            }
        ]);

        return view('admin.posts.show', compact('post'));
    }

    /**
     * نمایش فرم ویرایش پست
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\View\View
     */
    public function edit(Post $post)
    {
        // لود کردن روابط مورد نیاز
        $post->load(['authors', 'tags', 'featuredImage']);
        $coAuthors = $post->authors->pluck('id')->toArray();

        // استفاده از کوئری‌های ساده با انتخاب حداقل فیلدها
        $categories = Category::select(['id', 'name'])->orderBy('name')->get();
        $authors = Author::select(['id', 'name'])->orderBy('name')->get();
        $publishers = Publisher::select(['id', 'name'])->orderBy('name')->get();
        $tags = Tag::select(['id', 'name'])->orderBy('name')->get();

        return view('admin.posts.edit', compact('post', 'categories', 'authors', 'publishers', 'coAuthors', 'tags'));
    }

    /**
     * به‌روزرسانی پست در دیتابیس
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Post $post)
    {
        // اعتبارسنجی داده‌ها
        $validated = $this->validatePostData($request);

        // اطلاعات اضافی
        $coAuthors = $request->input('co_authors', []);
        $hideImage = $request->input('hide_image', false);
        $featuredImage = $request->file('featured_image');
        $tags = $request->input('tags');

        // حذف فیلدهای اضافی که مستقیماً در مدل ذخیره نمی‌شوند
        unset($validated['co_authors']);
        unset($validated['featured_image']);
        unset($validated['hide_image']);
        unset($validated['tags']);

        // به‌روزرسانی پست و روابط آن در یک ترانزاکشن
        try {
            DB::beginTransaction();

            // تنظیم مقادیر پیش‌فرض
            $validated['slug'] = Str::slug($validated['title']);

            // پاکسازی محتوا
            $validated['content'] = Purifier::clean($validated['content']);
            if (isset($validated['english_content'])) {
                $validated['english_content'] = Purifier::clean($validated['english_content']);
            }

            // به‌روزرسانی پست
            $post->update($validated);

            // به‌روزرسانی تصویر
            if ($featuredImage) {
                // حذف تصویر قبلی
                $this->deletePostImage($post->featuredImage);

                // ذخیره تصویر جدید
                $this->savePostImage($post, $featuredImage, $hideImage);
            } elseif ($post->featuredImage) {
                // به‌روزرسانی وضعیت نمایش تصویر فعلی
                $post->featuredImage->update([
                    'hide_image' => $hideImage ? 'hidden' : 'visible'
                ]);
            }

            // به‌روزرسانی نویسندگان همکار
            $post->authors()->sync($coAuthors);

            // همگام‌سازی برچسب‌ها (اگر وجود دارد)
            if ($tags !== null) {
                $this->syncTags($post, $tags);
            }

            DB::commit();

            return redirect()->route('admin.posts.index')
                ->with('success', 'کتاب با موفقیت بروزرسانی شد.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'خطا در به‌روزرسانی پست: ' . $e->getMessage());
        }
    }

    /**
     * حذف پست از دیتابیس
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Post $post)
    {
        try {
            DB::beginTransaction();

            // حذف تصویر اصلی
            $this->deletePostImage($post->featuredImage);

            // حذف رابطه با نویسندگان همکار
            $post->authors()->detach();

            // حذف رابطه با برچسب‌ها
            $post->tags()->detach();

            // حذف تمام تصاویر مرتبط
            foreach ($post->images as $image) {
                $this->deletePostImage($image);
            }

            // حذف پست
            $post->delete();

            DB::commit();

            return redirect()->route('admin.posts.index')
                ->with('success', 'کتاب با موفقیت حذف شد.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'خطا در حذف پست: ' . $e->getMessage());
        }
    }

    /**
     * حذف تصویر
     *
     * @param PostImage $image
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyImage(PostImage $image)
    {
        $post = $image->post;

        try {
            // حذف تصویر
            $this->deletePostImage($image);

            return redirect()->route('admin.posts.edit', $post)
                ->with('success', 'تصویر با موفقیت حذف شد.');

        } catch (\Exception $e) {
            return redirect()->route('admin.posts.edit', $post)
                ->with('error', 'خطا در حذف تصویر: ' . $e->getMessage());
        }
    }

    /**
     * تأیید اعتبار داده‌های پست
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    private function validatePostData(Request $request)
    {
        return $request->validate([
            'title' => 'required|max:255',
            'english_title' => 'nullable|max:255',
            'content' => 'required',
            'english_content' => 'nullable',
            'category_id' => 'required|exists:categories,id',
            'author_id' => 'nullable|exists:authors,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'featured_image' => 'nullable|image|max:2048',
            'language' => 'nullable|max:50',
            'publication_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'format' => 'nullable|max:50',
            'book_codes' => 'nullable',
            'tags' => 'nullable|string',
            'purchase_link' => 'nullable|url',
            'is_published' => 'boolean',
            'hide_content' => 'boolean',
            'co_authors' => 'nullable|array',
            'co_authors.*' => 'exists:authors,id',
            'hide_image' => 'nullable|boolean',
        ]);
    }

    /**
     * ذخیره تصویر پست
     *
     * @param Post $post
     * @param \Illuminate\Http\UploadedFile $image
     * @param bool $hideImage
     * @return PostImage
     */
    private function savePostImage(Post $post, $image, $hideImage = false)
    {
        // آپلود تصویر به هاست دانلود
        $path = $this->downloadHostService->upload($image, 'posts');

        // اگر آپلود به هاست دانلود با خطا مواجه شد، از روش قبلی استفاده می‌کنیم
        if (!$path) {
            $path = $image->store('posts', 'public');
        }

        // ایجاد رکورد تصویر برای پست
        return PostImage::create([
            'post_id' => $post->id,
            'image_path' => $path,
            'caption' => $post->title,
            'hide_image' => $hideImage ? 'hidden' : 'visible',
            'sort_order' => 0
        ]);
    }

    /**
     * حذف تصویر پست
     *
     * @param PostImage|null $image
     * @return bool
     */
    private function deletePostImage($image)
    {
        if (!$image) {
            return false;
        }

        // بررسی کنیم که تصویر در هاست دانلود است یا در استوریج محلی
        if (strpos($image->image_path, 'http') === 0 || strpos($image->image_path, 'posts/') === 0) {
            $this->downloadHostService->delete($image->image_path);
        } else {
            Storage::disk('public')->delete($image->image_path);
        }

        // حذف رکورد تصویر
        return $image->delete();
    }

    /**
     * همگام‌سازی برچسب‌های پست
     *
     * @param Post $post
     * @param string $tagsString
     * @return void
     */
    private function syncTags(Post $post, string $tagsString)
    {
        // جداسازی برچسب‌ها با کاما
        $tagNames = array_map('trim', explode(',', $tagsString));
        $tagIds = [];

        // بهینه‌سازی: استفاده از درج گروهی به جای درج یک به یک
        $existingTags = Tag::whereIn('name', $tagNames)->get()->keyBy('name');
        $newTagNames = [];

        foreach ($tagNames as $tagName) {
            if (empty($tagName)) continue;

            // اگر تگ وجود دارد
            if (isset($existingTags[$tagName])) {
                $tagIds[] = $existingTags[$tagName]->id;
            }
            // اگر تگ جدید است
            else {
                $newTagNames[] = $tagName;
            }
        }

        // ایجاد تگ‌های جدید با یک کوئری (در صورت وجود)
        if (!empty($newTagNames)) {
            $newTags = [];
            foreach ($newTagNames as $name) {
                $newTags[] = Tag::create([
                    'name' => $name,
                    'slug' => Str::slug($name)
                ]);
            }

            // اضافه کردن شناسه تگ‌های جدید به آرایه شناسه‌ها
            foreach ($newTags as $tag) {
                $tagIds[] = $tag->id;
            }
        }

        // همگام‌سازی برچسب‌ها با پست
        $post->tags()->sync($tagIds);
    }
}
