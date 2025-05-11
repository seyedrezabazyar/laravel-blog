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
     * نمایش لیست پست‌ها با کوئری ساده
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // غیرفعال کردن لاگ کوئری برای بهبود عملکرد
        DB::connection()->disableQueryLog();

        // کوئری ساده و سبک برای لیست پست‌ها
        $posts = DB::table('posts')
            ->select(['id', 'title', 'is_published', 'hide_content', 'slug', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // بازگرداندن نمای استاندارد
        return view('admin.posts.index', compact('posts'));
    }

    /**
     * نمایش فرم ویرایش پست با کوئری بسیار سبک
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            // غیرفعال کردن لاگ کوئری برای بهبود عملکرد
            DB::connection()->disableQueryLog();

            // 1. اطلاعات اصلی پست با کوئری مستقیم - فقط فیلدهای ضروری
            $post = DB::table('posts')
                ->where('id', $id)
                ->select([
                    'id', 'title', 'slug', 'english_title', 'content', 'english_content',
                    'category_id', 'author_id', 'publisher_id', 'language',
                    'publication_year', 'format', 'book_codes', 'purchase_link',
                    'is_published', 'hide_content'
                ])
                ->first();

            if (!$post) {
                return redirect()->route('admin.posts.index')
                    ->with('error', 'پست مورد نظر یافت نشد.');
            }

            // 2. تصویر شاخص
            $featuredImage = DB::table('post_images')
                ->where('post_id', $id)
                ->select(['id', 'post_id', 'image_path', 'hide_image', 'caption'])
                ->orderBy('sort_order')
                ->first();

            // 3. تگ‌ها با کوئری ساده
            $tags_list = DB::table('post_tag')
                ->join('tags', 'post_tag.tag_id', '=', 'tags.id')
                ->where('post_tag.post_id', $id)
                ->pluck('tags.name')
                ->implode(', ');

            // 4. نویسندگان همکار
            $post_authors = DB::table('post_author')
                ->where('post_id', $id)
                ->pluck('author_id')
                ->toArray();

            // 5. دسته‌بندی‌ها، نویسندگان و ناشران - همه در یک عملیات
            $categories = DB::table('categories')->select(['id', 'name'])->orderBy('name')->get();
            $authors = DB::table('authors')->select(['id', 'name'])->orderBy('name')->get();
            $publishers = DB::table('publishers')->select(['id', 'name'])->orderBy('name')->get();

            // تبدیل به آبجکت برای سازگاری با ویو
            $post = (object)$post;

            // نمایش ویو با داده‌های سبک
            return view('admin.posts.edit', compact(
                'post', 'featuredImage', 'tags_list', 'categories',
                'authors', 'post_authors', 'publishers'
            ));

        } catch (\Exception $e) {
            \Log::error('Error in edit post form: ' . $e->getMessage(), [
                'post_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.posts.index')
                ->with('error', 'خطا در بارگذاری فرم ویرایش: ' . $e->getMessage());
        }
    }

    /**
     * به‌روزرسانی پست در دیتابیس - نسخه بهینه‌سازی شده
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // 1. بررسی درخواست‌های تغییر وضعیت ساده (تغییر وضعیت انتشار یا نمایش)
        if ($request->has('toggle_publish') || $request->has('toggle_visibility')) {
            try {
                $updates = [];
                $statusMessage = '';

                // تغییر وضعیت انتشار
                if ($request->has('toggle_publish')) {
                    $currentValue = DB::table('posts')->where('id', $id)->value('is_published');
                    $newStatus = !$currentValue;
                    $updates['is_published'] = $newStatus;
                    $statusMessage = $newStatus ? 'منتشر' : 'به پیش‌نویس منتقل';
                }

                // تغییر وضعیت نمایش محتوا
                if ($request->has('toggle_visibility')) {
                    $currentValue = DB::table('posts')->where('id', $id)->value('hide_content');
                    $newVisibility = !$currentValue;
                    $updates['hide_content'] = $newVisibility;
                    $statusMessage = $newVisibility ? 'مخفی' : 'قابل نمایش';
                }

                // فقط اگر تغییری هست به‌روزرسانی کن
                if (!empty($updates)) {
                    DB::table('posts')->where('id', $id)->update($updates);

                    // پاک کردن کش‌های کلیدی
                    $this->clearLimitedPostCache($id);
                }

                // بازگشت با پیام موفقیت
                $title = $request->input('title', 'پست');
                return redirect()->route('admin.posts.index')
                    ->with('success', "کتاب «{$title}» با موفقیت {$statusMessage} شد.");

            } catch (\Exception $e) {
                \Log::error('خطا در تغییر وضعیت پست: ' . $e->getMessage(), [
                    'post_id' => $id,
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

            // پاکسازی محتوا برای مقادیر غیر تهی
            if (!empty($validated['content'])) {
                $validated['content'] = Purifier::clean($validated['content']);
            }

            if (!empty($validated['english_content'])) {
                $validated['english_content'] = Purifier::clean($validated['english_content']);
            }

            // شروع تراکنش دیتابیس
            DB::beginTransaction();

            // 3. به‌روزرسانی پست با یک کوئری مستقیم و بهینه
            $postUpdateData = $validated;

            // حذف داده‌های اضافی که نباید مستقیماً در جدول posts ذخیره شوند
            if (isset($postUpdateData['authors'])) unset($postUpdateData['authors']);
            if (isset($postUpdateData['tags'])) unset($postUpdateData['tags']);

            DB::table('posts')
                ->where('id', $id)
                ->update($postUpdateData);

            // 4. به‌روزرسانی وضعیت نمایش تصویر فعلی (اگر درخواست شده باشد)
            if (isset($validated['hide_image'])) {
                $featuredImageId = DB::table('post_images')
                    ->where('post_id', $id)
                    ->orderBy('sort_order')
                    ->value('id');

                if ($featuredImageId) {
                    DB::table('post_images')
                        ->where('id', $featuredImageId)
                        ->update([
                            'hide_image' => $validated['hide_image'] ? 'hidden' : 'visible'
                        ]);

                    // پاک کردن کش مربوط به تصویر
                    Cache::forget("post_image_{$featuredImageId}_url");
                }
            }

            // 5. به‌روزرسانی نویسندگان کتاب
            if (isset($validated['authors'])) {
                // حذف رابطه‌های قبلی
                DB::table('post_author')
                    ->where('post_id', $id)
                    ->delete();

                // افزودن رابطه‌های جدید
                $authors_data = [];
                foreach ($validated['authors'] as $author_id) {
                    $authors_data[] = [
                        'post_id' => $id,
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
                    ->where('post_id', $id)
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
                                'post_id' => $id,
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
            $this->clearPostCache($id);

            // 8. بازگشت پاسخ موفقیت‌آمیز
            return redirect()->route('admin.posts.index')
                ->with('success', 'کتاب با موفقیت بروزرسانی شد.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // خطای اعتبارسنجی - بازگشت به فرم با پیام‌های خطا
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            // برگشت تراکنش در صورت بروز خطا
            DB::rollBack();

            // ثبت خطا در لاگ
            \Log::error('خطا در به‌روزرسانی پست: ' . $e->getMessage(), [
                'post_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withInput()
                ->with('error', 'خطا در به‌روزرسانی پست: ' . $e->getMessage());
        }
    }

    /**
     * پاک کردن کش‌های مرتبط با تغییر وضعیت ساده (منتشر/مخفی) - سبک و سریع
     *
     * @param  int  $id
     * @return void
     */
    private function clearLimitedPostCache($id)
    {
        // فقط کش‌های ضروری را پاک می‌کنیم تا سربار کمتری داشته باشیم
        $cacheKeys = [
            "admin_posts_page_1",
            "post_{$id}_featured_image",
            "post_edit_{$id}_data",
        ];

        // کش‌های صفحه خانه
        $cacheKeys[] = 'home_latest_posts';

        // پاک کردن همه کش‌ها در یک عملیات
        Cache::deleteMultiple($cacheKeys);
    }

    /**
     * پاک کردن کش‌های مرتبط با پست - بهینه‌سازی شده
     *
     * @param  int  $id
     * @return void
     */
    private function clearPostCache($id)
    {
        // ابتدا کش‌های ضروری را پاک می‌کنیم
        $this->clearLimitedPostCache($id);

        // سپس اطلاعات ضروری برای پاک کردن سایر کش‌ها را دریافت می‌کنیم
        $postInfo = DB::table('posts')
            ->where('id', $id)
            ->select(['category_id', 'author_id', 'publisher_id'])
            ->first();

        if ($postInfo) {
            // کش‌های مرتبط با دسته‌بندی
            if ($postInfo->category_id) {
                Cache::forget("category_posts_{$postInfo->category_id}_page_1_admin");
                Cache::forget("category_posts_{$postInfo->category_id}_page_1_user");
            }

            // کش‌های مرتبط با نویسنده
            if ($postInfo->author_id) {
                Cache::forget("author_posts_{$postInfo->author_id}_page_1_admin");
                Cache::forget("author_posts_{$postInfo->author_id}_page_1_user");
            }

            // کش‌های مرتبط با ناشر
            if ($postInfo->publisher_id) {
                Cache::forget("publisher_posts_{$postInfo->publisher_id}_page_1_admin");
                Cache::forget("publisher_posts_{$postInfo->publisher_id}_page_1_user");
            }
        }

        // کش‌های مربوط به روابط پست
        Cache::forget("post_{$id}_related_posts_admin");
        Cache::forget("post_{$id}_related_posts_user");
        Cache::forget("post_{$id}_featured_image_id");
    }
}
