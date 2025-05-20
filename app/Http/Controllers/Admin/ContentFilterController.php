<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ContentFilterController extends Controller
{
    /**
     * نمایش صفحه فیلتر محتوا
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // دریافت آخرین کلمات فیلتر شده از سشن
        $lastFilteredWords = session('last_filtered_words', '');
        $lastFilterCount = session('last_filter_count', 0);
        $lastHiddenCount = session('last_hidden_count', 0);

        // دریافت کلمات فیلتر شده قبلی از تنظیمات (اگر وجود داشته باشد)
        $previousFilters = $this->getPreviousFilters();

        return view('admin.content-filter.index', compact(
            'lastFilteredWords',
            'lastFilterCount',
            'lastHiddenCount',
            'previousFilters'
        ));
    }

    /**
     * اجرای عملیات فیلتر محتوا
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function filter(Request $request)
    {
        // اعتبارسنجی درخواست
        $validated = $request->validate([
            'filter_words' => 'required|string|min:2',
            'hide_content' => 'boolean'
        ]);

        // پردازش کلمات کلیدی برای جستجو
        $words = array_filter(explode(',', $validated['filter_words']), function ($word) {
            return trim($word) !== '';
        });

        // بررسی اگر کلمات خالی است
        if (empty($words)) {
            return redirect()->route('admin.content-filter.index')
                ->with('error', 'هیچ کلمه کلیدی معتبری وارد نشده است.');
        }

        try {
            // لیست پست‌های مطابق با کلمات کلیدی
            $matchedPosts = $this->findPostsWithKeywords($words);
            $matchCount = count($matchedPosts);

            // تعداد پست‌هایی که مخفی شده‌اند
            $hiddenCount = 0;

            // اگر کاربر خواسته باشد پست‌ها مخفی شوند
            if ($request->input('hide_content', false)) {
                $hiddenCount = $this->hideMatchedPosts($matchedPosts);
            }

            // ذخیره کلمات فیلتر شده در تنظیمات
            $this->savePreviousFilter($validated['filter_words']);

            // ذخیره اطلاعات فیلتر در سشن برای نمایش در صفحه
            session([
                'last_filtered_words' => $validated['filter_words'],
                'last_filter_count' => $matchCount,
                'last_hidden_count' => $hiddenCount,
            ]);

            $message = "جستجو با موفقیت انجام شد. {$matchCount} پست حاوی کلمات کلیدی پیدا شد";

            if ($hiddenCount > 0) {
                $message .= " و {$hiddenCount} پست مخفی شد.";
            } else {
                $message .= ".";
            }

            return redirect()->route('admin.content-filter.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('خطا در فیلتر محتوا: ' . $e->getMessage(), [
                'filter_words' => $validated['filter_words'],
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.content-filter.index')
                ->with('error', 'خطا در اجرای فیلتر: ' . $e->getMessage());
        }
    }

    /**
     * نمایش نتایج جستجو بدون مخفی کردن
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        // اعتبارسنجی درخواست
        $validated = $request->validate([
            'filter_words' => 'required|string|min:2',
        ]);

        // پردازش کلمات کلیدی برای جستجو
        $words = array_filter(explode(',', $validated['filter_words']), function ($word) {
            return trim($word) !== '';
        });

        // بررسی اگر کلمات خالی است
        if (empty($words)) {
            return redirect()->route('admin.content-filter.index')
                ->with('error', 'هیچ کلمه کلیدی معتبری وارد نشده است.');
        }

        try {
            // لیست پست‌های مطابق با کلمات کلیدی
            $matchedPosts = $this->findPostsWithKeywords($words);

            // دریافت اطلاعات کامل پست‌ها برای نمایش
            $posts = Post::whereIn('id', $matchedPosts)
                ->select('id', 'title', 'slug', 'is_published', 'hide_content', 'created_at')
                ->orderBy('id', 'desc')
                ->paginate(15);

            return view('admin.content-filter.results', [
                'posts' => $posts,
                'filter_words' => $validated['filter_words']
            ]);

        } catch (\Exception $e) {
            Log::error('خطا در جستجوی محتوا: ' . $e->getMessage(), [
                'filter_words' => $validated['filter_words'],
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.content-filter.index')
                ->with('error', 'خطا در جستجو: ' . $e->getMessage());
        }
    }

    /**
     * مخفی کردن یک پست خاص
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function hidePost($id)
    {
        try {
            $post = Post::findOrFail($id);
            $post->hide_content = true;
            $post->save();

            return redirect()->back()
                ->with('success', "پست «{$post->title}» با موفقیت مخفی شد.");

        } catch (\Exception $e) {
            Log::error('خطا در مخفی کردن پست: ' . $e->getMessage(), [
                'post_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'خطا در مخفی کردن پست: ' . $e->getMessage());
        }
    }

    /**
     * نمایش یک پست خاص (از حالت مخفی خارج کردن)
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function showPost($id)
    {
        try {
            $post = Post::findOrFail($id);
            $post->hide_content = false;
            $post->save();

            return redirect()->back()
                ->with('success', "پست «{$post->title}» با موفقیت نمایش داده شد.");

        } catch (\Exception $e) {
            Log::error('خطا در نمایش پست: ' . $e->getMessage(), [
                'post_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'خطا در نمایش پست: ' . $e->getMessage());
        }
    }

    /**
     * مخفی کردن چندین پست به صورت همزمان
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkHide(Request $request)
    {
        $postIds = $request->input('post_ids', []);

        if (empty($postIds)) {
            return redirect()->back()
                ->with('error', 'هیچ پستی انتخاب نشده است.');
        }

        try {
            // مخفی کردن همه پست‌های انتخاب شده
            $affected = Post::whereIn('id', $postIds)
                ->update(['hide_content' => true]);

            return redirect()->back()
                ->with('success', "{$affected} پست با موفقیت مخفی شد.");

        } catch (\Exception $e) {
            Log::error('خطا در مخفی کردن دسته‌ای پست‌ها: ' . $e->getMessage(), [
                'post_ids' => $postIds,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'خطا در مخفی کردن پست‌ها: ' . $e->getMessage());
        }
    }

    /**
     * پیدا کردن پست‌هایی که شامل کلمات کلیدی هستند
     *
     * @param  array  $words
     * @return array
     */
    private function findPostsWithKeywords(array $words)
    {
        $query = Post::select('id');

        // ساخت کوئری برای جستجوی کلمات در عنوان و محتوا
        $query->where(function ($q) use ($words) {
            foreach ($words as $word) {
                $word = trim($word);
                if (!empty($word)) {
                    $q->orWhere('title', 'like', '%' . $word . '%')
                        ->orWhere('content', 'like', '%' . $word . '%');

                    // اگر english_title و english_content دارید، این‌ها را هم جستجو کنید
                    if (Schema::hasColumn('posts', 'english_title')) {
                        $q->orWhere('english_title', 'like', '%' . $word . '%');
                    }

                    if (Schema::hasColumn('posts', 'english_content')) {
                        $q->orWhere('english_content', 'like', '%' . $word . '%');
                    }
                }
            }
        });

        // دریافت لیست آیدی‌های پست‌های مطابق
        return $query->pluck('id')->toArray();
    }

    /**
     * مخفی کردن پست‌های مطابق با کلمات کلیدی
     *
     * @param  array  $postIds
     * @return int
     */
    private function hideMatchedPosts(array $postIds)
    {
        if (empty($postIds)) {
            return 0;
        }

        // مخفی کردن پست‌ها
        return Post::whereIn('id', $postIds)
            ->where('hide_content', false) // فقط پست‌هایی که مخفی نیستند
            ->update(['hide_content' => true]);
    }

    /**
     * دریافت لیست کلمات فیلتر شده قبلی
     *
     * @return array
     */
    private function getPreviousFilters()
    {
        try {
            $filters = DB::table('settings')
                ->where('key', 'content_filters')
                ->value('value');

            if ($filters) {
                $filtersArray = json_decode($filters, true);
                return is_array($filtersArray) ? $filtersArray : [];
            }
        } catch (\Exception $e) {
            Log::error('خطا در دریافت فیلترهای قبلی: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * ذخیره کلمات فیلتر شده در تنظیمات
     *
     * @param  string  $words
     * @return void
     */
    private function savePreviousFilter($words)
    {
        try {
            $filters = $this->getPreviousFilters();

            // افزودن کلمات جدید به لیست
            if (!in_array($words, $filters)) {
                // محدودیت تعداد فیلترهای ذخیره شده
                if (count($filters) >= 10) {
                    array_shift($filters); // حذف قدیمی‌ترین فیلتر
                }

                $filters[] = $words;

                // ذخیره در دیتابیس
                DB::table('settings')
                    ->updateOrInsert(
                        ['key' => 'content_filters'],
                        ['value' => json_encode($filters), 'updated_at' => now()]
                    );
            }
        } catch (\Exception $e) {
            Log::error('خطا در ذخیره فیلتر: ' . $e->getMessage());
        }
    }
}
