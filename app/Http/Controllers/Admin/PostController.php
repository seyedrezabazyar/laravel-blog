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
use Mews\Purifier\Facades\Purifier;

class PostController extends Controller
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
     * نمایش لیست پست‌ها
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $posts = Post::with(['user', 'category', 'author', 'publisher', 'featuredImage'])->latest()->paginate(10);
        return view('admin.posts.index', compact('posts'));
    }

    /**
     * نمایش فرم ایجاد پست جدید
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = Category::all();
        $authors = Author::all();
        $publishers = Publisher::all();
        $tags = Tag::orderBy('name')->get();
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
        $validated = $request->validate([
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
            'hide_image' => 'nullable|boolean', // برای تصویر اصلی
        ]);

        // تنظیم مقادیر پیش‌فرض
        $validated['user_id'] = auth()->id();
        $validated['slug'] = Str::slug($validated['title']);
        $validated['md5_hash'] = md5($validated['title'] . time()); // ایجاد یک هش منحصر به فرد

        // پاکسازی محتوا قبل از ذخیره
        $validated['content'] = Purifier::clean($validated['content']);
        if (isset($validated['english_content'])) {
            $validated['english_content'] = Purifier::clean($validated['english_content']);
        }

        // حذف فیلدهای اضافی از آرایه اعتبارسنجی شده
        $coAuthors = $request->input('co_authors', []);
        $hideImage = $request->input('hide_image', false);
        $featuredImage = $request->file('featured_image');
        $tags = null;

        if (isset($validated['tags'])) {
            $tags = $validated['tags'];
            unset($validated['tags']);
        }

        // حذف فیلدهای اضافی که مستقیماً در مدل ذخیره نمی‌شوند
        unset($validated['co_authors']);
        unset($validated['featured_image']);
        unset($validated['hide_image']);

        // ایجاد پست
        $post = Post::create($validated);

        // اضافه کردن تصویر اصلی
        if ($featuredImage) {
            // آپلود تصویر اصلی به هاست دانلود
            $path = $this->downloadHostService->upload($featuredImage, 'posts');

            // اگر آپلود به هاست دانلود با خطا مواجه شد، از روش قبلی استفاده می‌کنیم
            if (!$path) {
                $path = $featuredImage->store('posts', 'public');
            }

            // ایجاد رکورد تصویر برای پست
            PostImage::create([
                'post_id' => $post->id,
                'image_path' => $path,
                'caption' => $post->title,
                'hide_image' => $hideImage,
                'sort_order' => 0
            ]);
        }

        // اضافه کردن نویسندگان همکار
        if (!empty($coAuthors)) {
            $post->authors()->attach($coAuthors);
        }

        // همگام‌سازی برچسب‌ها
        if ($tags !== null) {
            $this->syncTags($post, $tags);
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'پست با موفقیت ایجاد شد.');
    }

    /**
     * نمایش جزئیات یک پست
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\View\View
     */
    public function show(Post $post)
    {
        $post->load(['user', 'category', 'author', 'publisher', 'authors', 'featuredImage', 'tags']);
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
        $categories = Category::all();
        $authors = Author::all();
        $publishers = Publisher::all();
        $coAuthors = $post->authors->pluck('id')->toArray();
        $tags = Tag::orderBy('name')->get();
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
        $validated = $request->validate([
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
            'hide_image' => 'nullable|boolean', // برای تصویر اصلی
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        // پاکسازی محتوا قبل از به‌روزرسانی
        $validated['content'] = Purifier::clean($validated['content']);
        if (isset($validated['english_content'])) {
            $validated['english_content'] = Purifier::clean($validated['english_content']);
        }

        // ذخیره اطلاعات اضافی
        $coAuthors = $request->input('co_authors', []);
        $hideImage = $request->input('hide_image', false);
        $featuredImage = $request->file('featured_image');
        $tags = null;

        if (isset($validated['tags'])) {
            $tags = $validated['tags'];
            unset($validated['tags']);
        }

        // حذف فیلدهای اضافی که مستقیماً در مدل ذخیره نمی‌شوند
        unset($validated['co_authors']);
        unset($validated['featured_image']);
        unset($validated['hide_image']);

        // به‌روزرسانی پست
        $post->update($validated);

        // به‌روزرسانی تصویر
        if ($featuredImage) {
            // ابتدا تصویر قبلی را حذف می‌کنیم
            $existingImage = $post->featuredImage;
            if ($existingImage) {
                // بررسی کنیم که تصویر در هاست دانلود است یا در استوریج محلی
                if (strpos($existingImage->image_path, 'http') === 0 || strpos($existingImage->image_path, 'posts/') === 0) {
                    $this->downloadHostService->delete($existingImage->image_path);
                } else {
                    Storage::disk('public')->delete($existingImage->image_path);
                }

                // رکورد تصویر را حذف می‌کنیم
                $existingImage->delete();
            }

            // آپلود تصویر جدید به هاست دانلود
            $path = $this->downloadHostService->upload($featuredImage, 'posts');

            // اگر آپلود به هاست دانلود با خطا مواجه شد، از روش قبلی استفاده می‌کنیم
            if (!$path) {
                $path = $featuredImage->store('posts', 'public');
            }

            // ایجاد رکورد تصویر جدید برای پست
            PostImage::create([
                'post_id' => $post->id,
                'image_path' => $path,
                'caption' => $post->title,
                'hide_image' => $hideImage,
                'sort_order' => 0
            ]);
        } elseif ($post->featuredImage) {
            // اگر تصویر جدیدی آپلود نشده، اما تصویر فعلی وجود دارد و می‌خواهیم وضعیت نمایش آن را تغییر دهیم
            $post->featuredImage->update(['hide_image' => $hideImage]);
        }

        // به‌روزرسانی نویسندگان همکار
        $post->authors()->sync($coAuthors);

        // همگام‌سازی برچسب‌ها
        if ($tags !== null) {
            $this->syncTags($post, $tags);
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'پست با موفقیت بروزرسانی شد.');
    }

    /**
     * حذف پست از دیتابیس
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Post $post)
    {
        // حذف تصویر اصلی
        $featuredImage = $post->featuredImage;
        if ($featuredImage) {
            // بررسی کنیم که تصویر در هاست دانلود است یا در استوریج محلی
            if (strpos($featuredImage->image_path, 'http') === 0 || strpos($featuredImage->image_path, 'posts/') === 0) {
                $this->downloadHostService->delete($featuredImage->image_path);
            } else {
                Storage::disk('public')->delete($featuredImage->image_path);
            }

            // رکورد تصویر را حذف می‌کنیم
            $featuredImage->delete();
        }

        // حذف رابطه با نویسندگان همکار
        $post->authors()->detach();

        // حذف رابطه با برچسب‌ها
        $post->tags()->detach();

        // حذف پست
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'پست با موفقیت حذف شد.');
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

        // بررسی کنیم که تصویر در هاست دانلود است یا در استوریج محلی
        if (strpos($image->image_path, 'http') === 0 || strpos($image->image_path, 'posts/') === 0) {
            $this->downloadHostService->delete($image->image_path);
        } else {
            Storage::disk('public')->delete($image->image_path);
        }

        // رکورد تصویر را حذف می‌کنیم
        $image->delete();

        return redirect()->route('admin.posts.edit', $post)
            ->with('success', 'تصویر با موفقیت حذف شد.');
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

        foreach ($tagNames as $tagName) {
            if (!empty($tagName)) {
                $tag = Tag::firstOrCreate(
                    ['slug' => Str::slug($tagName)],
                    ['name' => $tagName]
                );
                $tagIds[] = $tag->id;
            }
        }

        // همگام‌سازی برچسب‌ها با پست
        $post->tags()->sync($tagIds);
    }
}
