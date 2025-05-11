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

        try {
            // کش کردن داده‌های فرم ویرایش برای بهبود عملکرد
            $cacheKey = "post_edit_{$post->id}_data";

            // بارگذاری داده‌های پست با کوئری خام بهینه‌سازی شده
            $postData = DB::table('posts')
                ->where('id', $post->id)
                ->select([
                    'id', 'title', 'english_title', 'slug', 'content', 'english_content',
                    'category_id', 'author_id', 'publisher_id', 'language',
                    'publication_year', 'format', 'book_codes', 'purchase_link',
                    'is_published', 'hide_content', 'created_at', 'updated_at'
                ])
                ->first();

            // تبدیل به شی
            $post = (object)$postData;

            // لود تصویر شاخص
            $featuredImage = DB::table('post_images')
                ->where('post_id', $post->id)
                ->orderBy('sort_order')
                ->first();

            // دریافت نام نویسنده اصلی (برای نمایش در فیلد جداگانه اگر لازم باشد)
            $author_name = null;
            if (!empty($post->author_id)) {
                $author = DB::table('authors')
                    ->where('id', $post->author_id)
                    ->select('name')
                    ->first();
                if ($author) {
                    $author_name = $author->name;
                }
            }

            // بازیابی تمام نویسندگان برای نمایش در لیست انتخاب
            $authors = DB::table('authors')
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();

            // بازیابی نویسندگان همکار فعلی این کتاب
            $post_authors = DB::table('post_author')
                ->where('post_id', $post->id)
                ->pluck('author_id')
                ->toArray();

            // بازیابی لیست ناشران
            $publishers = DB::table('publishers')
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();

            // دریافت نام ناشر (برای نمایش در فیلد جداگانه اگر لازم باشد)
            $publisher_name = null;
            if (!empty($post->publisher_id)) {
                $publisher = DB::table('publishers')
                    ->where('id', $post->publisher_id)
                    ->select('name')
                    ->first();
                if ($publisher) {
                    $publisher_name = $publisher->name;
                }
            }

            // دریافت تگ‌های پست و تبدیل آن‌ها به رشته‌ای با کاما
            $tags_list = "";
            $tags = DB::table('post_tag')
                ->where('post_id', $post->id)
                ->join('tags', 'post_tag.tag_id', '=', 'tags.id')
                ->select('tags.name')
                ->get()
                ->pluck('name')
                ->toArray();

            if (!empty($tags)) {
                $tags_list = implode(', ', $tags);
            }

            // فقط دسته‌بندی‌ها را بارگیری می‌کنیم
            $categories = DB::table('categories')
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();

            return view('admin.posts.edit', compact(
                'post', 'categories', 'featuredImage', 'author_name', 'publisher_name',
                'tags_list', 'authors', 'post_authors', 'publishers'
            ));

        } catch (\Exception $e) {
            \Log::error('Error in edit post form: ' . $e->getMessage());
            return redirect()->route('admin.posts.index')
                ->with('error', 'خطا در بارگذاری فرم ویرایش: ' . $e->getMessage());
        }
    }

    /**
     * به‌روزرسانی پست در دیتابیس - نسخه بهینه‌سازی شده
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Post $post)
    {
        // 1. بررسی درخواست‌های تغییر وضعیت ساده (تغییر وضعیت انتشار یا نمایش)
        if ($request->has('toggle_publish') || $request->has('toggle_visibility')) {
            try {
                $updates = [];
                $statusMessage = '';

                // الف. تغییر وضعیت انتشار (منتشر شده یا پیش‌نویس)
                if ($request->has('toggle_publish')) {
                    $newStatus = !$post->is_published;
                    $updates['is_published'] = $newStatus;
                    $statusMessage = $newStatus ? 'منتشر' : 'به پیش‌نویس منتقل';
                }

                // ب. تغییر وضعیت نمایش محتوا (نمایش یا مخفی)
                if ($request->has('toggle_visibility')) {
                    $newVisibility = !$post->hide_content;
                    $updates['hide_content'] = $newVisibility;
                    $statusMessage = $newVisibility ? 'مخفی' : 'قابل نمایش';
                }

                // به‌روزرسانی فقط فیلدهای مورد نیاز با کوئری مستقیم (بدون لود کامل مدل)
                DB::table('posts')
                    ->where('id', $post->id)
                    ->update($updates);

                // پاک کردن محدود کش‌های مرتبط
                $this->clearLimitedPostCache($post);

                return redirect()->route('admin.posts.index')
                    ->with('success', "کتاب «{$post->title}» با موفقیت {$statusMessage} شد.");

            } catch (\Exception $e) {
                \Log::error('خطا در تغییر وضعیت پست: ' . $e->getMessage(), [
                    'post_id' => $post->id,
                    'trace' => $e->getTraceAsString()
                ]);

                return redirect()->back()
                    ->with('error', 'خطا در به‌روزرسانی وضعیت پست: ' . $e->getMessage());
            }
        }

        // 2. به‌روزرسانی کامل پست
        try {
            // اعتبارسنجی داده‌های ورودی - فقط فیلدهای مورد نیاز
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
                'authors' => 'nullable|array',
                'authors.*' => 'exists:authors,id',
                'tags' => 'nullable|string|max:500',
            ]);

            // به‌روزرسانی اسلاگ بر اساس عنوان
            $validated['slug'] = Str::slug($validated['title']);

            // پاکسازی محتوا - فقط برای فیلدهای محتوا
            if (isset($validated['content'])) {
                // کش کردن نتایج پاکسازی برای جلوگیری از پردازش مجدد در آینده
                $contentHash = md5($validated['content']);
                $cacheKey = "purified_content_{$contentHash}";

                $validated['content'] = Cache::remember($cacheKey, 86400, function () use ($validated) {
                    return Purifier::clean($validated['content']);
                });
            }

            if (isset($validated['english_content'])) {
                $englishContentHash = md5($validated['english_content']);
                $cacheKey = "purified_english_content_{$englishContentHash}";

                $validated['english_content'] = Cache::remember($cacheKey, 86400, function () use ($validated) {
                    return Purifier::clean($validated['english_content']);
                });
            }

            // شروع تراکنش دیتابیس
            DB::beginTransaction();

            // 3. به‌روزرسانی پست با یک کوئری مستقیم و بهینه
            $postUpdateData = $validated;

            // حذف داده‌های اضافی که نباید مستقیماً در جدول posts ذخیره شوند
            if (isset($postUpdateData['authors'])) unset($postUpdateData['authors']);
            if (isset($postUpdateData['tags'])) unset($postUpdateData['tags']);

            DB::table('posts')
                ->where('id', $post->id)
                ->update($postUpdateData);

            // 4. به‌روزرسانی وضعیت نمایش تصویر فعلی (اگر درخواست شده باشد)
            if (isset($validated['hide_image'])) {
                // استفاده از کش برای دریافت ID تصویر اصلی
                $featuredImageId = Cache::remember("post_{$post->id}_featured_image_id", 3600, function() use ($post) {
                    return DB::table('post_images')
                        ->where('post_id', $post->id)
                        ->orderBy('sort_order')
                        ->value('id');
                });

                if ($featuredImageId) {
                    DB::table('post_images')
                        ->where('id', $featuredImageId)
                        ->update([
                            'hide_image' => $validated['hide_image'] ? 'hidden' : 'visible'
                        ]);

                    // پاک کردن کش مربوط به تصویر
                    Cache::forget("post_image_{$featuredImageId}_url");
                    Cache::forget("post_image_{$featuredImageId}_display_url_admin");
                    Cache::forget("post_image_{$featuredImageId}_display_url_user");
                }
            }

            // 5. به‌روزرسانی نویسندگان کتاب
            if (isset($validated['authors'])) {
                // حذف رابطه‌های قبلی
                DB::table('post_author')
                    ->where('post_id', $post->id)
                    ->delete();

                // افزودن رابطه‌های جدید
                $authors_data = [];
                foreach ($validated['authors'] as $author_id) {
                    $authors_data[] = [
                        'post_id' => $post->id,
                        'author_id' => $author_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($authors_data)) {
                    DB::table('post_author')->insert($authors_data);
                }
            }

            // 6. به‌روزرسانی تگ‌ها
            if (isset($validated['tags'])) {
                // حذف روابط قبلی
                DB::table('post_tag')
                    ->where('post_id', $post->id)
                    ->delete();

                // اضافه کردن تگ‌های جدید
                if (!empty($validated['tags'])) {
                    $tags = explode(',', $validated['tags']);
                    foreach ($tags as $tag_name) {
                        $tag_name = trim($tag_name);
                        if (!empty($tag_name)) {
                            // بررسی وجود تگ یا ایجاد آن
                            $tag_id = DB::table('tags')
                                ->where('name', $tag_name)
                                ->value('id');

                            if (!$tag_id) {
                                $tag_id = DB::table('tags')->insertGetId([
                                    'name' => $tag_name,
                                    'slug' => Str::slug($tag_name),
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }

                            // ایجاد رابطه بین پست و تگ
                            DB::table('post_tag')->insert([
                                'post_id' => $post->id,
                                'tag_id' => $tag_id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }

            // تأیید تراکنش
            DB::commit();

            // 7. پاک کردن کش‌های مرتبط - از متد جداگانه استفاده می‌کنیم
            $this->clearPostCache($post);

            // 8. بازگشت پاسخ موفقیت‌آمیز
            return redirect()->route('admin.posts.index')
                ->with('success', 'کتاب با موفقیت بروزرسانی شد.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // خطای اعتبارسنجی - بازگشت به فرم با پیام‌های خطا
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            // برگشت تراکنش در صورت بروز خطا
            DB::rollBack();

            // ثبت خطا در لاگ
            \Log::error('خطا در به‌روزرسانی پست: ' . $e->getMessage(), [
                'post_id' => $post->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withInput()
                ->with('error', 'خطا در به‌روزرسانی پست: ' . $e->getMessage());
        }
    }

    /**
     * پاک کردن کش‌های مرتبط با تغییر وضعیت ساده (منتشر/مخفی) - سبک و سریع
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    private function clearLimitedPostCache(Post $post)
    {
        // فقط کش‌های ضروری را پاک می‌کنیم تا سربار کمتری داشته باشیم
        $cacheKeys = [
            "admin_posts_page_1",
            "post_{$post->id}_featured_image",
            "post_edit_{$post->id}_data",
        ];

        // کش‌های صفحه خانه
        $cacheKeys[] = 'home_latest_posts';

        // پاک کردن همه کش‌ها در یک عملیات
        Cache::deleteMultiple($cacheKeys);
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
            "post_edit_{$post->id}_data",
            "post_{$post->id}_featured_image_id",
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

        // کش‌های مرتبط با ناشر
        if ($post->publisher_id) {
            $cacheKeys[] = "publisher_posts_{$post->publisher_id}_page_1_admin";
            $cacheKeys[] = "publisher_posts_{$post->publisher_id}_page_1_user";
        }

        // پاک کردن همه کش‌ها در یک عملیات
        Cache::deleteMultiple($cacheKeys);
    }
}
