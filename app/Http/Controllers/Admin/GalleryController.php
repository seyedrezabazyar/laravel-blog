<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GalleryController extends Controller
{
    /**
     * نمایش صفحه گالری تصاویر جدید
     */
    public function index()
    {
        return view('admin.images.gallery');
    }

    /**
     * نمایش صفحه گالری تصاویر تایید شده
     */
    public function visible()
    {
        Log::info('Gallery visible page accessed');
        return view('admin.images.visible');
    }

    /**
     * نمایش صفحه گالری تصاویر رد شده
     */
    public function hidden()
    {
        return view('admin.images.hidden');
    }

    /**
     * نمایش صفحه گالری تصاویر واقعی
     */
    public function realImages()
    {
        Log::info('Gallery real images page accessed');
        return view('admin.images.real');
    }

    /**
     * دریافت تصاویر دسته‌بندی نشده
     */
    public function getImages(Request $request)
    {
        $images = PostImage::whereNull('hide_image')
            ->with('post:id,title')
            ->orderBy('id', 'asc') // ترتیب صعودی بر اساس ID
            ->paginate(100);

        // افزودن ویژگی‌های محاسبه شده و برگرداندن مسیر اصلی تصویر
        $images->getCollection()->transform(function ($image) {
            // استفاده مستقیم از image_path به جای image_url
            $image->makeVisible(['image_path']);

            // اضافه کردن مسیر کامل تصویر به عنوان یک ویژگی جدید
            $image->raw_image_url = $this->getFullImageUrl($image->image_path);

            return $image;
        });

        return response()->json($images);
    }

    /**
     * دریافت تصاویر تایید شده
     */
    public function getVisibleImages(Request $request)
    {
        // لاگ کردن درخواست برای اشکال‌زدایی
        Log::info('GetVisibleImages called with params: ' . json_encode($request->all()));

        try {
            // بررسی مقادیر ممکن برای hide_image در پایگاه داده
            $distinctValues = DB::table('post_images')
                ->select('hide_image')
                ->distinct()
                ->whereNotNull('hide_image')
                ->get()
                ->pluck('hide_image')
                ->toArray();

            Log::info('Distinct hide_image values: ' . json_encode($distinctValues));

            // تلاش با روش‌های مختلف برای یافتن تصاویر تایید شده
            $query = PostImage::query();

            // روش 1: اگر hide_image یک فیلد enum با مقدار 'visible' است
            if (in_array('visible', $distinctValues)) {
                Log::info('Using method 1: hide_image = visible');
                $query->where('hide_image', 'visible');
            }
            // روش 2: اگر hide_image یک فیلد boolean است و false نشان‌دهنده تایید است
            else if (in_array(0, $distinctValues) || in_array('0', $distinctValues)) {
                Log::info('Using method 2: hide_image = 0');
                $query->where('hide_image', 0);
            }
            // روش 3: اگر hide_image یک فیلد boolean است و true نشان‌دهنده تایید است
            else if (in_array(1, $distinctValues) || in_array('1', $distinctValues)) {
                Log::info('Using method 3: hide_image = 1');
                $query->where('hide_image', 1);
            }
            // روش 4: اگر از بقیه مقادیر استفاده می‌شود
            else {
                Log::info('Using method 4: first value from ' . json_encode($distinctValues));
                if (!empty($distinctValues)) {
                    $query->where('hide_image', $distinctValues[0]);
                } else {
                    // اگر هیچ مقداری وجود ندارد، فرض می‌کنیم که نال بودن نشان‌دهنده تایید نشده است
                    $query->whereNull('hide_image');
                }
            }

            // ادامه کوئری
            $query->with('post:id,title')
                ->orderBy('approved_at', 'desc');

            // نمایش تعداد برای اشکال‌زدایی
            $countBeforeFilters = $query->count();
            Log::info("Count before applying filters: $countBeforeFilters");

            // اعمال فیلتر جستجو
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('image_path', 'like', "%{$search}%");
                });
            }

            // اعمال فیلتر تاریخ
            if ($request->has('date') && !empty($request->date)) {
                switch ($request->date) {
                    case 'today':
                        $query->whereDate('approved_at', Carbon::today());
                        break;
                    case 'week':
                        $query->where('approved_at', '>=', Carbon::now()->subWeek());
                        break;
                    case 'month':
                        $query->where('approved_at', '>=', Carbon::now()->subMonth());
                        break;
                }
            }

            // اعمال ترتیب نمایش
            if ($request->has('sort') && !empty($request->sort)) {
                switch ($request->sort) {
                    case 'newest':
                        $query->orderBy('approved_at', 'desc');
                        break;
                    case 'oldest':
                        $query->orderBy('approved_at', 'asc');
                        break;
                }
            }

            // نمایش SQL کوئری در لاگ برای اشکال‌زدایی
            Log::info('Query SQL: ' . $query->toSql());
            Log::info('Query Bindings: ' . json_encode($query->getBindings()));

            // اجرای کوئری و دریافت نتایج
            $images = $query->paginate(100);

            // لاگ کردن تعداد نتایج برای اشکال‌زدایی
            Log::info('GetVisibleImages result count: ' . $images->count());

            // افزودن ویژگی‌های محاسبه شده و برگرداندن مسیر اصلی تصویر
            $images->getCollection()->transform(function ($image) {
                // استفاده مستقیم از image_path به جای image_url
                $image->makeVisible(['image_path']);

                // اضافه کردن مسیر کامل تصویر به عنوان یک ویژگی جدید
                $image->raw_image_url = $this->getFullImageUrl($image->image_path);

                return $image;
            });

            return response()->json($images);

        } catch (\Exception $e) {
            Log::error('Error in getVisibleImages: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت تصاویر: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * دریافت تصاویر رد شده
     */
    public function getHiddenImages(Request $request)
    {
        try {
            // بررسی مقادیر ممکن برای hide_image در پایگاه داده
            $distinctValues = DB::table('post_images')
                ->select('hide_image')
                ->distinct()
                ->whereNotNull('hide_image')
                ->get()
                ->pluck('hide_image')
                ->toArray();

            Log::info('Distinct hide_image values: ' . json_encode($distinctValues));

            // تلاش با روش‌های مختلف برای یافتن تصاویر رد شده
            $query = PostImage::query();

            // روش 1: اگر hide_image یک فیلد enum با مقدار 'hidden' است
            if (in_array('hidden', $distinctValues)) {
                $query->where('hide_image', 'hidden');
            }
            // روش 2: اگر hide_image یک فیلد boolean است و true نشان‌دهنده رد است
            else if (in_array(1, $distinctValues) || in_array('1', $distinctValues)) {
                $query->where('hide_image', 1);
            }
            // روش 3: اگر hide_image یک فیلد boolean است و false نشان‌دهنده رد است
            else if (in_array(0, $distinctValues) || in_array('0', $distinctValues)) {
                $query->where('hide_image', 0);
            }
            // روش 4: اگر از بقیه مقادیر استفاده می‌شود
            else {
                if (!empty($distinctValues)) {
                    $query->where('hide_image', $distinctValues[0]);
                } else {
                    // اگر هیچ مقداری وجود ندارد، فرض می‌کنیم که نال بودن نشان‌دهنده تایید نشده است
                    $query->whereNull('hide_image');
                }
            }

            $query->with('post:id,title')
                ->orderBy('updated_at', 'desc');

            // اعمال فیلتر جستجو
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('image_path', 'like', "%{$search}%");
                });
            }

            // اعمال ترتیب نمایش
            if ($request->has('sort') && !empty($request->sort)) {
                switch ($request->sort) {
                    case 'newest':
                        $query->orderBy('updated_at', 'desc');
                        break;
                    case 'oldest':
                        $query->orderBy('updated_at', 'asc');
                        break;
                }
            }

            $images = $query->paginate(100);

            // افزودن ویژگی‌های محاسبه شده و برگرداندن مسیر اصلی تصویر
            $images->getCollection()->transform(function ($image) {
                // استفاده مستقیم از image_path به جای image_url
                $image->makeVisible(['image_path']);

                // اضافه کردن مسیر کامل تصویر به عنوان یک ویژگی جدید
                $image->raw_image_url = $this->getFullImageUrl($image->image_path);

                return $image;
            });

            return response()->json($images);

        } catch (\Exception $e) {
            Log::error('Error in getHiddenImages: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت تصاویر: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * دریافت تصاویر واقعی - نسخه بهینه‌سازی شده
     */
    public function getRealImages(Request $request)
    {
        try {
            // لاگ برای اشکال‌زدایی
            Log::info('GetRealImages called with params: ' . json_encode($request->all()));

            // ساخت کوئری پایه
            $query = PostImage::query();

            // فقط تصاویری را بگیر که مسیر خالی یا default نباشند
            $query->whereNotNull('image_path')
                ->where('image_path', '!=', '')
                ->where(function($q) {
                    $q->where('image_path', 'NOT LIKE', '%default-book.png%')
                        ->where('image_path', 'NOT LIKE', '%placeholder%');
                });

            // فقط تصاویری که پسوند تصویر دارند
            $query->where(function($q) {
                $q->where('image_path', 'LIKE', '%.jpg')
                    ->orWhere('image_path', 'LIKE', '%.jpeg')
                    ->orWhere('image_path', 'LIKE', '%.png')
                    ->orWhere('image_path', 'LIKE', '%.gif')
                    ->orWhere('image_path', 'LIKE', '%.webp')
                    ->orWhere('image_path', 'LIKE', '%.svg');
            });

            // بارگذاری اطلاعات پست مرتبط
            $query->with('post:id,title');

            // اعمال فیلتر جستجو
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('image_path', 'like', "%{$search}%");
                });
            }

            // اعمال ترتیب نمایش
            if ($request->has('sort') && !empty($request->sort)) {
                switch ($request->sort) {
                    case 'newest':
                        $query->orderBy('id', 'desc');
                        break;
                    case 'oldest':
                        $query->orderBy('id', 'asc');
                        break;
                }
            } else {
                $query->orderBy('id', 'desc');
            }

            // لاگ کوئری برای اشکال‌زدایی
            Log::info('Query SQL: ' . $query->toSql());
            Log::info('Query Bindings: ' . json_encode($query->getBindings()));

            // اجرای کوئری و دریافت نتایج با پیجینیشن
            $images = $query->paginate(100);

            // لاگ تعداد نتایج
            Log::info('Total images found: ' . $images->total());

            // افزودن ویژگی‌های محاسبه شده و برگرداندن مسیر اصلی تصویر
            $images->getCollection()->transform(function ($image) {
                // استفاده مستقیم از image_path به جای image_url
                $image->makeVisible(['image_path']);

                // اضافه کردن مسیر کامل تصویر
                $image->raw_image_url = $this->getFullImageUrl($image->image_path);

                return $image;
            });

            // اضافه کردن لاگ برای اشکال‌زدایی
            Log::info('Real Images API response', [
                'count' => $images->count(),
                'current_page' => $images->currentPage(),
                'total_pages' => $images->lastPage(),
                'first_image' => $images->first() ? $images->first()->image_path : null
            ]);

            return response()->json($images);

        } catch (\Exception $e) {
            Log::error('Error in getRealImages: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت تصاویر: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * تبدیل مسیر تصویر به URL کامل
     */
    private function getFullImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return asset('images/default-book.png');
        }

        // Direct URL for HTTP/HTTPS paths
        if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
            return $imagePath;
        }

        // Handle images.balyan.ir domain
        if (strpos($imagePath, 'images.balyan.ir/') !== false) {
            return 'https://' . $imagePath;
        }

        // Handle download host images
        if (strpos($imagePath, 'post_images/') === 0 || strpos($imagePath, 'posts/') === 0) {
            return config('app.custom_image_host', 'https://images.balyan.ir') . '/' . $imagePath;
        }

        // Local storage fallback
        return asset('storage/' . $imagePath);
    }

    /**
     * دسته‌بندی تصویر
     */
    public function categorizeImage(Request $request)
    {
        $request->validate([
            'image_id' => 'required|exists:post_images,id',
            'hide_image' => 'required|boolean',
        ]);

        try {
            $image = PostImage::findOrFail($request->image_id);

            // تبدیل مقدار boolean به مقدار مناسب برای hide_image
            // بررسی نوع فیلد hide_image
            $distinctValues = DB::table('post_images')
                ->select('hide_image')
                ->distinct()
                ->whereNotNull('hide_image')
                ->get()
                ->pluck('hide_image')
                ->toArray();

            // تعیین مقدار مناسب برای hide_image
            if (in_array('visible', $distinctValues) || in_array('hidden', $distinctValues)) {
                // اگر فیلد یک enum است
                $image->hide_image = $request->hide_image ? 'hidden' : 'visible';
            } else {
                // اگر فیلد boolean است
                $image->hide_image = $request->hide_image ? 1 : 0;
            }

            // اگر تصویر تایید می‌شود، زمان تایید را ثبت کنید
            if (!$request->hide_image) {
                $image->approved_at = Carbon::now();
            }

            $image->save();

            // پاک کردن کش مربوط به این تصویر
            $this->clearImageCache($image->id);

            return response()->json([
                'success' => true,
                'message' => 'تصویر با موفقیت دسته‌بندی شد'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in categorizeImage: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'خطا در دسته‌بندی تصویر: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * مدیریت تصاویر تایید شده (خروج از حالت تایید یا رد کردن)
     */
    public function manageImage(Request $request)
    {
        $request->validate([
            'image_id' => 'required|exists:post_images,id',
            'action' => 'required|in:unapprove,reject',
        ]);

        try {
            $image = PostImage::findOrFail($request->image_id);

            // بررسی نوع فیلد hide_image
            $distinctValues = DB::table('post_images')
                ->select('hide_image')
                ->distinct()
                ->whereNotNull('hide_image')
                ->get()
                ->pluck('hide_image')
                ->toArray();

            if ($request->action === 'unapprove') {
                // خروج از حالت تایید - بازگشت به حالت دسته‌بندی نشده
                $image->hide_image = null;
                $image->approved_at = null;
            } else {
                // رد کردن تصویر
                if (in_array('hidden', $distinctValues)) {
                    $image->hide_image = 'hidden';
                } else if (in_array(1, $distinctValues) || in_array('1', $distinctValues)) {
                    $image->hide_image = 1;
                } else {
                    $image->hide_image = 0;
                }
                $image->approved_at = null;
            }

            $image->save();

            // پاک کردن کش مربوط به این تصویر
            $this->clearImageCache($image->id);

            return response()->json([
                'success' => true,
                'message' => 'تصویر با موفقیت بروزرسانی شد'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in manageImage: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی تصویر: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * پاک کردن کش مربوط به یک تصویر
     */
    private function clearImageCache($imageId)
    {
        Cache::forget("post_image_{$imageId}_url");
        Cache::forget("post_image_{$imageId}_display_url_admin");
        Cache::forget("post_image_{$imageId}_display_url_user");
    }
}
