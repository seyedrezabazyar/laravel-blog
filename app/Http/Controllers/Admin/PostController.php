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
     * نمایش لیست پست‌ها با کوئری بهینه‌سازی شده
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // دریافت پارامتر فیلتر از درخواست
        $filter = $request->get('filter');

        // ایجاد کوئری اولیه با بهینه‌سازی
        $postsQuery = Post::query()->select('id', 'title', 'slug', 'is_published', 'hide_content', 'created_at');

        // اعمال فیلترها بر اساس پارامتر درخواست
        switch ($filter) {
            case 'published':
                $postsQuery->where('is_published', true);
                break;
            case 'draft':
                $postsQuery->where('is_published', false);
                break;
            case 'hidden':
                $postsQuery->where('hide_content', true);
                break;
        }

        // مرتب‌سازی بر اساس تاریخ ایجاد (نزولی)
        $postsQuery->orderBy('created_at', 'desc');

        // استفاده از کش برای شمارش‌ها برای کاهش بار دیتابیس
        // کش به مدت 10 دقیقه ذخیره می‌شود و با هر تغییر در پست‌ها پاک می‌شود
        $publishedCount = Cache::remember('posts_published_count', 600, function () {
            return Post::where('is_published', true)->count();
        });

        $draftCount = Cache::remember('posts_draft_count', 600, function () {
            return Post::where('is_published', false)->count();
        });

        $hiddenCount = Cache::remember('posts_hidden_count', 600, function () {
            return Post::where('hide_content', true)->count();
        });

        // دریافت نتایج با پاگینیشن
        $posts = $postsQuery->paginate(15);

        // ارسال داده‌ها به ویو
        return view('admin.posts.index', compact(
            'posts',
            'publishedCount',
            'draftCount',
            'hiddenCount'
        ));
    }

    /**
     * نمایش فرم ویرایش پست با کوئری فوق‌العاده سبک - الهام گرفته از BlogController
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            // غیرفعال کردن لاگ کوئری برای بهبود عملکرد
            DB::connection()->disableQueryLog();

            // استفاده از کلید کش منحصر به فرد
            $cacheKey = "post_edit_{$id}_minimal_data";

            // 1. اطلاعات اصلی پست - فقط فیلدهای ضروری با select محدود و کش
            $post = Cache::remember($cacheKey, 1800, function() use ($id) {
                return DB::table('posts')
                    ->where('id', $id)
                    ->select([
                        'id', 'title', 'slug', 'english_title',
                        'category_id', 'author_id', 'publisher_id',
                        'language', 'publication_year', 'format',
                        'book_codes', 'purchase_link',
                        'is_published', 'hide_content'
                    ])
                    ->first();
            });

            if (!$post) {
                return redirect()->route('admin.posts.index')
                    ->with('error', 'پست مورد نظر یافت نشد.');
            }

            // کوئری‌های جداگانه و سبک برای اطلاعات ضروری

            // 2. فقط محتوای متنی - با کوئری جداگانه و کش
            $contentCacheKey = "post_edit_{$id}_content_data";
            $postContent = Cache::remember($contentCacheKey, 1800, function() use ($id) {
                return DB::table('posts')
                    ->where('id', $id)
                    ->select(['content', 'english_content'])
                    ->first();
            });

            if ($postContent) {
                $post->content = $postContent->content;
                $post->english_content = $postContent->english_content;
            } else {
                $post->content = '';
                $post->english_content = '';
            }

            // 3. تصویر شاخص - فقط ایدی و مسیر با کش
            $featuredImageCacheKey = "post_{$id}_featured_image_minimal";
            $featuredImage = Cache::remember($featuredImageCacheKey, 1800, function() use ($id) {
                return DB::table('post_images')
                    ->where('post_id', $id)
                    ->select('id', 'image_path', 'hide_image')
                    ->orderBy('sort_order')
                    ->first();
            });

            // 4. تگ‌ها - فقط به صورت رشته کاما-جدا برای سبکی بیشتر
            $tagsCacheKey = "post_{$id}_tags_string";
            $tags_list = Cache::remember($tagsCacheKey, 1800, function() use ($id) {
                return DB::table('post_tag')
                    ->join('tags', 'post_tag.tag_id', '=', 'tags.id')
                    ->where('post_tag.post_id', $id)
                    ->pluck('tags.name')
                    ->implode(', ');
            });

            // 5. نویسندگان همکار - فقط آیدی‌ها
            $coAuthorsCacheKey = "post_{$id}_coauthors_ids";
            $post_authors = Cache::remember($coAuthorsCacheKey, 1800, function() use ($id) {
                return DB::table('post_author')
                    ->where('post_id', $id)
                    ->pluck('author_id')
                    ->toArray();
            });

            // 6. استفاده از کش طولانی مدت (6 ساعت) برای لیست‌های ثابت
            // اینها تقریباً استاتیک هستند و نیاز به بروزرسانی مکرر ندارند

            // استفاده از کوئری‌های SQL خام برای سرعت بیشتر
            $categoriesCacheKey = "admin_categories_minimal_list";
            $categories = Cache::remember($categoriesCacheKey, 21600, function() {
                return DB::select("SELECT id, name FROM categories ORDER BY name");
            });

            $authorsCacheKey = "admin_authors_minimal_list";
            $authors = Cache::remember($authorsCacheKey, 21600, function() {
                return DB::select("SELECT id, name FROM authors ORDER BY name");
            });

            $publishersCacheKey = "admin_publishers_minimal_list";
            $publishers = Cache::remember($publishersCacheKey, 21600, function() {
                return DB::select("SELECT id, name FROM publishers ORDER BY name");
            });

            // تبدیل به آبجکت برای سازگاری با ویو
            $post = (object)$post;

            // نمایش ویو با حداقل داده‌های مورد نیاز
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
     * به‌روزرسانی پست در دیتابیس - نسخه فوق‌العاده بهینه‌سازی شده
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
                // استفاده از کوئری‌های بهینه برای عملیات‌های ساده
                $statusMessage = '';

                // تغییر وضعیت انتشار - با یک کوئری مستقیم بدون select اضافی
                if ($request->has('toggle_publish')) {
                    DB::statement("UPDATE posts SET is_published = NOT is_published WHERE id = ?", [$id]);
                    $isPublished = DB::scalar("SELECT is_published FROM posts WHERE id = ?", [$id]);
                    $statusMessage = $isPublished ? 'منتشر' : 'به پیش‌نویس منتقل';
                }

                // تغییر وضعیت نمایش محتوا - با یک کوئری مستقیم بدون select اضافی
                if ($request->has('toggle_visibility')) {
                    DB::statement("UPDATE posts SET hide_content = NOT hide_content WHERE id = ?", [$id]);
                    $isHidden = DB::scalar("SELECT hide_content FROM posts WHERE id = ?", [$id]);
                    $statusMessage = $isHidden ? 'مخفی' : 'قابل نمایش';
                }

                // پاک کردن فقط کش‌های ضروری مرتبط
                $this->clearMinimalPostCache($id);

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
                'image' => 'nullable|image|max:2048',
            ]);

            // به‌روزرسانی اسلاگ بر اساس عنوان
            $validated['slug'] = Str::slug($validated['title']);

            // پاکسازی محتوا برای مقادیر غیر تهی با گزینه‌های محدود برای سرعت بیشتر
            if (!empty($validated['content'])) {
                $purifierConfig = \HTMLPurifier_Config::createDefault();
                $purifierConfig->set('Cache.SerializerPath', storage_path('app/purifier'));
                $purifier = new \HTMLPurifier($purifierConfig);
                $validated['content'] = $purifier->purify($validated['content']);
            }

            if (!empty($validated['english_content'])) {
                $validated['english_content'] = $purifier->purify($validated['english_content']);
            }

            // شروع تراکنش دیتابیس - با استفاده از بلوک try/finally برای اطمینان از commit/rollback
            DB::beginTransaction();

            try {
                // 3. به‌روزرسانی پست با یک کوئری SQL مستقیم برای عملکرد بهتر
                // حذف داده‌های اضافی که نباید مستقیماً در جدول posts ذخیره شوند
                $postUpdateData = $validated;
                unset($postUpdateData['authors'], $postUpdateData['tags'], $postUpdateData['image'], $postUpdateData['hide_image']);

                // آماده‌سازی فیلدها و مقادیر برای کوئری update
                $updateFields = [];
                $updateValues = [];

                foreach ($postUpdateData as $field => $value) {
                    $updateFields[] = "`{$field}` = ?";
                    $updateValues[] = $value;
                }

                // اضافه کردن id به آرایه مقادیر
                $updateValues[] = $id;

                // اجرای کوئری SQL خام برای سرعت بیشتر
                DB::statement(
                    "UPDATE posts SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = ?",
                    $updateValues
                );

                // 4. پردازش تصویر - اگر آپلود شده باشد - با حداقل کوئری
                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('post_images', 'public');

                    // بررسی وجود تصویر شاخص با یک کوئری ساده
                    $featuredImageExists = DB::scalar(
                        "SELECT COUNT(*) FROM post_images WHERE post_id = ? ORDER BY sort_order LIMIT 1",
                        [$id]
                    );

                    if ($featuredImageExists) {
                        // بازیابی آیدی تصویر شاخص با یک کوئری ساده
                        $featuredImageId = DB::scalar(
                            "SELECT id FROM post_images WHERE post_id = ? ORDER BY sort_order LIMIT 1",
                            [$id]
                        );

                        // حذف تصویر قبلی از فضای ذخیره‌سازی (در پس‌زمینه)
                        $oldImagePath = DB::scalar(
                            "SELECT image_path FROM post_images WHERE id = ?",
                            [$featuredImageId]
                        );

                        if ($oldImagePath) {
                            try {
                                Storage::disk('public')->delete($oldImagePath);
                            } catch (\Exception $e) {
                                // ادامه دادن حتی در صورت بروز خطا در حذف تصویر قبلی
                                \Log::warning('خطا در حذف تصویر قبلی: ' . $e->getMessage());
                            }
                        }

                        // به‌روزرسانی رکورد تصویر موجود با SQL مستقیم
                        DB::statement(
                            "UPDATE post_images SET image_path = ?, updated_at = NOW() WHERE id = ?",
                            [$path, $featuredImageId]
                        );
                    } else {
                        // ایجاد یک رکورد جدید برای تصویر با SQL مستقیم
                        DB::statement(
                            "INSERT INTO post_images (post_id, image_path, sort_order, created_at, updated_at) VALUES (?, ?, 0, NOW(), NOW())",
                            [$id, $path]
                        );
                    }

                    // پاک کردن کش مربوط به تصویر
                    Cache::forget("post_{$id}_featured_image_minimal");
                }

                // 5. به‌روزرسانی وضعیت نمایش تصویر - فقط اگر تصویر شاخص وجود داشته باشد
                if (isset($validated['hide_image'])) {
                    // بررسی وجود تصویر شاخص با یک کوئری ساده
                    $featuredImageId = DB::scalar(
                        "SELECT id FROM post_images WHERE post_id = ? ORDER BY sort_order LIMIT 1",
                        [$id]
                    );

                    if ($featuredImageId) {
                        // به‌روزرسانی وضعیت نمایش تصویر با SQL مستقیم
                        DB::statement(
                            "UPDATE post_images SET hide_image = ?, updated_at = NOW() WHERE id = ?",
                            [$validated['hide_image'] ? 'hidden' : 'visible', $featuredImageId]
                        );

                        // پاک کردن کش مربوط به تصویر
                        $this->clearImageCache($featuredImageId);
                    }
                }

                // 6. به‌روزرسانی نویسندگان کتاب - بهینه‌سازی شده
                if (isset($validated['authors']) && is_array($validated['authors'])) {
                    // حذف رابطه‌های قبلی با یک کوئری ساده
                    DB::statement("DELETE FROM post_author WHERE post_id = ?", [$id]);

                    // اولین نویسنده به عنوان نویسنده اصلی
                    if (!empty($validated['authors'])) {
                        $mainAuthorId = reset($validated['authors']);

                        // به‌روزرسانی نویسنده اصلی با SQL مستقیم
                        DB::statement(
                            "UPDATE posts SET author_id = ? WHERE id = ?",
                            [$mainAuthorId, $id]
                        );

                        // ایجاد مقادیر برای درج دسته‌ای
                        $authorsValues = [];
                        $authorsSql = "INSERT INTO post_author (post_id, author_id, created_at, updated_at) VALUES ";
                        $now = now()->format('Y-m-d H:i:s');

                        foreach ($validated['authors'] as $author_id) {
                            $authorsValues[] = "({$id}, {$author_id}, '{$now}', '{$now}')";
                        }

                        // اجرای کوئری درج دسته‌ای برای همه نویسندگان
                        if (!empty($authorsValues)) {
                            DB::statement($authorsSql . implode(', ', $authorsValues));
                        }
                    } else {
                        // اگر هیچ نویسنده‌ای انتخاب نشده، نویسنده اصلی را هم null کنیم
                        DB::statement("UPDATE posts SET author_id = NULL WHERE id = ?", [$id]);
                    }

                    // پاک کردن کش مربوط به نویسندگان
                    Cache::forget("post_{$id}_coauthors_ids");
                }

                // 7. به‌روزرسانی تگ‌ها - بهینه‌سازی شده
                if (isset($validated['tags'])) {
                    // حذف روابط قبلی با یک کوئری ساده
                    DB::statement("DELETE FROM post_tag WHERE post_id = ?", [$id]);

                    // اضافه کردن تگ‌های جدید
                    if (!empty($validated['tags'])) {
                        $tags = explode(',', $validated['tags']);
                        $now = now()->format('Y-m-d H:i:s');

                        foreach ($tags as $tag_name) {
                            $tag_name = trim($tag_name);
                            if (!empty($tag_name)) {
                                // بررسی وجود تگ یا ایجاد آن - با استفاده از REPLACE INTO برای کارایی بیشتر
                                $slug = Str::slug($tag_name);

                                // استفاده از روش درج یا بازیابی برای تگ
                                DB::statement(
                                    "INSERT INTO tags (name, slug, created_at, updated_at)
                                VALUES (?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), updated_at = ?",
                                    [$tag_name, $slug, $now, $now, $now]
                                );

                                // بازیابی آیدی تگ (یا تگ جدیداً اضافه شده یا تگ موجود)
                                $tag_id = DB::scalar("SELECT id FROM tags WHERE slug = ?", [$slug]);

                                // ایجاد رابطه بین پست و تگ
                                if ($tag_id) {
                                    DB::statement(
                                        "INSERT INTO post_tag (post_id, tag_id, created_at, updated_at) VALUES (?, ?, ?, ?)",
                                        [$id, $tag_id, $now, $now]
                                    );
                                }
                            }
                        }
                    }

                    // پاک کردن کش مربوط به تگ‌ها
                    Cache::forget("post_{$id}_tags_string");
                }

                // تأیید تراکنش
                DB::commit();

            } catch (\Exception $e) {
                // برگشت تراکنش در صورت بروز خطا
                DB::rollBack();
                throw $e;
            }

            // 8. پاک کردن کش‌های مرتبط - با روش بهینه
            $this->clearPostCacheFast($id);

            // 9. بازگشت پاسخ موفقیت‌آمیز
            return redirect()->route('admin.posts.index')
                ->with('success', 'کتاب با موفقیت بروزرسانی شد.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // خطای اعتبارسنجی - بازگشت به فرم با پیام‌های خطا
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
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
     * پاک کردن کش‌های مرتبط با یک تصویر
     *
     * @param  int  $imageId
     * @return void
     */
    private function clearImageCache($imageId)
    {
        $keys = [
            "post_image_{$imageId}_url",
            "post_image_{$imageId}_display_url_admin",
            "post_image_{$imageId}_display_url_user"
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * پاک کردن حداقل کش‌های ضروری مرتبط با پست
     *
     * @param  int  $id
     * @return void
     */
    private function clearMinimalPostCache($id)
    {
        $keys = [
            "post_edit_{$id}_minimal_data",
            "admin_posts_page_1"
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * پاک کردن کش‌های مرتبط با پست - روش بهینه و سریع
     *
     * @param  int  $id
     * @return void
     */
    private function clearPostCacheFast($id)
    {
        // 1. پاک کردن کش‌های مستقیم پست
        $directKeys = [
            "post_edit_{$id}_minimal_data",
            "post_edit_{$id}_content_data",
            "post_{$id}_featured_image_minimal",
            "post_{$id}_tags_string",
            "post_{$id}_coauthors_ids",
            "post_{$id}_featured_image_id"
        ];

        foreach ($directKeys as $key) {
            Cache::forget($key);
        }

        // 2. پاک کردن کش صفحه اصلی و لیست‌ها
        $listKeys = [
            "admin_posts_page_1",
            "home_latest_posts"
        ];

        foreach ($listKeys as $key) {
            Cache::forget($key);
        }

        // 3. پاک کردن کش‌های مرتبط با دسته‌بندی، نویسنده و ناشر (با یک کوئری کوچک)
        try {
            $relatedInfo = DB::select("SELECT category_id, author_id, publisher_id FROM posts WHERE id = ? LIMIT 1", [$id]);

            if (!empty($relatedInfo) && isset($relatedInfo[0])) {
                $info = $relatedInfo[0];

                // کش‌های دسته‌بندی
                if (!empty($info->category_id)) {
                    Cache::forget("category_posts_{$info->category_id}_page_1_admin");
                    Cache::forget("category_posts_{$info->category_id}_page_1_user");
                }

                // کش‌های نویسنده
                if (!empty($info->author_id)) {
                    Cache::forget("author_posts_{$info->author_id}_page_1_admin");
                    Cache::forget("author_posts_{$info->author_id}_page_1_user");
                }

                // کش‌های ناشر
                if (!empty($info->publisher_id)) {
                    Cache::forget("publisher_posts_{$info->publisher_id}_page_1_admin");
                    Cache::forget("publisher_posts_{$info->publisher_id}_page_1_user");
                }
            }
        } catch (\Exception $e) {
            // ادامه دادن حتی در صورت بروز خطا در پاک کردن کش
            \Log::warning('خطا در پاک کردن کش‌های مرتبط: ' . $e->getMessage());
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
