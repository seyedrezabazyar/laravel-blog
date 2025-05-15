<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GalleryController extends Controller
{
    // نمایش همه تصاویر بدون فیلتر hide_image و با pagination
    public function index()
    {
        $images = PostImage::orderBy('id', 'desc')->paginate(20);
        return view('admin.images.gallery', compact('images'));
    }

    // نمایش تصاویری که کد وضعیت 200 دارند و hide_image = null
    public function real()
    {
        $page = request()->get('page', 1);
        $images = PostImage::whereNull('hide_image')->orderBy('id', 'desc')->paginate(20, ['*'], 'page', $page);
        $validImages = [];

        // برای بهبود عملکرد، فقط برای تعداد محدودی تصویر بررسی 200 را انجام می‌دهیم
        $imagesToCheck = $images->take(20);

        $responses = Http::pool(function ($pool) use ($imagesToCheck) {
            foreach ($imagesToCheck as $image) {
                $imageUrl = $image->image_url ?? $image->image_path;
                if (!empty($imageUrl)) {
                    $pool->as($image->id)->timeout(3)->get($imageUrl);
                }
            }
        });

        foreach ($imagesToCheck as $image) {
            if (isset($responses[$image->id]) && $responses[$image->id]->status() === 200) {
                $validImages[] = $image;
            }
        }

        return view('admin.images.real', compact('images', 'validImages'));
    }

    // API برای تأیید تصویر (visible)
    public function approve(Request $request, $id)
    {
        $image = PostImage::findOrFail($id);
        $image->update(['hide_image' => 'visible']);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'تصویر تأیید شد.']);
        }

        return redirect()->back()->with('success', 'تصویر با موفقیت تأیید شد.');
    }

    // API برای تأیید گروهی تصاویر صفحه فعلی
    public function bulkApprove(Request $request)
    {
        $imageIds = $request->input('image_ids', []);
        if (!empty($imageIds)) {
            PostImage::whereIn('id', $imageIds)->update(['hide_image' => 'visible']);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'همه تصاویر تأیید شدند.']);
            }

            return redirect()->back()->with('success', 'همه تصاویر با موفقیت تأیید شدند.');
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'هیچ تصویری برای تأیید وجود ندارد.']);
        }

        return redirect()->back()->with('error', 'هیچ تصویری برای تأیید وجود ندارد.');
    }

    // API برای رد تصویر (hidden)
    public function reject(Request $request, $id)
    {
        $image = PostImage::findOrFail($id);
        $image->update(['hide_image' => 'hidden']);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'تصویر رد شد.']);
        }

        return redirect()->back()->with('success', 'تصویر با موفقیت رد شد.');
    }

    // نمایش تصاویر تأیید شده (visible)
    public function visible()
    {
        $images = PostImage::where('hide_image', 'visible')->orderBy('id', 'desc')->paginate(12);
        return view('admin.images.visible', compact('images'));
    }

    // نمایش تصاویر رد شده (hidden)
    public function hidden()
    {
        $images = PostImage::where('hide_image', 'hidden')->orderBy('id', 'desc')->paginate(12);
        return view('admin.images.hidden', compact('images'));
    }
}
