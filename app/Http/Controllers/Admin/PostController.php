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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
     * TTL کش در دقیقه
     *
     * @var int
     */
    protected $cacheTtl = 1440; // 24 ساعت

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
     * نمایش لیست پست‌ها - بهینه‌سازی شده
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // ایجاد کلید کش یکتا براساس پارامترهای درخواست
        $cacheKey = 'admin_posts_' . md5(json_encode($request->all()));

        return Cache::remember($cacheKey, 10, function() use ($request) {
            // متغیرهای فیلتر
            $search = $request->input('search');
            $categoryId = $request->input('category');
            $authorId = $request->input('author');
            $publisherId = $request->input('publisher');
            $status = $request->input('status');
            $hideContent = $request->input('hide_content');

            // ساخت کوئری اصلی
            $postsQuery = Post::select(['id', 'title', 'slug', 'category_id', 'author_id', 'publisher_id',
                'is_published', 'hide_content', 'publication_year', 'created_at', 'updated_at']);

            // اضافه کردن روابط مورد نیاز با انتخاب فیلدهای ضروری
            $postsQuery->with([
                'category:id,name,slug',
                'author:id,name,slug',
                'publisher:id,name,slug',
                'featuredImage:id,post_id,image_path,hide_image,caption',
                'authors:id,name,slug',
            ]);

            // اعمال فیلترها در یک ترانزاکشن دیتابیس
            DB::connection()->disableQueryLog(); // غیرفعال کردن لاگ کوئری برای بهبود کارایی

            // اعمال فیلترهای جستجو
            if ($search) {
                $postsQuery->where(function($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('english_title', 'like', "%{$search}%")
                        ->orWhere('book_codes', 'like', "%{$search}%");
                });
            }

            if ($categoryId) {
                $postsQuery->where('category_id', $categoryId);
            }

            if ($authorId) {
                $postsQuery->where(function($query) use ($authorId) {
                    $query->where('author_id', $authorId)
                        ->orWhereHas('authors', function($q) use ($authorId) {
                            $q->where('authors.id', $authorId)->limit(1);
                        });
                });
            }

            if ($publisherId) {
                $postsQuery->where('publisher_id', $publisherId);
            }

            if ($status !== null && $status !== '') {
                $postsQuery->where('is_published', $status);
            }

            if ($hideContent !== null && $hideContent !== '') {
                $postsQuery->where('hide_content', $hideContent);
            }

            // مرتب‌سازی و دریافت نتایج
            $postsQuery->latest()->withCount('authors');
            $posts = $postsQuery->paginate(25)->withQueryString();

            // استفاده از کش برای دریافت اطلاعات فیلترها
            $categories = $this->getCachedCategories();
            $authors = $this->getCachedAuthors();
            $publishers = $this->getCachedPublishers();

            return view('admin.posts.index', compact('posts', 'categories', 'authors', 'publishers'));
        });
    }

    /**
     * نمایش فرم ایجاد پست جدید
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // استفاده از کش برای کاهش کوئری‌ها
        $categories = $this->getCachedCategories();
        $authors = $this->getCachedAuthors();
        $publishers = $this->getCachedPublishers();
        $tags = $this->getCachedTags();

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

            // پاک کردن کش مرتبط
            $this->clearRelatedCaches();

            return redirect()->route('admin.posts.index')
                ->with('success', 'پست با موفقیت ایجاد شد.');

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
        // کش کردن اطلاعات پست با روابط آن
        $cacheKey = "admin_post_show_{$post->id}";

        return Cache::remember($cacheKey, 30, function() use ($post) {
            $post->load([
                'user:id,name,email',
                'category:id,name,slug',
                'author:id,name,slug',
                'publisher:id,name,slug',
                'authors:id,name,slug',
                'featuredImage',
                'tags:id,name,slug',
                'images'
            ]);

            return view('admin.posts.show', compact('post'));
        });
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

        // استفاده از کش برای کاهش کوئری‌ها
        $categories = $this->getCachedCategories();
        $authors = $this->getCachedAuthors();
        $publishers = $this->getCachedPublishers();
        $tags = $this->getCachedTags();

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
                $post->featuredImage->update(['hide_image' => $hideImage]);
            }

            // به‌روزرسانی نویسندگان همکار
            $post->authors()->sync($coAuthors);

            // همگام‌سازی برچسب‌ها (اگر وجود دارد)
            if ($tags !== null) {
                $this->syncTags($post, $tags);
            }

            DB::commit();

            // پاک کردن کش مرتبط
            $this->clearRelatedCaches($post->id);

            return redirect()->route('admin.posts.index')
                ->with('success', 'پست با موفقیت بروزرسانی شد.');

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

            // پاک کردن کش مرتبط
            $this->clearRelatedCaches();

            return redirect()->route('admin.posts.index')
                ->with('success', 'پست با موفقیت حذف شد.');

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

            // پاک کردن کش مرتبط
            $this->clearRelatedCaches($post->id);

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
            'hide_image' => $hideImage,
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

        // پاک کردن کش تگ‌ها
        Cache::forget('admin_tags_list');
    }

    /**
     * دریافت دسته‌بندی‌ها از کش
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getCachedCategories()
    {
        return Cache::remember('admin_categories_list', $this->cacheTtl, function() {
            return Category::select(['id', 'name'])->orderBy('name')->get();
        });
    }

    /**
     * دریافت نویسندگان از کش
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getCachedAuthors()
    {
        return Cache::remember('admin_authors_list', $this->cacheTtl, function() {
            return Author::select(['id', 'name'])->orderBy('name')->get();
        });
    }

    /**
     * دریافت ناشران از کش
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getCachedPublishers()
    {
        return Cache::remember('admin_publishers_list', $this->cacheTtl, function() {
            return Publisher::select(['id', 'name'])->orderBy('name')->get();
        });
    }

    /**
     * دریافت تگ‌ها از کش
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getCachedTags()
    {
        return Cache::remember('admin_tags_list', $this->cacheTtl, function() {
            return Tag::select(['id', 'name'])->orderBy('name')->get();
        });
    }

    /**
     * پاک کردن کش‌های مرتبط
     *
     * @param int|null $postId
     * @return void
     */
    private function clearRelatedCaches($postId = null)
    {
        // پاک کردن کش‌های مرتبط با لیست‌ها
        Cache::forget('admin_posts_list');

        // پاک کردن کش‌های مرتبط با فیلترها
        $keys = ['search', 'category', 'author', 'publisher', 'status', 'hide_content', 'page'];
        foreach ($keys as $key) {
            Cache::forget('admin_posts_' . $key);
        }

        // پاک کردن کش‌های مرتبط با پست خاص
        if ($postId) {
            Cache::forget("admin_post_show_{$postId}");
            Cache::forget("admin_post_edit_{$postId}");
        }

        // پاک کردن هرگونه کش دیگر که با الگوی admin_posts_ شروع می‌شود
        foreach (Cache::getStore()->many(Cache::getStore()->keys('admin_posts_*')) as $key => $value) {
            Cache::forget($key);
        }
    }
}
