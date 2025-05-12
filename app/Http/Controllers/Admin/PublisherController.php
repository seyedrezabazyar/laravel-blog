<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Publisher;
use App\Services\DownloadHostService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PublisherController extends Controller
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
     * نمایش لیست ناشران
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $publishers = Publisher::withCount('posts')->paginate(10);
        return view('admin.publishers.index', compact('publishers'));
    }

    /**
     * نمایش فرم ایجاد ناشر جدید
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.publishers.create');
    }

    /**
     * ذخیره ناشر جدید در دیتابیس
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            Log::info('Store publisher request', $request->all());

            $validated = $request->validate([
                'name' => 'required|max:255',
                'slug' => 'nullable|max:255|unique:publishers,slug',
                'description' => 'nullable',
                'logo' => 'nullable|image|max:2048',
            ]);

            // اگر اسلاگ ارائه نشده باشد، آن را از نام ایجاد کنید
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            } else {
                // در غیر این صورت، اسلاگ ارائه شده را اسلاگ کنید
                $validated['slug'] = Str::slug($validated['slug']);
            }

            if ($request->hasFile('logo')) {
                // آپلود تصویر به هاست دانلود
                $path = $this->downloadHostService->upload($request->file('logo'), 'publishers');

                // اگر آپلود به هاست دانلود با خطا مواجه شد، از روش قبلی استفاده می‌کنیم
                if (!$path) {
                    $path = $request->file('logo')->store('publishers', config('filesystems.default_public', 'public'));
                }

                $validated['logo'] = $path;
            }

            $publisher = Publisher::create($validated);

            Log::info('Publisher created', ['id' => $publisher->id, 'name' => $publisher->name]);

            return redirect()->route('admin.publishers.index')
                ->with('success', 'ناشر با موفقیت ایجاد شد.');
        } catch (\Exception $e) {
            Log::error('Error creating publisher', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'خطا در ایجاد ناشر: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * نمایش فرم ویرایش ناشر
     *
     * @param  \App\Models\Publisher  $publisher
     * @return \Illuminate\View\View
     */
    public function edit(Publisher $publisher)
    {
        Log::info('Edit publisher page accessed', ['publisher_id' => $publisher->id]);
        return view('admin.publishers.edit', compact('publisher'));
    }

    /**
     * به‌روزرسانی ناشر در دیتابیس
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Publisher  $publisher
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Publisher $publisher)
    {
        try {
            Log::info('Update publisher request', [
                'publisher_id' => $publisher->id,
                'data' => $request->all()
            ]);

            $validated = $request->validate([
                'name' => 'required|max:255',
                'slug' => 'nullable|max:255|unique:publishers,slug,' . $publisher->id,
                'description' => 'nullable',
                'logo' => 'nullable|image|max:2048',
            ]);

            // اگر اسلاگ ارائه نشده باشد، آن را از نام ایجاد کنید
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            } else {
                // در غیر این صورت، اسلاگ ارائه شده را اسلاگ کنید
                $validated['slug'] = Str::slug($validated['slug']);
            }

            if ($request->hasFile('logo')) {
                // حذف لوگوی قبلی
                if ($publisher->logo) {
                    // بررسی کنیم که لوگو در هاست دانلود است یا در استوریج محلی
                    if (strpos($publisher->logo, 'http') === 0 || strpos($publisher->logo, 'publishers/') === 0) {
                        $this->downloadHostService->delete($publisher->logo);
                    } else {
                        Storage::disk('public')->delete($publisher->logo);
                    }
                }

                // آپلود لوگوی جدید به هاست دانلود
                $path = $this->downloadHostService->upload($request->file('logo'), 'publishers');

                // اگر آپلود به هاست دانلود با خطا مواجه شد، از روش قبلی استفاده می‌کنیم
                if (!$path) {
                    $path = $request->file('logo')->store('publishers', config('filesystems.default_public'));
                }

                $validated['logo'] = $path;
            }

            $publisher->update($validated);

            Log::info('Publisher updated', ['id' => $publisher->id, 'name' => $publisher->name]);

            return redirect()->route('admin.publishers.index')
                ->with('success', 'ناشر با موفقیت به‌روزرسانی شد.');
        } catch (\Exception $e) {
            Log::error('Error updating publisher', [
                'publisher_id' => $publisher->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'خطا در به‌روزرسانی ناشر: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * حذف ناشر از دیتابیس
     *
     * @param  \App\Models\Publisher  $publisher
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Publisher $publisher)
    {
        try {
            // بررسی اینکه آیا ناشر دارای کتاب است
            if ($publisher->posts()->count() > 0) {
                return redirect()->route('admin.publishers.index')
                    ->with('error', 'این ناشر دارای کتاب است و نمی‌توان آن را حذف کرد.');
            }

            // حذف لوگوی ناشر
            if ($publisher->logo) {
                // بررسی کنیم که لوگو در هاست دانلود است یا در استوریج محلی
                if (strpos($publisher->logo, 'http') === 0 || strpos($publisher->logo, 'publishers/') === 0) {
                    $this->downloadHostService->delete($publisher->logo);
                } else {
                    Storage::disk('public')->delete($publisher->logo);
                }
            }

            $publisher->delete();

            Log::info('Publisher deleted', ['id' => $publisher->id, 'name' => $publisher->name]);

            return redirect()->route('admin.publishers.index')
                ->with('success', 'ناشر با موفقیت حذف شد.');
        } catch (\Exception $e) {
            Log::error('Error deleting publisher', [
                'publisher_id' => $publisher->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.publishers.index')
                ->with('error', 'خطا در حذف ناشر: ' . $e->getMessage());
        }
    }
}
