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

        // فقط روابط مورد نیاز را بارگذاری می‌کنیم
        $post->load([
            'featuredImage' => function($query) {
                $query->select(['id', 'post_id', 'image_path', 'hide_image']);
            }
        ]);

        // کش کردن داده‌های ثابت برای کاهش کوئری‌های تکراری
        $categories = Cache::remember('admin_categories', 3600, function() {
            return Category::select(['id', 'name'])->orderBy('name')->get();
        });

        $authors = Cache::remember('admin_authors', 3600, function() {
            return Author::select(['id', 'name'])->orderBy('name')->get();
        });

        $publishers = Cache::remember('admin_publishers', 3600, function() {
            return Publisher::select(['id', 'name'])->orderBy('name')->get();
        });

        return view('admin.posts.edit', compact('post', 'categories', 'authors', 'publishers'));
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

            // به‌روزرسانی پست
            $post->update($validated);

            // به‌روزرسانی وضعیت نمایش تصویر فعلی (اگر وجود دارد)
            if ($post->featuredImage && isset($validated['hide_image'])) {
                $post->featuredImage->update([
                    'hide_image' => $validated['hide_image'] ? 'hidden' : 'visible'
                ]);
            }

            DB::commit();

            // پاک کردن کش‌های مرتبط
            $this->clearPostCache($post);

            return redirect()->route('admin.posts.index')
                ->with('success', 'کتاب با موفقیت بروزرسانی شد.');
        } catch (\Exception $e) {
            DB::rollBack();

            // لاگ خطا
            Log::error('Error updating post: ' . $e->getMessage(), ['post_id' => $post->id]);

            return redirect()->back()->withInput()
                ->with('error', 'خطا در به‌روزرسانی پست: ' . $e->getMessage());
        }
    }

    /**
     * پاک کردن کش‌های مرتبط با پست
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    private function clearPostCache(Post $post)
    {
        // پاک کردن کش صفحه اصلی پست‌ها
        Cache::forget("admin_posts_page_1");

        // پاک کردن کش‌های مرتبط با صفحات بلاگ
        Cache::forget("post_{$post->id}_related_posts_admin");
        Cache::forget("post_{$post->id}_related_posts_user");

        if ($post->content) {
            Cache::forget("post_{$post->id}_purified_content_" . md5($post->content));
        }

        // پاک کردن کش‌های مرتبط با صفحه خانه بلاگ
        Cache::forget('home_latest_posts');

        // پاک کردن سایر کش‌های مرتبط
        if ($post->category) {
            Cache::forget("category_posts_{$post->category->id}_page_1_admin");
            Cache::forget("category_posts_{$post->category->id}_page_1_user");
        }

        if ($post->author) {
            Cache::forget("author_posts_{$post->author->id}_page_1_admin");
            Cache::forget("author_posts_{$post->author->id}_page_1_user");
        }
    }
}
