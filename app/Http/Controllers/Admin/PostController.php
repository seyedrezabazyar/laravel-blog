<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\PostImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mews\Purifier\Facades\Purifier;

class PostController extends Controller
{
    /**
     * نمایش لیست پست‌ها با کوئری بهینه
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // غیرفعال کردن لاگ کوئری برای بهبود عملکرد
        DB::connection()->disableQueryLog();

        // تشخیص پارامتر صفحه‌بندی
        $page = $request->input('page', 1);
        $perPage = 20;

        // استفاده از کوئری بهینه و کش برای کاهش بار دیتابیس
        $cacheKey = "admin_posts_page_{$page}";

        // استفاده از کش با عدم کش‌کردن صفحات بعد از صفحه 1 برای اطمینان از به‌روز بودن
        if ($page == 1) {
            $posts = Cache::remember($cacheKey, 5, function () use ($perPage) {
                return Post::select(['id', 'title', 'is_published', 'hide_content', 'slug', 'created_at'])
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);
            });
        } else {
            $posts = Post::select(['id', 'title', 'is_published', 'hide_content', 'slug', 'created_at'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        }

        // بازگرداندن نمای استاندارد
        return view('admin.posts.index', compact('posts'));
    }

    /**
     * نمایش فرم ویرایش پست با بهینه‌سازی بارگذاری داده‌ها
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\View\View
     */
    public function edit(Post $post)
    {
        // غیرفعال کردن لاگ کوئری برای بهبود عملکرد
        DB::connection()->disableQueryLog();

        // استفاده از توابع خام SQL برای بهینه‌سازی بیشتر
        try {
            // بارگذاری مستقیم پست با کوئری اختصاصی - کاهش فشار روی ORM
            $postData = DB::table('posts')
                ->where('id', $post->id)
                ->select([
                    'id', 'title', 'english_title', 'slug', 'content', 'english_content',
                    'category_id', 'author_id', 'publisher_id', 'language',
                    'publication_year', 'format', 'book_codes', 'purchase_link',
                    'is_published', 'hide_content'
                ])
                ->first();

            if (!$postData) {
                return redirect()->route('admin.posts.index')
                    ->with('error', 'پست مورد نظر یافت نشد.');
            }

            // تبدیل به مدل پست برای استفاده در ویو
            $post = Post::make((array)$postData);
            $post->exists = true;
            $post->id = $postData->id;

            // بارگذاری تصویر شاخص - فقط اگر وجود داشته باشد
            $featuredImage = Cache::remember("post_{$post->id}_featured_image", 60, function() use ($post) {
                return DB::table('post_images')
                    ->where('post_id', $post->id)
                    ->select(['id', 'post_id', 'image_path', 'hide_image'])
                    ->orderBy('sort_order')
                    ->first();
            });

            if ($featuredImage) {
                $post->setRelation('featuredImage', PostImage::make((array)$featuredImage));
            }

            // بارگذاری داده‌های ثابت با کش طولانی‌مدت - این داده‌ها به ندرت تغییر می‌کنند
            $categories = Cache::remember('admin_categories_list', 3600, function() {
                return Category::select(['id', 'name'])
                    ->orderBy('name')
                    ->get();
            });

            $authors = Cache::remember('admin_authors_list', 3600, function() {
                return Author::select(['id', 'name'])
                    ->orderBy('name')
                    ->get();
            });

            $publishers = Cache::remember('admin_publishers_list', 3600, function() {
                return Publisher::select(['id', 'name'])
                    ->orderBy('name')
                    ->get();
            });

            return view('admin.posts.edit', compact('post', 'categories', 'authors', 'publishers'));

        } catch (\Exception $e) {
            Log::error('Error loading post edit form: ' . $e->getMessage(), [
                'post_id' => $post->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.posts.index')
                ->with('error', 'خطا در بارگذاری فرم ویرایش: ' . $e->getMessage());
        }
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
        // بررسی درخواست تغییر وضعیت انتشار (از صفحه لیست)
        if ($request->has('toggle_publish')) {
            // فقط فیلد مورد نیاز را به‌روزرسانی می‌کنیم - فشار کمتر روی دیتابیس
            $post->is_published = !$post->is_published;
            $post->save();

            $status = $post->is_published ? 'منتشر' : 'به پیش‌نویس منتقل';

            // پاک کردن کش مرتبط
            $this->clearPostCache($post);

            return redirect()->route('admin.posts.index')
                ->with('success', "کتاب «{$post->title}» با موفقیت {$status} شد.");
        }

        // بررسی درخواست تغییر وضعیت نمایش محتوا (از صفحه لیست)
        if ($request->has('toggle_visibility')) {
            // فقط فیلد مورد نیاز را به‌روزرسانی می‌کنیم
            $post->hide_content = !$post->hide_content;
            $post->save();

            $status = $post->hide_content ? 'مخفی' : 'قابل نمایش';

            // پاک کردن کش مرتبط
            $this->clearPostCache($post);

            return redirect()->route('admin.posts.index')
                ->with('success', "محتوای کتاب «{$post->title}» با موفقیت {$status} شد.");
        }

        // اعتبارسنجی داده‌های فرم (فقط فیلدهای مورد نیاز)
        $validated = $request->validate([
            'title' => 'required|max:255',
            'english_title' => 'nullable|max:255',
            'content' => 'required',
            'english_content' => 'nullable',
            'category_id' => 'required|exists:categories,id',
            'author_id' => 'nullable|exists:authors,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'language' => 'nullable|max:50',
            'publication_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'format' => 'nullable|max:50',
            'book_codes' => 'nullable',
            'purchase_link' => 'nullable|url',
            'is_published' => 'boolean',
            'hide_content' => 'boolean',
            'hide_image' => 'nullable|boolean',
        ]);

        // به‌روزرسانی اسلاگ بر اساس عنوان
        $validated['slug'] = Str::slug($validated['title']);

        // پاکسازی محتوا
        $validated['content'] = Purifier::clean($validated['content']);
        if (isset($validated['english_content'])) {
            $validated['english_content'] = Purifier::clean($validated['english_content']);
        }

        try {
            DB::beginTransaction();

            // استفاده از بروزرسانی مستقیم با کوئری برای کارایی بیشتر
            // این کار از فراخوانی هوک‌های مدل جلوگیری می‌کند و سربار کمتری دارد
            DB::table('posts')
                ->where('id', $post->id)
                ->update($validated);

            // به‌روزرسانی وضعیت نمایش تصویر فعلی (اگر وجود دارد)
            if (isset($validated['hide_image'])) {
                $featuredImageId = DB::table('post_images')
                    ->where('post_id', $post->id)
                    ->orderBy('sort_order')
                    ->value('id');

                if ($featuredImageId) {
                    DB::table('post_images')
                        ->where('id', $featuredImageId)
                        ->update([
                            'hide_image' => $validated['hide_image'] ? 'hidden' : 'visible'
                        ]);
                }
            }

            DB::commit();

            // پاک کردن کش‌های مرتبط
            $this->clearPostCache($post);

            return redirect()->route('admin.posts.index')
                ->with('success', 'کتاب با موفقیت بروزرسانی شد.');
        } catch (\Exception $e) {
            DB::rollBack();

            // لاگ خطا
            Log::error('Error updating post: ' . $e->getMessage(), [
                'post_id' => $post->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withInput()
                ->with('error', 'خطا در به‌روزرسانی پست: ' . $e->getMessage());
        }
    }

    /**
     * پاک کردن کش‌های مرتبط با پست - بهینه‌سازی شده
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    private function clearPostCache(Post $post)
    {
        // لیستی از کلیدهای کش که باید پاک شوند
        $cacheKeys = [
            "admin_posts_page_1",
            "post_{$post->id}_featured_image",
            "post_{$post->id}_related_posts_admin",
            "post_{$post->id}_related_posts_user",
        ];

        // کش محتوای پست
        if ($post->content) {
            $cacheKeys[] = "post_{$post->id}_purified_content_" . md5($post->content);
        }

        // کش‌های صفحه خانه
        $cacheKeys[] = 'home_latest_posts';

        // کش‌های مرتبط با دسته‌بندی
        if ($post->category_id) {
            $cacheKeys[] = "category_posts_{$post->category_id}_page_1_admin";
            $cacheKeys[] = "category_posts_{$post->category_id}_page_1_user";
        }

        // کش‌های مرتبط با نویسنده
        if ($post->author_id) {
            $cacheKeys[] = "author_posts_{$post->author_id}_page_1_admin";
            $cacheKeys[] = "author_posts_{$post->author_id}_page_1_user";
        }

        // پاک کردن همه کش‌ها در یک عملیات
        Cache::deleteMultiple($cacheKeys);
    }
}
