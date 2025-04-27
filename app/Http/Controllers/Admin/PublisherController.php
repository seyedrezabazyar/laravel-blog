<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Publisher;
use App\Services\DownloadHostService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'logo' => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('logo')) {
            // آپلود لوگو به هاست دانلود
            $path = $this->downloadHostService->upload($request->file('logo'), 'publishers');

            // اگر آپلود به هاست دانلود با خطا مواجه شد، از روش قبلی استفاده می‌کنیم
            if (!$path) {
                $path = $request->file('logo')->store('publishers', 'public');
            }

            $validated['logo'] = $path;
        }

        Publisher::create($validated);

        return redirect()->route('admin.publishers.index')
            ->with('success', 'ناشر با موفقیت ایجاد شد.');
    }

    /**
     * نمایش جزئیات یک ناشر
     *
     * @param  \App\Models\Publisher  $publisher
     * @return \Illuminate\View\View
     */
    public function show(Publisher $publisher)
    {
        $publisher->load('posts');
        return view('admin.publishers.show', compact('publisher'));
    }

    /**
     * نمایش فرم ویرایش ناشر
     *
     * @param  \App\Models\Publisher  $publisher
     * @return \Illuminate\View\View
     */
    public function edit(Publisher $publisher)
    {
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
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'logo' => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

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
                $path = $request->file('logo')->store('publishers', 'public');
            }

            $validated['logo'] = $path;
        }

        $publisher->update($validated);

        return redirect()->route('admin.publishers.index')
            ->with('success', 'ناشر با موفقیت به‌روزرسانی شد.');
    }

    /**
     * حذف ناشر از دیتابیس
     *
     * @param  \App\Models\Publisher  $publisher
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Publisher $publisher)
    {
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

        return redirect()->route('admin.publishers.index')
            ->with('success', 'ناشر با موفقیت حذف شد.');
    }
}
